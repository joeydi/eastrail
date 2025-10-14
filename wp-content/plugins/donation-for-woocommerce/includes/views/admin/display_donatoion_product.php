<?php
/**
 * Display donation product
 */
 

$dispSinglePage = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-disp-single-page', true ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-disp-single-page', true ) : 'no';
$dispShopPage = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-disp-shop-page', true ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-disp-shop-page', true ) : 'no'; 

wp_nonce_field( '_wcdnonce', '_wcdnonce' );
?>
<div class="sep15px">&nbsp;</div>

<div class="select-wrapper">
	<label class="wc-donation-switch">
		<input id="wc-donation-disp-single-page" name="wc-donation-disp-single-page" type="checkbox" value="yes" <?php ( 'yes' == $dispSinglePage ) ? esc_attr_e('checked') : ''; ?> >
		<span class="wc-slider round"></span>
	</label>
	<label for="wc-donation-disp-single-page" class="wc-text-label"><?php echo esc_attr( __( 'Display product single page', 'wc-donation' ) ); ?></label>
	<div class="sep15px">&nbsp;</div>
</div>

<div class="select-wrapper">
	<label class="wc-donation-switch">
		<input id="wc-donation-disp-shop-page" name="wc-donation-disp-shop-page" type="checkbox" value="yes" <?php ( 'yes' == $dispShopPage ) ? esc_attr_e('checked') : ''; ?> >
		<span class="wc-slider round"></span>
	</label>
	<label for="wc-donation-disp-shop-page" class="wc-text-label"><?php echo esc_attr( __( 'Display product shop page', 'wc-donation' ) ); ?></label>
	<small style="display:block; margin-top :10px; font-weight: bold">(<?php echo esc_html( __( 'If single page is disable this will disable automatically.', 'wc-donation' ) ); ?>)</small>
	<div class="sep15px">&nbsp;</div>
</div>

