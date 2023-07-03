<?php
/**
 * Frontend order  html .
 *
 * @package  donation
 */

if ( get_woocommerce_currency_symbol() ) {
	$currency_symbol =  get_woocommerce_currency_symbol();
}
$wp_rand = wp_rand( 1, 999 );
$donation_product = !empty( $object->product['product_id'] ) ? $object->product['product_id'] : '';
$donation_values = !empty( $object->campaign['predAmount'] ) ? $object->campaign['predAmount'] : array();
$donation_value_labels = !empty( $object->campaign['predLabel'] ) ? $object->campaign['predLabel'] : array();
$donation_min_value = !empty( $object->campaign['freeMinAmount'] ) ? $object->campaign['freeMinAmount'] : 0;
$donation_max_value = !empty( $object->campaign['freeMaxAmount'] ) ? $object->campaign['freeMaxAmount'] : '';
$display_donation = !empty($object->campaign['amount_display']) ? $object->campaign['amount_display'] : 'both';
$where_currency_symbole = !empty($object->campaign['currencyPos']) ? $object->campaign['currencyPos'] : 'before';
$donation_label  = !empty( $object->campaign['donationTitle'] ) ? $object->campaign['donationTitle'] : '';
$donation_button_text  = !empty( $object->campaign['donationBtnTxt'] ) ? $object->campaign['donationBtnTxt'] : esc_attr__('Donate', 'wc-donation');
$donation_button_color  = !empty( $object->campaign['donationBtnBgColor'] ) ? $object->campaign['donationBtnBgColor'] : '333333';
$donation_button_text_color  = !empty( $object->campaign['donationBtnTxtColor'] ) ? $object->campaign['donationBtnTxtColor'] : 'FFFFFF';
$display_donation_type = !empty( $object->campaign['DonationDispType'] ) ? $object->campaign['DonationDispType'] : 'select';

$RecurringDisp = !empty( $object->campaign['RecurringDisp'] ) ? $object->campaign['RecurringDisp'] : 'disabled';
$recurring_text = !empty( $object->campaign['recurringText'] ) ? $object->campaign['recurringText'] : 'Make it recurring for';
$causeDisp = !empty( $object->campaign['causeDisp'] ) ? $object->campaign['causeDisp'] : 'hide';
$causeNames = !empty( $object->campaign['causeNames'] ) ? $object->campaign['causeNames'] : array();
$causeDesc = !empty( $object->campaign['causeDesc'] ) ? $object->campaign['causeDesc'] : array();
$causeImg = !empty( $object->campaign['causeImg'] ) ? $object->campaign['causeImg'] : array();
/**
 * Donation Goal Settings
 */
$goalDisp = !empty( $object->goal['display'] ) ? $object->goal['display'] : '';
$goalType = !empty( $object->goal['type'] ) ? $object->goal['type'] : '';

$get_donations = WcdonationSetting::has_bought_items( $donation_product );

$progressBarColor = !empty( $object->goal['progress_bar_color'] ) ? $object->goal['progress_bar_color'] : '';
$dispDonorCount = !empty( $object->goal['display_donor_count'] ) ? $object->goal['display_donor_count'] : '';
$closeForm = !empty( $object->goal['form_close'] ) ? $object->goal['form_close'] : '';
$message = !empty( $object->goal['message'] ) ? $object->goal['message'] : '';

$progressOnWidget = !empty( $object->goal['show_on_widget'] ) ? $object->goal['show_on_widget'] : '';

$donation_tributes = get_option( 'wc-donation-tributes' );
$all_tributes = !empty( $object->campaign['tributes'] ) ? $object->campaign['tributes'] : array();
$donation_gift_aid = get_option( 'wc-donation-gift-aid' );

// var_dump(get_option( 'wc-donation-gift-aid-area', array() ));

