<?php
/**
 * File to  define settings for each campaign.
 *
 * @package donation
 */

/**
 *  Class   WcdonationCampaignSetting.
 *  Add plugin settings .
 */
class WcdonationCampaignSetting {

	/**
	 * Campaign ID .
	 *
	 * @var type
	 */
	private $campaign_id;

	/**
	 * Constructor function .
	 */
	public function __construct() {

		$this->campaign_id = isset( $_REQUEST['post'] ) ? sanitize_text_field($_REQUEST['post']) : '';

		// adding metabox for dispaly shop/single page setting.
		add_action( 'add_meta_boxes', array( $this, 'wc_donation_meta' ) );

		// saving details for each campaign on save post and update post.
		add_action( 'save_post', array( $this, 'wc_donation_save_campaigns_details' ), 99, 3 );

		add_action( 'before_delete_post', array( $this, 'wc_donation_delete_campaign_from_db' ), 99, 2 );

		add_action( 'init', array( $this, 'register_render_wc_donation_campaign_shortcode' ) );

		//add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
		add_action( 'wp_enqueue_scripts', array( $this, 'woocommerce_simple_add_to_cart_remove' ), 29 );
		add_action( 'template_redirect', array( $this, 'woocommerce_simple_add_to_cart_remove' ), 29 );

		//add_action('wp_enqueue_scripts', array( $this, 'remove_loop_button') );
		add_filter('woocommerce_loop_add_to_cart_link', array( $this, 'remove_loop_button' ), 99, 3);                

		add_action ( 'template_redirect', array( $this, 'no_ways' ), 999 );

		add_action( 'init', array( $this, 'wc_donation_register_gutenberg_blocks' ) );

		//remove donation price from archive page
		add_filter( 'woocommerce_get_price_html', array( $this, 'wc_donation_product_get_price_html' ), 99, 2 );

		//add donation goal progress bar on archive page
		//add_action( 'wc_donation_before_archive_add_donation_button', array( $this, 'wc_donation_goal_progress_on_shop' ), 10, 1 );
		add_action( 'woocommerce_after_shop_loop_item', array( $this, 'wc_donation_goal_progress_on_shop' ), 10, 1 );

		add_action( 'init', array( $this, 'wc_donation_summary_register_gutenberg_blocks' ) );

		add_action( 'init', array( $this, 'wc_donation_tabs_register_gutenberg_blocks' ) );

		add_action( 'pre_get_posts', array( $this, 'exclude_specific_doantion_product_from_shop' ) );     

		add_action('woocommerce_blocks_loaded', array( $this, 'woocommerce_cart_checkout_api_request' ) );  
	
		add_filter( 'wc_conditional_fee_cart_subtotal', array( $this, 'wc_conditional_fee_remove_donation_product_amount' ), 10, 2 );
		add_action('woocommerce_before_add_to_cart_button', array( $this, 'add_before_product_to_cart' ), 22);
	}
	public function add_before_product_to_cart() {
		// Get current product ID
		global $product;
		$current_product_id = $product->get_id();

		// Get the campaign settings
		$settings = get_option('wc_donation_settings', array());
		$donation_on_product = get_option('wc-donation-on-product', array());
		$campaigns = isset($settings['campaign_ids']) ? $settings['campaign_ids'] : array();
		$donation_enable_option = get_option( 'wc_donation_enable_option', true );
		if ( 'enable' === $donation_enable_option ) {
			if (is_array($donation_on_product) && in_array('single', $donation_on_product)) {
				// Loop through campaigns to check if current product ID exists
				foreach ($campaigns as $campaign_id => $campaign_data) {
					if (in_array($current_product_id, $campaign_data['product_ids'])) {
						// Calculate the donation price based on donation amount
						$donation_price = ( $product->get_price() ) / ( $campaign_data['donation_amount'] );
						// Display the campaign as a child product
						$this->display_campaign_as_child_product($campaign_id, $campaign_data['donation_amount'], $donation_price);
						break; // Stop the loop after showing the first match
					}
				}
			}
		}
	}

	private function display_campaign_as_child_product( $campaign_id, $donation_amount, $donation_price ) {
		$campaign_product = wc_get_product($campaign_id);

		if ($campaign_product) {
			echo '<div class="wc-donation-child-product">';
			echo '<span>' . esc_html($campaign_product->get_name()) . ' Incl. ' . esc_html($donation_amount) . '% for donation<span>';
			echo '</div>';
		}
	}
	public function woocommerce_cart_checkout_api_request() {

		woocommerce_store_api_register_update_callback( array(
			'namespace' => 'dfw',
			'callback'  => function ( $data ) {},
		) );
	}

	public function wc_donation_tabs_register_gutenberg_blocks() {
		$args           = array(
			'numberposts' => -1,
			'post_type'   => 'wc-donation',
		);
		$all_campaigns  = get_posts( $args );
		$donation_forms = array();
		$count          = 0;
		foreach ( $all_campaigns as $campaign ) {
			$donation_forms[$count]['ID']    = $campaign->ID;
			$donation_forms[$count]['title'] = $campaign->post_title;
			++$count;
		}       

		wp_register_script( 'wc-donation-tabs-block', WC_DONATION_URL . 'assets/js/gutenberg_tabs_block/build/index.js', array( 'wp-components', 'wp-blocks', 'wp-element', 'wp-editor' ), WC_DONATION_VERSION . '&t=' . gmdate('YmdHis') );

		
		$wc_donation_forms = array(
			'campaigns' => $donation_forms,
		);

		wp_localize_script( 'wc-donation-tabs-block', 'wc_donation_tabs', $wc_donation_forms );
		
		register_block_type( 'wc-donation/donation-tabs', array(
			'editor_script' => 'wc-donation-tabs-block',
			'render_callback' => array( $this, 'wc_donation_tabs_block_render_html' ),
		));
	}

	public function wc_donation_tabs_block_render_html( $attributes ) {

		$id1    = isset( $attributes['id1'] ) ? $attributes['id1'] : '';
		$id2    = isset( $attributes['id2'] ) ? $attributes['id2'] : '';
		$title1 = isset( $attributes['title1'] ) ? $attributes['title1'] : '';
		$title2 = isset( $attributes['title2'] ) ? $attributes['title2'] : '';      
		return '[wc_woo_donation_tabs ids="' . esc_attr($id1) . ',' . esc_attr($id2) . '" titles="' . esc_attr($title1) . ',' . esc_attr($title2) . '"]';
	}

	public function wc_donation_summary_register_gutenberg_blocks() {
		$args           = array(
			'numberposts' => -1,
			'post_type'   => 'wc-donation',
		);
		$all_campaigns  = get_posts( $args );
		$donation_forms = array();
		$count          = 0;
		foreach ( $all_campaigns as $campaign ) {
			$donation_forms[$count]['ID']    = $campaign->ID;
			$donation_forms[$count]['title'] = $campaign->post_title;
			++$count;
		}       

		wp_register_script( 'wc-donation-goal-summary-block', WC_DONATION_URL . 'assets/js/gutenberg_goal_summary_block/build/index.js', array( 'wp-blocks', 'wp-element', 'wp-editor' ), WC_DONATION_VERSION . '&t=' . gmdate('YmdHis') );

		
		$wc_donation_forms = array(
			'campaigns' => $donation_forms,
		);

		wp_localize_script( 'wc-donation-goal-summary-block', 'wc_donation_summary', $wc_donation_forms );
		
		register_block_type( 'wc-donation/goal-summary', array(
			'editor_script' => 'wc-donation-goal-summary-block',
			'render_callback' => array( $this, 'wc_donation_summary_block_render_html' ),
		));
	}

	public function wc_donation_summary_block_render_html( $attributes ) {

		$campaign_id   = isset( $attributes['id'] ) ? $attributes['id'] : '';
		$summary_title = isset( $attributes['title'] ) ? $attributes['title'] : '';
		$summary_desc  = isset( $attributes['desc'] ) ? $attributes['desc'] : '';
		return '[wc_woo_donation_summary id="' . esc_attr($campaign_id) . '" title="' . esc_attr($summary_title) . '" desc="' . esc_attr($summary_desc) . '" ]';
	}

	public function wc_donation_goal_progress_on_shop() {
		global $product;
		$prodID         = $product->get_id();
		$campaign_id = WcdonationSetting::get_campaign_id_by_product_id($prodID);
		$object      = self::get_product_by_campaign($campaign_id);
		$is_wc_donation = get_post_meta($prodID, 'is_wc_donation', true);
		if ( 'donation' == $is_wc_donation ) {
			if ( 'enabled' === $object->goal['display'] && 'enabled' === $object->goal['show_on_shop'] ) {       
				/**
				 * Donation Goal Settings
				 */
				$goalDisp         = !empty( $object->goal['display'] ) ? $object->goal['display'] : '';
				$goalType         = !empty( $object->goal['type'] ) ? $object->goal['type'] : '';
				$donation_product = !empty( $object->product['product_id'] ) ? $object->product['product_id'] : '';
				$get_donations    = WcdonationSetting::has_bought_items( $donation_product );

				$progressBarColor = !empty( $object->goal['progress_bar_color'] ) ? $object->goal['progress_bar_color'] : '';
				$dispDonorCount   = !empty( $object->goal['display_donor_count'] ) ? $object->goal['display_donor_count'] : '';
				$closeForm        = !empty( $object->goal['form_close'] ) ? $object->goal['form_close'] : '';
				$message          = !empty( $object->goal['message'] ) ? $object->goal['message'] : '';

				?>
				<style>
					li.product .wc_progressBarContainer > ul > li.wc_progress div.progressbar {
						background: #<?php esc_html_e( $progressBarColor ); ?>;
					}
				</style>	
				<?php

				if ( 'enabled' === $goalDisp && 'enabled' === $closeForm ) {
					$progress = 0;

					if ( 'fixed_amount' === $goalType || 'percentage_amount' === $goalType  ) { 
						$fixedAmount = !empty( $object->goal['fixed_amount'] ) ? $object->goal['fixed_amount'] : 0;
						
						if ( $fixedAmount > 0 ) {
							$progress = ( $get_donations['total_donation_amount']/$fixedAmount ) * 100;
						}
					}

					if ( 'no_of_donation' === $goalType  ) { 
						$no_of_donation = !empty( $object->goal['no_of_donation'] ) ? $object->goal['no_of_donation'] : 0;
						if ( $no_of_donation > 0 ) {
							$progress = ( $get_donations['total_donations']/$no_of_donation ) * 100;
						}
					}

					if ( $progress >= 100 ) {
						?>
						<p class="donation-goal-completed">
							<?php echo esc_html__($message, 'wc-donation'); ?>
						</p>
						<?php

						return;
					}

					if ( 'no_of_days' === $goalType  ) {
						$no_of_days   = !empty( $object->goal['no_of_days'] ) ? $object->goal['no_of_days'] : 0;
						$end_date     = gmdate('Y-m-d', strtotime($no_of_days));
						$current_date = gmdate('Y-m-d');
						
						if ( $current_date >= $end_date  ) {
							?>
							<p class="donation-goal-completed">
								<?php echo esc_html__($message, 'wc-donation'); ?>
							</p>
							<?php

							return;
						}
					
					}
				}
				
				require WC_DONATION_PATH . 'includes/views/frontend/blocks/frontend-donation-goal-disp.php' ;
			}
		}
	}

	public function wc_donation_product_get_price_html( $price, $product ) {
		$product_id          = $product->get_id();
		$is_donation_product = get_post_meta ( $product_id, 'is_wc_donation', true );
		
		if ( 'donation' === $is_donation_product ) {
			return '';
		}

		return $price;
	}

