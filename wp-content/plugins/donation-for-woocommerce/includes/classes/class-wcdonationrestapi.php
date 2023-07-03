<?php
/**
 * File to  define API settings .
 *
 * @package donation
 */

/**
 *  Class   WcdonationAPI .
 *  Add plugin settings .
 */
class WcdonationAPI {

	private $_api_namespace = 'wc-donation';

	private $_campaign_route = 'campaign';

	private $_DONATION_API = false;

	/**
	 * Add plugin menu page .
	 */
	public function __construct() {	

		$this->_DONATION_API = get_option( 'wc-donation-api', false );		

		add_action('rest_api_init', array( $this, 'wc_donation_register_routes') );

	}

	public function wc_donation_register_routes() {
		register_rest_route( 
			$this->_api_namespace . '/v1',
			$this->_campaign_route,
			array(
				'methods' => WP_REST_SERVER::READABLE,
				'permission_callback' => array( $this, 'check_permission_for_read'),			  
				'callback'    => array( $this, 'get_campaign_data'),
			)
		);

		register_rest_route( 
			$this->_api_namespace . '/v1',
			$this->_campaign_route . '/(?P<id>\d+)',      
			array(
				'methods' => WP_REST_SERVER::READABLE,
				'permission_callback' => array( $this, 'check_permission_for_read'),			  
				'callback'    => array( $this, 'get_campaign_data'),
			)
		);

		register_rest_route(
			$this->_api_namespace . '/v1',
			$this->_campaign_route, 
			array(
				'methods' => WP_REST_SERVER::CREATABLE,
				'permission_callback' => array( $this, 'check_permission'),
				'callback' => array( $this, 'create_campaign'),
			)
		   );

		   register_rest_route(
			$this->_api_namespace . '/v1',
			$this->_campaign_route . '/(?P<id>\d+)', 
			array(
				'methods' => WP_REST_SERVER::EDITABLE,
				'permission_callback' => array( $this, 'check_permission'),
				'callback' => array( $this, 'edit_campaign'),
			)
		   );

		   register_rest_route(
			$this->_api_namespace . '/v1',
			$this->_campaign_route . '/(?P<id>\d+)', 
			array(
				'methods' => WP_REST_SERVER::DELETABLE,
				'permission_callback' => array( $this, 'check_permission'),
				'callback' => array( $this, 'delete_campaign'),
			)
		   );
	}

	public function check_permission_for_read() {		

		if ( 'yes' !== $this->_DONATION_API ) {
			return new WP_Error( 'DONATION_API_DSIABLED', esc_html__( 'The WC Donation API is disabled on this site', 'wc-donation' ), array( 'status' => 400 ) );
		}

		return true;
	}

	public function check_permission() {

		if ( 'yes' !== $this->_DONATION_API ) {
			return new WP_Error( 'DONATION_API_DSIABLED', esc_html__( 'The WC Donation API is disabled on this site', 'wc-donation' ), array( 'status' => 400 ) );
		}
	
		// Restrict endpoint to only users who have the edit_posts capability.
		$user = wp_get_current_user();
		/**
		* Filter.
		* 
		* @since 3.4.5
		*/
		$allowed_roles = apply_filters('wc_donation_allow_role_for_create_donation_API', array( 'editor', 'administrator' ));
		if ( !$user->roles ) {
			return new WP_Error( 'FORBIDDEN', esc_html__( 'You are not allowed to access', 'wc-donation' ), array( 'status' => 403 ) );
		}
		
		if ( ! array_intersect( $allowed_roles, $user->roles ) ) {
			return new WP_Error( 'NOT_AUTHORIZED', esc_html__( 'You are not authorized to do that.', 'wc-donation' ), array( 'status' => 401 ) );
		}

		return true;
		

	}

