<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Not Authorized.' );
}

class WC_Checkout_Donation_Cart {

	public function __construct() {
		
		add_action( 'woocommerce_checkout_update_order_review', array( $this, 'handle_checkout_donation_component_line_item_update' ), 10 );

		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'add_component_to_line_item' ), 10, 4 );

		add_filter( 'woocommerce_thankyou_order_received_text', array( $this, 'donation_received_text' ), 90, 2 );
	}

	public function handle_checkout_donation_component_line_item_update( $post_data ) {
		
		parse_str( $post_data, $checkout_fields );

		if ( ! isset( $checkout_fields['is_checkout_donation_block'] ) ) {
			return true;
		}

		$donating_amount = 0;

		if ( isset( $checkout_fields['wc-donation-price'] ) ) {
			$campaign_id    = isset( $checkout_fields['campaign_id'] ) ? $checkout_fields['campaign_id'] : 0;
			$donation_price = $checkout_fields['wc-donation-price'];
			$donating_amount         = $donation_price;
			$item_key = Helper::get_cart_item_key_checkout_donation( $campaign_id );
			WC()->cart->cart_contents[$item_key]['custom_price'] = empty ( $donation_price ) ? 1 : $donation_price;
			WC()->cart->calculate_totals();
		}

		if ( isset( $checkout_fields['wc_check_fees'] ) ) {
			$campaign_id    = isset( $checkout_fields['campaign_id'] ) ? $checkout_fields['campaign_id'] : 0;
			$item_key = Helper::get_cart_item_key_checkout_donation( $campaign_id );
			if ( 'true' == $checkout_fields['wc_check_fees'] ) {
				$amount     = (float) Helper::get_checkout_donation_amount();

				$fee_type   = get_option( 'wc-donation-fees-type' );
				$value      = get_option( 'wc-donation-fees-percent' );

				if ( empty( $value ) ) {
					$value = 1;
				}

				$fee = $value; // fixed fee
				$fee_display = wc_price( $fee );

				if ( 'percentage' == $fee_type ) {
					$fee = round( ( $amount / 100 ) * $value, 2 );
					$fee_display = wc_price( $fee ) . ' (' . $value . '%)';
				}

				WC()->cart->cart_contents[$item_key]['fees_percent']    = $value;
				WC()->cart->cart_contents[$item_key]['processing_fee']  = $fee_display;
				WC()->cart->cart_contents[$item_key]['fee_type']        = $fee_type;

			} else {
				unset( 
					WC()->cart->cart_contents[$item_key]['fees_percent'],
					WC()->cart->cart_contents[$item_key]['processing_fee'],
					WC()->cart->cart_contents[$item_key]['fee_type']
				);
			}
		}

		if ( isset( $checkout_fields['wc_donation_is_checkout_campaign_recurring'], $checkout_fields['wc_donation_subscription_period_interval'], $checkout_fields['wc_donation_subscription_period'], $checkout_fields['wc_donation_subscription_length'] ) ) {
			$item_key       = Helper::get_cart_item_key_checkout_donation( $checkout_fields['campaign_id'] );

			$interval       = $checkout_fields['wc_donation_subscription_period_interval'];
			$period         = $checkout_fields['wc_donation_subscription_period'];
			$length         = $checkout_fields['wc_donation_subscription_length'];

			WC()->cart->cart_contents[$item_key]['billing_interval']                = $interval;
			WC()->cart->cart_contents[$item_key]['subscription_period_interval']    = $interval;
			WC()->cart->cart_contents[$item_key]['_subscription_period_interval']   = $interval;

			WC()->cart->cart_contents[$item_key]['billing_period']                  = $period;
			WC()->cart->cart_contents[$item_key]['subscription_period']             = $period;
			WC()->cart->cart_contents[$item_key]['_subscription_period']            = $period;

			WC()->cart->cart_contents[$item_key]['subscription_length']             = $length;
			WC()->cart->cart_contents[$item_key]['_subscription_length']            = $length;

		}

		WC()->cart->calculate_totals();
	}

	public function add_component_to_line_item( $item, $cart_item_key, $values, $order ) {

		$campaign_id        = filter_input( INPUT_POST, 'campaign_id', FILTER_SANITIZE_SPECIAL_CHARS );
		$cause              = filter_input( INPUT_POST, 'wc-donation-cause', FILTER_SANITIZE_SPECIAL_CHARS );
		$giftaid            = filter_input( INPUT_POST, 'wc_donation_gift_aid_checkbox', FILTER_SANITIZE_SPECIAL_CHARS );
		$tribute            = filter_input( INPUT_POST, 'wc-donation-tribute', FILTER_SANITIZE_SPECIAL_CHARS );
		$tribute_message    = filter_input( INPUT_POST, 'wc-donation-tribute-message', FILTER_SANITIZE_SPECIAL_CHARS );

		if ( ! empty( $cause ) ) {
			$item->add_meta_data( 'cause_name', wc_clean( $cause ) );
		}

		if ( ! empty( $giftaid ) ) {
			$item->add_meta_data( 'gift_aid', wc_clean( $giftaid ) );
		}

		if ( ! empty( $tribute ) ) {
			$item->add_meta_data( 'tribute', wc_clean( $tribute ) );
		}

		if ( ! empty( $tribute_message ) ) {
			$item->add_meta_data( 'tribute_message', wc_clean( $tribute_message ) );
		}

		if ( ! empty( $campaign_id ) ) {
			$item->add_meta_data( 'campaign_id', wc_clean( $campaign_id ) );
			$item->add_meta_data( 'compaign', get_the_title( $campaign_id ) );
			
			$order->update_meta_data( 'donation_order', $campaign_id );
			$order->save();
		}

		$item->add_meta_data( 'campaign_type', 'checkout_donation_block' );
	}

	public function donation_received_text( $text, $order ) {

		if ( $order instanceof WC_Order ) {
			if ( ! empty( $order->get_meta( 'donation_order' ) ) ) {
				add_action( 'gettext', function ( $translation, $text ) {
					if ( 'Order details' == $translation ) {
						return 'Donation details';
					}
					return $translation;
				}, 10, 2);
				return esc_html_e( 'Thank you. Your donation has been received', 'wc-donation' );
			}
		}

		return $text;
	}
}

( new WC_Checkout_Donation_Cart() );
