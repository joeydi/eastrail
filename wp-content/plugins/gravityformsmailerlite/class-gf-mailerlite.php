<?php

// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Gravity Forms MailerLite Add-on.
 *
 * @since 1.0.0
 * @package GravityForms
 * @author Rocketgenius
 * @copyright Copyright (c) 2024, Rocketgenius
 */

// Include the Gravity Forms Feed Add-on Framework.
GFForms::include_feed_addon_framework();

/**
 * Initialize the MailerLite feeds and API.
 */
class GF_MailerLite extends GFFeedAddOn {

	// setting up the initial instances, paths, ability to connect

	/**
	 * Contains an instance of this class, if available.
	 *
	 * @since   1.0.0
	 *
	 * @var     GF_MailerLite $_instance If available, contains an instance of this class.
	 */
	private static $_instance = null;

	/**
	 * Defines the version of the MailerLite Add-On.
	 *
	 * @since   1.0.0
	 *
	 * @var     string $_version Contains the version, defined from mailerlite.php
	 */
	protected $_version = GF_MAILERLITE_VERSION;

	/**
	 * Defines the minimum Gravity Forms version required.
	 *
	 * @since   1.0.0
	 *
	 * @var     string $_min_gravityforms_version The minimum version required.
	 */
	protected $_min_gravityforms_version = '2.8';

	/**
	 * Defines the plugin slug.
	 *
	 * @since   1.0.0
	 *
	 * @var     string $_slug The slug used for this plugin.
	 */
	protected $_slug = 'gravityformsmailerlite';

	/**
	 * Defines the main plugin file.
	 *
	 * @since   1.0.0
	 *
	 * @var     string $_path The path to the main plugin file, relative to the plugins folder.
	 */
	protected $_path = 'gravityformsmailerlite/mailerlite.php';

	/**
	 * Defines the full path to this class file.
	 *
	 * @since   1.0.0
	 *
	 * @var     string $_full_path The full path.
	 */
	protected $_full_path = __FILE__;

	/**
	 * Defines the URL where this Add-on can be found.
	 *
	 * @since   1.0.0
	 *
	 * @var     string $_url The URL of the Add-on.
	 */
	protected $_url = 'https://www.gravityforms.com';

	/**
	 * Defines the title of this Add-on.
	 *
	 * @since   1.0.0
	 *
	 * @var     string $_title The title of the Add-on.
	 */
	protected $_title = 'Gravity Forms MailerLite Add-On';

	/**
	 * Defines the short title of the Add-on.
	 *
	 * @since   1.0.0
	 *
	 * @var     string $_short_title The short title.
	 */
	protected $_short_title = 'MailerLite';

	/**
	 * Defines if Add-on should use Gravity Forms servers for update data.
	 *
	 * @since   1.0.0
	 *
	 * @var     bool
	 */
	protected $_enable_rg_autoupgrade = true;

	/**
	 * Defines the capabilities needed for the MailerLite Add-on
	 *
	 * @since   1.0.0
	 *
	 * @var     array $_capabilities The Capabilities needed for the Add-on
	 */
	protected $_capabilities = array( 'gravityforms_mailerlite', 'gravityforms_mailerlite_uninstall' );

	/**
	 * Defines the capability needed to access the Add-On settings page.
	 *
	 * @since   1.0.0
	 *
	 * @var     string $_capabilities_settings_page The capability needed to access the Add-On settings page.
	 */
	protected $_capabilities_settings_page = 'gravityforms_mailerlite';

	/**
	 * Defines the capability needed to access the Add-on form settings page.
	 *
	 * @since   1.0.0
	 *
	 * @var     string $_capabilities_form_settings The capability needed to access the Add-on form settings page.
	 */
	protected $_capabilities_form_settings = 'gravityforms_mailerlite';

	/**
	 * Defines the capability needed to uninstall the Add-on.
	 *
	 * @since   1.0.0
	 *
	 * @var     string $_capabilities_uninstall The capability needed to uninstall the Add-on.
	 */
	protected $_capabilities_uninstall = 'gravityforms_mailerlite_uninstall';