	public function delete_campaign( $request ) {
		
		// $param = $request->get_param( 'some_param' );
		// You can get the combined, merged set of parameters:
		// $parameters = $request->get_params();

		if ( isset( $request['id'] ) ) {
			$post = get_post( sanitize_text_field( $request['id'] ) );
			if ( $post && 'wc-donation' === $post->post_type ) {

				$prod_id = get_post_meta( $post->ID, 'wc_donation_product', true );

				$cart_donation = get_option('wc-donation-cart-product');
				$checkout_donation = get_option('wc-donation-checkout-product');
				$round_donation = get_option('wc-donation-round-product');

				if ( $cart_donation == $post->ID ) {
					update_option ('wc-donation-cart-product', '');
					update_option ('wc-donation-on-cart', 'no');
				}

				if ( $checkout_donation == $post->ID ) {
					update_option ('wc-donation-checkout-product', '');
					update_option ('wc-donation-on-checkout', 'no');
				}

				if ( $round_donation == $post->ID ) {
					update_option ('wc-donation-round-product', '');
					update_option ('wc-donation-on-round', 'no');
				}

				wp_delete_post( $prod_id, true); // Set to False if you want to send them to Trash.
				wp_delete_post( $post->ID, true);			
				return array(					
					'ID' => $post->ID,
					'message' => esc_html__( 'Campaign deleted successfully', 'wc-donation' )
				);
			} else {
				return new WP_Error( 'NOT_FOUND', esc_html__('Unable to delete. Campaign ID not found!', 'wc-donation'), array( 'status' => 404 ) );
			}			
		}		
	}

