<?php

class WcdonationSubscription {
	
	/**
	 * Class Constructor
	 */
	public function __construct() {		
		add_action('wp_ajax_wc_donation_get_sub_length_by_sub_period', array($this, 'wc_donation_get_sub_length_by_sub_period') );
		add_action('wp_ajax_nopriv_wc_donation_get_sub_length_by_sub_period', array($this, 'wc_donation_get_sub_length_by_sub_period') );

	}

	public function wc_donation_get_sub_length_by_sub_period () {

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
}

new WcdonationSubscription();