	/**
	 * Contains an instance of the MailerLite API class.
	 *
	 * @since   1.0.0
	 * @access  private
	 * @var     $_api   //if available, contains an instance of the MailerLite API class.
	 */
	public $api = null;

	/**
	 * Enable background feed processing to prevent performance issues delaying form submission completion.
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	protected $_async_feed_processing = true;

	/**
	 * Get an instance of this class.
	 *
	 * @since   1.0.0
	 * @access  public
	 *
	 * @return GF_MailerLite $_instance An instance of the GF_Mailerlite class
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new GF_MailerLite();
		}

		return self::$_instance;

	}

	/**
	 * Plugin starting point. Handles hooks, loading of language files and PayPal delayed payment support.
	 *
	 * @since   1.0.0
	 * @access  public
	 *
	 * @uses    GFFeedAddOn::add_delayed_payment_support()
	 */
	public function init() {

		parent::init();

		$this->add_delayed_payment_support(
			array(
				'option_label' => esc_html__( 'Subscribe user to MailerLite only when payment is received.', 'gravityformsmailerlite' ),
			)
		);
	}

	// # PLUGIN SETTINGS ------------------------------------------------

	/**
	 * Configures the settings which should be rendered on the add-on settings tab.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function plugin_settings_fields() {
		return array(
			array(
				'description' => '<p>' . esc_html__( 'MailerLite is an affordable, easy-to-use email marketing platform for anyone with an audience.', 'gravityformsmailerlite' ) . ' ' . sprintf( esc_html__( 'Go to %s to sign up.', 'gravityformsmailerlite' ), sprintf( '<a href="%s" target="_blank">%s</a>', 'https://www.mailerlite.com', esc_html__( 'MailerLite.com', 'gravityformsmailerlite' ) ) ) . '</p>',
				'fields'      => array(
					array(
						'name'              => 'api_key',
						'tooltip'           => esc_html__( 'Enter your MailerLite API key, which can be retrieved when you log in to MailerLite.com.', 'gravityformsmailerlite' ),
						'label'             => esc_html__( 'MailerLite API Key', 'gravityformsmailerlite' ),
						'type'              => 'text',
						'class'             => 'medium',
						'feedback_callback' => array( $this, 'initialize_api' ),
					),
				),
			),
		);

	}

	/**
	 * Clears the cached groups and fields when the plugin settings are saved.
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings The settings to be saved.
	 *
	 * @return void
	 */
	public function update_plugin_settings( $settings ) {
		GFCache::delete( $this->get_slug() . '_groups' );
		GFCache::delete( $this->get_slug() . '_fields' );
		parent::update_plugin_settings( $settings );
	}

	/**
	 * Initializes the MailerLite API if API credentials are valid.
	 *
	 * @since 1.0.0
	 *
	 * @return bool|null API Initialize state, returns null if no API is provided.
	 */
	public function initialize_api() {

		// If the API is already initialized, return true.
		if ( ! is_null( $this->api ) ) {
			return is_object( $this->api );
		}

		// Initialize the MailerLite API Library.
		if ( ! class_exists( 'GF_MailerLite_API' ) ) {
			require_once 'includes/class-gf-mailerlite-api.php';
		}

		// Log validation step.
		$this->log_debug( __METHOD__ . '(): Validating API key.' );

		$api_key = $this->get_plugin_setting( 'api_key' );

		if ( rgblank( $api_key ) ) {
			return null;
		}

		$ml       = new GF_MailerLite_API( $api_key );
		$response = $ml->is_api_key_valid();

		if ( is_wp_error( $response ) ) {
			$this->api = false;
			$this->log_error( __METHOD__ . '(): API key failed authentication; ' . $response->get_error_message() );

			return false;
		}

		$this->log_debug( __METHOD__ . '(): API key successfully authenticated.' );
		$this->api = $ml;

		return true;

	}

