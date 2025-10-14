<?php

if ( ! defined( 'ABSPATH' ) ) {
	die;
} 

class WC_Cart_Donation_Campaigns_Block {

	public function __construct() { }

	public static function display_wc_donation_on_cart_block( $atts ) {     

		if ( ! is_wc_endpoint_url('order-received') ) {

			$campaign_ids = isset( $atts['id'] ) ? explode( ',', $atts['id'] ) : ( isset( $atts['donation_ids'] ) ? $atts['donation_ids'] : array() );
			$format = isset( $atts['format'] ) ? $atts['format'] : ( isset( $atts['formattypes'] ) ? $atts['formattypes'] : '' );

			if ( is_array( $campaign_ids ) && count( $campaign_ids ) > 0 ) {
				
				if ( 'table' != $format ) {
					?>
					<div id='wc-donation-type-<?php esc_attr_e( $format ); ?>'>
					<?php 
					foreach ( $campaign_ids as $campaign_id ) {
						$post_exist = get_post( $campaign_id );
						if ( ! empty( $post_exist ) && ( isset( $post_exist->post_status ) && 'trash' !== $post_exist->post_status ) ) {
							$object = WcdonationCampaignSetting::get_product_by_campaign( $campaign_id );
		
							extract( WC_Cart_Donation_Campaigns::cart_campaign_template_data( $object ) );

							if ( has_block( 'woocommerce/checkout', get_the_ID() ) ) {
								$_type = 'checkout';
							} elseif ( has_block( 'woocommerce/cart', get_the_ID() ) ) {
								$_type = 'cart';
							} else {
								$_type = 'shortcode';
							}
		
							echo '<div class="wc_donation_on_cart ' . esc_attr( $format ) . '" id="wc_donation_on_cart">';
							/**
							* Action.
							* 
							* @since 3.4.5
							*/
							do_action ( 'wc_donation_before_cart_add_donation_block', $campaign_id );
							if ( ! empty( self::cart_campaign_format_type_template_block( $format ) ) ) {
								include self::cart_campaign_format_type_template_block( $format ); // nosemgrep audit.php.lang.security.file.inclusion-arg
							}

							/**
							* Action.
							* 
							* @since 3.4.5
							*/
							do_action ('wc_donation_after_cart_add_donation_block', $campaign_id);
							echo '</div>';
						} else {
							/* translators: %1$s refers to html tag, %2$s refers to html tag */
							printf(esc_html__('%1$s Campaign deleted by admin %2$s', 'wc-donation'), '<p class="wc-donation-error">', '</p>' );
							return;
						}
					} 
					?>
					</div>
					<?php 
				} elseif ( ! empty( self::cart_campaign_format_type_template_block( $format ) ) ) {
						include self::cart_campaign_format_type_template_block( $format ); // nosemgrep audit.php.lang.security.file.inclusion-arg
				}
					
			}
		}
	}

	public static function wc_donation_campaign_popup_display_button_block( $atts ) {

		$campaign_display_format    = isset( $atts['display_button'] ) ? $atts['display_button'] : '';
		$button_title               = isset( $atts['button_text'] ) && ! empty( $atts['button_text'] ) ? $atts['button_text'] : '';

		if ( empty( $button_title ) ) {
			$button_title = esc_html__( 'View Campaigns', 'wc-donation' );
		}

		if ( empty( $campaign_display_format ) || 'button_display' == $campaign_display_format ) { 
			?>
			<button type="button" class="button" onclick="document.getElementById('wc-donation-popup-block').classList.add('wc-popup-show'); document.getElementsByTagName('body')[0].classList.add('stopScroll');"><?php esc_attr_e( $button_title ); ?></button>
			<?php 
		}
	}

	public static function cart_campaign_format_type_template_block( $display_type ) {

		if ( file_exists( get_stylesheet_directory() . '/wc-donation/views/frontend/cart/cart-campaign-' . $display_type . '.php' ) ) {
			return get_stylesheet_directory() . '/wc-donation/views/frontend/cart/cart-campaign-' . $display_type . '.php';
		} else {
			return ( WC_DONATION_PATH . 'includes/views/frontend/cart/cart-campaign-' . $display_type . '.php' );
		}
	}
}