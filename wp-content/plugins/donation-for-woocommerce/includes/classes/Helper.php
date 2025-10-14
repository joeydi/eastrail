<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Not Authorized.' );
}

class Helper {
	
	public static function load_checkout_block_component( $component, $show = true, $args = array() ) {
		
		if ( ! $show ) {
return;
		}
		
		extract( $args );
		include sprintf( '%sincludes/views/frontend/checkout_block/component/%s.php', WC_DONATION_PATH, $component ); // nosemgrep: audit.php.lang.security.file.inclusion-arg
	}

	public static function collect_campaign_data( $campaign_id ) {
		$wp_rand                = wp_rand( 1, 999 );
		// campaign_product_id
		$campaign_product_id    = get_post_meta( $campaign_id, 'wc_donation_product', true );
		// campaign_product
		$campaign_product       = wc_get_product( $campaign_product_id );

		// campaign title and description
		$camaign_title          = get_the_title( $campaign_id );
		$camaign_description    = ( $campaign_product->get_description() ) ? $campaign_product->get_description() : $campaign_product->get_short_description();
		// campaign recurring vars
		$recurring_option       = get_post_meta( $campaign_id, 'wc-donation-recurring', true );
		$is_recurring_form      = ( 'user' == $recurring_option && class_exists( 'WC_Subscriptions' ) );

		// campaign goal vars
		$is_goal_enabled        = ( 'enabled' == get_post_meta( $campaign_id, 'wc-donation-goal-display-option', true ) );
		$progress_color         = get_post_meta( $campaign_id, 'wc-donation-goal-progress-bar-color', true );
		$goal_donations_data    = self::calculate_goal_progress( $campaign_id, $campaign_product_id );

		// amount vars
		$amount_type            = get_post_meta( $campaign_id, 'wc-donation-amount-display-option', true );
		$presets                = get_post_meta( $campaign_id, 'pred-amount', true );
		$presets                = ! empty( $presets ) ? $presets : array( 1 );
		$custom_amount_label    = get_post_meta( $campaign_id, 'free-amount-ph', true );

		$custom_range_min       = 1;
		$custom_range_max       = PHP_INT_MAX;

		if ( 'custom_range' == get_post_meta( $campaign_id, 'wc-donation-custom-type-option', true ) ) {
			$custom_range_min       = get_post_meta( $campaign_id, 'free-min-amount', true );
			$custom_range_max       = get_post_meta( $campaign_id, 'free-max-amount', true );
			$custom_amount_label    = sprintf( 'Enter amount between %s - %s', wc_price( $custom_range_min ), wc_price( $custom_range_max ) );
		}

		// giftaid vars
		$is_giftaid_enabled     = ( 'yes' == get_option( 'wc-donation-gift-aid', 'no' ) );

		// credit card processing vars
		$enabled_campaigns      = get_option( 'wc-donation-fees-product', array() );
		$is_card_fess_enabled   = ( in_array( $campaign_id, (array) $enabled_campaigns ) ) ? true : false;
		
		// tribute vars
		$tributes_data          = array(
			'campaign_id'       => $campaign_id,
			'wp_rand'           => $wp_rand,
			'donation_tributes' => get_option( 'wc-donation-tributes' ),
			'all_tributes'      => get_post_meta( $campaign_id, 'tributes', true ),
		);

		// cause vars
		$cause_data             = array(
			'campaign_id'       => $campaign_id,
			'wp_rand'           => $wp_rand,
			'causeDisp'         => get_post_meta( $campaign_id, 'wc-donation-cause-display-option', true ),
			'causeNames'        => get_post_meta( $campaign_id, 'donation-cause-names' ),
			'causeDesc'         => get_post_meta( $campaign_id, 'donation-cause-desc' ),
			'causeImg'          => get_post_meta( $campaign_id, 'donation-cause-img' ),
		);

		// campaign sharing vars
		$donation_sharing       = array(
			'donation_product'  => $campaign_product_id,
			'campaign_id'       => $campaign_id,
		);

		return compact( 
			'campaign_product_id',
			'campaign_product',
			'camaign_title' ,
			'camaign_description',
			'is_recurring_form',
			'is_goal_enabled',
			'progress_color',
			'goal_donations_data',
			'amount_type',
			'presets',
			'custom_amount_label',
			'custom_range_min',
			'custom_range_max',
			'is_giftaid_enabled',
			'is_card_fess_enabled',
			'tributes_data',
			'cause_data',
			'donation_sharing'
		);
	}

