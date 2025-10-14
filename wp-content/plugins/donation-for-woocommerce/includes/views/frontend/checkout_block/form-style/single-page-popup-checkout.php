<button type="button" onclick="document.getElementById('wc-checkout-donation-modal').classList.add('active-modal');">
	<?php esc_attr_e( 
		/**
		* Filter.
		* 
		* @since 4.0.0
		*/
		apply_filters( 'wc_donation_popup_checkout_button_text', 'Donate Now', 'single-page-popup-checkout' ) 
		); ?>
</button>
<div id="wc-checkout-donation-modal" class="">
	<div class="wc-checkout-donaton-modal-background">
		<div class="wc-checkout-donation-modal-body">
			<div class="wc-checkout-donation-modal-content">
				<span class="close-popup" onclick="document.getElementById('wc-checkout-donation-modal').classList.remove('active-modal');">×</span>
				<?php include_once sprintf( '%sincludes/views/frontend/checkout_block/form-style/single-page-checkout.php', WC_DONATION_PATH ); ?>
			</div>
		</div>
	</div>
</div>