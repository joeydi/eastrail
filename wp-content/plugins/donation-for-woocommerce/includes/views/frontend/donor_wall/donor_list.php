<?php
/**
* Donor Lists
*/
$report_ids = WcDonationReports::get_reports_by_campaign_id($campaign_id);

if ( false !== $report_ids && is_array( $report_ids ) ) {
	/**
	**
	*  Hook Filter
	*
	*  @since 3.6
	*/
	$_title = apply_filters('donor_list_title', $_title);
	?>
	<div class="donor-lists-wrapper">
		<?php
		if ( ! empty( trim($_title) ) ) {
			?>
			<h3 class="wc-donation-donor-title"><?php esc_html_e($_title); ?></h3>
			<?php
		}
		?>
		<div class="give-wrap give-grid-ie-utility">
			<div class="give-grid give-grid--3 donor-lists">
				<?php
				$count = 0;
				foreach ( $report_ids as $report_id ) {
					$order_id = get_post_meta($report_id, 'order_id', true);
					$_order = wc_get_order($order_id);
					if ( $_order ) {
						$amount_donated = get_post_meta($report_id, 'donation_amount', true);
						$curreny = $_order->get_currency();
						$formatted_price = wc_price( $amount_donated, array( 'currency' => $curreny ) );
						$user = $_order->get_user();
						if ( false !== $user ) {
							$count++;
							$fullName = $user->first_name . ' ' . $user->last_name;
							$avatar_html = get_avatar($user->ID, 80);
							?>
							<div class="give-grid__item">
								<div class="give-donor give-card">
									<div class="give-donor-container">
										<div class="give-donor-container__image gravatar-loaded" style="height: 80px; width: 80px;">
											<span class="give-donor-container__image__name_initial"><?php echo wp_kses_post($avatar_html); ?></span>
										</div>
										<div class="give-donor-container-variation" style="flex-direction: column; align-items: center;">
											<p class="give-donor-container-variation__timestamp"><?php esc_attr_e($fullName); ?></p>
										</div>
										<div class="give-donor-details">
											<div class="give-donor-details__wrapper">
												<span class="give - donor - details__amount_donated"><?php esc_attr_e('Amount Donated', 'wc-donation'); ?></span>
											</div>
											<span class="give-donor-details__total"><?php echo wp_kses_post($formatted_price); ?></span>
										</div>
									</div>
								</div>			
							</div>
							<?php
						}
					}
				}
				?>
			</div>
			<?php 
			if ( $count > 12 ) {
				?>
				<button class="give-donor__load_more give-button-with-loader">
					<span class="give-loading-animation"></span><?php esc_html_e('Load more', 'wc-donation'); ?>
				</button>
				<?php
			}
			?>
		</div>
	</div>
	<?php
}
