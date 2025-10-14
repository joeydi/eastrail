<?php
/**
 * Donation on Product Setting HTML
 */
?>
<div class="wc-donation-settings">

	<!-- Enable Product Donation -->
	<div class="select-wrapper">
		<label class="wc-donation-label"><?php echo esc_attr( __( 'Enable Product Donation', 'wc-donation' ) ); ?></label>
		<?php
		$enable_donation = !empty( get_option( 'wc_donation_enable_option', 'disable' ) ) ? get_option( 'wc_donation_enable_option', 'disable' ) : 'disable';
		$options = array( 'enable' => __( 'Enable', 'wc-donation' ), 'disable' => __( 'Disable', 'wc-donation' ) );
		foreach ( $options as $key => $value ) {
			$checked = ( $enable_donation === $key ) ? 'checked' : '';
			?>
			<input class="inp-cbx" style="display: none" type="radio" id="enable-product-donation-<?php esc_attr_e( $key ); ?>" name="wc_donation_enable_option" value="<?php esc_attr_e( $key ); ?>" <?php esc_attr_e( $checked ); ?>>
			<label class="cbx" for="enable-product-donation-<?php esc_attr_e( $key ); ?>">
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
			<small class="wc-donation-tooltip"><?php esc_html_e( 'Enable to add donation on products', 'wc-donation' ); ?></small>
		</div>
	</div>

	<!-- Enter Donation Amount -->
	<div class="select-wrapper">
		<label class="wc-donation-label" for="donation-amount"><?php echo esc_html__( 'Enter Donation Amount', 'wc-donation' ); ?></label>
		<input type="text" id="donation-amount" name="wc_donation_amount" value="<?php echo esc_attr( get_option( 'wc_donation_amount', '' ) ); ?>" placeholder="<?php esc_attr_e( 'Enter the percentage', 'wc-donation' ); ?>">
		<div class="wc-donation-tooltip-box">
			<small class="wc-donation-tooltip"><?php esc_html_e( 'The percentage you enter will be treated as a donation on the products customers purchase.', 'wc-donation' ); ?></small>
		</div>
	</div>

	<!-- Apply on All Products -->
	<div class="select-wrapper">
		<label class="wc-donation-label" for="apply-all-products"><?php echo esc_html__( 'Apply on All Products', 'wc-donation' ); ?></label>
		<input type="checkbox" class="inp-cbx" id="apply-all-products" name="wc_donation_apply_all" value="1" style="display: none" <?php checked( get_option( 'wc_donation_apply_all', '' ), 1 ); ?>>
		<label class="cbx circle-checkbox" for="apply-all-products">
			<span>
				<svg width="12px" height="9px" viewbox="0 0 12 9">
					<polyline points="1 5 4 8 11 1"></polyline>
				</svg>
			</span>
		</label>
		<div class="wc-donation-tooltip-box">
			<small class="wc-donation-tooltip"><?php esc_html_e( 'Check this if you want to apply on all products.', 'wc-donation' ); ?></small>
		</div>
	</div>

	<!-- Select Products -->
	<div class="select-wrapper">
		<label class="wc-donation-label" for="select-products"><?php echo esc_html__( 'Select Products', 'wc-donation' ); ?></label>
		<select id="select-products" name="wc_donation_products[]" multiple class="wc-donation-select">
			<?php
			$selected_products = get_option( 'wc_donation_products', array() );

			// Query for products without the 'is_wc_donation' meta key
			$args = array(
				'post_type'      => 'product',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'meta_query'     => array(
					array(
						'key'     => 'is_wc_donation',
						'compare' => 'NOT EXISTS', // Exclude products with this meta key
					),
				),
			);

			$products = get_posts( $args );

			foreach ( $products as $product ) {
				$product_obj = wc_get_product( $product->ID );
				?>
				<option value="<?php echo esc_attr( $product_obj->get_id() ); ?>" <?php selected( in_array( $product_obj->get_id(), $selected_products ) ); ?>>
					<?php echo esc_html( $product_obj->get_name() ); ?>
				</option>
				<?php
			}
			?>
		</select>
	</div>

	<!-- Select Categories -->
	<div class="select-wrapper">
		<label class="wc-donation-label" for="select-categories"><?php echo esc_html__( 'Select Categories', 'wc-donation' ); ?></label>
		<select id="select-categories" name="wc_donation_categories[]" multiple class="wc-donation-select">
			<?php
			$selected_categories = get_option( 'wc_donation_categories', array() );
			$categories = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false ) );
			foreach ( $categories as $category ) {
				?>
				<option value="<?php echo esc_attr( $category->term_id ); ?>" <?php selected( in_array( $category->term_id, $selected_categories ) ); ?>>
					<?php echo esc_html( $category->name ); ?>
				</option>
				<?php
			}
			?>
		</select>
	</div>

</div>