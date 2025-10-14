<?php
	$deactivate_thumbnail   = get_option( 'wc-donation-deactivate-campaign-thumbnail' );
	$deactivate_causes      = get_option( 'wc-donation-deactivate-campaign-causes' );
	$deactivate_description = get_option( 'wc-donation-deactivate-campaign-description' );
?>
<div class="wc_donation_on_cart table" id="wc_donation_on_cart">
	<table class="shop_table shop_table_responsive" cellspacing="0">
		<thead>
			<tr>
				<?php if ( 'yes' != $deactivate_thumbnail ) : ?>
					<th class="campaign-thumbnail"><?php esc_html_e( 'Thumbnail', 'wc-donation' ); ?></th>
				<?php endif; ?>
				
				<th class="campaign-name"><?php esc_html_e( 'Campaign Name', 'wc-donation' ); ?></th>

				<?php if ( 'yes' != $deactivate_description ) : ?>
					<th class="campaign-description"><?php esc_html_e( 'Description', 'wc-donation' ); ?></th>
				<?php endif; ?>

				<?php if ( 'yes' != $deactivate_causes ) : ?>
					<th class="campaign-causes"><?php esc_html_e( 'Causes', 'wc-donation' ); ?></th>
				<?php endif; ?>			
				
				<?php if ( 'yes' == get_option( 'wc-donation-card-fee', '' ) ) : ?>
					<th class="campaign-fees"><?php esc_html_e( 'Fees', 'wc-donation' ); ?></th>
				<?php endif; ?>

				<?php if ( 'yes' == get_option('wc-donation-gift-aid', '' ) ) : ?>
					<th class="campaign-gift"><?php esc_html_e( 'Gift Aid', 'wc-donation' ); ?></th>
				<?php endif; ?>

				<?php if ( 'yes' == get_option( 'wc-donation-tributes', '' ) ) : ?>
					<th	class="campaign-tributes"><?php esc_html_e( 'Tributes', 'wc-donation' ); ?></th>
				<?php endif; ?>
				
				<th class="campaign-amount"><?php esc_html_e( 'Amount', 'wc-donation' ); ?></th>
				<th class="campaign-call-to-action"></th>
			</tr>
		</thead>
		<tbody>
			
			<?php 
			foreach ( $campaign_ids as $campaign_id ) : 
				$post_exist = get_post( $campaign_id );

				if ( ! empty( $post_exist ) && ( isset( $post_exist->post_status ) && 'trash' !== $post_exist->post_status ) ) :

					$object = WcdonationCampaignSetting::get_product_by_campaign( $campaign_id ); 
					$setTimerDonation = WcDonation::setTimerDonation($object);
					$freeAmountPlaceHolder = $object->campaign['freeAmountPlaceHolder'];
					$dispCustomType = get_post_meta($campaign_id, 'wc-donation-custom-type-option', true  );
					$social_share = isset( $object->campaign['social_share'] ) ? $object->campaign['social_share'] : false;

					$prod_id = get_post_meta($campaign_id, 'wc_donation_product', true);
					if ($prod_id) {
						$product_post = get_post($prod_id);

						if ($product_post) {
							$product_content = $product_post->post_content;
							$product_excerpt = $product_post->post_excerpt;
						}
					}
					extract( WC_Cart_Donation_Campaigns::cart_campaign_template_data( $object ) );
					if ( has_block( 'woocommerce/checkout', get_the_ID() ) ) {
						$_type = 'checkout';
					} elseif ( has_block( 'woocommerce/cart', get_the_ID() ) ) {
						$_type = 'cart';
					} else {
						$_type = 'shortcode';
					} 
					?>

					<tr class="woocommerce-cart-form__cart-item cart_item">
						<?php require WC_DONATION_PATH . 'includes/views/frontend/cart/cart-campaign-styles.php' ; ?>
						<?php if ( 'yes' != $deactivate_thumbnail ) : ?>
							<td classp="campaign-thumbnail" data-title="Campaign Thumbnail">
								<?php echo get_the_post_thumbnail( $campaign_id, array( '120', '120' ) ); ?>
							</td>
						<?php endif; ?>

						<td class="campaign-name" data-title="Name">
							<p style="font-weight: 600;"><?php echo esc_attr( get_the_title( $campaign_id ) ); ?></p>
						</td>

						<?php if ( 'yes' != $deactivate_description ) : ?>
							<td class="campaign-description" data-title="Description">
								<?php echo esc_attr( get_the_excerpt( get_post_meta( $campaign_id, 'wc_donation_product', true ) ) ); ?>
							</td>
						<?php endif; ?>

						<?php if ( 'yes' != $deactivate_causes ) : ?>
							<td class="campaign-causes" data-title="Causes">
								<?php require WC_DONATION_PATH . 'includes/views/frontend/blocks/frontend-donation-cause-disp.php' ; ?>
							</td>
						<?php endif; ?>	

						<?php if ( 'yes' == get_option( 'wc-donation-card-fee', '' ) ) : ?>
							<td class="campaign-fees" data-title="Fees">
								<?php require WC_DONATION_PATH . 'includes/views/frontend/blocks/frontend-donation-extra-fee-disp.php' ; ?>
							</td>
						<?php endif; ?>

						<?php if ( 'yes' == get_option( 'wc-donation-gift-aid', '' ) ) : ?>
							<td class="campaign-gift-aid" data-title="Gift-Aids">
								<?php require WC_DONATION_PATH . 'includes/views/frontend/blocks/frontend-donation-gift-aid-disp.php' ; ?>
							</td>
						<?php endif; ?>

						<?php if ( 'yes' == get_option( 'wc-donation-tributes', '' ) ) : ?>
							<td class="campaign-tributes" data-title="Tributes">
								<?php require WC_DONATION_PATH . 'includes/views/frontend/blocks/frontend-donation-tribute-disp.php' ; ?>
							</td>
						<?php endif; ?>

						<td class="campaign-amount" data-title="Amount">
							<?php require WC_DONATION_PATH . 'includes/views/frontend/blocks/frontend-donation-amount-disp.php' ; ?>
							<?php require WC_DONATION_PATH . 'includes/views/frontend/blocks/frontend-donation-subscription-disp.php' ; ?>
							<?php require WC_DONATION_PATH . 'includes/views/frontend/blocks/frontend-donation-main-goal-disp.php' ; ?>
						</td>

						<td class="campaign-call-to-action" data-title="Donate Now">
							<?php
								$progress = 0;
							if ( 'enabled' === $goalDisp && 'enabled' === $closeForm ) {
								if ( 'fixed_amount' === $goalType || 'percentage_amount' === $goalType  ) { 
									$fixedAmount = !empty( $object->goal['fixed_amount'] ) ? $object->goal['fixed_amount'] : 0;     
									if ( $fixedAmount > 0 ) {
										$progress = ( $get_donations['total_donation_amount']/$fixedAmount ) * 100;
									}
								}
			
								if ( 'no_of_donation' === $goalType  ) { 
									$no_of_donation = !empty( $object->goal['no_of_donation'] ) ? $object->goal['no_of_donation'] : 0;
									if ( $no_of_donation > 0 ) {
										$progress = ( $get_donations['total_donations']/$no_of_donation ) * 100;
									}
								}
							}
							?>
							<?php if ( $progress >= 100 ) : ?>

								<p class="donation-goal-completed"><?php echo esc_html__($message, 'wc-donation'); ?></p>

								<?php 
							elseif ( 'no_of_days' === $goalType && 'enabled' === $closeForm  ) :
							
								$no_of_days = !empty( $object->goal['no_of_days'] ) ? $object->goal['no_of_days'] : 0;
								$end_date = gmdate('Y-m-d', strtotime($no_of_days));
								$current_date = gmdate('Y-m-d');
								
								if ( $current_date >= $end_date  ) : 
									?>
								
									<p class="donation-goal-completed"><?php echo esc_html__($message, 'wc-donation'); ?></p>
								
								<?php endif; ?>
							
							<?php else : ?>
							
								<?php require WC_DONATION_PATH . 'includes/views/frontend/blocks/frontend-donation-button-disp.php' ; ?>
							
							<?php endif; ?>

						</td>
					</tr>

				<?php endif; ?>

			<?php endforeach; ?>

		</tbody>
	</table>
	<div class="table-campaign-fees-summary">
		<?php require WC_DONATION_PATH . 'includes/views/frontend/blocks/frontend-donation-extra-fee-summary-disp.php' ; ?>
	</div>
</div>
