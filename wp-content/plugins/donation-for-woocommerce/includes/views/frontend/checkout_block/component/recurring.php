<?php 
if ( ! class_exists( 'WC_Subscriptions' ) ) {
	return;
}

$interval   = '1';
$period     = 'day';
$length     = '1';

?>
<div class="wc-donation-repeating" style="display:none;">
	<h3>Recurring Cycle</h3>
	<div class="repeating-selection wc-donation-in-action">
		<?php
			/**
			* Filter.
			* 
			* @since 3.4.5
			*/  
			$is_checked = apply_filters('wc_donation_is_recurring_checkbox', '');
		
			/**
			* Action wc_donation_before_subscription_interval.
			* 
			* @since 3.9.5
			*/
			do_action( 'wc_donation_before_subscription_interval' );
		?>
			<select name='_subscription_period_interval' id="_subscription_period_interval">
			<?php
			foreach ( wcs_get_subscription_period_interval_strings() as $key => $value ) {
				?>
				<option value="<?php echo esc_attr($key); ?>"><?php echo esc_attr( $value ); ?></option>
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
					echo '<option value="' . esc_attr( $key ) . '">' . esc_attr( $value ) . '</option>';
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
					<option value="<?php echo esc_attr($key); ?>"><?php echo esc_attr( $value ); ?></option>
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