if ( !is_array( get_option( 'wc-donation-gift-aid-area', array() ) ) ) {	
	$donation_gift_aid_area[] = get_option( 'wc-donation-gift-aid-area', array() );
} else {	
	$donation_gift_aid_area = get_option( 'wc-donation-gift-aid-area', array() );
}

// var_dump($donation_gift_aid_area);
$donation_gift_aid_title = get_option( 'wc-donation-gift-aid-title' );
$donation_gift_aid_checkbox_title = ! empty( get_option( 'wc-donation-gift-aid-checkbox-title' ) ) ? get_option( 'wc-donation-gift-aid-checkbox-title' ) : __('Yes, I would like to claim Gift Aid', 'wc-donation');
$donation_gift_aid_explanation = get_option( 'wc-donation-gift-aid-explanation' );
$donation_gift_aid_declaration = get_option( 'wc-donation-gift-aid-declaration' );
$is_cart = is_cart();
$is_checkout = is_checkout();

$blocks = ( is_array($object->campaign['ordering']) && count($object->campaign['ordering']) > 0 ) ? $object->campaign['ordering'] : array(
	'amount',
	'cause',
	'tribute',
	'gift-aid',
	'extra-fee',
	'subscription',
	'main-goal',
	'button',
	'extra-fee-summary'
);


/**
 * Donation product.
 *
 * @var type
 */

