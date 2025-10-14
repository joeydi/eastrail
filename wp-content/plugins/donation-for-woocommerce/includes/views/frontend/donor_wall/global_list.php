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
	$_title = apply_filters('global_donor_list_title', $_title);
	?>
	<div class="global-donor-lists-wrapper">
		<?php
		if ( ! empty( trim($_title) ) ) {
			?>
			<h3 class="wc-donation-donor-title"><?php esc_html_e($_title); ?></h3>
			<?php
		}
		?>
		<div class="give-wrap give-grid-ie-utility">
			<div class="give-grid give-grid--3 global-donor-lists">
				<?php
				$all_users = array();
				foreach ( $report_ids as $report_id ) {
					$order_id = get_post_meta($report_id, 'order_id', true);
					$_order = wc_get_order($order_id);
					if ( $_order ) {
						$user = $_order->get_user();
						if ( false !== $user ) {
							$all_users[$user->ID] = $user;
						}
					}
				}

				if ( is_array($all_users) && count($all_users) > 0 ) {
					foreach ( $all_users as $user_id => $user ) {
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
								</div>
							</div>			
						</div>
						<?php
					}
				}

				?>
			</div>
			<?php 
			if ( is_array($all_users) && count($all_users) > 12 ) {
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
