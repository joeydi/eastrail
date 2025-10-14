<?php
class WcdonationDisplayCloack {
	
	public function __construct() { }

	public function enqueue_donation_timer_scripts() {
		wp_enqueue_script('three-js', 'https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js', array(), 'r128', true);
		wp_enqueue_script('donation-timer-init', get_template_directory_uri() . '/js/donation-timer-init.js', array( 'jquery' ), null, true);
	}

	public function display_clocks( $campaign_id, $object ) {
		$product = wc_get_product( $object->product['product_id'] );
		if ( ! $campaign_id  ) {
			return;
		}
		
		$prodID         = $product->get_id();
		$is_wc_donation = get_post_meta( $prodID, 'is_wc_donation', true );

		

		if ( 'donation' == $is_wc_donation ) {
			// Get the clock type and settings from campaign settings
			$timerDisplay = get_post_meta($campaign_id, 'wc-donation-timer-display', true);
			$timertype = get_post_meta($campaign_id, 'wc-donation-setTimer-time-type', true);
			$timerDisplayType = get_post_meta($campaign_id, 'wc-donation-timer-display-type', true);
			$time = get_post_meta($campaign_id, 'wc-donation-setTimer-time', true);
			$startTime = '';
			$endTime = '';
			if ( ! empty( $timertype ) && 'daily' == $timertype ) {
				// Check if there is a 'daily' entry for start and end time
				if (!empty($time['daily']['end'])) {
					$startTime = $time['daily']['end'];
				}
				if (!empty($time['daily']['start'])) {
					$endTime = $time['daily']['start'];
				}
			}

			if ( ! empty( $timertype ) && 'specific_day' == $timertype ) {

				// Check if there is a specific start and end time for today
				$current_day = strtolower(gmdate('D')); // Get current day abbreviation (e.g., Mon, Tue, etc.)
				if (!empty($time['specific_day'][$current_day]['end'])) {
					$startTime = $time['specific_day'][$current_day]['end'];
				}
				if (!empty($time['specific_day'][$current_day]['start'])) {
					$endTime = $time['specific_day'][$current_day]['start'];
				}
			}

			if ( !empty($timerDisplay) && 'enable' === $timerDisplay) {
				// Render the appropriate clock based on the timer display type
				echo '<div class="wc-donation-timer">';
				
				switch ($timerDisplayType) {
					case 'flip_clock':
						wp_enqueue_script('donation-flipclock-js');
						wp_enqueue_style('donation-flipclock-css');
						echo '<div class="flip-clock" data-mode="countdown" data-start-time="' . esc_attr($startTime) . '" data-end-time="' . esc_attr($endTime) . '"></div>';
						break;

					case 'pomodoro':
						wp_enqueue_script('donation-progressbar-js');
						echo '<div class="pomodoro-clock-container" id="pomodoro-timer" data-start-time="' . esc_attr($startTime) . '" data-end-time="' . esc_attr($endTime) . '">
                            <div class="timer-label" id="timer-label">00:00</div>
                        </div>';
						break;

					default:
						// Default case to handle unexpected values
						break;
				}
				echo '</div>';
				
				return;
			}

		}
	}
}
