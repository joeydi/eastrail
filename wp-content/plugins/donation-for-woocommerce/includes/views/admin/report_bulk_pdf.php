<?php 
/** 
 * Generating HTML for PDF Receipt 
 * This template can be overridden by copying it to yourtheme/donation/views/report_bulk_pdf.php
 */
/**
* Filter.
* 
* @since 3.4.5
*/
$message = apply_filters( 'wc_donation_change_pdf_message', esc_html__('Thank you for your donations:', 'wc-donation') );
/**
* Action.
* 
* @since 3.4.5
*/
do_action( 'wc_donation_before_pdf_details' );
?>
<p style="margin: 0 0 5px 0; font-size: 14px;"><?php echo esc_html__('Please find the donation transactions', 'wc-donation'); ?></p>
<p style="margin: 0 0 5px 0; font-size: 14px;"><?php echo esc_html( $message ); ?></p>
<table style="width: 100%;background-color: #ffffff;border-collapse: collapse;border-width: 1px;border-color: #00000f;border-style: solid;font-size:14px;">
	<?php
	/**
	* Filter.
	* 
	* @since 3.4.5
	*/ 
	$bgColor = apply_filters( 'wc_donation_pdf_head_bg_color', '#6D3DAF' );
	/**
	* Filter.
	* 
	* @since 3.4.5
	*/
	$txtColor = apply_filters( 'wc_donation_pdf_head_txt_color', '#FFF' );
	?>
	<thead style="background-color: <?php echo esc_attr( $bgColor ); ?>;color: <?php echo esc_attr( $txtColor ); ?>">
		<tr>
			<th style="border-width: 1px;border-color:#000;border-style:solid;padding:3px;"><?php echo esc_html__( 'Campaign', 'wc-donation' ); ?></th>
			<th style="border-width: 1px;border-color:#000;border-style:solid;padding:3px;"><?php echo esc_html__( 'Amount', 'wc-donation' ); ?></th>
			<th style="border-width: 1px;border-color:#000;border-style:solid;padding:3px;"><?php echo esc_html__( 'Order ID', 'wc-donation' ); ?></th>
			<th style="border-width: 1px;border-color:#000;border-style:solid;padding:3px;"><?php echo esc_html__( 'Causes', 'wc-donation' ); ?></th>
			<th style="border-width: 1px;border-color:#000;border-style:solid;padding:3px;"><?php echo esc_html__( 'Gift Aid', 'wc-donation' ); ?></th>
			<th style="border-width: 1px;border-color:#000;border-style:solid;padding:3px;"><?php echo esc_html__( 'Tributes', 'wc-donation' ); ?></th>
			<th style="border-width: 1px;border-color:#000;border-style:solid;padding:3px;"><?php echo esc_html__( 'Date', 'wc-donation' ); ?></th>
			<th style="border-width: 1px;border-color:#000;border-style:solid;padding:3px;"><?php echo esc_html__( 'Payment Method', 'wc-donation' ); ?></th>      
		</tr>
	</thead>
	<tbody style="color: #000;">
		<?php 
		foreach ( $report_ids as $report_id ) {
			$order_id = get_post_meta( $report_id, 'order_id', true );
			$product_id = get_post_meta( $report_id, 'product_id', true );
			$my_order = wc_get_order( $order_id );
			if ( is_object( $my_order ) ) {				
				$currency = get_woocommerce_currency_symbol($my_order->get_currency());
				foreach ( $my_order->get_items() as $item_id => $item ) {					    		
					$donation_type = get_post_meta( $item->get_product_id(), 'is_wc_donation', true);
					if ( ! empty($donation_type) && 'donation' == $donation_type && $item->get_product_id() == $product_id ) {
						$campaign_id = wc_get_order_item_meta( $item_id, 'campaign_id', true );
						$cause = wc_get_order_item_meta( $item_id, 'cause_name', true );
						$gift_aid = wc_get_order_item_meta( $item_id, 'gift_aid', true );
						$tribute = wc_get_order_item_meta( $item_id, 'tribute', true );
						?>
						<tr>
							<td style="border-width: 1px;border-color:#000;border-style:solid;padding:3px;"><?php echo esc_html( get_the_title( $campaign_id  ) ); ?></td>
							<td style="border-width: 1px;border-color:#000;border-style:solid;padding:3px;"><?php echo esc_html( $currency . '' . $item->get_total() ); ?></td>
							<td style="border-width: 1px;border-color:#000;border-style:solid;padding:3px;"><?php echo esc_html( $my_order->get_id() ); ?></td>
							<td style="border-width: 1px;border-color:#000;border-style:solid;padding:3px;"><?php echo esc_html( $cause ); ?></td>
							<td style="border-width: 1px;border-color:#000;border-style:solid;padding:3px;"><?php echo esc_html( $gift_aid ); ?></td>
							<td style="border-width: 1px;border-color:#000;border-style:solid;padding:3px;"><?php echo esc_html( $tribute ); ?></td>
							<td style="border-width: 1px;border-color:#000;border-style:solid;padding:3px;"><?php echo esc_html( $my_order->get_date_created() ); ?></td>
							<td style="border-width: 1px;border-color:#000;border-style:solid;padding:3px;"><?php echo esc_html( $my_order->get_payment_method_title() ); ?></td>
						</tr>
						<?php
					}
				}
			}
		}
		?>
	</tbody>
</table>
<br/>
<br/>
<?php
/**
* Action.
* 
* @since 3.4.5
*/
do_action( 'wc_donation_after_pdf_details' );
?>
<p><?php echo esc_html__('Sincerely,', 'wc-donation'); ?></p>
<strong><?php echo esc_html( get_bloginfo( 'name' ) ); ?></strong>
