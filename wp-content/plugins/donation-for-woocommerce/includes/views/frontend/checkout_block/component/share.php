<div class="wc-donation-share" id="wc-donation-social-share">
	<span class="share-campaign">Share Campaign</span>
	<svg width="14" height="14" viewBox="0 0 16 16">
		<path fill="currentColor" d="M13,15H1c-0.552,0-1-0.448-1-1V4c0-0.552,0.448-1,1-1h4v2H2v8h10v-3h2v4C14,14.552,13.552,15,13,15z"></path>
		<path data-color="color-2" fill="currentColor" d="M16,4l-4-4v3C8.691,3,6,5.691,6,9h2c0-2.206,1.794-4,4-4v3L16,4z"></path>
	</svg>
</div>
<?php
foreach ( $donation_sharing as $donation_sharing_var => $donation_sharing_item ) {
	$$donation_sharing_var = $donation_sharing_item;
} 
	require WC_DONATION_PATH . 'includes/views/frontend/blocks/frontend-donation-social-share.php'; 
?>