	public function edit_campaign( $request ) {
		
		// $param = $request->get_param( 'some_param' );
		// You can get the combined, merged set of parameters:
		// $parameters = $request->get_params();	

		$parameters = $request->get_params();
		$result = false;
		$parameters['api_call'] = 'edit';
		$_POST = $parameters;

		if ( isset( $request['id'] ) && ! empty( sanitize_text_field( $request['id'] ) ) ) {

			if ( isset( $parameters['title'] ) && empty( trim( $parameters['title'] ) ) ) {
				return new WP_Error( 'Error', 'Campaign title is required', array( 'status' => 400 ) );
			}

			if ( isset( $parameters['status'] ) && empty( trim( $parameters['status'] ) ) ) {
				return new WP_Error( 'Error', 'Campaign status is required', array( 'status' => 400 ) );
			} else {
				if ( isset( $parameters['status'] ) && 'publish' != $parameters['status'] && 'trash' != $parameters['status'] ) {
					return new WP_Error( 'Error', 'Campaign status value is incorrect', array( 'status' => 400 ) );
				}
			}

			if ( isset( $parameters['wc-donation-amount-display-option'] ) && empty( trim( $parameters['wc-donation-amount-display-option'] ) ) ) {
				return new WP_Error( 'Error', 'Amount Type is required', array( 'status' => 400 ) );
			} else {
				if ( isset( $parameters['wc-donation-amount-display-option'] ) && 'predefined' != $parameters['wc-donation-amount-display-option'] && 'free-value' != $parameters['wc-donation-amount-display-option'] && 'both' != $parameters['wc-donation-amount-display-option'] ) {
					return new WP_Error( 'Error', 'Amount Type value is incorrect', array( 'status' => 400 ) );
				}
			}

			if ( isset( $parameters['wc-donation-amount-display-option'] ) && ( 'predefined' === $parameters['wc-donation-amount-display-option'] || 'both' === $parameters['wc-donation-amount-display-option'] ) ) {
				if ( ! isset( $parameters['pred-amount'] ) || ( isset( $parameters['pred-amount'] ) && empty( $parameters['pred-amount'] ) ) ) {
					return new WP_Error( 'Error', 'Donation level amount is required', array( 'status' => 400 ) );	
				}

				if ( ! isset( $parameters['pred-label'] ) || ( isset( $parameters['pred-label'] ) && empty( $parameters['pred-label'] ) ) ) {
					return new WP_Error( 'Error', 'Donation level amount label is required', array( 'status' => 400 ) );
				}

				if ( is_array( $parameters['pred-label'] ) && is_array( $parameters['pred-amount'] ) ) {
					$label_count = count( $parameters['pred-label'] );
					$amount_count = count( $parameters['pred-amount'] );
					if ( $label_count != $amount_count ) {
						return new WP_Error( 'Error', 'Count should be same for donation amount and donation label arrays', array( 'status' => 400 ) );
					}
				}
			}

			if ( isset( $parameters['wc-donation-amount-display-option'] ) && ( 'free-value' === $parameters['wc-donation-amount-display-option'] || 'both' === $parameters['wc-donation-amount-display-option'] ) ) {
				if ( ! isset( $parameters['free-min-amount'] ) || ( isset( $parameters['free-min-amount'] ) && empty( $parameters['free-min-amount'] ) ) ) {
					return new WP_Error( 'Error', 'Free minimum amount is required', array( 'status' => 400 ) );
				}

				if ( ! isset( $parameters['free-max-amount'] ) || ( isset( $parameters['free-max-amount'] ) && empty( $parameters['free-max-amount'] ) ) ) {
					return new WP_Error( 'Error', 'Free minimum amount is required', array( 'status' => 400 ) );
				}
			}

			if ( isset( $parameters['wc-donation-display-donation-type'] ) && 'select' !== $parameters['wc-donation-display-donation-type'] && 'radio' !== $parameters['wc-donation-display-donation-type'] && 'label' !== $parameters['wc-donation-display-donation-type'] ) {
				return new WP_Error( 'Error', 'Donation display type value is incorrect.', array( 'status' => 400 ) );
			}

			if ( isset( $parameters['wc-donation-currency-position'] ) && 'before' !== $parameters['wc-donation-currency-position'] && 'after' !== $parameters['wc-donation-currency-position'] ) {
				return new WP_Error( 'Error', 'Currency Position value is incorrect.', array( 'status' => 400 ) );
			}

			if ( isset( $parameters['wc-donation-recurring'] ) && 'enabled' === $parameters['wc-donation-recurring']  ) {
				if ( ! isset( $parameters['_subscription_period_interval'] ) || ( isset( $parameters['_subscription_period_interval'] ) && empty( $parameters['_subscription_period_interval'] ) ) ) {
					return new WP_Error( 'Error', 'subscription interval is required', array( 'status' => 400 ) );
				}

				if ( ! isset( $parameters['_subscription_period'] ) || ( isset( $parameters['_subscription_period'] ) && empty( $parameters['_subscription_period'] ) ) ) {
					return new WP_Error( 'Error', 'subscription period is required', array( 'status' => 400 ) );
				}

				if ( ! isset( $parameters['_subscription_length'] ) || ( isset( $parameters['_subscription_length'] ) && '' == $parameters['_subscription_length'] && 0 != $parameters['_subscription_length'] ) ) {
					return new WP_Error( 'Error', 'subscription length is required', array( 'status' => 400 ) );
				}
			}

			if ( isset( $parameters['wc-donation-recurring'] ) && 'user' === $parameters['wc-donation-recurring']  ) {
				if ( ! isset( $parameters['wc-donation-recurring-txt'] ) || ( isset( $parameters['wc-donation-recurring-txt'] ) && empty( $parameters['wc-donation-recurring-txt'] ) ) ) {
					return new WP_Error( 'Error', 'recurring text is required', array( 'status' => 400 ) );
				}
			}

			if ( isset( $parameters['wc-donation-goal-display-option'] ) && 'enabled' === $parameters['wc-donation-goal-display-option']  ) {
				if ( ! isset( $parameters['wc-donation-goal-display-type'] ) || ( isset( $parameters['wc-donation-goal-display-type'] ) && empty( $parameters['wc-donation-goal-display-type'] ) ) ) {
					return new WP_Error( 'Error', 'goal type is required', array( 'status' => 400 ) );
				}
			}

			if ( isset( $parameters['wc-donation-goal-display-type'] ) && ( 'fixed_amount' === $parameters['wc-donation-goal-display-type'] || 'percentage_amount' === $parameters['wc-donation-goal-display-type'] ) ) {
				
				if ( ! isset( $parameters['wc-donation-goal-fixed-amount-field'] ) || ( isset( $parameters['wc-donation-goal-fixed-amount-field'] ) && empty( $parameters['wc-donation-goal-fixed-amount-field'] ) ) ) {
					return new WP_Error( 'Error', 'goal fixed amount is required', array( 'status' => 400 ) );
				}
			}

			if ( isset( $parameters['wc-donation-goal-display-type'] ) && 'no_of_donation' === $parameters['wc-donation-goal-display-type'] ) {
				
				if ( ! isset( $parameters['wc-donation-goal-no-of-donation-field'] ) || ( isset( $parameters['wc-donation-goal-no-of-donation-field'] ) && empty( $parameters['wc-donation-goal-no-of-donation-field'] ) ) ) {
					return new WP_Error( 'Error', 'No. of donation is required', array( 'status' => 400 ) );
				}
			}

			if ( isset( $parameters['wc-donation-goal-display-type'] ) && 'no_of_days' === $parameters['wc-donation-goal-display-type'] ) {
			
				if ( ! isset( $parameters['wc-donation-goal-no-of-days-field'] ) || ( isset( $parameters['wc-donation-goal-no-of-days-field'] ) && empty( $parameters['wc-donation-goal-no-of-days-field'] ) ) ) {
					return new WP_Error( 'Error', 'No. of day is required', array( 'status' => 400 ) );
				} else {					
					$parameters['wc-donation-goal-no-of-days-field'] = gmdate('d-M-Y', strtotime($parameters['wc-donation-goal-no-of-days-field']));
				}
			}

			if ( isset( $parameters['donation-cause-img'] ) && is_array( $parameters['donation-cause-img'] ) ) {
				if ( count($parameters['donation-cause-img']) != count($parameters['donation-cause-name']) || count($parameters['donation-cause-img']) != count($parameters['donation-cause-desc']) ) {
					return new WP_Error( 'Error', 'Array count for donation cause image, cause name and casuse desc are not same.', array( 'status' => 400 ) );

				}
			}

			$post = get_post( sanitize_text_field( $request['id'] ) );

			if ( $post && 'wc-donation' === $post->post_type ) {

				$result = wp_insert_post( 
					array(
						'ID'          => $post->ID,
						'post_title'  => isset($parameters['title']) ? $parameters['title'] : $post->post_title,
						'post_status' => isset($parameters['status']) ? $parameters['status'] : $post->post_status,
						'post_type'   => $post->post_type
					), 
					true, 
					true
				);
			} else {
				return new WP_Error( 'NOT_FOUND', esc_html__('Unable to update. Campaign ID not found!', 'wc-donation'), array( 'status' => 404 ) );
			}		
		}

		$result = get_post($result);
		$result->campaign_meta = self::get_campaign_metadata( $result->ID );
		return get_post($result);
	}

