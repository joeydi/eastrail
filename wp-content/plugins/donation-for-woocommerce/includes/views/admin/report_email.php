<?php
/** 
 * Generating HTML for Email Receipt 
 * This template can be overridden by copying it to yourtheme/donation/views/report_email.php
 */

/**
* Filter.
* 
* @since 3.4.5
*/
$message = apply_filters( 'wc_donation_change_admin_email_message', __( 'Thank you for your donation. Your Kindness is appreciated.', 'wc-donation' ) );
?>
<p style="margin: 0 0 5px 0; font-size: 14px;"><?php echo esc_html__( 'Dear', 'wc-donation' ) . ' ' . esc_html( $order_billing_first_name ); ?></p>
<p style="margin: 0 0 5px 0; font-size: 14px;"><?php echo esc_html( $message ); ?></p>
<br><br>
<h3><?php echo esc_html__( 'Donation Details', 'wc-donation' ); ?></h3>
<ul style="display: block; width: 100%; padding:  0; margin: 0 0 10px 0; list-style:none; font-size: 14px;">
	<?php
	foreach ( $my_order->get_items() as $item_id => $item ) {
		$donation_type = get_post_meta( $item->get_product_id(), 'is_wc_donation', true );
		if ( ! empty( $donation_type ) && 'donation' == $donation_type && $item->get_product_id() == $product_id ) {
			$campaign_id = wc_get_order_item_meta( $item_id, 'campaign_id', true );
			$currency    = get_woocommerce_currency_symbol( $my_order->get_currency() );
			?>
			<li style="margin: 0 0 5px 0;"><strong><?php echo esc_html__( 'Campaign', 'wc-donation' ); ?>: <?php echo esc_html( get_the_title( $campaign_id ) ); ?></strong></li>
			<li style="margin: 0 0 5px 0;"><strong><?php echo esc_html__( 'Order #', 'wc-donation' ); ?>: </strong><?php echo esc_html( $my_order->get_id() ); ?></li>
			<li style="margin: 0 0 5px 0;"><strong><?php echo esc_html__( 'Amount', 'wc-donation' ); ?>: </strong><?php echo esc_html( $currency . '' . $item->get_total() ); ?></li>
			<li style="margin: 0 0 5px 0;"><strong><?php echo esc_html__( 'Payment Method', 'wc-donation' ); ?>: </strong><?php echo esc_html( $my_order->get_payment_method_title() ); ?></li>
			<?php
		}
	}
	?>
</ul><br><br>
<?php
/**
* Action.
* 
* @since 3.4.5
*/
do_action( 'wc_donation_after_report_email_details' );