	public function wc_donation_register_gutenberg_blocks() {
		/**
		* Filter.
		* 
		* @since 3.8.1
		*/
		$should_register_blocks = apply_filters( 'wc_donation_should_register_blocks', true );

		// If the filter returns false, exit the function early.
		if ( ! $should_register_blocks ) {
			return;
		}
		/**
		* Filter.
		* 
		* @since 3.8.1
		*/
		$should_register_blocks = apply_filters( 'wc_donation_should_register_blocks', true );
		// If the filter returns false, exit the function early.
		if ( ! $should_register_blocks ) {
			return;
		}
		/**
		 * Featured Block
		 * 
		 * @since 3.7
		*/
		$this->wc_donation_register_gutenberg_feature_blocks();

		/**
		 * All Donation Block
		 * 
		 * @since 3.7
		*/
		$this->wc_donation_register_gutenberg_all_donation_blocks();

		$args           = array(
			'numberposts' => -1,
			'post_type'   => 'wc-donation',
		);
		$all_campaigns  = get_posts( $args );
		$donation_forms = array();
		$count          = 0;
		foreach ( $all_campaigns as $campaign ) {
			$donation_forms[$count]['ID']    = $campaign->ID;
			$donation_forms[$count]['title'] = $campaign->post_title;
			++$count;
		}

		wp_enqueue_style( 'wc_shortcode_block', WC_DONATION_URL . 'assets/js/gutenberg_shortcode_block/build/style-index.css' );
		wp_register_script( 'wc_shortcode_block', WC_DONATION_URL . 'assets/js/gutenberg_shortcode_block/build/index.js', array( 'wp-components', 'wp-blocks', 'wp-element', 'wp-editor', 'selectWoo', 'jquery' ), WC_DONATION_VERSION );
		if ( is_admin() ) {
			wp_enqueue_script( 'wc_shortcode_block' );
		}
		$wc_donation_forms = array(
			'forms' => $donation_forms,
		);
		wp_localize_script( 'wc_shortcode_block', 'wc_donation_forms', $wc_donation_forms );
		
		register_block_type( 'wc-donation/shortcode', array(
			'editor_script'   => 'wc_shortcode_block',
			'render_callback' => array( $this, 'wc_donation_custom_gutenberg_render_html' ),
		));
	}

	/**
	 * Register Feature Block
	 * 
	 * @since 3.7
	*/
	public function wc_donation_register_gutenberg_feature_blocks() {

		wp_register_script( 'wc_feature_shortcode_block', WC_DONATION_URL . 'assets/js/gutenberg_feature_block/build/index.js', array( 'wp-components', 'wp-blocks', 'wp-element', 'wp-editor', 'selectWoo', 'jquery' ), WC_DONATION_VERSION );
		
		if ( is_admin() ) {
			wp_enqueue_script( 'wc_feature_shortcode_block' );
		}

		register_block_type( 'wc-donation/feature-shortcode', array(
			'editor_script'   => 'wc_feature_shortcode_block',
			'render_callback' => array( $this, 'wc_donation_feature_gutenberg_render_html' ),
		));
	}

	/**
	 * Callback for feature block
	 * 
	 * @since 3.7
	*/
	public function wc_donation_feature_gutenberg_render_html( $attributes ) {   
		
		return '[wc_woo_feature_donation]';
	}

	/**
	 * Register All Donation Block
	 * 
	 * @since 3.7
	*/
	public function wc_donation_register_gutenberg_all_donation_blocks() {

		wp_register_script( 'wc_all_campaign_shortcode_block', WC_DONATION_URL . 'assets/js/gutenberg_all_campaign_block/build/index.js', array( 'wp-components', 'wp-blocks', 'wp-element', 'wp-editor', 'selectWoo', 'jquery' ), WC_DONATION_VERSION );
		
		if ( is_admin() ) {
			wp_enqueue_script( 'wc_all_campaign_shortcode_block' );
		}

		register_block_type( 'wc-donation/all-campaign-shortcode', array(
			'editor_script'   => 'wc_all_campaign_shortcode_block',
			'render_callback' => array( $this, 'wc_donation_all_donation_gutenberg_render_html' ),
		));
	}

	/**
	 * Callback for all donation block
	 * 
	 * @since 3.7
	*/
	public function wc_donation_all_donation_gutenberg_render_html( $attributes ) {   
		
		return '[wc_woo_all_campaign]';
	}

	public function wc_donation_custom_gutenberg_render_html( $attributes ) {   
		
		if ( isset( $attributes['donation_ids'] ) && count($attributes['donation_ids']) > 0 ) {

			if ( !isset($attributes['is_block']) ) {
				$attributes['is_block'] = true;
			}

			if ( !isset($attributes['formattypes']) ) {
				$attributes['formattypes'] = 'block';
			}

			if ( !isset($attributes['displaytype']) ) {
				$attributes['displaytype'] = 'page';
			}

			if ( !isset($attributes['popup_header']) ) {
				$attributes['popup_header'] = 'Campaign';
			}

			if ( !isset($attributes['popup_button']) ) {
				$attributes['popup_button'] = 'View Campaigns';
			}

			if ( !isset($attributes['display_button']) ) {
				$attributes['display_button'] = 'auto_display';
			}

			return '[wc_woo_donation display_button="' . esc_attr( $attributes['display_button'] ) . '" button_text="' . esc_attr( $attributes['popup_button'] ) . '" popup_title="' . esc_attr( $attributes['popup_header'] ) . '" display_type="' . esc_attr( $attributes['displaytype'] ) . '" is_block="' . esc_attr( $attributes['is_block'] ) . '" format="' . esc_attr( $attributes['formattypes'] ) . '" id="' . esc_attr( implode( ',', $attributes['donation_ids'] ) ) . '"]';
		}
	}

	/**
	 * Continue from here tomorrow 04 OCT 2023
	 * 
	 */
	public function exclude_specific_doantion_product_from_shop( $query ) {
		if ( ! is_admin() && function_exists( 'is_shop' ) && is_shop() && $query->is_main_query() ) {
			$all_campaigns = get_posts(array(
				'fields'          => 'ids',
				'posts_per_page'  => -1,
				'post_status' => 'publish',
				'post_type' => 'wc-donation',
			));

			$excluded_ids = array();

			foreach ( $all_campaigns as $campaign_id ) {
				$object           = self::get_product_by_campaign($campaign_id);
				$setTimerDonation = WcDonation::setTimerDonation($object);
				if ( isset( $setTimerDonation['status'], $setTimerDonation['type'] ) && false === $setTimerDonation['status'] && 'hide' === $setTimerDonation['type'] ) {
					$excluded_ids[] = $object->product['product_id'];
				}
			}           

			// Exclude the product from the shop query
			$query->set( 'post__not_in', $excluded_ids );
		}
	}

	public function remove_loop_button( $button, $product, $arg = '' ) {

		if ( !empty( $product ) ) {
			$prodID         = $product->get_id();
			$is_wc_donation = get_post_meta($prodID, 'is_wc_donation', true);
			$url            = get_permalink( $prodID );
		
			if ( 'donation' == $is_wc_donation ) {
				$campaign_id = WcdonationSetting::get_campaign_id_by_product_id($prodID);
				$object      = self::get_product_by_campaign($campaign_id);

				/* Donation setTimer Settings */
				$setTimerDonation = WcDonation::setTimerDonation($object);              
				if ( false === $setTimerDonation['status'] && 'hide' === $setTimerDonation['type'] ) {
					return;
				}
				
				if ( false === $setTimerDonation['status'] && 'display_message' === $setTimerDonation['type'] ) {
					echo wp_kses_post('<p class="setTimerMsg">' . $setTimerDonation['message'] . '</p>');

					return;
				}
				if ( isset($object) && ! empty($object) && isset($object->campaign['donationBtnTxt']) ) { 
					/**
					* Action.
					* 
					* @since 3.4.5
					*/
					do_action ('wc_donation_before_archive_add_donation_button', $product );
					
					$button = sprintf( '<a href="%s" data-quantity="1" class="%s">%s</a>',
					esc_url( $url ),
					esc_attr( isset( $args['class'] ) ? $args['class'] : 'button' ),
					$object->campaign['donationBtnTxt'] );
					/**
					* Action.
					* 
					* @since 3.4.5
					*/                  
					do_action ('wc_donation_after_archive_add_donation_button', $product, $object);
				}   
			}
		}

		return $button;
	}


	public function woocommerce_simple_add_to_cart_remove() {
	   
		wp_register_script( 'donation-flipclock-js', WC_DONATION_URL . 'assets/js/flipclock.js', array( 'jquery' ), '0.7.8', true );
		wp_register_style( 'donation-flipclock-css', WC_DONATION_URL . 'assets/css/flipclock.css', array(), '0.7.8');
		wp_register_script( 'donation-progressbar-js', WC_DONATION_URL . 'assets/js/progressbar.js', array(), null, true);

		global $post;
		
		if ( !empty( $post ) ) { 
			$campaign_id  = WcdonationSetting::get_campaign_id_by_product_id($post->ID);
			$prod_id      = get_post_meta($campaign_id, 'wc_donation_product', true);
			$product_post = get_post($prod_id);
			if ($product_post) {
				$product_content = $product_post->post_content;
			}
			$object = self::get_product_by_campaign($campaign_id);

			if ( isset($object) && ! empty($object) ) { 

				/* Donation setTimer Settings */
				$setTimerDonation = WcDonation::setTimerDonation($object);              
				if ( false === $setTimerDonation['status'] && 'hide' === $setTimerDonation['type'] ) {
					remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20);
					remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
					add_filter( 'woocommerce_product_tabs', '__return_empty_array', 99 );
				} elseif (( 'yes' == get_option( 'wc-donation-tributes' ) && 'yes' == get_option( 'wc-donation-messages' ) )
						|| ( 'yes' == get_option( 'wc-donation-donor-wall' ) && ( 'yes' == $object->donor['donor_list'] || 'yes' == $object->donor['anonymous_list'] ) )
						|| ( !empty($product_content) )
					) {

						add_filter( 'woocommerce_product_tabs', array( $this, 'woocommerce_product_tabs_callback' ), 10 );
				}
				
				remove_action( 'woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );
				remove_action( 'woocommerce_simple_subscription_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );
				remove_action( 'woocommerce_grouped_add_to_cart', 'woocommerce_grouped_add_to_cart', 30 );
				remove_action( 'woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30 );
				remove_action( 'woocommerce_external_add_to_cart', 'woocommerce_external_add_to_cart', 30 );
				remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation', 10 );
				remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20 );
				
				remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
				remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );

				//older version of wc subscription
				if ( class_exists('WC_Subscriptions') && version_compare( WC_Subscriptions::$version, '4.1.0', '<' ) ) {
					remove_action( 'woocommerce_subscription_add_to_cart', 'WC_Subscriptions::subscription_add_to_cart', 30 );
					remove_action( 'woocommerce_variable-subscription_add_to_cart', 'WC_Subscriptions::variable_subscription_add_to_cart', 30 );
				}

				//for newer version of wcs subscription
				if ( class_exists('WC_Subscriptions') && class_exists('WCS_Template_Loader') && version_compare( WC_Subscriptions::$version, '4.1.0', '>=' ) ) {
					remove_action( 'woocommerce_subscription_add_to_cart', 'WCS_Template_Loader::get_subscription_add_to_cart', 30 );
					remove_action( 'woocommerce_variable-subscription_add_to_cart', 'WCS_Template_Loader::get_variable_subscription_add_to_cart', 30 );
				}

				add_action('woocommerce_single_product_summary', array( $this, 'wc_donation_single_template' ), 999 );
				
			}
		}
	}

	public function no_ways() {

		global $post;       
	
		if ( !empty( $post ) ) { 
			
			$prod_id = get_post_meta( $post->ID, 'wc_donation_product', true );
			if ( ! empty($prod_id) ) {
				$url = get_permalink( $prod_id );
				wp_safe_redirect($url);
				exit();
			}

			$campaign_id = WcdonationSetting::get_campaign_id_by_product_id($post->ID);
			$object      = self::get_product_by_campaign($campaign_id);
			if ( isset($object->product['is_single']) && 'no' == $object->product['is_single'] ) {              
				$url = home_url();
				wp_safe_redirect($url);
				exit();       
				
			}
		}
	}

