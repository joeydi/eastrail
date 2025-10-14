<?php

/**
 *  Class   WcdonationCategory.
 * 
 */
class WcdonationCategory {

	public function __construct() {
		add_action( 'init', array( $this, 'register_wc_donation_taxonomy' ) );
		add_action( 'wc_donation_categories_add_form_fields', array( $this, 'add_category_image_field' ), 10, 2 );
		add_action( 'wc_donation_categories_edit_form_fields', array( $this, 'edit_category_image_field' ), 10, 2 );
		add_action( 'created_wc_donation_categories', array( $this, 'save_category_image_field' ), 10, 2 );
		add_action( 'edited_wc_donation_categories', array( $this, 'save_category_image_field' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_media' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter( 'manage_edit-wc_donation_categories_columns', array( $this, 'add_image_column' ) );
		add_filter( 'manage_wc_donation_categories_custom_column', array( $this, 'populate_image_column' ), 10, 3 );
	}

	public function register_wc_donation_taxonomy() {
		$labels = array(
			'name'                       => _x( 'Campaign Categories', 'taxonomy general name', 'wc-donation' ),
			'singular_name'              => _x( 'Campaign Category', 'taxonomy singular name', 'wc-donation' ),
			'search_items'               => __( 'Search Campaign Categories', 'wc-donation' ),
			'all_items'                  => __( 'All Campaign Categories', 'wc-donation' ),
			'parent_item'                => __( 'Parent Campaign Category', 'wc-donation' ),
			'parent_item_colon'          => __( 'Parent Campaign Category:', 'wc-donation' ),
			'edit_item'                  => __( 'Edit Campaign Category', 'wc-donation' ),
			'update_item'                => __( 'Update Campaign Category', 'wc-donation' ),
			'add_new_item'               => __( 'Add New Campaign Category', 'wc-donation' ),
			'new_item_name'              => __( 'New Campaign Category Name', 'wc-donation' ),
			'menu_name'                  => __( 'Campaign Categories', 'wc-donation' ),
		);

		$args = array(
			'hierarchical'               => true,
			'labels'                     => $labels,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'query_var'                  => true,
			'rewrite'                    => array( 'slug' => 'donation-category' ),
			'show_in_rest'               => true,
			'default_term'               => array(
				'name' => 'Uncategorized',
				'slug' => 'uncategorized',
				'description' => 'Default category for uncategorized donation campaigns',
			),
		);

		register_taxonomy( 'wc_donation_categories', array( 'wc-donation' ), $args );
	}

	public function add_category_image_field( $taxonomy ) {
		?>
		<div class="form-field term-group">
			<label for="category-image-id"><?php esc_html_e( 'Image', 'wc-donation'); ?></label>
			<?php wp_nonce_field( 'save_category_image', 'category_image_nonce' ); ?>
			<input type="hidden" id="category-image-id" name="category-image-id" value="">
			<div id="category-image-wrapper"></div>
			<p>
				<input type="button" class="button button-secondary" id="upload-category-image" value="<?php esc_html_e('Add Image', 'wc-donation'); ?>" />
				<input type="button" class="button button-secondary" id="remove-category-image" value="<?php esc_html_e('Remove Image', 'wc-donation'); ?>" />
			</p>
		</div>
		<?php
	}

	public function edit_category_image_field( $term, $taxonomy ) {
		$image_id = get_term_meta( $term->term_id, 'category-image-id', true );
		?>
		<tr class="form-field term-group-wrap">
			<th scope="row">
				<label for="category-image-id"><?php esc_html_e('Image', 'wc-donation'); ?></label>
			</th>
			<td>
				<?php wp_nonce_field( 'save_category_image', 'category_image_nonce' ); ?>
				<input type="hidden" id="category-image-id" name="category-image-id" value="<?php echo esc_attr( $image_id ); ?>">
				<div id="category-image-wrapper">
					<?php if ( $image_id ) { ?>
						<?php echo wp_get_attachment_image( $image_id, 'thumbnail' ); ?>
					<?php } ?>
				</div>
				<p>
					<input type="button" class="button button-secondary" id="upload-category-image" value="<?php esc_html_e( 'Add Image', 'wc-donation' ); ?>" />
					<input type="button" class="button button-secondary" id="remove-category-image" value="<?php esc_html_e( 'Remove Image', 'wc-donation' ); ?>" />
				</p>
			</td>
		</tr>
		<?php
	}


	public function save_category_image_field( $term_id, $tt_id ) {
		// Check if the nonce is set.
		if ( isset( $_POST['category_image_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['category_image_nonce'] ), 'save_category_image' ) ) {
			// Sanitize and update or delete the term meta.
			if ( isset( $_POST['category-image-id'] ) && '' !== sanitize_text_field( $_POST['category-image-id'] ) ) {
				$image = sanitize_text_field( $_POST['category-image-id'] );
				update_term_meta( $term_id, 'category-image-id', $image );
			} else {
				delete_term_meta( $term_id, 'category-image-id' );
			}
		}
	}


	public function load_media() {
		wp_enqueue_media();
	}

	public function enqueue_scripts() {
		wp_enqueue_script( 'admin-category-image', WC_DONATION_URL . 'assets/js/category-image.js', array( 'jquery' ), null, true );
	}

	public function add_image_column( $columns ) {
		$new_columns = array();
		foreach ( $columns as $key => $value ) {
			if ( 'name' == $key ) {
				$new_columns['image'] = __( 'Image', 'wc-donation' );
			}
			$new_columns[$key] = $value;
		}
		return $new_columns;
	}

	public function populate_image_column( $content, $column_name, $term_id ) {
		if ( 'image' === $column_name ) {
			$image_id = get_term_meta( $term_id, 'category-image-id', true );
			if ( $image_id ) {
				$image = wp_get_attachment_image( $image_id, 'thumbnail', false, array( 'style' => 'width:50px;height:auto;' ) );
				$content = $image;
			} else {
				$content = __( 'No Image', 'wc-donation' );
			}
		}
		return $content;
	}
}

new WcdonationCategory();
