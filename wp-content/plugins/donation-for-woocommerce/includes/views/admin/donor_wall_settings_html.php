<?php
/**
 * Time Configuration Setting HTML
 */
$campDonorList = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-campaign-donor-list', true ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-campaign-donor-list', true ) : 'no';
$anonDonorList = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-anonymous-donor-list', true ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-anonymous-donor-list', true ) : 'no';
$donarListTitle = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-donor-list-title', true ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-donor-list-title', true ) : '';
$anonymousDonarListTitle = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-anonymous-donor-list-title', true ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-anonymous-donor-list-title', true ) : '';

wp_nonce_field( '_wcdnonce', '_wcdnonce' );
?>
<h3><?php esc_html_e('Campaign Donor List', 'wc-donation'); ?></h3>
<div class="select-wrapper" data-parent="wc-donation-campaign-donor-list">
	<label class="wc-donation-switch">
		<input id="wc-donation-campaign-donor-list" name="wc-donation-campaign-donor-list" type="checkbox" value="yes" <?php ( 'yes' == $campDonorList ) ? esc_attr_e('checked') : ''; ?> >
		<span class="wc-slider round"></span>
	</label>
	<label for="wc-donation-campaign-donor-list" class="wc-text-label"><?php echo esc_attr( __( 'Enable Campaign Donor List', 'wc-donation' ) ); ?></label>
	<div class="sep15px">&nbsp;</div>
</div>

<div class="select-wrapper" data-child="wc-donation-campaign-donor-list" data-show="yes">
	<label class="wc-donation-label" for="wc-donation-donor-list-title"><?php echo esc_attr( __( 'Title', 'wc-donation' ) ); ?></label>
	<input type="text" id="wc-donation-donor-list-title" name="wc-donation-donor-list-title" value="<?php echo esc_attr($donarListTitle); ?>">
</div>

<h3><?php esc_html_e('Campaign Anonymous Donor List', 'wc-donation'); ?></h3>
<div class="select-wrapper" data-parent="wc-donation-anonymous-donor-list">
	<label class="wc-donation-switch">
		<input id="wc-donation-anonymous-donor-list" name="wc-donation-anonymous-donor-list" type="checkbox" value="yes" <?php ( 'yes' == $anonDonorList ) ? esc_attr_e('checked') : ''; ?> >
		<span class="wc-slider round"></span>
	</label>
	<label for="wc-donation-anonymous-donor-list" class="wc-text-label"><?php echo esc_attr( __( 'Enable Campaign Anonymous Donor List', 'wc-donation' ) ); ?></label>	
	<div class="sep15px">&nbsp;</div>
</div>

<div class="select-wrapper" data-child="wc-donation-anonymous-donor-list" data-show="yes">
	<label class="wc-donation-label" for="wc-donation-anonymous-donor-list-title"><?php echo esc_attr( __( 'Title', 'wc-donation' ) ); ?></label>
	<input type="text" id="wc-donation-anonymous-donor-list-title" name="wc-donation-anonymous-donor-list-title" value="<?php echo esc_attr($anonymousDonarListTitle); ?>">
</div>