	public function wc_donation_single_template() {
		
		global $post;
		if ( !empty( $post ) ) { 
			$post_exist = get_post( $post->ID );
			if ( !empty($post_exist) && ( isset($post_exist->post_status) && 'trash' !== $post_exist->post_status ) ) {
				$campaign_id = WcdonationSetting::get_campaign_id_by_product_id($post->ID);
				$object      = self::get_product_by_campaign($campaign_id);              
				$_type       = 'single';

				/* Donation setTimer Settings */
				$setTimerDonation = WcDonation::setTimerDonation($object);              
				if ( false === $setTimerDonation['status'] && 'hide' === $setTimerDonation['type'] ) {
					return;
				}

				echo '<div class="widget_wc-donation-widget" id="wc_donation_on_single_' . esc_html($campaign_id) . '">';
				/**
				* Action.
				* 
				* @since 3.4.5
				*/
				do_action ('wc_donation_before_single_add_donation', $campaign_id, $post->ID);
				require WC_DONATION_PATH . 'includes/views/frontend/frontend-order-donation.php';
				/**
				* Action.
				* 
				* @since 3.4.5
				*/
				do_action ('wc_donation_after_single_add_donation', $campaign_id, $post->ID);
				echo '</div>';
			} else {
				/* translators: %1$s refers to html tag, %2$s refers to html tag */
				printf(esc_html__('%1$s Campaign deleted by admin %2$s', 'wc-donation'), '<p class="wc-donation-error">', '</p>' );
				return;
			}
		}
	}

	public function register_render_wc_donation_campaign_shortcode() {
		add_shortcode( 'wc_woo_donation', array( $this, 'render_wc_donation_campaign' ) );
		add_shortcode( 'wc_woo_donation_summary', array( $this, 'render_wc_woo_donation_summary' ) );
		add_shortcode( 'wc_woo_donation_tabs', array( $this, 'render_wc_woo_donation_tabs' ) );      
		add_shortcode( 'wc_woo_donor_wall', array( $this, 'render_wc_woo_donor_wall' ) );
		add_shortcode( 'wc_woo_global_donor_wall', array( $this, 'render_wc_woo_global_donor_wall' ) );
		add_shortcode( 'wc_woo_leaderboard_donor_wall', array( $this, 'render_wc_woo_leaderboard_donor_wall' ) );
		add_shortcode( 'wc_woo_feature_donation', array( $this, 'render_wc_donation_feature_campaign' ) );
		add_shortcode( 'wc_woo_all_campaign', array( $this, 'render_wc_donation_all_campaign' ) );
		add_shortcode( 'wc_woo_progressBarContainer', array( $this, 'render_wc_progressBarContainer' ) );
	}

	public function render_wc_woo_global_donor_wall( $atts ) {

		$_title = '';
		if ( isset($atts['title']) ) {
			$_title = $atts['title'];
		}

		ob_start();
		require WC_DONATION_PATH . 'includes/views/frontend/donor_wall/global_list.php';
		return ob_get_clean();
	}

	public function render_wc_woo_leaderboard_donor_wall( $atts ) {

		$_title = '';
		if ( isset($atts['title']) ) {
			$_title = $atts['title'];
		}

		ob_start();
		require WC_DONATION_PATH . 'includes/views/frontend/donor_wall/leaderboard_list.php';
		return ob_get_clean();
	}

	public function render_wc_woo_donor_wall( $atts ) {     

		if ( ! isset( $atts['id'] ) || empty( $atts['id'] ) ) {
			return esc_html__('Campaign id is missing', 'wc-donation');
		}

		$campaign_id = $atts['id'];
		$object      = self::get_product_by_campaign($campaign_id);

		if ( !isset($atts['type']) || empty($atts['type']) ) {
			$atts['type'] = 'anonymous_list';
		}

		$_title = '';
		if ( isset($atts['title']) ) {
			$_title = $atts['title'];
		}

		ob_start();
		if ( 'anonymous_list' == $atts['type'] ) {
			require_once WC_DONATION_PATH . 'includes/views/frontend/donor_wall/anonymous_list.php';
		}

		if ( 'donor_list' == $atts['type'] ) {
			require_once WC_DONATION_PATH . 'includes/views/frontend/donor_wall/donor_list.php';
		}

		if ( 'both' == $atts['type'] ) {
			require_once WC_DONATION_PATH . 'includes/views/frontend/donor_wall/donor_list.php';
			require_once WC_DONATION_PATH . 'includes/views/frontend/donor_wall/anonymous_list.php';
		}
		return ob_get_clean();
	}
	public function render_wc_progressBarContainer( $atts ) {
		if ( ! isset( $atts['id'] ) || empty( $atts['id'] ) ) {
			/**
			* Filter.
			* 
			* @since 3.8
			*/
			return apply_filters( 'wc_donation_goal_campaign_id_missing_message', esc_html__( 'Campaign ID is required.', 'wc-donation' ) );
		}
		$campaign_id     = $atts['id'];
		/**
		 * Donation Goal Settings
		 */
		$object = self::get_product_by_campaign($campaign_id);
		if ( ! isset( $object ) || empty( $object ) ) {
			/**
			* Filter.
			* 
			* @since 3.8
			*/
			return apply_filters( 'wc_donation_goal_invalid_campaign_id_message', esc_html__( 'Campaign ID is Invalid.', 'wc-donation' ) );
		}
		if ( 'enabled' === $object->goal['display'] ) {
			$goalDisp         = !empty( $object->goal['display'] ) ? $object->goal['display'] : '';
			$goalType         = !empty( $object->goal['type'] ) ? $object->goal['type'] : '';
			$donation_product = !empty( $object->product['product_id'] ) ? $object->product['product_id'] : '';
			$get_donations    = WcdonationSetting::has_bought_items( $donation_product );

			$progressBarColor = !empty( $object->goal['progress_bar_color'] ) ? $object->goal['progress_bar_color'] : '';
			$dispDonorCount   = !empty( $object->goal['display_donor_count'] ) ? $object->goal['display_donor_count'] : '';
			$closeForm        = !empty( $object->goal['form_close'] ) ? $object->goal['form_close'] : '';
			$message          = !empty( $object->goal['message'] ) ? $object->goal['message'] : '';
			ob_start(); 
			?>
			<style>
				li.product .wc_progressBarContainer > ul > li.wc_progress div.progressbar {
					background: #<?php esc_html_e( $progressBarColor ); ?>;
				}
			</style>	
			<?php

			if ( 'enabled' === $goalDisp && 'enabled' === $closeForm ) {
				$progress = 0;

				if ( 'fixed_amount' === $goalType || 'percentage_amount' === $goalType  ) { 
					$fixedAmount = !empty( $object->goal['fixed_amount'] ) ? $object->goal['fixed_amount'] : 0;
					
					if ( $fixedAmount > 0 ) {
						$progress = ( $get_donations['total_donation_amount']/$fixedAmount ) * 100;
					}
				}

				if ( 'no_of_donation' === $goalType  ) { 
					$no_of_donation = !empty( $object->goal['no_of_donation'] ) ? $object->goal['no_of_donation'] : 0;
					if ( $no_of_donation > 0 ) {
						$progress = ( $get_donations['total_donations']/$no_of_donation ) * 100;
					}
				}

				if ( $progress >= 100 ) {
					?>
					<p class="donation-goal-completed">
						<?php echo esc_html__($message, 'wc-donation'); ?>
					</p>
					<?php

					return;
				}

				if ( 'no_of_days' === $goalType  ) {
					$no_of_days   = !empty( $object->goal['no_of_days'] ) ? $object->goal['no_of_days'] : 0;
					$end_date     = gmdate('Y-m-d', strtotime($no_of_days));
					$current_date = gmdate('Y-m-d');
					
					if ( $current_date >= $end_date  ) {
						?>
						<p class="donation-goal-completed">
							<?php echo esc_html__($message, 'wc-donation'); ?>
						</p>
						<?php

						return;
					}
				
				}
			}
			 
			require WC_DONATION_PATH . 'includes/views/frontend/blocks/frontend-donation-goal-disp.php' ;
			return ob_get_clean();
		} else {
			ob_start(); 
			/**
			* Filter.
			* 
			* @since 3.8
			*/ 
			return apply_filters( 'wc_donation_goal_disable_message', esc_html__( 'Donation Goal are disable for this campaign.', 'wc-donation' ) );
			return ob_get_clean();
			
		}
	}

	public function render_wc_woo_donation_tabs( $atts ) {

		if ( ! isset( $atts['ids'] ) || empty( $atts['ids'] ) ) {
			return esc_html__('Campaign ids are missing', 'wc-donation');
		} else {
			$ids  = explode( ',', $atts['ids'] );
			$id_1 = ( isset( $ids[0] ) && ! empty( trim( $ids[0] ) ) ) ? trim( $ids[0] ) : false;
			$id_2 = ( isset( $ids[1] ) && ! empty( trim( $ids[1] ) ) ) ? trim( $ids[1] ) : false;
			
			if ( !$id_1 || !$id_2 ) {
				return esc_html__('Campaign ids are missing', 'wc-donation');
			}
		}

		if ( ! isset( $atts['titles'] ) || empty( $atts['titles'] ) ) {
			return esc_html__('Tab titles are missing', 'wc-donation');
		} else {
			$titles  = explode( ',', $atts['titles'] );
			$title_1 = ( isset( $titles[0] ) && ! empty( trim( $titles[0] ) ) ) ? trim( $titles[0] ) : false;
			$title_2 = ( isset( $titles[1] ) && ! empty( trim( $titles[1] ) ) ) ? trim( $titles[1] ) : false;
			
			if ( !$title_1 || !$title_2 ) {
				return esc_html__('Tab titles are missing', 'wc-donation');
			}
		}

		ob_start();     
		require WC_DONATION_PATH . 'includes/views/frontend/wc-donation-tabs.php';      
		return ob_get_clean();
	}

	public function render_wc_woo_donation_summary( $atts ) {

		if ( ! isset( $atts['id'] ) || empty( $atts['id'] ) ) {
			return;
		}

		ob_start();
		$campaign_id     = $atts['id'];
		$summary_title   = isset( $atts['title'] ) ? $atts['title'] : '';
		$summary_desc    = isset( $atts['desc'] ) ? $atts['desc'] : '';
		$product_id      = get_post_meta( $campaign_id, 'wc_donation_product', true );
		$donation_amount = get_post_meta( $product_id, 'total_donation_amount', true );
		$currency_symbol = get_woocommerce_currency_symbol();
		/**
		* Action.
		* 
		* @since 3.4.5
		*/
		do_action ('wc_donation_summary_before', $campaign_id, $product_id);
		require WC_DONATION_PATH . 'includes/views/frontend/wc-donation-summary.php';
		/**
		* Action.
		* 
		* @since 3.4.5
		*/
		do_action ('wc_donation_summary_after', $campaign_id, $product_id);
		return ob_get_clean();
	}