	public function create_campaign( $request ) {
		
		// $param = $request->get_param( 'some_param' );
		// You can get the combined, merged set of parameters:
		$parameters = $request->get_params();
		$result = false;
		$_POST = $parameters;

		if ( ! isset( $parameters['title'] ) || ( isset( $parameters['title'] ) && empty( trim( $parameters['title'] ) ) ) ) {
			return new WP_Error( 'Error', 'Campaign title is required', array( 'status' => 400 ) );
		}

		// if ( ! isset( $parameters['status'] ) || ( isset( $parameters['status'] ) && empty( trim( $parameters['status'] ) ) ) ) {
		// 	return new WP_Error( 'Error', 'Campaign status is required', array( 'status' => 400 ) );	
		// }

		if ( ! isset( $parameters['wc-donation-amount-display-option'] ) || ( isset( $parameters['wc-donation-amount-display-option'] ) && empty( trim( $parameters['wc-donation-amount-display-option'] ) ) ) ) {
			return new WP_Error( 'Error', 'Amount Type is required', array( 'status' => 400 ) );
		} else {
			if ( 'predefined' != $parameters['wc-donation-amount-display-option'] && 'free-value' != $parameters['wc-donation-amount-display-option'] && 'both' != $parameters['wc-donation-amount-display-option'] ) {
				return new WP_Error( 'Error', 'Amount Type value is incorrect', array( 'status' => 400 ) );
			}
		}

		if ( isset( $parameters['wc-donation-amount-display-option'] ) && ( 'predefined' === $parameters['wc-donation-amount-display-option'] || 'both' === $parameters['wc-donation-amount-display-option'] ) ) {
			if ( ! isset( $parameters['pred-amount'] ) || ( isset( $parameters['pred-amount'] ) && empty( $parameters['pred-amount'] ) ) ) {
				return new WP_Error( 'Error', 'Donation level amount is required', array( 'status' => 400 ) );	
			}

			if ( ! isset( $parameters['pred-label'] ) || ( isset( $parameters['pred-label'] ) && empty( $parameters['pred-label'] ) ) ) {
				return new WP_Error( 'Error', 'Donation level amount label is required', array( 'status' => 400 ) );
			}

			if ( is_array( $parameters['pred-label'] ) && is_array( $parameters['pred-amount'] ) ) {
				$label_count = count( $parameters['pred-label'] );
				$amount_count = count( $parameters['pred-amount'] );
				if ( $label_count != $amount_count ) {
					return new WP_Error( 'Error', 'Count should be same for donation amount and donation label arrays', array( 'status' => 400 ) );
				}
			}
		}

		if ( isset( $parameters['wc-donation-amount-display-option'] ) && ( 'free-value' === $parameters['wc-donation-amount-display-option'] || 'both' === $parameters['wc-donation-amount-display-option'] ) ) {
			if ( ! isset( $parameters['free-min-amount'] ) || ( isset( $parameters['free-min-amount'] ) && empty( $parameters['free-min-amount'] ) ) ) {
				return new WP_Error( 'Error', 'Free minimum amount is required', array( 'status' => 400 ) );
			}

			if ( ! isset( $parameters['free-max-amount'] ) || ( isset( $parameters['free-max-amount'] ) && empty( $parameters['free-max-amount'] ) ) ) {
				return new WP_Error( 'Error', 'Free maximum amount is required', array( 'status' => 400 ) );
			}
		}

		if ( isset( $parameters['wc-donation-recurring'] ) && 'enabled' === $parameters['wc-donation-recurring']  ) {
			if ( ! isset( $parameters['_subscription_period_interval'] ) || ( isset( $parameters['_subscription_period_interval'] ) && empty( $parameters['_subscription_period_interval'] ) ) ) {
				return new WP_Error( 'Error', 'subscription interval is required', array( 'status' => 400 ) );
			}

			if ( ! isset( $parameters['_subscription_period'] ) || ( isset( $parameters['_subscription_period'] ) && empty( $parameters['_subscription_period'] ) ) ) {
				return new WP_Error( 'Error', 'subscription period is required', array( 'status' => 400 ) );
			}

			if ( ! isset( $parameters['_subscription_length'] ) || ( isset( $parameters['_subscription_length'] ) && '' == $parameters['_subscription_length'] && 0 != $parameters['_subscription_length'] ) ) {
				return new WP_Error( 'Error', 'subscription length is required', array( 'status' => 400 ) );
			}
		}

		if ( isset( $parameters['wc-donation-display-donation-type'] ) && 'select' !== $parameters['wc-donation-display-donation-type'] && 'radio' !== $parameters['wc-donation-display-donation-type'] && 'label' !== $parameters['wc-donation-display-donation-type'] ) {
			return new WP_Error( 'Error', 'Donation display type value is incorrect.', array( 'status' => 400 ) );
		}

		if ( isset( $parameters['wc-donation-currency-position'] ) && 'before' !== $parameters['wc-donation-currency-position'] && 'after' !== $parameters['wc-donation-currency-position'] ) {
			return new WP_Error( 'Error', 'Currency Position value is incorrect.', array( 'status' => 400 ) );
		}

		if ( isset( $parameters['wc-donation-recurring'] ) && 'user' === $parameters['wc-donation-recurring']  ) {
			if ( ! isset( $parameters['wc-donation-recurring-txt'] ) || ( isset( $parameters['wc-donation-recurring-txt'] ) && empty( $parameters['wc-donation-recurring-txt'] ) ) ) {
				return new WP_Error( 'Error', 'recurring text is required', array( 'status' => 400 ) );
			}
		}

		if ( isset( $parameters['wc-donation-goal-display-option'] ) && 'enabled' === $parameters['wc-donation-goal-display-option']  ) {
			if ( ! isset( $parameters['wc-donation-goal-display-type'] ) || ( isset( $parameters['wc-donation-goal-display-type'] ) && empty( $parameters['wc-donation-goal-display-type'] ) ) ) {
				return new WP_Error( 'Error', 'goal type is required', array( 'status' => 400 ) );
			}
		}

		if ( isset( $parameters['wc-donation-goal-display-type'] ) && ( 'fixed_amount' === $parameters['wc-donation-goal-display-type'] || 'percentage_amount' === $parameters['wc-donation-goal-display-type'] ) ) {
			
			if ( ! isset( $parameters['wc-donation-goal-fixed-amount-field'] ) || ( isset( $parameters['wc-donation-goal-fixed-amount-field'] ) && empty( $parameters['wc-donation-goal-fixed-amount-field'] ) ) ) {
				return new WP_Error( 'Error', 'goal fixed amount is required', array( 'status' => 400 ) );
			}
		}

		if ( isset( $parameters['wc-donation-goal-display-type'] ) && 'no_of_donation' === $parameters['wc-donation-goal-display-type'] ) {
			
			if ( ! isset( $parameters['wc-donation-goal-no-of-donation-field'] ) || ( isset( $parameters['wc-donation-goal-no-of-donation-field'] ) && empty( $parameters['wc-donation-goal-no-of-donation-field'] ) ) ) {
				return new WP_Error( 'Error', 'No. of donation is required', array( 'status' => 400 ) );
			}
		}

		if ( isset( $parameters['wc-donation-goal-display-type'] ) && 'no_of_days' === $parameters['wc-donation-goal-display-type'] ) {
			
			if ( ! isset( $parameters['wc-donation-goal-no-of-days-field'] ) || ( isset( $parameters['wc-donation-goal-no-of-days-field'] ) && empty( $parameters['wc-donation-goal-no-of-days-field'] ) ) ) {
				return new WP_Error( 'Error', 'No. of day is required', array( 'status' => 400 ) );
			} else {					
				$parameters['wc-donation-goal-no-of-days-field'] = gmdate('d-M-Y', strtotime($parameters['wc-donation-goal-no-of-days-field']));					
			}
		}

		if ( isset( $parameters['donation-cause-img'] ) && is_array( $parameters['donation-cause-img'] ) ) {
			if ( count($parameters['donation-cause-img']) != count($parameters['donation-cause-name']) || count($parameters['donation-cause-img']) != count($parameters['donation-cause-desc']) ) {
				return new WP_Error( 'Error', 'Array count for donation cause image, cause name and casuse desc are not same.', array( 'status' => 400 ) );

			}
		}

		$result = wp_insert_post( 
			array(
				'post_title'  => $parameters['title'],
				//'post_status' => $parameters['status'],
				'post_status' => 'publish',
				'post_type'   => 'wc-donation'
			), 
			true, 
			true
		);

		$result = get_post($result);
		$result->campaign_meta = self::get_campaign_metadata( $result->ID );
		return get_post($result);
	}
	
