<form method="post" class="wc-donation-form" action="options.php">
	<?php
	/**
	 * Admin settings.
	 *
	 * @package donation
	 */

	settings_fields( 'wc-donation-general-settings-group' );
	do_settings_sections( 'wc-donation-general-settings-group' );
	$campaigns = get_posts(array(
		'fields'          => 'ids',
		'posts_per_page'  => -1,
		'post_type' => 'wc-donation',
	));
	$donation_on_checkout = get_option( 'wc-donation-on-checkout' );
	$donation_on_checkout_location = get_option( 'wc-donation-checkout-location' );
	$donation_on_cart = get_option( 'wc-donation-on-cart' );
	$donation_on_cart_location = get_option( 'wc-donation-cart-location' );
	$campaign_display_type = get_option( 'wc-donation-campaign-display-type' );
	$cart_campaign_popup_title = get_option( 'wc-donation-cart-campaign-popup-title' );
	$campaign_display_format = get_option( 'wc-donation-cart-campaign-display-format' );
	$cart_campaign_popup_button_title = get_option( 'wc-donation-cart-campaign-popup-button-title' );
	$donation_on_cart_campaign_format_type = get_option( 'wc-donation-cart-campaign-format-type' );
	$deactivate_campaign_thumbnail = get_option( 'wc-donation-deactivate-campaign-thumbnail' );
	$deactivate_campaign_causes = get_option( 'wc-donation-deactivate-campaign-causes' );
	$deactivate_campaign_description = get_option( 'wc-donation-deactivate-campaign-description' );
	$cart_campaigns = !empty( get_option( 'wc-donation-cart-product' ) ) ? get_option( 'wc-donation-cart-product' ) : array();
	$checkout_campaigns = !empty( get_option( 'wc-donation-checkout-product' ) ) ? get_option( 'wc-donation-checkout-product' ) : array();
	$donation_on_round = get_option( 'wc-donation-on-round' );
	$donation_card_fee = get_option( 'wc-donation-card-fee' );
	$donation_fees_type = get_option('wc-donation-fees-type', 'percentage');
	$donation_recommended_products = get_option( 'wc-donation-recommended' );

	$donation_pdf = get_option( 'wc-donation-pdf-receipt' );
	$donation_tributes = get_option( 'wc-donation-tributes' );
	$tributes_messages = get_option( 'wc-donation-messages' );
	$donor_wall = get_option( 'wc-donation-donor-wall' );
	$donation_api = get_option( 'wc-donation-api' );
	$donation_gift_aid = get_option( 'wc-donation-gift-aid' );
	$donation_gift_aid_area = get_option( 'wc-donation-gift-aid-area', array() );
	$donation_gift_aid_title = get_option( 'wc-donation-gift-aid-title' );
	$donation_gift_aid_checkbox_title = get_option( 'wc-donation-gift-aid-checkbox-title' );
	$donation_gift_aid_explanation = get_option( 'wc-donation-gift-aid-explanation' );
	$donation_gift_aid_declaration = get_option( 'wc-donation-gift-aid-declaration' );

	$donation_round_multiplier = get_option( 'wc-donation-round-multiplier' );
	$donation_fees_percent = get_option( 'wc-donation-fees-percent' );
	$donation_label  = !empty( esc_attr( get_option( 'wc-donation-round-field-label' ))) ? esc_attr( get_option( 'wc-donation-round-field-label' )) : 'Donation';
	$donation_message  = !empty( esc_attr( get_option( 'wc-donation-round-field-message' ))) ? esc_attr( get_option( 'wc-donation-round-field-message' )) : '';
	$donation_fees_message  = !empty( esc_attr( get_option( 'wc-donation-fees-field-message' ))) ? esc_attr( get_option( 'wc-donation-fees-field-message' )) : '';
	$donation_button_text  = !empty( esc_attr( get_option( 'wc-donation-round-button-text' ))) ? esc_attr( get_option( 'wc-donation-round-button-text' )) : 'Donate';
	$donation_button_cancel_text  = !empty( esc_attr( get_option( 'wc-donation-round-button-cancel-text' ))) ? esc_attr( get_option( 'wc-donation-round-button-cancel-text' )) : 'Skip';
	$donation_button_color  = !empty( esc_attr( get_option( 'wc-donation-round-button-color' ))) ? esc_attr( get_option( 'wc-donation-round-button-color' )) : 'd5d5d5';
	$donation_button_text_color  = !empty( esc_attr( get_option( 'wc-donation-round-button-text-color' ))) ? esc_attr( get_option( 'wc-donation-round-button-text-color' )) : '000000';

	?>

	<h1 class="wc-main-title"><?php echo esc_html__('General Setting', 'wc-donation'); ?></h1>
	<div class="sep20px">&nbsp;</div>

	<div class="wc-block-setting-wrapper">
		<div class="wc-block-setting wc-grid-half">
			<h3><?php echo esc_html__('Legacy Cart Donation', 'wc-donation'); ?></h3>
			<?php 
			if ( 'yes' === $donation_on_cart ) {
				$checked_for_cart = 'checked';
			} else {
				$checked_for_cart = '';
			}
			
			?>
			<div class="select-wrapper">
				<label class="wc-donation-switch">
					<input id="wc-donation-on-cart" name="wc-donation-on-cart" type="checkbox" value="yes" <?php echo esc_html( $checked_for_cart); ?>>
					<span class="wc-slider round"></span>
				</label>
				<label for="wc-donation-on-cart" class="wc-text-label"><?php echo esc_attr( __( 'Show Donation form on cart', 'wc-donation' ) ); ?></label>
			</div>

			<div class="select-wrapper">				
				<label for=""><?php echo esc_attr( __( 'Select Campaign', 'wc-donation' ) ); ?></label>
				<select class='select short' style="width:200px;" name='wc-donation-cart-product[]' multiple>
					<!-- <option value=""><?php echo esc_html(__('select Campaign', 'wc-donation')); ?></option> -->
					<?php
					foreach ( $campaigns as $campaign ) {
						if ( is_array($cart_campaigns) && in_array($campaign, $cart_campaigns) ) {
							$selected = "selected='selected'";
						} else {
							$selected = '';
						}
						echo '<option value="' . esc_attr( $campaign ) . '"' .
						esc_attr($selected) . '>' .
						esc_attr( get_the_title($campaign) ) . '</option>';
					}
					?>
				</select>
			</div>

			<div class="select-wrapper">				
				<label for=""><?php echo esc_attr( __( 'Choose Location on Cart', 'wc-donation' ) ); ?></label>
				<select class='select short' style="width:200px;" name='wc-donation-cart-location'>
				<?php if ( empty( $donation_on_cart_location ) || ! $donation_on_cart_location ) : ?>
					<?php $donation_on_cart_location = 'after_cart_table'; ?>
				<?php endif; ?>
					<option value="before_cart_table" <?php selected( $donation_on_cart_location, 'before_cart_table', true ); ?>>Before Cart Table</option>
					<option value="after_cart_table" <?php selected( $donation_on_cart_location, 'after_cart_table', true ); ?>>After Cart Table</option>
					<option value="after_cart" <?php selected( $donation_on_cart_location, 'after_cart', true ); ?>>After Cart</option>
				</select>
			</div>

			<div class="select-wrapper">
				<?php if ( empty( $campaign_display_type ) || ! $campaign_display_type ) : ?>
					<?php $campaign_display_type = 'page'; ?>
				<?php endif; ?>			
				<label for=""><?php echo esc_attr( __( 'Campaign Display Type', 'wc-donation' ) ); ?></label>
				<div class="select-wrapper cart-campaign-display-type-option" style="margin-top: 10px;">
					<input class="inp-cbx" style="display: none" type="radio" id="page" name="wc-donation-campaign-display-type" value="page" <?php checked( $campaign_display_type, 'page', true ); ?>>
					<label class="cbx" for="page" style="margin-right: 10px;">
						<span>
							<svg width="12px" height="9px" viewBox="0 0 12 9">
								<polyline points="1 5 4 8 11 1"></polyline>
							</svg>
						</span>
						<span>Page</span>
					</label>
					<input class="inp-cbx" style="display: none" type="radio" id="popup" name="wc-donation-campaign-display-type" value="popup" <?php checked( $campaign_display_type, 'popup', true ); ?>>
					<label class="cbx" for="popup">
						<span>
							<svg width="12px" height="9px" viewBox="0 0 12 9">
								<polyline points="1 5 4 8 11 1"></polyline>
							</svg>
						</span>
						<span>Pop-up</span>
					</label>
				</div>
			</div>
			
			<div class="wc-donation-popup-settings">
				<div class="select-wrapper">
					<label for="">Pop-up Header Text<br><small style="color:#777">(Enter the title for the popup screen)</small></label>
					<input type="text" value="<?php echo ( ! empty( $cart_campaign_popup_title ) ? esc_attr( $cart_campaign_popup_title ) : 'Campaigns' ); ?>" name="wc-donation-cart-campaign-popup-title" id="wc-donation-cart-campaign-popup-title">
				</div>

				<div class="select-wrapper">
					<?php if ( empty( $campaign_display_format ) || ! $campaign_display_format ) : ?>
						<?php $campaign_display_format = 'button_display'; ?>
					<?php endif; ?>			
					<label for=""><?php echo esc_attr( __( 'Campaign Display Format', 'wc-donation' ) ); ?></label>
					<div class="select-wrapper cart-campaign-display-format-option" style="margin-top: 10px;">
						<input class="inp-cbx" style="display: none" type="radio" id="auto_display" name="wc-donation-cart-campaign-display-format" value="auto_display" <?php checked( $campaign_display_format, 'auto_display', true ); ?>>
						<label class="cbx" for="auto_display" style="margin-right: 10px;">
							<span>
								<svg width="12px" height="9px" viewBox="0 0 12 9">
									<polyline points="1 5 4 8 11 1"></polyline>
								</svg>
							</span>
							<span>Auto Display</span>
						</label>
						<input class="inp-cbx" style="display: none" type="radio" id="button_display" name="wc-donation-cart-campaign-display-format" value="button_display" <?php checked( $campaign_display_format, 'button_display', true ); ?>>
						<label class="cbx" for="button_display">
							<span>
								<svg width="12px" height="9px" viewBox="0 0 12 9">
									<polyline points="1 5 4 8 11 1"></polyline>
								</svg>
							</span>
							<span>Button Display</span>
						</label>
					</div>
				</div>

				<div class="select-wrapper">
					<label for="">Show Button Text - Pop-up Screen<br><small style="color:#777">(Enter the text for Show Button on the page to open Pop-up screen)</small></label>
					<input type="text" value="<?php echo ( ! empty( $cart_campaign_popup_button_title ) ? esc_attr( $cart_campaign_popup_button_title ) : 'View Campaigns' ); ?>" name="wc-donation-cart-campaign-popup-button-title" id="wc-donation-cart-campaign-popup-button-title">
				</div>
			</div>

			<div class="select-wrapper">				
				<label for=""><?php echo esc_attr( __( 'Campaign Format Type', 'wc-donation' ) ); ?></label>
				<select class='select short' style="width:200px;" name='wc-donation-cart-campaign-format-type'>
				<?php if ( empty( $donation_on_cart_campaign_format_type ) || ! $donation_on_cart_campaign_format_type ) : ?>
					<?php $donation_on_cart_campaign_format_type = 'list'; ?>
				<?php endif; ?>
					<option value="block" <?php selected( $donation_on_cart_campaign_format_type, 'block', true ); ?>>Block</option>
					<option value="table" <?php selected( $donation_on_cart_campaign_format_type, 'table', true ); ?>>Table</option>
					<option value="list" <?php selected( $donation_on_cart_campaign_format_type, 'list', true ); ?>>List</option>
					<option value="grid" <?php selected( $donation_on_cart_campaign_format_type, 'grid', true ); ?>>Grid</option>
				</select>
			</div>
			

			<div class="select-wrapper" id="wc-donation-deactivate-columns-in-table-format">
				<label for="" style=""><?php echo esc_html__('Deactivate Columns in Table Format', 'wc-donation'); ?></label>
				<div class="select-wrapper" style="margin-top: 10px;">
					<label class="wc-donation-switch">
						<input id="wc-donation-deactivate-campaign-thumbnail" name="wc-donation-deactivate-campaign-thumbnail" type="checkbox" value="yes" <?php checked( $deactivate_campaign_thumbnail, 'yes', true ); ?>>
						<span class="wc-slider round"></span>
					</label>
					<label for="wc-donation-deactivate-campaign-thumbnail" class="wc-text-label"><?php echo esc_attr( __( 'Deactivate Campaign Thumbnail', 'wc-donation' ) ); ?></label>
				</div>

				<div class="select-wrapper">
					<label class="wc-donation-switch">
						<input id="wc-donation-deactivate-campaign-causes" name="wc-donation-deactivate-campaign-causes" type="checkbox" value="yes" <?php checked( $deactivate_campaign_causes, 'yes', true ); ?>>
						<span class="wc-slider round"></span>
					</label>
					<label for="wc-donation-deactivate-campaign-causes" class="wc-text-label"><?php echo esc_attr( __( 'Deactivate Campaign Causes', 'wc-donation' ) ); ?></label>
				</div>

				<div class="select-wrapper">
					<label class="wc-donation-switch">
						<input id="wc-donation-deactivate-campaign-description" name="wc-donation-deactivate-campaign-description" type="checkbox" value="yes" <?php checked( $deactivate_campaign_description, 'yes', true ); ?>>
						<span class="wc-slider round"></span>
					</label>
					<label for="wc-donation-deactivate-campaign-description" class="wc-text-label"><?php echo esc_attr( __( 'Deactivate Campaign Description', 'wc-donation' ) ); ?></label>
					<p><small style="color:#777;font-size: 13px;font-weight: 500;">Select the option to Deactivate Columns in Table Format(Cart)</small></p>
				</div>
			</div>
		</div>

		<div class="wc-block-setting wc-grid-half">
			<h3><?php echo esc_html__('Legacy Checkout Donation', 'wc-donation'); ?></h3>
			<?php 
			if ( 'yes' === $donation_on_checkout ) {
				$checked_for_checkout = 'checked';
			} else {
				$checked_for_checkout = '';
			}
			
			?>
			<div class="select-wrapper">
				<label class="wc-donation-switch">
					<input id="wc-donation-on-checkout" name="wc-donation-on-checkout" type="checkbox" value="yes" <?php echo esc_html( $checked_for_checkout); ?>>
					<span class="wc-slider round"></span>
				</label>
				<label for="wc-donation-on-checkout" class="wc-text-label"><?php echo esc_attr( __( 'Show Donation form on checkout', 'wc-donation' ) ); ?></label>
			</div>

			<div class="select-wrapper">
				<label for=""><?php echo esc_attr( __( 'Select Campaign', 'wc-donation' ) ); ?></label>
				<select class='select short' style="width:200px;" name='wc-donation-checkout-product[]' multiple>
					<!-- <option value=""><?php echo esc_html(__('Select Campaign', 'wc-donation')); ?></option> -->
					<?php
					foreach ( $campaigns as $campaign ) {
						if ( is_array($checkout_campaigns) && in_array($campaign, $checkout_campaigns) ) {
							$selected = "selected='selected'";
						} else {
							$selected = '';
						}

						echo '<option value="' . esc_attr( $campaign ) . '"' .
						esc_attr($selected) . '>' .
						esc_attr( get_the_title($campaign) ) . '</option>';
					}
					?>
				</select>
			</div>

			<div class="select-wrapper">				
				<label for="">Choose Location on Checkout</label>
				<?php if ( empty( $donation_on_checkout_location ) || ! $donation_on_checkout_location ) : ?>
					<?php $donation_on_checkout_location = 'before_payment_method'; ?>
				<?php endif; ?>
				<select class="select short" style="width:200px;" name="wc-donation-checkout-location">
					<option value="before_payment_method" <?php selected( $donation_on_checkout_location, 'before_payment_method', true ); ?>>Before Payment method section</option>
					<option value="after_payment_method" <?php selected( $donation_on_checkout_location, 'after_payment_method', true ); ?>>After Payment method section</option>
				</select>
			</div>

		</div>

		<div class="wc-block-setting wc-grid-full">

			<div class="wc-block-setting">
				<h3><?php echo esc_html__('Round Off Donation', 'wc-donation'); ?></h3>
				<?php 
				if ( 'yes' === $donation_on_round ) {
					$checked_for_round = 'checked';
				} else {
					$checked_for_round = '';
				}
				
				$remaining_campaigns = array();

				if ( 'yes' === $donation_on_cart ) { 
					$remaining_campaigns = $cart_campaigns; 
				}

				if ( 'yes' === $donation_on_checkout ) {
					$remaining_campaigns = array_merge( ( array ) $remaining_campaigns, ( array ) $checkout_campaigns);
				}

				 $remaining_campaign = get_posts(array(
					'fields'          => 'ids',
					'posts_per_page'  => -1,
					'post_type' => 'wc-donation',
					'post__not_in' => $remaining_campaigns,
				));

					?>

				<div class="select-wrapper">
					<label class="wc-donation-switch">
						<input id="wc-donation-on-round" name="wc-donation-on-round" type="checkbox" value="yes" <?php echo esc_html( $checked_for_round); ?> onchange="document.getElementById('wc-donation-round-multiplier').required=this.checked">
						<span class="wc-slider round"></span>
					</label>
					<label for="wc-donation-on-round" class="wc-text-label"><?php echo esc_attr( __( 'Round Off Donation', 'wc-donation' ) ); ?></label>
				</div>

				<div class="select-wrapper">
					<label for=""><?php echo esc_attr( __( 'Select Campaign', 'wc-donation' ) ); ?></label>
					<select class='select short' style="width:200px;" name='wc-donation-round-product'>
						<option><?php echo esc_html(__('select Campaign', 'wc-donation')); ?></option>
						<?php	      
		
						foreach ( $remaining_campaign as $campaign ) {
							$object = WcdonationCampaignSetting::get_product_by_campaign($campaign);
							$goalDisp = !empty( $object->goal['display'] ) ? $object->goal['display'] : '';

							if ( 'enabled' !== $goalDisp ) {
								?>
								<option value="<?php echo esc_attr($campaign); ?>" <?php selected( get_option( 'wc-donation-round-product'), $campaign ); ?>><?php echo esc_attr( get_the_title($campaign) ); ?></option>
								<?php
							}                           
						}
						?>
					</select>
				</div>
				
				<div class="select-wrapper">
					<label for=""><?php echo esc_attr__( 'Round Off Multiplier', 'wc-donation' ); ?><br><small style="color:#777"><?php echo esc_attr__( '(number should be greater than 0. if empty or other value than integer, it will be considered as 1)', 'wc-donation' ); ?></small></label>
					<input type="number" min="1" value="<?php echo esc_attr($donation_round_multiplier); ?>" name="wc-donation-round-multiplier" id="wc-donation-round-multiplier" <?php echo ( 'yes' === $donation_on_round ) ? esc_attr( 'required' ) : ''; ?> />
				</div>

				<!-- <div class="select-wrapper">
					<label for=""><?php echo esc_attr__( 'Popup title', 'wc-donation' ); ?></label>
					<input type="text" value="<?php echo esc_attr($donation_label); ?>" name="wc-donation-round-field-label" id="wc-donation-round-field-label" />
				</div> -->				

			</div>

			<div class="wc-block-setting">

				<div class="select-wrapper">
					<label for=""><?php echo esc_attr__( 'Popup Message (use %amount% to show dynamic donation value in message)', 'wc-donation' ); ?></label>
					<textarea name="wc-donation-round-field-message" id="wc-donation-round-field-message" cols="30" rows="5" style="resize:none"><?php echo esc_attr($donation_message); ?></textarea>
				</div>

				<div class="select-wrapper">
					<label for=""><?php echo esc_attr__( 'Cancel Button Text', 'wc-donation' ); ?></label>
					<input type="text" value="<?php echo esc_attr($donation_button_cancel_text); ?>" name="wc-donation-round-button-cancel-text" id="wc-donation-round-button-cancel-text" />
				</div>

			</div>

		</div>

		<div class="wc-block-setting wc-grid-full">

			<div class="wc-block-setting">
				<h3><?php echo esc_html__('Credit Card Processing Fees', 'wc-donation'); ?></h3>
				<?php 
				if ( 'yes' === $donation_card_fee ) {
					$checked_for_fee = 'checked';
				} else {
					$checked_for_fee = '';
				}
				
				?>
				<div class="select-wrapper">
					<label class="wc-donation-switch">
						<input id="wc-donation-card-fee" name="wc-donation-card-fee" type="checkbox" value="yes" <?php echo esc_html( $checked_for_fee ); ?> onchange="document.getElementById('wc-donation-fees-percent').required=this.checked">
						<span class="wc-slider round"></span>
					</label>
					<label for="wc-donation-card-fee" class="wc-text-label"><?php echo esc_attr( __( 'Credit Card Processing Fees', 'wc-donation' ) ); ?></label>
				</div>

				<div class="select-wrapper">
					<label><?php echo esc_attr( __( 'Select Campaigns', 'wc-donation' ) ); ?></label>
					<?php //wp_enqueue_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css' ); ?>
					<?php //wp_enqueue_style('woocommerce_admin_styles'); ?>
					<?php wp_enqueue_script('selectWoo'); ?>
					<select class='select short fee_campaign' name='wc-donation-fees-product[]' multiple>
						
						<?php
						foreach ( $campaigns as $campaign ) {
							$get_fee_campaign = get_option('wc-donation-fees-product');
							if ( !is_array( $get_fee_campaign ) ) {
								$get_fee_campaign = array();
							}
							?>
							<option value="<?php echo esc_attr($campaign); ?>" <?php selected( in_array( $campaign, $get_fee_campaign ) ); ?>><?php echo esc_attr( get_the_title($campaign) ); ?></option>
							<?php	                      
						}
						?>
					</select>
				</div>

				<div class="select-wrapper">
					<label for=""><?php echo esc_attr__( 'Select Fees Type', 'wc-donation' ); ?></label>
					<select name="wc-donation-fees-type" id="wc-donation-fees-type">
						<option value="percentage" <?php selected($donation_fees_type, 'percentage', true); ?>><?php echo esc_html__('Percentage', 'wc-donation'); ?></option>
						<option value="fixed" <?php selected($donation_fees_type, 'fixed', true); ?>><?php echo esc_html__('Fixed', 'wc-donation'); ?></option>
					</select>
				</div>

				<div class="select-wrapper">
					<label for=""><?php echo esc_attr__( 'Enter Fees Amount', 'wc-donation' ); ?><br><small style="color:#777"><?php echo esc_attr__( '(Enter processing fees amount to be charged)', 'wc-donation' ); ?></small></label>
					<input type="number" min="0.1" step="any" value="<?php echo esc_attr($donation_fees_percent); ?>" <?php echo ( 'yes' === $donation_card_fee ) ? esc_attr( 'required' ) : ''; ?> name="wc-donation-fees-percent" id="wc-donation-fees-percent" />
				</div>

			</div>

			<div class="wc-block-setting">

				<div class="select-wrapper">
					<label for=""><?php echo esc_attr__( 'Enter Processing Fees Text', 'wc-donation' ); ?></label>
					<textarea name="wc-donation-fees-field-message" id="wc-donation-fees-field-message" cols="30" rows="5" style="resize:none"><?php echo esc_attr($donation_fees_message); ?></textarea>
				</div>
			</div>
		</div>
		
		<!-- new settings for tributes PDF Receipt & Gift Aid -->
		<div class="wc-block-setting wc-grid-full">
			<div class="wc-block-setting">
				<h3><?php echo esc_html__('Gift Aid UK', 'wc-donation'); ?></h3>
				<?php

				$checked_gift_aid_area_cart = '';
				$checked_gift_aid_area_checkout = '';
				$checked_gift_aid_area_widget = '';
				$checked_gift_aid_area_single = '';

				if ( is_array( $donation_gift_aid_area ) ) {

					if ( in_array('cart', $donation_gift_aid_area) ) {
						$checked_gift_aid_area_cart = 'checked';
					} else {
						$checked_gift_aid_area_cart = '';
					}

					if ( in_array('checkout', $donation_gift_aid_area) ) {
						$checked_gift_aid_area_checkout = 'checked';
					} else {
						$checked_gift_aid_area_checkout = '';
					}

					if ( in_array('widget', $donation_gift_aid_area) ) {
						$checked_gift_aid_area_widget = 'checked';
					} else {
						$checked_gift_aid_area_widget = '';
					}

					if ( in_array('single', $donation_gift_aid_area) ) {
						$checked_gift_aid_area_single = 'checked';
					} else {
						$checked_gift_aid_area_single = '';
					}
				} else {
					if ( 'cart' === $donation_gift_aid_area ) {
						$checked_gift_aid_area_cart = 'checked';
					} else {
						$checked_gift_aid_area_cart = '';
					}

					if ( 'checkout' === $donation_gift_aid_area ) {
						$checked_gift_aid_area_checkout = 'checked';
					} else {
						$checked_gift_aid_area_checkout = '';
					}
				}

				if ( 'yes' === $donation_gift_aid ) {
					$checked_gift_aid = 'checked';
				} else {
					$checked_gift_aid = '';
				}
				
				?>

				<div class="select-wrapper">
					<label class="wc-donation-switch">
						<input id="wc-donation-gift-aid" name="wc-donation-gift-aid" type="checkbox" value="yes" <?php echo esc_html__( $checked_gift_aid ); ?>>
						<span class="wc-slider round"></span>
					</label>
					<label class="wc-text-label"><?php echo esc_html__( 'Gift Aid UK', 'wc-donation' ); ?></label>
				</div>

				<div class="select-wrapper">
					
					<div class="mb-10">
						<label for="wc-donation-gift-aid-area-cart" style="margin-left:0;" class="wc-donation-switch">
							<input id="wc-donation-gift-aid-area-cart" name="wc-donation-gift-aid-area[]" type="checkbox" value="cart" <?php echo esc_html__( $checked_gift_aid_area_cart ); ?>>
							<span class="wc-slider round"></span>						
						</label>
						<label class="wc-text-label"><?php echo esc_html__( 'Visible on Cart', 'wc-donation' ); ?></label>
					</div>

					<div class="mb-10">
						<label for="wc-donation-gift-aid-area-checkout" class="wc-donation-switch">
							<input id="wc-donation-gift-aid-area-checkout" name="wc-donation-gift-aid-area[]" type="checkbox" value="checkout" <?php echo esc_html__( $checked_gift_aid_area_checkout ); ?>>
							<span class="wc-slider round"></span>						
						</label>
						<label class="wc-text-label"><?php echo esc_html__( 'Visible on Checkout', 'wc-donation' ); ?></label>
					</div>

					<div class="mb-10">
						<label for="wc-donation-gift-aid-area-widget" class="wc-donation-switch">
							<input id="wc-donation-gift-aid-area-widget" name="wc-donation-gift-aid-area[]" type="checkbox" value="widget" <?php echo esc_html__( $checked_gift_aid_area_widget ); ?>>
							<span class="wc-slider round"></span>						
						</label>
						<label class="wc-text-label"><?php echo esc_html__( 'Visible on Widget', 'wc-donation' ); ?></label>
					</div>

					<div class="mb-10">
						<label for="wc-donation-gift-aid-area-single" class="wc-donation-switch">
							<input id="wc-donation-gift-aid-area-single" name="wc-donation-gift-aid-area[]" type="checkbox" value="single" <?php echo esc_html__( $checked_gift_aid_area_single ); ?>> 
							<span class="wc-slider round"></span>
						</label>
						<label class="wc-text-label"><?php echo esc_html__( 'Visible on single Page', 'wc-donation' ); ?></label>
					</div>

					<div class="clearfix">&nbsp;</div>
				</div>

				<div class="select-wrapper">
					<label for=""><?php echo esc_attr__( 'Gift Aid Title', 'wc-donation' ); ?></label>
					<input type="text" value="<?php echo esc_attr($donation_gift_aid_title); ?>" name="wc-donation-gift-aid-title" id="wc-donation-gift-aid-title" />
				</div>

				<div class="select-wrapper">
					<label for=""><?php echo esc_attr__( 'Gift Aid Checkbox Text', 'wc-donation' ); ?></label>
					<input type="text" value="<?php echo esc_attr($donation_gift_aid_checkbox_title); ?>" name="wc-donation-gift-aid-checkbox-title" id="wc-donation-gift-aid-title" />
				</div>

			</div>

			<div class="wc-block-setting">

				<div class="select-wrapper">
					<label for=""><?php echo esc_attr__( 'Gift Aid Explanation', 'wc-donation' ); ?></label>
					<textarea name="wc-donation-gift-aid-explanation" id="wc-donation-gift-aid-explanation" cols="30" rows="6" style="resize:none"><?php echo esc_attr($donation_gift_aid_explanation); ?></textarea>
				</div>

				<div class="select-wrapper">
					<label for=""><?php echo esc_attr__( 'Gift Aid Declaration Message', 'wc-donation' ); ?></label>
					<textarea name="wc-donation-gift-aid-declaration" id="wc-donation-gift-aid-declaration" cols="30" rows="6" style="resize:none"><?php echo esc_attr($donation_gift_aid_declaration); ?></textarea>
				</div>
				
			</div>
		</div>

		<div class="wc-block-setting wc-grid-full">
			<div class="wc-block-setting">
				<h3><?php echo esc_html__('Donation on Products', 'wc-donation'); ?></h3>

				<!-- Enable Product Donation -->
				<div class="select-wrapper">
					<?php
					$settings = get_option('wc_donation_settings', array());
					$enable_donation = get_option('wc_donation_enable_option', 'disable');
					?>
					<label class="wc-donation-switch">
						<input id="enable-product-donation" name="wc_donation_enable_option" type="checkbox" value="enable" <?php echo 'enable' === $enable_donation ? 'checked' : ''; ?>>
						<span class="wc-slider round"></span>
					</label>
					<label for="enable-product-donation" class="wc-text-label"><?php echo esc_html__('Enable Product Donation', 'wc-donation'); ?></label>
				</div>
				<!-- Select Campaign -->
				<div class="select-wrapper" style="width: 35%;">
					<label class="wc-donation-label" for="select-campaign"><?php echo esc_html__('Select Campaign', 'wc-donation'); ?></label>
					<select id="select-campaign" name="wc_donation_settings[campaign_ids][]" class="select short wc-donation-select">
						<?php
						$selected_campaign = isset($settings['campaign_id']) ? $settings['campaign_id'] : '';
						$args = array(
							'post_type'      => 'product',
							'posts_per_page' => -1,
							'post_status'    => 'publish',
							'meta_query'     => array(
								array(
									'key'     => 'is_wc_donation',
									'compare' => 'EXISTS',
								),
							),
						);
						$products = get_posts($args);

						foreach ($products as $product) {
							$product_obj = wc_get_product($product->ID);
							?>
							<option value="<?php echo esc_attr($product->ID); ?>" <?php selected($product_obj->get_id(), $selected_campaign); ?>>
								<?php echo esc_html($product_obj->get_name()); ?>
							</option>
						<?php } ?>
					</select>
				</div>

				<!-- Enter Donation Amount -->
				<div class="select-wrapper" style="width: 35%;">
					<label class="wc-donation-label" for="donation-amount"><?php echo esc_html__('Enter Donation Amount', 'wc-donation'); ?></label>
					<input type="number" id="donation-amount" name="wc_donation_settings[donation_amount]" value="<?php echo esc_attr(isset($settings['donation_amount']) ? $settings['donation_amount'] : ''); ?>" placeholder="<?php esc_attr_e('Enter percentage', 'wc-donation'); ?>">
				</div>

				<!-- Select Products -->
				<div class="select-wrapper" style="width: 35%;">
					<label class="wc-donation-label" for="select-products"><?php echo esc_html__('Select Products', 'wc-donation'); ?></label>
					<select id="select-products" name="wc_donation_settings[product_ids][]" multiple class="select short wc-donation-select">
						<?php
						$selected_products = isset($settings['product_ids']) ? (array) $settings['product_ids'] : array();
						$args = array(
							'post_type'      => 'product',
							'posts_per_page' => -1,
							'post_status'    => 'publish',
							'meta_query'     => array(
								array(
									'key'     => 'is_wc_donation',
									'compare' => 'NOT EXISTS',
								),
							),
						);
						$products = get_posts($args);

						foreach ($products as $product) {
							$product_obj = wc_get_product($product->ID);
							?>
							<option value="<?php echo esc_attr($product_obj->get_id()); ?>" <?php selected(in_array($product_obj->get_id(), $selected_products)); ?>>
								<?php echo esc_html($product_obj->get_name()); ?>
							</option>
						<?php } ?>
					</select>
				</div>

				<!-- Save and Cancel -->
				<div>
					<button type="button" id="save-donation-settings" class="button button-primary"><?php esc_html_e('Save', 'wc-donation'); ?></button>
					<button type="button" id="cancel-donation-settings" class="button"><?php esc_html_e('Cancel', 'wc-donation'); ?></button>
				</div>

				<!-- Donation Campaign Table -->
				<div class="wc-donation-table">
					<h3><?php esc_html_e('Donation Campaigns', 'wc-donation'); ?></h3>
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th><?php esc_html_e('Select Campaign', 'wc-donation'); ?></th>
								<th><?php esc_html_e('Donation Amount', 'wc-donation'); ?></th>
								<th><?php esc_html_e('Select Products', 'wc-donation'); ?></th>
								<th><?php esc_html_e('Actions', 'wc-donation'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php 
							$campaign_ids = isset($settings['campaign_ids']) ? (array) $settings['campaign_ids'] : array();
							if (!empty($campaign_ids)) : 
								?>
								<?php foreach ($campaign_ids as $campaign_id => $data) : ?>
									<tr id="campaign-row-<?php echo esc_attr($campaign_id); ?>">
										<td><?php echo esc_html(get_the_title($campaign_id)); ?></td>
										<td><?php echo esc_html($data['donation_amount']); ?>%</td>
										<td>
											<?php
											$product_names = array();
											if (!empty($data['product_ids'])) {
												foreach ((array) $data['product_ids'] as $product_id) {
													$product_obj = wc_get_product($product_id);
													if ($product_obj) {
														$product_names[] = $product_obj->get_name();
													}
												}
											}
											echo esc_html(implode(', ', $product_names));
											?>
										</td>
										<td>
											<button type="button" class="button edit-campaign" data-campaign-id="<?php echo esc_attr($campaign_id); ?>"><?php esc_html_e('Edit', 'wc-donation'); ?></button>
											<button type="button" class="button delete-campaign" data-campaign-id="<?php echo esc_attr($campaign_id); ?>"><?php esc_html_e('Delete', 'wc-donation'); ?></button>
										</td>
									</tr>
								<?php endforeach; ?>
							<?php else : ?>
								<tr>
									<td colspan="4"><?php esc_html_e('No campaigns found.', 'wc-donation'); ?></td>
								</tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
			<div class="wc-block-setting">
				<?php
				$checked_donation_on_product_cart = '';
				$checked_donation_on_product_checkout = '';
				$checked_donation_on_product_single = '';

				$donation_on_product = get_option('wc-donation-on-product', array() ); // Retrieve saved settings.

				if (is_array($donation_on_product)) {
					$checked_donation_on_product_cart = in_array('cart', $donation_on_product) ? 'checked' : '';
					$checked_donation_on_product_checkout = in_array('checkout', $donation_on_product) ? 'checked' : '';
					$checked_donation_on_product_single = in_array('single', $donation_on_product) ? 'checked' : '';
				} else {
					$checked_donation_on_product_cart = ( 'cart' === $donation_on_product ) ? 'checked' : '';
					$checked_donation_on_product_checkout = ( 'checkout' === $donation_on_product ) ? 'checked' : '';
				}
				?>
				<div class="mb-10">
					<label for="wc-donation-on-product-cart" class="wc-donation-switch">
						<input id="wc-donation-on-product-cart" name="wc-donation-on-product[]" type="checkbox" value="cart" <?php echo esc_attr( $checked_donation_on_product_cart ); ?>>
						<span class="wc-slider round"></span>
					</label>
					<label class="wc-text-label"><?php echo esc_html__('Visible on Cart', 'wc-donation'); ?></label>
				</div>
				<div class="mb-10">
					<label for="wc-donation-on-product-checkout" class="wc-donation-switch">
						<input id="wc-donation-on-product-checkout" name="wc-donation-on-product[]" type="checkbox" value="checkout" <?php echo esc_attr( $checked_donation_on_product_checkout ); ?>>
						<span class="wc-slider round"></span>
					</label>
					<label class="wc-text-label"><?php echo esc_html__('Visible on Checkout', 'wc-donation'); ?></label>
				</div>
				<div class="mb-10">
					<label for="wc-donation-on-product-single" class="wc-donation-switch">
						<input id="wc-donation-on-product-single" name="wc-donation-on-product[]" type="checkbox" value="single" <?php echo esc_attr( $checked_donation_on_product_single ); ?>>
						<span class="wc-slider round"></span>
					</label>
					<label class="wc-text-label"><?php echo esc_html__('Visible on Single Product Page', 'wc-donation'); ?></label>
				</div>
			</div>
		</div>


		<div class="wc-block-setting wc-grid-full">

			<div class="wc-block-setting">
				<h3><?php echo esc_html__('PDF Receipt', 'wc-donation'); ?></h3>
				<?php 
				if ( 'yes' === $donation_pdf ) {
					$checked_for_pdf = 'checked';
				} else {
					$checked_for_pdf = '';
				}
				
				?>
				<div class="select-wrapper">
					<label class="wc-donation-switch">
						<input id="wc-donation-pdf-receipt" name="wc-donation-pdf-receipt" type="checkbox" value="yes" <?php echo esc_html__( $checked_for_pdf ); ?>>
						<span class="wc-slider round"></span>
					</label>
					<label for="wc-donation-pdf-receipt" class="wc-text-label"><?php echo esc_html__( 'Enable to send PDF Receipt with Email', 'wc-donation' ); ?></label>
				</div>
			</div>

			<div class="wc-block-setting">
				<h3><?php echo esc_html__('Tributes', 'wc-donation'); ?></h3>
				<?php 
				if ( 'yes' === $donation_tributes ) {
					$checked_for_tributes = 'checked';
				} else {
					$checked_for_tributes = '';
				}
				
				?>
				<div class="select-wrapper">
					<label class="wc-donation-switch">
						<input id="wc-donation-tributes" name="wc-donation-tributes" type="checkbox" value="yes" <?php echo esc_html__( $checked_for_tributes ); ?>>
						<span class="wc-slider round"></span>
					</label>
					<label for="wc-donation-tributes" class="wc-text-label"><?php echo esc_html__( 'Enable Tributes', 'wc-donation' ); ?></label>
				</div>

				
				<div class="select-wrapper">
					<h3><?php echo esc_html__('Messages', 'wc-donation'); ?></h3>
					<label class="wc-donation-switch">
						<input id="wc-donation-messages" name="wc-donation-messages" type="checkbox" value="yes" <?php checked( $tributes_messages, 'yes', true ); ?>>
						<span class="wc-slider round"></span>
					</label>
					<label for="wc-donation-messages" class="wc-text-label"><?php echo esc_html__( 'Enable Messages', 'wc-donation' ); ?></label>
				</div>

				<div class="select-wrapper" data-parent="wc-donation-donor-wall">
					<h3><?php echo esc_html__('Donor Wall', 'wc-donation'); ?></h3>
					<label class="wc-donation-switch">
						<input id="wc-donation-donor-wall" name="wc-donation-donor-wall" type="checkbox" value="yes" <?php checked( $donor_wall, 'yes', true ); ?>>
						<span class="wc-slider round"></span>
					</label>
					<label for="wc-donation-donor-wall" class="wc-text-label"><?php echo esc_html__( 'Enable Donor Wall', 'wc-donation' ); ?></label>
				</div>

				<div class="select-wrapper" data-child="wc-donation-donor-wall" data-show="yes" style="display:none;">
					<p style="display: flex; align-items: center;"><textarea readonly id="copy1">[wc_woo_global_donor_wall]</textarea> <a href="javascript:void(0);" style="text-decoration: none;" onclick="copyToClip('copy1')"><span class="dashicons dashicons-admin-page"></span></a></p>
					<p style="display: flex; align-items: center;"><textarea readonly id="copy2">[wc_woo_leaderboard_donor_wall]</textarea> <a href="javascript:void(0);" style="text-decoration: none;" onclick="copyToClip('copy2')"><span class="dashicons dashicons-admin-page"></span></a></p>
				</div>

			</div>

			<div class="wc-block-setting">
				<h3><?php echo esc_html__('WC Donation API', 'wc-donation'); ?></h3>
				<?php 
				if ( 'yes' === $donation_api ) {
					$checked_for_api = 'checked';
				} else {
					$checked_for_api = '';
				}
				
				?>
				<div class="select-wrapper">
					<label class="wc-donation-switch">
						<input id="wc-donation-api" name="wc-donation-api" type="checkbox" value="yes" <?php echo esc_html__( $checked_for_api ); ?>>
						<span class="wc-slider round"></span>
					</label>
					<label for="wc-donation-api" class="wc-text-label"><?php echo esc_html__( 'Enable API', 'wc-donation' ); ?></label>
				</div>
			</div>

			<div class="wc-block-setting">
				<h3><?php echo esc_html__('Synchronize Campaign Data', 'wc-donation'); ?></h3>
				<div class="select-wrapper">
					<a href="#" id="wc_donation_sync_data" class="button button-primary"><?php echo esc_html__('Synchronize', 'wc-donation'); ?></a>
					<small style="color: #777;display:block;margin-top:5px"><?php echo esc_html__( 'By clicking this button, your donation order data will be properly synced. Use this, if you are finding inacurrate campaign data.', 'wc-donation' ); ?></small>
				</div>
				<div id="wc-donation-sync-result"></div>				
			</div>

		</div>

	</div> <!--end of wrapper-->

	<?php submit_button(); ?>

</form>
