<?php 
/**
 * Donation goal HTML
 */

$goalDisp = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-goal-display-option', true  ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-goal-display-option', true  ) : 'disabled'; 
$goalType = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-goal-display-type', true  ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-goal-display-type', true  ) : 'fixed_amount';
$fixedAmount = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-goal-fixed-amount-field', true  ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-goal-fixed-amount-field', true  ) : '';
$fixedInitialAmount = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-goal-fixed-initial-amount-field', true  ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-goal-fixed-initial-amount-field', true  ) : '';
$no_of_donation = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-goal-no-of-donation-field', true  ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-goal-no-of-donation-field', true  ) : ''; 
$no_of_days = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-goal-no-of-days-field', true  ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-goal-no-of-days-field', true  ) : ''; 
$progressBarColor = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-goal-progress-bar-color', true  ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-goal-progress-bar-color', true  ) : '333333'; 
$dispDonorCount = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-goal-display-donor-count', true  ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-goal-display-donor-count', true  ) : 'disabled'; 
$closeForm = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-goal-close-form', true  ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-goal-close-form', true  ) : '';
$message = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-goal-close-form-text', true  ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-goal-close-form-text', true  ) : '';
$progressOnShop = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-progress-on-shop', true  ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-progress-on-shop', true  ) : '';
$progressOnWidget = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-progress-on-widget', true  ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-progress-on-widget', true  ) : '';
?>

<div class="select-wrapper goal-display-option">
	<label class="wc-donation-label" for=""><?php echo esc_attr( __( 'Donation Goal', 'wc-donation' ) ); ?></label>
	<?php
	foreach ( WcDonation::DISPLAY_GOAL() as $key => $value ) { 
		if ( $goalDisp == $key ) {
			$checked = 'checked';
		} else {
			$checked = '';
		}
		?>
		<input class="inp-cbx" style="display: none" type="radio" id="<?php esc_attr_e($key); ?>" name="wc-donation-goal-display-option" value="<?php esc_attr_e($key); ?>" <?php esc_attr_e($checked); ?> >
		<label class="cbx" for="<?php esc_attr_e($key); ?>">
			<span>
				<svg width="12px" height="9px" viewbox="0 0 12 9">
					<polyline points="1 5 4 8 11 1"></polyline>
				</svg>
			</span>
			<span><?php esc_attr_e( $value ); ?></span>
		</label>
		<?php
	}
	?>
	<div class="wc-donation-tooltip-box">
		<small class="wc-donation-tooltip">If enable, You can set goal on this campaign.</small>
	</div>
</div>

<div class="select-wrapper goal-display-type">
	<label class="wc-donation-label" for=""><?php echo esc_attr( __( 'Goal Type', 'wc-donation' ) ); ?></label>
	<ul class="wc-donation-list">
	<?php
	foreach ( WcDonation::DISPLAY_GOAL_TYPE() as $key => $value ) { 
		if ( $goalType == $key ) {
			$checked = 'checked';
		} else {
			$checked = '';
		}
		?>
		<li>
			<input class="inp-cbx" style="display: none" type="radio" id="<?php esc_attr_e($key); ?>" name="wc-donation-goal-display-type" value="<?php esc_attr_e($key); ?>" <?php esc_attr_e($checked); ?> >
			<label class="cbx" for="<?php esc_attr_e($key); ?>">
				<span>
					<svg width="12px" height="9px" viewbox="0 0 12 9">
						<polyline points="1 5 4 8 11 1"></polyline>
					</svg>
				</span>
				<span><?php esc_attr_e( $value ); ?></span>
			</label>
		</li>
		<?php
	}
	?>
	</ul>
	<div class="wc-donation-tooltip-box">
		<small class="wc-donation-tooltip">Define which type of goal you</small>
	</div>
</div>

<div class="select-wrapper wc-donation-goal-target" id="blk_fixed_amount">
	<label class="wc-donation-label" for="wc-donation-goal-fixed-amount-field"><?php echo esc_attr( __( 'Goal Amount', 'wc-donation' ) ); ?></label>
	<input type="number" id="wc-donation-goal-fixed-amount-field" Placeholder="<?php echo esc_html__('Enter Goal Amount', 'wc-donation'); ?>" name="wc-donation-goal-fixed-amount-field" value="<?php echo esc_attr($fixedAmount); ?>">
	<div class="wc-donation-tooltip-box">
		<small class="wc-donation-tooltip">Enter amount without symbol.</small>
	</div>
</div>

