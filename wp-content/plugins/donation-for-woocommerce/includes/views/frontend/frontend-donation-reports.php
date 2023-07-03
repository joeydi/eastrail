<?php 
/**
* User Donation Reports
*/

if ( ! is_user_logged_in() ) {
	return;
}

$user_id = get_current_user_id();

if ( isset( $_REQUEST['clear_filter'] ) ) {
	//just clearing filters
	$args = array(
		'post_type'  => 'wc-donation-report',
		'numberposts' => -1,
		'post_status' => 'private',
		'orderby'    => 'date',
		'sort_order' => 'desc',
		'meta_query' => array(
			'relation' => 'AND',
			array(
				'key'     => 'user_id',
				'value'   => $user_id,
				'compare' => '=',
			)
		)		
	);
} elseif ( isset( $_REQUEST['year'] ) ) {
	$active_year = 'active';
	$this_year = gmdate('Y');
	$date_query = array(
		'column'  => 'post_date',
		'year'   => $this_year
	);
} elseif ( isset( $_REQUEST['last_month'] ) ) {
	$active_last_month = 'active';
	$last_month = gmdate('m', strtotime('last month') );
	$date_query = array(
		'column'  => 'post_date',
		'month'   => $last_month
	);
} elseif ( isset( $_REQUEST['this_month'] ) ) {
	$active_this_month = 'active';
	$this_month = gmdate('m');
	$date_query = array(
		'column'  => 'post_date',
		'month'   => $this_month
	);
} elseif ( isset( $_REQUEST['last_week'] ) ) {
	$active_last_Week = 'active';
	$last_week_start_date = gmdate('Y-m-d', strtotime( '7 days ago' ) );
	$last_week_end_date = gmdate('Y-m-d', strtotime( '1 days ago' ) );
	$date_query = array(
		'column'  => 'post_date',
		'after' => $last_week_start_date, // any strtotime()-acceptable format!
		'before' => $last_week_end_date,
		'inclusive' => true, // include the selected days as well
	);
} elseif ( isset( $_REQUEST['DateFrom'] ) && isset( $_REQUEST['DateTo'] ) && isset( $_REQUEST['custom'] ) ) {
	$date_query = array(
		'column'  => 'post_date',
		'after' => sanitize_text_field( $_REQUEST['DateFrom'] ), // any strtotime()-acceptable format!
		'before' => sanitize_text_field( $_REQUEST['DateTo'] ),
		'inclusive' => true, // include the selected days as well
		'column'    => 'post_date' // 'post_modified', 'post_date_gmt', 'post_modified_gmt'
	);
}

$args = array(
	'post_type'  => 'wc-donation-report',
	'numberposts' => -1,
	'post_status' => 'private',
	'orderby'    => 'date',
	'sort_order' => 'desc',
	'meta_query' => array(
		'relation' => 'AND',
		array(
			'key'     => 'user_id',
			'value'   => $user_id,
			'compare' => '=',
		)
	),
	'date_query' => @$date_query
);

$reports = get_posts( $args );

