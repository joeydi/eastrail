<?php

class WcdonationSubscription {
	
	/**
	 * Class Constructor
	 */
	public function __construct() {     
		add_action('wp_ajax_wc_donation_get_sub_length_by_sub_period', array( $this, 'wc_donation_get_sub_length_by_sub_period' ) );
		add_action('wp_ajax_nopriv_wc_donation_get_sub_length_by_sub_period', array( $this, 'wc_donation_get_sub_length_by_sub_period' ) );

		add_filter( 'wcs_recurring_cart_next_payment_date', array( $this, 'next_payment_date' ), 95 );

		add_filter( 'wcs_recurring_cart_end_date', array( $this, 'end_date' ), 95 );
	}

	public function wc_donation_get_sub_length_by_sub_period() {

		if ( !isset( $_POST['nonce'] ) || ( isset( $_POST['nonce'] ) && !wp_verify_nonce(sanitize_text_field($_POST['nonce']), '_wcdnonce' ) ) ) {
			wp_die( 'Not Authorized' );
		}
		
		if ( isset($_POST['period']) && !empty(sanitize_text_field($_POST['period'])) ) {

			if ( isset($_POST['length']) && !empty(sanitize_text_field($_POST['length'])) ) {
				$result['range'] = wcs_get_subscription_ranges(sanitize_text_field($_POST['period']))[sanitize_text_field($_POST['length'])];
				print_r(json_encode($result));
				wp_die(); // return proper response
			} else {
				$result['range'] = wcs_get_subscription_ranges(sanitize_text_field($_POST['period']));
				print_r(json_encode($result));
				wp_die();
			}
		}
	}
	
	public static function get_first_renewal_payment_time( $product, $billing_interval, $billing_length, $period, $timezone = 'gmt' ) {

		if ( ! WC_Subscriptions_Product::is_subscription( $product ) ) {
			return 0;
		}

		if ( $billing_interval !== $billing_length ) {

			$from_date = gmdate( 'Y-m-d H:i:s' );
			$from_date_param = $from_date;
			$site_time_offset = (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
			$first_renewal_timestamp = wcs_add_time( $billing_interval, $period, wcs_date_to_time( $from_date ) + $site_time_offset );

			if ( 'site' !== $timezone ) {
				$first_renewal_timestamp -= $site_time_offset;
			}
		} else {
			$first_renewal_timestamp = 0;
		}

		return $first_renewal_timestamp;
	}

	public static function get_expiration_date( $subscription_length, $period ) {

		if ( $subscription_length > 0 ) {

			$from_date = gmdate( 'Y-m-d H:i:s' );

			$expiration_date = gmdate( 'Y-m-d H:i:s', wcs_add_time( $subscription_length, $period, wcs_date_to_time( $from_date ) ) );

		} else {

			$expiration_date = 0;

		}

		return $expiration_date;
	}

	public function next_payment_date( $first_renewal_date ) {
		if ( empty( WC()->cart ) ) {
			return $first_renewal_date;
		}
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

			if ( ! isset( $cart_item['campaign_id'], $cart_item['subscription_period_interval'], $cart_item['subscription_length'], $cart_item['subscription_period'] ) ) {
				return $first_renewal_date;
			}
			
			$first_renewal_timestamp = self::get_first_renewal_payment_time( 
				$cart_item['data'],
				$cart_item['subscription_period_interval'],
				$cart_item['subscription_length'],
				$cart_item['subscription_period'],
				gmdate( 'Y-m-d H:i:s' )
			);
		}
		if ( $first_renewal_timestamp > 0 ) {
			return gmdate( 'Y-m-d H:i:s', $first_renewal_timestamp );
		}
		return $first_renewal_timestamp;
	}

	public function end_date( $end_date ) {
		if ( empty( WC()->cart ) ) {
			return $end_date;
		}
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			if ( ! isset( $cart_item['campaign_id'], $cart_item['subscription_period_interval'], $cart_item['subscription_length'], $cart_item['subscription_period'] ) ) {
				return $end_date;
			}
			$expiration = self::get_expiration_date( 
				$cart_item['subscription_length'],
				$cart_item['subscription_period']
			);
		}

		if ( $expiration > 0 ) {
			return $expiration;
		}
		return $expiration;
	}
}

new WcdonationSubscription();
