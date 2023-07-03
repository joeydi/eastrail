<?php
if ( 'yes' === $donation_tributes && is_array( $all_tributes ) && count( $all_tributes ) > 0 && ! empty( $all_tributes[0] ) ) {
	?>
	<div class="wc-donation-tribute-wrapper">
		<h3 class="wc-donation-title"><?php echo esc_html__( 'Tributes', 'wc-donation' ); ?></h3>
		<div class="all_tributes">
			<?php
			foreach ( $all_tributes as $k => $v ) { 
				if ( '' != $v ) {
					?>
					<label class="wc-label-radio" for="wc_donation_tribute_checkbox_<?php echo esc_attr($campaign_id) . '_' . esc_attr( $k ) . '_' . esc_attr($wp_rand); ?>">
							<input <?php echo ( 0 == $k ) ? 'data-waschecked="false"' : null; ?> type="radio" id="wc_donation_tribute_checkbox_<?php echo esc_attr($campaign_id) . '_' . esc_attr( $k ) . '_' . esc_attr($wp_rand); ?>" name="wc_donation_tribute_checkbox" value="<?php echo esc_html( $v ); ?>" > <?php echo esc_html( $v ); ?><div class="checkmark"></div>
						</label>
					<?php
				}
			}
			?>
			<div style="clear:both;"></div>
			<input type="hidden" id="wc_donation_trubte_name_<?php echo esc_attr($campaign_id) . '_' . esc_attr($wp_rand); ?>" class="wc_donation_trubte_name" Placeholder="<?php echo esc_html__( 'Enter Name', 'wc-donation' ); ?>" value="">
			<input type="hidden" id="_hidden_tribute_<?php echo esc_attr($campaign_id) . '_' . esc_attr($wp_rand); ?>" name="tribute" value="">
		</div>
	</div>
	<?php
}
