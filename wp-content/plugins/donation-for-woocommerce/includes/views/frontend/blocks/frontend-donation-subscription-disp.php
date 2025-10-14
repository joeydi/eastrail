<?php
if ( 'user' == $RecurringDisp && class_exists('WC_Subscriptions') ) {
	$interval   = !empty( $object->campaign['interval'] ) ? $object->campaign['interval'] : '1';
	$period     = !empty( $object->campaign['period'] ) ? $object->campaign['period'] : 'day';
	$length     = isset( $object->campaign['length'] ) ? $object->campaign['length'] : '1';
	/**
	* Filter.
	* 
	* @since 3.4.5
	*/  
	$is_checked = apply_filters('wc_donation_is_recurring_checkbox', '');
	?>
	<div class="row3">
		<label class="wc-label-radio recurring-label">
			<input class="donation-is-recurring" id="is-recurring-<?php echo esc_attr( $campaign_id ) . '_' . esc_attr( $wp_rand ); ?>" type="checkbox" data-id="is-recurring-<?php echo esc_attr( $campaign_id ) . '_' . esc_attr( $wp_rand ); ?>" name="wc_is_recurring" value="yes" <?php echo esc_attr($is_checked); ?> ><?php echo esc_attr( $recurring_text, 'wc-donation' ); ?>  
			<div class="checkmark"></div>
		</label>
		<div class="donation_subscription">
			<?php 
			/**
			* Action.
			* 
			* @since 3.4.5
			*/
			do_action( 'wc_donation_before_subscription_interval' );
			?>
			<select name='_subscription_period_interval' id="_subscription_period_interval">
			<?php
			foreach ( wcs_get_subscription_period_interval_strings() as $key => $value ) {
				?>
				<option value="<?php echo esc_attr($key); ?>" <?php selected( $interval, $key ); ?>><?php echo esc_attr( $value ); ?></option>
				<?php
			}
			?>
			</select>
			<?php
			/**
			* Action.
			* 
			* @since 3.4.5
			*/
			do_action( 'wc_donation_after_subscription_interval' );
			?>
			<?php
			/**
			* Action.
			* 
			* @since 3.4.5
			*/
			do_action( 'wc_donation_before_subscription_period' );
			?>
			<select name="_subscription_period" class="_subscription_period">
				<?php
				foreach ( wcs_get_available_time_periods() as $key => $value ) {
					echo '<option value="' . esc_attr( $key ) . '" ' . selected( $period, $key, false ) . ' >' . esc_attr( $value ) . '</option>';
				}
				?>
			</select>			
			<?php
			/**
			* Action.
			* 
			* @since 3.4.5
			*/
			do_action( 'wc_donation_after_subscription_period' );
			?>
			<?php
			/**
			* Action.
			* 
			* @since 3.4.5
			*/
			do_action( 'wc_donation_before_subscription_length' );
			?>
			<select name='_subscription_length' id="_subscription_length">
				<?php
				/**
				* Filter.
				* 
				* @since 3.4.5
				*/
				$period = apply_filters( 'wc_donation_recurring_default_period', $period);
				foreach ( wcs_get_subscription_ranges( $period ) as $key => $value ) {
					?>
					<option value="<?php echo esc_attr($key); ?>" <?php selected( $length, $key ); ?>><?php echo esc_attr( $value ); ?></option>
					<?php
				}
				?>
			</select>
			<?php
			/**
			* Action.
			* 
			* @since 3.4.5
			*/
			do_action( 'wc_donation_after_subscription_length' );
			?>
		</div>
	</div>		
	<?php
}


// WC Swings
if ( 'user' == $RecurringDisp && class_exists('Subscriptions_For_Woocommerce') && ! class_exists('WC_Subscriptions') ) {
	/**
	* Filter wc_donation_is_recurring_checkbox.
	* 
	* @since 3.8.1
	*/
	$is_checked = apply_filters('wc_donation_is_recurring_checkbox', '');
	$recurring_text = !empty( get_post_meta ( $campaign_id, 'wc-donation-recurring-txt', true  ) ) ? get_post_meta ( $campaign_id, 'wc-donation-recurring-txt', true  ) : '';
	$intervals = array(
		'day'  => esc_html__('Day(s)', 'wc-donation'),
		'week'  => esc_html__('Weeks', 'wc-donation'),
		'month' => esc_html__('Months', 'wc-donation'),
		'year'  => esc_html__('Years', 'wc-donation'),
	);
	?>
	<div class="wc_donation_subscription_table">
	<table>
		<tr>
			<td>
				<p id="wc-free-donation-recurring-text">
					<label><?php echo esc_attr( $recurring_text ); ?></label>
				</p>
			</td>
			<td>
				<input class="donation-is-recurring" id="is-recurring-<?php echo esc_attr( $campaign_id ) . '_' . esc_attr( $wp_rand ); ?>" 
					   type="checkbox" data-id="is-recurring-<?php echo esc_attr( $campaign_id ) . '_' . esc_attr( $wp_rand ); ?>" 
					   name="wc_is_recurring" value="yes" <?php echo esc_attr($is_checked); ?>>
			</td>
		</tr>
		<tr class="subscription-options">
			<td>
				<label for="wps_sfw_subscription_number"><?php echo esc_html('Subscriptions Per Interval', 'wc-donation'); ?></label>
			</td>
			<td>
				<input type="number" class="short wc_input_number wps_sfw_subscription_number" min="1" required="" 
					   name="wps_sfw_subscription_number" id="wps_sfw_subscription_number" value="" placeholder="Subscription interval">
			</td>
			<td>
				<div class="wc_donation_subscription_selectors">
					<select id="wps_sfw_subscription_interval" name="wps_sfw_subscription_interval" class="wps_sfw_subscription_interval">
						<?php foreach ($intervals as $value => $label) : ?>
							<option value="<?php echo esc_attr($value); ?>" <?php selected($value); ?>>
								<?php echo esc_attr($label); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			</td>
		</tr>
		<tr class="subscription-options">
			<td>
				<label for="wps_sfw_subscription_number"><?php echo esc_html('Subscriptions Expiry Interval', 'wc-donation'); ?></label>
			</td>
			<td>
				<input type="number" class="short wc_input_number wps_sfw_subscription_expiry_number" min="1" 
					   name="wps_sfw_subscription_expiry_number" id="wps_sfw_subscription_expiry_number" value="" placeholder="Subscription expiry">
			</td>
			<td>
				<div class="wc_donation_subscription_selectors">
					<select id="wps_sfw_subscription_expiry_interval" name="wps_sfw_subscription_expiry_interval" class="wps_sfw_subscription_expiry_interval">
						<?php foreach ($intervals as $value => $label) : ?>
							<option value="<?php echo esc_attr($value); ?>" <?php selected($value); ?>>
								<?php echo esc_attr($label); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			</td>
		</tr>
	</table>
</div>

	<?php
}