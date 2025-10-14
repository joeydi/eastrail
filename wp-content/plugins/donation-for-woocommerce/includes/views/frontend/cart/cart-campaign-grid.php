<?php
$setTimerDonation = WcDonation::setTimerDonation($object);
$post_exist = ! empty( $object->campaign['campaign_id'] ) ? get_post( $object->campaign['campaign_id'] ) : '';
if ( empty( $donation_product ) || empty($post_exist) || ( isset($post_exist->post_status) && 'trash' == $post_exist->post_status ) ) { 
	$message = __('You have enabled donation on this page but didn\'t select campaign for it.', 'wc-donation');
	$notice_type = 'error';
	wc_clear_notices(); //<--- check this line.
	$result = wc_add_notice($message, $notice_type); 
	return $result;
}
$dispCustomType = get_post_meta( $object->campaign['campaign_id'], 'wc-donation-custom-type-option', true  );
$freeAmountPlaceHolder = $object->campaign['freeAmountPlaceHolder'];
$social_share = isset( $object->campaign['social_share'] ) ? $object->campaign['social_share'] : false;

if ( 'enabled' === $goalDisp && 'enabled' === $closeForm ) {
	$progress = 0;

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

	if ( $progress >= 100 ) {
		?>
		<p class="donation-goal-completed">
			<?php echo esc_html__($message, 'wc-donation'); ?>
		</p>
		<?php

		return;
	}

	if ( 'no_of_days' === $goalType  ) {
		$no_of_days = !empty( $object->goal['no_of_days'] ) ? $object->goal['no_of_days'] : 0;
		$end_date = gmdate('Y-m-d', strtotime($no_of_days));
		$current_date = gmdate('Y-m-d');
		
		if ( $current_date >= $end_date  ) {
			?>
			<p class="donation-goal-completed">
				<?php echo esc_html__($message, 'wc-donation'); ?>
			</p>
			<?php

			return;
		}
	
	}
}

require WC_DONATION_PATH . 'includes/views/frontend/cart/cart-campaign-styles.php' ; 
?>

<div class="wc-donation-in-action" data-donation-type="<?php echo esc_attr( $display_donation ); ?>">
	<div class="in-action-elements">
		<div class="grid-campaign-thumbnail">
			<?php if ( ! empty( get_the_post_thumbnail( $campaign_id, array( '160', '160' ) ) ) ) : ?>
				<?php echo get_the_post_thumbnail( $campaign_id, array( '160', '160' ) ); ?>
			<?php else : ?>
				<img width="160" height="160" src="<?php echo esc_url( WC_DONATION_URL . 'assets/images/no-image-cart-campaign.png' ); ?>" class="attachment-160x160 size-160x160 wp-post-image">
			<?php endif; ?>
		</div>
		<div class="grid-campaign-title">
			<h3><?php echo esc_attr( get_the_title( $campaign_id ) ); ?></h3>
		</div>
		<div class="grid-campaign-description">
			<?php echo esc_attr( get_the_excerpt( get_post_meta( $campaign_id, 'wc_donation_product', true ) ) ); ?>
		</div>
		<?php 
		if ( is_array( $blocks ) && count( $blocks ) > 0 ) {
			foreach ( $blocks as $block ) {
				?>
				<div class="grid-campaign-<?php echo esc_attr($block); ?>">
				<?php
					require WC_DONATION_PATH . 'includes/views/frontend/blocks/frontend-donation-' . $block . '-disp.php' ;
				?>
				</div>
				<?php
			}
		}
		?>
	</div>
</div>
<div style="clear:both;height:1px;">&nbsp;</div>