	public function get_campaign_data( $data ) {		

		if ( isset( $data['id'] ) ) {

			$campaign = get_post( $data['id'] );			

			if ( empty($campaign) || ( ! empty($campaign) && 'wc-donation' !== $campaign->post_type ) ) {
				return new WP_Error( 'Error', 'Invalid campaign id', array( 'status' => 404 ) );
			}

			$campaign->campaign_meta = self::get_campaign_metadata( $data['id'] );

			if ( isset($campaign->campaign_meta['pred-amount'][0]) ) {				
				$campaign->campaign_meta['pred-amount'][0] = unserialize($campaign->campaign_meta['pred-amount'][0]);
			}

			if ( isset($campaign->campaign_meta['pred-label'][0]) ) {				
				$campaign->campaign_meta['pred-label'][0] = unserialize($campaign->campaign_meta['pred-label'][0]);
			}

			return rest_ensure_response($campaign);

		} else {

			$new_campaign = array();

			$campaigns = get_posts(
				array(
					'post_type' => 'wc-donation',
					'numberposts' => -1,
					'suppress_filters' => true,
				)
			);

			foreach ( $campaigns as $campaign ) {
				
				$campaign->campaign_meta = self::get_campaign_metadata( $campaign->ID );

				array_push($new_campaign, $campaign);
			}			

			return rest_ensure_response($new_campaign);		
		}
	}

	private static function get_campaign_metadata( $id ) {
		$campaign_meta = get_post_meta( $id );
		
		unset($campaign_meta['_edit_lock']);
		unset($campaign_meta['_edit_last']);

		if ( isset( $campaign_meta['wc_donation_product'][0] ) ) {
			$campaign_meta['total_donors'][0] = get_post_meta( $campaign_meta['wc_donation_product'][0], 'total_donors', true );
			$campaign_meta['total_donations'][0] = get_post_meta( $campaign_meta['wc_donation_product'][0], 'total_donations', true );
			$campaign_meta['total_donation_amount'][0] = get_post_meta( $campaign_meta['wc_donation_product'][0], 'total_donation_amount', true );
		}

		return $campaign_meta;
	}

}

new WcdonationAPI();
