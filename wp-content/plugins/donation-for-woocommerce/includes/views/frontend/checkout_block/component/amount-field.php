<div class="wc-donation-free-amount">
	<div class="wc-donation-other-amount">
		<input type="number" name="" class="wc-donation-amount-field" id="" min="<?php esc_attr_e( $custom_range_min ); ?>" max="<?php esc_attr_e( $custom_range_max ); ?>" oninput="WCCheckoutDonation.priceInputHandle(this);">
		<div class="wc-donation-other-amount-currency"><?php esc_attr_e(get_woocommerce_currency_symbol()); ?></div>
		<div class="amount-frame"></div>
	</div>
	<p class="description"><?php echo wp_kses_post( $custom_amount_label ); ?></p>
</div>