	// # FEED SETTINGS --------------------------------------------------

	/**
	 * Define feed settings fields.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function feed_settings_fields() {

		$settings = array(
			array(
				'title'       => esc_html__( 'MailerLite Feed Settings', 'gravityformsmailerlite' ),
				'description' => '<p>' . esc_html__( 'Create a MailerLite feed to integrate with your MailerLite account.', 'gravityformsmailerlite' ) . '</p>',
				'fields'      => array(
					array(
						'name'     => 'feedName',
						'label'    => esc_html__( 'Name', 'gravityformsmailerlite' ),
						'type'     => 'text',
						'required' => true,
						'class'    => 'medium',
						'tooltip'  => sprintf(
							'<strong>%s</strong>%s',
							esc_html__( 'Name', 'gravityformsmailerlite' ),
							esc_html__( 'Enter a feed name to uniquely identify this setup.', 'gravityformsmailerlite' ),
						),
					),
				),
			),
			array(
				'fields'     => array(
					array(
						'name'       => 'mailerliteGroup',
						'label'      => esc_html__( 'MailerLite Group', 'gravityformsmailerlite' ),
						'type'       => 'select',
						'choices'    => $this->get_groups_as_choices(),
						'no_choices' => esc_html__( 'No MailerLite groups found.', 'gravityformsmailerlite' ),
						'tooltip'    => sprintf(
							'<strong>%s</strong>%s',
							esc_html__( 'MailerLite Group', 'gravityformsmailerlite' ),
							esc_html__( 'Select the MailerLite group you would like the contact to be subscribed to. Groups can be created in your MailerLite account.', 'gravityformsmailerlite' ),
						),
					),
					array(
						'name'          => 'mappedStandardFields',
						'label'         => esc_html__( 'Map Fields', 'gravityformsmailerlite' ),
						'type'  	    => 'field_map',
						'field_map'      => $this->standard_fields_for_feed_mapping(),
						'tooltip'       => sprintf(
							'<strong>%s</strong>%s',
							esc_html__( 'Map Fields', 'gravityformsmailerlite' ),
							esc_html__( 'Associate your MailerLite merge tags to the appropriate Gravity Form fields by selecting the appropriate form field from the list.', 'gravityformsmailerlite' ),
						),
					),
					array(
						'name'          => 'mappedFields',
						'type'          => 'generic_map',
						'key_field'     => array(
							'choices' => $this->get_ml_fields_as_choices(),
						),
						'value_field'   => array(
							'allow_custom' => true,
						),
						'save_callback' => array( $this, 'create_new_custom_fields' ),
					),
					array(
						'name'    => 'optinCondition',
						'label'   => esc_html__( 'Conditional Logic', 'gravityformsmailerlite' ),
						'type'    => 'feed_condition',
						'tooltip' => sprintf(
							'<strong>%s</strong>%s',
							esc_html__( 'Conditional Logic', 'gravityformsmailerlite' ),
							esc_html__( 'When enabled, the contact will only be subscribed to MailerLite if the conditions are met. When disabled, the contact will always be subscribed to the selected group.', 'gravityformsmailerlite' ),
						),
					),
					array( 'type' => 'save' ),
				),
			),
		);

		return $settings;

	}

	/**
	 * Return the choices for the group feed setting.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @return array Group choices.
	 */
	public function get_groups_as_choices() {
		$groups = $this->get_ml_groups();
		if ( empty( $groups ) ) {
			return array();
		}

		$choices = array(
			array(
				'label' => esc_html__( 'Select a MailerLite Group', 'gravityformsmailerlite' ),
				'value' => '',
			),
		);

		foreach ( $groups as $group ) {
			$choices[] = array(
				'label' => esc_html( $group['name'] ),
				'value' => esc_attr( $group['id'] ),
			);
		}

		return $choices;
	}

