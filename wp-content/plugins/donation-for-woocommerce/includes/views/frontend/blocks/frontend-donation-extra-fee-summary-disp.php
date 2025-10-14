<?php
$check_fee_option = get_option('wc-donation-card-fee');
$get_fee_campaign = get_option('wc-donation-fees-product');
$get_fee_type = get_option('wc-donation-fees-type', 'percentage');
if ( 'yes' === $check_fee_option && in_array( $campaign_id, (array) $get_fee_campaign ) ) {
	?>
	<div class="row3 wc-donation-summary" id="wc-donation-summary-<?php echo esc_attr($campaign_id) . '_' . esc_attr($wp_rand); ?>">
		<table cellspacing="0" class="wc-donation-summary-table">
			<thead>
				<tr>
					<th><?php echo esc_html__( 'Item', 'wc-donation' ); ?></th>
					<td><?php echo esc_html__( 'Charge', 'wc-donation' ); ?></td>
				</tr>
			</thead>
			<tbody>
				<tr class="wc-donation-charge">
					<th><?php echo esc_html__( 'Donation', 'wc-donation' ); ?></th>
					<td><span class="wc-donation-currency-symbol"><?php echo esc_attr($currency_symbol); ?></span><span class="wc-donation-amt"><?php echo esc_html__('NONE', 'wc-donation'); ?></span></td>
				</tr>
				<tr class="wc-donation-fee-summary">
					<?php 
					if ( 'percentage' == $get_fee_type ) {
						?>
						<th><?php echo esc_html__( 'Fees', 'wc-donation' ) . ' ( ' . esc_attr( get_option( 'wc-donation-fees-percent' ) ) . '% )'; ?></th>
						<?php
					} else {
						?>
						<th><?php echo esc_html__( 'Fees', 'wc-donation' ); ?></th>
						<?php
					}
					?>
					<td><span class="wc-donation-currency-symbol"><?php echo esc_attr($currency_symbol); ?></span><span class="wc-donation-amt"><?php echo esc_html__('NONE', 'wc-donation'); ?></span></td>
				</tr>
			</tbody>
			<tfoot>
				<tr class="wc-donation-summary-total">
					<th><?php echo esc_html__( 'Total', 'wc-donation' ); ?></th>
					<td><span class="wc-donation-currency-symbol"><?php echo esc_attr($currency_symbol); ?></span><span class="wc-donation-amt"><?php echo esc_html__('No Charge', 'wc-donation'); ?></span></td>
				</tr>
			</tfoot>
		</table>
	</div>
	<?php
}
