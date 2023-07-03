<?php
/**
 * Frontend roundoff  html .
 *
 * @package  donation
 */

if ( get_woocommerce_currency_symbol() ) {
	$currency_symbol =  get_woocommerce_currency_symbol();
}
$donation_product = !empty( $object->product['product_id'] ) ? $object->product['product_id'] : '';
$donation_values = !empty( $object->campaign['predAmount'] ) ? $object->campaign['predAmount'] : array();
$donation_value_labels = !empty( $object->campaign['predLabel'] ) ? $object->campaign['predLabel'] : array();
$donation_min_value = !empty( $object->campaign['freeMinAmount'] ) ? $object->campaign['freeMinAmount'] : '';
$donation_max_value = !empty( $object->campaign['freeMaxAmount'] ) ? $object->campaign['freeMaxAmount'] : '';
$display_donation = !empty($object->campaign['amount_display']) ? $object->campaign['amount_display'] : 'both';
$where_currency_symbole = !empty($object->campaign['currencyPos']) ? $object->campaign['currencyPos'] : 'before';
$donation_label  = !empty( $object->campaign['donationTitle'] ) ? $object->campaign['donationTitle'] : '';
$donation_button_text  = !empty( $object->campaign['donationBtnTxt'] ) ? $object->campaign['donationBtnTxt'] : esc_attr__('Donate', 'wc-donation');
$donation_button_color  = !empty( $object->campaign['donationBtnBgColor'] ) ? $object->campaign['donationBtnBgColor'] : '333333';
$donation_button_text_color  = !empty( $object->campaign['donationBtnTxtColor'] ) ? $object->campaign['donationBtnTxtColor'] : 'FFFFFF';
$display_donation_type = !empty( $object->campaign['DonationDispType'] ) ? $object->campaign['DonationDispType'] : 'select';
$donation_body_text = !empty( get_option('wc-donation-round-field-message') ) ? get_option('wc-donation-round-field-message') : '';
$donation_button_cancel_text = !empty( get_option('wc-donation-round-button-cancel-text') ) ? get_option('wc-donation-round-button-cancel-text') : esc_attr('Skip', 'wc-donation');

$RecurringDisp = !empty( $object->campaign['RecurringDisp'] ) ? $object->campaign['RecurringDisp'] : 'disabled';


if ( empty( $donation_product ) ) { 
	$message = __('You have enabled donation on this page but didn\'t select campaign for it.', 'wc-donation');
	$notice_type = 'error';
	$result = wc_add_notice($message, $notice_type); 
	return $result;
}
?>

<div class="wc-donation-popup" id="wc-donation-popup">
	<style>
		#wc-donation-popup {
			--wc-bg-color: #<?php esc_html_e( $donation_button_color ); ?>;
			--wc-txt-color: #<?php esc_html_e( $donation_button_text_color ); ?>;
		}

		<?php
		if ( 'before' === $where_currency_symbole ) {
			?>
			#wc-donation-popup .price-wrapper::before {
				background: #<?php esc_html_e( $donation_button_color ); ?>;
				color: #<?php esc_html_e( $donation_button_text_color ); ?>;
			}
			<?php
		} else {
			?>
			#wc-donation-popup .price-wrapper::after {
				background: #<?php esc_html_e( $donation_button_color ); ?>;
				color: #<?php esc_html_e( $donation_button_text_color ); ?>;
			}
			<?php
		}
		?>
	</style>
	<div class="wc-donation-popup-backdrop"></div>
	<div class="wc-donation-popup-content">
		<div class="wc-donation-popup-header">
			<span class="wc-close">x</span>
			<h3><?php echo esc_html( __( $donation_label, 'wc-donation' ) ); ?></h3>
		</div>
		<div class="wc-donation-popup-body">
			<div class="wc_donation_on_checkout">
				<div class="wc-donation-in-action">
					<p class="donation_text"><?php echo esc_html( __( $donation_body_text, 'wc-donation' ) ); ?></p>
					<div class="row1">
						<div class="price-wrapper <?php echo esc_attr($where_currency_symbole); ?>" currency="<?php echo esc_attr($currency_symbol); ?>">
							<input type="text" class="wc-input-text roundoff-donation-price donate_<?php echo esc_attr($campaign_id); ?> <?php echo esc_attr($where_currency_symbole); ?>" name="wc-donation-price" id="wc-donation-price-<?php echo esc_attr($campaign_id); ?>" value="" disabled="true">
						</div>
					</div>
					<div class="row2">
						<input type="hidden" name="wc_donation_camp" id="wc_donation_camp_<?php echo esc_attr($campaign_id); ?>" class="wc_donation_camp" value="<?php echo esc_attr($campaign_id); ?>">
						<button class="button " data-type="roundoff" style="background-color:#<?php esc_attr_e( $donation_button_color ); ?>;border-color:#<?php esc_attr_e( $donation_button_color ); ?>;color:#<?php esc_attr_e( $donation_button_text_color ); ?>;width:100%" id="wc-donation-round-f-submit-donation" value='Donate'><?php echo esc_attr( __( $donation_button_text, 'wc-donation' ) ); ?></button>
						<div style="height:15px; clear:both;">&nbsp;</div>
						<button class="button" data-type="roundoff-skip" style="background-color:#<?php esc_attr_e( $donation_button_color ); ?>;border-color:#<?php esc_attr_e( $donation_button_color ); ?>;color:#<?php esc_attr_e( $donation_button_text_color ); ?>;width:100%" id="wc-donation-round-f-submit-skip-donation" value='skip'><?php echo esc_attr( __( $donation_button_cancel_text, 'wc-donation' ) ); ?></button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