	/**
	 * Prepare standard fields for mapping.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return array
	 */
	public function standard_fields_for_feed_mapping() {
		return array(
			array(
				'name'     => 'email',
				'value'    => 'email',
				'label'    => esc_html__( 'Email', 'gravityformsmailerlite' ),
				'required' => true,
				'field_types' => array( 'email', 'hidden' ),
			),
			array(
				'name'     => 'name',
				'value'    => 'name',
				'label'    => esc_html__( 'First Name', 'gravityformsmailerlite' ),
				'required' => false,
				'field_types' => array( 'name', 'text', 'hidden' ),
			),
			array(
				'name'     => 'last_name',
				'value'    => 'last_name',
				'label'    => esc_html__( 'Last Name', 'gravityformsmailerlite' ),
				'required' => false,
				'field_types' => array( 'name', 'text', 'hidden' ),
			),
		);
	}

	/**
	 * Return an array of MailerLite Subscription fields which can be mapped to the Form fields/entry meta.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array
	 */
	public function get_ml_fields_as_choices() {

		$fields = $this->get_ml_fields();
		if ( empty( $fields ) ) {
			return array();
		}

		usort(
			$fields,
			function ( $a, $b ) {
				return strnatcasecmp( $a['name'], $b['name'] );
			}
		);

		foreach ( $fields as $field ) {
			if ( in_array( $field['key'], array( 'email', 'name', 'last_name' ) ) ) {
				continue;
			}

			switch ( $field['type'] ) {
				case 'number':
					$field_type = array( 'number' );
					break;
				case 'date':
					$field_type = array( 'date', 'hidden' );
					break;
				default:
					$field_type = null;
					break;
			}

			$field_map[] = array(
				'label'       => $field['name'],
				'name'        => $field['key'],
				'value'       => $field['key'],
				'field_types' => $field_type,
			);
		}

		return $field_map;
	}

	/**
	 * Creates new custom fields when the feed settings are saved.
	 *
	 * @since  1.0.0
	 *
	 * @param \Gravity_Forms\Gravity_Forms\Settings\Fields\Generic_Map $setting_field The setting field object.
	 * @param array                                                    $field_value   The posted field value.
	 *
	 * @return array
	 */
	public function create_new_custom_fields( $setting_field, $field_value ) {
		if ( empty( $setting_field->key_field ) || empty( $field_value ) || ! $this->initialize_api() ) {
			return $field_value;
		}

		$new_choices = array();

		foreach ( $field_value as $key => &$field ) {
			if ( empty( $field['custom_key'] ) ) {
				continue;
			}

			$custom_key = trim( $field['custom_key'] );
			if ( empty( $custom_key ) ) {
				unset( $field_value[ $key ] );
			}

			//phpcs:ignore
			$new_field = $this->api->create_field( array( 'name' => $custom_key, 'type' => 'text' ) );
			if ( is_wp_error( $new_field ) ) {
				$this->log_error( __METHOD__ . "(): Unable to create custom field '{$field['custom_key']}'; " . $new_field->get_error_message() );
				continue;
			}

			// Clearing the custom field key.
			$field['key']        = $new_field['key'];
			$field['custom_key'] = '';

			// Adding to the key field choices, so it will be selected when the setting is rendered.
			$new_choices[] = array(
				'label' => $new_field['name'],
				'name'  => $field['key'],
				'value' => $field['key'],
			);

			$this->log_debug( __METHOD__ . "(): New field '{$new_field['name']}' created." );
		}

		if ( ! empty( $new_choices ) ) {
			$setting_field->key_field['choices'] = array_merge( $setting_field->key_field['choices'], $new_choices );
			usort(
				$setting_field->key_field['choices'],
				function ( $a, $b ) {
					return strnatcasecmp( $a['label'], $b['label'] );
				}
			);
			GFCache::delete( $this->get_slug() . '_fields' );
		}

		return $field_value;
	}

	// # FEED LIST ------------------------------------------------------