if ( is_array( $reports ) && count( $reports ) > 0 ) {
	foreach ( $reports as $report ) {
		$report_id = $report->ID;
		$order_id = get_post_meta( $report_id, 'order_id', true );
		$my_order = wc_get_order( $order_id );
		$gift_aid = get_post_meta( $report_id, 'gift_aid', true );
		$donor_email = $my_order->get_billing_email();
		$campaign_id = get_post_meta( $report_id, 'campaign_id', true );
		$product_id = get_post_meta( $report_id, 'product_id', true );
		$product = wc_get_product( $product_id );			
		$product_sku = $product->get_sku();
		$donation_amount = get_post_meta( $report_id, 'donation_amount', true );
		$tribute = get_post_meta( $report_id, 'tribute', true );
		$cause_name = get_post_meta( $report_id, 'cause_name', true );
		$date = gmdate( 'Y-m-d h:i a', strtotime( $report->post_date ) );
		?>
		<div id="report-<?php echo esc_attr($report_id); ?>" style="display:none;">
			<table class="view-donation-report" style="width: 100%; margin: 20px 0">
				<tr>
					<th><?php echo esc_html__('Date:', 'wc-donation'); ?></th>
					<td><?php echo esc_html( $date ); ?></td>
				</tr>
				<tr>
					<th><?php echo esc_html__('Donor Email:', 'wc-donation'); ?></th>
					<td><?php echo esc_html( $donor_email ); ?></td>
				</tr>
				<tr>
					<th><?php echo esc_html__('Order ID:', 'wc-donation'); ?></th>
					<td><?php echo esc_html( $order_id ); ?></td>
				</tr>
				<tr>
					<th><?php echo esc_html__('Campaign', 'wc-donation'); ?></th>
					<td><?php echo esc_html( get_the_title( $campaign_id ) ); ?></td>
				</tr>
				<tr>
					<th><?php echo esc_html__('Product SKU', 'wc-donation'); ?></th>
					<td><?php echo esc_html( $product_sku ); ?></td>
				</tr>
				<tr>
					<th><?php echo esc_html__('Donation Amount', 'wc-donation'); ?></th>
					<td><?php echo esc_html( $donation_amount ); ?></td>
				</tr>
				<?php if ( ! empty( trim( $tribute ) ) ) { ?>
				<tr>
					<th><?php echo esc_html__('Tribute', 'wc-donation'); ?></th>
					<td><?php echo esc_html( $tribute ); ?></td>
				</tr>
				<?php } ?>
				<?php if ( ! empty( trim( $cause_name ) ) ) { ?>
				<tr>
					<th><?php echo esc_html__('Cause', 'wc-donation'); ?></th>
					<td><?php echo esc_html( $cause_name ); ?></td>
				</tr>
				<?php } ?>
				<?php if ( 'yes' == $gift_aid ) { ?>
				<tr>
					<th><?php echo esc_html__('Gift Aid', 'wc-donation'); ?></th>
					<td><?php echo esc_html( $gift_aid ); ?></td>
				</tr>
				<?php } ?>
			</table>
			<?php 
			if ( 'yes' == $gift_aid ) {
				$donation_gift_aid_checkbox_title = get_option( 'wc-donation-gift-aid-checkbox-title' );
				$donation_gift_aid_explanation = get_option( 'wc-donation-gift-aid-explanation' );
				$donation_gift_aid_declaration = get_option( 'wc-donation-gift-aid-declaration' );
				if ( !empty( trim( $donation_gift_aid_checkbox_title ) ) && !empty( trim( $donation_gift_aid_explanation ) ) && !empty( trim( $donation_gift_aid_declaration ) ) ) {
					?>
					<br />
					<p style="margin: 0 0 5px 0; font-size: 14px;"><?php echo esc_html( $donation_gift_aid_explanation ); ?></p>
					<br />
					<label>
						<input type="checkbox" value="yes" checked>&nbsp;&nbsp;<?php echo esc_html( $donation_gift_aid_checkbox_title ); ?>
					</label>
					<div style="clear:both;"></div>
					<br />
					<p style="margin: 0 0 5px 0; font-size: 14px;"><?php echo esc_html( $donation_gift_aid_declaration ); ?></p>
					<?php
				}
			}
			?>
		</div>
		<?php
	}
}
?>

<div class="toRight">
	<form method="POST">
		<input type="submit" name="clear_filter" class="button button-primary" value="<?php echo esc_html__( 'Clear Filter', 'wc-donation' ); ?>">
		<input id="wc-donation-export-report-csv" class="button action" type="submit" name="export_csv" value="<?php echo esc_html__( 'Export CSV', 'wc-donation' ); ?>" >
		<input id="wc-donation-export-report-pdf" class="button action" type="submit" name="export_pdf" value="<?php echo esc_html__( 'Download PDF', 'wc-donation' ); ?>" >
		<input type="hidden" name="user_id" value="<?php echo esc_attr( $user_id ); ?>">
	</form>
