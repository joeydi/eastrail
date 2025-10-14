<?php
/**
 * Campgin Shortcode
 */

?>
<div class="sep15px">&nbsp;</div>

<div class="select-wrapper">
	<textarea spellcheck="false" id="wc-donation-campaign-shortcode" class="wc-shortcode-field">[wc_woo_donation id="<?php esc_attr_e($this->campaign_id); ?>"]</textarea>
	<a href="javascript:void(0);" class="wc-button-primary wc-uppercase " onclick="copyToClip('wc-donation-campaign-shortcode')">Copy text</a>
	<div class="sep15px">&nbsp;</div>
</div>
