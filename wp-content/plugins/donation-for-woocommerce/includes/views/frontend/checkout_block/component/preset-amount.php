<div class="wc-donation-preset-amounts">
	<?php foreach ( $presets as $preset ) : ?>
	<div class="wc-donation-preset-item">
		<input type="radio" name="wc-donation-amount" id="" class="preset-amount-item" data-amount="<?php esc_attr_e( $preset ); ?>" onchange="WCCheckoutDonation.onAmountSelect('<?php esc_attr_e( $preset ); ?>');">
		<div class="wc-donation-preset-value">
			<span><?php esc_attr_e( get_woocommerce_currency_symbol() ); ?></span><?php esc_attr_e( $preset ); ?>
		</div>
		<div class="item-check"></div>
	</div>
	<?php endforeach; ?>
</div>