	public static function calculate_goal_progress( $campaign_id, $product_id ) {
		$donations_data     = array();

		$donations_data_raw = WcdonationSetting::has_bought_items( $product_id );
		$donations_data     = array_merge( $donations_data, $donations_data_raw );
		$goal_type          = get_post_meta( $campaign_id, 'wc-donation-goal-display-type', true );
		$init_amount        = get_post_meta( $campaign_id, 'wc-donation-goal-fixed-initial-amount-field', true );
		$goal_amount        = get_post_meta( $campaign_id, 'wc-donation-goal-fixed-amount-field', true );

		if ( 'fixed_amount' === $goal_type || 'percentage_amount' === $goal_type ) {
			$fixedInitialAmount     = ! empty( $init_amount )    ? $init_amount   : 0;
			$fixedAmount            = ! empty( $goal_amount )   ? $goal_amount  : 0;

			if ( $fixedAmount > 0 ) {
				$progress = ( ( $donations_data_raw['total_donation_amount'] + $fixedInitialAmount )/$fixedAmount ) * 100;
				if ( $progress >= 100 ) {
					$progress = 100;
				}
				$donations_data['raised']   = wc_price( $donations_data_raw['total_donation_amount'] + $fixedInitialAmount );
				$donations_data['goal']     = sprintf( 'of %s goal', wc_price( $goal_amount ) );
				$donations_data['progress'] = $progress;

				if ( 'percentage_amount' === $goal_type  ) {
					$donations_data['raised'] = $progress . '% Raised';
				}
			}
		} else if ( 'no_of_donation' === $goal_type  ) {
			$no_of_donation = get_post_meta( $campaign_id, 'wc-donation-goal-no-of-donation-field', true );
			$no_of_donation = ! empty( $no_of_donation ) ? $no_of_donation : 0;

			if ( $no_of_donation > 0 ) {
				$progress = ( $donations_data_raw['total_donations'] / $no_of_donation ) * 100;
				if ( $progress >= 100 ) {
					$progress = 100;
				}
				$donations_data['raised']   = sprintf( '%s Donation Raised', $donations_data_raw['total_donations'] );
				$donations_data['goal']     = sprintf( 'Out of %s Donations', $no_of_donation );
				$donations_data['progress'] = $progress;
			}
		} else if ( 'no_of_days' === $goal_type  ) {
			$no_of_days     = get_post_meta( $campaign_id, 'wc-donation-goal-no-of-days-field', true );
			$no_of_days     = ! empty( $no_of_days ) ? $no_of_days : 0;   

			$end_date       = gmdate('Y-m-d', strtotime($no_of_days));
			$current_date   = gmdate('Y-m-d');
			$date1          = new DateTime($current_date);  //current date or any date
			$date2          = new DateTime($end_date);   //Future date
			$leftDays       = $date2->diff($date1)->format('%a');  //find difference
			$totaltDays     = get_post_meta( $campaign_id, 'wc-donation-goal-total-days', true );
			$totaltDays     = ! empty( $totaltDays ) ? $totaltDays : 0;

			if ( !empty($totaltDays) || 0 != $totaltDays ) {
				$progress = ( ( $totaltDays - $leftDays )/$totaltDays ) * 100;
				if ( $progress >= 100 ) {
					$progress = 100;
				}
			} else {
				$progress = 100;
			}
			$donations_data['raised']   = sprintf( '%s Days Left', $leftDays );
			$donations_data['goal']     = sprintf( 'Out of %s Days', $totaltDays );
			$donations_data['progress'] = $progress;      
		}

		return $donations_data;
	}

	public static function add_to_cart_checkout_donation( $campaign_id ) {
		if ( empty( WC()->cart ) ) {
			return false;
		}

		$product_id = get_post_meta( $campaign_id, 'wc_donation_product', true );

		$is_product_in_cart = false;

		foreach ( WC()->cart->get_cart() as $cart_item ) {

			if ( isset( $cart_item['wc_is_donation_product'] ) && sprintf( 'campaign_%s_product_%s', $campaign_id, $product_id ) == $cart_item['wc_is_donation_product'] ) {
				$is_product_in_cart = true;
				break;
			}
		}

		if ( ! $is_product_in_cart) {
			WC()->cart->add_to_cart( 
				$product_id, 
				1, 
				0,
				array(),
				array( 
					'wc_is_donation_product' => sprintf(
						'campaign_%s_product_%s', 
						$campaign_id, $product_id 
					),
					'custom_price'      => self::get_campaign_min_price( $campaign_id),
					'campaign_id'       => $campaign_id,
				) 
			);
		}
	}

	public static function get_campaign_min_price( $campaign_id ) {
		$amount_type    = get_post_meta( $campaign_id, 'wc-donation-amount-display-option', true );
		$presets        = get_post_meta( $campaign_id, 'pred-amount', true );
		$presets        = ! empty( $presets ) ? $presets : array( 1 );

		return ( 'free-value' == $amount_type ) ? get_post_meta( $campaign_id, 'free-min-amount', true ) : reset( $presets );
	}

	public static function get_checkout_donation_amount() {
		foreach ( WC()->cart->get_cart() as $cart_item ) {
			if ( isset( $cart_item['wc_is_donation_product'], $cart_item['custom_price'] ) ) {
				return ! empty( $cart_item['custom_price'] ) ? $cart_item['custom_price'] : 1;
			}
		}
		return 0;
	}

	public static function get_cart_item_key_checkout_donation( $campaign_id ) {
		$product_id = get_post_meta( $campaign_id, 'wc_donation_product', true );

		foreach ( WC()->cart->get_cart() as $item_key => $cart_item ) {
			if ( isset( $cart_item['wc_is_donation_product'] ) && sprintf( 'campaign_%s_product_%s', $campaign_id, $product_id ) ) {
				return $item_key;
			}
		}
		return false;
	}

	public static function checkout_donation_error_display( $message ) {
		return sprintf( '<div class="wc-checkout-donation-error"><svg height="20" style="overflow:visible;enable-background:new 0 0 32 32" viewBox="0 0 32 32" width="20" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g><g id="Error_1_"><g id="Error"><circle cx="16" cy="16" id="BG" r="16" style="fill:#D72828;"/><path d="M14.5,25h3v-3h-3V25z M14.5,6v13h3V6H14.5z" id="Exclamatory_x5F_Sign" style="fill:#E6E6E6;"/></g></g></g></svg><p>%s</p></div>', $message );
	}
}
