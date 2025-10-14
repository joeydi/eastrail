<?php
/**
* Global Lists
*/
$report_ids = WcDonationReports::get_all_reports();

if ( false !== $report_ids && is_array( $report_ids ) ) {
	/**
	**
	*  Hook Filter
	*
	*  @since 3.6
	*/
	$_title = apply_filters('leaderboard_donor_list_title', $_title);
	?>
	<div class="leaderboard-donor-lists-wrapper">
		<?php
		if ( ! empty( trim($_title) ) ) {
			?>
			<h3 class="wc-donation-donor-title"><?php esc_html_e($_title); ?></h3>
			<?php
		}
		?>
		<div class="give-wrap give-grid-ie-utility">
			<div class="give-grid give-grid--3 leaderboard-donor-lists">
				<?php
				$all_users = array();
				$user_amounts = array();
				foreach ( $report_ids as $report_id ) {
					$order_id = get_post_meta($report_id, 'order_id', true);
					$_order = wc_get_order($order_id);
					if ( $_order ) {
						$amount_donated = get_post_meta($report_id, 'donation_amount', true);
						$user = $_order->get_user();
						if ( false !== $user ) {
							$all_users[$user->ID] = $user;
							if ( isset($user_amounts[$user->ID]) ) {
								$user_amounts[$user->ID] += $amount_donated;
							} else {
								$user_amounts[$user->ID] = $amount_donated;
							}
						}
					}
				}

				arsort($user_amounts);

				if ( is_array($user_amounts) && count($user_amounts) > 0 ) {
					foreach ( $user_amounts as $user_id => $amount ) {
						$user = get_userdata($user_id);
						$fullName = $user->first_name . ' ' . $user->last_name;
						$avatar_html = get_avatar($user_id, 80);
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
										<span class="give-donor-details__total"><?php echo wp_kses_post(wc_price($amount)); ?></span>
									</div>
								</div>
							</div>			
						</div>
						<?php
					}
				}

				?>
			</div>
			<?php 
			if ( is_array($user_amounts) && count($user_amounts) > 12 ) {
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
