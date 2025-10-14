<?php 
/**
 * Form Setting HTML
 */

$DonationDisp = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-display-donation-type', true  ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-display-donation-type', true  ) : 'select'; 
$currencyPos = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-currency-position', true  ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-currency-position', true  ) : 'before'; 
$donationTitle = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-title', true  ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-title', true  ) : ''; 
$prod_id = get_post_meta($this->campaign_id, 'wc_donation_product', true);

if ($prod_id) {
	$product_post = get_post($prod_id);

	if ($product_post) {
		$campaign_description = isset($product_post->post_content) ? $product_post->post_content : '';
		$campaign_short_description = isset($product_post->post_excerpt) ? $product_post->post_excerpt : '';
	}
} else {
		$campaign_description = '';
		$campaign_short_description = '';
}
$donationBtnTxt = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-button-text', true  ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-button-text', true  ) : 'Donate'; 
$donationBtnTxtColor = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-button-text-color', true  ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-button-text-color', true  ) : 'FFFFFF'; 
$donationBtnBgColor = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-button-bg-color', true  ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-button-bg-color', true  ) : '333333';

$tributeColor = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-tribute-color', true  ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-tribute-color', true  ) : '333333';
$currencyBgColor = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-currency-bg-color', true  ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-currency-bg-color', true  ) : '333333';
$currencySymbolColor = !empty( get_post_meta ( $this->campaign_id, 'wc-donation-currency-symbol-color', true  ) ) ? get_post_meta ( $this->campaign_id, 'wc-donation-currency-symbol-color', true  ) : 'FFFFFF';
?>

<div class="select-wrapper">
	<label for="wc-donation-display-donation-type" class="wc-donation-label"><?php echo esc_attr( __( 'Display Type', 'wc-donation' ) ); ?></label>
	<select name='wc-donation-display-donation-type' id="wc-donation-display-donation-type">
		<option value="">--Select Display Type--</option>
		<?php
		foreach ( WcDonation::DISPLAY_DONATION_TYPE() as $key => $value ) {
			echo '<option value="' . esc_attr( $key ) . '"' .
			selected( $DonationDisp, $key ) . '>' .
			esc_attr( $value ) . '</option>';
		}
		?>
	</select>
</div>

<div class="select-wrapper">
	<label for="wc-donation-currency-position" class="wc-donation-label"><?php echo esc_attr( __( 'Currency Position', 'wc-donation' ) ); ?></label>
	<select name='wc-donation-currency-position' id="wc-donation-currency-position">
		<option value="">--Select Currency Position--</option>
		<?php
		foreach ( WcDonation::CURRENCY_SIMBOL() as $key => $value ) {
			echo '<option value="' . esc_attr( $key ) . '"' .
			selected( $currencyPos, $key ) . '>' .
			esc_attr( $value ) . '</option>';
		}
		?>
	</select>
</div>

<div class="select-wrapper">
	<label class="wc-donation-label" for="wc-donation-title"><?php echo esc_attr( __( 'Donation Field Label', 'wc-donation' ) ); ?></label>
	<input type="text" id="wc-donation-title" Placeholder="<?php echo esc_html__('Enter Donation Field Label', 'wc-donation'); ?>" name="wc-donation-title" value="<?php echo esc_attr($donationTitle); ?>">
</div>

<div class="select-wrapper">
	<label class="wc-donation-label" for="wc-donation-button-text"><?php echo esc_attr( __( 'Donation Button Label', 'wc-donation' ) ); ?></label>
	<input type="text" id="wc-donation-button-text" Placeholder="<?php echo esc_html__('Enter Donation Button Label', 'wc-donation'); ?>" name="wc-donation-button-text" value="<?php echo esc_attr($donationBtnTxt); ?>">
</div>

<div class="select-wrapper">
	<label class="wc-donation-label" for="wc-donation-button-text-color"><?php echo esc_attr( __( 'Donation Button Text Color', 'wc-donation' ) ); ?></label>
	<input type="text" class="jscolor" id="wc-donation-button-text-color" name="wc-donation-button-text-color" value="<?php echo esc_attr($donationBtnTxtColor); ?>">
</div>

<div class="select-wrapper">
	<label class="wc-donation-label" for="wc-donation-button-bg-color"><?php echo esc_attr( __( 'Donation Button Color', 'wc-donation' ) ); ?></label>
	<input type="text" class="jscolor" id="wc-donation-button-bg-color" name="wc-donation-button-bg-color" value="<?php echo esc_attr($donationBtnBgColor); ?>">
</div>

<div class="select-wrapper">
	<label class="wc-donation-label" for="wc-donation-currency-symbol-color"><?php echo esc_attr( __( 'Currency Symbol Color', 'wc-donation' ) ); ?></label>
	<input type="text" class="jscolor" id="wc-donation-currency-symbol-color" name="wc-donation-currency-symbol-color" value="<?php echo esc_attr($currencySymbolColor); ?>">
</div>

<div class="select-wrapper">
	<label class="wc-donation-label" for="wc-donation-currency-bg-color"><?php echo esc_attr( __( 'Currency Background Color', 'wc-donation' ) ); ?></label>
	<input type="text" class="jscolor" id="wc-donation-currency-bg-color" name="wc-donation-currency-bg-color" value="<?php echo esc_attr($currencyBgColor); ?>">
</div>

<div class="select-wrapper">
	<label class="wc-donation-label" for="wc-donation-tribute-color"><?php echo esc_attr( __( 'Tribute Checkbox Color', 'wc-donation' ) ); ?></label>
	<input type="text" class="jscolor" id="wc-donation-tribute-color" name="wc-donation-tribute-color" value="<?php echo esc_attr($tributeColor); ?>">
</div>

<div class="select-wrapper">
	<label class="wc-donation-label" for="wc-donation-description"><?php echo esc_attr( __( 'Campaign Description', 'wc-donation' ) ); ?></label>
	<?php
	$content = !empty($campaign_description) ? $campaign_description : ''; // Use existing campaign description if available.
	$custom_editor_id = 'wc-donation-description';
	$custom_editor_name = 'wc-donation-description';
	$args = array(
		'media_buttons' => false, // This setting removes the media button.
		'textarea_name' => $custom_editor_name, // Set custom name.
		'textarea_rows' => get_option('default_post_edit_rows', 10), // Determine the number of rows.
		'quicktags' => false, // Remove view as HTML button.
	);
	wp_editor( $content, $custom_editor_id, $args );
	?>
</div>

<div class="select-wrapper">
	<label class="wc-donation-label" for="wc-donation-short-description"><?php echo esc_attr( __( 'Campaign Short Description', 'wc-donation' ) ); ?></label>
	<input type="text" id="wc-donation-short-description" Placeholder="<?php echo esc_html__('Enter Short Description', 'wc-donation'); ?>" name="wc-donation-short-description" value="<?php echo esc_attr($campaign_short_description); ?>">
</div>