	public function render_wc_donation_campaign( $atts ) {
		$campaign_ids = explode( ',', $atts['id'] );
		$display_type = isset( $atts['display_type'] ) ? $atts['display_type'] : '';
		$is_block     = isset( $atts['is_block'] ) ? $atts['is_block'] : false;
		
		$campaign_display_type = get_option( 'wc-donation-cart-campaign-display-format' );
		$campaign_type         = get_option( 'wc-donation-campaign-display-type' );

		ob_start();

		if ( ! empty( $campaign_ids ) ) {

			if ( $is_block ) {
				if ( ! empty( $display_type ) && 'popup' == $display_type ) {
					WC_Cart_Donation_Campaigns_Block::wc_donation_campaign_popup_display_button_block( $atts );
				} else {
					WC_Cart_Donation_Campaigns_Block::display_wc_donation_on_cart_block( $atts );
				}
				return ob_get_clean();
			}

			if ( ! empty( $campaign_display_type ) && 'popup' == $campaign_type && 'button_display' == $campaign_display_type && is_cart() ) {
				
				WC_Cart_Donation_Campaigns::wc_donation_campaign_popup_display_button();

			} elseif ( ( ! is_admin() || ( class_exists('\Elementor\Plugin') && \Elementor\Plugin::$instance->editor->is_edit_mode() ) ) && isset( $atts['id'] ) && ! empty( $atts['id'] ) ) {
				

					$post_exist = (object) get_post( $atts['id'] );

				if ( !empty($post_exist) || ( isset($post_exist->post_status) && 'trash' !== $post_exist->post_status ) ) {
					$campaign_id = $atts['id'];
					$object      = self::get_product_by_campaign($campaign_id);
					$_type       = 'shortcode';
					echo '<div class="widget_wc-donation-widget" id="wc_donation_on_shortcode_' . esc_html($campaign_id) . '">';
					/**
					* Action.
					* 
					* @since 3.4.5
					*/
					do_action ('wc_donation_before_shortcode_add_donation', $campaign_id);
					require WC_DONATION_PATH . 'includes/views/frontend/frontend-order-donation.php';
					/**
					* Action.
					* 
					* @since 3.4.5
					*/
					do_action ('wc_donation_after_shortcode_add_donation', $campaign_id);
					echo '</div>';
				} else {
					/* translators: %1$s refers to html tag, %2$s refers to html tag */
					printf(esc_html__('%1$s Campaign deleted by admin %2$s', 'wc-donation'), '<p class="wc-donation-error">', '</p>' );
				}
			} else {
				/* translators: %1$s refers to html tag, %2$s refers to html tag */
				printf(esc_html__('%1$s Please enter correct shortcode %2$s', 'wc-donation'), '<p class="wc-donation-error">', '</p>' );
				
			}

		}

		return ob_get_clean();
	}
	private function get_categories_dropdown( $selected_category ) {
		
		$categories = get_terms(array(
			'taxonomy' => 'wc_donation_categories',
			'hide_empty' => true,
		));

		$output = '<select name="campaign_category" onchange="document.getElementById(\'campaign-filter\').submit();">';
		$output .= '<option value="">' . esc_html__('All Categories', 'wc-donation') . '</option>';

		if ( ! empty( $categories ) ) { 
			foreach ( $categories as $category ) {
				if ( 0 == $category->parent ) {
					$selected = ( $selected_category === $category->slug ) ? 'selected' : '';
					$output .= '<option value="' . esc_attr($category->slug) . '" ' . esc_attr($selected) . '>' . esc_html($category->name) . '</option>';

					$child_categories = get_terms(array(
						'taxonomy' => 'wc_donation_categories',
						'hide_empty' => true,
						'parent' => $category->term_id,
					));

					foreach ($child_categories as $child) {
						$selected = ( $selected_category === $child->slug ) ? 'selected' : '';
						$output .= '<option value="' . esc_attr( $child->slug ) . '" ' . esc_attr( $selected ) . '>&nbsp;&nbsp;&nbsp;&nbsp;' . esc_html( $child->name ) . '</option>';
					}
				}
			}
		}

		$output .= '</select>';
		return $output;
	}


	private function get_sorting_dropdown( $selected_sort ) {
		$options = array(
			'' => __('Default Sorting', 'wc-donation'),
			'date_desc' => __('Sort by date: Newest First', 'wc-donation'),
			'date_asc' => __('Sort by date: Oldest First', 'wc-donation'),
			'featured' => __('Sort by featured campaigns', 'wc-donation'),
			'name_asc' => __('Sort by Name: A to Z', 'wc-donation'),
			'name_desc' => __('Sort by Name: Z to A', 'wc-donation'),
		);

		$output = '<select name="campaign_sort" onchange="document.getElementById(\'campaign-filter\').submit();">';

		foreach ($options as $value => $label) {
			$output .= '<option value="' . esc_attr($value) . '"' . selected($selected_sort, $value, false) . '>' . esc_html($label) . '</option>';
		}

		$output .= '</select>';
		return $output;
	}

