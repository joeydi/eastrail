<?php
/**
 * Time Configuration Setting HTML
 */

$setTimerDisp = !empty( get_post_meta( $this->campaign_id, 'wc-donation-setTimer-display-option', true ) ) ? get_post_meta( $this->campaign_id, 'wc-donation-setTimer-display-option', true ) : 'disabled';
$timeFormat = !empty( get_post_meta( $this->campaign_id, 'wc-donation-setTimer-time-format', true ) ) ? get_post_meta( $this->campaign_id, 'wc-donation-setTimer-time-format', true ) : '12';
$timeType = !empty( get_post_meta( $this->campaign_id, 'wc-donation-setTimer-time-type', true ) ) ? get_post_meta( $this->campaign_id, 'wc-donation-setTimer-time-type', true ) : 'daily';
$displayAfterTimeEnds = !empty( get_post_meta( $this->campaign_id, 'wc-donation-setTimer-display-after-end', true ) ) ? get_post_meta( $this->campaign_id, 'wc-donation-setTimer-display-after-end', true ) : 'hide';
$setTimerEndMessage = !empty( get_post_meta( $this->campaign_id, 'wc-donation-setTimer-display-end-message', true ) ) ? get_post_meta( $this->campaign_id, 'wc-donation-setTimer-display-end-message', true ) : '';
$timings = !empty( get_post_meta( $this->campaign_id, 'wc-donation-setTimer-time', true ) ) ? get_post_meta( $this->campaign_id, 'wc-donation-setTimer-time', true ) : array();
$timerDisplay = !empty( get_post_meta( $this->campaign_id, 'wc-donation-timer-display', true ) ) ? get_post_meta( $this->campaign_id, 'wc-donation-timer-display', true ) : 'disable';
$timerDisplayType = !empty( get_post_meta( $this->campaign_id, 'wc-donation-timer-display-type', true ) ) ? get_post_meta( $this->campaign_id, 'wc-donation-timer-display-type', true ) : 'flip_clock';
$flipClockMode = !empty( get_post_meta( $this->campaign_id, 'wc-donation-flip-clock-mode', true ) ) ? get_post_meta( $this->campaign_id, 'wc-donation-flip-clock-mode', true ) : 'countup';

