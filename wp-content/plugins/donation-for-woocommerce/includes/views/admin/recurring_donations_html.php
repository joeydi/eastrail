<?php 
/**
 * Recurring Donation HTML
 */
if ( ! class_exists('WC_Subscriptions') ) {
	?>
	<div id="message" class="notice notice-warning">
		<p><?php echo esc_html__('WooCommerce Subscriptions is inactive. The WooCommerce Subscription plugin must be active for Recurring donation to work.', 'wc-donation'); ?></p>
	</div>
	<?php
	return;
}

$RecurringDisp = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-recurring', true  ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-recurring', true  ) : 'disabled';
$interval = !empty( get_post_meta ( $this->campaign_id, '_subscription_period_interval', true  ) ) ? get_post_meta ( $this->campaign_id, '_subscription_period_interval', true  ) : '1';
$recurring_text = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-recurring-txt', true  ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-recurring-txt', true  ) : '';
$period = !empty( get_post_meta ( $this->campaign_id, '_subscription_period', true  ) ) ? get_post_meta ( $this->campaign_id, '_subscription_period', true  ) : 'month';
$length = !empty( get_post_meta ( $this->campaign_id, '_subscription_length', true  ) ) ? get_post_meta ( $this->campaign_id, '_subscription_length', true  ) : '0';
$prod_id = get_post_meta( $this->campaign_id, 'wc_donation_product', true );	
?>

<div class="select-wrapper">
	<label for="wc-donation-recurring" class="wc-donation-label"><?php echo esc_attr( __( 'Display Type', 'wc-donation' ) ); ?></label>
	<select name='wc-donation-recurring' id="wc-donation-recurring">
		<?php
		foreach ( WcDonation::DISPLAY_RECURRING_TYPE() as $key => $value ) {
			echo '<option value="' . esc_attr( $key ) . '"' .
			selected( $RecurringDisp, $key ) . '>' .
			esc_attr( $value ) . '</option>';
		}
		?>
	</select>
</div>
<div class="select-wrapper" id="wc-donation-recurring-text">
	<label class="wc-donation-label" for="wc-donation-recurring-txt"><?php echo esc_html__('Recurring Text', 'wc-donation' ); ?></label>
	<input type="text" id="wc-donation-recurring-txt" name="wc-donation-recurring-txt" placeholder="Enter Donation Recurring Text" value="<?php echo esc_attr( $recurring_text ); ?>">
</div>
<div id="wc-donation-recurring-schedules">
	<label for="_subscription_period_interval" class="wc-donation-label"><?php echo esc_attr( __( 'Interval & Length Of Recurring Donation', 'wc-donation' ) ); ?></label>
	<div style="clear:both;height:15px;">&nbsp;</div>
	<div class="select-wrapper">
		<select name='_subscription_period_interval' id="_subscription_period_interval">
			<?php
			foreach ( wcs_get_subscription_period_interval_strings() as $key => $value ) {
				?>
				<option value="<?php echo esc_attr($key); ?>" <?php selected( $interval, $key ); ?>><?php echo esc_attr( $value ); ?></option>
				<?php
			}
			?>
		</select>

		<select name='_subscription_period' id="_subscription_period">
			<?php
			foreach ( wcs_get_available_time_periods() as $key => $value ) {
				?>
				<option value="<?php echo esc_attr($key); ?>" <?php selected( $period, $key ); ?>><?php echo esc_attr( $value ); ?></option>
				<?php
			}
			?>
		</select>

		<select name='_subscription_length' id="_subscription_length">
			<?php
			foreach ( wcs_get_subscription_ranges( $period ) as $key => $value ) {
				?>
				<option value="<?php echo esc_attr($key); ?>" <?php selected( $length, $key ); ?>><?php echo esc_attr( $value ); ?></option>
				<?php
			}
			?>
		</select>
	</div>
</div>
