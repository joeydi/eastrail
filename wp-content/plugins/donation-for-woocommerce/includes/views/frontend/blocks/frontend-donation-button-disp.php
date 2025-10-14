<div class="row2">
	<?php
	
	if ( false === $setTimerDonation['status'] && 'display_message' === $setTimerDonation['type'] && ! empty( $setTimerDonation['message'] ) ) {
		//die('its time');
		require_once WC_DONATION_PATH . 'includes/views/frontend/blocks/frontend-donation-display-timer-types.php';
		$display_clock = new WcdonationDisplayCloack();
		$display_clock->display_clocks( $campaign_id, $object );
		echo wp_kses_post('<p class="setTimerMsg">' . $setTimerDonation['message'] . '</p>');
		return;
	}
	?>
	

	<input type="hidden" name="wc_donation_camp" id="wc_donation_camp_<?php echo esc_attr($campaign_id) . '_' . esc_attr($wp_rand); ?>" class="wc_donation_camp" value="<?php echo esc_attr($campaign_id); ?>">
	<input type="hidden" name="wc_rand_id" class="wp_rand" value="<?php echo esc_attr($wp_rand); ?>">
	<button class="button wc-donation-f-submit-donation" data-min-value="<?php echo esc_attr($donation_min_value); ?>" data-max-value="<?php echo esc_attr($donation_max_value); ?>" data-type="<?php esc_attr_e( $_type ); ?>" style="background-color:#<?php esc_attr_e( $donation_button_color ); ?>;border-color:#<?php esc_attr_e( $donation_button_color ); ?>;color:#<?php esc_attr_e( $donation_button_text_color ); ?>;" id='wc-donation-f-submit-donation' value='Donate'><?php echo esc_attr( __( $donation_button_text, 'wc-donation' ) ); ?></button>
	<?php if ( $social_share ) : ?>
		<button class="button wc-donation-social-share" style="background-color:#<?php esc_attr_e( $donation_button_color ); ?>;border-color:#<?php esc_attr_e( $donation_button_color ); ?>;color:#<?php esc_attr_e( $donation_button_text_color ); ?>;" id='wc-donation-social-share' value='social_share'><?php echo esc_attr( __( 'Social Share', 'wc-donation' ) ); ?></button>
		<?php require_once WC_DONATION_PATH . 'includes/views/frontend/blocks/frontend-donation-social-share.php'; ?>
	<?php endif; ?>
</div>
