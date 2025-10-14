<?php

class WcdonationSubscriptionFree {
	
	/**
	 * Class Constructor
	 */
	public function __construct() {     
		//Compatibility with WooCommerce Subscriptions for WooCommerce
		add_filter( 'wps_sfw_price_html', array( $this, 'wps_sfw_price_html' ), 10, 3 );
		add_filter( 'wps_sfw_recurring_data', array( $this, 'wps_sfw_recurring_data' ), 10, 2 );
		add_filter( 'woocommerce_get_item_data', array( $this, 'woocommerce_get_item_data' ), 10, 2 );
		add_filter( 'wps_sfw_check_subscription_product_type', array( $this, 'wps_sfw_check_product_is_subscription' ) , 99 , 2 );
	}

	public function woocommerce_get_item_data( $item_data, $cart_item ) {
		
		$price   = null;
		$product = $cart_item['data'];
		if ( is_object( $product ) ) {
			$product_id = $product->get_id();
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				$wps_sfw_subscription_is_recurring = isset($cart_item['wps_sfw_subscription_is_recurring']) ? $cart_item['wps_sfw_subscription_is_recurring'] : '';
			}
			$wps_subscription_product = wps_sfw_get_meta_data( $product_id, '_wps_sfw_users', true );
		}
	
		if ( 'user' !== $wps_subscription_product ) {
			return $item_data;
		}
		if ( 'yes' !== $wps_sfw_subscription_is_recurring ) {
			return $item_data;
		}


		$price_html = $this->wps_sfw_cart_price_html( $price, $wps_price_html='', $product_id );
	
		/**
		* Filter wps_sfw_block_cart_price.
		* 
		* @since 3.8.1
		*/
		$data[] = apply_filters(
			'wps_sfw_block_cart_price',
			array(
				'name'   => 'wps-sfw-price-html',
				'hidden' => true,
				'value'  => html_entity_decode( $price_html, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401 ),
			),
			$cart_item
		);
		
