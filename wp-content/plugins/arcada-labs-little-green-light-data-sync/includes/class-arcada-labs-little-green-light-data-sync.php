<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://arcadalabs.com
 * @since      1.0.0
 *
 * @package    Arcada_Labs_Little_Green_Light_Data_Sync
 * @subpackage Arcada_Labs_Little_Green_Light_Data_Sync/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Arcada_Labs_Little_Green_Light_Data_Sync
 * @subpackage Arcada_Labs_Little_Green_Light_Data_Sync/includes
 * @author     Arcada Labs <hello@arcadalabs.com>
 */
class Arcada_Labs_Little_Green_Light_Data_Sync {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Arcada_Labs_Little_Green_Light_Data_Sync_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'ARCADA_LABS_LITTLE_GREEN_LIGHT_DATA_SYNC_VERSION' ) ) {
			$this->version = ARCADA_LABS_LITTLE_GREEN_LIGHT_DATA_SYNC_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'arcada-labs-little-green-light-data-sync';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Arcada_Labs_Little_Green_Light_Data_Sync_Loader. Orchestrates the hooks of the plugin.
	 * - Arcada_Labs_Little_Green_Light_Data_Sync_i18n. Defines internationalization functionality.
	 * - Arcada_Labs_Little_Green_Light_Data_Sync_Admin. Defines all hooks for the admin area.
	 * - Arcada_Labs_Little_Green_Light_Data_Sync_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/vendor/autoload.php';

        require 'utils/gf_utils.php';
        require 'LGL/LGLCore.php';
        require 'Constants/Dropdowns.php';
        require 'Constants/Names.php';
        require 'LGL/Sync/arcada-labs-lgl-sync-operator.php';
        require 'Wizard/Arcada_Labs_Wizard_Operator.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-arcada-labs-little-green-light-data-sync-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-arcada-labs-little-green-light-data-sync-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-arcada-labs-little-green-light-data-sync-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-arcada-labs-little-green-light-data-sync-public.php';

		$this->loader = new Arcada_Labs_Little_Green_Light_Data_Sync_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Arcada_Labs_Little_Green_Light_Data_Sync_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Arcada_Labs_Little_Green_Light_Data_Sync_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Arcada_Labs_Little_Green_Light_Data_Sync_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_bugsnag' );

        $this->loader->add_action('admin_menu', $plugin_admin, 'add_options_page');
        $this->loader->add_action('admin_init', $plugin_admin, 'setup_settings_sections');
        $this->loader->add_action('admin_init', $plugin_admin, 'setup_activation_sections');

        $this->loader->add_action('rest_api_init', $plugin_admin, 'add_api_endpoints');

        $this->loader->add_action('show_user_profile', $plugin_admin, 'arcada_labs_lgl_extra_user_profile_fields');
        $this->loader->add_action('edit_user_profile', $plugin_admin, 'arcada_labs_lgl_extra_user_profile_fields');
        $this->loader->add_action('personal_options_update', $plugin_admin, 'arcada_labs_lgl_save_extra_user_profile_fields');
        $this->loader->add_action('edit_user_profile_update', $plugin_admin, 'arcada_labs_lgl_save_extra_user_profile_fields');

		// WooCommerce product page editions
		$this->loader->add_filter('manage_edit-product_columns', $plugin_admin, 'wc_add_product_columns');
		$this->loader->add_action('manage_product_posts_custom_column', $plugin_admin, 'wc_manage_product_columns', 10, 2);
		$this->loader->add_action('woocommerce_product_quick_edit_start', $plugin_admin, 'wc_add_quick_edit_fields');
		$this->loader->add_action('woocommerce_product_quick_edit_save', $plugin_admin, 'wc_add_bulk_edit_fields_save');
		$this->loader->add_action('woocommerce_product_bulk_edit_start', $plugin_admin, 'wc_add_bulk_edit_fields');
        $this->loader->add_action('woocommerce_product_bulk_edit_save', $plugin_admin, 'wc_add_bulk_edit_fields_save');

        if (!class_exists('woocommerce')) {
            $this->loader->add_action('woocommerce_created_customer', $plugin_admin, 'arcada_labs_lgl_user_register', 10, 1);
            $this->loader->add_action('woocommerce_created_customer', $plugin_admin, 'arcada_labs_lgl_user_update', 10, 2);
        } else {
            $this->loader->add_action('user_register', $plugin_admin, 'arcada_labs_lgl_user_register', 10, 1);
            $this->loader->add_action('profile_update', $plugin_admin, 'arcada_labs_lgl_user_update', 10, 2);
        }

        $this->loader->add_action('add_meta_boxes', $plugin_admin, 'arcada_labs_lgl_product_sync_field');
        $this->loader->add_action('save_post', $plugin_admin, 'arcada_labs_lgl_save_product_meta', 10, 2);

        $this->loader->add_action('woocommerce_thankyou', $plugin_admin, 'arcada_labs_lgl_order_sync');
        $this->loader->add_action('woocommerce_subscription_renewal_payment_complete', $plugin_admin, 'arcada_labs_lgl_recurring_sync', 10, 2);

        $this->loader->add_action('gform_post_payment_completed', $plugin_admin, 'arcada_labs_lgl_gform_payment_sync', 10, 2);
        $this->loader->add_action('gform_after_submission', $plugin_admin, 'arcada_labs_lgl_gform_sync', 10, 2);

	}

	/**
	 * Register all the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Arcada_Labs_Little_Green_Light_Data_Sync_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Arcada_Labs_Little_Green_Light_Data_Sync_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
