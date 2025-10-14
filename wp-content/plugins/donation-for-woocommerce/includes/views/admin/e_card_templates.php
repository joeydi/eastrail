<?php
/**
 * E-Card Templates Setting HTML
 */
$show_e_card_templates = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-e-card-templates-display-option', true  ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-e-card-templates-display-option', true  ) : 'disabled';

// Fetch existing E-Card templates from post meta
$uploaded_templates = !empty(get_post_meta($this->campaign_id, 'wc-donation-e-card-templates', true)) 
	? get_post_meta($this->campaign_id, 'wc-donation-e-card-templates', true) 
	: array();
?>
<div class="select-wrapper">
	<label class="wc-donation-label" for=""><?php echo esc_attr( __( 'Enable E-Card Templates', 'wc-donation' ) ); ?></label>
	<?php
	foreach ( WcDonation::DISPLAY_GOAL() as $key => $value ) { 
		if ( $show_e_card_templates == $key ) {
			$checked = 'checked';
		} else {
			$checked = '';
		}
		?>
		<input class="inp-cbx" style="display: none" type="radio" id="e-card-templates-<?php esc_attr_e( $key ); ?>" name="wc-donation-e-card-templates-display-option" value="<?php esc_attr_e( $key ); ?>" <?php esc_attr_e( $checked ); ?> >
		<label class="cbx" for="e-card-templates-<?php esc_attr_e( $key ); ?>">
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
		<small class="wc-donation-tooltip"><?php esc_html_e( 'Enable an E-card to be sent to customers via email when they make a donation.', 'wc-donation' ); ?></small>
	</div>
</div>
<div class="select-wrapper">
	<!-- E-Card Template Upload Section -->
	<div class="wc-donation-e-card-upload">
		<label class="wc-donation-label">
			<?php echo esc_html( __( 'Upload E-Card Template', 'wc-donation' ) ); ?>
		</label>
		<div class="wc-donation-tooltip-box">
			<small class="wc-donation-tooltip"><?php esc_html_e( 'Please upload an image file for the E-card template. Supported formats are PNG and JPEG.', 'wc-donation' ); ?></small>
		</div>

		<?php wp_nonce_field( 'save_e_card_template', 'e_card_template_nonce' ); ?>
		
		<?php 
		// Fetch the existing template from post meta
		$uploaded_template = get_post_meta( $this->campaign_id, 'wc-donation-e-card-template', true );
		?>
		<input type="hidden" id="e-card-template-ids" name="e-card-template-ids" value="<?php echo esc_attr( $uploaded_template ); ?>">

		<div id="e-card-template-wrapper">
			<?php if ( ! empty( $uploaded_template ) ) : ?>
				<div class="e-card-template">
					<img src="<?php echo esc_url( $uploaded_template ); ?>" alt="E-Card Template">
					<button type="button" class="remove-template-btn" data-template-url="<?php echo esc_url( $uploaded_template ); ?>">
						<?php esc_html_e( 'Remove', 'wc-donation' ); ?>
					</button>
				</div>
			<?php else : ?>
				<p><?php esc_html_e( 'No template uploaded yet.', 'wc-donation' ); ?></p>
			<?php endif; ?>
		</div>

		<p>
			<button type="button" class="button button-secondary" id="upload-e-card-template">
				<?php esc_html_e( 'Add Template', 'wc-donation' ); ?>
			</button>
		</p>
		<small class="wc-donation-tooltip">
			<?php esc_html_e( 'Supported formats: PNG, JPEG.', 'wc-donation' ); ?>
		</small>
	</div>
</div>

