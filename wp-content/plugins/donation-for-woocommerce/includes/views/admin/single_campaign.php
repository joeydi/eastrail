<?php
/**
 * Single Campaign
 */
$tabber = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-tablink', true  ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-tablink', true  ) : 'tab-1'; 
?>
<style>
	#wc_donation_meta__3 {
		border: 1px solid #6d36af;
	}
	#wc_donation_meta__3 .postbox-header {
		border-bottom: 1px solid #6d36af;
		display:none!important;
	}
	#wc_donation_meta__3 .inside {
		margin: 0!important;
		padding: 0!important;
		box-sizing: border-box!important;
	}
	.wc-donation-tabConainer {
		display: grid;
		grid-template-rows: repeat(1, max-content);
		grid-template-columns: repeat(4, 1fr);
	}
	.wc-donation-tab {
		margin: 24px 0 24px 24px;
	}
	.wc-donation-tab a {
		display: block;
		padding: 16px;
		color: #000;
		font-size: 12px;
		text-decoration: none;
		outline: none!important;
		border-bottom: 1px solid #6d36af;
		border-right: 1px solid #6d36af;
		border-left: 1px solid #6d36af;
	}
	.wc-donation-tab a[href="tab-1"] {
		border-top: 1px solid #6d36af;
	}
	.wc-donation-tab a:hover, .wc-donation-tab a:active, .wc-donation-tab a:focus {
		outline:none!important;
		box-shadow:none!important;
	}
	.wc-donation-tab a.active {
		background-color: #6d36af;
		outline: none;
		color: #fff;
	}
	.wc-donation-tabcontent {
		padding: 20px;
		background: #fff;
		grid-column: 2/-1;
		display: none;
		border-left: 0;
	}

	.wc-donation-tabConainer #tab-8 .select-wrapper label, .wc-donation-tabConainer #tab-9 .select-wrapper label {
		width: 180px;
		vertical-align: middle;
	}

	select#wc-donation-dokan-seller, select#wc-donation-product-vendor-seller {
		vertical-align: initial;
	}
</style>

