<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://arcadalabs.com
 * @since      1.0.0
 *
 * @package    Arcada_Labs_Little_Green_Light_Data_Sync
 * @subpackage Arcada_Labs_Little_Green_Light_Data_Sync/admin
 */

use ArcadaLabs\LGL\Sync\Arcada_Labs_LGL_Sync_Operator;
use ArcadaLabs\Utils\GFUtils;
use ArcadaLabs\Wizard\Arcada_Labs_Wizard_Operator;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Arcada_Labs_Little_Green_Light_Data_Sync
 * @subpackage Arcada_Labs_Little_Green_Light_Data_Sync/admin
 * @author     Arcada Labs <hello@arcadalabs.com>
 */
class Arcada_Labs_Little_Green_Light_Data_Sync_Admin {


    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Sync Operators
     *
     */
    private $sync_operator;

    /**
     * Wizard Operators
     */
    private $wizard_operator;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->sync_operator = new Arcada_Labs_LGL_Sync_Operator();
        $this->wizard_operator = new Arcada_Labs_Wizard_Operator();

        /* region Wizard variables */
        $this->start_step = get_option(\ArcadaLabs\Constants\Names::OPTIONS['start_step']);
        $this->license_step = get_option(\ArcadaLabs\Constants\Names::OPTIONS['license_step']);
        $this->api_key = get_option(\ArcadaLabs\Constants\Names::OPTIONS['api_key']);
        $this->webhook_url = get_option(\ArcadaLabs\Constants\Names::OPTIONS['webhook_url']);
        $this->forms_filled = get_option(\ArcadaLabs\Constants\Names::OPTIONS['forms_filled']);
        $this->wc_products = get_option(\ArcadaLabs\Constants\Names::OPTIONS['wc_products']);
        $this->initial_sync = get_option(\ArcadaLabs\Constants\Names::OPTIONS['initial_sync']);
        $this->complete = get_option(\ArcadaLabs\Constants\Names::OPTIONS['complete']);
        /* endregion */

