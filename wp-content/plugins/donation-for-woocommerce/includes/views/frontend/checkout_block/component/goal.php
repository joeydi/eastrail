<div class="wc-donation-goal-progress">
	<div class="wc-donation-goal">
		<div class="wc-donation-goal-acheived">
			<div class="wc-donation-goal-acheived-amount">
				<?php echo wp_kses_post( $goal_donations_data['raised'] ); ?>
			</div>
			<div class="wc-donation-goal-total-amount"><?php echo wp_kses_post( $goal_donations_data['goal'] ); ?></div>
		</div>
		<div class="wc-donation-donors-count">
			<p class="donors-count"><?php esc_attr_e( sprintf( '%s Donors', $goal_donations_data['total_donors'] ) ); ?></p>
		</div>
	</div>
	<div class="wc-donation-progress">
		<div style="<?php ! empty( $progress_color ) ? esc_attr_e( sprintf( 'background-color:#%s;', $progress_color ) ) : ''; ?>width:<?php esc_attr_e( $goal_donations_data['progress'] ); ?>%;">&nbsp;</div>
	</div>
</div>