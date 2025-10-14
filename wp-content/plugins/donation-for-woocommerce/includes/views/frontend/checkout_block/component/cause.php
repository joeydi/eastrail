<div class="wc-donation-cause">
	<?php
	foreach ( $cause_data as $cause_var => $cause_item ) {
		$$cause_var = $cause_item;
	}
		include_once WC_DONATION_PATH . 'includes/views/frontend/blocks/frontend-donation-cause-disp.php';
	?>
	<input type="hidden" name="wc-donation-cause" id="<?php echo esc_attr( 'wc-donation-cause-' ) . esc_attr( $campaign_id ) . '_' . esc_attr($wp_rand); ?>" class="<?php echo esc_attr( 'wc-donation-cause-' ) . esc_attr( $campaign_id ) . '_' . esc_attr($wp_rand); ?>" value="" />
</div>