	/**
	 * Enable feed creation.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function can_create_feed() {
		return $this->initialize_api();
	}

	/**
	 * Return the plugin's icon for the plugin/form settings menu.
	 *
	 * @since 1.0.0
	 *
	 * @return string For newer versions of Gravity, return the CSS class associated with the icon. Otherwise, URL to the local icon file.
	 */
	public function get_menu_icon() {

		return $this->is_gravityforms_supported( '2.8.8.1' ) ? 'gform-icon--mailerlite' : file_get_contents( $this->get_base_path() . '/images/menu-icon.svg' );

	}

	/**
	 * Enable feed duplication.
	 *
	 * @since 1.0.0
	 *
	 * @param int|array $id The ID of the feed to be duplicated or the feed object when duplicating a form.
	 *
	 * @return bool
	 */
	public function can_duplicate_feed( $id ) {
		return true;
	}

	/**
	 * Define the feed list table columns.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function feed_list_columns() {
		return array(
			'feedName'              => esc_html__( 'Name', 'gravityformsmailerlite' ),
			'mailerlite_group_name' => esc_html__( 'MailerLite Group', 'gravityformsmailerlite' ),
		);
	}

	/**
	 * Returns the value to be displayed in the MailerLite Group column.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $feed The feed currently being processed.
	 *
	 * @return string
	 */
	public function get_column_value_mailerlite_group_name( $feed ) {
		$group_id  = rgars( $feed, 'meta/mailerliteGroup' );
		$cache_key = $this->get_slug() . '_group_' . $group_id . '_name';
		$name      = GFCache::get( $cache_key );

		if ( empty ( $group_id ) && empty( $name ) ) {
			return ( esc_html__( 'None', 'gravityformsmailerlite' ) );
		}

		if ( ! empty( $name ) ) {
			return $name;
		}

		// If unable to initialize API, return the group ID.
		if ( ! $this->initialize_api() ) {
			return $group_id;
		}

		// Get the group.
		$group = $this->api->get_group( $group_id );

		if ( is_wp_error( $group ) ) {
			$this->log_error( __METHOD__ . "(): Unable to retrieve MailerLite group {$group_id}. " . $group->get_error_message() );

			return $group_id;
		}

		$name = rgar( $group, 'name' );
		GFCache::set( $cache_key, $name, true, 0.5 * MINUTE_IN_SECONDS );

		return $name;
	}

	// # FEED PROCESSING ------------------------------------------------