<div class="select-wrapper wc-donation-goal-target" id="blk_fixed_initial_amount">
	<label class="wc-donation-label" for="wc-donation-goal-fixed-initial-amount-field"><?php echo esc_attr( __( 'Initial Goal Amount', 'wc-donation' ) ); ?></label>
	<input type="number" id="wc-donation-goal-fixed-initial-amount-field" Placeholder="<?php echo esc_html__('Enter Initial Goal Amount', 'wc-donation'); ?>" name="wc-donation-goal-fixed-initial-amount-field" value="<?php echo esc_attr($fixedInitialAmount); ?>">
	<div class="wc-donation-tooltip-box">
		<small class="wc-donation-tooltip">Enter amount without symbol.</small>
	</div>
</div>

<div class="select-wrapper wc-donation-goal-target" id="blk_no_of_donation">
	<label class="wc-donation-label" for="wc-donation-goal-no-of-donation-field"><?php echo esc_attr( __( 'No. of Donations', 'wc-donation' ) ); ?></label>
	<input type="text" id="wc-donation-goal-no-of-donation-field" Placeholder="<?php echo esc_html__('Enter No. of Donations', 'wc-donation'); ?>" name="wc-donation-goal-no-of-donation-field" value="<?php echo esc_attr($no_of_donation); ?>">
	<div class="wc-donation-tooltip-box">
		<small class="wc-donation-tooltip">Enter number of donation you want.</small>
	</div>
</div>

<div class="select-wrapper wc-donation-goal-target" id="blk_no_of_days">
	<label class="wc-donation-label" for="wc-donation-goal-no-of-days-field"><?php echo esc_attr( __( 'End of Goal', 'wc-donation' ) ); ?></label>
	<input type="text" id="wc-donation-goal-no-of-days-field" Placeholder="<?php echo esc_html__('dd-mm-yy', 'wc-donation'); ?>" name="wc-donation-goal-no-of-days-field" value="<?php echo esc_attr(gmdate('d-M-Y', strtotime($no_of_days))); ?>">
	<div class="wc-donation-tooltip-box">
		<small class="wc-donation-tooltip">Your goal will be end before a day selected.</small>
	</div>
</div>

<div class="select-wrapper">
	<label class="wc-donation-label" for="wc-donation-goal-progress-bar-color"><?php echo esc_attr( __( 'Progress bar Color', 'wc-donation' ) ); ?></label>
	<input type="text" class="jscolor" id="wc-donation-goal-progress-bar-color" name="wc-donation-goal-progress-bar-color" value="<?php echo esc_attr($progressBarColor); ?>">
	<div class="wc-donation-tooltip-box">
		<small class="wc-donation-tooltip">Color will be change on frontend progress bar.</small>
	</div>
</div>

<div class="select-wrapper goal-display-option">
	<label class="wc-donation-label" for=""><?php echo esc_attr( __( 'Display Donor Count', 'wc-donation' ) ); ?></label>
	<?php
	foreach ( WcDonation::DISPLAY_GOAL() as $key => $value ) { 
		if ( $dispDonorCount == $key ) {
			$checked = 'checked';
		} else {
			$checked = '';
		}
		?>
		<input class="inp-cbx" style="display: none" type="radio" id="donor_<?php esc_attr_e($key); ?>" name="wc-donation-goal-display-donor-count" value="<?php esc_attr_e($key); ?>" <?php esc_attr_e($checked); ?> >
		<label class="cbx" for="donor_<?php esc_attr_e($key); ?>">
			<span>
				<svg width="12px" height="9px" viewbox="0 0 12 9">
					<polyline points="1 5 4 8 11 1"></polyline>
				</svg>
			</span>
			<span><?php esc_attr_e( $value ); ?></span>
		</label>
		<?php
	}
	?>
	<div class="wc-donation-tooltip-box">
		<small class="wc-donation-tooltip">If enable, display unqiue donors count on frontend.</small>
	</div>
</div>

<div class="select-wrapper">
	<?php 
	if ( 'enabled' === $closeForm ) {
		$checked = 'checked';
	} else {
		$checked = '';
	}
	?>
	<label class="wc-donation-label"><?php echo esc_attr( __( 'Close Form', 'wc-donation' ) ); ?></label>
	<input class="inp-cbx" style="display: none" type="checkbox" id="wc-donation-goal-close-form" name="wc-donation-goal-close-form" value="enabled" <?php esc_attr_e($checked); ?> >
	<label class="cbx cbx-square" for="wc-donation-goal-close-form">
		<span>
			<svg width="12px" height="9px" viewbox="0 0 12 9">
				<polyline points="1 5 4 8 11 1"></polyline>
			</svg>
		</span>
		<span><?php echo esc_attr( __( 'Enable', 'wc-donation' ) ); ?></span>
	</label>
	<div class="wc-donation-tooltip-box">
		<small class="wc-donation-tooltip">Enable to close form after goal is achieved.</small>
	</div>
