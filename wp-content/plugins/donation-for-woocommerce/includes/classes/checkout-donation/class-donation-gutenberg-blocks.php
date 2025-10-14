<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCDonationGutenbergBlocks
 */
class WCDonationGutenbergBlocks {    
	/**
	 * Method __construct
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'init_blocks' ) );
	}
	
	/**
	 * Method init_blocks
	 *
	 * @return void
	 */
	public function init_blocks() {
		
		wp_register_script( 
			'wc-donation-checkout-block', 
			WC_DONATION_URL . 'build/checkout_block/index.js', 
			array( 'wp-components', 'wp-blocks', 'wp-element', 'wp-editor' ), WC_DONATION_VERSION 
		);
		
		if ( is_admin() ) {
			wp_enqueue_script( 'wc-donation-checkout-block' );
		}

		wp_localize_script( 'wc-donation-checkout-block', 'wc_donation_checkout_block', array(
			'style_options' => array(
				array(
					'value' => 'single-page-checkout',
					'label' => 'Single Page Checkout',
				),
				array(
					'value' => 'multi-step-checkout',
					'label' => 'Multi Step Wizard Checkout',
				),
				array(
					'value' => 'single-page-popup-checkout',
					'label' => 'Single Page Popup Checkout',
				),
				array(
					'value' => 'multi-step-wizard-popup-checkout',
					'label' => 'Multi Step Wizard Popup Checkout',
				),
			),
		) );

		register_block_type( 
			'wc-donation/checkout-block', 
			array(
				'editor_script'   => 'wc-donation-checkout-block',
				'render_callback' => array( $this, 'render' ),
			)
		);
	}

	public function render( $attributes ) {

		$style = isset( $attributes['style'] ) ? $attributes['style'] : 'single-page-checkout';

		if ( ! isset( $attributes['campaign_id'] ) || empty( $attributes['campaign_id'] ) ) {
			return Helper::checkout_donation_error_display(  esc_html__( 'Error: Donation Campaign not selected in the "WC Checkout Donation" Block. Please select a campaign to proceed.', 'wc-donation' ) );
		}

		$campaign_id        = absint( $attributes['campaign_id'] );
		
		// clock display start 
		$object             = WcdonationCampaignSetting::get_product_by_campaign( $campaign_id );
		$setTimerDonation   = WcDonation::setTimerDonation( $object );

		require_once WC_DONATION_PATH . 'includes/views/frontend/blocks/frontend-donation-display-timer-types.php';
		$clock              = new WcdonationDisplayCloack();
		if ( ! empty( $clock->display_clocks( $campaign_id, $object ) ) ) {
			echo wp_kses( $clock->display_clocks( $campaign_id, $object ), array(
				'div'   => array( 'id' => true, 'class' => true, 'data-start' => true, 'data-start-time' => true, 'data-end-time' => true ),
			) );
		}

		if ( isset( $setTimerDonation['status'], $setTimerDonation['type'] ) && ! $setTimerDonation['status'] ) {
			if ( 'hide' === $setTimerDonation['type'] ) {
				return;
			} else {
				return Helper::checkout_donation_error_display( esc_html( $setTimerDonation['message'] ) );
			}
		}
		// clock display end

		return sprintf( '[wc_checkout_donation style="%s" campaign_id="%s"]', esc_attr( $style ), $campaign_id );
	}
}

( new WCDonationGutenbergBlocks() );
