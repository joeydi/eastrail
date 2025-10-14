<?php

/**
Plugin Name: Gravity Forms MailerLite Add-On
Plugin URI: https://gravityforms.com
Description: Integrates Gravity Forms with MailerLite, allowing users to automatically be subscribed to MailerLite on form submission.
Version: 1.1.1
Author: Gravity Forms
Author URI: https://gravityforms.com
License: GPL-2.0+
Text Domain: gravityformsmailerlite
Domain Path: /languages

------------------------------------------------------------------------
Copyright 2024-2025 Rocketgenius Inc.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see http://gnu.org/licenses.
 **/

// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

define( 'GF_MAILERLITE_VERSION', '1.1.1' );

// If Gravity Forms is loaded, bootstrap the MailerLite Add-On.
add_action( 'gform_loaded', array( 'GF_MailerLite_Bootstrap', 'load' ), 5 );

/**
 * Class GF_MailerLite_Bootstrap
 *
 * Handles the loading of the MailerLite Add-On and registers with the Add-On Framework.
 */

class GF_MailerLite_Bootstrap {

	/**
	 * If the Feed Add-On Framework exists, MailerLite Add-On is loaded.
	 *
	 * @access public
	 * @static
	 */
	public static function load() {

		if ( ! method_exists( 'GFForms', 'include_feed_addon_framework' ) ) {
			return;
		}

		require_once( 'class-gf-mailerlite.php' );

		GFAddOn::register( 'GF_MailerLite' );
	}
}

/**
 * Returns the instance of the MailerLite class.
 *
 * @see    GF_MailerLite::get_instance()
 *
 * @return GF_MailerLite
 */

function gf_mailerlite() {
	return GF_MailerLite::get_instance();
}
