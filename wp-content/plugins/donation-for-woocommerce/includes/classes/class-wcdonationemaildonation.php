<?php
/**
 * Plugin Name: Donation Emails Post Type
 * Description: Custom Post Type for managing Donation Emails with meta boxes for campaign settings and a taxonomy for categorization.
 */

class WcdonationEmail {
	public function __construct() {
		add_action( 'init', array( $this, 'wc_donation_email_posttype' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_custom_meta_boxes' ) );
		add_filter( 'manage_wc-donation-email_posts_columns', array( $this, 'email_modify_column_names' ) );
		add_action( 'manage_wc-donation-email_posts_custom_column', array( $this, 'email_custom_column_content' ), 10, 2 );
		add_filter( 'mce_buttons', array( $this, 'wcf_filter_mce_button' ) );
		add_filter( 'mce_external_plugins', array( $this, 'wcf_filter_mce_plugin' ), 9 );
		add_action('admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ));
		add_action('save_post_wc-donation-email', array( $this, 'save_post_meta' ));
	}
	// Save post meta data when the post is saved
	public function save_post_meta( $post_id ) {
		// Verify nonce for each meta box
		if (!isset($_POST['campaign_nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['campaign_nonce']), 'save_campaign')) {
			return;
		}
		if (!isset($_POST['actions_nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['actions_nonce']), 'save_actions')) {
			return;
		}
		if (!isset($_POST['templates_nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['templates_nonce']), 'save_templates')) {
			return;
		}
		if (!isset($_POST['awareness_email_nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['awareness_email_nonce']), 'save_awareness_email')) {
			return;
		}
		if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['nonce']), 'email-nonce')) {
			return;
		}

		// Prevent auto-saves and bulk edits
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
		if (isset($_POST['post_type']) && 'wc-donation-email' !== $_POST['post_type']) {
			return;
		}

		// Save campaigns
		if (isset($_POST['campaign_ids']) && is_array($_POST['campaign_ids'])) {
			update_post_meta($post_id, 'campaign_ids', array_map('sanitize_text_field', $_POST['campaign_ids']));
		} else {
			delete_post_meta($post_id, 'campaign_ids');
		}

		// Save actions
		if (isset($_POST['actions'])) {
			update_post_meta($post_id, '_donation_action', sanitize_text_field($_POST['actions']));
		} else {
			delete_post_meta($post_id, '_donation_action');
		}

		// Save email templates toggle
		$email_templates_enabled = isset($_POST['email_templates_enabled']) && 'yes' === $_POST['email_templates_enabled'] ? 'yes' : 'no';
		update_post_meta($post_id, '_email_templates_enabled', $email_templates_enabled);

		// Save awareness email settings
		$awareness_email_enabled = isset($_POST['awareness_email_enabled']) && 'yes' === $_POST['awareness_email_enabled'] ? 'yes' : 'no';
		update_post_meta($post_id, '_awareness_email_enabled', $awareness_email_enabled);

		if (isset($_POST['awareness_email_interval']) && is_numeric($_POST['awareness_email_interval'])) {
			update_post_meta($post_id, '_awareness_email_interval', intval($_POST['awareness_email_interval']));
		} else {
			delete_post_meta($post_id, '_awareness_email_interval');
		}

		if (isset($_POST['awareness_email_unit'])) {
			update_post_meta($post_id, '_awareness_email_unit', sanitize_text_field($_POST['awareness_email_unit']));
		} else {
			delete_post_meta($post_id, '_awareness_email_unit');
		}

		// Save donation email settings
		if (isset($_POST['donation_email_subject'])) {
			update_post_meta($post_id, 'donation_email_subject', sanitize_text_field($_POST['donation_email_subject']));
		} else {
			delete_post_meta($post_id, 'donation_email_subject');
		}

		if (isset($_POST['donation_email_editor'])) {
			update_post_meta($post_id, 'donation_email_editor', wp_kses_post($_POST['donation_email_editor']));
		} else {
			delete_post_meta($post_id, 'donation_email_editor');
		}
	}


	public function wcf_filter_mce_button( $buttons ) {
		array_push( $buttons, 'wc_donation_email_editor' );
		return $buttons;
	}

	public function wcf_filter_mce_plugin( $plugins ) {
		global $post;

		if (
			( isset($post) && 'wc-donation-email' === get_post_type($post) ) || 
			( isset($_REQUEST['post_type']) && 'wc-donation-email' === sanitize_text_field($_REQUEST['post_type']) )
		) {
			$plugins['wc_donation_email_editor'] = WC_DONATION_URL . 'assets/js/mce_editor.js';
		}

		return $plugins;
	}

	public function enqueue_admin_scripts( $hook ) {
		global $post;

		// Check if it's the post edit page (post.php) or the list page (edit.php) for 'wc-donation-email' custom post type
		if (
			( 'post.php' === $hook || 'edit.php' === $hook ) || 
			( isset($post) && 
			'wc-donation-email' === get_post_type($post) )
		) {
			wp_enqueue_script('selectWoo');
			wp_enqueue_style('select2');

			// Enqueue mce_editor.js script
			wp_enqueue_script(
				'wc-donation-email-editor',
				WC_DONATION_URL . 'assets/js/mce_editor.js',
				array( 'jquery' ),
				null,
				true
			);

			// Enqueue donation-email.js script
			wp_enqueue_script(
				'wc-donation-email-script',
				WC_DONATION_URL . 'assets/js/donation-email.js',
				array( 'jquery' ),
				null,
				true
			);

			// Pass data to donation-email.js
			wp_localize_script('wc-donation-email-script', 'donationEmailVars', array(
				'ajax_url'   => admin_url('admin-ajax.php'),
				'nonce'      => wp_create_nonce('donation_email_action'),
			));

			wp_localize_script('wc-donation-email-editor', 'dfw_mce', array(
				'order_id'                  => 'Order ID',
				'customer_name'             => 'Customer Name',
				'campaign_name'             => 'Campaign Name',
				'donated_amount'            => 'Donated Amount',
				'donation_goal'             => 'Donation Goal',
				'tribute'                   => 'Tribute',
				'cause'                     => 'Cause',
				'gift_aid'                  => 'Gift Aid',
				'donation_type'             => 'Donation Type',
				'Campaign_e_card_Template'  => 'Campaign E-card Template',
				'campaign_url'              => 'campaign URL',
			));

			// Enqueue the admin CSS file for both post edit and list pages
			wp_enqueue_style(
				'admin-wc-donation-form',
				WC_DONATION_URL . 'assets/css/admin-wc-donation-email.css',
				array(),
				null
			);
		}
	}

	public function email_modify_column_names( $columns ) {

		unset( $columns['title'] );
		unset( $columns['date'] );
		
		$columns['email_name']   = __( 'Name', 'wc-donation' );
		$columns['email_subject']     = __( 'Subject', 'wc-donation' );
		$columns['action']         = __( 'Action', 'wc-donation' );
		$columns['status']      = __( 'Enable/Disable Email', 'wc-donation' );
		$columns['date_time']            = __( 'Date Time', 'wc-donation' );

		/**              
		 * WC Donation filter.
		 * 
		 * @since 1.0.0
		 */
		return apply_filters( 'wc_add_email_columns', $columns );
	}
	public function email_custom_column_content( $column_name, $post_id ) {
		switch ( $column_name ) {
			case 'email_name':
				echo esc_html( get_the_title( $post_id ) );
				break;

			case 'email_subject':
				$email_subject = get_post_meta( $post_id, 'donation_email_subject', true );
				echo esc_html( $email_subject ? $email_subject : __( 'No subject set', 'wc-donation' ) );
				break;

			case 'action':
				$action = get_post_meta( $post_id, '_donation_action', true );
				echo $action 
					? esc_html( ucfirst( str_replace('_', ' ', $action) ) ) 
					: esc_html__( 'No action set', 'wc-donation' );
				break;

			case 'status':
				$status = get_post_meta($post_id, '_email_templates_enabled', true);
				$checked = 'yes' === $status ? 'checked' : '';
				echo '<label class="switch">
				        <input type="checkbox" class="toggle-email-status" data-post-id="' . esc_attr($post_id) . '" ' . esc_attr($checked) . '>
				        <span class="slider round"></span>
				      </label>';                
				break;

			case 'date_time':
				echo get_the_date( 'Y-m-d H:i:s', $post_id );
				break;
		}
	}

	// Register the custom post type
	public function wc_donation_email_posttype() {
		$labels = array(
			'name'                  => _x( 'Donation Emails', 'Post type general name', 'wc-donation' ),
			'singular_name'         => _x( 'Donation Email', 'Post type singular name', 'wc-donation' ),
			'menu_name'             => _x( 'Donation Emails', 'Admin Menu text', 'wc-donation' ),
			'name_admin_bar'        => _x( 'Donation Email', 'Add New on Toolbar', 'wc-donation' ),
			'add_new'               => __( 'Add New Email', 'wc-donation' ),
			'add_new_item'          => __( 'Add New Donation Email', 'wc-donation' ),
			'edit_item'             => __( 'Edit Donation Email', 'wc-donation' ),
			'new_item'              => __( 'New Donation Email', 'wc-donation' ),
			'view_item'             => __( 'View Donation Email', 'wc-donation' ),
			'search_items'          => __( 'Search Donation Emails', 'wc-donation' ),
			'not_found'             => __( 'No Donation Emails found', 'wc-donation' ),
			'not_found_in_trash'    => __( 'No Donation Emails found in Trash', 'wc-donation' ),
		);

		$args = array(
			'label'                 => __( 'Donation Emails', 'wc-donation' ),
			'labels'                => $labels,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => 'edit.php?post_type=wc-donation',
			'menu_icon'             => 'dashicons-email',
			'supports'            => array( 'title' ), // Title
			'has_archive'           => true,
			'rewrite'               => array( 'slug' => 'donation-emails' ),
			'menu_position'         => 25,
		);

		register_post_type( 'wc-donation-email', $args );
	}


	// Add custom metaboxes
	public function add_custom_meta_boxes() {
		add_meta_box('donation-email-information-meta-box', 'Email Information', array( $this, 'donation_render_email_information_meta_box' ), 'wc-donation-email', 'normal', 'default');

		add_meta_box( 'donation_campaign', 'Campaign', array( $this, 'render_campaign_box' ), 'wc-donation-email', 'side' );
		add_meta_box( 'donation_actions', 'Actions', array( $this, 'render_actions_box' ), 'wc-donation-email', 'side' );
		add_meta_box( 'donation_templates', 'Email Template', array( $this, 'render_email_templates_box' ), 'wc-donation-email', 'side' );
		add_meta_box( 'donation_awareness_email', 'Awareness Email', array( $this, 'render_awareness_email_box' ), 'wc-donation-email', 'side' );
	}

	public function donation_render_email_information_meta_box( $post ) {
		$email_subject = get_post_meta($post->ID, 'donation_email_subject', true);
		$email_editor = get_post_meta($post->ID, 'donation_email_editor', true);
		wp_nonce_field( 'email-nonce', 'nonce' );
		?>
		<div class="email-admin-form-field">
			<label><?php echo esc_html__('Subject:', 'abandoned-cart'); ?></label>
			<input type="text" name="donation_email_subject" value="<?php echo esc_html__( $email_subject ); ?>" required>
		</div>
		<?php
		wp_editor($email_editor, 'donation_editor_id', array(
			'textarea_name' => 'donation_email_editor',
			'editor_class' => 'custom-donation-editor',
		));
	}

	public function render_campaign_box( $post ) {
		wp_nonce_field('save_campaign', 'campaign_nonce');

		// Retrieve selected campaigns
		$selected_campaigns = get_post_meta($post->ID, 'campaign_ids', true);
		if (!is_array($selected_campaigns)) {
			$selected_campaigns = array();
		}
		$selected_campaigns = array_map('intval', $selected_campaigns);
		// Fetch campaigns dynamically
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

		// Render the select field
		echo '<div class="select-wrapper">';
		echo '<label for="select-campaign">' . esc_html__('Select Campaigns', 'wc-donation') . '</label>';
		echo '<select id="select-campaign" name="campaign_ids[]" multiple="multiple" class="widefat">';

		// Loop through products to generate options
		foreach ($products as $product) {
			$product_obj = wc_get_product($product->ID);
			$selected = in_array($product->ID, $selected_campaigns, true) ? 'selected="selected"' : '';
			echo '<option value="' . esc_attr($product->ID) . '" ' . esc_attr($selected) . '>';
			echo esc_html($product_obj->get_name());
			echo '</option>';
		}

		echo '</select>';
		echo '</div>';

		// Add jQuery to initialize selectWoo
		echo '<script type="text/javascript">
	        jQuery(document).ready(function($){
	            $("#select-campaign").selectWoo({
	                placeholder: "' . esc_js(__('Select Campaigns', 'wc-donation')) . '",
	                allowClear: true
	            });
	        });
	    </script>';
	}

	public function render_actions_box( $post ) {
		wp_nonce_field('save_actions', 'actions_nonce');

		// Retrieve the selected action from post meta
		$selected_action = get_post_meta($post->ID, '_donation_action', true);

		echo '<label for="actions">' . esc_html__('Select Donation Action', 'wc-donation') . '</label>';
		echo '<select name="actions" id="actions" class="widefat">';
		echo '<option value="completed_email" ' . selected($selected_action, 'completed_email', false) . '>' . esc_html__('Donation Completed', 'wc-donation') . '</option>';
		echo '<option value="pending_email" ' . selected($selected_action, 'pending_email', false) . '>' . esc_html__('Donation Pending', 'wc-donation') . '</option>';
		echo '<option value="failed_email" ' . selected($selected_action, 'failed_email', false) . '>' . esc_html__('Donation Failed', 'wc-donation') . '</option>';
		echo '<option value="refunded_email" ' . selected($selected_action, 'refunded_email', false) . '>' . esc_html__('Donation Refunded', 'wc-donation') . '</option>';
		echo '<option value="cancelled_email" ' . selected($selected_action, 'cancelled_email', false) . '>' . esc_html__('Donation Cancelled', 'wc-donation') . '</option>';
		echo '<option value="awareness_email" ' . selected($selected_action, 'awareness_email', false) . '>' . esc_html__('Awareness of Campaign', 'wc-donation') . '</option>';
		echo '</select>';
	}

	public function render_email_templates_box( $post ) {
		wp_nonce_field('save_templates', 'templates_nonce');

		// Retrieve the current state (enabled or disabled) from post meta
		$is_enabled = get_post_meta($post->ID, '_email_templates_enabled', true);

		// Set default value to 'no' (disabled) if nothing is saved
		if ('' === $is_enabled) {
			$is_enabled = 'no';
		}

		// Render the toggle slider
		echo '<label for="email_templates_toggle">' . esc_html__('Enable Email', 'wc-donation') . '</label>';
		echo '<label class="switch">';
		echo '<input type="checkbox" name="email_templates_enabled" id="email_templates_toggle" value="yes"' . checked($is_enabled, 'yes', false) . '>';
		echo '<span class="slider round"></span>';
		echo '</label>';
	}

	public function render_awareness_email_box( $post ) {
		wp_nonce_field('save_awareness_email', 'awareness_email_nonce');

		// Retrieve current values from post meta
		$awareness_enabled = get_post_meta($post->ID, '_awareness_email_enabled', true);
		$time_interval = get_post_meta($post->ID, '_awareness_email_interval', true);
		$time_unit = get_post_meta($post->ID, '_awareness_email_unit', true);

		// Set default values if they don't exist
		$awareness_enabled = '' === $awareness_enabled ? 'no' : $awareness_enabled;
		$time_interval = $time_interval ? $time_interval : '30'; // Default to 30 days
		$time_unit = $time_unit ? $time_unit : 'days';

		// Render the toggle slider
		echo '<div style="margin-bottom: 15px;">';
		echo '<label>' . esc_html__('Enable Awareness Email', 'wc-donation') . '</label>';
		echo '<label class="switch" style="display: inline-block; margin-left: 10px;">';
		echo '<input type="checkbox" name="awareness_email_enabled" id="awareness_email_toggle" value="yes" ' . checked($awareness_enabled, 'yes', false) . '>';
		echo '<span class="slider round"></span>';
		echo '</label>';
		echo '</div>';

		// Render the time interval dropdowns
		echo '<div>';
		echo '<label for="awareness_email_interval">' . esc_html__('Send Email After', 'wc-donation') . '</label>';
		echo '<input type="number" name="awareness_email_interval" id="awareness_email_interval" value="' . esc_attr($time_interval) . '" min="1" style="width: 55px; margin-left: 6px;">';
		echo '<select name="awareness_email_unit" id="awareness_email_unit" style="margin-left: 5px;">';
		echo '<option value="days" ' . selected($time_unit, 'days', false) . '>' . esc_html__('Days', 'wc-donation') . '</option>';
		echo '<option value="weeks" ' . selected($time_unit, 'weeks', false) . '>' . esc_html__('Weeks', 'wc-donation') . '</option>';
		echo '<option value="months" ' . selected($time_unit, 'months', false) . '>' . esc_html__('Months', 'wc-donation') . '</option>';
		echo '</select>';
		echo '</div>';
	}
}

new WcdonationEmail();


