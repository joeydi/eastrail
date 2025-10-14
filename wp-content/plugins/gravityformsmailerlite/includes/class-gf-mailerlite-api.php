<?php

// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Gravity Forms MailerLite API Library.
 *
 * @since 1.0
 * @package GravityForms
 * @author Rocketgenius
 * @copyright Copyright (c) 2024, Rocketgenius
 */

class GF_MailerLite_API {

	/**
	 * MailerLite account API key
	 *
	 * @since 1.0
	 * @access protected
	 * @var string $api_key MailerLite account API key.
	 */
	protected $api_key = null;

	/**
	 * MailerLite API URL
	 *
	 * @since 1.0
	 * @access protected
	 * @var string $api_url MailerLite API URL.
	 */
	protected $api_url = 'https://connect.mailerlite.com/api/';

	/**
	 * Initialize API library
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @param string $api_key MailerLite account API key.
	 */
	public function __construct( $api_key = null ) {

		$this->api_key = $api_key;

	}

	/**
	 * Tests the API Key by getting the total number of subscribers.
	 *
	 * @since 1.0
	 *
	 * @return WP_Error|true
	 */
	public function is_api_key_valid() {
		$response = $this->make_request( 'subscribers?limit=0' );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		return isset( $response['total'] ) ? true : new WP_Error( 'api_key_error', rgar( $response, 'message' ) );
	}

	// # GROUPS --------------------------------------------------------------------------------------------------------

	/**
	 * Get all MailerLite groups.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @uses GF_MailerLite_API::make_request()
	 *
	 * @return array
	 */

	public function get_groups() {

		return $this->make_request( 'groups', array(), 'GET', 'data' );

	}

	/**
	 * Get a specific MailerLite group.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @param string $group_id Group ID.
	 *
	 * @uses GF_MailerLite_API::make_request()
	 *
	 * @return array
	 */
	public function get_group( $group_id ) {
		return $this->make_request( 'groups/' . $group_id , array(), 'GET', 'data' );
	}

	// # FIELDS --------------------------------------------------------------------------------------------------------

	/**
	 * Returns the fields.
	 *
	 * @since 1.0
	 *
	 * @return array|WP_Error
	 */
	public function get_fields() {
		return $this->make_request( 'fields', array(), 'GET', 'data' );
	}

	/**
	 * Creates a new field.
	 *
	 * @since 1.0
	 *
	 * @param array $field The new field properties.
	 *
	 * @return array|WP_Error
	 */
	public function create_field( $field ) {
		return $this->make_request( 'fields', $field, 'POST', 'data', array( 200, 201 ) );
	}

	// # SUBSCRIBERS ---------------------------------------------------------------------------------------------------

	/**
	 * Gets a subscriber by email address.
	 *
	 * @since 1.0
	 *
	 * @param string $email The subscriber email address.
	 *
	 * @return array|WP_Error
	 */
	public function get_subscriber( $email ) {
		return $this->make_request( 'subscribers/' . $email, array(), 'GET', 'data' );
	}

	/**
	 * Adds or updates the subscriber.
	 *
	 * @since 1.0
	 *
	 * @param array $subscriber The subscriber properties.
	 *
	 * @return array|WP_Error
	 */
	public function upsert_subscriber( $subscriber ) {
		return $this->make_request( 'subscribers', $subscriber, 'POST', 'data', array( 200, 201 ) );
	}

	// # REQUEST METHODS -----------------------------------------------------------------------------------------------

	/**
	 * Make API request.
	 *
	 * @since 1.0
	 * @since 1.2 Added the $expected_code param.
	 *
	 * @param string    $action        Request action.
	 * @param array     $options       Request options.
	 * @param string    $method        Request method. Defaults to GET.
	 * @param string    $return_key    Array key from response to return. Defaults to null (return full response).
	 * @param int|int[] $expected_code The expected HTTP response code or an array of codes.
	 *
	 * @return array|WP_Error
	 */
	private function make_request( $action, $options = array(), $method = 'GET', $return_key = null, $expected_code = 200 ) {

		// Get API Key
		$api_key = $this->api_key;

		// Build base request URL. swapped out
		$request_url = $this->api_url . $action;

		// Add request URL parameters, if needed.
		if ( 'GET' === $method && ! empty( $options ) ) {
			$request_url = add_query_arg( urlencode_deep( $options ), $request_url );
		}

		// Build request headers.
		$headers = array(
			'Authorization' => 'Bearer ' . $api_key,
			'Content-Type'  => 'application/json',
			'Accept'        => 'application/json',
		);

		// Build request arguments.
		$args = array(
			'user-agent' => sprintf( 'Gravity Forms MailerLite/%s (%s)', GF_MAILERLITE_VERSION, esc_url( site_url() ) ),
			'headers'    => $headers,
			'method'     => $method,
			'body'       => $method !== 'GET' ? json_encode( $options ) : null,
			/**
			 * Filters if SSL verification should occur.
			 *
			 * @since 1.0
			 *
			 * @param bool   $sslverify   If the SSL certificate should be verified. Defaults to false.
			 * @param string $request_url The request URL.
			 *
			 * @return bool
			 */
			'sslverify'  => apply_filters( 'https_local_ssl_verify', false, $request_url ),

			/**
			 * Sets the HTTP timeout, in seconds, for the request.*
			 * @since 1.0
			 *
			 * @param int    $timeout     The HTTP request timeout in seconds. Default is 30.
			 * @param string $request_url The request URL.
			 *
			 * @return int
			 */
			'timeout'    => apply_filters( 'http_request_timeout', 30, $request_url ),
		);

		// Get request response.
		$response = wp_remote_request( $request_url, $args );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// Decode response.
		$result = gf_mailerlite()->maybe_decode_json( $response['body'] );

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( ( is_int( $expected_code ) && $response_code !== $expected_code ) || ( is_array( $expected_code ) && ! in_array( $response_code, $expected_code, true ) ) ) {
			if ( rgar( $result, 'error' ) ) {
				return new WP_Error( rgars( $result, 'error/code' ), rgars( $result, 'error/message' ) );
			}

			if ( rgar( $result, 'message' ) ) {
				return new WP_Error( $response_code, $result['message'], rgar( $result, 'errors' ) );
			}

			return new WP_Error( $response_code, wp_remote_retrieve_response_message( $response ), $response );
		}

		// If a return key is defined and array item exists, return it.
		if ( ! empty( $return_key ) && isset( $result[ $return_key ] ) ) {
			return $result[ $return_key ];
		}

		return $result;
	}

}
