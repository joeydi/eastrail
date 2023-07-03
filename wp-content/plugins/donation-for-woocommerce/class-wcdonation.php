<?php
/**
 * Plugin Name: Donation For Woocommerce
 * Description: Donation For WooCommerce extension enables you to add “donation” as a regular WooCommerce product without any efforts to create it.
 * Version: 3.4.6.2
 * Author: wpexpertsio
 * Author URI: http://wpexpert.io/
 * Developer: wpexpertsio
 * Developer URI: https://wpexperts.io/
 * Text Domain: wc-donation
 * Domain Path: /languages
 * Woo: 5573073:0b2656d08c34d80d1d9c7523887d65f3
 * WC requires at least: 3.0
 * WC tested up to: 6.9.4
 *
 * @package donation
 */

if ( ! defined( 'ABSPATH' ) ) {

	exit;
}
define( 'WC_DONATION_URL', plugin_dir_url( __FILE__ ) );
define( 'WC_DONATION_PATH', plugin_dir_path( __FILE__ ) );
define( 'WC_DONATION_FILE', __FILE__ );
define( 'WC_DONATION_VERSION', '3.4.6.2' );
define( 'WC_DONATION_SLUG', 'wc-donation' );

/**
 * Main class
 */

if ( ! class_exists( 'WcDonation' ) ) :
	/**
	 * Class WcDonation
	 * Check dependencies and include files .
	 */
	class WcDonation {
		/**
		 * Construct
		 */
		public function __construct() {	

			/**
			 * Plugin need woocomerce plugin
			 */
			if ( is_multisite() ) {
				/**
				* Filter.
				* 
				* @since 3.4.5
				*/
				$active_plugin = apply_filters( 'active_plugins', get_site_option( 'active_sitewide_plugins' ) );
			}

			/**
			* Filter.
			* 
			* @since 3.4.5
			*/
			if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) || isset( $active_plugin['woocommerce/woocommerce.php'] ) ) {				

				add_action( 'wp_loaded', array( $this, 'wc_donation_backward_compatibility') );
				add_action( 'plugins_loaded', array( $this, 'wc_donation_load_textdomain' ) );
				add_action( 'admin_notices', array( $this, 'wc_donation_new_ver_plugin_notice' ) );
				add_action( 'admin_footer', array( $this, 'wc_donation_remove_notice_script' ) );
				add_action( 'admin_init', array( $this, 'wc_donation_plugin_notice_dismissed' ) );
				add_action( 'before_woocommerce_init', array( $this, 'woo_hpos_incompatibility' ) );
				$this->includes();

			} else {

				/**
				 * Notice for admin
				 */
				add_action( 'admin_notices', array( $this, 'inactive_plugin_notice' ) );
			}
		}

		public function woo_hpos_incompatibility() {
			if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			}
		}

		public function wc_donation_plugin_notice_dismissed() {
			
			$user_id = get_current_user_id();
			if ( isset( $_GET['wc-donation-notice-dismissed'] ) ) {
				add_user_meta( $user_id, 'wc_donation_plugin_notice_dismissed', 'true', true );
			}
		}

		public function wc_donation_new_ver_plugin_notice() {
			$class     = 'notice notice-warning is-dismissible wc-donation-is-dismissible';
			$headline  = __( 'Donation for WooCommerce version ' . WC_DONATION_VERSION, 'wc-donation' );
			$message   = __( 'It is to inform you that we have changed the mechanism of donation and orders amount calculation for performance enhancement. If you see mixed or confusing data, please go to setting page for synchronization. After synchronization, your data will display fine.', 'wc-donation' );
			$setting_text = 'Settings';
			$url = admin_url('edit.php?post_type=wc-donation&page=general#wc_donation_sync_data');

			$user_id = get_current_user_id();

			if ( ! get_user_meta( $user_id, 'wc_donation_plugin_notice_dismissed' ) ) {
				printf( '<div class="%1$s"><h2>%2$s</h2><p style="font-size:15px">%3$s <a href="%4$s">%5$s</a></p></div>', esc_attr( $class ), esc_html( $headline ), esc_html( $message ), esc_url( $url ), esc_html( $setting_text ) );
			}
		}

		public function wc_donation_remove_notice_script() {
			?>
			<script>
				jQuery(document).on( 'click', '.wc-donation-is-dismissible button', function(){
					window.location.href = '?wc-donation-notice-dismissed';			
				});
			</script>
			<?php
		}

		public function wc_donation_load_textdomain() {
			load_plugin_textdomain( 'wc-donation', false, basename( dirname( __FILE__ ) ) . '/languages/' );
		}

		public function wc_donation_backward_compatibility () {
			
			// last updated on 02 Nov 2020 - Continue from here!
			if ( get_option('wc_donation_backward_comp') == false ) {

				$products = wc_get_products( array( 'type' => 'simple', 'limit' => -1, 'meta_key' => 'is_wc_donation', 'meta_value' => 'donation' ) );
				if ( !empty($products) && count($products) > 0 ) {

					$cart_product = get_option('wc-donation-product');
					$widget_product = get_option('wc-donation-widget-product');
					$roundoff_product = get_option('wc-donation-round-product');

					foreach ( $products as $product ) {
						
						$prod_id = $product->get_id();						

						// check for cart and checkout page
						if ( $prod_id == $cart_product ) {

							$title = $product->get_name();
							$campaign_args = array (
								'post_title' => $title,
								'post_type' => 'wc-donation',
								'post_status' => 'publish',
								'post_name' => sanitize_title( 'WC Donation - ' . $title )
							);					
							$campaign_id = wp_insert_post( $campaign_args );

							if ( !empty($campaign_id) ) {

								//backward compatibility before select product id now campaign id
								update_option('wc-donation-product', $campaign_id);
								update_option('wc-donation-checkout-product', $campaign_id);

								//add campaign to cart
								update_option('wc-donation-cart-product', $campaign_id  );

								//adding product id into camapaign as meta value
								update_post_meta( $campaign_id, 'wc_donation_product', $prod_id  );

								//adding campaign id into product as meta value two way sync
								update_post_meta( $prod_id, 'wc_donation_campaign', $campaign_id  );

								// make product hide from shop
								wp_set_object_terms( $prod_id, array( 'exclude-from-catalog', 'exclude-from-search' ), 'product_visibility' );
								update_post_meta( $prod_id, '_visibility', '_visibility_hidden' );
								update_post_meta( $prod_id, '_price', '0' );
								update_post_meta( $prod_id, '_tax_status', 'none' );
								update_post_meta( $prod_id, '_sku', $prod_id );

								//set product attachment_id to campaign attachment id
								$attachment_id = get_post_thumbnail_id( $prod_id );
								if ( $attachment_id ) {
									set_post_thumbnail( $campaign_id, $attachment_id );
								}

								//saving campaign meta values
								update_post_meta ( $campaign_id, 'wc-donation-tablink', 'tab-1'  );
								update_post_meta ( $campaign_id, 'wc-donation-disp-single-page', 'no'  );
								update_post_meta ( $campaign_id, 'wc-donation-disp-shop-page', 'no'  );

								$donation_disp_type = get_option('wc-donation-display-donation');
								if ( '' != $donation_disp_type ) {
									update_post_meta ( $campaign_id, 'wc-donation-amount-display-option', $donation_disp_type  );
								} else {
									update_post_meta ( $campaign_id, 'wc-donation-amount-display-option', 'both'  );
								}

								update_post_meta ( $campaign_id, 'free-min-amount', ''  );
								update_post_meta ( $campaign_id, 'free-max-amount', ''  );

								$donation_values = get_option( 'wc-donation-donation-values' );
								if ( ! empty( $donation_values ) && count($donation_values) > 0 ) { 
									update_post_meta ( $campaign_id, 'pred-amount', $donation_values );
									update_post_meta ( $campaign_id, 'pred-label', $donation_values );
								} else {
									update_post_meta ( $campaign_id, 'pred-amount', ''  );
									update_post_meta ( $campaign_id, 'pred-label', ''  );
								}

								$where_currency_symbole = get_option( 'wc-donation-currency-symbol' );
								if ( !empty( $where_currency_symbole ) ) {
									update_post_meta ( $campaign_id, 'wc-donation-currency-position', $where_currency_symbole );
								} else {
									update_post_meta ( $campaign_id, 'wc-donation-currency-position', 'before'  );
								}

								$donation_label  = !empty( esc_attr( get_option( 'wc-donation-field-label' ))) ? esc_attr( get_option( 'wc-donation-field-label' )) : 'Donation';
								update_post_meta ( $campaign_id, 'wc-donation-title', $donation_label );

								$donation_button_text  = !empty( esc_attr( get_option( 'wc-donation-button-text' ))) ? esc_attr( get_option( 'wc-donation-button-text' )) : 'Donate';
								update_post_meta ( $campaign_id, 'wc-donation-button-text', $donation_button_text  );

								$donation_button_color  = !empty( esc_attr( get_option( 'wc-donation-button-color' ))) ? esc_attr( get_option( 'wc-donation-button-color' )) : 'd5d5d5';
								update_post_meta ( $campaign_id, 'wc-donation-button-bg-color', $donation_button_color  );

								$donation_button_text_color  = !empty( esc_attr( get_option( 'wc-donation-button-text-color' ))) ? esc_attr( get_option( 'wc-donation-button-text-color' )) : '000000';
								update_post_meta ( $campaign_id, 'wc-donation-button-text-color', $donation_button_text_color  );								
							}
						}

						// check for widget and shortcode page
						if ( $prod_id == $widget_product) {
							$title = $product->get_name();
							$campaign_args = array (
								'post_title' => $title,
								'post_type' => 'wc-donation',
								'post_status' => 'publish',
								'post_name' => sanitize_title( 'WC Donation - ' . $title )
							);					
							$campaign_id = wp_insert_post( $campaign_args );

							if ( !empty($campaign_id) ) {
								
								//adding product id into camapaign as meta value
								update_post_meta( $campaign_id, 'wc_donation_product', $prod_id  );

								//adding campaign id into product as meta value two way sync
								update_post_meta( $prod_id, 'wc_donation_campaign', $campaign_id  );

								// make product hide from shop
								wp_set_object_terms( $prod_id, array( 'exclude-from-catalog', 'exclude-from-search' ), 'product_visibility' );
								update_post_meta( $prod_id, '_visibility', '_visibility_hidden' );
								update_post_meta( $prod_id, '_price', '0' );
								update_post_meta( $prod_id, '_tax_status', 'none' );
								update_post_meta( $prod_id, '_', 'wc-donate-' . $prod_id );

								//set product attachment_id to campaign attachment id
								$attachment_id = get_post_thumbnail_id( $prod_id );
								if ( $attachment_id ) {
									set_post_thumbnail( $campaign_id, $attachment_id );
								}

								//saving campaign meta values
								update_post_meta ( $campaign_id, 'wc-donation-tablink', 'tab-1'  );
								update_post_meta ( $campaign_id, 'wc-donation-disp-single-page', 'no'  );
								update_post_meta ( $campaign_id, 'wc-donation-disp-shop-page', 'no'  );

								$display_donation_type = get_option( 'wc-donation-widget-display-donation' );
								if ( '' != $display_donation_type ) {
									update_post_meta ( $campaign_id, 'wc-donation-amount-display-option', $display_donation_type  );
								} else {
									update_post_meta ( $campaign_id, 'wc-donation-amount-display-option', 'both'  );
								}

								$display_donation_show_type = get_option( 'wc-donation-widget-display-donation-type' );
								if ( '' != $display_donation_show_type ) {
									update_post_meta ( $campaign_id, 'wc-donation-display-donation-type', $display_donation_show_type  );
								} else {
									update_post_meta ( $campaign_id, 'wc-donation-display-donation-type', 'select'  );
								}

								update_post_meta ( $campaign_id, 'free-min-amount', ''  );
								update_post_meta ( $campaign_id, 'free-max-amount', ''  );

								$donation_values = get_option( 'wc-donation-widget-donation-values' );
								if ( ! empty( $donation_values ) && count($donation_values) > 0 ) { 
									update_post_meta ( $campaign_id, 'pred-amount', $donation_values );
									update_post_meta ( $campaign_id, 'pred-label', $donation_values );
								} else {
									update_post_meta ( $campaign_id, 'pred-amount', ''  );
									update_post_meta ( $campaign_id, 'pred-label', ''  );
								}

								$where_currency_symbole = get_option( 'wc-donation-widget-currency-symbol' );
								if ( !empty( $where_currency_symbole ) ) {
									update_post_meta ( $campaign_id, 'wc-donation-currency-position', $where_currency_symbole );
								} else {
									update_post_meta ( $campaign_id, 'wc-donation-currency-position', 'before'  );
								}

								$donation_label  = !empty( esc_attr( get_option( 'wc-donation-widget-field-label' ))) ? esc_attr( get_option( 'wc-donation-widget-field-label' )) : 'Donation';
								update_post_meta ( $campaign_id, 'wc-donation-title', $donation_label );

								$donation_button_text  = !empty( esc_attr( get_option( 'wc-donation-widget-button-text' ))) ? esc_attr( get_option( 'wc-donation-widget-button-text' )) : 'Donate';
								update_post_meta ( $campaign_id, 'wc-donation-button-text', $donation_button_text  );

								$donation_button_color  = !empty( esc_attr( get_option( 'wc-donation-widget-button-color' ))) ? esc_attr( get_option( 'wc-donation-widget-button-color' )) : 'd5d5d5';
								update_post_meta ( $campaign_id, 'wc-donation-button-bg-color', $donation_button_color  );

								$donation_button_text_color  = !empty( esc_attr( get_option( 'wc-donation-widget-button-text-color' ))) ? esc_attr( get_option( 'wc-donation-widget-button-text-color' )) : '000000';
								update_post_meta ( $campaign_id, 'wc-donation-button-text-color', $donation_button_text_color  );								
							}
						}

						// check for roundoff
						if ( $prod_id == $roundoff_product) {
							$title = $product->get_name();
							$campaign_args = array (
								'post_title' => $title,
								'post_type' => 'wc-donation',
								'post_status' => 'publish',
								'post_name' => sanitize_title( 'WC Donation - ' . $title )
							);					
							$campaign_id = wp_insert_post( $campaign_args );

							if ( !empty($campaign_id) ) {

								//backward compatibility before select product id now campaign id
								update_option('wc-donation-round-product', $campaign_id);

								$roundoff_switch = get_option('wc-donation-round-switch');
								update_option('wc-donation-on-round', $roundoff_switch);

								//adding product id into camapaign as meta value
								update_post_meta( $campaign_id, 'wc_donation_product', $prod_id  );

								//adding campaign id into product as meta value two way sync
								update_post_meta( $prod_id, 'wc_donation_campaign', $campaign_id  );

								//add campaign to cart
								update_option('wc-donation-round-product', $campaign_id  );

								// make product hide from shop
								wp_set_object_terms( $prod_id, array( 'exclude-from-catalog', 'exclude-from-search' ), 'product_visibility' );
								update_post_meta( $prod_id, '_visibility', '_visibility_hidden' );
								update_post_meta( $prod_id, '_price', '0' );
								update_post_meta( $prod_id, '_tax_status', 'none' );
								update_post_meta( $prod_id, '_sku', $prod_id );

								//set product attachment_id to campaign attachment id
								$attachment_id = get_post_thumbnail_id( $prod_id );
								if ( $attachment_id ) {
									set_post_thumbnail( $campaign_id, $attachment_id );
								}

								//saving campaign meta values
								update_post_meta ( $campaign_id, 'wc-donation-tablink', 'tab-1'  );
								update_post_meta ( $campaign_id, 'wc-donation-disp-single-page', 'no'  );
								update_post_meta ( $campaign_id, 'wc-donation-disp-shop-page', 'no'  );
								update_post_meta ( $campaign_id, 'wc-donation-amount-display-option', 'free-value'  );
								update_post_meta ( $campaign_id, 'free-min-amount', ''  );
								update_post_meta ( $campaign_id, 'free-max-amount', ''  );
								update_post_meta ( $campaign_id, 'pred-amount', ''  );
								update_post_meta ( $campaign_id, 'pred-label', ''  );

								$where_currency_symbole = get_option( 'wc-donation-round-currency-symbol' );
								if ( !empty( $where_currency_symbole ) ) {
									update_post_meta ( $campaign_id, 'wc-donation-currency-position', $where_currency_symbole );
								} else {
									update_post_meta ( $campaign_id, 'wc-donation-currency-position', 'before'  );
								}

								$donation_label  = !empty( esc_attr( get_option( 'wc-donation-round-field-label' ))) ? esc_attr( get_option( 'wc-donation-round-field-label' )) : 'Donation';
								update_post_meta ( $campaign_id, 'wc-donation-title', $donation_label );

								$donation_button_text  = !empty( esc_attr( get_option( 'wc-donation-round-button-text' ))) ? esc_attr( get_option( 'wc-donation-round-button-text' )) : 'Donate';
								update_post_meta ( $campaign_id, 'wc-donation-button-text', $donation_button_text  );

								$donation_button_color  = !empty( esc_attr( get_option( 'wc-donation-round-button-color' ))) ? esc_attr( get_option( 'wc-donation-round-button-color' )) : 'd5d5d5';
								update_post_meta ( $campaign_id, 'wc-donation-button-bg-color', $donation_button_color  );

								$donation_button_text_color  = !empty( esc_attr( get_option( 'wc-donation-round-button-text' ))) ? esc_attr( get_option( 'wc-donation-round-button-text' )) : 'Donate';
								update_post_meta ( $campaign_id, 'wc-donation-button-text-color', $donation_button_text_color  );
								
							}
						}
					}
				}
				
				//echo 'we need to work on bw comp';
				
				//backward comp done
				update_option('wc_donation_backward_comp', 'true');
			}

		}
		
		public static function get_wpml_lang_code () {
		
			$suffix = '';
			if ( ! defined( 'ICL_LANGUAGE_CODE' ) ) {
				return $suffix;
			}
			$suffix = '_' . ICL_LANGUAGE_CODE;
			return $suffix;
			
		}	

		public static function DISPLAY_DONATION () {
			return array (
				'predefined' => __('Pre-Defined', 'wc-donation'),
				'free-value' => __('Custom Value', 'wc-donation'),
				'both' => __('Both', 'wc-donation'),
			);
		}
		
		public static function CURRENCY_SIMBOL () {
			return array (
				'before' => __('Before', 'wc-donation'),
				'after'  => __('After', 'wc-donation'),
			);
		}
		
		public static function DISPLAY_DONATION_TYPE () {
			return array (
				'select' => __('Dropdown', 'wc-donation'),
				'radio'  => __('Radio', 'wc-donation'),
				'label'  => __('Label', 'wc-donation')
			);
		}
		
		public static function DISPLAY_RECURRING_TYPE () {
			return array (
				'disabled' => __('Disable', 'wc-donation'),
				'enabled' => __('Enable - Admin\'s Choice', 'wc-donation'),
				'user'  => __('Enable - User\'s Choice', 'wc-donation')
				//'admin'  => __('Enable - Admin\'s Choice', 'wc-donation')
			);
		}

		public static function DISPLAY_GOAL () {
			return array (
				'enabled' => __('Enable', 'wc-donation'),
				'disabled' => __('Disable', 'wc-donation'),
			);
		}
		public static function DISPLAY_CAUSE () {
			return array (
				'show' => __('Enable', 'wc-donation'),
				'hide' => __('Disable', 'wc-donation'),
			);
		}

		public static function DISPLAY_GOAL_TYPE () {
			return array (
				'fixed_amount' => __('Amount Raised', 'wc-donation'),
				'percentage_amount' => __('Percentage Raised', 'wc-donation'),
				'no_of_donation' => __('Number of Donations', 'wc-donation'),
				'no_of_days' => __('Number of Days', 'wc-donation'),
			);
		}

		/**
		 * Notification on not active
		 */
		public function inactive_plugin_notice() {
			?>
			<div id="message" class="error">
				<p><?php printf( esc_html( __( 'Wc Donation webhooks Need Woocommerce to be active!', 'wc-donation' ) ) ); ?></p>
			</div>
			<?php
		}

		/**
		 * Includes
		 */
		public function includes() {
			require_once WC_DONATION_PATH . '/includes/classes/class-wcdonationPdf.php';
			require_once WC_DONATION_PATH . '/includes/classes/class-wcdonationsetting.php';
			require_once WC_DONATION_PATH . '/includes/classes/class-wcdonationcampaignsetting.php';
			require_once WC_DONATION_PATH . '/includes/classes/class-wcdonationsubscription.php';
			require_once WC_DONATION_PATH . '/includes/classes/class-wcdonationproces.php';
			require_once WC_DONATION_PATH . '/includes/classes/class-wcdonationwidgetproces.php';
			require_once WC_DONATION_PATH . '/includes/classes/class-wcdonationreports.php';
			require_once WC_DONATION_PATH . '/includes/classes/class-wcdonationorder.php';

			//REGISTER API FOR WC DONATION
			require_once WC_DONATION_PATH . '/includes/classes/class-wcdonationrestapi.php';			
		}

	}

	$instance = new WcDonation();


endif;
