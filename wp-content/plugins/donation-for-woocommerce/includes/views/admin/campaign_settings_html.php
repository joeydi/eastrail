<?php 
/**
 * Campaign Setting HTML
 */
if ( ! did_action( 'wp_enqueue_media' ) ) {
		wp_enqueue_media();
}
$dispCustomType = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-custom-type-option', true  ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-custom-type-option', true  ) : 'custom_range';
$freeAmountPlaceHolder = !empty( get_post_meta ( $this->campaign_id, 'free-amount-ph', true  ) ) ? get_post_meta ( $this->campaign_id, 'free-amount-ph', true  ) : '';
$amountDisp = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-amount-display-option', true  ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-amount-display-option', true  ) : 'both';
$freeMinAmount = !empty( get_post_meta ( $this->campaign_id, 'free-min-amount', true  ) ) ? get_post_meta ( $this->campaign_id, 'free-min-amount', true  ) : ''; 
$freeMaxAmount = !empty( get_post_meta ( $this->campaign_id, 'free-max-amount', true  ) ) ? get_post_meta ( $this->campaign_id, 'free-max-amount', true  ) : ''; 
$predAmount = !empty( get_post_meta ( $this->campaign_id, 'pred-amount', false  ) ) ? get_post_meta ( $this->campaign_id, 'pred-amount', false  ) : array();
$predLabel = !empty( get_post_meta ( $this->campaign_id, 'pred-label', false  ) ) ? get_post_meta ( $this->campaign_id, 'pred-label', false  ) : array();
$required = ( 'custom_range' == $dispCustomType && ( 'both' === $amountDisp || 'free-value' === $amountDisp ) ) ? 'required' : '';
?>
<div class="select-wrapper display-option">
	<label class="wc-donation-label" for=""><?php echo esc_attr( __( 'Amount Type', 'wc-donation' ) ); ?></label>
	<?php
	foreach ( WcDonation::DISPLAY_DONATION() as $key => $value ) { 
		if ( $amountDisp == $key ) {
			$checked = 'checked';
		} else {
			$checked = '';
		}

		?>
		<input class="inp-cbx" style="display: none" type="radio" id="<?php esc_attr_e($key); ?>" name="wc-donation-amount-display-option" value="<?php esc_attr_e($key); ?>" <?php esc_attr_e($checked); ?> >
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
</div>

<div id="wc-donation-predefined-wrapper" class="display-wrapper" data-id="predefined">
	<?php 
	if ( ! empty( $predAmount[0] ) ) {
		foreach ( $predAmount[0] as $key => $val ) {
			?>
			<div class="pred" id="pred-<?php echo esc_attr($key); ?>">
				<div class="pred-wrapper">
					<a href="#" class="dashicons dashicons-trash pred-delete"></a>
					<h4><?php echo esc_html__('Donation Level', 'wc-donation'); ?></h4>
					<div class="select-wrapper">
						<label class="wc-donation-label" for="pred-amount-<?php echo esc_attr($key); ?>"><?php echo esc_attr( __( 'Amount', 'wc-donation' ) ); ?></label>
						<input type="number" step="0.01" id="pred-amount-<?php echo esc_attr($key); ?>" Placeholder="<?php echo esc_html__('Enter Amount', 'wc-donation'); ?>" name="pred-amount[]" value="<?php echo esc_attr($val); ?>"min="1" required>
					</div>
					<div class="select-wrapper">
						<label class="wc-donation-label" for="pred-label-<?php echo esc_attr($key); ?>"><?php echo esc_attr( __( 'Label', 'wc-donation' ) ); ?></label>
						<input type="text" id="pred-label-<?php echo esc_attr($key); ?>" Placeholder="<?php echo esc_html__('Enter Label', 'wc-donation'); ?>" name="pred-label[]" value="<?php echo esc_attr($predLabel[0][$key]); ?>" required>
					</div>
				</div>
			</div>
			<?php
		}
	}
	?>
		
</div>
<a href="#" id="pred-add-more"><?php echo esc_attr( __( 'Add Level', 'wc-donation' ) ); ?></a>

<div id="wc-donation-free-value-wrapper" class="display-wrapper" data-id="free-value">
	<div class="free-amount">
		<div class="select-wrapper custom-display-option">
			<label class="wc-donation-label" for=""><?php echo esc_attr( __( 'Custom Type', 'wc-donation' ) ); ?></label>
			<?php
			foreach ( WcDonation::DISPLAY_CUSTOM_TYPE() as $key => $value ) { 
				if ( $dispCustomType == $key ) {
					$checked = 'checked';
				} else {
					$checked = '';
				}

				?>
				<input class="inp-cbx" style="display: none" type="radio" id="<?php esc_attr_e($key); ?>" name="wc-donation-custom-type-option" value="<?php esc_attr_e($key); ?>" <?php esc_attr_e($checked); ?> >
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
		</div>
		<div class="pred-wrapper" style="display:flex; justify-content: space-between; align-items: center; width: max-content;">
			<div class="toLeft" data-id="custom_range">				
				<div class="select-wrapper" style="display:flex; align-items: center;">
					<label class="wc-donation-label" for="free-min-amount"><?php echo esc_attr( __( 'Min Amount', 'wc-donation' ) ); ?></label>
					<input type="number" step="any" id="free-min-amount" Placeholder="<?php echo esc_html__('Enter Amount', 'wc-donation'); ?>" name="free-min-amount" value="<?php echo esc_attr($freeMinAmount); ?>"min="1" <?php esc_attr_e($required); ?> />
				</div>
				<div class="select-wrapper" style="display:flex; align-items: center;">
					<label class="wc-donation-label" for="free-max-amount"><?php echo esc_attr( __( 'Max Amount', 'wc-donation' ) ); ?></label>
					<input type="number" step="any" id="free-max-amount" Placeholder="<?php echo esc_html__('Enter Amount', 'wc-donation'); ?>" name="free-max-amount" value="<?php echo esc_attr($freeMaxAmount); ?>"min="1" <?php esc_attr_e($required); ?> />
				</div>
			</div>

			<div class="toRight" data-id="custom_value">				
				<div class="select-wrapper" style="display:flex; align-items: center; margin: 0;">
					<label class="wc-donation-label" for="free-amount-ph"><?php echo esc_attr( __( 'Custom Amount Placeholder Text', 'wc-donation' ) ); ?></label>
					<textarea name="free-amount-ph" id="free-amount-ph" cols="30" rows="4" style="resize:none; margin: 0;"><?php echo esc_attr($freeAmountPlaceHolder); ?></textarea>
				</div>
			</div>
		</div>
	</div>
</div>