	/**
	 * Processes a feed for the given form and entry.
	 *
	 * @since  1.0.0
	 * @since  1.1.0 Updated return value for consistency with other add-ons, so the framework can save the feed status to the entry meta.
	 *
	 * @access public
	 *
	 * @param array $feed  The feed object to be processed.
	 * @param array $entry The entry object currently being processed.
	 * @param array $form  The form object currently being processed.
	 *
	 * @return WP_Error|array
	 */
	public function process_feed( $feed, $entry, $form ) {

		// Log that we are processing the feed.
		$this->log_debug( __METHOD__ . '(): Processing feed: ' . rgar( $feed, 'id' ) . '.' );

		// If unable to initialize API, log error and return.
		if ( ! $this->initialize_api() ) {
			$this->add_feed_error( esc_html__( 'Unable to process feed because the API could not be initialized.', 'gravityformsmailerlite' ), $feed, $entry, $form );

			return new WP_Error( 'api_not_initialized', 'API was not initialized.' );
		}

		// Get mapped email address.
		$email = $this ->get_field_value( $form, $entry, rgar( $feed['meta'], 'mappedStandardFields_email' ) );

		// If email address is invalid, log error and return.
		if ( GFCommon::is_invalid_or_empty_email( $email ) ) {
			$this->add_feed_error( esc_html__( 'A valid email address must be provided.', 'gravityformsmailerlite' ), $feed, $entry, $form );

			return new WP_Error( 'invalid_email', 'Invalid email address.' );
		}

		$subscriber = array(
			'email'  => $email,
			'fields' => $this->get_subscriber_fields( $entry, $feed, $form ),
		);

		$invalid_group_message = esc_html__( 'Group (ID: %s) does not exist in the connected MailerLite account. Please be sure to update your feeds.', 'gravityformsmailerlite' );
		$group                 = rgars( $feed, 'meta/mailerliteGroup' );

		if ( ! empty( $group ) ) {
			$existing_groups = $this->get_ml_groups();
			$group_exists    = false;

			foreach ( $existing_groups as $existing_group ) {
				if ( rgar( $existing_group, 'id' ) == $group ) {
					$group_exists = true;
					break;
				}
			}

			if ( $group_exists ) {
				$subscriber['groups'] = array( $group );
			} else {
				$this->add_feed_error( sprintf( $invalid_group_message, $group ), $feed, $entry, $form );
			}
		}

		// Getting subscriber details.
		$existing_subscriber = $this->api->get_subscriber( $email );

		if ( is_wp_error( $existing_subscriber ) ) {
			$this->log_debug( __METHOD__ . sprintf( '(): Unable to retrieve subscriber (%s). %s', $subscriber['email'], $existing_subscriber->get_error_message() ) );
			$existing_subscriber = array();
		}

		$filter_args = array( 'gform_mailerlite_subscriber', rgar( $form, 'id' ) );
		if ( gf_has_filters( $filter_args ) ) {
			$this->log_debug( __METHOD__ . '(): Executing functions hooked to gform_mailerlite_subscriber.' );
			/**
			 * Allows the subscriber properties to be overridden before the upsert request is made.
			 *
			 * @since 1.0
			 *
			 * @param array $subscriber          The subscriber properties.
			 * @param array $existing_subscriber The subscribers existing properties or an empty array if there isn't a subscriber with the given email address.
			 * @param array $feed                The feed currently being processed.
			 * @param array $form                The form currently being processed.
			 * @param array $entry               The entry currently being processed.
			 */
			$subscriber = gf_apply_filters( $filter_args, $subscriber, $existing_subscriber, $feed, $form, $entry );
		}

		$action = empty( $existing_subscriber ) ? 'added' : 'updated';
		$this->log_debug( __METHOD__ . "(): Subscriber to be {$action}: " . print_r( $subscriber, true ) );

		// Adding new subscriber to group.
		$result = $this->api->upsert_subscriber( $subscriber );
		if ( is_wp_error( $result ) && $result->get_error_message() === "The selected groups.0 is invalid." ) {
			$this->add_feed_error( sprintf( $invalid_group_message, $subscriber['groups'][0] ), $feed, $entry, $form );
			unset( $subscriber['groups'] );
			$result = $this->api->upsert_subscriber( $subscriber );
		}

		if ( is_wp_error( $result ) ) {
			$this->add_feed_error( sprintf( esc_html__( 'Unable to upsert subscriber. %s', 'gravityformsmailerlite' ), $result->get_error_message() ), $feed, $entry, $form );

			$error_data = $result->get_error_data();
			if ( ! empty( $error_data ) ) {
				$this->log_error( __METHOD__ . '(): Errors: ' . print_r( $error_data, true ) );
			}

			return $result;
		}

		$this->log_debug( __METHOD__ . '(): Result => ' . json_encode( $result, true ) );

		$note = empty( $existing_subscriber ) ? esc_html__( 'Subscriber added. ID: %s.', 'gravityformsmailerlite' ) : esc_html__( 'Subscriber updated. ID: %s.', 'gravityformsmailerlite' );
		$this->add_note( rgar( $entry, 'id' ), sprintf( $note, rgar( $result, 'id' ) ), 'success' );

		return $entry;
	}