        $this->access = $this->sync_operator->get_license_tiers();
        if (get_option('arcada_labs_lgl_sync_settings_field_lgl_api_key')) {
            $this->funds = $this->sync_operator->getFunds();
            $this->categories = $this->sync_operator->getCategories();
            $this->campaigns = $this->sync_operator->getCampaigns();
            $this->gift_types = $this->sync_operator->getGiftTypes();
            $this->payment_types = $this->sync_operator->getPaymentTypes();
        } else {
            $this->funds = array();
            $this->categories = array();
            $this->campaigns = array();
            $this->gift_types = array();
            $this->payment_types = array();
        }
    }

    public function enqueue_bugsnag () {
        wp_enqueue_script( 'bugsnag', 'https://d2wy8f7a9ursnm.cloudfront.net/v7/bugsnag.min.js');
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Arcada_Labs_Little_Green_Light_Sync_Loader as all the hooks are defined
         * in that particular class.
         *
         * The Arcada_Labs_Little_Green_Light_Sync_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/arcada-labs-little-green-light-data-sync-admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Arcada_Labs_Little_Green_Light_Sync_Loader as all the hooks are defined
         * in that particular class.
         *
         * The Arcada_Labs_Little_Green_Light_Sync_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/arcada-labs-little-green-light-data-sync-admin.js', array( 'jquery' ), $this->version, false);

        wp_localize_script($this->plugin_name . '-admin', 'arcadaData', array(
            'root' => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest')
        ));
    }

    /**
     * Register the REST routes for the syncs
     */
    public function add_api_endpoints()
    {
        register_rest_route('arcada-lgl-sync/v1', 'sync/constituent', array(
            'methods' => 'POST',
            'args' => ['data'],
            'callback' => array($this->sync_operator, 'run_constituent_sync'),
            'permission_callback' => array($this, 'rest_permissions_check'),
        ));

        register_rest_route('arcada-lgl-sync/v1', 'sync/constituent-count', array(
            'methods' => 'POST',
            'args' => ['data'],
            'callback' => array($this->sync_operator, 'run_constituent_count'),
            'permission_callback' => array($this, 'rest_permissions_check'),
        ));

        register_rest_route('arcada-lgl-sync/v1', 'sync/transaction-count', array(
            'methods' => 'POST',
            'args' => ['data'],
            'callback' => array($this->sync_operator, 'run_transaction_count'),
            'permission_callback' => array($this, 'rest_permissions_check'),
        ));

        register_rest_route('arcada-lgl-sync/v1', 'sync/transaction', array(
            'methods' => 'POST',
            'args' => ['data'],
            'callback' => array($this->sync_operator, 'run_transaction_sync'),
            'permission_callback' => array($this, 'rest_permissions_check'),
        ));

        register_rest_route('arcada-lgl-sync/v1', 'sync/forms-count', array(
            'methods' => 'POST',
            'args' => ['data'],
            'callback' => array($this->sync_operator, 'run_forms_count'),
            'permission_callback' => array($this, 'rest_permissions_check'),
        ));

        register_rest_route('arcada-lgl-sync/v1', 'sync/forms', array(
            'methods' => 'POST',
            'args' => ['data'],
            'callback' => array($this->sync_operator, 'run_forms_sync'),
            'permission_callback' => array($this, 'rest_permissions_check'),
        ));

        register_rest_route('arcada-lgl-sync/v1', 'sync/activate-license', array(
            'methods' => 'POST',
            'args' => ['data'],
            'callback' => array($this->sync_operator, 'activate_license_key'),
            'permission_callback' => array($this, 'rest_permissions_check'),
        ));

        register_rest_route('arcada-lgl-sync/v1', 'sync/deactivate-license', array(
            'methods' => 'POST',
            'args' => ['data'],
            'callback' => array($this->sync_operator, 'deactivate_license_key'),
            'permission_callback' => array($this, 'rest_permissions_check'),
        ));

        register_rest_route('arcada-lgl-sync/v1', 'wizard', array(
            'methods' => 'POST',
            'args' => ['data'],
            'callback' => array($this->wizard_operator, 'wizard_flow'),
            'permission_callback' => array($this, 'rest_permissions_check'),
        ));
    }

    /**
     * Checks if the request has the proper permission
     * @return bool
     */
    public function rest_permissions_check()
    {
        return true;
        $user = wp_get_current_user();
        $allowed_roles = array('administrator');
        if (array_intersect($allowed_roles, $user->roles)) {
            return true;
        }
        return false;
    }


	/**
	 * @param $post_id
	 * Function that renders the lgl properties for WooCommerce products
	 * @return void
	 */
	public function render_product_lgl_fields($post_id = false) {
		$checked = '';

		if ($post_id) {
			$category = get_post_meta($post_id, '_lgl_category', true);
			$campaign = get_post_meta($post_id, '_lgl_campaign', true);
			$fund = get_post_meta($post_id, '_lgl_fund', true);
			$gift_type = get_post_meta($post_id, '_lgl_gift_type', true);

			$id_number = get_post_meta($post_id, '_lgl_sync', true);
			if (!!$id_number) {
				$checked = 'checked';
			}
		}
		$sync_check_id = rand(1, 999999999);

		echo '<div class="form-group"><input id="_lgl_sync_'. $sync_check_id .'" type="checkbox" name="_lgl_sync" value="true" ' . $checked . ' ><label for="_lgl_sync_'. $sync_check_id .'">Sync this product</label></input></div>';

		$funds = $this->funds;
		$categories = $this->categories;
		$campaigns = $this->campaigns;
		$gift_types = $this->gift_types;

		$field_name = '_lgl_category';
		include 'partials/settings-fields/lgl-categories.php';

		$field_name = '_lgl_campaign';
		include 'partials/settings-fields/lgl-campaigns.php';

		$field_name = '_lgl_fund';
		include 'partials/settings-fields/lgl-funds.php';

		$field_name = '_lgl_gift_type';
		include 'partials/settings-fields/lgl-gift-types.php';

	}

	public function wc_add_product_columns($columns) {
		$columns['_lgl_sync'] = __('Sync to LGL');
		$columns['_lgl_category'] = __('LGL Category');
		$columns['_lgl_campaign'] = __('LGL Campaign');
		$columns['_lgl_fund'] = __('LGL Fund');
		$columns['_lgl_gift_type'] = __('LGL Gift Type');

		return $columns;
	}

	public function wc_manage_product_columns($column, $post_id) {
		if ($column ==  '_lgl_sync') {
			$id_number = get_post_meta($post_id, '_lgl_sync', true);
			if (!!$id_number) {
				echo __('Yes');
			} else {
				echo __('No');
			}
		}

		if ($column ==  '_lgl_category') {
			echo get_post_meta($post_id, '_lgl_category', true);
		}

		if ($column ==  '_lgl_campaign') {
			echo get_post_meta($post_id, '_lgl_campaign', true);
		}

		if ($column ==  '_lgl_fund') {
			echo get_post_meta($post_id, '_lgl_fund', true);
		}

		if ($column ==  '_lgl_gift_type') {
			echo get_post_meta($post_id, '_lgl_gift_type', true);
		}

	}

	/**
	 * Function that renders the properties for quick editing on the products list page
	 * @return void
	 */
	public function wc_add_quick_edit_fields() {
		if ($this->access[\ArcadaLabs\Constants\Names::TIERS['WC_LICENSE']]) {

			echo '<legend class="arcada-lgl-field-group"><span class="arcada-lgl-field-group-title">Little Green Light sync options</span>';

			$this->render_product_lgl_fields();

			echo '</legend>';
		}
	}

	/**
	 * Function that renders the properties for the Bulk edit on the products list page
	 * @return void
	 */
	public function wc_add_bulk_edit_fields() {
		if ($this->access[\ArcadaLabs\Constants\Names::TIERS['WC_LICENSE']]) {
			echo '<legend class="arcada-lgl-field-group"><span class="arcada-lgl-field-group-title">Little Green Light sync options</span>';

			$this->render_product_lgl_fields();

			echo '</legend>';
		}
	}

	/**
	 * @param $product
	 * Function to sae the bulk edition of WooCommerce products
	 * @return void
	 */
	public function wc_add_bulk_edit_fields_save($product) {
		$post_id = $product->get_id();

		// Sanitize user input and update the meta field in the database.
		if (isset($_REQUEST['_lgl_sync'])) {
			update_post_meta( $post_id, '_lgl_sync', wc_clean($_REQUEST['_lgl_sync']) );
		}
		if (isset($_REQUEST['_lgl_category'])) {
			update_post_meta( $post_id, '_lgl_category', wc_clean($_REQUEST['_lgl_category']) );
		}
		if (isset($_REQUEST['_lgl_campaign'])) {
			update_post_meta( $post_id, '_lgl_campaign', wc_clean($_REQUEST['_lgl_campaign']) );
		}
		if (isset($_REQUEST['_lgl_fund'])) {
			update_post_meta( $post_id, '_lgl_fund', wc_clean($_REQUEST['_lgl_fund']) );
		}
		if (isset($_REQUEST['_lgl_gift_type'])) {
			update_post_meta( $post_id, '_lgl_gift_type', wc_clean($_REQUEST['_lgl_gift_type']) );
		}
	}

    /**
     * Add the plugin to the WP menu
     */
    public function add_options_page()
    {

        $this->plugin_screen_hook_suffix = add_menu_page(
            __('LGL Sync', 'arcada-labs'),
            __('LGL Sync', 'arcada-labs'),
            'manage_options',
            $this->plugin_name,
            array($this, 'display_sync_plugin_index_page'),
            'dashicons-cloud',
            58
        );

        $this->plugin_screen_hook_suffix = add_submenu_page(
            $this->plugin_name,
            __('Settings', 'arcada-labs'),
            __('Settings', 'arcada-labs'),
            'manage_options',
            $this->plugin_name . '-settings',
            array($this, 'display_sync_plugin_settings_page')
        );

        $this->plugin_screen_hook_suffix = add_submenu_page(
            $this->plugin_name,
            __('Product Activation', 'arcada-labs'),
            __('Product Activation', 'arcada-labs'),
            'manage_options',
            $this->plugin_name . '-product-activation',
            array($this, 'display_sync_plugin_product_activation_page')
        );
    }

    /**
     * Set up the activation fields
     */
    public function setup_activation_sections()
    {
        add_settings_section(
            'arcada_labs_lgl_sync_general_activation',
            __('Register your license', 'arcada-labs'),
            false,
            $this->plugin_name . '-activation'
        );

        $fields = array(
            array(
                'id'    => 'arcada_labs_lgl_sync_license_key',
                'title' => __('Your License Key', 'arcada-labs'),
                'section' => 'arcada_labs_lgl_sync_general_activation',
                'callback' => 'arcada_labs_lgl_sync_text_field',
                'type'  => 'text'
            ),
        );

        foreach ($fields as $field) {
            add_settings_field(
                $field['id'],
                $field['title'],
                array($this, $field['callback']),
                $this->plugin_name . '-activation',
                $field['section'],
                $field
            );
            register_setting('arcada_labs_lgl_sync_activation', $field['id']);
        }
    }

    /**
     * Set up the settings fields
     */
    public function setup_settings_sections()
    {
        add_settings_section(
            'arcada_labs_lgl_sync_general_settings',
            __('Little Green Light Keys', 'arcada-labs'),
            false,
            $this->plugin_name . '-settings'
        );

        $fields = array(
            array(
                'id'    => 'arcada_labs_lgl_sync_settings_field_lgl_api_key',
                'title' => __('Little Green Light API key', 'arcada-labs'),
                'section' => 'arcada_labs_lgl_sync_general_settings',
                'callback' => 'arcada_labs_lgl_sync_text_field',
                'type'  => 'text'
            ),
            array(
                'id'    => 'arcada_labs_lgl_webhook_url',
                'title' => __('Little Green Light Webhook URL', 'arcada-labs'),
                'section' => 'arcada_labs_lgl_sync_general_settings',
                'callback' => 'arcada_labs_lgl_sync_url_field',
                'type'  => 'text'
            )
        );

        /* region Adding Forms according to license */
        if ($this->access[\ArcadaLabs\Constants\Names::TIERS['GF_LICENSE']]) {
            add_settings_section(
                'arcada_labs_lgl_sync_general_settings',
                __('Gravity Forms', 'arcada-labs'),
                false,
                $this->plugin_name . '-settings-g-form'
            );
            add_settings_field(
                'arcada_labs_lgl_sync_settings_field_forms',
                __('Forms to apply sync', 'arcada-labs'),
                array($this, 'arcada_labs_lgl_sync_forms_field'),
                $this->plugin_name . '-settings-g-form',
                'arcada_labs_lgl_sync_general_settings',
                array(
                    'id'    => 'arcada_labs_lgl_sync_settings_field_forms',
                    'title' => __('Forms to apply sync', 'arcada-labs'),
                    'section' => 'arcada_labs_lgl_sync_general_settings',
                    'callback' => 'arcada_labs_lgl_sync_forms_field',
                    'type'  => 'select'
                )
            );
            register_setting('arcada_labs_lgl_sync_settings_g_form', 'arcada_labs_lgl_sync_settings_field_forms');
        }
        if ($this->access[\ArcadaLabs\Constants\Names::TIERS['WC_LICENSE']]) {
            add_settings_section(
                'arcada_labs_lgl_sync_general_settings',
                __('WooCommerce', 'arcada-labs'),
                false,
                $this->plugin_name . '-settings-wc-form'
            );
            add_settings_field(
                'arcada_labs_lgl_sync_wc_payment_type',
                __('Payment type', 'arcada-labs'),
                array($this, 'arcada_labs_lgl_sync_payment_type'),
                $this->plugin_name . '-settings-wc-form',
                'arcada_labs_lgl_sync_general_settings',
                array(
                    'id'    => 'arcada_labs_lgl_sync_wc_payment_type',
                    'title' => __('Payment type', 'arcada-labs'),
                    'section' => 'arcada_labs_lgl_sync_general_settings',
                    'callback' => 'arcada_labs_lgl_sync_payment_type',
                    'type'  => 'select'
                )
            );
            register_setting('arcada_labs_lgl_sync_settings_wc_payment_type', 'arcada_labs_lgl_sync_wc_payment_type');
        }
        /* endregion */

        foreach ($fields as $field) {
            add_settings_field(
                $field['id'],
                $field['title'],
                array($this, $field['callback']),
                $this->plugin_name . '-settings',
                $field['section'],
                $field
            );
            register_setting('arcada_labs_lgl_sync_settings', $field['id']);
        }
    }

    /**
     * Simple text field
     * @param $args
     */
    public function arcada_labs_lgl_sync_text_field($args)
    {
        include 'partials/settings-fields/field-lgl-sync-plugin-settings-text.php';
    }

    /**
     * Simple text field
     * @param $args
     */
    public function arcada_labs_lgl_sync_url_field($args)
    {
        include 'partials/settings-fields/field-lgl-sync-plugin-settings-text.php';
    }

	public function arcada_labs_lgl_sync_payment_type($args)
	{
		$payment_types = $this->payment_types;
		$payment_type = get_option('arcada_labs_lgl_sync_wc_payment_type') ?? 'Credit Card';
		$field_name = 'arcada_labs_lgl_sync_wc_payment_type';

		echo '<p class="lgl-input-instructions">
            Choose the payment type that is used on your site.
            <br>
            These values will be used for your WooCommerce transaction synchronizations.
            <br>
            <br>
        </p>';

		include 'partials/settings-fields/lgl-payment-types.php';
	}

    /**
     * The forms control
     * @param $args
     */
    public function arcada_labs_lgl_sync_forms_field($args)
    {
        $funds = $this->funds;
        $categories = $this->categories;
        $campaigns = $this->campaigns;
        $gift_types = $this->gift_types;
        $payment_types = $this->payment_types;

        include 'partials/settings-fields/field-lgl-sync-plugin-settings-forms.php';
    }

    /**
     * Display the initial wizard
     * @return void
     */
    public function display_wizard()
    {
        // The following variables are used on the includes under, don't remove them
        $GF_LICENSE = $this->access[\ArcadaLabs\Constants\Names::TIERS['GF_LICENSE']];
        $WC_LICENSE = $this->access[\ArcadaLabs\Constants\Names::TIERS['WC_LICENSE']];

        $start_step = $this->start_step;
        $license_step = $this->license_step;
        $api_key = $this->api_key;
        $webhook_url = $this->webhook_url;
        $forms_filled = $this->forms_filled;
        $wc_products = $this->wc_products;
        $initial_sync = $this->initial_sync;
        $complete = $this->complete;

        $license_step_field = \ArcadaLabs\Constants\Names::OPTIONS['license_step'];
        $api_key_field = \ArcadaLabs\Constants\Names::OPTIONS['api_key'];
        $webhook_url_field = \ArcadaLabs\Constants\Names::OPTIONS['webhook_url'];
        $forms_filled_field = \ArcadaLabs\Constants\Names::OPTIONS['forms_filled'];
        $wc_products_field = \ArcadaLabs\Constants\Names::OPTIONS['wc_products'];
        $initial_sync_field = \ArcadaLabs\Constants\Names::OPTIONS['initial_sync'];

        $lgl_dashboard = get_option('arcada_labs_lgl_sync_dashboard_link');

        $completed = (bool) $start_step;
        include_once 'partials/header.php';
        include_once 'partials/wizard/start.php';
        $completed = (bool) $start_step == (bool) $license_step;
        include_once 'partials/wizard/license.php';
        $completed = (bool) $license_step == (bool) $api_key;
        include_once 'partials/wizard/api_key.php';
        $completed = (bool) $api_key == (bool) $webhook_url;
        include_once 'partials/wizard/webhook_url.php';

        $completed = (bool) $webhook_url == (bool) $complete;

        $completed = (bool) $webhook_url == (bool) $forms_filled;
        include_once 'partials/wizard/forms_to_fill.php';
        $completed = (bool) $forms_filled == (bool) $complete;

        if ($GF_LICENSE) {
            $completed = (bool)$forms_filled == (bool)$wc_products;
        } else {
            $completed = (bool)$webhook_url == (bool)$wc_products;
        }

		$payment_types = $this->payment_types;
        include_once 'partials/wizard/wc_products.php';
        $completed = (bool) $wc_products == (bool) $complete;

        include_once 'partials/wizard/initial_sync.php';
    }


    /**
     * The page for the settings of the plugin
     */
    public function display_sync_plugin_settings_page()
    {
        if ($this->complete) {
            $GF_LICENSE = $this->access[\ArcadaLabs\Constants\Names::TIERS['GF_LICENSE']];
            $WC_LICENSE = $this->access[\ArcadaLabs\Constants\Names::TIERS['WC_LICENSE']];
            include_once 'partials/page-lgl-sync-plugin-settings.php';
        } else {
            $this->display_wizard();
        }
    }

    /**
     * The page for the activation of the plugin
     */
    public function display_sync_plugin_product_activation_page()
    {
        if ($this->complete) {
            include_once 'partials/page-lgl-sync-plugin-activation.php';
        } else {
            $this->display_wizard();
        }
    }

    /**
     * The page for the index, it also contains the Wizard for the initial setup
     */
    public function display_sync_plugin_index_page()
    {
        $GF_LICENSE = $this->access[\ArcadaLabs\Constants\Names::TIERS['GF_LICENSE']];
        $WC_LICENSE = $this->access[\ArcadaLabs\Constants\Names::TIERS['WC_LICENSE']];
        if ($this->complete) {
            include_once 'partials/page-lgl-sync-plugin-index.php';
        } else {
            $this->display_wizard();
        }
    }

    /*
     * Action to add lgl_id input to user profile
     * */
    public function arcada_labs_lgl_extra_user_profile_fields($user)
    {
        include 'partials/users/field-lgl-sync-plugin-user-fields.php';
    }

    /*
     * Action to save custom lgl_id meta to the user profile
     * */
    function arcada_labs_lgl_save_extra_user_profile_fields($user_id)
    {
        if (empty($_POST['_wpnonce']) || ! wp_verify_nonce($_POST['_wpnonce'], 'update-user_' . $user_id)) {
            return;
        }

        if (!current_user_can('edit_user', $user_id)) {
            return false;
        }
        update_user_meta($user_id, 'lgl_id', $_POST['lgl_id']);
    }

    /*
     * Action to sync a user to LGL after registration
     * */
    function arcada_labs_lgl_user_register($user_id)
    {
        if ($this->complete) {
            $user_data = get_userdata($user_id);

            $constituent = [
                'external_constituent_id' => $user_id,
            ];

            if (isset($_POST['first_name'])) {
                $constituent['first_name'] = $_POST['first_name'];
            } else {
                $constituent['first_name'] = $user_data->user_nicename;
            }
            if (isset($_POST['last_name'])) {
                $constituent['last_name'] = $_POST['last_name'];
            }
            $constituent['email'] = $_POST['email'];
            $data = new WC_Customer($user_id);

            if ($street = $data->get_billing_address_1()) {
                $constituent['street'] = $street;

                if ($city = $data->get_billing_city()) {
                    $constituent['city'] = $city;
                }
                if ($postal_code = $data->get_billing_postcode()) {
                    $constituent['postal_code'] = $postal_code;
                }
                if ($country = $data->get_billing_country()) {
                    $constituent['country'] = $country;
                }
                if ($state = $data->get_billing_state()) {
                    $constituent['state'] = $state;
                }

            }

            if (!empty($constituent['email'])) {
                $this->sync_operator->hookCall($constituent);
            }
        }
    }

    /**
     * Action to sync a user to LGL after profile update
     * @throws \Exception
     */
    function arcada_labs_lgl_user_update($user_id, $old_data)
    {
        if ($this->complete) {
            $constituent = $this->sync_operator->makeConstituentFromWCUser($user_id);

            $this->sync_operator->hookCall($constituent);
        }
    }

    /**
     * Render field to indicate if product should be synced
     */
    function arcada_labs_lgl_product_sync_field()
    {
        if ($this->access[\ArcadaLabs\Constants\Names::TIERS['WC_LICENSE']]) {

            add_meta_box(
                'arcada_labs_lgl_product_meta',
                'Sync with Little Green Light',
                function () {
                    global $post;

	                echo '<legend class="arcada-lgl-field-group"><span class="arcada-lgl-field-group-title">Little Green Light sync options</span>';

	                $this->render_product_lgl_fields($post->ID);

	                echo '<p class="lgl-disclaimer">
                            <small>*For all Gift Types with no selection "Gift" will be used.</small>
                        </p>';

	                echo '<input type="hidden" name="custom_product_field_nonce" value="' . wp_create_nonce() . '">';

	                echo '</legend>';
                },
                'product',
                'advanced',
                'default'
            );
        }
    }

    /**
     * Save meta to indicate if the product should be synced as gifts on LGL
     * @param $post_id
     * @param $post
     * @return mixed|void
     */
    function arcada_labs_lgl_save_product_meta($post_id, $post)
    {
        // We need to verify this with the proper authorization (security stuff).
        // Check if our nonce is set.
        if (!isset($_POST['custom_product_field_nonce'])) {
            return $post_id;
        }
        $nonce = $_REQUEST[ 'custom_product_field_nonce' ];
        //Verify that the nonce is valid.
        if (!wp_verify_nonce($nonce)) {
            return $post_id;
        }
        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id;
        }
        // Check the user's permissions.
        if ('product' == $_POST['post_type']) {
            if (!current_user_can('edit_product', $post_id)) {
                return $post_id;
            }
        } else {
            if (!current_user_can('edit_post', $post_id)) {
                return $post_id;
            }
        }
        // Sanitize user input and update the meta field in the database.
        update_post_meta($post_id, '_lgl_sync', $_POST[ '_lgl_sync' ]);
        update_post_meta($post_id, '_lgl_category', $_POST[ '_lgl_category' ]);
        update_post_meta($post_id, '_lgl_campaign', $_POST[ '_lgl_campaign' ]);
        update_post_meta($post_id, '_lgl_fund', $_POST[ '_lgl_fund' ]);
        update_post_meta($post_id, '_lgl_gift_type', $_POST[ '_lgl_gift_type' ]);
    }

    /**
     * Action to sync the order to LGL if at least one product is selected to be synced
     * @param $order_id
     * @throws \Exception
     */
    function arcada_labs_lgl_order_sync($order_id)
    {
        if ($this->complete) {
            if ($this->access[\ArcadaLabs\Constants\Names::TIERS['WC_LICENSE']]) {
                if (!$order_id) {
                    return;
                }
                // Allow code execution only once
                $order = wc_get_order($order_id);
                if ($gifts = $this->sync_operator->makeGiftFromWCOrder($order)) {
                    // if the user account exists
                    if ($user_id = $order->get_user_id()) {
                        // we create the constituent
                        $constituent = $this->sync_operator->makeConstituentFromWCUser($user_id);
                    } else {
                        // otherwise, we create one with the billing info
                        $constituent = $this->sync_operator->makeConstituentFromWCOrder($order);
                    }

                    foreach ($gifts as $gift) {
                        $hook_data = $this->sync_operator->makeWebHookData($constituent, $gift);
                        $hook_data['received_date'] = date("Y-m-d");

                        $this->sync_operator->hookCall($hook_data);
                    }
                }
            }
        }
    }

    /**
     * On subscriptions renewal run a new sync as it is a new Gift record
     * @throws \Exception
     */
    function arcada_labs_lgl_recurring_sync($subscription, $last_order)
    {
        $this->arcada_labs_lgl_order_sync($last_order->get_id());
    }

    private function gform_sync($entry, $paymentRequired)
    {
        if ($this->complete) {
            if ($this->access[\ArcadaLabs\Constants\Names::TIERS['GF_LICENSE']]) {
                $form = $entry['form_id'];

                if ($gv_forms = get_option('arcada_labs_lgl_sync_settings_field_forms')) {
                    $selected_forms = $gv_forms['form'] ?? [];
                    $selected_categories = $gv_forms['category'] ?? [];
                    $selected_campaigns = $gv_forms['campaign'] ?? [];
                    $selected_funds = $gv_forms['fund'] ?? [];
                    $selected_gift_types = $gv_forms['gift_type'] ?? [];
                    $selected_payment_types = $gv_forms['payment_type'] ?? [];
                    $entry_values = GFAPI::get_entry($entry['id']);

                    // check if the entry comes from a form that is selected for sync
                    foreach ($selected_forms as $index => $form_id) {
                        if (intval($form_id) === intval($form)) {

                            // we verify that the form has the required fields
                            $fields = GFUtils::getFieldsFromForm($form_id);
                            if ($this->sync_operator->gFormFieldsValid($fields)) {
                                $constituent = $this->sync_operator->makeConstituentFromGF($entry_values, $fields);

                                // we create the gift on LGL
                                if (array_key_exists('total', $fields)) {
                                    $gift_data = $this->sync_operator->makeGiftFromGF(
                                        $entry_values,
                                        $fields,
                                        $selected_categories[$index] ?? false,
                                        $selected_campaigns[$index] ?? false,
                                        $selected_funds[$index] ?? false,
                                        $selected_gift_types[$index] ?? false,
                                        $selected_payment_types[$index] ?? false,
                                    );
                                    $hook_data = $this->sync_operator->makeWebHookData($constituent, $gift_data);
                                    $hook_data['received_date'] = date("Y-m-d");
                                } else {
                                    $hook_data = $this->sync_operator->makeWebHookData($constituent, []);
                                }

                                if (($paymentRequired && array_key_exists('total', $fields)) || !$paymentRequired) {
                                    $this->sync_operator->hookCall($hook_data);
                                }
                            }

                            // when form found and sync is done break
                            break;
                        }
                    }
                }
            }
        }
    }

    /**
     * Sync the new entries of the forms
     * @param $entry
     * @param $action
     */
    function arcada_labs_lgl_gform_sync($entry, $action)
    {
        $this->gform_sync($entry, false);
    }

    /**
     * Sync the new entries of the forms after a payment has
     * @param $entry
     * @param $action
     */
    function arcada_labs_lgl_gform_payment_sync($entry, $action)
    {
        $this->gform_sync($entry, true);
    }

}