<div class="wc-donation-tabConainer">
	<div class="wc-donation-tab">
	<input type="hidden" id="wc-donation-tablink" name="wc-donation-tablink" value="<?php echo esc_attr($tabber); ?>">
	<a href="tab-1" class="wc-donation-tablinks" ><?php esc_html_e('Campaign Settings', 'wc-donation'); ?></a>
	<a href="tab-2" class="wc-donation-tablinks" ><?php esc_html_e('Form Settings', 'wc-donation'); ?></a>
	<?php 
	if ( class_exists('WC_Subscriptions') || class_exists('Subscriptions_For_Woocommerce') ) { 
		?>
		<a  href="tab-3" class="wc-donation-tablinks" ><?php esc_html_e('Recurring Donations', 'wc-donation'); ?></a>
		<?php 
	} else {
		?>
		<a href="#"  class="wc-donation-tablinks" style="pointer-events:none!important; opacity: 0.35" ><?php esc_html_e('Recurring Donations', 'wc-donation'); ?></a>
		<?php 
	}
	?>
	<a href="tab-4" class="wc-donation-tablinks"><?php esc_html_e('Donation Goal', 'wc-donation'); ?></a>
	<a href="tab-5" class="wc-donation-tablinks"><?php esc_html_e('Donation Cause', 'wc-donation'); ?></a>
	<?php 
	if ( 'yes' === get_option( 'wc-donation-tributes' ) ) { 
		?>
		<a href="tab-6" class="wc-donation-tablinks"><?php esc_html_e('Tributes', 'wc-donation'); ?></a>
		<?php
	}
	?>
	<a href="tab-7" class="wc-donation-tablinks"><?php esc_html_e('Frontend Ordering', 'wc-donation'); ?></a>

	<?php if ( class_exists('WeDevs_Dokan') ) : ?>
		<a href="tab-8" class="wc-donation-tablinks"><?php esc_html_e('Dokan', 'wc-donation'); ?></a>
	<?php endif; ?>
		
	<?php if ( class_exists('WC_Product_Vendors') ) : ?>
		<a href="tab-9" class="wc-donation-tablinks"><?php esc_html_e('Product Vendors', 'wc-donation'); ?></a>
	<?php endif; ?>

	<a  href="tab-10" class="wc-donation-tablinks" ><?php esc_html_e('Time Configuration', 'wc-donation'); ?></a>

	<?php
	if ( 'yes' === get_option( 'wc-donation-donor-wall' ) ) {
		?>
		<a  href="tab-11" class="wc-donation-tablinks" ><?php esc_html_e('Donor Wall', 'wc-donation'); ?></a>
		<?php
	}
	?>
	<a  href="tab-12" class="wc-donation-tablinks" ><?php esc_html_e('Social Share', 'wc-donation'); ?></a>
	<a  href="tab-13" class="wc-donation-tablinks" ><?php esc_html_e('E-Card Templates', 'wc-donation'); ?></a>
	</div>

	<div id="tab-1" class="wc-donation-tabcontent">
		<?php require_once WC_DONATION_PATH . 'includes/views/admin/campaign_settings_html.php'; ?>
	</div>

	<div id="tab-2" class="wc-donation-tabcontent">
		<?php require_once WC_DONATION_PATH . 'includes/views/admin/form_settings_html.php'; ?> 
	</div>

	<div id="tab-3" class="wc-donation-tabcontent">
		<?php require_once WC_DONATION_PATH . 'includes/views/admin/recurring_donations_html.php'; ?>
	</div>
	
	<div id="tab-4" class="wc-donation-tabcontent">
		<?php require_once WC_DONATION_PATH . 'includes/views/admin/donation_goal_html.php'; ?>
	</div>
	<div id="tab-5" class="wc-donation-tabcontent">
		<?php require_once WC_DONATION_PATH . 'includes/views/admin/donation_cause_html.php'; ?>
	</div>
	<div id="tab-6" class="wc-donation-tabcontent">
		<?php require_once WC_DONATION_PATH . 'includes/views/admin/donation_tribute_html.php'; ?>
	</div>
	<div id="tab-7" class="wc-donation-tabcontent">
		<?php require_once WC_DONATION_PATH . 'includes/views/admin/donation_ordering_html.php'; ?>
	</div>
	<div id="tab-8" class="wc-donation-tabcontent">
		<?php if ( class_exists('WeDevs_Dokan') ) : ?>
			<?php
				global $user_ID;
				$admin_user = get_user_by( 'id', $user_ID );
				$selected = ! empty( get_post_meta ( $this->campaign_id, 'wc-donation-dokan-seller', true  ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-dokan-seller', true  ) : '';
				$user_query = new WP_User_Query( array( 'role' => 'seller' ) );
				$sellers    = $user_query->get_results();
			?>
			<div class="select-wrapper">
				<label for="wc-donation-dokan-seller" class="wc-donation-label"><?php esc_html_e('Vendor(Seller)', 'wc-donation'); ?></label>
				<select name="wc-donation-dokan-seller" id="wc-donation-dokan-seller">
					<?php if ( ! $sellers ) : ?>
						<option value="<?php echo esc_attr($admin_user->ID); ?>"><?php echo esc_attr($admin_user->display_name); ?></option>
					<?php else : ?>
						<option value="<?php echo esc_attr($user_ID); ?>" <?php selected( $selected, $user_ID ); ?>><?php echo esc_attr($admin_user->display_name); ?></option>
						<?php foreach ( $sellers as $key => $user) : ?>
							<option value="<?php echo esc_attr($user->ID); ?>" <?php selected( $selected, $user->ID ); ?>><?php echo ! empty( get_user_meta( $user->ID, 'dokan_store_name', true ) ) ? esc_attr(get_user_meta( $user->ID, 'dokan_store_name', true )) : esc_attr($user->display_name); ?></option>
						<?php endforeach ?>
					<?php endif ?>
				</select>
			</div>
		<?php endif; ?>
	</div>
	
	<div id="tab-9" class="wc-donation-tabcontent">
		<?php if ( class_exists('WC_Product_Vendors') && class_exists('WC_Product_Vendors_Utils') ) : ?>
			<?php
				$terms = (array) get_terms( 'wcpv_product_vendors');
			if ( ! empty( $terms ) ) {
				$post_term = get_post_meta( $this->campaign_id, 'wc-donation-product-vendor-seller', true );
			}
			?>
			<div class="select-wrapper">
				<label for="wc-donation-product-vendor-seller" class="wc-donation-label">Vendor(Seller)</label>
				<select name="wc-donation-product-vendor-seller" id="wc-donation-product-vendor-seller">
					<option value="">Select a Vendor</option>
					<?php foreach ( $terms as $_term ) : ?>
						<option value="<?php echo esc_attr( $_term->term_id ); ?>" <?php selected( $post_term, $_term->term_id, true ); ?>><?php echo esc_html( $_term->name ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		<?php endif; ?>
	</div>

	<div id="tab-10" class="wc-donation-tabcontent">
		<?php require_once WC_DONATION_PATH . 'includes/views/admin/time_configuration_html.php'; ?>
	</div>

	<?php
	if ( 'yes' === get_option( 'wc-donation-donor-wall' ) ) {
		?>
		<div id="tab-11" class="wc-donation-tabcontent">
			<?php require_once WC_DONATION_PATH . 'includes/views/admin/donor_wall_settings_html.php'; ?>
		</div>
		<?php
	}
	?>
	<div id="tab-12" class="wc-donation-tabcontent">
		<?php require_once WC_DONATION_PATH . 'includes/views/admin/social_share.php'; ?>
	</div>
	<div id="tab-13" class="wc-donation-tabcontent">
		<?php require_once WC_DONATION_PATH . 'includes/views/admin/e_card_templates.php'; ?>
	</div>
</div>