	/**
	 * Prepares the mapped fields for addition to the subscriber.
	 *
	 * @since 1.0.0
	 *
	 * @param array $entry The entry currently being processed.
	 * @param array $feed  The feed currently being processed.
	 * @param array $form  The form currently being processed.
	 *
	 * @return array
	 */
	public function get_subscriber_fields( $entry, $feed, $form ) {
		$fields = array(
			'name'      => $this->get_field_value( $form, $entry, rgar( $feed['meta'], 'mappedStandardFields_name' ) ),
			'last_name' => $this->get_field_value( $form, $entry, rgar( $feed['meta'], 'mappedStandardFields_last_name' ) ),
		);

		$field_map = $this->get_generic_map_fields( $feed, 'mappedFields', $form, $entry );

		foreach ( $field_map as $key => $value ) {
			if ( 'email' === $key || rgblank( $value ) ) {
				continue;
			}

			$field = $this->get_ml_field( $key );
			if ( rgar( $field, 'type' ) === 'date' ) {
				$field = GFFormsModel::get_field( $form, $this->get_mapped_field_id( $feed, $key ) );
				if ( ! $field instanceof GF_Field_Date ) {
					$timestamp = strtotime( $value );
					if ( ! $timestamp ) {
						continue;
					}
					$value = gmdate( 'Y-m-d', $timestamp );
				}
			} elseif ( rgar( $field, 'type' ) === 'text' ) {
				$value = substr( $value, 0, 1024 );
			}

			$fields[ $key ] = $value;
		}

		return $fields;
	}

	// # HELPER METHODS -------------------------------------------------

	/**
	 * Gets the groups from the MailerLite API.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_ml_groups() {
		$cache_key = $this->get_slug() . '_groups';
		$groups    = GFCache::get( $cache_key );

		if ( ! empty( $groups ) ) {
			return $groups;
		}

		if ( ! $this->initialize_api() ) {
			return array();
		}

		$groups = $this->api->get_groups();
		if ( is_wp_error( $groups ) ) {
			$this->log_error( __METHOD__ . '(): Unable to retrieve MailerLite groups from API. ' . $groups->get_error_message() );

			return array();
		}

		if ( empty( $groups ) ) {
			return array();
		}

		GFCache::set( $cache_key, $groups, true, 0.5 * MINUTE_IN_SECONDS );

		return $groups;
	}

	/**
	 * Gets the fields from the MailerLite API.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_ml_fields() {
		$cache_key = $this->get_slug() . '_fields';
		$fields    = GFCache::get( $cache_key );

		if ( ! empty( $fields ) ) {
			return $fields;
		}

		if ( ! $this->initialize_api() ) {
			return array();
		}

		$fields = $this->api->get_fields();
		if ( is_wp_error( $fields ) ) {
			$this->log_error( __METHOD__ . '(): Unable to retrieve MailerLite fields from API. ' . $fields->get_error_message() );

			return array();
		}

		if ( empty( $fields ) ) {
			return array();
		}

		GFCache::set( $cache_key, $fields, true, 2 * MINUTE_IN_SECONDS );

		return $fields;
	}

	/**
	 * Gets a specific MailerLite field.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The field key.
	 *
	 * @return array
	 */
	public function get_ml_field( $key ) {
		$cache_key = $this->get_slug() . '_field_' . $key;
		$field     = GFCache::get( $cache_key );

		if ( ! empty( $field ) ) {
			return $field;
		}

		$fields = $this->get_ml_fields();
		if ( empty( $fields ) ) {
			return array();
		}

		foreach ( $fields as $field ) {
			if ( rgar( $field, 'key' ) === $key ) {
				GFCache::set( $cache_key, $field, true, 2 * MINUTE_IN_SECONDS );

				return $field;
			}
		}

		return array();
	}

	/**
	 * Returns the ID of the form field, input, or entry property mapped to the specified key.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $feed The feed currently being processed.
	 * @param string $key  The key of the mapped field.
	 *
	 * @return string
	 */
	public function get_mapped_field_id( $feed, $key ) {
		static $ids = array();

		if ( ! empty( $ids ) ) {
			return rgar( $ids, $key );
		}

		$fields = rgars( $feed, 'meta/mappedFields', array() );
		if ( empty( $fields ) ) {
			return '';
		}

		foreach ( $fields as $field ) {
			$ids[ rgar( $field, 'key' ) ] = rgar( $field, 'value', '' );
		}

		return rgar( $ids, $key, '' );
	}

}