	private function get_campaign_query_args( $paged, $posts_per_page, $selected_category, $selected_sort, $exclude_roundoff_campaign, $featured = false ) {
		$args = array(
			'post_type' => 'wc-donation',
			'paged' => $paged,
			'posts_per_page' => $posts_per_page,
			'post__not_in' => array( $exclude_roundoff_campaign ),
		);

		if ($selected_category) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'wc_donation_categories',
					'field' => 'slug',
					'terms' => $selected_category,
				),
			);
		}

		switch ($selected_sort) {
			case 'date_desc':
				$args['orderby'] = 'date';
				$args['order']   = 'DESC';
				break;
			case 'date_asc':
				$args['orderby'] = 'date';
				$args['order']   = 'ASC';
				break;
			case 'featured':
				$args['meta_query'] = array(
					array(
						'key' => 'feature_donation',
						'value' => 'yes',
						'compare' => '=',
					),
				);
				break;
			case 'name_asc':
				$args['orderby'] = 'title';
				$args['order']   = 'ASC';
				break;
			case 'name_desc':
				$args['orderby'] = 'title';
				$args['order']   = 'DESC';
				break;
			default:
				break;
		}

		if ($featured) {
			$args['meta_query'] = array(
				array(
					'key' => 'feature_donation',
					'value' => 'yes',
					'compare' => '=',
				),
			);
		}

		return $args;
	}

	private function display_campaigns( $query ) {
		if ($query->have_posts()) {
			echo '<div class="wc-donation-feature-campaigns">';

			while ($query->have_posts()) {
				$query->the_post();
				$post_id   = get_the_ID();
				$prod_id   = get_post_meta($post_id, 'wc_donation_product', true);
				$title     = get_the_title($post_id);
				$permalink = get_permalink($post_id);
				$image     = get_the_post_thumbnail($post_id, 'medium');
				$excerpt   = '';
				$is_featured = get_post_meta($post_id, 'feature_donation', true);

				if ($prod_id) {
					$product_post = get_post($prod_id);
					if ($product_post) {
						$excerpt = $product_post->post_excerpt;
					} else {
						$excerpt = '';
					}
				}

				echo '<div class="wc-donation-item">';
					// Add a container for the star icon
				if ('yes' === $is_featured) {
					echo '<div class="featured-star">&#9733;</div>'; // Unicode star character
				}
					echo '<div class="wc-donation-image">' . wp_kses_post($image) . '</div>';
					echo '<h2 class="wc-donation-title">' . esc_html($title) . '</h2>';
					echo '<div class="wc-donation-excerpt">' . esc_html($excerpt) . '</div>';
					echo '<a class="button wc-donation-feature-button" href="' . esc_url($permalink) . '">' . esc_html__('Donate', 'wc-donation') . '</a>';
				echo '</div>';
			}

			echo '</div>';
			echo '<div class="pagination">';
			$pagination_links = paginate_links(array( 'total' => $query->max_num_pages ));

			if (!empty($pagination_links)) {
				echo wp_kses(
					$pagination_links,
					array(
						'span' => array(
							'aria-current' => array(),
							'class' => array(),
						),
						'a' => array(
							'class' => array(),
							'href' => array(),
						),
					)
				);
			}

			echo '</div>';
			wp_reset_postdata();
		} else {
			echo '<p>' . esc_html__('No donation campaigns found.', 'wc-donation') . '</p>';
		}
	}

	public function render_wc_donation_all_campaign( $atts ) {
		$selected_category         = isset($_GET['campaign_category']) ? sanitize_text_field($_GET['campaign_category']) : '';
		$selected_sort             = isset($_GET['campaign_sort']) ? sanitize_text_field($_GET['campaign_sort']) : '';
		$paged                     = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
		$posts_per_page            = 6;
		$exclude_roundoff_campaign = get_option('wc-donation-round-product', true);

		$args         = $this->get_campaign_query_args($paged, $posts_per_page, $selected_category, $selected_sort, $exclude_roundoff_campaign);
		$all_campaign = new WP_Query($args);

		ob_start();
		echo '<form method="GET" id="campaign-filter" class="campaign-filter">';
		echo '<div class="filter-container">';

		echo wp_kses(
			$this->get_sorting_dropdown($selected_sort),
			array(
				'select' => array(
					'name' => array(),
					'onchange' => array(),
				),
				'option' => array(
					'value' => array(),
					'selected' => array(),
				),
			)
		);

		echo wp_kses(
			$this->get_categories_dropdown($selected_category),
			array(
				'select' => array(
					'name' => array(),
					'onchange' => array(),
				),
				'option' => array(
					'value' => array(),
					'selected' => array(),
				),
			)
		);

		echo '</div>';
		echo '</form>';

		$this->display_campaigns($all_campaign);

		return ob_get_clean();
	}

	public function render_wc_donation_feature_campaign( $atts ) {
		$selected_category         = isset($_GET['campaign_category']) ? sanitize_text_field($_GET['campaign_category']) : '';
		$paged                     = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
		$posts_per_page            = 6;
		$exclude_roundoff_campaign = get_option('wc-donation-round-product', true);

		$args             = $this->get_campaign_query_args($paged, $posts_per_page, $selected_category, '', $exclude_roundoff_campaign, true);
		$feature_campaign = new WP_Query($args);

		ob_start();
		echo '<form method="GET" id="campaign-filter">';
		echo '<div id="campaign-cat">';
		echo wp_kses(
			$this->get_categories_dropdown($selected_category),
			array(
				'select' => array(
					'name' => array(),
					'onchange' => array(),
				),
				'option' => array(
					'value' => array(),
					'selected' => array(),
				),
			)
		);

		echo '</div>';
		echo '</form>';

		$this->display_campaigns($feature_campaign);

		return ob_get_clean();
	}
	/**
	 * Delete all data related to campaign
	 */
	public function wc_donation_delete_campaign_from_db( $post_id = '', $post = '' ) {
		
		// We check if the global post type isn't ours and just return
		global $post_type;

		if ( 'wc-donation' !== $post_type ) {
			return;
		}

		$prod_id = get_post_meta( $post_id, 'wc_donation_product', true );

		$cart_donation     = get_option('wc-donation-cart-product');
		$checkout_donation = get_option('wc-donation-checkout-product');
		$round_donation    = get_option('wc-donation-round-product');

		if ( $cart_donation == $post_id ) {
			update_option ('wc-donation-cart-product', '');
			update_option ('wc-donation-on-cart', 'no');
		}

		if ( $checkout_donation == $post_id ) {
			update_option ('wc-donation-checkout-product', '');
			update_option ('wc-donation-on-checkout', 'no');
		}

		if ( $round_donation == $post_id ) {
			update_option ('wc-donation-round-product', '');
			update_option ('wc-donation-on-round', 'no');
		}

		wp_delete_post( $prod_id, true); // Set to False if you want to send them to Trash.
		
		$path = WC_DONATION_PATH . 'vendor/deps/qr-codes/' . get_post_meta( $post_id, 'wc-donation-social-share-qr-url', true );
		if ( file_exists( $path )) {
			unlink( $path );
		}
	}

	/**
	 * Saving campaign postmeta
	 */
	public function wc_donation_save_campaigns_details( $post_id, $post, $updated ) {
		
		/**
		* Filter.
		* 
		* @since 3.8
		*/
		$donation_status = apply_filters( 'wc_donation_post_status', array( 'publish' ) );

		if ( 'wc-donation' == $post->post_type && in_array( $post->post_status, $donation_status  ) ) {
			
			if ( !isset($_POST['_wcdnonce']) || ( isset($_POST['_wcdnonce']) && !wp_verify_nonce(sanitize_text_field($_POST['_wcdnonce']), '_wcdnonce') ) ) {
				wp_die( 'Not Authorized' );
			}

			if ( empty( $post->post_title ) ) {
				$my_post = array(
					'ID'           => $post_id,
					'post_title'   => '(no title)',
				);
				wp_update_post( $my_post );
			}

			/**
			* Action.
			* 
			* @since 3.4.5
			*/
			do_action ('wc_donation_before_save_campaign_meta', $post_id);

			//for API call if thumbnail Image was set
			if ( isset( $_POST['_thumbnail_id'] ) && ! empty( sanitize_text_field($_POST['_thumbnail_id']) ) ) {
				update_post_meta($post_id, '_thumbnail_id', sanitize_text_field($_POST['_thumbnail_id']));
			}

			if ( isset( $_POST['wc-donation-tablink'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-tablink'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-tablink', sanitize_text_field( $_POST['wc-donation-tablink'] )  );
			} else {
				update_post_meta ( $post_id, 'wc-donation-tablink', 'tab-1'  );
			}

			if ( isset( $_POST['wc-donation-disp-single-page'] ) && 'yes' == sanitize_text_field( $_POST['wc-donation-disp-single-page'] ) ) {
				update_post_meta ( $post_id, 'wc-donation-disp-single-page', 'yes'  );

				if ( isset( $_POST['wc-donation-disp-shop-page'] ) && 'yes' == sanitize_text_field( $_POST['wc-donation-disp-shop-page'] ) ) {
					update_post_meta ( $post_id, 'wc-donation-disp-shop-page', 'yes'  );
				} else {
					update_post_meta ( $post_id, 'wc-donation-disp-shop-page', 'no'  );
				}
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'wc-donation-disp-single-page', 'no'  );
					update_post_meta ( $post_id, 'wc-donation-disp-shop-page', 'no'  );
			}

			if ( isset( $_POST['wc-donation-disp-feature'] ) && 'yes' == sanitize_text_field( $_POST['wc-donation-disp-feature'] ) ) { 
				update_post_meta ( $post_id, 'feature_donation', 'yes'  );
				update_post_meta ( $post_id, 'wc-donation-disp-single-page', 'yes'  );
			} else {
				update_post_meta ( $post_id, 'feature_donation', 'no'  );
			}

			if ( isset( $_POST['wc-donation-amount-display-option'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-amount-display-option'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-amount-display-option', sanitize_text_field( $_POST['wc-donation-amount-display-option'] )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'wc-donation-amount-display-option', 'both'  );
			}

			if ( isset( $_POST['wc-donation-custom-type-option'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-custom-type-option'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-custom-type-option', sanitize_text_field( $_POST['wc-donation-custom-type-option'] )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'wc-donation-custom-type-option', 'custom_range'  );
			}

			if ( isset( $_POST['free-amount-ph'] ) && ! empty( sanitize_text_field( $_POST['free-amount-ph'] ) ) ) { 
				update_post_meta ( $post_id, 'free-amount-ph', sanitize_text_field( $_POST['free-amount-ph'] )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'free-amount-ph', '' );
			}

			if ( isset( $_POST['pred-amount'] ) && ! empty( array_map( 'sanitize_text_field', wp_unslash( $_POST['pred-amount'] ) ) ) ) { 
				update_post_meta ( $post_id, 'pred-amount', array_map( 'sanitize_text_field', wp_unslash( $_POST['pred-amount'] ) )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'pred-amount', ''  );               
			}

			if ( isset( $_POST['pred-label'] ) && ! empty( array_map( 'sanitize_text_field', wp_unslash( $_POST['pred-label'] ) ) ) ) { 
				update_post_meta ( $post_id, 'pred-label', array_map( 'sanitize_text_field', wp_unslash( $_POST['pred-label'] ) )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'pred-label', ''  );
			}
			
			if ( isset( $_POST['free-min-amount'] ) && ! empty( sanitize_text_field( $_POST['free-min-amount'] ) ) && 
				 isset( $_POST['free-max-amount'] ) && ! empty( sanitize_text_field( $_POST['free-max-amount'] ) ) ) {
				
				$min_amount = sanitize_text_field( $_POST['free-min-amount'] );
				$max_amount = sanitize_text_field( $_POST['free-max-amount'] );
				
				// Check if max amount is greater than min amount
				if ( floatval($max_amount) > floatval($min_amount) ) {
					update_post_meta( $post_id, 'free-min-amount', $min_amount );
					update_post_meta( $post_id, 'free-max-amount', $max_amount );
				} else {
					update_post_meta( $post_id, 'free-min-amount', $max_amount );
					update_post_meta( $post_id, 'free-max-amount', $min_amount );
				}
			} elseif ( ! isset($_POST['api_call']) ) {
				if ( ! isset( $_POST['free-min-amount'] ) || empty( sanitize_text_field( $_POST['free-min-amount'] ) ) ) {
					update_post_meta( $post_id, 'free-min-amount', '' );
				}
					
				if ( ! isset( $_POST['free-max-amount'] ) || empty( sanitize_text_field( $_POST['free-max-amount'] ) ) ) {
					update_post_meta( $post_id, 'free-max-amount', '' );
				}
			}

			if ( isset( $_POST['wc-donation-display-donation-type'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-display-donation-type'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-display-donation-type', sanitize_text_field( $_POST['wc-donation-display-donation-type'] )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'wc-donation-display-donation-type', 'select'  );
			}
			
			if ( isset( $_POST['wc-donation-currency-position'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-currency-position'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-currency-position', sanitize_text_field( $_POST['wc-donation-currency-position'] )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'wc-donation-currency-position', 'before'  );
			}
			$prod_id = get_post_meta( $post_id, 'wc_donation_product', true );
			if ($prod_id) {
				// Get the custom description from the POST data
				if (isset($_POST['wc-donation-description'])) {
					$custom_description = wp_kses_post($_POST['wc-donation-description']);

					// Update the post_content of the product post
					$product_post = array(
						'ID'           => $prod_id,
						'post_content' => $custom_description,
					);
					wp_update_post($product_post);
				} else {
					$product_post = array(
						'ID'           => $prod_id,
						'post_content' => '',
					);
					wp_update_post($product_post);
				}
				if (isset($_POST['wc-donation-short-description'])) {
					$custom_description = wp_kses_post($_POST['wc-donation-short-description']);

					// Update the post_content of the product post
					$product_post = array(
						'ID'           => $prod_id,
						'post_excerpt' => $custom_description,
					);
					wp_update_post($product_post);
				} else {
					$product_post = array(
						'ID'           => $prod_id,
						'post_excerpt' => '',
					);
					wp_update_post($product_post);
				}
			}
			
			/* if ( isset( $_POST['wc-donation-short-description'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-short-description'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-short-description', sanitize_text_field( $_POST['wc-donation-short-description'] )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'wc-donation-short-description', '' );
			} */
			
			if ( isset( $_POST['wc-donation-title'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-title'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-title', sanitize_text_field( $_POST['wc-donation-title'] )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'wc-donation-title', '' );
			}
			
			if ( isset( $_POST['wc-donation-button-text'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-button-text'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-button-text', sanitize_text_field( $_POST['wc-donation-button-text'] )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'wc-donation-button-text', 'Donate'  );
			}
			
			if ( isset( $_POST['wc-donation-button-text-color'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-button-text-color'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-button-text-color', sanitize_text_field( $_POST['wc-donation-button-text-color'] )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'wc-donation-button-text-color', 'FFFFFF'  );
			}

			if ( isset( $_POST['wc-donation-currency-symbol-color'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-currency-symbol-color'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-currency-symbol-color', sanitize_text_field( $_POST['wc-donation-currency-symbol-color'] )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'wc-donation-currency-symbol-color', 'FFFFFF'  );
			}

			if ( isset( $_POST['wc-donation-currency-bg-color'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-currency-bg-color'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-currency-bg-color', sanitize_text_field( $_POST['wc-donation-currency-bg-color'] )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'wc-donation-currency-bg-color', '333333'  );
			}

			if ( isset( $_POST['wc-donation-tribute-color'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-tribute-color'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-tribute-color', sanitize_text_field( $_POST['wc-donation-tribute-color'] )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'wc-donation-tribute-color', '333333'  );
			}
			
			if ( isset( $_POST['wc-donation-button-bg-color'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-button-bg-color'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-button-bg-color', sanitize_text_field( $_POST['wc-donation-button-bg-color'] )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'wc-donation-button-bg-color', '333333'  );
			}
			
			if ( isset( $_POST['wc-donation-recurring'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-recurring'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-recurring', sanitize_text_field( $_POST['wc-donation-recurring'] )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'wc-donation-recurring', 'disabled'  );
			}
			
			if ( isset( $_POST['_subscription_period_interval'] ) && ! empty( sanitize_text_field( $_POST['_subscription_period_interval'] ) ) ) { 
				update_post_meta ( $post_id, '_subscription_period_interval', sanitize_text_field( $_POST['_subscription_period_interval'] )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, '_subscription_period_interval', ''  );
			}

			if ( isset( $_POST['_subscription_period'] ) && ! empty( sanitize_text_field( $_POST['_subscription_period'] ) ) ) { 
				update_post_meta ( $post_id, '_subscription_period', sanitize_text_field( $_POST['_subscription_period'] )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, '_subscription_period', ''  );
			}

			if ( isset( $_POST['_subscription_length'] ) ) { 
				update_post_meta ( $post_id, '_subscription_length', sanitize_text_field( $_POST['_subscription_length'] )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, '_subscription_length', ''  );
			}

			if ( isset( $_POST['wc-donation-recurring'] ) && 'user' === sanitize_text_field( $_POST['wc-donation-recurring'] ) ) {
				if ( isset( $_POST['wc-donation-recurring-txt'] ) ) {
					update_post_meta ( $post_id, 'wc-donation-recurring-txt', sanitize_text_field( $_POST['wc-donation-recurring-txt'] ) );
				}
				update_post_meta ( $post_id, '_subscription_length', '1' );
				update_post_meta ( $post_id, '_subscription_period', 'day' );
				update_post_meta ( $post_id, '_subscription_period_interval', '1' );
			}

			if ( isset( $_POST['wc-donation-recurring'] ) && 'disabled' !== sanitize_text_field( $_POST['wc-donation-recurring'] ) ) {
				
				$interval = !empty( get_post_meta ( $post_id, '_subscription_period_interval', true  ) ) ? get_post_meta ( $post_id, '_subscription_period_interval', true  ) : '1';
				$period   = !empty( get_post_meta ( $post_id, '_subscription_period', true  ) ) ? get_post_meta ( $post_id, '_subscription_period', true  ) : 'day';
				$length   = !empty( get_post_meta ( $post_id, '_subscription_length', true  ) ) ? get_post_meta ( $post_id, '_subscription_length', true  ) : '1';
				$prod_id  = get_post_meta( $post_id, 'wc_donation_product', true );
								
			}

			//donation goal settings
			if ( isset( $_POST['wc-donation-goal-display-option'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-goal-display-option'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-goal-display-option', sanitize_text_field( $_POST['wc-donation-goal-display-option'] )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'wc-donation-goal-display-option', 'disabled'  );
			}

			if ( isset( $_POST['wc-donation-goal-display-type'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-goal-display-type'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-goal-display-type', sanitize_text_field( $_POST['wc-donation-goal-display-type'] )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'wc-donation-goal-display-type', 'fixed_amount'  );
			}

			if ( isset( $_POST['wc-donation-goal-fixed-amount-field'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-goal-fixed-amount-field'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-goal-fixed-amount-field', sanitize_text_field( $_POST['wc-donation-goal-fixed-amount-field'] )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'wc-donation-goal-fixed-amount-field', ''  );
			}

			if ( isset( $_POST['wc-donation-goal-fixed-initial-amount-field'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-goal-fixed-initial-amount-field'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-goal-fixed-initial-amount-field', sanitize_text_field( $_POST['wc-donation-goal-fixed-initial-amount-field'] )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'wc-donation-goal-fixed-initial-amount-field', '');
			}

			if ( isset( $_POST['wc-donation-goal-no-of-donation-field'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-goal-no-of-donation-field'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-goal-no-of-donation-field', sanitize_text_field( $_POST['wc-donation-goal-no-of-donation-field'] )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'wc-donation-goal-no-of-donation-field', ''  );
			}

			if ( isset( $_POST['wc-donation-goal-no-of-days-field'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-goal-no-of-days-field'] ) ) ) {
				$end_date = gmdate('Y-m-d', strtotime(sanitize_text_field( $_POST['wc-donation-goal-no-of-days-field'] )));
				$current_date = gmdate('Y-m-d');

				// Check if the end date is in the past
				if ($current_date <= $end_date) {
					// If the end date is valid, save it and calculate the total days
					update_post_meta($post_id, 'wc-donation-goal-no-of-days-field', sanitize_text_field($_POST['wc-donation-goal-no-of-days-field']));
					
					$date1 = new DateTime($current_date);  // Current date
					$date2 = new DateTime($end_date);      // End date
					$totalDays = $date2->diff($date1)->format('%a');  // Calculate the difference in days
					update_post_meta($post_id, 'wc-donation-goal-total-days', $totalDays);
				}

			} elseif ( ! isset($_POST['api_call']) ) {
				update_post_meta($post_id, 'wc-donation-goal-no-of-days-field', '');
				update_post_meta($post_id, 'wc-donation-goal-total-days', 0);
			}

			if ( isset( $_POST['wc-donation-goal-progress-bar-color'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-goal-progress-bar-color'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-goal-progress-bar-color', sanitize_text_field( $_POST['wc-donation-goal-progress-bar-color'] )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'wc-donation-goal-progress-bar-color', '333333'  );
			}

			if ( isset( $_POST['wc-donation-goal-display-donor-count'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-goal-display-donor-count'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-goal-display-donor-count', sanitize_text_field( $_POST['wc-donation-goal-display-donor-count'] )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'wc-donation-goal-display-donor-count', 'disabled'  );
			}

			if ( isset( $_POST['wc-donation-goal-close-form'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-goal-close-form'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-goal-close-form', sanitize_text_field( $_POST['wc-donation-goal-close-form'] )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'wc-donation-goal-close-form', ''  );
			}

			if ( isset( $_POST['wc-donation-goal-close-form-text'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-goal-close-form-text'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-goal-close-form-text', sanitize_text_field( $_POST['wc-donation-goal-close-form-text'] )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'wc-donation-goal-close-form-text', ''  );
			}

			//new settings for show progress bar on shop page.
			if ( isset( $_POST['wc-donation-progress-on-shop'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-progress-on-shop'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-progress-on-shop', sanitize_text_field( $_POST['wc-donation-progress-on-shop'] )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'wc-donation-progress-on-shop', ''  );
			}

			//new settings for show progress bar on widget.
			if ( isset( $_POST['wc-donation-progress-on-widget'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-progress-on-widget'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-progress-on-widget', sanitize_text_field( $_POST['wc-donation-progress-on-widget'] )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'wc-donation-progress-on-widget', ''  );
			}

			//donation cause settings
			if ( isset( $_POST['wc-donation-cause-display-option'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-cause-display-option'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-cause-display-option', sanitize_text_field( $_POST['wc-donation-cause-display-option'] )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'wc-donation-cause-display-option', 'hide'  );
			}

			if ( isset( $_POST['donation-cause-name'] ) && ! empty( array_map( 'sanitize_text_field', wp_unslash( $_POST['donation-cause-name'] ) ) ) ) { 
				update_post_meta ( $post_id, 'donation-cause-names', array_map( 'sanitize_text_field', wp_unslash( $_POST['donation-cause-name'] ) )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'donation-cause-names', ''  );
			}
			if ( isset( $_POST['donation-cause-desc'] ) && ! empty( array_map( 'sanitize_text_field', wp_unslash( $_POST['donation-cause-desc'] ) ) ) ) { 
				update_post_meta ( $post_id, 'donation-cause-desc', array_map( 'sanitize_text_field', wp_unslash( $_POST['donation-cause-desc'] ) )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'donation-cause-desc', ''  );
			}

			if ( isset( $_POST['donation-cause-img'] ) && ! empty( array_map( 'sanitize_text_field', wp_unslash( $_POST['donation-cause-img'] ) ) ) ) { 
				update_post_meta ( $post_id, 'donation-cause-img', array_map( 'sanitize_text_field', wp_unslash( $_POST['donation-cause-img'] ) )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'donation-cause-img', ''  );
			}

			if ( isset( $_POST['tributes'] ) && ! empty( array_map( 'sanitize_text_field', wp_unslash( $_POST['tributes'] ) ) ) ) { 
				update_post_meta ( $post_id, 'tributes', array_map( 'sanitize_text_field', wp_unslash( $_POST['tributes'] ) )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'tributes', ''  );
			}

			if ( isset( $_POST['wc-donation-dokan-seller'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-dokan-seller'] ) ) ) {
				update_post_meta ( $post_id, 'wc-donation-dokan-seller', sanitize_text_field( $_POST['wc-donation-dokan-seller'] )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'wc-donation-dokan-seller', ''  );
			}
			
			if ( isset( $_POST['wc-donation-product-vendor-seller'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-product-vendor-seller'] ) ) ) {
				update_post_meta ( $post_id, 'wc-donation-product-vendor-seller', sanitize_text_field( $_POST['wc-donation-product-vendor-seller'] )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'wc-donation-product-vendor-seller', ''  );
			}


			//Set Timer settings
			if ( isset( $_POST['wc-donation-setTimer-display-option'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-setTimer-display-option'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-setTimer-display-option', sanitize_text_field( $_POST['wc-donation-setTimer-display-option'] )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'wc-donation-setTimer-display-option', 'disabled'  );
			}

			if ( isset( $_POST['wc-donation-setTimer-time-format'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-setTimer-time-format'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-setTimer-time-format', sanitize_text_field( $_POST['wc-donation-setTimer-time-format'] )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'wc-donation-setTimer-time-format', '12'  );
			}

			if ( isset( $_POST['wc-donation-setTimer-time-type'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-setTimer-time-type'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-setTimer-time-type', sanitize_text_field( $_POST['wc-donation-setTimer-time-type'] )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'wc-donation-setTimer-time-type', 'daily'  );
			}

			if ( isset( $_POST['wc-donation-setTimer-display-after-end'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-setTimer-display-after-end'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-setTimer-display-after-end', sanitize_text_field( $_POST['wc-donation-setTimer-display-after-end'] )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'wc-donation-setTimer-display-after-end', 'hide'  );
			}

			if ( isset( $_POST['wc-donation-setTimer-display-end-message'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-setTimer-display-end-message'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-setTimer-display-end-message', sanitize_text_field( $_POST['wc-donation-setTimer-display-end-message'] )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'wc-donation-setTimer-display-end-message', ''  );
			}           

			if ( isset( $_POST['wc-donation-setTimer-time'] ) ) {
				$data = $_POST;
				if ( isset( $data['wc-donation-setTimer-time']['specific_day'] ) ) {
					foreach ( $data['wc-donation-setTimer-time']['specific_day'] as $index => $day_item ) {
						if ( isset( $data['wc-donation-setTimer-time']['specific_day'][$index]['switch'] ) ) {
							if ( empty( $data['wc-donation-setTimer-time']['specific_day'][$index]['start'] ) ) {
								$data['wc-donation-setTimer-time']['specific_day'][$index]['start'] = '00:00'; 
							}

							if ( empty( $data['wc-donation-setTimer-time']['specific_day'][$index]['end'] ) ) {
								$data['wc-donation-setTimer-time']['specific_day'][$index]['end'] = '23:59';
							}
						}
					}
				}

				if ( isset( $data['wc-donation-setTimer-time']['daily'] ) ) {
					if ( empty( $data['wc-donation-setTimer-time']['daily']['start'] ) ) {
						$data['wc-donation-setTimer-time']['daily']['start'] = '00:00'; 
					}

					if ( empty( $data['wc-donation-setTimer-time']['daily']['end'] ) ) {
						$data['wc-donation-setTimer-time']['daily']['end'] = '23:59';
					}
				}

				update_post_meta ( $post_id, 'wc-donation-setTimer-time', wp_unslash( $data['wc-donation-setTimer-time'] )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'wc-donation-setTimer-time', array() );
			}

			// Timer Display settings
			if ( isset( $_POST['wc-donation-timer-display'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-timer-display'] ) ) ) {
				update_post_meta( $post_id, 'wc-donation-timer-display', sanitize_text_field( $_POST['wc-donation-timer-display'] ) );
			} elseif ( ! isset($_POST['api_call']) ) {
				update_post_meta( $post_id, 'wc-donation-timer-display', 'disable' );
			}
			
			if ( isset( $_POST['wc-donation-timer-display-type'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-timer-display-type'] ) ) ) {
				update_post_meta( $post_id, 'wc-donation-timer-display-type', sanitize_text_field( $_POST['wc-donation-timer-display-type'] ) );
			} elseif ( ! isset($_POST['api_call']) ) {
				update_post_meta( $post_id, 'wc-donation-timer-display-type', 'flip_clock' );
			}

			if ( isset( $_POST['wc-donation-campaign-donor-list'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-campaign-donor-list'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-campaign-donor-list', sanitize_text_field( $_POST['wc-donation-campaign-donor-list'] )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'wc-donation-campaign-donor-list', 'no'  );
			}

			if ( isset( $_POST['wc-donation-anonymous-donor-list'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-anonymous-donor-list'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-anonymous-donor-list', sanitize_text_field( $_POST['wc-donation-anonymous-donor-list'] )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'wc-donation-anonymous-donor-list', 'no'  );
			}

			if ( isset( $_POST['wc-donation-donor-list-title'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-donor-list-title'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-donor-list-title', sanitize_text_field( $_POST['wc-donation-donor-list-title'] )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'wc-donation-donor-list-title', ''  );
			}

			if ( isset( $_POST['wc-donation-anonymous-donor-list-title'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-anonymous-donor-list-title'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-anonymous-donor-list-title', sanitize_text_field( $_POST['wc-donation-anonymous-donor-list-title'] )  );
			} elseif ( ! isset($_POST['api_call']) ) {
					update_post_meta ( $post_id, 'wc-donation-anonymous-donor-list-title', ''  );
			}


			// For Subscription Free Plugin (WP Swings) saving data
			if ( class_exists('Subscriptions_For_Woocommerce') && ! class_exists('WC_Subscriptions') ) {
	
					$prod_id      = get_post_meta($post_id, 'wc_donation_product', true);
					$RecurringDisp = !empty( get_post_meta ( $post_id, 'wc-donation-recurring', true  ) ) ? get_post_meta ( $post_id, 'wc-donation-recurring', true  ) : 'disabled';
					$wps_sfw_product = 'enabled' == $RecurringDisp ? 'yes' : 'no';
					$wps_sfw_user = 'user' == $RecurringDisp ? $RecurringDisp : 'no';

					wps_sfw_update_meta_data( $prod_id, '_wps_sfw_product', $wps_sfw_product );
					wps_sfw_update_meta_data( $prod_id, '_wps_sfw_users', $wps_sfw_user );

				if ( isset( $wps_sfw_product ) && ! empty( $wps_sfw_product ) ) {

					$wps_sfw_subscription_number = isset( $_POST['wps_sfw_subscription_number'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_sfw_subscription_number'] ) ) : '';
					$wps_sfw_subscription_interval = isset( $_POST['wps_sfw_subscription_interval'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_sfw_subscription_interval'] ) ) : '';
					$wps_sfw_subscription_expiry_number = isset( $_POST['wps_sfw_subscription_expiry_number'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_sfw_subscription_expiry_number'] ) ) : '';
					$wps_sfw_subscription_expiry_interval = isset( $_POST['wps_sfw_subscription_expiry_interval'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_sfw_subscription_expiry_interval'] ) ) : '';
					$wps_subscription_text = isset( $_POST['wc-donation-recurring-txt'] ) ? sanitize_text_field( wp_unslash( $_POST['wc-donation-recurring-txt'] ) ) : '';

					//wps_sfw_update_meta_data( $post_id, 'wc-donation-recurring-txt', !empty($recurring_text) ? $recurring_text : 'Is recurring' );

					wps_sfw_update_meta_data( $post_id, 'wps_sfw_subscription_number', $wps_sfw_subscription_number );
					wps_sfw_update_meta_data( $post_id, 'wps_sfw_subscription_interval', $wps_sfw_subscription_interval );
					wps_sfw_update_meta_data( $post_id, 'wps_sfw_subscription_expiry_number', $wps_sfw_subscription_expiry_number );
					wps_sfw_update_meta_data( $post_id, 'wps_sfw_subscription_expiry_interval', $wps_sfw_subscription_expiry_interval );

					wps_sfw_update_meta_data( $prod_id, 'wps_sfw_subscription_number', $wps_sfw_subscription_number );
					wps_sfw_update_meta_data( $prod_id, 'wps_sfw_subscription_interval', $wps_sfw_subscription_interval );
					wps_sfw_update_meta_data( $prod_id, 'wps_sfw_subscription_expiry_number', $wps_sfw_subscription_expiry_number );
					wps_sfw_update_meta_data( $prod_id, 'wps_sfw_subscription_expiry_interval', $wps_sfw_subscription_expiry_interval );

				}
			}


			//Set social share settings
			if ( isset( $_POST['wc-donation-social-share-display-option'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-social-share-display-option'] ) ) ) { 
				update_post_meta ( $post_id, 'wc-donation-social-share-display-option', sanitize_text_field( $_POST['wc-donation-social-share-display-option'] )  );
				
				if ( 'enabled' == sanitize_text_field( $_POST['wc-donation-social-share-display-option'] ) ) {
					
					$path = WC_DONATION_PATH . 'vendor/deps/qr-codes/' . get_post_meta( $post_id, 'wc-donation-social-share-qr-url', true );
					if ( empty( get_post_meta( $post_id, 'wc-donation-social-share-qr-url', true ) ) || ! file_exists( $path ) ) {
						require_once WC_DONATION_PATH . 'vendor/deps/phpqrcode/qrlib.php';
						$url = get_permalink( $prod_id );
						$tempDir = WC_DONATION_PATH . 'vendor/deps/qr-codes/';
						$fileName = uniqid('wc-donation-') . '.png';
						$filePath = $tempDir . $fileName;
						// Generate QR code
						QRcode::png( $url, $filePath, 'L', 10, 2 );
						update_post_meta ( $post_id, 'wc-donation-social-share-qr-url', $fileName );

					}
						
				}

			} elseif ( ! isset($_POST['api_call']) ) {
				update_post_meta ( $post_id, 'wc-donation-social-share-display-option', 'disabled'  );
			}

			// Update E-Card Templates Display Option
			if ( isset( $_POST['wc-donation-e-card-templates-display-option'] ) && ! empty( sanitize_text_field( $_POST['wc-donation-e-card-templates-display-option'] ) ) ) { 
				update_post_meta( $post_id, 'wc-donation-e-card-templates-display-option', sanitize_text_field( $_POST['wc-donation-e-card-templates-display-option'] ) );
			} elseif ( ! isset( $_POST['api_call'] ) ) {
				update_post_meta( $post_id, 'wc-donation-e-card-templates-display-option', 'disabled' );
			}

			if ( isset( $_POST['e-card-template-ids'] ) ) {
				$uploaded_template = sanitize_text_field( $_POST['e-card-template-ids'] );
				$uploaded_template = stripslashes( $uploaded_template );

				if ( filter_var( $uploaded_template, FILTER_VALIDATE_URL ) ) {
					$allowed_extensions = array( 'png', 'jpeg', 'jpg' );
					$file_extension = pathinfo( parse_url( $uploaded_template, PHP_URL_PATH ), PATHINFO_EXTENSION );

					if ( in_array( strtolower( $file_extension ), $allowed_extensions, true ) ) {
						update_post_meta( $post_id, 'wc-donation-e-card-template', $uploaded_template );
					} else {
						update_post_meta( $post_id, 'wc-donation-e-card-template', '' );
					}
				} else {
					update_post_meta( $post_id, 'wc-donation-e-card-template', '' );
				}
			}

			/**
			* Action.
			* 
			* @since 3.4.5
			*/
			do_action ('wc_donation_after_save_campaign_meta', $post_id);
		
		}
	}

	/**
	 * Adding setting for shop page / single page chexkbox
	 */
	public function wc_donation_meta() {
		
		add_meta_box ( 
			'wc_donation_meta__1',
			esc_html__( 'Display Donation Product', 'wc-donation' ),
			array( $this, 'render_wc_donation_meta__1_html' ),
			'wc-donation',
			'side',
			'high'
		);
		
		add_meta_box ( 
			'wc_donation_meta__2',
			esc_html__( 'Campaign Shortcode', 'wc-donation' ),
			array( $this, 'render_wc_donation_meta__2_html' ),
			'wc-donation',
			'side',
			'high'
		);
		
		add_meta_box ( 
			'wc_donation_meta__3',
			esc_html__( 'Campaign', 'wc-donation' ),
			array( $this, 'render_wc_donation_meta__3_html' ),
			'wc-donation', 
			'advanced', 
			'high'
		);

		add_meta_box ( 
			'wc_donation_meta__4',
			esc_html__( 'Feature Campaign', 'wc-donation' ),
			array( $this, 'render_wc_donation_meta__4_html' ),
			'wc-donation',
			'side',
			'high'
		);
	}

	/**
	 * Rendering HTMl for meta 1
	 */
	public function render_wc_donation_meta__1_html() {
		require_once WC_DONATION_PATH . 'includes/views/admin/display_donatoion_product.php';
	}

	/**
	 * Rendering HTMl for meta 2
	 */
	public function render_wc_donation_meta__2_html() {
		require_once WC_DONATION_PATH . 'includes/views/admin/campaign_shortcode.php';
	}
	
	/**
	 * Rendering HTMl for meta 3
	 */
	public function render_wc_donation_meta__3_html() {
		require_once WC_DONATION_PATH . 'includes/views/admin/single_campaign.php';
	}

	/**
	 * Rendering HTMl for meta 4
	 */
	public function render_wc_donation_meta__4_html() {
		require_once WC_DONATION_PATH . 'includes/views/admin/display_campaign_feature.php';
	}

	public static function get_product_by_campaign( $campaign_id = '' ) {

		$campaign_id = absint($campaign_id);
		
		if ( empty ( $campaign_id ) || 0 == $campaign_id ) {
			return;
		}

		$prod_id = get_post_meta( $campaign_id, 'wc_donation_product', true );
		$product = wc_get_product( $prod_id );

		if ( $product instanceof WC_Product ) {         

			$product_name      = $product->get_name();
			$product_type      = $product->get_type();
			$product_slug      = $product->get_slug();
			$product_permalink = get_permalink( $prod_id );

			$campaign_name            = get_the_title( $campaign_id );
			$campaign_slug            = get_post_field( 'post_name', $campaign_id );
			$amountDisp               = !empty( get_post_meta ( $campaign_id, 'wc-donation-amount-display-option', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-amount-display-option', true  ) : 'both';
			$dispCustomType           = !empty( get_post_meta ( $campaign_id, 'wc-donation-custom-type-option', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-custom-type-option', true  ) : 'custom_range';
			$freeAmountPlaceHolder    = !empty( get_post_meta ( $campaign_id, 'free-amount-ph', true  ) ) ? get_post_meta ( $campaign_id, 'free-amount-ph', true  ) : '';
			$freeMinAmount            = !empty( get_post_meta ( $campaign_id, 'free-min-amount', true  ) ) ? get_post_meta ( $campaign_id, 'free-min-amount', true  ) : 0; 
			$freeMaxAmount            = !empty( get_post_meta ( $campaign_id, 'free-max-amount', true  ) ) ? get_post_meta ( $campaign_id, 'free-max-amount', true  ) : 1000; 
			$predAmount               = !empty( get_post_meta ( $campaign_id, 'pred-amount', false  ) ) ? get_post_meta ( $campaign_id, 'pred-amount', false  ) : array();            
			$predLabel                = !empty( get_post_meta ( $campaign_id, 'pred-label', false  ) ) ? get_post_meta ( $campaign_id, 'pred-label', false  ) : array();
			$DonationDispType         = !empty( get_post_meta ( $campaign_id, 'wc-donation-display-donation-type', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-display-donation-type', true  ) : 'select'; 
			$currencyPos              = !empty( get_post_meta ( $campaign_id, 'wc-donation-currency-position', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-currency-position', true  ) : 'before'; 
			$donationDescription      = !empty( get_post_meta ( $campaign_id, 'wc-donation-description', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-description', true  ) : ''; 
			$donationShortDescription = !empty( get_post_meta ( $campaign_id, 'wc-donation-short-description', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-short-description', true  ) : ''; 
			$donationTitle            = !empty( get_post_meta ( $campaign_id, 'wc-donation-title', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-title', true  ) : ''; 
			$donationBtnTxt           = !empty( get_post_meta ( $campaign_id, 'wc-donation-button-text', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-button-text', true  ) : 'Donate'; 
			$donationBtnTxtColor      = !empty( get_post_meta ( $campaign_id, 'wc-donation-button-text-color', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-button-text-color', true  ) : 'FFFFFF'; 
			$donationBtnBgColor       = !empty( get_post_meta ( $campaign_id, 'wc-donation-button-bg-color', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-button-bg-color', true  ) : '333333';

			$tributeColor        = !empty( get_post_meta ( $campaign_id, 'wc-donation-tribute-color', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-tribute-color', true  ) : '333333';
			$currencyBgColor     = !empty( get_post_meta ( $campaign_id, 'wc-donation-currency-bg-color', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-currency-bg-color', true  ) : '333333';
			$currencySymbolColor = !empty( get_post_meta ( $campaign_id, 'wc-donation-currency-symbol-color', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-currency-symbol-color', true  ) : 'FFFFFF';

			$RecurringDisp  = !empty( get_post_meta ( $campaign_id, 'wc-donation-recurring', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-recurring', true  ) : 'disabled';
			$recurring_text = !empty( get_post_meta ( $campaign_id, 'wc-donation-recurring-txt', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-recurring-txt', true  ) : '';
			$interval       = !empty( get_post_meta ( $campaign_id, '_subscription_period_interval', true  ) ) ? get_post_meta ( $campaign_id, '_subscription_period_interval', true  ) : '1';
			$period         = !empty( get_post_meta ( $campaign_id, '_subscription_period', true  ) ) ? get_post_meta ( $campaign_id, '_subscription_period', true  ) : 'day';
			$length         = !empty( get_post_meta ( $campaign_id, '_subscription_length', true  ) ) ? get_post_meta ( $campaign_id, '_subscription_length', true  ) : '1';
			$dispSinglePage = !empty( get_post_meta ( $campaign_id, 'wc-donation-disp-single-page', true ) ) ? get_post_meta ( $campaign_id, 'wc-donation-disp-single-page', true ) : 'no';
			$dispShopPage   = !empty( get_post_meta ( $campaign_id, 'wc-donation-disp-shop-page', true ) ) ? get_post_meta ( $campaign_id, 'wc-donation-disp-shop-page', true ) : 'no'; 

			//donation goal
			$goalDisp           = !empty( get_post_meta ( $campaign_id, 'wc-donation-goal-display-option', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-goal-display-option', true  ) : 'disabled'; 
			$goalType           = !empty( get_post_meta ( $campaign_id, 'wc-donation-goal-display-type', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-goal-display-type', true  ) : 'fixed_amount';
			$fixedAmount        = !empty( get_post_meta ( $campaign_id, 'wc-donation-goal-fixed-amount-field', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-goal-fixed-amount-field', true  ) : '';
			$fixedInitialAmount = !empty( get_post_meta ( $campaign_id, 'wc-donation-goal-fixed-initial-amount-field', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-goal-fixed-initial-amount-field', true  ) : '';
			$no_of_donation     = !empty( get_post_meta ( $campaign_id, 'wc-donation-goal-no-of-donation-field', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-goal-no-of-donation-field', true  ) : ''; 
			$no_of_days         = !empty( get_post_meta ( $campaign_id, 'wc-donation-goal-no-of-days-field', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-goal-no-of-days-field', true  ) : ''; 
			$total_days         = !empty( get_post_meta ( $campaign_id, 'wc-donation-goal-total-days', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-goal-total-days', true  ) : 0;
			$progressBarColor   = !empty( get_post_meta ( $campaign_id, 'wc-donation-goal-progress-bar-color', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-goal-progress-bar-color', true  ) : '333333'; 
			$dispDonorCount     = !empty( get_post_meta ( $campaign_id, 'wc-donation-goal-display-donor-count', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-goal-display-donor-count', true  ) : 'disabled'; 
			$closeForm          = !empty( get_post_meta ( $campaign_id, 'wc-donation-goal-close-form', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-goal-close-form', true  ) : ''; 
			$message            = !empty( get_post_meta ( $campaign_id, 'wc-donation-goal-close-form-text', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-goal-close-form-text', true  ) : ''; 
			$progressOnShop     = !empty( get_post_meta ( $campaign_id, 'wc-donation-progress-on-shop', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-progress-on-shop', true  ) : '';
			$progressOnWidget   = !empty( get_post_meta ( $campaign_id, 'wc-donation-progress-on-widget', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-progress-on-widget', true  ) : '';
			//donation cause
			$causeDisp  = !empty( get_post_meta ( $campaign_id, 'wc-donation-cause-display-option', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-cause-display-option', true  ) : 'hide';
			$causeNames = !empty( get_post_meta ( $campaign_id, 'donation-cause-names', false  ) ) ? get_post_meta ( $campaign_id, 'donation-cause-names', false  ) : array();
			$causeDesc  = !empty( get_post_meta ( $campaign_id, 'donation-cause-desc', false  ) ) ? get_post_meta ( $campaign_id, 'donation-cause-desc', false  ) : array();
			$causeImg   = !empty( get_post_meta ( $campaign_id, 'donation-cause-img', false  ) ) ? get_post_meta ( $campaign_id, 'donation-cause-img', false  ) : array();

			//Donation tributes
			$tributes = !empty( get_post_meta ( $campaign_id, 'tributes', true  ) ) ? get_post_meta ( $campaign_id, 'tributes', true  ) : array();

			//Donation Ordering
			$ordering = !empty( get_post_meta ( $campaign_id, 'ordering', true  ) ) ? get_post_meta ( $campaign_id, 'ordering', true  ) : array();

			//Set Timer
			$setTimerDisp         = !empty( get_post_meta ( $campaign_id, 'wc-donation-setTimer-display-option', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-setTimer-display-option', true  ) : 'disabled';
			$timeFormat           = !empty( get_post_meta ( $campaign_id, 'wc-donation-setTimer-time-format', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-setTimer-time-format', true  ) : '12';
			$timeType             = !empty( get_post_meta ( $campaign_id, 'wc-donation-setTimer-time-type', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-setTimer-time-type', true  ) : 'daily';
			$displayAfterTimeEnds = !empty( get_post_meta ( $campaign_id, 'wc-donation-setTimer-display-after-end', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-setTimer-display-after-end', true  ) : 'hide';
			$setTimerEndMessage   = !empty( get_post_meta ( $campaign_id, 'wc-donation-setTimer-display-end-message', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-setTimer-display-end-message', true  ) : '';
			$timings              = !empty( get_post_meta ( $campaign_id, 'wc-donation-setTimer-time', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-setTimer-time', true  ) : array();

			
			//Donor Settings
			$campDonorList           = !empty( get_post_meta ( $campaign_id, 'wc-donation-campaign-donor-list', true ) ) ? get_post_meta ( $campaign_id, 'wc-donation-campaign-donor-list', true ) : 'no';
			$anonDonorList           = !empty( get_post_meta ( $campaign_id, 'wc-donation-anonymous-donor-list', true ) ) ? get_post_meta ( $campaign_id, 'wc-donation-anonymous-donor-list', true ) : 'no';
			$donarListTitle          = !empty( get_post_meta ( $campaign_id, 'wc-donation-donor-list-title', true ) ) ? get_post_meta ( $campaign_id, 'wc-donation-donor-list-title', true ) : '';
			$anonymousDonarListTitle = !empty( get_post_meta ( $campaign_id, 'wc-donation-anonymous-donor-list-title', true ) ) ? get_post_meta ( $campaign_id, 'wc-donation-anonymous-donor-list-title', true ) : '';
			$social_share =  ! empty( get_post_meta( $campaign_id, 'wc-donation-social-share-display-option', true ) ) && 'enabled' == get_post_meta( $campaign_id, 'wc-donation-social-share-display-option', true ) ? true : false;
			$show_e_card_templates = !empty( get_post_meta ( $campaign_id, 'wc-donation-e-card-templates-display-option', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-e-card-templates-display-option', true  ) : 'disabled';
			/**
			* Action.
			* 
			* @since 3.4.5
			*/
			$arr = apply_filters ( 'wc_donation_get_campaign', array(
				'campaign' => array(
					'campaign_id' => $campaign_id,
					'campaign_slug' => $campaign_slug,
					'campaign_name' => $campaign_name,
					'amount_display' => $amountDisp,
					'dispCustomType' => $dispCustomType,
					'freeAmountPlaceHolder' => $freeAmountPlaceHolder,
					'freeMinAmount' => $freeMinAmount,
					'freeMaxAmount' => $freeMaxAmount,
					'predAmount' => $predAmount,
					'predLabel' => $predLabel,
					'DonationDispType' => $DonationDispType,
					'currencyPos' => $currencyPos,
					'donationTitle' => $donationTitle,
					'donationBtnTxt' => $donationBtnTxt,
					'donationBtnTxtColor' => $donationBtnTxtColor,
					'donationBtnBgColor' => $donationBtnBgColor,
					'currencySymbolColor' => $currencySymbolColor,
					'currencyBgColor' => $currencyBgColor,
					'tributeColor' => $tributeColor,
					'donationDescription' => $donationDescription,
					'donationShortDescription' => $donationShortDescription,
					'RecurringDisp' => $RecurringDisp,
					'recurringText' => $recurring_text,
					'causeDisp' => $causeDisp,
					'causeDesc' => $causeDesc,
					'causeImg' => $causeImg,
					'causeNames' => $causeNames,
					'interval' => $interval,
					'period' => $period,
					'length' => $length,
					'tributes' => $tributes,
					'ordering' => $ordering,
					'social_share' => $social_share,
					'show_e_card_templates' => $show_e_card_templates,
				),
				'goal' => array(
					'display' => $goalDisp,
					'type' => $goalType,
					'fixed_amount' => $fixedAmount,
					'fixed_initial_amount' => $fixedInitialAmount,
					'no_of_donation' => $no_of_donation,
					'no_of_days' => $no_of_days,
					'total_days' => $total_days,
					'progress_bar_color' => $progressBarColor,
					'display_donor_count' => $dispDonorCount,
					'form_close' => $closeForm,
					'message' => $message,
					'show_on_widget' => $progressOnWidget,
					'show_on_shop' => $progressOnShop,
				),
				'timer' => array(
					'display' => $setTimerDisp,
					'format'  => $timeFormat,
					'time_type' => $timeType,
					'display_end' => $displayAfterTimeEnds,
					'display_message' => $setTimerEndMessage,
					'timing' => $timings,
				),
				'product' => array(
					'product_id' => $prod_id,
					'product_name' => $product_name,
					'product_type' => $product_type,
					'product_slug' => $product_slug,
					'product_permalink' => $product_permalink,
					'is_single' => $dispSinglePage,
					'is_shop' => $dispShopPage,
				),
				'donor' => array(
					'donor_list' => $campDonorList,
					'donor_title' => $donarListTitle,
					'anonymous_list' => $anonDonorList,
					'anonymous_title' => $anonymousDonarListTitle,
				),
				
				), $campaign_id );

			$object = (object) $arr;

			return $object;
		}
	}

	/* public function woocommerce_donor_tab_tabs_callback( $tabs ) {
		$product_id = get_the_ID();
		$campaign_id = get_post_meta( $product_id, 'wc_donation_campaign', true );

		$tribute_wall = get_post_meta( $campaign_id, 'tribute_wall', true );
		
		if ( empty( $campaign_id ) || empty( $tribute_wall ) ) {
			return array();
		}   

		unset( $tabs['reviews'] );

		$tabs['wc_donation_campaign_donor_wall'] = array( 
			// translators: %s to display count
			'title'     => esc_html__( 'Donor Wall', 'wc-donation' ),
			'priority'  => 15,
			'callback'  => array( $this, 'wc_donation_donor_wall' ),
		);

		return $tabs;
	} */

	public function wc_donation_donor_wall( $campaign_id ) {
		$product_id  = get_the_ID();
		$campaign_id = get_post_meta( $product_id, 'wc_donation_campaign', true );
		$object      = self::get_product_by_campaign($campaign_id);

		if ( isset( $object->donor['donor_list'] ) && 'yes' === $object->donor['donor_list'] ) {
			$_title = $object->donor['donor_title'];
			require_once WC_DONATION_PATH . 'includes/views/frontend/donor_wall/donor_list.php';
		}

		if ( isset( $object->donor['anonymous_list'] ) && 'yes' === $object->donor['anonymous_list'] ) {
			$_title = $object->donor['anonymous_title'];
			require_once WC_DONATION_PATH . 'includes/views/frontend/donor_wall/anonymous_list.php';
		}
	}
	public function woocommerce_product_tabs_callback( $tabs ) {
		$product_id   = get_the_ID();
		$campaign_id  = get_post_meta($product_id, 'wc_donation_campaign', true);
		$tribute_wall = get_post_meta($campaign_id, 'tribute_wall', true);
		$object = self::get_product_by_campaign($campaign_id);
		unset($tabs['description']);
		unset($tabs['reviews']);
		// Ensure the campaign ID and tribute wall are not empty
		if (!empty($campaign_id) && !empty($tribute_wall)) {
			unset($tabs['reviews']);
			
			if ('yes' == get_option('wc-donation-tributes') && 'yes' == get_option('wc-donation-messages')) {
				$tabs['wc_donation_campaign_tribute_wall'] = array(
					// translators: %s to tribute wall
					'title' => sprintf( esc_html__( 'Tribute Wall (%s)', 'wc-donation' ), count( $tribute_wall ) ),
					'priority' => 10,
					'callback' => array( $this, 'wc_donation_tribute_wall' ),
				);
			}
			
			if ( 'yes' == get_option('wc-donation-donor-wall') && ( 'yes' == $object->donor['donor_list'] || 'yes' == $object->donor['anonymous_list'] ) ) {
				$tabs['wc_donation_campaign_donor_wall'] = array(
					'title' => esc_html__('Donor Wall', 'wc-donation'),
					'priority' => 15,
					'callback' => array( $this, 'wc_donation_donor_wall' ),
				);
			}
		}

		return $tabs;
	}
	/* public function woocommerce_product_tabs_callback( $tabs ) {

		$product_id = get_the_ID();
		$campaign_id = get_post_meta( $product_id, 'wc_donation_campaign', true );

		$tribute_wall = get_post_meta( $campaign_id, 'tribute_wall', true );
		
		if ( empty( $campaign_id ) || empty( $tribute_wall ) ) {
			return array();
		}

		unset( $tabs['reviews'] );

		$tabs['wc_donation_campaign_tribute_wall'] = array( 
			// translators: %s to display count
			'title'     => esc_html__( sprintf( 'Tribute Wall (%s)', count( $tribute_wall ) ), 'wc-donation' ),
			'priority'  => 10,
			'callback'  => array( $this, 'wc_donation_tribute_wall' ),
		);

		return $tabs;
	} */

	public function wc_donation_tribute_wall() {
		$product_id  = get_the_ID();
		$campaign_id = get_post_meta( $product_id, 'wc_donation_campaign', true );

		$tribute_wall = get_post_meta( $campaign_id, 'tribute_wall', true );
		
		$guest_user = false;

		if ( empty( $campaign_id ) || empty( $tribute_wall ) ) {
			return;
		}

		$count = count( $tribute_wall );

		include_once WC_DONATION_PATH . 'includes/views/frontend/wc-donation-tribute-wall.php';
	}

	public function wc_conditional_fee_remove_donation_product_amount( $subtotal, $fee_type ) {
		$subtotal = WC()->cart->subtotal_ex_tax;
		
		if ( empty( WC()->cart->cart_contents ) ) {
			return $subtotal;
		}
		$donation_amount = 0;
		foreach ( WC()->cart->cart_contents as $item ) {
			if ( isset( $item['campaign_id'] ) ) {
				$donation_amount += ( $item['custom_price'] * $item['quantity'] );
			}
		}
		return $subtotal - $donation_amount;
	}
}

new WcdonationCampaignSetting();
