<?php

$data       = extract( Helper::collect_campaign_data( $campaign_id ) );
?>
<div class="wc-donation-container">
	<div class="wc-donation-card step-checkout">
		<div class="wc-donation-header">

			<?php
			Helper::load_checkout_block_component( 
				'title', 
				true, 
				compact( 'camaign_title' ) 
				);
			?>
			
			<?php
			Helper::load_checkout_block_component( 
				'description',
				true,  
				compact( 'camaign_description' ) 
				); 
			?>
			
			<?php
			Helper::load_checkout_block_component( 
				'toggle', 
				$is_recurring_form
				); 
			?>
			
		</div>
		<div class="wc-donation-body">
			<div class="wc-donation-checkout-step-1 checkout-step active" data-step="1">
				<?php
				Helper::load_checkout_block_component( 
					'goal', 
					$is_goal_enabled, 
					compact( 
						'progress_color', 
						'goal_donations_data' 
						) 
					); 
				?>

				<div class="wc-donation-amounts">
					<?php if ( in_array( $amount_type, array( 'predefined', 'both' ) ) ) : ?>
						<?php
						Helper::load_checkout_block_component( 
							'preset-amount', 
							true, 
							compact( 'presets' ) 
							); 
						?>
					<?php endif; ?>

					<?php if ( in_array( $amount_type, array( 'free-value', 'both' ) ) ) : ?>
						<?php
						Helper::load_checkout_block_component( 
							'amount-field', 
							true, 
							compact( 
								'custom_amount_label', 
								'custom_range_min', 
								'custom_range_max' ) 
							); 
						?>
					<?php endif; ?>
				</div>
				<?php
				Helper::load_checkout_block_component( 
					'recurring', 
					$is_recurring_form 
					); 
				?>
				
				<?php
				Helper::load_checkout_block_component( 
					'cause', 
					true,
						compact( 'cause_data' )
					); 
				?>
				<?php
				Helper::load_checkout_block_component( 
					'tributes',
					true,
					compact( 'tributes_data' ) 
					); 
				?>
				<?php
				Helper::load_checkout_block_component( 
					'consents', 
					true, 
					compact( 
						'is_card_fess_enabled', 
						'is_giftaid_enabled' 
						) 
					); 
				?>
				<?php 
					$event = 'onclick="WCCheckoutDonation.validateStep(1, this)"';
					Helper::load_checkout_block_component( 
						'button', 
						true, 
						compact( 'event' ) 
					);
					?>
			</div>

			<div class="donor-details">

				<?php 
					/**
					* Action woocommerce_before_checkout_form.
					* 
					* @since 3.9.5
					*/
					do_action('woocommerce_before_checkout_form', $checkout);
				?>
				
				<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data">
					<input type="hidden" name="is_checkout_donation_block" value="yes" />
					<div class="wc-donation-checkout-step-2 checkout-step" data-step="2">
						<?php
						Helper::load_checkout_block_component( 
							'donor-details' 
							); 
						?>
						<?php 
							$event = 'onclick="WCCheckoutDonation.validateStep(2, this)"';
							Helper::load_checkout_block_component( 
								'button', 
								true, 
								compact( 'event' ) 
							);
							?>
						<?php
						Helper::load_checkout_block_component( 
							'step-back' 
							); 
						?>
					</div>
					<div class="wc-donation-checkout-step-3 checkout-step" data-step="3">
						<?php
						Helper::load_checkout_block_component( 
							'order-review-payments' 
							); 
						?>
						<?php
						Helper::load_checkout_block_component( 
							'step-back' 
							); 
						?>
					</div>
				</form>
				<?php 
					/**
					* Action woocommerce_after_checkout_form.
					* 
					* @since 3.9.5
					*/
					do_action('woocommerce_after_checkout_form', $checkout);
				?>
			</div>            

			<?php
			Helper::load_checkout_block_component( 
				'share',
				true,
				compact( 'donation_sharing' )
				); 
			?>
			<input type="hidden" name="wc_campaign_id" id="wc_campaign_id" value="<?php esc_attr_e( $campaign_id ); ?>" />
			<input type="hidden" name="wc-donation-form-type" id="wc-donation-form-type" value="multi-step" />
		</div>
	
	</div>

</div>