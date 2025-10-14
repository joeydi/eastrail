<?php
/**
 * Frontend Donation Tabs  html .
 *
 * @package  donation
 */
?>
<div class="wc-donation-tabs-wrap">
	<?php
	/**
	* Action.
	* 
	* @since 3.4.5
	*/
	do_action ('wc_donation_tabs_before', $id_1, $id_2, $title_1, $title_2);
	?>
	<ul class="tab-nav">
		<li class="active" data-tab="tab-content-1"><?php echo esc_html($title_1); ?></li>
		<li data-tab="tab-content-2"><?php echo esc_html($title_2); ?></li>
	</ul>

	<div class="wc-donation-tabs-content-container">
		<div class="wc-donation-tab-content tab-content-1 active">
			<?php echo do_shortcode('[wc_woo_donation id="' . $id_1 . '"]'); ?>    
		</div>

		<div class="wc-donation-tab-content tab-content-2">
			<?php echo do_shortcode('[wc_woo_donation id="' . $id_2 . '"]'); ?>
		</div>
	</div>
	<?php
	/**
	* Action.
	* 
	* @since 3.4.5
	*/
	do_action ('wc_donation_tabs_after', $id_1, $id_2, $title_1, $title_2);
	?>
</div>
