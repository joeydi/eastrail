<form method="post" class="wc-donation-form" action="options.php">
	<?php
	/**
	 * Admin settings.
	 *
	 * @package donation
	 */

	settings_fields( 'wc-donation-general-settings-group' );
	do_settings_sections( 'wc-donation-general-settings-group' );
	$products        = wc_get_products( array( 'type' => 'simple', 'limit' => -1, 'meta_key' => 'is_wc_donation', 'meta_value' => 'donation' ) );
	$donation_on_checkout = get_option( 'wc-donation-on-checkout' );
	$donation_on_cart = get_option( 'wc-donation-on-cart' );
	$donation_values = get_option( 'wc-donation-donation-values' );
	$donation_label  = !empty( esc_attr( get_option( 'wc-donation-field-label' ))) ? esc_attr( get_option( 'wc-donation-field-label' )) : 'Donation';
	$donation_button_text  = !empty( esc_attr( get_option( 'wc-donation-button-text' ))) ? esc_attr( get_option( 'wc-donation-button-text' )) : 'Donate';
	$donation_button_color  = !empty( esc_attr( get_option( 'wc-donation-button-color' ))) ? esc_attr( get_option( 'wc-donation-button-color' )) : 'd5d5d5';
	$donation_button_text_color  = !empty( esc_attr( get_option( 'wc-donation-button-text-color' ))) ? esc_attr( get_option( 'wc-donation-button-text-color' )) : '000000';
	
		// echo '<pre>';
		// print_r(WcDonation::get_wpml_lang_code());
		// echo '</pre>';
		// wp_die();
	?>

	<div class="select-wrapper">
		<label for=""><?php echo esc_attr( __( 'Donation Product', 'wc-donation' ) ); ?></label>
		<select class='select short' style="width:200px;" name='wc-donation-product<?php echo esc_attr(WcDonation::get_wpml_lang_code()); ?>' >
			<option><?php echo esc_html(__('select product', 'wc-donation')); ?></option>
			<?php
			foreach ( $products as $product ) {
				echo '<option value="' . esc_attr( $product->get_id() ) . '"' .
				selected( get_option( 'wc-donation-product' . WcDonation::get_wpml_lang_code() ), $product->get_id() ) . '>' .
				esc_attr( $product->get_name() ) . '</option>';
			}
			?>
		</select>
	</div>

	<?php wp_reset_postdata(); ?>

	<?php 
	if ( 'yes' === $donation_on_checkout ) {
		$checked_for_checkout = 'checked';
	} 
	
	?>
	<div class="select-wrapper">
		<label class="wc-donation-switch">
			<input id="wc-donation-on-checkout" name="wc-donation-on-checkout" type="checkbox" value="yes" <?php echo esc_html( @$checked_for_checkout); ?>>
			<span class="wc-slider round"></span>
		</label>
		<label for="wc-donation-on-checkout"><?php echo esc_attr( __( 'Show Donation form on checkout', 'wc-donation' ) ); ?></label>
	</div>

	<?php 
	if ( 'yes' === $donation_on_cart ) {
		$checked_for_cart = 'checked';
	} 
	
	?>
	<div class="select-wrapper">
		<label class="wc-donation-switch">
			<input id="wc-donation-on-cart" name="wc-donation-on-cart" type="checkbox" value="yes" <?php echo esc_html( @$checked_for_cart); ?>>
			<span class="wc-slider round"></span>
		</label>
		<label for="wc-donation-on-cart"><?php echo esc_attr( __( 'Show Donation form on cart', 'wc-donation' ) ); ?></label>
	</div>

	<div class="select-wrapper">
		<label for=""><?php echo esc_attr( __( 'Display Donation', 'wc-donation' ) ); ?></label>
		<select name='wc-donation-display-donation'>
			<option><?php echo esc_html(__('select product', 'wc-donation')); ?></option>
			<?php
			foreach ( WcDonation::DISPLAY_DONATION() as $key => $value ) {
				echo '<option value="' . esc_attr( $key ) . '"' .
				selected( get_option( 'wc-donation-display-donation' ), $key ) . '>' .
				esc_attr( $value ) . '</option>';
			}
			?>
		</select>
	</div>

	<div id="wc-donation-section-form-donation">

		<div>
			<div id="wc-danation-stored-values-donation">
				<?php
				if ( ! empty( $donation_values ) ) {
					foreach ( $donation_values as $value ) {
						echo "<div class='wc-donation-row-donation'> 
						<span class='wc-donation-value-donation'>" . esc_attr( $value ) . "</span> 
						<button class ='wc-donation-row-delete-donation'> " . esc_attr( __( 'Delete', 'wc-donation' ) ) . '</button>
						</div>';
					}
				}
				?>
			</div>
			<button class='button button-primary' onclick="displayForm(event)"> <i class="fa fa-plus"></i> <?php echo esc_attr( __( 'Add Donation', 'wc-donation' ) ); ?></button>
			<div id="wc-donation-form-donation" style="display:none;">
				<input id='wc-domain-input-value-donation' name='wc-domain-donation-value' type='number' min="1" value="<?php echo esc_attr( get_option( 'wc-domain-donation-value' ) ); ?>">
				<button class='button button-primary' id='wc-domain-submit-value-donation'> <?php echo esc_attr( __( 'Save', 'wc-donation' ) ); ?> </button>
			</div>
		</div>

	</div>

	<div class="select-wrapper">
		<label for=""><?php echo esc_attr__( 'Currency Symbol', 'wc-donation' ); ?></label>

		<select name='wc-donation-currency-symbol'>
			<?php
			foreach ( WcDonation::CURRENCY_SIMBOL() as $key => $value ) {
				echo '<option value="' . esc_attr( $key ) . '"' .
				selected( get_option( 'wc-donation-currency-symbol' ), $key ) . '>' .
				esc_attr( $value ) . '</option>';
			}
			?>
		</select>
	</div>

	<div class="select-wrapper">
		<label for=""><?php echo esc_attr__( 'Donation Field Label', 'wc-donation' ); ?></label>
		<input type="text" value="<?php echo esc_attr($donation_label); ?>" name="wc-donation-field-label" id="wc-donation-field-label" />
	</div>

	<div class="select-wrapper">
		<label for=""><?php echo esc_attr__( 'Button Text', 'wc-donation' ); ?></label>
		<input type="text" value="<?php echo esc_attr($donation_button_text); ?>" name="wc-donation-button-text" id="wc-donation-button-text" />
	</div>

	<div class="select-wrapper">
		<label for=""><?php echo esc_attr__( 'Button Color', 'wc-donation' ); ?></label>
		<input type="text" class="jscolor" value="<?php echo esc_attr($donation_button_color); ?>" name="wc-donation-button-color" id="wc-donation-button-color" />
	</div>

	<div class="select-wrapper">
		<label for=""><?php echo esc_attr__( 'Button Text Color', 'wc-donation' ); ?></label>
		<input type="text" class="jscolor" value="<?php echo esc_attr($donation_button_text_color); ?>" name="wc-donation-button-text-color" id="wc-donation-button-text-color" />
	</div>


	<?php submit_button(); ?>

</form>
