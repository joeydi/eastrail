<div class="wc-donation-tributes">
<?php
foreach ( $tributes_data as $tribute_var => $tribute_item ) {
	$$tribute_var = $tribute_item;
}
	include_once WC_DONATION_PATH . 'includes/views/frontend/blocks/frontend-donation-tribute-disp.php'; ?>
</div>