		return $data;
	}


	public function wps_sfw_cart_price_html( $price, $wps_price_html, $product_id ) {
		$wps_subscription_product = wps_sfw_get_meta_data( $product_id, '_wps_sfw_users', true );
	
		if ( 'user' !== $wps_subscription_product ) {
			return $price;
		}
	
		$price = '';

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$wps_sfw_subscription_number = isset($cart_item['wps_sfw_subscription_number']) ? $cart_item['wps_sfw_subscription_number'] : '';
			$wps_sfw_subscription_interval = isset($cart_item['wps_sfw_subscription_interval']) ? $cart_item['wps_sfw_subscription_interval'] : '';
			$wps_sfw_subscription_expiry_number = isset($cart_item['wps_sfw_subscription_expiry_number']) ? $cart_item['wps_sfw_subscription_expiry_number'] : '';
			$wps_sfw_subscription_expiry_interval = isset($cart_item['wps_sfw_subscription_expiry_interval']) ? $cart_item['wps_sfw_subscription_expiry_interval'] : '';
			$wps_sfw_subscription_is_recurring = isset($cart_item['wps_sfw_subscription_is_recurring']) ? $cart_item['wps_sfw_subscription_is_recurring'] : '';
		}
		
		if ( 'yes' !== $wps_sfw_subscription_is_recurring ) {
			return;
		}


		if ( isset( $wps_sfw_subscription_expiry_number ) && ! empty( $wps_sfw_subscription_expiry_number ) ) {

			$wps_sfw_subscription_expiry_interval = $cart_item['wps_sfw_subscription_expiry_interval'];
			
			$wps_price_html = $this->wps_sfw_get_time_interval( $wps_sfw_subscription_expiry_number, $wps_sfw_subscription_expiry_interval );
			// Show interval html.
			$wps_price = $this->wps_sfw_get_time_interval_for_price( $wps_sfw_subscription_number, $wps_sfw_subscription_interval );

			/* translators: %s: susbcription interval */
			$wps_sfw_price_html = '<span class="wps_sfw_interval">' . sprintf( esc_html( ' / %s ' ), $wps_price ) . '</span>';

			/**
			* Filter wps_sfw_show_sync_intervals.
			* 
			* @since 3.8.1
			*/
			$price = apply_filters( 'wps_sfw_show_sync_intervals', $wps_sfw_price_html, $product_id = null );
	
			/* translators: %s: susbcription interval */
			$price .=  '<span class="wps_sfw_expiry_interval">' . sprintf( esc_html__( ' For %s ', 'subscriptions-for-woocommerce' ), $wps_price_html ) . '</span>';

			/**
			* Filter wc_donation_wps_sfw_show_one_time_subscription_price_block.
			* 
			* @since 3.8.1
			*/
			$price = apply_filters( 'wc_donation_wps_sfw_show_one_time_subscription_price_block', $price, $product_id = null);

		} elseif ( isset( $wps_sfw_subscription_number ) && ! empty( $wps_sfw_subscription_number ) ) {
			$wps_price_html = $this->wps_sfw_get_time_interval_for_price( $wps_sfw_subscription_number, $wps_sfw_subscription_interval );
			/* translators: %s: susbcription interval */
			$wps_sfw_price_html =  '<span class="wps_sfw_interval">' . sprintf( esc_html( ' / %s ' ), $wps_price_html ) . '</span>';

			$price = $wps_sfw_price_html;
		

		}

		/**
		* Filter wc_donation_wps_sfw_override_price.
		* 
		* @since 3.8.1
		*/
		return apply_filters( 'wc_donation_wps_sfw_override_price', $price, $product_id);
	}

	/**
	 * Method wps_sfw_price_html
	 *
	 * @param $price $price
	 * @param $wps_price_html 
	 * @param $product_id
	 *
	 * @return void
	 */
	public function wps_sfw_price_html( $price, $wps_price_html, $product_id ) {
		$wps_subscription_product = wps_sfw_get_meta_data( $product_id, '_wps_sfw_users', true );
	
		if ( 'user' !== $wps_subscription_product ) {
			return $price;
		}
	
		$price = '';

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$wps_sfw_subscription_number = isset($cart_item['wps_sfw_subscription_number']) ? $cart_item['wps_sfw_subscription_number'] : '';
			$wps_sfw_subscription_interval = isset($cart_item['wps_sfw_subscription_interval']) ? $cart_item['wps_sfw_subscription_interval'] : '';
			$wps_sfw_subscription_expiry_number = isset($cart_item['wps_sfw_subscription_expiry_number']) ? $cart_item['wps_sfw_subscription_expiry_number'] : '';
			$wps_sfw_subscription_expiry_interval = isset($cart_item['wps_sfw_subscription_expiry_interval']) ? $cart_item['wps_sfw_subscription_expiry_interval'] : '';
			$wps_sfw_subscription_is_recurring = isset($cart_item['wps_sfw_subscription_is_recurring']) ? $cart_item['wps_sfw_subscription_is_recurring'] : '';
		}
		
		if ( 'yes' !== $wps_sfw_subscription_is_recurring ) {
			return;
		}


		if ( isset( $wps_sfw_subscription_expiry_number ) && ! empty( $wps_sfw_subscription_expiry_number ) ) {

			$wps_sfw_subscription_expiry_interval = $cart_item['wps_sfw_subscription_expiry_interval'];
			
			$wps_price_html = $this->wps_sfw_get_time_interval( $wps_sfw_subscription_expiry_number, $wps_sfw_subscription_expiry_interval );
			// Show interval html.
			$wps_price = $this->wps_sfw_get_time_interval_for_price( $wps_sfw_subscription_number, $wps_sfw_subscription_interval );

			/* translators: %s: susbcription interval */
			$wps_sfw_price_html = '<span class="wps_sfw_interval">' . sprintf( esc_html( ' / %s ' ), $wps_price ) . '</span>';

			/**
			* Filter wps_sfw_show_sync_intervals.
			* 
			* @since 3.8.1
			*/
			$price = apply_filters( 'wps_sfw_show_sync_intervals', $wps_sfw_price_html, $product_id = null );
	
			/* translators: %s: susbcription interval */
			$price .=  '<span class="wps_sfw_expiry_interval">' . sprintf( esc_html__( ' For %s ', 'subscriptions-for-woocommerce' ), $wps_price_html ) . '</span>';

	

			/**
			* Filter wc_donation_wps_sfw_show_one_time_subscription_price_block.
			* 
			* @since 3.8.1
			*/
			$price_html = apply_filters( 'wc_donation_wps_sfw_show_one_time_subscription_price_block', $price, $product_id = null);

			
		} elseif ( isset( $wps_sfw_subscription_number ) && ! empty( $wps_sfw_subscription_number ) ) {
			$wps_price_html = $this->wps_sfw_get_time_interval_for_price( $wps_sfw_subscription_number, $wps_sfw_subscription_interval );
			/* translators: %s: susbcription interval */
			$wps_sfw_price_html =  '<span class="wps_sfw_interval">' . sprintf( esc_html( ' / %s ' ), $wps_price_html ) . '</span>';

			$price_html = $wps_sfw_price_html;
		

		}
	
		$price = wc_price($cart_item['custom_price']) . $price_html;

		/**
		* Filter wc_donation_wps_sfw_override_price.
		* 
		* @since 3.8.1
		*/
		return apply_filters( 'wc_donation_wps_sfw_override_price', $price, $product_id);
	}



	/**
	 * Method wps_sfw_recurring_data
	 *
	 * @param $wps_recurring_data
	 * @param $wps_recurring_data, $product_id
	 *
	 * @return array
	 */
	public function wps_sfw_recurring_data( $wps_recurring_data, $product_id ) {


		$wps_subscription_product = wps_sfw_get_meta_data( $product_id, '_wps_sfw_users', true );

		if ( 'user' !== $wps_subscription_product ) {
			return $wps_recurring_data;
		}

		foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
			
			$wps_recurring_data['wps_sfw_subscription_number']          = isset($cart_item['wps_sfw_subscription_number']) ? $cart_item['wps_sfw_subscription_number'] : '';
			$wps_recurring_data['wps_sfw_subscription_interval']        = isset($cart_item['wps_sfw_subscription_interval']) ? $cart_item['wps_sfw_subscription_interval'] : '';
			$wps_recurring_data['wps_sfw_subscription_expiry_number']   = isset($cart_item['wps_sfw_subscription_expiry_number']) ? $cart_item['wps_sfw_subscription_expiry_number'] : '';
			$wps_recurring_data['wps_sfw_subscription_expiry_interval'] = isset($cart_item['wps_sfw_subscription_expiry_interval']) ? $cart_item['wps_sfw_subscription_expiry_interval'] : '';

		}

		return $wps_recurring_data;
	}


	
	/**
	 * Method wps_sfw_show_sync_interval
	 *
	 * @param $wps_sfw_price_html
	 * @param $wps_sfw_price_html $product_id
	 *
	 * @return @html
	 */
	public function wps_sfw_show_sync_interval( $wps_sfw_price_html, $product_id ) {

		$wps_subscription_product = wps_sfw_get_meta_data( $product_id, '_wps_sfw_users', true );

		if ( 'user' !== $wps_subscription_product ) {
			return $wps_sfw_price_html;
		}
	
		$price = '';

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$wps_sfw_subscription_number = isset($cart_item['wps_sfw_subscription_number']) ? $cart_item['wps_sfw_subscription_number'] : '';
			$wps_sfw_subscription_interval = isset($cart_item['wps_sfw_subscription_interval']) ? $cart_item['wps_sfw_subscription_interval'] : '';
			$wps_sfw_subscription_expiry_number = isset($cart_item['wps_sfw_subscription_expiry_number']) ? $cart_item['wps_sfw_subscription_expiry_number'] : '';
			$wps_sfw_subscription_expiry_interval = isset($cart_item['wps_sfw_subscription_expiry_interval']) ? $cart_item['wps_sfw_subscription_expiry_interval'] : '';
			$wps_sfw_subscription_is_recurring = isset($cart_item['wps_sfw_subscription_is_recurring']) ? $cart_item['wps_sfw_subscription_is_recurring'] : '';
		}
		
		if ( 'yes' !== $wps_sfw_subscription_is_recurring ) {
			return;
		}

		if ( isset( $wps_sfw_subscription_expiry_number ) && ! empty( $wps_sfw_subscription_expiry_number ) ) {

			$wps_sfw_subscription_expiry_interval = $cart_item['wps_sfw_subscription_expiry_interval'];         
			$wps_price_html = $this->wps_sfw_get_time_interval( $wps_sfw_subscription_expiry_number, $wps_sfw_subscription_expiry_interval );
			// Show interval html.
			$wps_price = $this->wps_sfw_get_time_interval_for_price( $wps_sfw_subscription_number, $wps_sfw_subscription_interval );

			/* translators: %s: susbcription interval */
			$wps_sfw_price_html = '<span class="wps_sfw_interval">' . sprintf( esc_html( ' / %s ' ), $wps_price ) . '</span>';

			/**
			* Filter wps_sfw_show_sync_intervals.
			* 
			* @since 3.8.1
			*/
			$price = apply_filters( 'wps_sfw_show_sync_intervals', $wps_sfw_price_html, $product_id = null );
	
			/* translators: %s: susbcription interval */
			$price .= '<span class="wps_sfw_expiry_interval">' . sprintf( esc_html__( ' For %s ', 'subscriptions-for-woocommerce' ), $wps_price_html ) . '</span>';

	
			/**
			* Filter wc_donation_wps_sfw_show_one_time_subscription_price_block.
			* 
			* @since 3.8.1
			*/
			$price = apply_filters( 'wc_donation_wps_sfw_show_one_time_subscription_price_block', $price, $product_id = null);
		
		} elseif ( isset( $wps_sfw_subscription_number ) && ! empty( $wps_sfw_subscription_number ) ) {
			$wps_price_html = $this->wps_sfw_get_time_interval_for_price( $wps_sfw_subscription_number, $wps_sfw_subscription_interval );
			/* translators: %s: susbcription interval */
			$wps_sfw_price_html = '<span class="wps_sfw_interval">' . sprintf( esc_html( ' / %s ' ), $wps_price_html ) . '</span>';

			$price = $wps_sfw_price_html;
		

		}
		/**
		* Filter wc_donation_wps_sfw_override_price.
		* 
		* @since 3.8.1
		*/
		return apply_filters( 'wc_donation_wps_sfw_override_price', $price, $product_id);
	}

	
	/**
	 * Method wps_sfw_get_time_interval_for_price
	 *
	 * @param $wps_sfw_subscription_number 
	 * @param $wps_sfw_subscription_interval
	 *
	 * @return @html
	 */
	public function wps_sfw_get_time_interval_for_price( $wps_sfw_subscription_number, $wps_sfw_subscription_interval ) {
		$wps_number = (int) $wps_sfw_subscription_number;
		if ( 1 == $wps_sfw_subscription_number ) {
			$wps_sfw_subscription_number = '';
		}

		$wps_price_html = '';
		switch ( $wps_sfw_subscription_interval ) {
			case 'day':
				/* translators: %s: Day,%s: Days */
				$wps_price_html = sprintf( _n( '%s Day', '%s Days', $wps_number, 'subscriptions-for-woocommerce' ), $wps_sfw_subscription_number );
				break;
			case 'week':
				/* translators: %s: Week,%s: Weeks */
				$wps_price_html = sprintf( _n( '%s Week', '%s Weeks', $wps_number, 'subscriptions-for-woocommerce' ), $wps_sfw_subscription_number );
				break;
			case 'month':
				/* translators: %s: Month,%s: Months */
				$wps_price_html = sprintf( _n( '%s Month', '%s Months', $wps_number, 'subscriptions-for-woocommerce' ), $wps_sfw_subscription_number );
				break;
			case 'year':
				/* translators: %s: Year,%s: Years */
				$wps_price_html = sprintf( _n( '%s Year', '%s Years', $wps_number, 'subscriptions-for-woocommerce' ), $wps_sfw_subscription_number );
				break;
		}

		return $wps_price_html;
	}

	
	/**
	 * Method wps_sfw_get_time_interval
	 *
	 * @param $wps_sfw_subscription_number
	 * @param $wps_sfw_subscription_interval
	 *
	 * @return @html
	 */
	public function wps_sfw_get_time_interval( $wps_sfw_subscription_number, $wps_sfw_subscription_interval ) {
		$wps_sfw_subscription_number = (int) $wps_sfw_subscription_number;
		$wps_price_html = '';
		switch ( $wps_sfw_subscription_interval ) {
			case 'day':
				/* translators: %s: Day,%s: Days */
				$wps_price_html = sprintf( _n( '%s Day', '%s Days', $wps_sfw_subscription_number, 'subscriptions-for-woocommerce' ), $wps_sfw_subscription_number );
				break;
			case 'week':
				/* translators: %s: Week,%s: Weeks */
				$wps_price_html = sprintf( _n( '%s Week', '%s Weeks', $wps_sfw_subscription_number, 'subscriptions-for-woocommerce' ), $wps_sfw_subscription_number );
				break;
			case 'month':
				/* translators: %s: Month,%s: Months */
				$wps_price_html = sprintf( _n( '%s Month', '%s Months', $wps_sfw_subscription_number, 'subscriptions-for-woocommerce' ), $wps_sfw_subscription_number );
				break;
			case 'year':
				/* translators: %s: Year,%s: Years */
				$wps_price_html = sprintf( _n( '%s Year', '%s Years', $wps_sfw_subscription_number, 'subscriptions-for-woocommerce' ), $wps_sfw_subscription_number );
				break;
		}

		/**
		* Filter wps_sfw_display_time_interval.
		* 
		* @since 3.8.1
		*/
		return apply_filters( 'wps_sfw_display_time_interval', $wps_price_html );
	}


		
	/**
	 * Method wps_sfw_check_product_is_subscription
	 *
	 * @param $wps_is_subscription
	 * @param $product $product
	 *
	 * @return bool
	 */
	public function wps_sfw_check_product_is_subscription( $wps_is_subscription, $product ) {
		$wps_subscription_product = wps_sfw_get_meta_data( $product->get_id(), '_wps_sfw_users', true );
	
		if ( 'user' !== $wps_subscription_product ) {
			return $wps_is_subscription;
		}
	
		$wps_sfw_subscription_is_recurring = '';
	
		foreach ( WC()->cart->get_cart() as $cart_item ) {
			if ( !empty( $cart_item['wps_sfw_subscription_is_recurring'] ) ) {
				$wps_sfw_subscription_is_recurring = $cart_item['wps_sfw_subscription_is_recurring'];
				break;
			}
		}
	
		if ( 'yes' === $wps_sfw_subscription_is_recurring ) {
			$wps_is_subscription = true;
		}
	
		return $wps_is_subscription;
	}
}

new WcdonationSubscriptionFree();
