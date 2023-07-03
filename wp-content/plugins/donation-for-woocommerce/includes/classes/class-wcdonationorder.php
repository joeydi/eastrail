<?php

class WcDonationOrder {

	public function __construct() {

		add_filter( 'restrict_manage_posts', array( $this, 'add_new_order_filter' ), 10, 0 );
		add_action( 'pre_get_posts', array( $this, 'process_admin_shop_order_compaign_name_filter' ), 10, 1 );		
		add_action( 'wp_ajax_donation_to_order', array( $this, 'add_donation_to_order_action' ), 10 );
		add_action( 'wp_ajax_nopriv_donation_to_order', array( $this, 'add_donation_to_order_action' ), 10 );
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'wc_donation_alter_price_cart' ), 9999 );
		add_filter( 'woocommerce_cart_item_price', array( $this, 'wc_donation_cart_item_price_filter' ), 99, 3 );
		add_action('woocommerce_checkout_create_order_line_item', array( $this, 'addCartItemMetaToOrderItemMeta' ), 10, 3);
		// add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'add_donation_causes_order_meta' ), 10, 4 );
		add_filter('woocommerce_order_item_display_meta_key', array( $this, 'changeOrderItemMetaTitle' ), 20, 3 );
		add_filter('woocommerce_order_item_display_meta_value', array( $this, 'changeOrderItemMetaValue' ), 20, 3 );
		add_filter('woocommerce_hidden_order_itemmeta', array( $this, 'wc_donation_custom_woocommerce_hidden_order_itemmeta' ), 10, 1 );
		add_filter('woocommerce_order_item_get_formatted_meta_data', array( $this, 'wc_donation_order_item_get_formatted_meta_data' ), 10, 1 );
		
		add_filter('woocommerce_get_item_data', array( $this, 'displayCartItemCompaignMeta' ), 10, 2);

		add_action('woocommerce_thankyou', array( $this, 'wc_donation_counts_on_place_order' ), 10);
		add_action('woocommerce_order_status_changed', array( $this, 'wc_donation_order_status_changed' ), 10, 4);		
		add_action('woocommerce_subscription_renewal_payment_complete', array( $this, 'wc_donation_counts_on_subscription' ), 10, 2);		

		add_filter('woocommerce_subscriptions_product_price_string_inclusions', array($this, 'wc_donation_recurring_price'), 10, 2 );
		add_filter('woocommerce_subscriptions_product_period', array($this, 'wc_donation_recurring_period'), 10, 2 );
		add_filter('woocommerce_subscriptions_product_period_interval', array($this, 'wc_donation_recurring_interval'), 10, 2 );
		add_filter('woocommerce_subscriptions_product_length', array($this, 'wc_donation_recurring_length'), 10, 2 );

		add_filter( 'woocommerce_email_attachments', array( $this, 'wc_donation_attach_pdf_invoice_for_donation_on_new_order' ), 999, 4 );

		add_action('woocommerce_thankyou', array( $this, 'wc_donation_create_report' ), 10, 1);
		add_action('woocommerce_subscription_renewal_payment_complete', array( $this, 'wc_donation_create_report_on_subscription_renewal' ), 10, 2);

		add_action( 'wp_ajax_wc_donation_reset_data', array( $this, 'wc_donation_reset_data' ) );

	}

	public function wc_donation_reset_data() {

		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'donation_reset_nonce' ) ) {
			exit('Unauthorized');
		}

		if ( isset( $_POST['campaign_id'] ) ) {

			return self::reset_donation_campagin_data( sanitize_text_field($_POST['campaign_id']) );
		}

		wp_die();
	}

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
				'fields'       => array( 'id', 'display_name' )
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
		$order_status_to_check = apply_filters( 'wc_donation_order_status_changed_to', array('cancelled') );
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

	public function wc_donation_create_report_on_subscription_renewal ( $subscription, $last_order ) {
		
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

				WcDonationReports::add_report( $order_id, $item_id, $product_id, $donation_amount, $currency );
			}

		}

	}

	public function wc_donation_create_report ( $order_id ) {
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

				$is_donation_product = get_post_meta ($product_id, 'is_wc_donation', true);
				
				if ( ! empty( $is_donation_product ) && 'donation' === $is_donation_product ) {
					$donation_amount = $item->get_total();

					WcDonationReports::add_report( $order_id, $item_id, $product_id, $donation_amount, $currency );
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

	public function wc_donation_cart_item_price_filter ( $price, $cart_item, $cart_item_key ) {
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
	
	public function wc_donation_counts_on_subscription ( $subscription, $last_order ) {
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
	public function wc_donation_amount_counts () {
		$args = array(
			'status' => array( 'wc-processing', 'wc-completed' ),
			'limit' => -1
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
	
	public function wc_donation_counts_on_place_order ( $order_id ) {

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

		if ( isset($item_values['fees_percent']) ) {
			$item->add_meta_data( 'fees_percent', sanitize_text_field( $item_values['fees_percent'] ) );
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
			$key = 'Cause';
		}
		if ( 'gift_aid' === $meta->key ) {
			$key = 'Gift Aid';
		}
		if ( 'tribute' === $meta->key ) {
			$key = 'Tribute';
		}
		if ( 'processing_fee' === $meta->key ) {
			$key = 'Processing Fees';
		}
		return $key;
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
	public function add_donation_causes_order_meta ( $item, $cart_item_key, $values, $order ) {
		if ( !empty( $values['cause_name'] ) ) {
			$item->add_meta_data( 'Cause', $values['cause_name'] );
		}
		if ( !empty( $values['gift_aid'] ) ) {
			$item->add_meta_data( 'Gift Aid', $values['gift_aid'] );
		}
		if ( !empty( $values['tribute'] ) ) {
			$item->add_meta_data( 'Tribute', $values['tribute'] );
		}
		if ( !empty( $values['processing_fee'] ) ) {
			$item->add_meta_data( 'Processing Fees', $values['processing_fee'] );
		}
	}
	public function wc_donation_custom_woocommerce_hidden_order_itemmeta ( $item_meta ) {
		
		$item_meta[] = 'compaign';
		$item_meta[] = 'campaign_type';
		$item_meta[] = 'fees_percent';
		$item_meta[] = 'campaign_id';
		/**
		* Action.
		* 
		* @since 3.4.5
		*/
		return apply_filters( 'wc_donation_hidden_order_itemmeta', $item_meta);
	}

	public function wc_donation_order_item_get_formatted_meta_data ( $formatted_meta ) {
		
		$temp_metas = array();

		foreach ( $formatted_meta as $key => $meta ) {
			if ( isset( $meta->key ) && ! in_array( $meta->key, array ( 'compaign', 'campaign_type', 'campaign_id', 'fees_percent' ) ) ) {
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

		if ( isset( $cart_item['cause_name'] ) && !empty( $cart_item['cause_name'] ) && '' !== $cart_item['cause_name']) {
		
			$item_data[] = array(
				'key' => __('Cause', 'wc-donation'),
				'value' => wc_clean( $cart_item['cause_name'] ),
				'display' => '',
			);
		}

		if ( isset( $cart_item['gift_aid'] ) && !empty( $cart_item['gift_aid'] ) && '' !== $cart_item['gift_aid']) {
		
			$item_data[] = array(
				'key' => __('Gift Aid', 'wc-donation'),
				'value' => wc_clean( $cart_item['gift_aid'] ),
				'display' => '',
			);
		}

		if ( isset( $cart_item['tribute'] ) && !empty( $cart_item['tribute'] ) && '' !== $cart_item['tribute']) {
		
			$item_data[] = array(
				'key' => __('Tribute', 'wc-donation'),
				'value' => wc_clean( $cart_item['tribute'] ),
				'display' => '',
			);
		}

		if ( isset( $cart_item['fees_percent'] ) && !empty( $cart_item['fees_percent'] ) && '' !== $cart_item['fees_percent']) {
			$item_data[] = array(
				'key' => __('Processing Fees', 'wc-donation'),
				'value' => wc_clean( $cart_item['processing_fee'] ),
				'display' => '',
			);
		}
			
		//return $item_data;

		/**
		* Action.
		* 
		* @since 3.4.5
		*/
		return apply_filters ( 'wc_donation_before_display_meta_on_cart', $item_data, $cart_item );
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
				'post_type' => 'wc-donation'
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

	public function wc_donation_alter_price_cart ( $cart ) {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}
 
		if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 ) {
			return;
		}
	
		foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
			$product = $cart_item['data'];
			$price = $product->get_price();
			if ( isset( $cart_item['custom_price'] ) && isset( $cart_item['fees_percent'] ) && !empty( $cart_item['fees_percent'] ) ) {
				$feesPercent = $cart_item['fees_percent'] / 100;
				$donationAmount = $cart_item['custom_price'];
				$summed = $donationAmount * $feesPercent;
				$cart_item['data']->set_price( $price + $donationAmount + $summed);
				// print_r( $price + $donationAmount + $summed);
				// die();
			} elseif ( isset( $cart_item['custom_price'] ) ) {
				$cart_item['data']->set_price( $price + $cart_item['custom_price']);
			}
		}
	} 

	public function add_donation_to_order_action() {

		if ( !isset( $_POST['nonce'] ) || ( isset( $_POST['nonce'] ) && !wp_verify_nonce(sanitize_text_field($_POST['nonce']), '_wcdnonce' ) ) ) {
			wp_die( 'Not Authorized' );
		}
		
		/**
		* Action.
		* 
		* @since 3.4.5
		*/
		do_action ('wc_donation_before_donate');
		
		if ( !isset($_POST['campaign_id']) || empty( sanitize_text_field($_POST['campaign_id']) ) ) {
			wp_die('No campaign ID found!');
		}

		$object = WcdonationCampaignSetting::get_product_by_campaign( sanitize_text_field($_POST['campaign_id']) );		
		$isDone = false;
		$product_id = $object->product['product_id'];
		$response['response'] = 'failed';
		$response['campaign_id'] = isset( $_POST['campaign_id'] ) ? sanitize_text_field($_POST['campaign_id']) : 0;
		$RecurringDisp = $object->campaign['RecurringDisp'];
		$is_recurring = isset($_POST['is_recurring']) ? sanitize_text_field($_POST['is_recurring']) : 'no';
		$new_period = isset($_POST['new_period']) ? sanitize_text_field($_POST['new_period']) : 'day';
		$new_length = isset($_POST['new_length']) ? sanitize_text_field($_POST['new_length']) : '1';
		$new_interval = isset($_POST['new_interval']) ? sanitize_text_field($_POST['new_interval']) : '1';
		$gift_aid = isset($_POST['gift_aid']) ? sanitize_text_field($_POST['gift_aid']) : '';
		$tribute = isset($_POST['tribute']) ? sanitize_text_field($_POST['tribute']) : '';

		if (!empty($_POST['amount'])) {

			// check if recurring type is user
			
			$cart_item_data = array(
				'custom_price' => sanitize_text_field( $_POST['amount'] ),
				'campaign_id' => sanitize_text_field($_POST['campaign_id']),
				'cause_name' => '',
				'fees_percent' => '',
				'processing_fee' => '',
				'gift_aid' => '',
				'tribute' => ''
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

			if ( isset($_POST['fees']) && !empty( $_POST['fees'] ) && '' !== $_POST['fees']) {
				$summed = sanitize_text_field( $_POST['amount'] ) * sanitize_text_field($_POST['fees']) / 100;
				$cart_item_data['fees_percent'] = sanitize_text_field( $_POST['fees'] );
				$cart_item_data['processing_fee'] = wc_price( $summed ) . ' (' . sanitize_text_field($_POST['fees']) . '%)';
			}
			if ( isset($object->campaign['campaign_name']) ) {
				$cart_item_data['compaign'] = sanitize_text_field( $object->campaign['campaign_name'] );
				$cart_item_data['campaign_type'] = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';
			}

			$cartItems = $this->getCartItemKeysIncludeProduct( $product_id );

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
			$response['response'] = 'success';

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
}

new WcDonationOrder();
