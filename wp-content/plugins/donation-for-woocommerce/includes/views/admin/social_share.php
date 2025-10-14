<?php
/**
 * Social Share Setting HTML
 */
$show_socialshare = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-social-share-display-option', true  ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-social-share-display-option', true  ) : 'disabled';
?>
<div class="select-wrapper">
	<label class="wc-donation-label" for=""><?php echo esc_attr( __( 'Social Share', 'wc-donation' ) ); ?></label>
	<?php
	foreach ( WcDonation::DISPLAY_GOAL() as $key => $value ) { 
		if ( $show_socialshare == $key ) {
			$checked = 'checked';
		} else {
			$checked = '';
		}
		?>
		<input class="inp-cbx" style="display: none" type="radio" id="socialshare-<?php esc_attr_e( $key ); ?>" name="wc-donation-social-share-display-option" value="<?php esc_attr_e( $key ); ?>" <?php esc_attr_e( $checked ); ?> >
		<label class="cbx" for="socialshare-<?php esc_attr_e( $key ); ?>">
			<span>
				<svg width="12px" height="9px" viewbox="0 0 12 9">
					<polyline points="1 5 4 8 11 1"></polyline>
				</svg>
			</span>
			<span><?php esc_attr_e( $value ); ?></span>
		</label>
		<?php
	}
	?>
	<div class="wc-donation-tooltip-box">
		<small class="wc-donation-tooltip"><?php esc_html_e( 'Enable to show social sharebar on single product page.', 'wc-donation' ); ?></small>
	</div>
</div>