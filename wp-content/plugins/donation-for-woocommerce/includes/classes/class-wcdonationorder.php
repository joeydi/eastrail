<?php

class WcDonationOrder {
	
	public function __construct() {
		add_filter( 'restrict_manage_posts', array( $this, 'add_new_order_filter' ), 10, 0 );
		add_action( 'pre_get_posts', array( $this, 'process_admin_shop_order_compaign_name_filter' ), 10, 1 );      
		add_action( 'wp_ajax_donation_to_order', array( $this, 'add_donation_to_order_action' ), 10 );
		add_action( 'wp_ajax_nopriv_donation_to_order', array( $this, 'add_donation_to_order_action' ), 10 );
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'wc_donation_alter_price_cart' ), 99 );
		add_filter( 'woocommerce_cart_item_price', array( $this, 'wc_donation_cart_item_price_filter' ), 99, 3 );
		add_action('woocommerce_checkout_create_order_line_item', array( $this, 'addCartItemMetaToOrderItemMeta' ), 10, 3);
		// add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'add_donation_causes_order_meta' ), 10, 4 );
		add_filter('woocommerce_order_item_display_meta_key', array( $this, 'changeOrderItemMetaTitle' ), 20, 3 );
		add_filter('woocommerce_checkout_order_processed', array( $this, 'woocommerce_checkout_order_processed' ), 10, 1 );
		add_filter('woocommerce_store_api_checkout_order_processed', array( $this, 'woocommerce_checkout_order_processed' ), 10, 1 );
		add_filter('woocommerce_order_item_display_meta_value', array( $this, 'changeOrderItemMetaValue' ), 20, 3 );
		add_filter('woocommerce_hidden_order_itemmeta', array( $this, 'wc_donation_custom_woocommerce_hidden_order_itemmeta' ), 10, 1 );
		add_filter('woocommerce_order_item_get_formatted_meta_data', array( $this, 'wc_donation_order_item_get_formatted_meta_data' ), 10, 1 );
		
		add_filter('woocommerce_get_item_data', array( $this, 'displayCartItemCompaignMeta' ), 10, 2);

		add_action('woocommerce_thankyou', array( $this, 'wc_donation_counts_on_place_order' ), 10);
		add_action('woocommerce_order_status_changed', array( $this, 'wc_donation_order_status_changed' ), 10, 4);      
		add_action('woocommerce_subscription_renewal_payment_complete', array( $this, 'wc_donation_counts_on_subscription' ), 10, 2);       

		add_filter('woocommerce_subscriptions_product_price_string_inclusions', array( $this, 'wc_donation_recurring_price' ), 10, 2 );
		add_filter('woocommerce_subscriptions_product_period', array( $this, 'wc_donation_recurring_period' ), 10, 2 );
		add_filter('woocommerce_subscriptions_product_period_interval', array( $this, 'wc_donation_recurring_interval' ), 10, 2 );
		add_filter('woocommerce_subscriptions_product_length', array( $this, 'wc_donation_recurring_length' ), 10, 2 );

		add_filter( 'woocommerce_email_attachments', array( $this, 'wc_donation_attach_pdf_invoice_for_donation_on_new_order' ), 999, 4 );

		add_action('woocommerce_thankyou', array( $this, 'wc_donation_create_report' ), 10, 1);
		if ( is_admin() ) {
			add_action('woocommerce_new_order', array( $this, 'wc_donation_create_report' ), 10, 1);
		}
		add_action('woocommerce_subscription_renewal_payment_complete', array( $this, 'wc_donation_create_report_on_subscription_renewal' ), 10, 2);

		add_action( 'wp_ajax_wc_donation_reset_data', array( $this, 'wc_donation_reset_data' ) );

		add_action('woocommerce_new_order_item', array( $this, 'custom_action_on_order_item' ), 10, 2);

		add_action('woocommerce_order_status_pending', array( $this, 'handle_order_pending' ) );
		add_action('woocommerce_order_status_completed', array( $this, 'handle_order_completed' ) );
		add_action('woocommerce_order_status_refunded', array( $this, 'handle_order_refunded' ) );
		add_action('woocommerce_order_status_cancelled', array( $this, 'handle_order_cancelled' ) );
		add_action('woocommerce_order_status_failed', array( $this, 'handle_order_failed' ) );
		add_action('send_awareness_donation_email_event', array( $this, 'send_scheduled_awareness_email' ), 10, 3);
		add_action('wp_ajax_update_email_status', array( $this, 'update_email_status' ));
	}

	public function update_email_status() {
		// Verify nonce for extra security
		if ( ! isset($_POST['nonce']) || ! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'donation_email_action')) {
			wp_send_json_error(array( 'message' => 'Invalid nonce.' ));
			return;
		}

		// Check if user has permission to edit post
		if (!isset($_POST['post_id']) || !current_user_can('edit_post', sanitize_text_field( $_POST['post_id'] ) ) ) {
			wp_send_json_error(array( 'message' => 'Unauthorized.' ));
			return;
		}

		$post_id = intval($_POST['post_id']);
		$email_templates_enabled = isset($_POST['email_templates_enabled']) && 'yes' === $_POST['email_templates_enabled'] ? 'yes' : 'no';

		// Update the post meta
		if (update_post_meta($post_id, '_email_templates_enabled', $email_templates_enabled)) {
			wp_send_json_success(array( 'message' => 'Post meta updated successfully.' ));
		} else {
			wp_send_json_error(array( 'message' => 'Failed to update post meta.' ));
		}
	}
	
	public function get_email_template( $template_post_id ) {
		// Retrieve email subject and content from post meta using the post ID
		$subject = get_post_meta($template_post_id, 'donation_email_subject', true);
		$content = get_post_meta($template_post_id, 'donation_email_editor', true);

		return array(
			'subject' => $subject,
			'content' => $content,
		);
	}

	public function send_customer_email( $order, $template_post_id, $product_id ) {
		// Get customer email
		$customer_email = $order->get_billing_email();

		// Get email subject and content dynamically from the database
		$template = $this->get_email_template($template_post_id); // Use $this to call the method
		$subject = $template['subject'];
		$content = $template['content'];

		if (empty($subject) || empty($content)) {
			// Log or handle cases where the template is not properly configured
			echo '<pre>Error: Email template not properly configured for template ID: ' . esc_html($template_post_id) . '</pre>';
			return;
		}

		// Initialize placeholders with default values
		$placeholders = array(
			'{order_id}' => $order->get_id(),
			'{customer_name}' => $order->get_billing_first_name(),
			'{campaign_name}' => '',
			'{donated_amount}' => '',
			'{donation_goal}' => '1000', // Example value, replace with actual data
			'{tribute}' => '',
			'{cause}' => '',
			'{gift_aid}' => '',
			'{donation_type}' => 'donation', // Default type
			'{Campaign_e_card_Template}' => '', // Example: Add logic for e-card template link if needed
			'{campaign_url}' => '', // Example: Add campaign-specific URL if needed
		);

		// Populate placeholders from order items
		foreach ($order->get_items() as $item_id => $item) {
			$type = get_post_meta($item->get_product_id(), 'is_wc_donation', true);
			if (!empty($type) && 'donation' === $type && $item->get_product_id() == $product_id) {
				$campaign_id = wc_get_order_item_meta($item_id, 'campaign_id', true);
				$donation_type   = get_post_meta( $campaign_id, 'donation_type', true );
				if ( 'recurring' == $donation_type ) {
					$donation_type_campaign = 'Recurring Donation';
				} else {
					$donation_type_campaign = 'One Time Donation';
				}
				$placeholders['{campaign_name}'] = get_the_title($campaign_id);
				$placeholders['{cause}'] = wc_get_order_item_meta($item_id, 'cause_name', true);
				$placeholders['{gift_aid}'] = wc_get_order_item_meta($item_id, 'gift_aid', true);
				$placeholders['{tribute}'] = wc_get_order_item_meta($item_id, 'tribute', true);
				$placeholders['{donated_amount}'] = wc_price($item->get_total(), array( 'currency' => $order->get_currency() ));
				$placeholders['{campaign_url}'] = get_permalink($campaign_id);
				$placeholders['{donation_goal}'] = get_post_meta($campaign_id, 'wc-donation-goal-fixed-amount-field', true);
				$placeholders['{donation_type}'] = $donation_type_campaign;
				$e_card_template_enable = get_post_meta ( $campaign_id, 'wc-donation-e-card-templates-display-option', true  );
				if ( 'enabled' === $e_card_template_enable ) {
					$e_card_templates = get_post_meta($campaign_id, 'wc-donation-e-card-template', true);
					
					if (!empty($e_card_templates)) {
						$e_card_templates = maybe_unserialize($e_card_templates);

						if ( !empty($e_card_templates) ) {
							$placeholders['{Campaign_e_card_Template}'] = '<img src="' . esc_url($e_card_templates) . '" alt="Campaign E-Card Template" style="max-width:100%;">';
						} else {
							$placeholders['{Campaign_e_card_Template}'] = ''; // Fallback if no valid URL is found
						}
					} else {
						$placeholders['{Campaign_e_card_Template}'] = ''; // Fallback if meta is empty
					}
				}
				
				// Add logic for other placeholders as needed
				break; // Assuming one match per order
			}
		}

		// Replace placeholders in the email content
		foreach ($placeholders as $placeholder => $value) {
			$content = str_replace($placeholder, $value, $content);
		}

		/**
		* Filter.
		* 
		* @since 3.9.6.1
		*/
		$content = apply_filters( 'wc_donation_send_customer_email_body', $content );
		// Send the email
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		$email_sent = wp_mail($customer_email, $subject, $content, $headers);
	}

	// Helper function to convert time to seconds
	private function convert_time_to_seconds( $interval, $unit ) {
		switch ($unit) {
			case 'days':
				return $interval * DAY_IN_SECONDS;
			case 'weeks':
				return $interval * WEEK_IN_SECONDS;
			case 'years':
				return $interval * YEAR_IN_SECONDS;
			default:
				return 0;
		}
	}

	// Hook to send the awareness email
	public function send_scheduled_awareness_email( $order_id, $email_post_id, $product_id ) {

		update_option('cron_test', $product_id);

		//wp_mail('shahzad@gmail.com', 'chk', 'test');
		$order = wc_get_order($order_id);
		
		if ($order) {
			// Send the awareness email
			$this->send_customer_email($order, $email_post_id, $product_id);
		}
	}

	public function handle_order_completed( $order_id ) {
		$order = wc_get_order($order_id);

		// Get product IDs from the order
		$product_ids = array();
		foreach ($order->get_items() as $item) {
			$product_ids[] = $item->get_product_id();
		}

		// Retrieve campaign IDs from the product meta
		$campaign_ids = array();
		foreach ($product_ids as $product_id) {
			$campaign_id = get_post_meta($product_id, 'wc_donation_campaign', true);
			if (!empty($campaign_id)) {
				$campaign_ids[] = $campaign_id;
			}
		}

		if (!empty($campaign_ids)) {
			// Get all posts of custom post type 'wc-donation-email'
			$args = array(
				'post_type'      => 'wc-donation-email',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
			);
			$donation_emails = get_posts($args);

			foreach ($donation_emails as $email_post) {
				// Check the post meta key `_donation_action`
				$donation_action = get_post_meta($email_post->ID, '_donation_action', true);
				$awareness_email_enabled = get_post_meta($email_post->ID, '_awareness_email_enabled', true);
				$email_templates_enabled = get_post_meta($email_post->ID, '_email_templates_enabled', true);
				if ( 'completed_email' === $donation_action && 'yes' === $email_templates_enabled ) {
					// Get campaign IDs associated with this email post
					$email_campaign_ids = get_post_meta($email_post->ID, 'campaign_ids', true);

					if (!empty($email_campaign_ids)) {
						$email_campaign_ids = maybe_unserialize($email_campaign_ids);

						// Check if any of the order campaign IDs match the email campaign IDs
						foreach ($product_ids as $product_id) {
							if (in_array($product_id, $email_campaign_ids)) {
								// Trigger email sending immediately
								$this->send_customer_email($order, $email_post->ID, $product_id);
							}
						}
					}
				} elseif ( 'awareness_email' === $donation_action && 'yes' === $awareness_email_enabled && 'yes' === $email_templates_enabled ) {
					// Get awareness email interval and unit
					$awareness_email_interval = get_post_meta($email_post->ID, '_awareness_email_interval', true);
					$awareness_email_unit = get_post_meta($email_post->ID, '_awareness_email_unit', true);

					// Convert interval to seconds
					$interval_seconds = $this->convert_time_to_seconds($awareness_email_interval, $awareness_email_unit);

					if ($interval_seconds > 0) {
						// Schedule the awareness email
						if ( ! wp_next_scheduled( 'send_awareness_donation_email_event' ) ) {
							wp_schedule_single_event(
								time() + $interval_seconds, // Trigger after interval
								'send_awareness_donation_email_event', // Hook to listen for
								array(
									'order_id' => $order_id,
									'email_post_id' => $email_post->ID,
									'product_id' => reset($product_ids), // Assuming one product per awareness email
								)
							);
						}
					}
				}
			}
		}
	}

	public function handle_order_pending( $order_id ) {
		$order = wc_get_order($order_id);

		// Get product IDs from the order
		$product_ids = array();
		foreach ($order->get_items() as $item) {
			$product_ids[] = $item->get_product_id();
		}

		// Retrieve campaign IDs from the product meta
		$campaign_ids = array();
		foreach ($product_ids as $product_id) {
			$campaign_id = get_post_meta($product_id, 'wc_donation_campaign', true);
			if (!empty($campaign_id)) {
				$campaign_ids[] = $campaign_id;
			}
		}

		if (!empty($campaign_ids)) {
			// Get all posts of custom post type 'wc-donation-email'
			$args = array(
				'post_type'      => 'wc-donation-email',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
			);
			$donation_emails = get_posts($args);

			foreach ($donation_emails as $email_post) {
				// Check if the post meta key `_donation_action` is `completed_email`
				$donation_action = get_post_meta($email_post->ID, '_donation_action', true);
				$email_templates_enabled = get_post_meta($email_post->ID, '_email_templates_enabled', true);
				if ( 'pending_email' === $donation_action && 'yes' === $email_templates_enabled ) {
					// Get campaign IDs associated with this email post
					$email_campaign_ids = get_post_meta($email_post->ID, 'campaign_ids', true);

					if (!empty($email_campaign_ids)) {
						$email_campaign_ids = maybe_unserialize($email_campaign_ids);

						// Check if any of the order campaign IDs match the email campaign IDs
						foreach ($product_ids as $product_id) {
							if (in_array($product_id, $email_campaign_ids)) {
								// Trigger email sending
								$this->send_customer_email($order, $email_post->ID, $product_id);
							}
						}
					}
				}
			}
		}
	}

	public function handle_order_refunded( $order_id ) {
		$order = wc_get_order($order_id);

		// Get product IDs from the order
		$product_ids = array();
		foreach ($order->get_items() as $item) {
			$product_ids[] = $item->get_product_id();
		}

		// Retrieve campaign IDs from the product meta
		$campaign_ids = array();
		foreach ($product_ids as $product_id) {
			$campaign_id = get_post_meta($product_id, 'wc_donation_campaign', true);
			if (!empty($campaign_id)) {
				$campaign_ids[] = $campaign_id;
			}
		}

		if (!empty($campaign_ids)) {
			// Get all posts of custom post type 'wc-donation-email'
			$args = array(
				'post_type'      => 'wc-donation-email',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
			);
			$donation_emails = get_posts($args);

			foreach ($donation_emails as $email_post) {
				// Check if the post meta key `_donation_action` is `completed_email`
				$donation_action = get_post_meta($email_post->ID, '_donation_action', true);
				$email_templates_enabled = get_post_meta($email_post->ID, '_email_templates_enabled', true);
				if ( 'refunded_email' === $donation_action && 'yes' === $email_templates_enabled ) {
					// Get campaign IDs associated with this email post
					$email_campaign_ids = get_post_meta($email_post->ID, 'campaign_ids', true);

					if (!empty($email_campaign_ids)) {
						$email_campaign_ids = maybe_unserialize($email_campaign_ids);

						// Check if any of the order campaign IDs match the email campaign IDs
						foreach ($product_ids as $product_id) {
							if (in_array($product_id, $email_campaign_ids)) {
								// Trigger email sending
								$this->send_customer_email($order, $email_post->ID, $product_id);
							}
						}
					}
				}
			}
		}
	}

	public function handle_order_cancelled( $order_id ) {
		$order = wc_get_order($order_id);

		// Get product IDs from the order
		$product_ids = array();
		foreach ($order->get_items() as $item) {
			$product_ids[] = $item->get_product_id();
		}

		// Retrieve campaign IDs from the product meta
		$campaign_ids = array();
		foreach ($product_ids as $product_id) {
			$campaign_id = get_post_meta($product_id, 'wc_donation_campaign', true);
			if (!empty($campaign_id)) {
				$campaign_ids[] = $campaign_id;
			}
		}

		if (!empty($campaign_ids)) {
			// Get all posts of custom post type 'wc-donation-email'
			$args = array(
				'post_type'      => 'wc-donation-email',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
			);
			$donation_emails = get_posts($args);

			foreach ($donation_emails as $email_post) {
				// Check if the post meta key `_donation_action` is `completed_email`
				$donation_action = get_post_meta($email_post->ID, '_donation_action', true);
				$email_templates_enabled = get_post_meta($email_post->ID, '_email_templates_enabled', true);
				if ( 'cancelled_email' === $donation_action && 'yes' === $email_templates_enabled ) {
					// Get campaign IDs associated with this email post
					$email_campaign_ids = get_post_meta($email_post->ID, 'campaign_ids', true);

					if (!empty($email_campaign_ids)) {
						$email_campaign_ids = maybe_unserialize($email_campaign_ids);

						// Check if any of the order campaign IDs match the email campaign IDs
						foreach ($product_ids as $product_id) {
							if (in_array($product_id, $email_campaign_ids)) {
								// Trigger email sending
								$this->send_customer_email($order, $email_post->ID, $product_id);
							}
						}
					}
				}
			}
		}
	}

	public function handle_order_failed( $order_id ) {
		$order = wc_get_order($order_id);

		// Get product IDs from the order
		$product_ids = array();
		foreach ($order->get_items() as $item) {
			$product_ids[] = $item->get_product_id();
		}

		// Retrieve campaign IDs from the product meta
		$campaign_ids = array();
		foreach ($product_ids as $product_id) {
			$campaign_id = get_post_meta($product_id, 'wc_donation_campaign', true);
			if (!empty($campaign_id)) {
				$campaign_ids[] = $campaign_id;
			}
		}

		if (!empty($campaign_ids)) {
			// Get all posts of custom post type 'wc-donation-email'
			$args = array(
				'post_type'      => 'wc-donation-email',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
			);
			$donation_emails = get_posts($args);

			foreach ($donation_emails as $email_post) {
				// Check if the post meta key `_donation_action` is `completed_email`
				$donation_action = get_post_meta($email_post->ID, '_donation_action', true);
				$email_templates_enabled = get_post_meta($email_post->ID, '_email_templates_enabled', true);
				if ( 'failed_email' === $donation_action && 'yes' === $email_templates_enabled ) {
					// Get campaign IDs associated with this email post
					$email_campaign_ids = get_post_meta($email_post->ID, 'campaign_ids', true);

					if (!empty($email_campaign_ids)) {
						$email_campaign_ids = maybe_unserialize($email_campaign_ids);

						// Check if any of the order campaign IDs match the email campaign IDs
						foreach ($product_ids as $product_id) {
							if (in_array($product_id, $email_campaign_ids)) {
								// Trigger email sending
								$this->send_customer_email($order, $email_post->ID, $product_id);
							}
						}
					}
				}
			}
		}
	}

	public function custom_action_on_order_item( $item_id, $item ) {
		if ( ! is_admin() ) {
			return false;
		}
		if ( 'line_item' === $item->get_type() ) {
			
			$product = $item->get_product(); // Get the product object
			
			if ( $product && 'donation' === get_post_meta($product->get_id(), 'is_wc_donation', true) ) {
				
				$campaign_id = get_post_meta($product->get_id(), 'wc_donation_campaign', true);
				$campaign_type = get_post_meta($campaign_id, 'wc-donation-recurring', true);
				
				if ( 'disabled' === $campaign_type ) {
					$campaign_type = 'single';
				} else {
					$campaign_type = 'recurring';
				}
				$item->add_meta_data( 'campaign_id', sanitize_text_field($campaign_id) );
				$item->add_meta_data( 'compaign', get_the_title(sanitize_text_field($campaign_id)) );
				$item->add_meta_data( 'campaign_type', sanitize_text_field( $campaign_type ) );
				$item->save();
			}
		}
	}


	/**
	 * Method update_sub_cart
	 *
	 * @param $cart_item_data
	 *
	 * @return void
	 */
	public function update_sub_cart( $cart_item_data ) {

		$cart = WC()->cart->get_cart();
		// Iterate over each item in the cart
		foreach ( $cart as $cart_item_key => $cart_item ) {
			// Update the cart item data
			WC()->cart->cart_contents[$cart_item_key]['wps_sfw_subscription_number'] =  isset($cart_item_data['wps_sfw_subscription_number']) ? $cart_item_data['wps_sfw_subscription_number'] : '';    
			WC()->cart->cart_contents[$cart_item_key]['wps_sfw_subscription_interval'] = isset($cart_item_data['wps_sfw_subscription_interval']) ? $cart_item_data['wps_sfw_subscription_interval'] : '';
			WC()->cart->cart_contents[$cart_item_key]['wps_sfw_subscription_expiry_number'] = isset($cart_item_data['wps_sfw_subscription_expiry_number']) ? $cart_item_data['wps_sfw_subscription_expiry_number'] : '';
			WC()->cart->cart_contents[$cart_item_key]['wps_sfw_subscription_expiry_interval'] = isset($cart_item_data['wps_sfw_subscription_expiry_interval']) ? $cart_item_data['wps_sfw_subscription_expiry_interval'] : '';
		}
	}
	
	
	/**
	 * Method wc_donation_reset_data
	 *
	 * @return void
	 */
	public function wc_donation_reset_data() {

		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), '_wcdnonce' ) ) {
			exit('Unauthorized');
		}

		if ( isset( $_POST['campaign_id'] ) ) {

			return self::reset_donation_campagin_data( sanitize_text_field($_POST['campaign_id']) );
		}

		wp_die();
	}
	
	/**
	 * Method reset_donation_campagin_data
	 *
	 * @param $campaign_id
	 *
	 * @return void
	 */
	public static function reset_donation_campagin_data( $campaign_id = '' ) {

		if ( empty( $campaign_id ) ) {
			return false;
		}

		$product_id = get_post_meta( $campaign_id, 'wc_donation_product', true );
		$is_donation_product = get_post_meta ( $product_id, 'is_wc_donation', true );
		if ( 'donation' === $is_donation_product ) {
			$all_users = get_users( array(
				'meta_key'     => 'donation_product_' . $product_id,
				'meta_value'   => 'yes',
				'fields'       => array( 'id', 'display_name' ),
			));

			if ( is_array( $all_users ) && count( $all_users ) > 0 ) {
				foreach ( $all_users as $user ) {
					delete_user_meta( $user->id, 'donation_product_' . $product_id );
				}
			}

			update_post_meta( $product_id, 'total_donations', 0 );
			update_post_meta( $product_id, 'total_donors', 0 );
			update_post_meta( $product_id, 'total_donation_amount', 0 );
			return true;
		} else {
			return false;
		}
	}

	public function wc_donation_order_status_changed( $order_id, $order_status_from, $order_status_to, $order ) {
		/**
		* Action.
		* 
		* @since 3.4.5
		*/
		$order_status_to_check = apply_filters( 'wc_donation_order_status_changed_to', array( 'cancelled' ) );
		if ( in_array( $order_status_from, array( 'completed', 'processing' ), true ) && in_array( $order_status_to, $order_status_to_check, true ) ) {

			// Get the user ID from WC_Order methods
			$user_id = $order->get_user_id(); // or $order->get_customer_id();
			$is_donation_product = 'no';
			// Loop through order items
			foreach ( $order->get_items() as $item_id => $item ) {

				// Get the product object
				$product_id = $item->get_product_id();
				
				//Check if donation product
				
				$is_donation_product = get_post_meta ($product_id, 'is_wc_donation', true);
				if ( 'donation' === $is_donation_product ) {

					$item_total = (float) $item->get_total(); // Get the item line total discounted
					//increase the value by 1
					$total_donations = (int) get_post_meta( $product_id, 'total_donations', true );
					$total_donation_amount = (float) get_post_meta( $product_id, 'total_donation_amount', true );

					if ( 0 < $total_donations ) {
						$total_donations = --$total_donations;
						update_post_meta( $product_id, 'total_donations', $total_donations);
					}

					if ( $item_total <= $total_donation_amount ) {
						$total_donation_amount = $total_donation_amount - $item_total;
						update_post_meta( $product_id, 'total_donation_amount', $total_donation_amount );
					}
					
				}
			}

		}

		if ( in_array( $order_status_from, array( 'on-hold', 'pending' ), true ) && in_array( $order_status_to, array( 'completed', 'processing' ), true ) ) {

			// Get the user ID from WC_Order methods
			$user_id = $order->get_user_id(); // or $order->get_customer_id();
			$is_donation_product = 'no';
			// Loop through order items
			foreach ( $order->get_items() as $item_id => $item ) {

				// Get the product object
				$product_id = $item->get_product_id();
				
				//Check if donation product
				
				$is_donation_product = get_post_meta ($product_id, 'is_wc_donation', true);
				if ( 'donation' === $is_donation_product ) {
					
					if ( 'yes' !== get_user_meta( $user_id, 'donation_product_' . $product_id, true ) ) {
						//decrease the value by 1
						update_user_meta( $user_id, 'donation_product_' . $product_id, 'yes' );
						$total_donors = get_post_meta( $product_id, 'total_donors', true );
						$total_donors++;
						update_post_meta( $product_id, 'total_donors', $total_donors);                  
					}

					$item_total = (float) $item->get_total(); // Get the item line total discounted
					//increase the value by 1
					$total_donations = (int) get_post_meta( $product_id, 'total_donations', true );
					$total_donation_amount = (float) get_post_meta( $product_id, 'total_donation_amount', true );

					$total_donations = ++$total_donations;
					update_post_meta( $product_id, 'total_donations', $total_donations);

					$total_donation_amount = $total_donation_amount + $item_total;
					update_post_meta( $product_id, 'total_donation_amount', $total_donation_amount );
					
				}
			}
			
		}
	}



	public function wc_donation_create_report_on_subscription_renewal( $subscription, $last_order ) {
		
		$order_id = $last_order->id;
		if ( ! $order_id ) {
			return;
		}

		// Get an instance of the WC_Order object
		$my_order = wc_get_order( $order_id );
		$currency = get_woocommerce_currency_symbol($my_order->get_currency());

		// Loop through order items
		foreach ( $my_order->get_items() as $item_id => $item ) {

			$product_id = $item->get_product_id();

			$is_donation_product = get_post_meta ($product_id, 'is_wc_donation', true);
			
			if ( ! empty( $is_donation_product ) && 'donation' === $is_donation_product ) {
				$donation_amount = $item->get_total();
				$donation_type = 'recurring';
				WcDonationReports::add_report( $order_id, $item_id, $product_id, $donation_amount, $currency, $donation_type );
			}

		}
	}

	public function wc_donation_create_report( $order_id ) {
		//will start working here.
		if ( ! $order_id ) {
			return;
		}

		// Get an instance of the WC_Order object
		$my_order = wc_get_order( $order_id );

		// Allow code execution only once 
		if ( ! $my_order->get_meta( 'wc_donation_report_generated' , true ) ) {

			$currency = get_woocommerce_currency_symbol($my_order->get_currency());

			// Loop through order items
			foreach ( $my_order->get_items() as $item_id => $item ) {

				$product_id = $item->get_product_id();

				$is_donation_product = get_post_meta ( $product_id, 'is_wc_donation', true );
				
				if ( ! empty( $is_donation_product ) && 'donation' === $is_donation_product ) {
					$donation_amount = $item->get_total();
					$product_campaign_id = get_post_meta( $product_id, 'wc_donation_campaign', true );

					// Check if $product_campaign_id exists before using it to retrieve donation_recurring meta
					if ( $product_campaign_id ) {
						$donation_recurring = get_post_meta($product_campaign_id, 'wc-donation-recurring', true);

						 // Check if donation_recurring meta exists
						if ( '' !== $donation_recurring && 'disabled' == $donation_recurring ) {
							$donation_type = 'non_recurring';
						} else {
							$donation_type = 'recurring';
						}
					}

					$combinedCampaignIds[]= $product_campaign_id;
					$my_order->update_meta_data( 'wc_campaign_id', $combinedCampaignIds );

					WcDonationReports::add_report( $order_id, $item_id, $product_id, $donation_amount, $currency, $donation_type );
				} else {
					$enable_product_donation = get_option( 'wc_donation_enable_option', true );
					if ( 'enable' === $enable_product_donation ) {
						$settings = get_option( 'wc_donation_settings', array() );
						$campaign_settings = isset( $settings['campaign_ids'] ) ? $settings['campaign_ids'] : array();

						$total_donation = 0;
						if ( empty( $is_donation_product ) ) {
							foreach ( $campaign_settings as $campaign_id => $campaign ) {
								if ( in_array( $product_id, $campaign['product_ids'] ) ) {
									$product_total = $item->get_total();
									
									$item_id = $item->get_id();
									$donation_percentage = isset( $campaign['donation_amount'] ) ? $campaign['donation_amount'] : 0;
									$donation_amount = $item->get_meta( 'campaign_donation_amount', true );

									$product_campaign_id = get_post_meta( $campaign_id, 'wc_donation_campaign', true );

									// Check if $product_campaign_id exists before using it to retrieve donation_recurring meta
									if ( $product_campaign_id ) {
										$donation_recurring = get_post_meta($product_campaign_id, 'wc-donation-recurring', true);

										 // Check if donation_recurring meta exists
										if ( '' !== $donation_recurring && 'disabled' == $donation_recurring ) {
											$donation_type = 'non_recurring';
										} else {
											$donation_type = 'recurring';
										}
									}

									$combinedCampaignIds[]= $product_campaign_id;
									$my_order->update_meta_data( 'wc_campaign_id', $combinedCampaignIds );
									
									WcDonationReports::add_report( $order_id, $item_id, $campaign_id, $donation_amount, $currency, $donation_type );
								}
							}
						}
					}
					
				}

			}
			
			$my_order->update_meta_data( 'wc_donation_report_generated', true );
			$my_order->save();
		}
	}

	public function wc_donation_attach_pdf_invoice_for_donation_on_new_order( $attachments, $email_id, $order, $email ) {

		// Avoiding errors and problems
		if ( ! is_a( $order, 'WC_Order' ) || ! isset( $email_id ) ) {
			return $attachments;
		}

		if ( 'yes' == get_option('wc-donation-pdf-receipt') ) {
			/**
			* Action.
			* 
			* @since 3.4.5
			*/
			$email_ids = apply_filters( 'wc_donation_email_attachments', array( 'new_order', 'customer_processing_order', 'customer_completed_renewal_order', 'customer_completed_order' ) );

			$flag = false;

			if ( ! empty( $order ) ) {
				foreach ( $order->get_items() as $item_id => $item ) { 
					
					$product_id = $item->get_product_id();

					$donation_type = get_post_meta($product_id, 'is_wc_donation', true);

					if ( ! empty($donation_type) && 'donation' == $donation_type ) {
						$flag = true;
						break;
					}
				}
			}

			if ( in_array ( $email_id, $email_ids ) && $flag ) {
				$pdf = new WcdonationPdf();
				$pdf_path = $pdf->wc_donation_pdf_receipt( $order, '', false );
				if ( !empty( $pdf_path ) ) {                    
					$attachments[] = $pdf_path;
				}
			}
		}

		return $attachments;
	}

	public function wc_donation_recurring_price( $include, $product ) {
		if ( ( !is_admin() && is_cart() ) || ( is_checkout() ) ) :
			$cart = WC()->cart;
			$product_id = $product->get_id();
			$is_donation_product = get_post_meta ( $product_id, 'is_wc_donation', true );
			foreach ( $cart->get_cart() as $key => $value ) {

				if ( isset($value['subscription_period']) && 'donation' === $is_donation_product && $product_id === $value['product_id'] ) {
					$include['subscription_period'] = $value['subscription_period'];
					$include['subscription_length'] = $value['subscription_length'];
					return $include;
				}
			}
		endif;
		return $include;
	}
	public function wc_donation_recurring_period( $include, $product ) {
		if ( ( !is_admin() && is_cart() ) || ( is_checkout() ) ) :
			$cart = WC()->cart;
			$product_id = $product->get_id();
			$is_donation_product = get_post_meta ( $product_id, 'is_wc_donation', true );
			foreach ( $cart->get_cart() as $key => $value ) {
				if ( isset($value['subscription_period']) && 'donation' === $is_donation_product && $product_id === $value['product_id'] ) {
					return $value['subscription_period'];
				}
			}
		endif;
		return $include;
	}
	public function wc_donation_recurring_interval( $include, $product ) {
		if ( ( !is_admin() && is_cart() ) || ( is_checkout() ) ) :
			$cart = WC()->cart;
			$product_id = $product->get_id();
			$is_donation_product = get_post_meta ( $product_id, 'is_wc_donation', true );
			foreach ( $cart->get_cart() as $key => $value ) {
				if ( isset($value['subscription_period_interval']) && 'donation' === $is_donation_product && $product_id === $value['product_id'] ) {
					return $value['subscription_period_interval'];
				}
			}
		endif;
		return $include;
	}
	public function wc_donation_recurring_length( $include, $product ) {
		if ( ( !is_admin() && is_cart() ) || ( is_checkout() ) ) :
			$cart = WC()->cart;
			$product_id = $product->get_id();
			$is_donation_product = get_post_meta ( $product_id, 'is_wc_donation', true );
			foreach ( $cart->get_cart() as $key => $value ) {
				if ( isset($value['subscription_length']) && 'donation' === $is_donation_product && $product_id === $value['product_id'] ) {
					return $value['subscription_length'];
				}
			}
		endif;
		return $include;
	}

	public function wc_donation_cart_item_price_filter( $price, $cart_item, $cart_item_key ) {
		if ( isset($cart_item['compaign']) && isset($cart_item['fees_percent']) && !empty($cart_item['fees_percent'] ) ) {
			$feesPercent = $cart_item['fees_percent'] / 100;
			$donationAmount = $cart_item['custom_price'];
			$summed = $donationAmount * $feesPercent;
			return wc_price($donationAmount + $summed);
		} elseif (isset($cart_item['compaign'])) {
			return wc_price( $cart_item['custom_price'] );
		} else {
			return $price;
		}
	}
	
	public function wc_donation_counts_on_subscription( $subscription, $last_order ) {
		//will start working here.

		$order_id = $last_order->id;
		if ( ! $order_id ) {
			return;
		}

		// Get an instance of the WC_Order object
		$order = wc_get_order( $order_id );

		// Loop through order items
		foreach ( $order->get_items() as $item_id => $item ) {

			// Get the product object
			$product = $item->get_product();

			// Get the product Id
			$product_id = $product->get_id();

			$total_donations = !empty(get_post_meta( $product_id, 'total_donations', true )) ? get_post_meta( $product_id, 'total_donations', true ) : 0;
			$total_donation_amount = !empty(get_post_meta( $product_id, 'total_donation_amount', true )) ? get_post_meta( $product_id, 'total_donation_amount', true ) : 0;
			$item_total = $item->get_total(); // Get the item line total discounted

			//increase the value by 1
			++$total_donations;

			// update to database in product meta
			/**
			* Action.
			* 
			* @since 3.4.5
			*/
			$total_donations = apply_filters ( 'wc_donation_total_donation_count_on_renewal', $total_donations, $product_id );
			update_post_meta( $product_id, 'total_donations', $total_donations);            

			//increase the value by 1
			$total_donation_amount += $item_total;

			// update to database in product meta
			/**
			* Action.
			* 
			* @since 3.4.5
			*/
			$total_donation_amount = apply_filters ( 'wc_donation_total_donation_amount_on_renewal', $total_donation_amount, $product_id );
			update_post_meta( $product_id, 'total_donation_amount', $total_donation_amount);

		}
	}
	
	//This function is deprecated as we have implemented another method for count
	public function wc_donation_amount_counts() {
		$args = array(
			'status' => array( 'wc-processing', 'wc-completed' ),
			'limit' => -1,
		);
		
		$orders = wc_get_orders($args);
		
		$total_donation_amount = array();
		$total_donations = array();
		$total_count = 0;
		$donation_amount_total = 0;
		$total_donors = 0;
		
		// Get an instance of the WC_Order object
		foreach ( $orders as $order ) {
			
			// Get the user ID from WC_Order methods
			$user_id = $order->get_user_id(); // or $order->get_customer_id();
			$is_donation_product = 'no';
			// Loop through order items
			foreach ( $order->get_items() as $item_id => $item ) {

				// Get the product object
				$product_id = $item->get_product_id();
				
				//Check if donation product
				
				$is_donation_product = get_post_meta ($product_id, 'is_wc_donation', true);
				if ( 'donation' === $is_donation_product ) {
					if ( 'yes' !== get_user_meta( $user_id, 'donation_product_' . $product_id, true ) ) {
						//increase the value by 1
						update_user_meta($user_id, 'donation_product_' . $product_id, 'yes');
						$total_donors++;
						update_post_meta( $product_id, 'total_donors', $total_donors);
					}
					$item_total = $item->get_total(); // Get the item line total discounted
					$total_count++;
					//increase the value by 1
					$total_donations[$product_id] = $total_count;
					//add the amount to total
					$donation_amount_total += $item_total; 
					$total_donation_amount[$product_id] = $donation_amount_total;
					// update to database in product meta
					/**
					* Action.
					* 
					* @since 3.4.5
					*/
					$total_donations[$product_id] = apply_filters ( 'wc_donation_total_donation_count', $total_donations[$product_id], $product_id );
					update_post_meta( $product_id, 'total_donations', $total_donations[$product_id]);
					// update to database in product meta
					/**
					* Action.
					* 
					* @since 3.4.5
					*/
					$total_donation_amount[$product_id] = apply_filters ( 'wc_donation_total_donation_amount', $total_donation_amount[$product_id], $product_id );
					update_post_meta( $product_id, 'total_donation_amount', $total_donation_amount[$product_id]);
				}
			}
		}
	}
	
	public function wc_donation_counts_on_place_order( $order_id ) {

		if ( ! $order_id ) {
			return;
		}

		$order = wc_get_order( $order_id );

		// Allow code execution only once
		if ( ! $order->get_meta( '_thankyou_action_done' , true ) ) { 

			$order_status  = $order->get_status(); // Get the order status

			// if order status is processing or completed.
			if ( in_array( $order_status, array( 'wc-processing', 'wc-completed' ) ) ) {

				// Get the user ID from WC_Order methods
				$user_id = $order->get_user_id(); // or $order->get_customer_id();
				

				// Loop through order items
				foreach ( $order->get_items() as $item_id => $item ) {

					// Get the product object
					$product = $item->get_product();
		
					// Get the product Id
					$product_id = $product->get_id();

					$is_donation_product = get_post_meta ($product_id, 'is_wc_donation', true);
					
					if ( 'donation' === $is_donation_product ) {

						if ( 'yes' !== get_user_meta( $user_id, 'donation_product_' . $product_id, true ) ) {

							$total_donors = ! empty( get_post_meta( $product_id, 'total_donors', true ) ) ? get_post_meta( $product_id, 'total_donors', true ) : 0;
							//increase the value by 1
							++$total_donors;
							update_post_meta( $product_id, 'total_donors', $total_donors);
							update_user_meta($user_id, 'donation_product_' . $product_id, 'yes');
						}

						$total_donations = ! empty( get_post_meta( $product_id, 'total_donations', true ) ) ? get_post_meta( $product_id, 'total_donations', true ) : 0;
						$total_donation_amount = ! empty( get_post_meta( $product_id, 'total_donation_amount', true ) ) ? get_post_meta( $product_id, 'total_donation_amount', true ) : 0;

						$item_total = $item->get_total(); // Get the item line total discounted

						//increase the value by 1
						++$total_donations;

						// update to database in product meta
						/**
						* Action.
						* 
						* @since 3.4.5
						*/
						$total_donations = apply_filters ( 'wc_donation_total_donation_count', $total_donations, $product_id );
						update_post_meta( $product_id, 'total_donations', $total_donations);

						//increase the value by 1
						$total_donation_amount += $item_total;

						// update to database in product meta
						/**
						* Action.
						* 
						* @since 3.4.5
						*/
						$total_donation_amount = apply_filters ( 'wc_donation_total_donation_amount', $total_donation_amount, $product_id );
						update_post_meta( $product_id, 'total_donation_amount', $total_donation_amount);
					}
				}
			}
		}
	}
	
	/**
	 * AddCartItemMetaToOrderItemMeta
	 *
	 * @param  mixed $item
	 * @param  mixed $item_key
	 * @param  mixed $item_values
	 * @return void
	 */
	public function addCartItemMetaToOrderItemMeta( $item, $item_key, $item_values ) {
		
		if ( isset($item_values['cause_name']) ) {
			$item->add_meta_data( 'cause_name', sanitize_text_field( $item_values['cause_name'] ) );
		}

		if ( isset($item_values['gift_aid']) ) {
			$item->add_meta_data( 'gift_aid', sanitize_text_field( $item_values['gift_aid'] ) );
		}

		if ( isset($item_values['tribute']) ) {
			$item->add_meta_data( 'tribute', sanitize_text_field( $item_values['tribute'] ) );
		}

		if ( isset($item_values['tribute_message']) ) {
			$item->add_meta_data( 'tribute_message', sanitize_text_field( $item_values['tribute_message'] ) );
		}

		if ( isset($item_values['fees_percent']) ) {
			$item->add_meta_data( 'fees_percent', sanitize_text_field( $item_values['fees_percent'] ) );
		}

		if ( isset($item_values['fee_type']) ) {
			$item->add_meta_data( 'fee_type', sanitize_text_field( $item_values['fee_type'] ) );
		}

		if ( isset($item_values['processing_fee']) ) {
			$item->add_meta_data( 'processing_fee', sanitize_text_field( $item_values['processing_fee'] ) );
		}

		if ( isset($item_values['compaign']) ) {
			$item->add_meta_data( 'compaign', sanitize_text_field( $item_values['compaign'] ) );
		}

		if ( isset($item_values['campaign_type']) ) {
			$item->add_meta_data( 'campaign_type', sanitize_text_field( $item_values['campaign_type'] ) );
		}

		if ( isset($item_values['campaign_id']) ) {
			$item->add_meta_data( 'campaign_id', sanitize_text_field( $item_values['campaign_id'] ) );
		}
		
		if ( isset($item_values['selectedLabel']) ) {
			$item->add_meta_data( 'selectedLabel', sanitize_text_field( $item_values['selectedLabel'] ) );
		}

		// $item->save();
	}

	public function changeOrderItemMetaTitle( $key, $meta, $item ) {
		/**
		* Action.
		* 
		* @since 3.4.5
		*/
		$key = apply_filters ( 'wc_donation_before_display_meta_key_on_order', $key, $meta, $item );
		if ( 'cause_name' === $meta->key ) {
			$key = __('Cause', 'wc-donation');
		}
		if ( 'gift_aid' === $meta->key ) {
			$key = __('Gift Aid', 'wc-donation');
		}
		if ( 'tribute' === $meta->key ) {
			$key = __('Tribute', 'wc-donation');
		}
		if ( 'tribute_message' === $meta->key ) {
			$key = __('Tribute Message', 'wc-donation');
		}
		if ( 'processing_fee' === $meta->key ) {
			$key = __('Processing Fees', 'wc-donation');
		}
		/**
		* Filter.
		* 
		* @since 3.9.6.1
		*/
		return apply_filters ( 'wc_donation_after_display_meta_key_on_order', $key, $meta, $item );
	}
	public function changeOrderItemMetaValue( $value, $meta, $item ) {
		/**
		* Action.
		* 
		* @since 3.4.5
		*/
		$value = apply_filters ( 'wc_donation_before_display_meta_value_on_order', $value, $meta, $item );
		return $value;
	}
	public function add_donation_causes_order_meta( $item, $cart_item_key, $values, $order ) {
		if ( !empty( $values['cause_name'] ) ) {
			$item->add_meta_data( 'Cause', $values['cause_name'] );
		}
		if ( !empty( $values['gift_aid'] ) ) {
			$item->add_meta_data( 'Gift Aid', $values['gift_aid'] );
		}
		if ( !empty( $values['tribute'] ) ) {
			$item->add_meta_data( 'Tribute', $values['tribute'] );
		}
		if ( !empty( $values['tribute_message'] ) ) {
			$item->add_meta_data( 'Tribute Message', $values['tribute_message'] );
		}
		if ( !empty( $values['processing_fee'] ) ) {
			$item->add_meta_data( 'Processing Fees', $values['processing_fee'] );
		}
	}
	public function wc_donation_custom_woocommerce_hidden_order_itemmeta( $item_meta ) {
		
		$item_meta[] = 'compaign';
		$item_meta[] = 'campaign_type';
		$item_meta[] = 'fees_percent';
		$item_meta[] = 'campaign_id';
		$item_meta[] = 'fee_type';
		/**
		* Action.
		* 
		* @since 3.4.5
		*/
		return apply_filters( 'wc_donation_hidden_order_itemmeta', $item_meta);
	}

	public function wc_donation_order_item_get_formatted_meta_data( $formatted_meta ) {
		
		$temp_metas = array();

		foreach ( $formatted_meta as $key => $meta ) {
			if ( isset( $meta->key ) && ! in_array( $meta->key, array( 'compaign', 'campaign_type', 'campaign_id', 'fees_percent' ) ) ) {
				$temp_metas[ $key ] = $meta;
			}

		}

		/**
		* Action.
		* 
		* @since 3.4.5
		*/
		return apply_filters( 'wc_donation_hidden_order_frontend_itemmeta', $temp_metas, $formatted_meta );
	}

	public function displayCartItemCompaignMeta( $item_data, $cart_item ) {

		if ( isset( $cart_item['cause_name'] ) && ! empty( $cart_item['cause_name'] ) && '' !== $cart_item['cause_name'] ) {
			$item_data[] = array(
				'key' => __( 'Cause', 'wc-donation' ),
				'value' => wc_clean( $cart_item['cause_name'] ),
				'display' => '',
			);
		}

		if ( isset( $cart_item['gift_aid'] ) && ! empty( $cart_item['gift_aid'] ) && '' !== $cart_item['gift_aid'] ) {
			$item_data[] = array(
				'key' => __( 'Gift Aid', 'wc-donation' ),
				'value' => wc_clean( $cart_item['gift_aid'] ),
				'display' => '',
			);
		}

		if ( isset( $cart_item['tribute'] ) && ! empty( $cart_item['tribute'] ) && '' !== $cart_item['tribute'] ) {
			$item_data[] = array(
				'key' => __( 'Tribute', 'wc-donation' ),
				'value' => wc_clean( $cart_item['tribute'] ),
				'display' => '',
			);
		}

		if ( isset( $cart_item['tribute_message'] ) && ! empty( $cart_item['tribute_message'] ) && '' !== $cart_item['tribute_message'] ) {
			$item_data[] = array(
				'key' => __( 'Tribute Message', 'wc-donation' ),
				'value' => wc_clean( $cart_item['tribute_message'] ),
				'display' => '',
			);
		}

		if ( isset( $cart_item['fees_percent'] ) && ! empty( $cart_item['fees_percent'] ) && '' !== $cart_item['fees_percent'] ) {
			$item_data[] = array(
				'key' => __( 'Processing Fees', 'wc-donation' ),
				'value' => wc_clean( $cart_item['processing_fee'] ),
				'display' => '',
			);
		}
		$donation_on_product = get_option( 'wc-donation-on-product' );
		$donation_enable_option = get_option( 'wc_donation_enable_option', true );
		if ( 'enable' === $donation_enable_option ) {
			if (is_array($donation_on_product)) {
				if ( ( is_cart() && in_array( 'cart', $donation_on_product ) ) || ( is_checkout() && in_array( 'checkout', $donation_on_product ) ) ) {
					$product_id = $cart_item['product_id'];
					// Get the campaign settings
					$settings = get_option( 'wc_donation_settings', array() );
					
					$campaigns = isset( $settings['campaign_ids'] ) ? $settings['campaign_ids'] : array();
					// Loop through campaigns to check if the current product ID is linked to a campaign
					foreach ( $campaigns as $campaign_id => $campaign_data ) {
						if ( in_array( $product_id, $campaign_data['product_ids'] ) ) {
							// Calculate the donation price based on donation amount
							$product = wc_get_product( $product_id );
							if ( $product ) {
								$donation_price = ( $product->get_price() ) * ( $campaign_data['donation_amount'] / 100 ); // Corrected donation calculation
								$donation_text = $campaign_data['donation_amount'] . '% for donation';
								$campaign_product = wc_get_product( $campaign_id );

								// Add the donation information to the item data
								$item_data[] = array(
									'name'  => 'Campaign Donation',
									'value' => '<p>' . esc_html( $campaign_product->get_name() ) . ' ' . esc_html__( 'Incl.', 'text-domain' ) . ' ' . esc_html( $donation_text ) . '</p>',
								);
							}
							break; // Stop the loop after finding the first match
						}
					}
				}
			}
		}
		// Ensure donation is enabled for the product and we are on either the "cart" or "checkout" page
		

		/**
		 * Action.
		 *
		 * @since 3.4.5
		 */
		return apply_filters( 'wc_donation_before_display_meta_on_cart', $item_data, $cart_item );
	}

	public function add_new_order_filter() {
		global $pagenow, $post_type;
		$filter_id = 'filter_compaign_type';
		if ( 'shop_order' === $post_type && 'edit.php' === $pagenow ) {
			if ( isset ( $_GET['filter_compaign_type'] ) ) {
				$current   = sanitize_text_field( $_GET['filter_compaign_type']  );
			} else {
				$current   = '';
			}
			echo '<select name="' . esc_attr( $filter_id ) . '">
			<option value="">' . esc_html( __( 'Filter by Campaign Name', 'wc-donation' ) ) . '</option>';
			$all_campaigns = get_posts(array(
				'fields'          => 'ids',
				'posts_per_page'  => -1,
				'post_type' => 'wc-donation',
			));
			foreach ( $all_campaigns as $campaign ) {
				printf(
					'<option value="%s" %s> %s </option>',
					esc_html( $campaign ),
					esc_attr( $campaign ) === $current ? '" selected="selected"' : '',
					esc_html( get_the_title( $campaign ) )
				);
			}
			echo '</select>';
		}
	}


	public function process_admin_shop_order_compaign_name_filter( $query ) {
		global $pagenow, $post_type, $wpdb;
		if (isset ( $_GET[ 'filter_compaign_type' ] )) { 
			if (
					$query->is_admin && 'edit.php' === $pagenow && 'shop_order' === $post_type && sanitize_text_field($_GET[ 'filter_compaign_type' ]) && '' !== sanitize_text_field($_GET[ 'filter_compaign_type' ])
			) {
				$order_ids = $wpdb->get_col(
					$wpdb->prepare(
						"
				SELECT DISTINCT o.ID
				FROM {$wpdb->prefix}posts o
				INNER JOIN {$wpdb->prefix}woocommerce_order_items oi
					ON oi.order_id = o.ID
				INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim
					ON oi.order_item_id = oim.order_item_id
				
				WHERE o.post_type = %s
				AND oim.meta_key = 'campaign_id'
				AND oim.meta_value = %s
				
			",
						$post_type,
						sanitize_text_field( $_GET[ 'filter_compaign_type' ] )
					)
				);

				if ( ! empty( $order_ids ) ) {
					$query->set( 'post__in', $order_ids );      // Set the new "meta query".
					$query->set( 'posts_per_page', 25 );        // Set "posts per page".
					$query->set( 'paged', ( get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1 ) );    // Set "paged".
				}
			}
		}
		return $query;
	}

	public function updateExistingCartItem( $cartItemId, $metaData ) {
		$cartItem = WC()->cart->cart_contents[$cartItemId];
		foreach ($metaData as $key => $value) {
			$cartItem[$key] = $value;
		}
		if ( isset($cartItem['fees_percent']) && !empty( $cartItem['fees_percent'] ) ) {
			$feesPercent = $cartItem['fees_percent']/100;
			$donationAmount = $cartItem['custom_price'];
			$summed = $donationAmount * $feesPercent;
			$newPrice = $donationAmount + $summed;
			$cartItem['data']->set_price( $newPrice );
		} else {
			$cartItem['data']->set_price($cartItem['custom_price']);
		}
		WC()->cart->cart_contents[$cartItemId] = $cartItem;
		WC()->cart->set_session();
	}

	public function getCartItemKeysIncludeProduct( $product_id ) {
		$cartItemsIds = array();        
		
		foreach (WC()->cart->get_cart() as $cart_item_key => $values) {

			$product = $values['data'];
			$wc_product_id = version_compare( WC_VERSION, '3.0', '<' ) ? $product->id : $product->get_id();
			
			if ( $product_id == $wc_product_id ) {
				
				$cartItemsIds[] = $cart_item_key;
			}
		}
		
		return $cartItemsIds;
	}

	public function wc_donation_alter_price_cart( $cart ) {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}
 
		if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 ) {
			return;
		}

		foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
			$product = $cart_item['data'];
			$price  = method_exists( $product, 'get_price' ) ? floatval( $product->get_price() ) : floatval( $product->price );
			if ( isset( $cart_item['custom_price'] ) && isset( $cart_item['fees_percent'] ) && !empty( $cart_item['fees_percent'] ) ) {
				if ( 'percentage' === $cart_item['fee_type'] ) {
					$feesPercent = $cart_item['fees_percent'] / 100;
					$donationAmount = $cart_item['custom_price'];
					$summed = $donationAmount * $feesPercent;
					$cart_item['data']->set_price( $price + $donationAmount + $summed);
				} else {
					$cart_item['data']->set_price( $price + $cart_item['custom_price'] + $cart_item['fees_percent'] );
				}
			} elseif ( isset( $cart_item['custom_price'] ) ) {
				$cart_item['data']->set_price( (float) $price + (float) $cart_item['custom_price']);
			}
		}
	} 

	public function add_donation_to_order_action() {
		
		if ( !isset( $_POST['nonce'] ) || ( isset( $_POST['nonce'] ) && !wp_verify_nonce(sanitize_text_field($_POST['nonce']), '_wcdnonce' ) ) ) {
			wp_die( esc_html__( 'Not Authorized', 'wc-donation' ) );
		}
		
		/**
		* Action.
		* 
		* @since 3.4.5
		*/
		do_action ('wc_donation_before_donate');
		
		if ( !isset($_POST['campaign_id']) || empty( sanitize_text_field($_POST['campaign_id']) ) ) {
			wp_die( esc_html__( 'No campaign ID found!', 'wc-donation' ) );
		}
		
		$setTimerDisp = !empty( get_post_meta ( sanitize_text_field($_POST['campaign_id']), 'wc-donation-setTimer-display-option', true  ) ) ? get_post_meta ( sanitize_text_field($_POST['campaign_id']), 'wc-donation-setTimer-display-option', true  ) : 'disabled';
		
		if ('enabled' == $setTimerDisp) {
			$timertype = get_post_meta(sanitize_text_field($_POST['campaign_id']), 'wc-donation-setTimer-time-type', true);
			$time = get_post_meta(sanitize_text_field($_POST['campaign_id']), 'wc-donation-setTimer-time', true);
			$startTime = null;

			if (!empty($timertype) && 'daily' == $timertype) {
				// Check if there is a 'daily' entry for start and end time
				if (!empty($time['daily']['end'])) {
					$startTime = $time['daily']['end'];
					$startTime = strtotime(gmdate('Y-m-d ') . $startTime);
				}
			}

			if (!empty($timertype) && 'specific_day' == $timertype) {
				// Check if there is a specific start and end time for today
				$current_day = strtolower(gmdate('D')); // Get current day abbreviation (e.g., Mon, Tue, etc.)
				if (!empty($time['specific_day'][$current_day]['end'])) {
					$startTime = $time['specific_day'][$current_day]['end'];
					$startTime = strtotime(gmdate('Y-m-d ') . $startTime);
				}
			}
			
			if ($startTime) {
				// Get the current time in the format "H:i"
				$current_time = current_time('timestamp');
				// Compare the current time with the start time
				if ($current_time >= $startTime) {
					$response['response'] = 'error';
					/**
					* Filter.
					* 
					* @since 3.8
					*/
					echo json_encode( apply_filters('wc_donation_alter_donate_response', $response ) );
					wp_die();
				}
			}
		}

		$object = WcdonationCampaignSetting::get_product_by_campaign( sanitize_text_field($_POST['campaign_id']) );   
		$isDone = false;
		$product_id = $object->product['product_id'];
		if ( class_exists('Subscriptions_For_Woocommerce') && ! class_exists('WC_Subscriptions') ) {
			$wps_subscription_product = wps_sfw_get_meta_data( $product_id, '_wps_sfw_users', true );  
		}
		$response['response'] = 'failed';
		$response['campaign_id'] = isset( $_POST['campaign_id'] ) ? sanitize_text_field($_POST['campaign_id']) : 0;
		$RecurringDisp = $object->campaign['RecurringDisp'];
		$is_recurring = isset($_POST['is_recurring']) ? sanitize_text_field($_POST['is_recurring']) : 'no';
		$new_period = isset($_POST['new_period']) ? sanitize_text_field($_POST['new_period']) : 'day';
		$new_length = isset($_POST['new_length']) ? sanitize_text_field($_POST['new_length']) : '1';
		$new_interval = isset($_POST['new_interval']) ? sanitize_text_field($_POST['new_interval']) : '1';
		$gift_aid = isset($_POST['gift_aid']) ? sanitize_text_field($_POST['gift_aid']) : '';
		$tribute = isset($_POST['tribute']) ? sanitize_text_field($_POST['tribute']) : '';
		$tribute_message = isset($_POST['tribute_message']) ? sanitize_text_field($_POST['tribute_message']) : '';
		$fee_type = ! empty( get_option( 'wc-donation-card-fee' ) ) && 'yes' === get_option('wc-donation-card-fee') && isset( $_POST['fees'] ) && ! empty( sanitize_text_field( $_POST['fees'] ) ) ? get_option('wc-donation-fees-type') : '';
		$wps_sfw_subscription_number = isset($_POST['wps_sfw_subscription_number']) ? sanitize_text_field($_POST['wps_sfw_subscription_number']) : '';
		$wps_sfw_subscription_interval = isset($_POST['wps_sfw_subscription_interval']) ? sanitize_text_field($_POST['wps_sfw_subscription_interval']) : '';
		$wps_sfw_subscription_expiry_number = isset($_POST['wps_sfw_subscription_expiry_number']) ? sanitize_text_field($_POST['wps_sfw_subscription_expiry_number']) : '';
		$wps_sfw_subscription_expiry_interval = isset($_POST['wps_sfw_subscription_expiry_interval']) ? sanitize_text_field($_POST['wps_sfw_subscription_expiry_interval']) : '';


		if ( isset( $_POST['cc_processing_fee'] ) && 'require' == sanitize_text_field($_POST['cc_processing_fee']) ) {
			/**
			* Filter.
			* 
			* @since 3.6.3
			*/
			$response['message'] = apply_filters( 'cc_fees_message', __('Credit Card fee must be checked!', 'wc-donation') );
			/**
			* Filter.
			* 
			* @since 3.6.3
			*/
			echo ( json_encode( apply_filters ( 'wc_donation_alter_donate_response', $response ) ) );
			wp_die();
		}

		if (!empty($_POST['amount'])) {

			// check if recurring type is user
			
			$cart_item_data = array(
				'custom_price' => sanitize_text_field( $_POST['amount'] ),
				'campaign_id' => sanitize_text_field($_POST['campaign_id']),
				'cause_name' => '',
				'fees_percent' => '',
				'processing_fee' => '',
				'fee_type' => '',
				'gift_aid' => '',
				'tribute' => '',
				'tribute_message' => '',
			);
			
			if ( 'user' == $RecurringDisp ) {
				
				if ( 'yes' == $is_recurring ) {
					$cart_item_data['billing_interval'] = $new_interval;
					$cart_item_data['billing_period'] = $new_period;
					$cart_item_data['subscription_period_interval'] = $new_interval;
					$cart_item_data['subscription_period'] = $new_period;
					$cart_item_data['subscription_length'] = $new_length;
					$cart_item_data['_subscription_period_interval'] = $new_interval;
					$cart_item_data['_subscription_period'] = $new_period;
					$cart_item_data['_subscription_length'] = $new_length;


					if ( isset($wps_subscription_product) && 'user' === $wps_subscription_product ) {
						$cart_item_data['wps_sfw_subscription_number'] = $wps_sfw_subscription_number;
						$cart_item_data['wps_sfw_subscription_interval'] = $wps_sfw_subscription_interval;
						$cart_item_data['wps_sfw_subscription_expiry_number'] = $wps_sfw_subscription_expiry_number;
						$cart_item_data['wps_sfw_subscription_expiry_interval'] = $wps_sfw_subscription_expiry_interval;
						$cart_item_data['wps_sfw_subscription_is_recurring'] = $is_recurring;
						$this->update_sub_cart( $cart_item_data );  
					}


				} else { // pass this donation as one time donation
					$cart_item_data['billing_interval'] = '1';
					$cart_item_data['billing_period'] = 'day';
					$cart_item_data['subscription_period_interval'] = '1';
					$cart_item_data['subscription_period'] = 'day';
					$cart_item_data['subscription_length'] = '1';
					$cart_item_data['_subscription_period_interval'] = '1';
					$cart_item_data['_subscription_period'] = 'day';
					$cart_item_data['_subscription_length'] = '1';
				}

				
		

			}
			
			if ( isset($_POST['cause']) && !empty( $_POST['cause'] ) && '' !== $_POST['cause']) {
				$cart_item_data['cause_name'] = sanitize_text_field( $_POST['cause'] );
			}

			if ( !empty( $gift_aid ) && 'yes' == $gift_aid ) {
				$cart_item_data['gift_aid'] = sanitize_text_field( $gift_aid );
			}

			if ( !empty( $tribute ) && '' !== $tribute ) {
				$cart_item_data['tribute'] = sanitize_text_field( $tribute );
			}

			if ( !empty( $tribute_message ) && '' !== $tribute_message ) {
				$cart_item_data['tribute_message'] = sanitize_text_field( $tribute_message );
			}

			if ( !empty( $fee_type ) && '' !== $fee_type  ) {
				$cart_item_data['fee_type'] = sanitize_text_field( $fee_type );
			}

			if ( isset($_POST['fees']) && !empty( $_POST['fees'] ) && '' !== $_POST['fees']) {
				if ( 'percentage' === $fee_type ) {
					$summed = sanitize_text_field( $_POST['amount'] ) * sanitize_text_field($_POST['fees']) / 100;
					$cart_item_data['fees_percent'] = sanitize_text_field( $_POST['fees'] );
					$cart_item_data['processing_fee'] = wc_price( $summed ) . ' (' . sanitize_text_field($_POST['fees']) . '%)';
				} else {
					$cart_item_data['fees_percent'] = sanitize_text_field( $_POST['fees'] );
					$cart_item_data['processing_fee'] = wc_price( sanitize_text_field( $_POST['fees'] ) );
				}
			}
			if ( isset($object->campaign['campaign_name']) ) {
				$cart_item_data['compaign'] = sanitize_text_field( $object->campaign['campaign_name'] );
				$cart_item_data['campaign_type'] = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';
			}

			$cartItems = $this->getCartItemKeysIncludeProduct( $product_id );
			if (!empty($_POST['selectedLabel'])) {
				$cart_item_data['selectedLabel'] = sanitize_text_field( $_POST['selectedLabel'] );
			}
			
			if (empty($cartItems)) {
				WC()->cart->add_to_cart($product_id, 1, 0, array(), $cart_item_data);
				$isDone = true;
			} else {
				$cart = WC()->cart->get_cart();
				foreach ($cartItems as $cartItemKey) {
					if ( !$isDone && $cart[$cartItemKey]['compaign'] && sanitize_text_field( $object->campaign['campaign_name'] == $cart[$cartItemKey]['compaign'] ) ) {
						$this->updateExistingCartItem($cartItemKey, $cart_item_data);
						$isDone = true;
					} elseif ( !$isDone && !$cart[$cartItemKey]['compaign'] && !isset($object->campaign['campaign_name']) ) {
						$this->updateExistingCartItem($cartItemKey, $cart_item_data);
						$isDone = true;
					}
				}
				if ( !$isDone ) {
					WC()->cart->add_to_cart( $product_id, 1, 0, array(), $cart_item_data );
				}
			}
			if ( 'page' == get_option('wc-donation-campaign-display-type') ) {
				wc_add_notice(__( 'Donation Added to Cart.', 'wc-donation' ), 'success');
			}

			$response['response'] = 'success';

			if ( isset( $_POST['type'] ) && 'cart' == $_POST['type'] ) {
				$response['cart_url'] = wc_get_cart_url();
			} else {
				$response['cart_url'] = '';
			}

			if ( ! is_checkout() && isset($_POST['type']) && ( 'widget'===$_POST['type'] || 'shortcode'===$_POST['type'] || 'single'===$_POST['type'] ) ) {

				$response['checkoutUrl'] = wc_get_checkout_url();
	
			} else {
	
				$response['checkoutUrl'] = '';
	
			}

		}

		/**
		* Filter.
		* 
		* @since 3.4.5
		*/
		do_action ('wc_donation_after_donate');

		/**
		* Filter.
		* 
		* @since 3.4.5
		*/
		echo ( json_encode( apply_filters ( 'wc_donation_alter_donate_response', $response ) ) );
		wp_die();
	}

	public function woocommerce_checkout_order_processed( $order_id ) {

		if ( is_object( $order_id ) ) {
			$order = $order_id;
		} else {
			$order = wc_get_order( $order_id );
		}

		foreach ( $order->get_items() as $item ) {
			$product_id = $item->get_product_id();
			$campaign_id = get_post_meta( $product_id, 'wc_donation_campaign', true );
			
			if ( empty( $campaign_id ) ) {
				$settings = get_option( 'wc_donation_settings', array() );
				$campaign_settings = isset( $settings['campaign_ids'] ) ? $settings['campaign_ids'] : array();

				$total_donation = 0;
				$donation_enable_option = get_option( 'wc_donation_enable_option', true );
				if ( 'enable' === $donation_enable_option ) {
					foreach ( $campaign_settings as $campaign_id => $campaign ) {
						if ( in_array( $product_id, $campaign['product_ids'] ) ) {
							$product_total = $item->get_total();
							$quantity = $item->get_quantity();
							$campaign_post = get_post( $campaign_id ); // Fetch campaign post object by ID

							// Ensure the post exists and has a valid title (campaign name)
							$campaign_name = ( $campaign_post && isset( $campaign_post->post_title ) ) ? $campaign_post->post_title : 'N/A';
							
							// Get the item ID for the current item
							$item_id = $item->get_id();
							$donation_percentage = isset( $campaign['donation_amount'] ) ? $campaign['donation_amount'] : 0;
							$donation_amount = ( $donation_percentage / 100 ) * $product_total;
							$total_donations = (int) get_post_meta( $campaign_id, 'total_donations', true );
							$total_donation_amount = (float) get_post_meta( $campaign_id, 'total_donation_amount', true );

							$total_donations = ++$total_donations;
							update_post_meta( $campaign_id, 'total_donations', $total_donations);

							$total_donors = get_post_meta( $campaign_id, 'total_donors', true );
							$total_donors++;
							update_post_meta( $campaign_id, 'total_donors', $total_donors);

							$total_donation_amount = $total_donation_amount + $donation_amount;
							update_post_meta( $campaign_id, 'total_donation_amount', $total_donation_amount );

							// Calculate new product price after subtracting the donation
							$new_product_total = $product_total - $donation_amount;
							$new_product_price_per_unit = $new_product_total / $quantity;

							// Save campaign data as item meta
							$item->add_meta_data( 'campaign_donation_amount', $donation_amount );
							$item->add_meta_data( 'campaign_donation_percentage(%)', $donation_percentage );
							$item->add_meta_data( 'campaign_name', $campaign_name );
							$item->add_meta_data( 'campaign_applied', 'yes' );

							// Update the product line total to reflect the adjusted price
							$item->set_total( $new_product_price_per_unit * $quantity );
							$item->set_subtotal( $new_product_price_per_unit * $quantity );

							$total_donation += $donation_amount;

							break;
						}
					}

					// Save total donation for the order if applicable
					if ( $total_donation > 0 ) {
						$order->update_meta_data( '_total_campaign_donation', $total_donation );
					}
				}
				

				continue;
			}

			// Tribute handling remains unchanged
			$tribute_name = '';
			$tribute_posted_name = '';
			$tribute_message_data = array();

			$posted_tribute = $item->get_meta('tribute');
			$tribute_message = $item->get_meta('tribute_message');

			$tribute_message_data['message'] = $tribute_message;
			$tribute_message_data['campaign_id'] = $campaign_id;
			$tribute_message_data['user_id'] = $order->get_customer_id();

			$tributes = get_post_meta( $campaign_id, 'tributes', true );

			if ( ! empty( $tributes ) && is_array( $tributes ) ) {
				foreach ( $tributes as $tribute ) {
					if ( str_contains( $posted_tribute, trim( $tribute ) ) ) {
						$tribute_posted_name = trim( str_replace( trim( $tribute ), '', $posted_tribute ) );
						$tribute_name = trim( str_replace( $tribute_posted_name, '', $posted_tribute ) );
					}
				}
			}

			$tribute_message_data['tribute'] = $tribute_name;
			$tribute_message_data['tribute_name'] = $tribute_posted_name;
			$tribute_message_data['timestamp'] = current_time( 'timestamp' );

			$tribute_wall = get_post_meta( $campaign_id, 'tribute_wall', true );

			if ( empty( $tribute_wall ) ) {
				$tribute_data = array( $tribute_message_data );
			} else {
				$tribute_wall[] = $tribute_message_data;
				$tribute_data = $tribute_wall;
			}

			update_post_meta( $campaign_id, 'tribute_wall', $tribute_data );
		}

		$order->save(); // Ensure order updates are saved
	}
}

new WcDonationOrder();
