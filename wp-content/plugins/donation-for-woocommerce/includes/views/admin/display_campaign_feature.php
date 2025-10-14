<?php
/**
 * Display feature donation
 */
 

$dispfeaturePage = ! empty( get_post_meta ( $this->campaign_id, 'feature_donation', true ) ) ? get_post_meta ( $this->campaign_id, 'feature_donation', true ) : 'no';

wp_nonce_field( '_wcdnonce', '_wcdnonce' );
?>
<div class="sep15px">&nbsp;</div>

<div class="select-wrapper">
	<label class="wc-donation-switch">
		<input id="wc-donation-disp-feature" name="wc-donation-disp-feature" type="checkbox" value="yes" <?php ( 'yes' == $dispfeaturePage ) ? esc_attr_e('checked') : ''; ?> >
		<span class="wc-slider round"></span>
	</label>
	<label for="wc-donation-disp-feature" class="wc-text-label"><?php esc_html_e( 'Display as featured Campaign', 'wc-donation' ); ?></label>
	<small style="display:block; margin-top :10px; font-weight: bold">(<?php echo esc_html( __( 'If Feature Campaign is enable then the display product single page will enable automatically .', 'wc-donation' ) ); ?>)</small>
	<div class="sep15px">&nbsp;</div>
</div>