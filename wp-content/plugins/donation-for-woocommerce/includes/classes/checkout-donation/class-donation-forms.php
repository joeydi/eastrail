<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Not Authorized.' );
}

/**
 * WC_Checkout_Donation_Form
 */
class WC_Checkout_Donation_Form {
	
	/**
	 * Is_checkout_donation
	 *
	 * @var bool
	 */
	public $is_checkout_donation = false;
	
	/**
	 * Method __construct
	 *
	 * @return void
	 */
	public function __construct() {

		add_shortcode( 'wc_checkout_donation', array( $this, 'render_shortcode' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'register_block_styles' ), 100 );

		add_filter( 'woocommerce_is_checkout', array( $this, 'is_checkout_donation_form' ) );

		add_filter( 'woocommerce_order_button_text', array( $this, 'checkout_donation_button_text' ), 200 );
		
		add_filter( 'gettext', array( $this, 'filter_gettext' ), 10, 3 );
	}
	
	/**
	 * Method render_shortcode
	 *
	 * @param array $atts
	 *
	 * @return void
	 */
	public function render_shortcode( $atts ) {

		$checkout   = WC()->checkout();
		$atts       = shortcode_atts( array(
			'campaign_id'   => 0,
			'style'         => 'single-page-checkout',
		), $atts );

		if ( wp_is_serving_rest_request() || is_admin() ) {
			return;
		}
		
		$campaign_id = $atts['campaign_id'];

		ob_start();

		$this->checkout_scripts();
		
		if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
			
			echo '<ul class="woocommerce-info" role="info"><li>';
			esc_html_e('Please log in to donate.', 'wc-donation');
			echo '</li></ul>';
			wc_get_template('myaccount/form-login.php');

		} elseif ( ! isset( WC()->cart ) ) {

				wc_add_notice(__( 'In the current view, the checkout donation form is not available.', 'wc-donation' ) );
		} else {

			$this->is_checkout_donation = true;
			Helper::add_to_cart_checkout_donation( $campaign_id );
			include_once sprintf( '%sincludes/views/frontend/checkout_block/form-style/%s.php', WC_DONATION_PATH, $atts['style'] ); // nosemgrep: audit.php.lang.security.file.inclusion-arg

		}

		return ob_get_clean();
	}
		
	/**
	 * Method register_block_styles
	 *
	 * @return void
	 */
	public function register_block_styles() {
		
		if ( has_block('wc-donation/checkout-block') ) {
			wp_register_style( 'wc-donation-checkout', WC_DONATION_URL . 'assets/css/checkout_block.css' );
			wp_register_script( 'wc-donation-checkout', WC_DONATION_URL . 'assets/js/gutenberg_checkout_block/donation-checkout.js' );

			$this->checkout_scripts();
		}
	}

	public function checkout_scripts() {

		foreach ( array( 'wc-donation-checkout' ) as $css_dep_id ) {
			wp_enqueue_style( $css_dep_id );
		}

		$jsdeps = array(
			'jquery',
			'wc-checkout',
			'wc-cart',
			'wc-donation-checkout',
			'accounting',
		);
		// If registration is enabled wc-password-strength-meter
		if ( 'yes' === get_option( 'woocommerce_enable_signup_and_login_from_checkout' ) && 'no' === get_option( 'woocommerce_registration_generate_password' ) && ! is_user_logged_in() ) {
			$jsdeps[] = 'wc-password-strength-meter';
		}
		foreach ( $jsdeps as $js_dep_id ) {
			wp_enqueue_script( $js_dep_id );
		}
		
		if ( ! is_admin() ) {
			wp_dequeue_script( 'select2' );
			wp_deregister_script( 'select2' );
			
			wp_dequeue_style( 'select2' );
			wp_deregister_style( 'select2' );

		}
		
		wp_dequeue_style( 'wc_shortcode_block' );
		wp_deregister_style( 'wc_shortcode_block' );
	}

	/**
	 * Method is_checkout_donation_form
	 *
	 * @param bool $is_it
	 *
	 * @return bool
	 */
	public function is_checkout_donation_form( $is_it ) {
		if ( has_block('wc-donation/checkout-block') ) {
			return true;
		}
		return $is_it;
	}
	
	/**
	 * Method checkout_donation_button_text
	 *
	 * @param string $__
	 *
	 * @return string
	 */
	public function checkout_donation_button_text( $__ ) {
		if ( $this->is_checkout_donation ) {
			return __('Donate now', 'wc-donation');
		}

		return $__;
	}
	
	/**
	 * Method filter_gettext
	 *
	 * @param string $translated
	 * @param string $original_text
	 * @param string $domain
	 *
	 * @return string
	 */
	public function filter_gettext( $translated, $original_text, $domain ) {   
		
		if ( is_admin() || !$this->is_checkout_donation ) {
			return $translated;
		}
		
		if ( 'Billing details' == $original_text ) {
			return __( 'Donor Details', 'wc-donation' );
		} 

		return $translated;
	}
}

( new WC_Checkout_Donation_Form() );
