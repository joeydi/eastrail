<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://arcadalabs.com
 * @since      1.0.0
 *
 * @package    Arcada_Labs_Little_Green_Light_Data_Sync
 * @subpackage Arcada_Labs_Little_Green_Light_Data_Sync/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Arcada_Labs_Little_Green_Light_Data_Sync
 * @subpackage Arcada_Labs_Little_Green_Light_Data_Sync/includes
 * @author     Arcada Labs <hello@arcadalabs.com>
 */
class Arcada_Labs_Little_Green_Light_Data_Sync_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'arcada-labs-little-green-light-data-sync',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