</div>

<div class="select-wrapper" id="close_msg_text">
	<label class="wc-donation-label" for="wc-donation-goal-close-form-text"><?php echo esc_attr( __( 'Message', 'wc-donation' ) ); ?></label>
	<textarea name="wc-donation-goal-close-form-text" Placeholder="<?php echo esc_html__('Enter Message Here', 'wc-donation'); ?>" id="wc-donation-goal-close-form-text" cols="50" rows="4"><?php echo esc_attr($message); ?></textarea>
	<div class="wc-donation-tooltip-box">
		<small class="wc-donation-tooltip">Display csutom message when the goal is achieved.</small>
	</div>
</div>

<div class="select-wrapper">
	<?php 
	if ( 'enabled' === $progressOnShop ) {
		$checked = 'checked';
	} else {
		$checked = '';
	}
	?>
	<label class="wc-donation-label"><?php echo esc_attr( __( 'Display Goal on Shop Page', 'wc-donation' ) ); ?></label>
	<input class="inp-cbx" style="display: none" type="checkbox" id="wc-donation-progress-on-shop" name="wc-donation-progress-on-shop" value="enabled" <?php esc_attr_e($checked); ?> >
	<label class="cbx cbx-square" for="wc-donation-progress-on-shop">
		<span>
			<svg width="12px" height="9px" viewbox="0 0 12 9">
				<polyline points="1 5 4 8 11 1"></polyline>
			</svg>
		</span>
		<span><?php echo esc_attr( __( 'Enable', 'wc-donation' ) ); ?></span>
	</label>
	<div class="wc-donation-tooltip-box">
		<small class="wc-donation-tooltip">Enable to show goal progress bar on shop page.</small>
	</div>
</div>

<div class="select-wrapper">
	<?php 
	if ( 'enabled' === $progressOnWidget ) {
		$checked = 'checked';
	} else {
		$checked = '';
	}
	?>
	<label class="wc-donation-label"><?php echo esc_attr( __( 'Display Goal on Widget', 'wc-donation' ) ); ?></label>
	<input class="inp-cbx" style="display: none" type="checkbox" id="wc-donation-progress-on-widget" name="wc-donation-progress-on-widget" value="enabled" <?php esc_attr_e($checked); ?> >
	<label class="cbx cbx-square" for="wc-donation-progress-on-widget">
		<span>
			<svg width="12px" height="9px" viewbox="0 0 12 9">
				<polyline points="1 5 4 8 11 1"></polyline>
			</svg>
		</span>
		<span><?php echo esc_attr( __( 'Enable', 'wc-donation' ) ); ?></span>
	</label>
	<div class="wc-donation-tooltip-box">
		<small class="wc-donation-tooltip">Enable to show goal progress bar on widget.</small>
	</div>
</div>

<div class="select-wrapper">	
	<label class="wc-donation-label"><?php echo esc_attr( __( 'Reset Goal', 'wc-donation' ) ); ?></label>
	<a href="#" id="wc-donation-goal-reset" class="button button-primary button-large" style="background-color: #d73131; border-color: #d73131;" data-campaign-id="<?php esc_attr_e( $this->campaign_id ); ?>"><?php echo esc_html__( 'Reset Goal', 'wc-donation' ); ?></a>
	<div class="wc-donation-tooltip-box">
		<small class="wc-donation-tooltip">Clicking reset will reset your current goal and all progress will be lost.</small>
	</div>
</div>

<div id="dialog-reset" title="<?php echo esc_html__('Warning', 'wc-donation'); ?>" style="display:none">
	<center>
		<svg width="100" height="100" viewBox="0 0 20 20" fill="#FF0000" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M9.022 3.566a1.13 1.13 0 011.96 0l6.857 11.667c.457.778-.092 1.767-.98 1.767H3.144c-.889 0-1.437-.99-.98-1.767L9.022 3.566zM9.002 14a1 1 0 112 0 1 1 0 01-2 0zM10 7a.905.905 0 00-.9.995l.35 3.507a.553.553 0 001.1 0l.35-3.507A.905.905 0 0010 7z" clip-rule="evenodd"/>
		</svg>
		<h4><?php echo esc_html__('Are you sure you want to reset this goal?', 'wc-donation'); ?></h4>
		<p><?php echo esc_html__('Clicking reset will reset your current goal and all progress will be lost.', 'wc-donation'); ?></p>
</center>	
</div>