$post_exist = !empty( $object->campaign['campaign_id'] ) ? get_post( $object->campaign['campaign_id'] ) : '';
if ( empty( $donation_product ) || empty($post_exist) || ( isset($post_exist->post_status) && 'trash' == $post_exist->post_status ) ) { 
	$message = __('You have enabled donation on this page but didn\'t select campaign for it.', 'wc-donation');
	$notice_type = 'error';
	wc_clear_notices(); //<--- check this line.
	$result = wc_add_notice($message, $notice_type); 
	return $result;
}

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
		$no_of_days = !empty( $object->goal['no_of_days'] ) ? $object->goal['no_of_days'] : 0;
		$end_date = gmdate('Y-m-d', strtotime($no_of_days));
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
?>
<style>
	:root {
		--wc-bg-color: #<?php esc_html_e( $donation_button_color ); ?>;
		--wc-txt-color: #<?php esc_html_e( $donation_button_text_color ); ?>;
	}

	<?php
	if ( 'before' === $where_currency_symbole ) {
		if ( 'checkout' == $type ) {
			?>
			#wc_donation_on_checkout .price-wrapper::before {
				background: #<?php esc_html_e( $donation_button_color ); ?>;
				color: #<?php esc_html_e( $donation_button_text_color ); ?>;
			}
			<?php
		}

		if ( 'cart' == $type ) {
			?>
			#wc_donation_on_cart .price-wrapper::before {
				background: #<?php esc_html_e( $donation_button_color ); ?>;
				color: #<?php esc_html_e( $donation_button_text_color ); ?>;
			}
			<?php
		}

		if ( 'widget' == $type ) {
			?>
			#wc_donation_on_widget_<?php echo esc_attr($campaign_id); ?> .price-wrapper::before {
				background: #<?php esc_html_e( $donation_button_color ); ?>;
				color: #<?php esc_html_e( $donation_button_text_color ); ?>;
			}
			
			<?php
		}

		if ( 'shortcode' == $type ) {
			?>
			#wc_donation_on_shortcode_<?php echo esc_attr($campaign_id); ?> .price-wrapper::before {
				background: #<?php esc_html_e( $donation_button_color ); ?>;
				color: #<?php esc_html_e( $donation_button_text_color ); ?>;
			}

			<?php
		}

		if ( 'single' == $type ) {
			?>
			#wc_donation_on_single_<?php echo esc_attr($campaign_id); ?> .price-wrapper::before {
				background: #<?php esc_html_e( $donation_button_color ); ?>;
				color: #<?php esc_html_e( $donation_button_text_color ); ?>;
			}

			<?php
		}
	} else {
		if ( 'checkout' == $type ) {
			?>
			#wc_donation_on_checkout .price-wrapper::after {
				background: #<?php esc_html_e( $donation_button_color ); ?>;
				color: #<?php esc_html_e( $donation_button_text_color ); ?>;
			}
			<?php
		}

		if ( 'cart' == $type ) {
			?>
			#wc_donation_on_cart .price-wrapper::after {
				background: #<?php esc_html_e( $donation_button_color ); ?>;
				color: #<?php esc_html_e( $donation_button_text_color ); ?>;
			}
			<?php
		}

		if ( 'widget' == $type ) {
			?>
			#wc_donation_on_widget_<?php echo esc_attr($campaign_id); ?> .price-wrapper::after {
				background: #<?php esc_html_e( $donation_button_color ); ?>;
				color: #<?php esc_html_e( $donation_button_text_color ); ?>;
			}
			
			<?php
		}

		if ( 'shortcode' == $type ) {
			?>
			#wc_donation_on_shortcode_<?php echo esc_attr($campaign_id); ?> .price-wrapper::after {
				background: #<?php esc_html_e( $donation_button_color ); ?>;
				color: #<?php esc_html_e( $donation_button_text_color ); ?>;
			}
			<?php
		}
		
		if ( 'single' == $type ) {
			?>
			#wc_donation_on_single_<?php echo esc_attr($campaign_id); ?> .price-wrapper::after {
				background: #<?php esc_html_e( $donation_button_color ); ?>;
				color: #<?php esc_html_e( $donation_button_text_color ); ?>;
			}
			<?php
		}
	} 

	if ( 'checkout' == $type ) {
		?>
		#wc_donation_on_checkout .wc-input-text {
			border-color: #<?php esc_html_e( $donation_button_color ); ?>!important;
		}

		#wc_donation_on_checkout .checkmark {
			border-color: #<?php esc_html_e( $donation_button_color ); ?>!important;
		}
		#wc_donation_on_checkout .wc-label-radio input:checked ~ .checkmark {
			background-color: #<?php esc_html_e( $donation_button_color); ?>;
		}
		#wc_donation_on_checkout .wc-label-radio .checkmark:after {
			border-color: #<?php esc_html_e( $donation_button_text_color); ?>!important;
		}
		#wc_donation_on_checkout .wc-label-button {
			border-color: #<?php esc_html_e( $donation_button_color ); ?>!important;
			color: #<?php esc_html_e( $donation_button_color ); ?>!important;
		}
		#wc_donation_on_checkout label.wc-label-button.wc-active {
			background-color: #<?php esc_html_e( $donation_button_color ); ?>!important;
			color: #<?php esc_html_e( $donation_button_text_color); ?>!important;
		}
		#wc_donation_on_checkout .wc_progressBarContainer > ul > li.wc_progress div.progressbar {
			background: #<?php esc_html_e( $progressBarColor ); ?>;
		}
		<?php
	}

	if ( 'cart' == $type ) {
		?>
		#wc_donation_on_cart .wc-input-text {
			border-color: #<?php esc_html_e( $donation_button_color ); ?>!important;
		}

		#wc_donation_on_cart .checkmark {
			border-color: #<?php esc_html_e( $donation_button_color ); ?>!important;
		}
		#wc_donation_on_cart .wc-label-radio input:checked ~ .checkmark {
			background-color: #<?php esc_html_e( $donation_button_color); ?>;
		}
		#wc_donation_on_cart .wc-label-radio .checkmark:after {
			border-color: #<?php esc_html_e( $donation_button_text_color); ?>!important;
		}
		#wc_donation_on_cart .wc-label-button {
			border-color: #<?php esc_html_e( $donation_button_color ); ?>!important;
			color: #<?php esc_html_e( $donation_button_color ); ?>!important;
		}
		#wc_donation_on_cart label.wc-label-button.wc-active {
			background-color: #<?php esc_html_e( $donation_button_color ); ?>!important;
			color: #<?php esc_html_e( $donation_button_text_color); ?>!important;
		}
		#wc_donation_on_cart .wc_progressBarContainer > ul > li.wc_progress div.progressbar {
			background: #<?php esc_html_e( $progressBarColor ); ?>;
		}
		<?php
	}

	if ( 'widget' == $type ) {
		?>
		#wc_donation_on_widget_<?php echo esc_attr($campaign_id); ?> .wc-input-text {
			border-color: #<?php esc_html_e( $donation_button_color ); ?>!important;
		}

		#wc_donation_on_widget_<?php echo esc_attr($campaign_id); ?> .checkmark {
			border-color: #<?php esc_html_e( $donation_button_color ); ?>!important;
		}
		#wc_donation_on_widget_<?php echo esc_attr($campaign_id); ?> .wc-label-radio input:checked ~ .checkmark {
			background-color: #<?php esc_html_e( $donation_button_color); ?>;
		}
		#wc_donation_on_widget_<?php echo esc_attr($campaign_id); ?> .wc-label-radio .checkmark:after {
			border-color: #<?php esc_html_e( $donation_button_text_color); ?>!important;
		}
		#wc_donation_on_widget_<?php echo esc_attr($campaign_id); ?> .wc-label-button {
			border-color: #<?php esc_html_e( $donation_button_color ); ?>!important;
			color: #<?php esc_html_e( $donation_button_color ); ?>!important;
		}
		#wc_donation_on_widget_<?php echo esc_attr($campaign_id); ?> label.wc-label-button.wc-active {
			background-color: #<?php esc_html_e( $donation_button_color ); ?>!important;
			color: #<?php esc_html_e( $donation_button_text_color); ?>!important;
		}
		#wc_donation_on_widget_<?php echo esc_attr($campaign_id); ?> .wc_progressBarContainer > ul > li.wc_progress div.progressbar {
			background: #<?php esc_html_e( $progressBarColor ); ?>;
		}
		<?php
	}

	if ( 'shortcode' == $type ) {
		?>
		#wc_donation_on_shortcode_<?php echo esc_attr($campaign_id); ?> .wc-input-text {
			border-color: #<?php esc_html_e( $donation_button_color ); ?>!important;
		}

		#wc_donation_on_shortcode_<?php echo esc_attr($campaign_id); ?> .checkmark {
			border-color: #<?php esc_html_e( $donation_button_color ); ?>!important;
		}
		#wc_donation_on_shortcode_<?php echo esc_attr($campaign_id); ?> .wc-label-radio input:checked ~ .checkmark {
			background-color: #<?php esc_html_e( $donation_button_color); ?>;
		}
		#wc_donation_on_shortcode_<?php echo esc_attr($campaign_id); ?> .wc-label-radio .checkmark:after {
			border-color: #<?php esc_html_e( $donation_button_text_color); ?>!important;
		}
		#wc_donation_on_shortcode_<?php echo esc_attr($campaign_id); ?> .wc-label-button {
			border-color: #<?php esc_html_e( $donation_button_color ); ?>!important;
			color: #<?php esc_html_e( $donation_button_color ); ?>!important;
		}
		#wc_donation_on_shortcode_<?php echo esc_attr($campaign_id); ?> label.wc-label-button.wc-active {
			background-color: #<?php esc_html_e( $donation_button_color ); ?>!important;
			color: #<?php esc_html_e( $donation_button_text_color); ?>!important;
		}
		#wc_donation_on_shortcode_<?php echo esc_attr($campaign_id); ?> .wc_progressBarContainer > ul > li.wc_progress div.progressbar {
			background: #<?php esc_html_e( $progressBarColor ); ?>;
		}
		<?php
	}
	
	if ( 'single' == $type ) {
		?>
		#wc_donation_on_single_<?php echo esc_attr($campaign_id); ?> .wc-input-text {
			border-color: #<?php esc_html_e( $donation_button_color ); ?>!important;
		}

		#wc_donation_on_single_<?php echo esc_attr($campaign_id); ?> .checkmark {
			border-color: #<?php esc_html_e( $donation_button_color ); ?>!important;
		}
		#wc_donation_on_single_<?php echo esc_attr($campaign_id); ?> .wc-label-radio input:checked ~ .checkmark {
			background-color: #<?php esc_html_e( $donation_button_color); ?>;
		}
		#wc_donation_on_single_<?php echo esc_attr($campaign_id); ?> .wc-label-radio .checkmark:after {
			border-color: #<?php esc_html_e( $donation_button_text_color); ?>!important;
		}
		#wc_donation_on_single_<?php echo esc_attr($campaign_id); ?> .wc-label-button {
			border-color: #<?php esc_html_e( $donation_button_color ); ?>!important;
			color: #<?php esc_html_e( $donation_button_color ); ?>!important;
		}
		#wc_donation_on_single_<?php echo esc_attr($campaign_id); ?> label.wc-label-button.wc-active {
			background-color: #<?php esc_html_e( $donation_button_color ); ?>!important;
			color: #<?php esc_html_e( $donation_button_text_color); ?>!important;
		}
		#wc_donation_on_single_<?php echo esc_attr($campaign_id); ?> .wc_progressBarContainer > ul > li.wc_progress div.progressbar {
			background: #<?php esc_html_e( $progressBarColor ); ?>;
		}
		<?php
	}
	
	?>
