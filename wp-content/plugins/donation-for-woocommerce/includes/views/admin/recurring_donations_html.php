<?php 
/**
 * Recurring Donation HTML
 */
if ( ! class_exists('WC_Subscriptions') && ! class_exists('Subscriptions_For_Woocommerce') ) {
	?>
	<div id="message" class="notice notice-warning">
		<p><?php echo esc_html__('WooCommerce Subscriptions is inactive. The WooCommerce Subscription plugin must be active for Recurring donation to work.', 'wc-donation'); ?></p>
	</div>
	<?php
	return;
}

// For Woocommerce Subscription
if ( class_exists('WC_Subscriptions') ) {
	$RecurringDisp  = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-recurring', true  ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-recurring', true  ) : 'disabled';
	$interval       = !empty( get_post_meta ( $this->campaign_id, '_subscription_period_interval', true  ) ) ? get_post_meta ( $this->campaign_id, '_subscription_period_interval', true  ) : '1';
	$recurring_text = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-recurring-txt', true  ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-recurring-txt', true  ) : '';
	$period         = !empty( get_post_meta ( $this->campaign_id, '_subscription_period', true  ) ) ? get_post_meta ( $this->campaign_id, '_subscription_period', true  ) : 'month';
	$length         = !empty( get_post_meta ( $this->campaign_id, '_subscription_length', true  ) ) ? get_post_meta ( $this->campaign_id, '_subscription_length', true  ) : '0';
	$prod_id        = get_post_meta( $this->campaign_id, 'wc_donation_product', true );    
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
	<?php
}

// For Subscription Free Plugin (WP Swings)
if ( class_exists('Subscriptions_For_Woocommerce') && ! class_exists('WC_Subscriptions') ) {

	$RecurringDisp                              = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-recurring', true  ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-recurring', true  ) : 'disabled';
	$recurring_text                             = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-recurring-txt', true  ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-recurring-txt', true  ) : '';
	$wps_sfw_subscription_number                = !empty( wps_sfw_get_meta_data ( $this->campaign_id, 'wps_sfw_subscription_number', true  ) ) ? wps_sfw_get_meta_data ( $this->campaign_id, 'wps_sfw_subscription_number', true  ) : '1';
	$wps_sfw_subscription_interval              = !empty( wps_sfw_get_meta_data ( $this->campaign_id, 'wps_sfw_subscription_interval', true  ) ) ? wps_sfw_get_meta_data ( $this->campaign_id, 'wps_sfw_subscription_interval', true  ) : 'month';
	$wps_sfw_subscription_expiry_number         = !empty( wps_sfw_get_meta_data ( $this->campaign_id, 'wps_sfw_subscription_expiry_number', true  ) ) ? wps_sfw_get_meta_data ( $this->campaign_id, 'wps_sfw_subscription_expiry_number', true  ) : 'month';
	$wps_sfw_subscription_expiry_interval       = !empty( wps_sfw_get_meta_data ( $this->campaign_id, 'wps_sfw_subscription_expiry_interval', true  ) ) ? wps_sfw_get_meta_data ( $this->campaign_id, 'wps_sfw_subscription_expiry_interval', true  ) : 'month';
	// $prod_id                                     = get_post_meta( $this->campaign_id, 'wc_donation_product', true );  
	$intervals = array(
		'day'  => esc_html__('Day(s)', 'wc-donation'),
		'week'  => esc_html__('Weeks', 'wc-donation'),
		'month' => esc_html__('Months', 'wc-donation'),
		'year'  => esc_html__('Years', 'wc-donation'),
	);
	

	?>

	<div id="wps_sfw_product_target_section" class="wc_donation_subscription panel woocommerce_options_panel hidden" style="display: block;">
		<p>
			<label for="wc-donation-recurring"><?php echo esc_attr( __( 'Display Type', 'wc-donation' ) ); ?></label>
			<select name='wc-donation-recurring' id="wc-free-donation-recurring">
				<?php
				foreach ( WcDonation::DISPLAY_RECURRING_TYPE() as $key => $value ) {
					echo '<option value="' . esc_attr( $key ) . '"' .
					selected( $RecurringDisp, $key ) . '>' .
					esc_attr( $value ) . '</option>';
				}
				?>
			</select>
		</p>

		<p id="wc-free-donation-recurring-text">
			<label ><?php echo esc_html__('Recurring Text', 'wc-donation' ); ?></label>
			<input type="text" id="wc-donation-recurring-txt" name="wc-donation-recurring-txt" placeholder="Enter Donation Recurring Text" value="<?php echo esc_attr( $recurring_text ); ?>">
		</p>

		<p class="wc_donation_free_subscription">
			<label for="wps_sfw_subscription_number"><?php echo esc_html('Subscriptions Per Interval', 'wc-donation'); ?></label>
			<input type="number" class="short wc_input_number" min="1" required="" name="wps_sfw_subscription_number" id="wps_sfw_subscription_number" value="<?php echo esc_attr( $wps_sfw_subscription_number ); ?>" placeholder="Enter subscription interval"> 
			<select id="wps_sfw_subscription_interval" name="wps_sfw_subscription_interval" class="wps_sfw_subscription_interval">
			 <?php foreach ($intervals as $value => $label) : ?>
					<option value="<?php echo esc_attr($value); ?>" <?php selected($wps_sfw_subscription_interval, $value); ?>>
						<?php echo esc_attr($label); ?>
					</option>
			 <?php endforeach; ?>
			</select>
			<span class="woocommerce-help-tip" aria-label="Choose the subscriptions time interval for the product &quot;for example 10 days&quot;"></span>
		</p>

		<p class="wc_donation_free_subscription ">
			<label for="wps_sfw_subscription_expiry_number"><?php echo esc_html('Subscriptions Expiry Interval', 'wc-donation'); ?></label>
			<input type="number" class="short wc_input_number" min="1" name="wps_sfw_subscription_expiry_number" id="wps_sfw_subscription_expiry_number" value="<?php echo esc_attr($wps_sfw_subscription_expiry_number); ?>" placeholder="Enter subscription expiry"> 
			<select id="wps_sfw_subscription_expiry_interval" name="wps_sfw_subscription_expiry_interval" class="wps_sfw_subscription_expiry_interval">
				<?php foreach ($intervals as $value => $label) : ?>
					<option value="<?php echo esc_attr($value); ?>" <?php selected($wps_sfw_subscription_expiry_interval, $value); ?>>
						<?php echo esc_attr($label); ?>
					</option>
				<?php endforeach; ?>
			</select>
			 <span class="woocommerce-help-tip" aria-label="Choose the subscriptions expiry time interval for the product &quot;leave empty for unlimited&quot;"></span>		
		</p>



	</div>
	<?php
}