</div>
<div style="clear:both;"></div>
<form class="report_filter_form" method="POST">
	<div>
		<input type="submit" name="year" class="<?php echo esc_html( @$active_year ); ?>" value="<?php echo esc_html__( 'Year', 'wc-donation' ); ?>" />
	</div>
	<div>
		<input type="submit" name="last_month" class="<?php echo esc_html( @$active_last_month ); ?>" value="<?php echo esc_html__( 'Last Month', 'wc-donation' ); ?>" />
	</div>
	<div>
		<input type="submit" name="this_month" class="<?php echo esc_html( @$active_this_month ); ?>" value="<?php echo esc_html__( 'This Month', 'wc-donation' ); ?>" />
	</div>
	<div>
		<input type="submit" name="last_week" class="<?php echo esc_html( @$active_last_Week ); ?>" value="<?php echo esc_html__( 'Last 7 days', 'wc-donation' ); ?>" />
	</div>
	<div>
		<label><?php echo esc_html__('Custom: ', 'wc-donation'); ?></label>
		<input type="text" placeholder="<?php echo esc_html__('yy-mm-dd', 'wc-donation'); ?>" name="DateFrom" value="<?php echo esc_html( sanitize_text_field( @$_REQUEST['DateFrom'] ) ); ?>" autocomplete="off" readonly />
		<strong><?php echo esc_html__(' - ', 'wc-donation'); ?></strong>
		<input type="text" placeholder="<?php echo esc_html__('yy-mm-dd', 'wc-donation'); ?>" name="DateTo" value="<?php echo esc_html( sanitize_text_field( @$_REQUEST['DateTo'] ) ); ?>" autocomplete="off" readonly />
		<input type="submit" name="custom" value="Go" class="btn-filter">
	</div>
</form>
<?php 
if ( is_array( $reports ) && count( $reports ) > 0 ) {
	?>
	<table class="wc-donation-reports" cellspacing="0">
		<thead>		
			<tr>
				<th><?php echo esc_html__('Order', 'wc-donation'); ?></th>
				<th><?php echo esc_html__('Campaign', 'wc-donation'); ?></th>
				<th><?php echo esc_html__('Donation', 'wc-donation'); ?></th>
				<th><?php echo esc_html__('Date', 'wc-donation'); ?></th>
				<th><?php echo esc_html__('Action', 'wc-donation'); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php	
		foreach ( $reports as $report ) {
			$report_id = $report->ID;
			$order_id = get_post_meta( $report_id, 'order_id', true );
			$campaign_id = get_post_meta( $report_id, 'campaign_id', true );
			$product_id = get_post_meta( $report_id, 'product_id', true );
			$donation_amount = get_post_meta( $report_id, 'donation_amount', true );
			$date = gmdate( 'Y-m-d h:i a', strtotime( $report->post_date ) );
			?>
			<tr>
				<td><?php echo esc_html( '#' . $order_id ); ?></td>
				<td><?php echo esc_html( get_the_title( $campaign_id ) ); ?></td>
				<td><?php echo wp_kses_post( wc_price($donation_amount) ); ?></td>
				<td><?php echo esc_html( $date ); ?></td>
				<td>
					<a href="#TB_inline?inlineId=report-<?php echo esc_attr($report_id); ?>" style="margin: 0 5px 5px 0;" title="<?php echo esc_html__('Donation Report For #' . $order_id); ?>" class="thickbox button button-primary view-donation-report" ><?php echo esc_html__('View Report', 'wc-donation'); ?></a>
					<a href="<?php echo esc_url ( admin_url('admin-ajax.php') . '?action=wc_donation_generate_report_pdf&order_id=' . $order_id . '&product_id=' . $product_id . '&_wpnonce=' . wp_create_nonce('wc_donation_generate_report_pdf') ); ?>" style="margin: 0 5px 5px 0;" class="button button-primary download-donation-report" ><?php echo esc_html__('Download PDF', 'wc-donation'); ?></a>
				</td>
			</tr>
			<?php
		}
		?>
		</tbody>
		<tfoot>
			<tr>
				<th><?php echo esc_html__('Order', 'wc-donation'); ?></th>
				<th><?php echo esc_html__('Campaign', 'wc-donation'); ?></th>
				<th><?php echo esc_html__('Donation', 'wc-donation'); ?></th>
				<th><?php echo esc_html__('Date', 'wc-donation'); ?></th>
				<th><?php echo esc_html__('Action', 'wc-donation'); ?></th>
			</tr>
		</tfoot>
	</table>
	<?php
} else {
	echo '<p class="report_no_found">' . esc_html__( 'Sorry, no reports found', 'wc-donation' ) . '</p>';
}
