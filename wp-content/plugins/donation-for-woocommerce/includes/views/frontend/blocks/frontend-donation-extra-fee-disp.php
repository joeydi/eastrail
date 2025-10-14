<?php
$get_fee_campaign = get_option('wc-donation-fees-product');
$check_fee_option = get_option('wc-donation-card-fee');
$cc_processing_label = ! empty( get_option('wc-donation-fees-field-message') ) ? get_option('wc-donation-fees-field-message') : esc_html( 'Credit Card Processing Fees.', 'wc-donation' );

if ( !is_array( $get_fee_campaign ) ) {
	$get_fee_campaign = array();
}

if ( 'yes' === $check_fee_option && in_array( $campaign_id, $get_fee_campaign ) ) {
	/**
	* Filter.
	* 
	* @since 3.6.3
	*/
	$reqiured = apply_filters( 'card_processing_required_field', false, $campaign_id );
	?>
	<div class="row1" id="cc-processing-fees">
		<div class="row1"><label class="wc-label-radio"><input class="donation-processing-fees" id="processing-fees-<?php echo esc_attr( $campaign_id ) . '_' . esc_attr( $wp_rand ); ?>" type="checkbox" data-camp="<?php echo esc_attr( $campaign_id ) . '_' . esc_attr( $wp_rand ); ?>" data-id="fees-<?php echo esc_attr( $campaign_id ) . '_' . esc_attr( $wp_rand ); ?>" name="wc_check_fees" value="<?php echo esc_attr( get_option( 'wc-donation-fees-percent' ) ); ?>"><?php echo esc_attr( $cc_processing_label ); ?><div class="checkmark"></div></label></div>
		<?php echo '<input data-require="' . esc_attr( $reqiured ) . '" type="hidden" id="wc-donation-fees-' . esc_attr($campaign_id) . '_' . esc_attr($wp_rand) . '" class="donate_fees_' . esc_attr($campaign_id) . '_' . esc_attr($wp_rand) . '" name="wc-donation-fees" value="">'; ?>
	</div>
	<?php
}
