<div class="row3">
	<div class="wc_progressBarContainer" data-campaign-id="<?php echo esc_attr( $object->campaign['campaign_id'] ); ?>">
		<ul>
			<?php
			$progress = 0;
			if ( 'fixed_amount' === $goalType || 'percentage_amount' === $goalType  ) {
				$fixedInitialAmount = !empty( $object->goal['fixed_initial_amount'] ) ? $object->goal['fixed_initial_amount'] : 0;
				$fixedAmount = !empty( $object->goal['fixed_amount'] ) ? $object->goal['fixed_amount'] : 0;
				if ( $fixedAmount > 0 ) {
					$progress = ( ( $get_donations['total_donation_amount'] + $fixedInitialAmount )/$fixedAmount ) * 100;
					if ( $progress >= 100 ) {
						$progress = 100;
					}
				}
				?>
				<li class="wc_progress_details">
					<div class="raised_amount">
						<?php if ( 'fixed_amount' === $goalType ) { ?>
							<?php echo wp_kses_post( wc_price( ( $get_donations['total_donation_amount'] + $fixedInitialAmount ) ) ); ?>
						<?php } else { ?>
							<span><?php echo esc_attr(round(esc_attr(@$progress), 2)); ?>%</span>
						<?php } ?>
						<span><?php esc_html_e('Raised', 'wc-donation'); ?></span>
					</div>
					<div class="required_amount">
						<span><?php esc_html_e('of', 'wc-donation'); ?></span>
						<?php echo wp_kses_post( wc_price( $fixedAmount ) ); ?>
					</div>
				</li>
				<?php 
			}
			 
			if ( 'no_of_donation' === $goalType  ) {
				$no_of_donation = !empty( $object->goal['no_of_donation'] ) ? $object->goal['no_of_donation'] : 0;
				if ( $no_of_donation > 0 ) {
					$progress = ( $get_donations['total_donations']/$no_of_donation ) * 100;
					if ( $progress >= 100 ) {
						$progress = 100;
					}
				}
				?>
				<li class="wc_progress_details">
					<div class="raised_amount">
						<span><?php echo esc_attr($get_donations['total_donations']); ?></span>
						<span><?php esc_html_e('Donation Raised', 'wc-donation'); ?></span>
					</div>
					<div class="required_amount">
						<span><?php esc_html_e('of', 'wc-donation'); ?></span>
						<span><?php echo esc_attr($no_of_donation); ?></span>
					</div>
				</li>
				<?php 
			} 
			 
			if ( 'no_of_days' === $goalType  ) {
				$no_of_days = !empty( $object->goal['no_of_days'] ) ? $object->goal['no_of_days'] : 0;                          
				$end_date = gmdate('Y-m-d', strtotime($no_of_days));
				$current_date = gmdate('Y-m-d');
				$date1 = new DateTime($current_date);  //current date or any date
				$date2 = new DateTime($end_date);   //Future date
				$leftDays = $date2->diff($date1)->format('%a');  //find difference
				$totaltDays = !empty( $object->goal['total_days'] ) ? $object->goal['total_days'] : 0;
				if ( !empty($totaltDays) || 0 != $totaltDays ) {
					$progress = ( ( $totaltDays - $leftDays )/$totaltDays ) * 100;
					if ( $progress >= 100 ) {
						$progress = 100;
					}
				} else {
					$progress = 100;
				}                       
				?>
				<li class="wc_progress_details">
					<div class="raised_amount">	
						<!--this will be dynamic later-->
						<?php /* translators: %d define number of days */ ?>
						<span><?php printf( esc_html__( _n('%d day Left', '%d days Left', $leftDays, 'wc-donation'), 'wc-donation' ), esc_attr($leftDays) ); ?></span>
					</div>
					<div class="required_amount">
						<?php /* translators: %d define number of days */ ?>
						<span><?php printf( esc_html__( _n('Out of %d Day', 'Out of %d Days', $totaltDays, 'wc-donation' ), 'wc-donation'), esc_attr($totaltDays) ); ?></span>
					</div>
				</li>
				<?php
			}
			?>
			<li class="wc_progress">
				<div class="progressbar" style="width:<?php esc_attr_e(@$progress); ?>%"></div>
			</li>
			<?php if ( 'enabled' === $dispDonorCount  ) { ?>
				<li class="wc_donor_count"><?php echo esc_attr($get_donations['total_donors']) . ' ' . esc_html__('Donors', 'wc-donation'); ?></li>
			<?php } ?>
		</ul>
	</div>
</div>
