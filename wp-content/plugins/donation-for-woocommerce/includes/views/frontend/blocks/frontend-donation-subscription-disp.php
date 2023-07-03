<?php
if ( 'user' == $RecurringDisp && class_exists('WC_Subscriptions') ) {
	$interval 	= !empty( $object->campaign['interval'] ) ? $object->campaign['interval'] : '1';
	$period 	= !empty( $object->campaign['period'] ) ? $object->campaign['period'] : 'day';
	$length 	= isset( $object->campaign['length'] ) ? $object->campaign['length'] : '1';
	$period_arr = '<select name="_subscription_period" class="_subscription_period">'; 
	foreach ( wcs_get_available_time_periods() as $key => $value ) {
		$period_arr	.= '<option value="' . esc_attr( $key ) . '" ' . selected( $period, $key, false ) . ' >' . esc_attr( $value ) . '</option>';
	}
	$period_arr	.= '</select>';
	/**
	* Filter.
	* 
	* @since 3.4.5
	*/	
	$is_checked = apply_filters('wc_donation_is_recurring_checkbox', '');
	?>
	<div class="row3">
		<label class="wc-label-radio recurring-label"><input class="donation-is-recurring" id="is-recurring-<?php echo esc_attr( $campaign_id ) . '_' . esc_attr( $wp_rand ); ?>" type="checkbox" data-id="is-recurring-<?php echo esc_attr( $campaign_id ) . '_' . esc_attr( $wp_rand ); ?>" name="wc_is_recurring" value="yes" <?php echo esc_attr($is_checked); ?> ><?php echo esc_attr( $recurring_text, 'wc-donation' ); ?>  <div class="checkmark"></div>
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
			<?php echo nl2br($period_arr); ?> 
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
		</label>
	</div>		
	<?php
}