?>
<div class="select-wrapper">
	<label class="wc-donation-label" for=""><?php echo esc_attr( __( 'Set Timer', 'wc-donation' ) ); ?></label>
	<?php
	foreach ( WcDonation::DISPLAY_GOAL() as $key => $value ) { 
		if ( $setTimerDisp == $key ) {
			$checked = 'checked';
		} else {
			$checked = '';
		}
		?>
		<input class="inp-cbx" style="display: none" type="radio" id="setTimer-<?php esc_attr_e($key); ?>" name="wc-donation-setTimer-display-option" value="<?php esc_attr_e($key); ?>" <?php esc_attr_e($checked); ?> >
		<label class="cbx" for="setTimer-<?php esc_attr_e($key); ?>">
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
		<small class="wc-donation-tooltip"><?php esc_html_e('If enable, You can set the timer on this campaign.', 'wc-donation'); ?></small>
	</div>
</div>

<!-- This setting we will use in our next release-->
<div class="select-wrapper" style="display:none!important">
	<label class="wc-donation-label" for=""><?php echo esc_attr( __( 'Time Format', 'wc-donation' ) ); ?></label>
	<?php
	foreach ( WcDonation::DISPLAY_TIME_FORMAT() as $key => $value ) { 
		if ( $timeFormat == $key ) {
			$checked = 'checked';
		} else {
			$checked = '';
		}
		?>
		<input class="inp-cbx" style="display: none" type="radio" id="timeFormat-<?php esc_attr_e($key); ?>" name="wc-donation-setTimer-time-format" value="<?php esc_attr_e($key); ?>" <?php esc_attr_e($checked); ?> >
		<label class="cbx" for="timeFormat-<?php esc_attr_e($key); ?>">
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
		<small class="wc-donation-tooltip"><?php esc_html_e('Set time format to visible time on front-end.', 'wc-donation'); ?></small>
	</div>
</div>

<div class="select-wrapper" data-parent="wc-donation-setTimer-time-type">
	<label class="wc-donation-label" for=""><?php echo esc_attr( __( 'Timer Type', 'wc-donation' ) ); ?></label>
	<?php
	foreach ( WcDonation::DISPLAY_TIME_TYPE() as $key => $value ) { 
		if ( $timeType == $key ) {
			$checked = 'checked';
		} else {
			$checked = '';
		}
		?>
		<input class="inp-cbx" style="display: none" type="radio" id="timeType-<?php esc_attr_e($key); ?>" name="wc-donation-setTimer-time-type" value="<?php esc_attr_e($key); ?>" <?php esc_attr_e($checked); ?> >
		<label class="cbx" for="timeType-<?php esc_attr_e($key); ?>">
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
		<small class="wc-donation-tooltip"><?php esc_html_e('Select which type of timer you want for the campaign.', 'wc-donation'); ?></small>
	</div>
</div>

<div class="form-time-slot-box" data-child="wc-donation-setTimer-time-type" data-show="daily" style="display:none;">
	<div class="form-time-slots-title-row">    	
		<div class="form-start-time-slots" style="padding-top: 15px; margin-bottom: 20px">
			<span><?php esc_html_e('Start Time', 'wc-donation'); ?></span>
		</div>
		<div class="form-end-time-slots" style="padding-top: 15px; margin-bottom: 20px">
			<span><?php esc_html_e('End Time', 'wc-donation'); ?></span>
		</div>
	</div>
	<!-- Daily -->
	<div class="form-time-slots-content-row">        
		<div class="form-start-time-slots">
			<input type="time" name="wc-donation-setTimer-time[daily][start]" class="setTimer" value="<?php echo isset($timings['daily']['start']) ? esc_attr($timings['daily']['start']) : ''; ?>">
		</div>
		<div class="form-end-time-slots">
			<input type="time" name="wc-donation-setTimer-time[daily][end]" class="setTimer" value="<?php echo isset($timings['daily']['end']) ? esc_attr($timings['daily']['end']) : ''; ?>">
		</div>
	</div>
</div>

<div class="form-time-slot-box" data-child="wc-donation-setTimer-time-type" data-show="specific_day" style="display:none;">
	<div class="form-time-slots-title-row">
		<div class="form-time-slots-days" style="padding-top: 15px; margin-bottom: 20px">
			<span><?php esc_html_e('Select Day', 'wc-donation'); ?></span>
		</div>
		<div class="form-start-time-slots" style="padding-top: 15px; margin-bottom: 20px">
			<span><?php esc_html_e('Start Time', 'wc-donation'); ?></span>
		</div>
		<div class="form-end-time-slots" style="padding-top: 15px; margin-bottom: 20px">
			<span><?php esc_html_e('End Time', 'wc-donation'); ?></span>
		</div>
	</div>
	<!-- MONDAY -->
	<div class="form-time-slots-content-row">
		<label class="form-time-slots-days">
			<input type="checkbox" class="wc_donation_time_toggle" name="wc-donation-setTimer-time[specific_day][mon][switch]" value="1" <?php echo isset($timings['specific_day']['mon']['switch']) ? 'checked' : ''; ?>>
			<span><?php esc_html_e('Mon', 'wc-donation'); ?></span>
		</label>
		<div class="form-start-time-slots">
			<input type="time" name="wc-donation-setTimer-time[specific_day][mon][start]" class="setTimer" value="<?php echo isset($timings['specific_day']['mon']['start']) ? esc_attr($timings['specific_day']['mon']['start']) : ''; ?>">
		</div>
		<div class="form-end-time-slots">
			<input type="time" name="wc-donation-setTimer-time[specific_day][mon][end]" class="setTimer" value="<?php echo isset($timings['specific_day']['mon']['end']) ? esc_attr($timings['specific_day']['mon']['end']) : ''; ?>">
		</div>
	</div>
	
	<!-- TUESDAY -->
	<div class="form-time-slots-content-row">
		<label class="form-time-slots-days">
			<input type="checkbox" class="wc_donation_time_toggle" name="wc-donation-setTimer-time[specific_day][tue][switch]" value="1" <?php echo isset($timings['specific_day']['tue']['switch']) ? 'checked' : ''; ?>>
			<span><?php esc_html_e('Tue', 'wc-donation'); ?></span>
		</label>
		<div class="form-start-time-slots">
			<input type="time" name="wc-donation-setTimer-time[specific_day][tue][start]" class="setTimer" value="<?php echo isset($timings['specific_day']['tue']['start']) ? esc_attr($timings['specific_day']['tue']['start']) : ''; ?>">
		</div>
		<div class="form-end-time-slots">
			<input type="time" name="wc-donation-setTimer-time[specific_day][tue][end]" class="setTimer" value="<?php echo isset($timings['specific_day']['tue']['end']) ? esc_attr($timings['specific_day']['tue']['end']) : ''; ?>">
		</div> 
	</div>
	
	<!-- WEDNESDAY -->
	<div class="form-time-slots-content-row">
		<label class="form-time-slots-days">
			<input type="checkbox" class="wc_donation_time_toggle" name="wc-donation-setTimer-time[specific_day][wed][switch]" value="1" <?php echo isset($timings['specific_day']['wed']['switch']) ? 'checked' : ''; ?>>
			<span><?php esc_html_e('Wed', 'wc-donation'); ?></span>
		</label>
		<div class="form-start-time-slots">
			<input type="time" name="wc-donation-setTimer-time[specific_day][wed][start]" class="setTimer" value="<?php echo isset($timings['specific_day']['wed']['start']) ? esc_attr($timings['specific_day']['wed']['start']) : ''; ?>">
		</div>
		<div class="form-end-time-slots">
			<input type="time" name="wc-donation-setTimer-time[specific_day][wed][end]" class="setTimer" value="<?php echo isset($timings['specific_day']['wed']['end']) ? esc_attr($timings['specific_day']['wed']['end']) : ''; ?>">
		</div>
	</div>
	
	<!-- THURSDAY -->
	<div class="form-time-slots-content-row">
		<label class="form-time-slots-days">
			<input type="checkbox" class="wc_donation_time_toggle" name="wc-donation-setTimer-time[specific_day][thu][switch]" value="1" <?php echo isset($timings['specific_day']['thu']['switch']) ? 'checked' : ''; ?>>
			<span><?php esc_html_e('Thu', 'wc-donation'); ?></span>
		</label>
		<div class="form-start-time-slots">
			<input type="time" name="wc-donation-setTimer-time[specific_day][thu][start]" class="setTimer" value="<?php echo isset($timings['specific_day']['thu']['start']) ? esc_attr($timings['specific_day']['thu']['start']) : ''; ?>">
		</div>
		<div class="form-end-time-slots">
			<input type="time" name="wc-donation-setTimer-time[specific_day][thu][end]" class="setTimer" value="<?php echo isset($timings['specific_day']['thu']['end']) ? esc_attr($timings['specific_day']['thu']['end']) : ''; ?>">
		</div>
	</div>
	
	<!-- FRIDAY -->
	<div class="form-time-slots-content-row">
		<label class="form-time-slots-days">
			<input type="checkbox" class="wc_donation_time_toggle" name="wc-donation-setTimer-time[specific_day][fri][switch]" value="1" <?php echo isset($timings['specific_day']['fri']['switch']) ? 'checked' : ''; ?>>
			<span><?php esc_html_e('Fri', 'wc-donation'); ?></span>
		</label>
		<div class="form-start-time-slots">
			<input type="time" name="wc-donation-setTimer-time[specific_day][fri][start]" class="setTimer" value="<?php echo isset($timings['specific_day']['fri']['start']) ? esc_attr($timings['specific_day']['fri']['start']) : ''; ?>">
		</div>
		<div class="form-end-time-slots">
			<input type="time" name="wc-donation-setTimer-time[specific_day][fri][end]" class="setTimer" value="<?php echo isset($timings['specific_day']['fri']['end']) ? esc_attr($timings['specific_day']['fri']['end']) : ''; ?>">
		</div>
	</div>
	
	<!-- SATURDAY -->
	<div class="form-time-slots-content-row">
		<label class="form-time-slots-days">
			<input type="checkbox" class="wc_donation_time_toggle" name="wc-donation-setTimer-time[specific_day][sat][switch]" value="1" <?php echo isset($timings['specific_day']['sat']['switch']) ? 'checked' : ''; ?>>
			<span><?php esc_html_e('Sat', 'wc-donation'); ?></span>
		</label>
		<div class="form-start-time-slots">
			<input type="time" name="wc-donation-setTimer-time[specific_day][sat][start]" class="setTimer" value="<?php echo isset($timings['specific_day']['sat']['start']) ? esc_attr($timings['specific_day']['sat']['start']) : ''; ?>">
		</div>
		<div class="form-end-time-slots">
			<input type="time" name="wc-donation-setTimer-time[specific_day][sat][end]" class="setTimer" value="<?php echo isset($timings['specific_day']['sat']['end']) ? esc_attr($timings['specific_day']['sat']['end']) : ''; ?>">
		</div>
	</div>
	
	<!-- SUNDAY -->
	<div class="form-time-slots-content-row">
		<label class="form-time-slots-days">
			<input type="checkbox" class="wc_donation_time_toggle" name="wc-donation-setTimer-time[specific_day][sun][switch]" value="1" <?php echo isset($timings['specific_day']['sun']['switch']) ? 'checked' : ''; ?>>
			<span><?php esc_html_e('Sun', 'wc-donation'); ?></span>
		</label>
		<div class="form-start-time-slots">
			<input type="time" name="wc-donation-setTimer-time[specific_day][sun][start]" class="setTimer" value="<?php echo isset($timings['specific_day']['sun']['start']) ? esc_attr($timings['specific_day']['sun']['start']) : ''; ?>">
		</div>
		<div class="form-end-time-slots">
			<input type="time" name="wc-donation-setTimer-time[specific_day][sun][end]" class="setTimer" value="<?php echo isset($timings['specific_day']['sun']['end']) ? esc_attr($timings['specific_day']['sun']['end']) : ''; ?>">
		</div>
	</div>
	
</div>

<div class="select-wrapper">
	<label class="wc-donation-label" for=""><?php echo esc_attr( __( 'Timer Display', 'wc-donation' ) ); ?></label>
	<input class="inp-cbx" style="display: none" type="radio" id="timer-display-enable" name="wc-donation-timer-display" value="enable" <?php checked( 'enable', $timerDisplay ); ?> >
	<label class="cbx" for="timer-display-enable">
		<span>
			<svg width="12px" height="9px" viewbox="0 0 12 9">
				<polyline points="1 5 4 8 11 1"></polyline>
			</svg>
		</span>
		<span><?php esc_attr_e( 'Enable', 'wc-donation' ); ?></span>
	</label>
	<input class="inp-cbx" style="display: none" type="radio" id="timer-display-disable" name="wc-donation-timer-display" value="disable" <?php checked( 'disable', $timerDisplay ); ?> >
	<label class="cbx" for="timer-display-disable">
		<span>
			<svg width="12px" height="9px" viewbox="0 0 12 9">
				<polyline points="1 5 4 8 11 1"></polyline>
			</svg>
		</span>
		<span><?php esc_attr_e( 'Disable', 'wc-donation' ); ?></span>
	</label>
	<div class="wc-donation-tooltip-box">
		<small class="wc-donation-tooltip"><?php esc_html_e('Enable or disable the timer display.', 'wc-donation'); ?></small>
	</div>
</div>

<div class="select-wrapper">
	<label class="wc-donation-label" for=""><?php echo esc_attr( __( 'Timer Display Type', 'wc-donation' ) ); ?></label>
	<input class="inp-cbx" style="display: none" type="radio" id="timer-display-type-flip" name="wc-donation-timer-display-type" value="flip_clock" <?php checked( 'flip_clock', $timerDisplayType ); ?> >
	<label class="cbx" for="timer-display-type-flip">
		<span>
			<svg width="12px" height="9px" viewbox="0 0 12 9">
				<polyline points="1 5 4 8 11 1"></polyline>
			</svg>
		</span>
		<span><?php esc_attr_e( 'Flip Clock', 'wc-donation' ); ?></span>
	</label>
	<input class="inp-cbx" style="display: none" type="radio" id="timer-display-type-pomodoro" name="wc-donation-timer-display-type" value="pomodoro" <?php checked( 'pomodoro', $timerDisplayType ); ?> >
	<label class="cbx" for="timer-display-type-pomodoro">
		<span>
			<svg width="12px" height="9px" viewbox="0 0 12 9">
				<polyline points="1 5 4 8 11 1"></polyline>
			</svg>
		</span>
		<span><?php esc_attr_e( 'Pomodoro', 'wc-donation' ); ?></span>
	</label>
	<div class="wc-donation-tooltip-box">
		<small class="wc-donation-tooltip"><?php esc_html_e('Select the type of timer display.', 'wc-donation'); ?></small>
	</div>
</div>

<div class="select-wrapper" data-parent="wc-donation-setTimer-display-after-end">
	<label class="wc-donation-label" for=""><?php echo esc_attr( __( 'Campaign after Time Ends', 'wc-donation' ) ); ?></label>
	<?php
	foreach ( WcDonation::DISPLAY_SETTIMER_TYPE() as $key => $value ) { 
		if ( $displayAfterTimeEnds == $key ) {
			$checked = 'checked';
		} else {
			$checked = '';
		}
		?>
		<input class="inp-cbx" style="display: none" type="radio" id="timeEndDisplay-<?php esc_attr_e($key); ?>" name="wc-donation-setTimer-display-after-end" value="<?php esc_attr_e($key); ?>" <?php esc_attr_e($checked); ?> >
		<label class="cbx" for="timeEndDisplay-<?php esc_attr_e($key); ?>">
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
		<small class="wc-donation-tooltip"><?php esc_html_e('Campaign display type after the time ends.', 'wc-donation'); ?></small>
	</div>
</div>

<div class="select-wrapper" data-child="wc-donation-setTimer-display-after-end" data-show="display_message" style="display:none;">
	<label class="wc-donation-label" for="wc-donation-setTimer-display-end-message"><?php echo esc_attr( __( 'Custom Message for Campaign', 'wc-donation' ) ); ?></label>
	<textarea name="wc-donation-setTimer-display-end-message" Placeholder="<?php echo esc_html__('Enter Message Here', 'wc-donation'); ?>" id="wc-donation-setTimer-display-end-message" cols="50" rows="4" style="resize: none;"><?php echo esc_attr($setTimerEndMessage); ?></textarea>
	<div class="wc-donation-tooltip-box">
		<small class="wc-donation-tooltip"><?php esc_html_e('Display message after the time ends.', 'wc-donation'); ?></small>
	</div>
</div>
