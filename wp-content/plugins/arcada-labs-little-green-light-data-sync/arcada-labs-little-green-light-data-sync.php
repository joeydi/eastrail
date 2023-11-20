<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://arcadalabs.com
 * @since             1.0.0
 * @package           Arcada_Labs_Little_Green_Light_Data_Sync
 *
 * @wordpress-plugin
 * Plugin Name:       CRM Sync for Little Green Light
 * Plugin URI:        https://arcadalabs.com/wordpress-woocommerce-and-gravity-forms-sync-for-lgl/
 * Description:       Sync all your customer's information to your Little Green Light account.
 * Version:           1.0.5
 * Author:            Arcada Labs
 * Author URI:        https://arcadalabs.com/about/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       arcada-labs-little-green-light-data-sync
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 0.1.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'ARCADA_LABS_LITTLE_GREEN_LIGHT_DATA_SYNC_VERSION', '1.0.5' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-arcada-labs-little-green-light-data-sync-activator.php
 */
function activate_arcada_labs_little_green_light_data_sync() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-arcada-labs-little-green-light-data-sync-activator.php';
	Arcada_Labs_Little_Green_Light_Data_Sync_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-arcada-labs-little-green-light-data-sync-deactivator.php
 */
function deactivate_arcada_labs_little_green_light_data_sync() {
    delete_option(\ArcadaLabs\Constants\Names::OPTIONS['start_step']);
    delete_option(\ArcadaLabs\Constants\Names::OPTIONS['license_step']);
    delete_option(\ArcadaLabs\Constants\Names::OPTIONS['api_key']);
    delete_option(\ArcadaLabs\Constants\Names::OPTIONS['webhook_url']);
    delete_option(\ArcadaLabs\Constants\Names::OPTIONS['forms_filled']);
    delete_option(\ArcadaLabs\Constants\Names::OPTIONS['wc_products']);
    delete_option(\ArcadaLabs\Constants\Names::OPTIONS['initial_sync']);
    delete_option(\ArcadaLabs\Constants\Names::OPTIONS['complete']);
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-arcada-labs-little-green-light-data-sync-deactivator.php';
	Arcada_Labs_Little_Green_Light_Data_Sync_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_arcada_labs_little_green_light_data_sync' );
register_deactivation_hook( __FILE__, 'deactivate_arcada_labs_little_green_light_data_sync' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-arcada-labs-little-green-light-data-sync.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_arcada_labs_little_green_light_data_sync() {

	$plugin = new Arcada_Labs_Little_Green_Light_Data_Sync();
	$plugin->run();

}
run_arcada_labs_little_green_light_data_sync();


if( ! class_exists( 'ArcadaLGLUpdateChecker' ) ) {

	class ArcadaLGLUpdateChecker{

		public $plugin_slug;
		public $version;
		public $cache_key;
		public $cache_allowed;

		public function __construct() {

			$this->plugin_slug = plugin_basename( __DIR__ );
			$this->version = '1.0.5';
			$this->cache_key = 'misha_custom_upd';
			$this->cache_allowed = false;

			add_filter( 'plugins_api', array( $this, 'info' ), 20, 3 );
			add_filter( 'site_transient_update_plugins', array( $this, 'update' ) );
			add_action( 'upgrader_process_complete', array( $this, 'purge' ), 10, 2 );

		}

		public function request(){

			$remote = get_transient( $this->cache_key );

			if( false === $remote || ! $this->cache_allowed ) {

				$remote = wp_remote_get(
					'https://clacks.arcadalabs.com/info.json',
					array(
						'timeout' => 10,
						'headers' => array(
							'Accept' => 'application/json'
						)
					)
				);

				if(
					is_wp_error( $remote )
					|| 200 !== wp_remote_retrieve_response_code( $remote )
					|| empty( wp_remote_retrieve_body( $remote ) )
				) {
					return false;
				}

				set_transient( $this->cache_key, $remote, DAY_IN_SECONDS );

			}

			$remote = json_decode( wp_remote_retrieve_body( $remote ) );

			return $remote;

		}


		function info( $res, $action, $args ) {

			// do nothing if you're not getting plugin information right now
			if( 'plugin_information' !== $action ) {
				return $res;
			}

			// do nothing if it is not our plugin
			if( $this->plugin_slug !== $args->slug ) {
				return $res;
			}

			// get updates
			$remote = $this->request();

			if( ! $remote ) {
				return $res;
			}

			$res = new stdClass();

			$res->name = $remote->name;
			$res->slug = $remote->slug;
			$res->version = $remote->version;
			$res->tested = $remote->tested;
			$res->requires = $remote->requires;
			$res->author = $remote->author;
			$res->author_profile = $remote->author_profile;
			$res->download_link = $remote->download_url;
			$res->trunk = $remote->download_url;
			$res->requires_php = $remote->requires_php;
			$res->last_updated = $remote->last_updated;

			$res->sections = array(
				'description' => $remote->sections->description,
				'installation' => $remote->sections->installation,
				'changelog' => $remote->sections->changelog
			);

			if( ! empty( $remote->banners ) ) {
				$res->banners = array(
					'low' => $remote->banners->low,
					'high' => $remote->banners->high
				);
			}

			return $res;

		}

		public function update( $transient ) {

			if ( empty($transient->checked ) ) {
				return $transient;
			}

			$remote = $this->request();

			if(
				$remote
				&& version_compare( $this->version, $remote->version, '<' )
				&& version_compare( $remote->requires, get_bloginfo( 'version' ), '<=' )
				&& version_compare( $remote->requires_php, PHP_VERSION, '<' )
			) {
				$res = new stdClass();
				$res->slug = $this->plugin_slug;
				$res->plugin = plugin_basename( __FILE__ ); // misha-update-plugin/misha-update-plugin.php
				$res->new_version = $remote->version;
				$res->tested = $remote->tested;
				$res->package = $remote->download_url;

				$transient->response[ $res->plugin ] = $res;

			}

			return $transient;

		}

		public function purge( $upgrader, $options ){

			if (
				$this->cache_allowed
				&& 'update' === $options['action']
				&& 'plugin' === $options[ 'type' ]
			) {
				// just clean the cache when new plugin version is installed
				delete_transient( $this->cache_key );
			}

		}


	}

	new ArcadaLGLUpdateChecker();

}
