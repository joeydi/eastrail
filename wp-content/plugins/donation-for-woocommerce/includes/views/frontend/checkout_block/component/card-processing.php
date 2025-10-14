<?php
	$label = get_option( 'wc-donation-fees-field-message', esc_html__( 'Cover credit card processing fees.', 'wc-donation' ) );
?>
<div class="round card-processing">
	<label for="wc_check_fees">
		<input type="checkbox" name="card_processing" id="wc_check_fees" onchange="WCCheckoutDonation.handleCoverCardFees(this);" />
		<div class="checkmark"></div>
	<?php esc_attr_e( $label ); ?>
	</label>
</div>