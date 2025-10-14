<style>
	.dashicons-money-alt:before {
		font-size: 36px !important;
		padding-bottom: 20px !important;
	}
</style>
<div class="row1">
	<h3 class="wc-donation-title ">
		<?php 
		/**
		 * 
		 * Action.
		 * 
		 * @since 3.4.6.2
		 * 
		 */
		do_action( 'wc_donation_before_title' ); 
		?>
		<?php ! empty( $donation_label ) ? esc_html_e( __( $donation_label, 'wc-donation' ) ) : ''; ?>
	</h3>
	<?php
	if ( ( 'predefined' === $display_donation || 'both' === $display_donation ) ) {

		if ( 'select' === $display_donation_type ) {
			?>
			<div class="price-wrapper <?php echo esc_attr($where_currency_symbole); ?>" currency="<?php echo esc_attr($currency_symbol); ?>">							
				<select name="wc_select_price" data-id="<?php echo esc_attr($campaign_id) . '_' . esc_attr($wp_rand); ?>" class='wc-label-select select wc-input-text <?php echo esc_attr($where_currency_symbole); ?>' id='wc-donation-f-donation-value-<?php echo esc_attr($wp_rand); ?>'>
				<option value=""><?php echo esc_html__('--Please select--', 'wc-donation'); ?></option>
				<?php
				if ( isset( $donation_values[0] ) && ( count( (array) $donation_values[0] ) > 0 ) && ! empty( $donation_values[0] ) ) {
					foreach ( $donation_values[0] as $key => $value ) {
						?>
						<option value='<?php echo esc_attr( $value ); ?>'><?php echo !empty( $donation_value_labels[0][$key] ) ? esc_attr( $donation_value_labels[0][$key] ) : esc_attr( $value ); ?></option>
						<?php
					}
				}

				if ( 'both' === $display_donation ) {
					?>
					<option value="wc-donation-other-amount"><?php echo esc_html__('Other', 'wc-donation'); ?></option>
					<?php
				}
				?>
				</select>
				</div>
			<?php
		}

		if ( 'radio' === $display_donation_type ) { 
			?>
			<div class="row1">
			<?php
			if ( isset( $donation_values[0] ) && ( count( (array) $donation_values[0] ) > 0 ) && ! empty( $donation_values[0] ) ) {
				foreach ( $donation_values[0] as $key => $value ) {
					?>
					<label for="<?php echo esc_attr($campaign_id) . '_' . esc_attr($key) . '_' . esc_attr($wp_rand); ?>" class="wc-label-radio">
						<?php /* echo esc_attr( $donation_value_labels[0][$key] ); */ ?>
						<?php echo !empty( $donation_value_labels[0][$key] ) ? esc_attr( $donation_value_labels[0][$key] ) : esc_attr( $value ); ?>
						<input type="radio" data-id="<?php echo esc_attr($campaign_id) . '_' . esc_attr($wp_rand); ?>" name="wc_radio_price" id="<?php echo esc_attr($campaign_id) . '_' . esc_attr( $key ) . '_' . esc_attr($wp_rand); ?>" value="<?php echo esc_attr( $value ); ?>">                                
						<div class="checkmark"></div>
					</label>
					<?php
				}
			}

			if ( 'both' === $display_donation ) {               
				?>
				<label for="wc-donation-other-amount-<?php echo esc_attr($campaign_id) . '_' . esc_attr($wp_rand); ?>" class="wc-label-radio">
					<?php echo esc_html__( 'Other', 'wc-donation' ); ?>
					<input type="radio" data-id="<?php echo esc_attr($campaign_id) . '_' . esc_attr($wp_rand); ?>" name="wc_radio_price" id="wc-donation-other-amount-<?php echo esc_attr($campaign_id) . '_' . esc_attr($wp_rand); ?>" value="wc-donation-other-amount">                                
					<div class="checkmark"></div>
				</label>
				<?php
			}
			?>
			</div>
			<?php
		}

		if ( 'label' === $display_donation_type ) {
			?>
			<div class="row1">
			<?php
			if ( isset( $donation_values[0] ) && ( count( (array) $donation_values[0] ) > 0 ) && ! empty( $donation_values[0] ) ) {
				foreach ( $donation_values[0] as $key => $value ) {
					?>
					<label class="wc-label-button" for="<?php echo esc_attr($campaign_id) . '_' . esc_attr( $key ) . '_' . esc_attr($wp_rand); ?>">
						<input type="radio" data-id="<?php echo esc_attr($campaign_id) . '_' . esc_attr($wp_rand); ?>" name="wc_label_price" id="<?php echo esc_attr($campaign_id) . '_' . esc_attr( $key ) . '_' . esc_attr($wp_rand); ?>" value="<?php echo esc_attr( $value ); ?>">
						<?php /* echo esc_attr( $donation_value_labels[0][$key] ); */ ?>
						<?php echo !empty( $donation_value_labels[0][$key] ) ? esc_attr( $donation_value_labels[0][$key] ) : esc_attr( $value ); ?>
					</label>
					<?php
				}
			}

			if ( 'both' === $display_donation ) { 
				?>
				<label class="wc-label-button" for="wc-donation-other-amount-<?php echo esc_attr($campaign_id) . '_' . esc_attr($wp_rand); ?>">
					<input type="radio" data-id="<?php echo esc_attr($campaign_id) . '_' . esc_attr($wp_rand); ?>" name="wc_label_price" id="wc-donation-other-amount-<?php echo esc_attr($campaign_id) . '_' . esc_attr($wp_rand); ?>" value="wc-donation-other-amount">
					<?php echo esc_html__( 'Other', 'wc-donation' ); ?>
				</label>
				<?php
			}
			?>
			</div>
			<?php 
		}

		if ( 'both' === $display_donation ) { 
			
			if ( 'custom_range' === $dispCustomType ) {
				/**
				* Filter.
				* 
				* @since 3.4.5
				*/
				$placeholder_other_val = apply_filters( 'wc_donation_other_amount_placeholder', esc_html__('Enter amount between ', 'wc-donation') . $donation_min_value . ' - ' . $donation_max_value, $donation_min_value, $donation_max_value ); 
				?>
				<div style="display:none; margin:0; " class="price-wrapper other-price-wrapper-<?php echo esc_attr($campaign_id) . '_' . esc_attr( $wp_rand ); ?> <?php echo esc_attr($where_currency_symbole); ?>" currency="<?php echo esc_attr($currency_symbol); ?>">
					<input type="text" data-min="<?php echo esc_attr($donation_min_value); ?>" data-max="<?php echo esc_attr( $donation_max_value ); ?>" data-campaign_id="<?php echo esc_attr( $campaign_id ); ?>" data-rand_id="<?php echo esc_attr( $wp_rand ); ?>" style="display:none" Placeholder="<?php echo wp_kses_post($placeholder_other_val) ; ?>" class="grab-donation wc-input-text wc-donation-f-donation-other-value" id="wc-donation-f-donation-other-value-<?php echo esc_attr($campaign_id) . '_' . esc_attr( $wp_rand ); ?>">
				</div>
				<!-- <div class="wc-donation-tooltip" style="display: none;">
					<span class="wc-donation-tooltip-icon">i</span>
					<span class="wc-donation-tooltip-text"><?php esc_attr_e( $placeholder_other_val ); ?></span>
				</div> -->
				<?php	
			} else {
				?>
				<div style="display:none; margin:0; " class="price-wrapper other-price-wrapper-<?php echo esc_attr($campaign_id) . '_' . esc_attr( $wp_rand ); ?> <?php echo esc_attr($where_currency_symbole); ?>" currency="<?php echo esc_attr($currency_symbol); ?>">
					<input type="text" data-min="any" data-max="any" data-campaign_id="<?php echo esc_attr( $campaign_id ); ?>" data-rand_id="<?php echo esc_attr( $wp_rand ); ?>" style="display:none" Placeholder="<?php echo wp_kses_post($freeAmountPlaceHolder) ; ?>" class="grab-donation wc-input-text wc-donation-f-donation-other-value" id="wc-donation-f-donation-other-value-<?php echo esc_attr($campaign_id) . '_' . esc_attr( $wp_rand ); ?>">
				</div>
				<!-- <div class="wc-donation-tooltip" style="display: none;">
					<span class="wc-donation-tooltip-icon">i</span>
					<span class="wc-donation-tooltip-text"><?php esc_attr_e( $freeAmountPlaceHolder ); ?></span>
				</div> -->
				<?php
			}           
		}
		echo '<input type="hidden" id="wc-donation-price-' . esc_attr($campaign_id) . '_' . esc_attr($wp_rand) . '" class="donate_' . esc_attr($campaign_id) . '_' . esc_attr($wp_rand) . '" name="wc-donation-price" value="">';
		echo '<input type="hidden" id="wc-donation-cause-' . esc_attr($campaign_id) . '_' . esc_attr($wp_rand) . '" class="donate_cause_' . esc_attr($campaign_id) . '_' . esc_attr($wp_rand) . '" name="wc-donation-cause" value="">';

	} else {

		if ( 'custom_range' === $dispCustomType ) {
			/**
			* Filter.
			* 
			* @since 3.4.5
			*/
			$placeholder_other_val = apply_filters( 'wc_donation_other_amount_placeholder', esc_html__('Enter amount between ', 'wc-donation') . $donation_min_value . ' - ' . $donation_max_value, $donation_min_value, $donation_max_value );
			?>
			<div class="price-wrapper <?php echo esc_attr($where_currency_symbole); ?>" currency="<?php echo esc_attr($currency_symbol); ?>">
				<input type="text" data-min="<?php echo esc_attr($donation_min_value); ?>" data-max="<?php echo esc_attr($donation_max_value); ?>" data-campaign_id="<?php echo esc_attr($campaign_id); ?>" data-rand_id="<?php echo esc_attr($wp_rand); ?>" onKeyup="return NumbersOnly(this, event, true);" class="grab-donation wc-input-text donate_<?php echo esc_attr($campaign_id) . '_' . esc_attr($wp_rand); ?> <?php echo esc_attr($where_currency_symbole); ?>" Placeholder="<?php echo wp_kses_post($placeholder_other_val); ?>" name="wc-donation-price" >
				<!-- <div class="wc-donation-tooltip" style="display: none;">
					<span class="wc-donation-tooltip-icon">i</span>
					<span class="wc-donation-tooltip-text"><?php esc_attr_e( $placeholder_other_val ); ?></span>
				</div> -->
			</div>
			<?php
		} else {           
			?>
			<div class="price-wrapper <?php echo esc_attr($where_currency_symbole); ?>" currency="<?php echo esc_attr($currency_symbol); ?>">
				<input type="text" data-min="any" data-max="any" data-campaign_id="<?php echo esc_attr( $campaign_id ); ?>" data-rand_id="<?php echo esc_attr( $wp_rand ); ?>" Placeholder="<?php echo wp_kses_post($freeAmountPlaceHolder) ; ?>" class="grab-donation wc-input-text wc-donation-f-donation-other-value" id="wc-donation-f-donation-other-value-<?php echo esc_attr($campaign_id) . '_' . esc_attr( $wp_rand ); ?>">
				<div class="wc-donation-tooltip" style="display: none;">
					<span class="wc-donation-tooltip-icon">i</span>
					<span class="wc-donation-tooltip-text"><?php esc_attr_e( $freeAmountPlaceHolder ); ?></span>
				</div>
			</div>
			<?php
		}

		echo '<input type="hidden" id="wc-donation-price-' . esc_attr($campaign_id) . '_' . esc_attr($wp_rand) . '" class="donate_' . esc_attr($campaign_id) . '_' . esc_attr($wp_rand) . '" name="wc-donation-price" value="">';
		echo '<input type="hidden" id="wc-donation-cause-' . esc_attr($campaign_id) . '_' . esc_attr($wp_rand) . '" class="donate_cause_' . esc_attr($campaign_id) . '_' . esc_attr($wp_rand) . '" name="wc-donation-cause" value="">';

	}
	?>
</div>
