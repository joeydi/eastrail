<?php
/**
 * Single Campaign
 */
$tabber = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-tablink', true  ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-tablink', true  ) : 'tab-1'; 
?>
<style>
	#wc_donation_meta__3 {
		border: 1px solid #6d36af;
	}
	#wc_donation_meta__3 .postbox-header {
		border-bottom: 1px solid #6d36af;
		display:none!important;
	}
	#wc_donation_meta__3 .inside {
		margin: 0!important;
		padding: 0!important;
		box-sizing: border-box!important;
	}
	.wc-donation-tabConainer {
		display: grid;
		grid-template-rows: repeat(1, max-content);
		grid-template-columns: repeat(4, 1fr);
	}
	.wc-donation-tab {
		margin: 24px 0 24px 24px;
	}
	.wc-donation-tab a {
		display: block;
		padding: 16px;
		color: #000;
		font-size: 12px;
		text-decoration: none;
		outline: none!important;
		border-bottom: 1px solid #6d36af;
		border-right: 1px solid #6d36af;
		border-left: 1px solid #6d36af;
	}
	.wc-donation-tab a[href="tab-1"] {
		border-top: 1px solid #6d36af;
	}
	.wc-donation-tab a:hover, .wc-donation-tab a:active, .wc-donation-tab a:focus {
		outline:none!important;
		box-shadow:none!important;
	}
	.wc-donation-tab a.active {
		background-color: #6d36af;
		outline: none;
		color: #fff;
	}
	.wc-donation-tabcontent {
		padding: 20px;
		background: #fff;
		grid-column: 2/-1;
		display: none;
		border-left: 0;
	}
</style>

<div class="wc-donation-tabConainer">
	<div class="wc-donation-tab">
	<input type="hidden" id="wc-donation-tablink" name="wc-donation-tablink" value="<?php echo esc_attr($tabber); ?>">
	<a href="tab-1" class="wc-donation-tablinks" >Campaign Settings</a>
	<a href="tab-2" class="wc-donation-tablinks" >Form Settings</a>
	<?php 
	if ( class_exists('WC_Subscriptions') ) { 
		?>
		<a  href="tab-3" class="wc-donation-tablinks" >Recurring Donations</a>
		<?php 
	} else {
		?>
		<a href="#"  class="wc-donation-tablinks" style="pointer-events:none!important; opacity: 0.35" >Recurring Donations</a>
		<?php 
	}
	?>
	<a href="tab-4" class="wc-donation-tablinks">Donation Goal</a>
	<a href="tab-5" class="wc-donation-tablinks">Donation Cause</a>
	<?php 
	if ( 'yes' === get_option( 'wc-donation-tributes' ) ) { 
		?>
		<a href="tab-6" class="wc-donation-tablinks">Tributes</a>
		<?php
	}
	?>
	<a href="tab-7" class="wc-donation-tablinks">Frontend Ordering</a>
	</div>

	<div id="tab-1" class="wc-donation-tabcontent">
		<?php require_once WC_DONATION_PATH . '/includes/views/admin/campaign_settings_html.php'; ?>
	</div>

	<div id="tab-2" class="wc-donation-tabcontent">
		<?php require_once WC_DONATION_PATH . '/includes/views/admin/form_settings_html.php'; ?> 
	</div>

	<div id="tab-3" class="wc-donation-tabcontent">
		<?php require_once WC_DONATION_PATH . '/includes/views/admin/recurring_donations_html.php'; ?>
	</div>
	
	<div id="tab-4" class="wc-donation-tabcontent">
		<?php require_once WC_DONATION_PATH . '/includes/views/admin/donation_goal_html.php'; ?>
	</div>
	<div id="tab-5" class="wc-donation-tabcontent">
		<?php require_once WC_DONATION_PATH . '/includes/views/admin/donation_cause_html.php'; ?>
	</div>
	<div id="tab-6" class="wc-donation-tabcontent">
		<?php require_once WC_DONATION_PATH . '/includes/views/admin/donation_tribute_html.php'; ?>
	</div>
	<div id="tab-7" class="wc-donation-tabcontent">
		<?php require_once WC_DONATION_PATH . '/includes/views/admin/donation_ordering_html.php'; ?>
	</div>
</div>