</style>
<div class="wc-donation-in-action" data-donation-type="<?php echo esc_attr($display_donation); ?>">
	<div class="in-action-elements">
		<?php

		if ( is_array( $blocks ) && count( $blocks ) > 0 ) {
			foreach ( $blocks as $block ) {
				require( WC_DONATION_PATH . 'includes/views/frontend/blocks/frontend-donation-' . $block . '-disp.php' );
			}
		}


		/* Donation Tributes Block Start */
		// require( WC_DONATION_PATH . 'includes/views/frontend/blocks/frontend-donation-amount-disp.php' );
		/* Donation Tributes Block End */

		/* Donation Tributes Block Start */
		// require( WC_DONATION_PATH . 'includes/views/frontend/blocks/frontend-donation-cause-disp.php' );
		/* Donation Tributes Block End */

		/* Donation Tributes Block Start */
		// require( WC_DONATION_PATH . 'includes/views/frontend/blocks/frontend-donation-tribute-disp.php' );
		/* Donation Tributes Block End */

		/* Donation Gift Aid Block Start */
		// require( WC_DONATION_PATH . 'includes/views/frontend/blocks/frontend-donation-gift-aid-disp.php' );
		/* Donation Gift Aid Block End */

		/* Donation Extra Fee Block Start */
		// require( WC_DONATION_PATH . 'includes/views/frontend/blocks/frontend-donation-extra-fee-disp.php' );
		/* Donation Extra Fee Block End */

		/* Donation Subscription Block Start */
		// require( WC_DONATION_PATH . 'includes/views/frontend/blocks/frontend-donation-subscription-disp.php' );
		/* Donation Subscription Block End */

		/* Donation Goal Block Start */
		// require( WC_DONATION_PATH . 'includes/views/frontend/blocks/frontend-donation-main-goal-disp.php' );
		/* Donation Goal Block End */

		/* Donation Button Block Start */
		// require( WC_DONATION_PATH . 'includes/views/frontend/blocks/frontend-donation-button-disp.php' );
		/* Donation Button Block End */

		/* Donation Extra Fee Summary Block Start */
		// require( WC_DONATION_PATH . 'includes/views/frontend/blocks/frontend-donation-extra-fee-summary-disp.php' );
		/* Donation Extra Fee Summary Block End */
		?>
	</div>
</div>
<div style="clear:both;height:1px;">&nbsp;</div>
