<?php
if ( 'yes' === $donation_gift_aid ) {	
	if ( ( in_array('single', $donation_gift_aid_area) && is_product() && 'single' === $type ) || ( in_array('cart', $donation_gift_aid_area) && $is_cart && 'cart' === $type ) || ( in_array('checkout', $donation_gift_aid_area) && $is_checkout && 'checkout' === $type ) || ( in_array('widget', $donation_gift_aid_area) && 'shortcode' === $type ) ) {
		?>
		<div class="wc-donation-gift-aid-wrapper">		
			<?php 
			if ( ! empty( trim( $donation_gift_aid_title ) ) ) {
				?>
				<h3 class="wc-donation-title"><?php echo esc_html( $donation_gift_aid_title ); ?></h3>
				<?php
			}
			?>

			<?php 
			if ( ! empty( trim( $donation_gift_aid_explanation ) ) ) {
				?>
				<p class="wc-donation-gift-aid-explanation" ><?php echo esc_html( $donation_gift_aid_explanation ); ?></p>
				<?php
			}
			?>

			<label class="wc-label-radio" for="wc_donation_gift_aid_checkbox_<?php echo esc_attr($campaign_id) . '_' . esc_attr($wp_rand); ?>">
				<input type="checkbox" id="wc_donation_gift_aid_checkbox_<?php echo esc_attr($campaign_id) . '_' . esc_attr($wp_rand); ?>" name="wc_donation_gift_aid_checkbox" value="yes" > <?php echo esc_html( $donation_gift_aid_checkbox_title ); ?><div class="checkmark"></div>
			</label>

			<?php 
			if ( ! empty( trim( $donation_gift_aid_declaration ) ) ) {
				?>
				<div style="clear:both;"></div>
				<p class="wc-donation-gift-aid-declaration" ><?php echo esc_html( $donation_gift_aid_declaration ); ?></p>
				<?php
			}
			?>
		</div>
		<?php
	}
}
