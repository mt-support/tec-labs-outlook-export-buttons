<?php
/**
 * Plugin Name:       The Events Calendar Extension: Outlook Export Buttons
 * Plugin URI:        https://theeventscalendar.com/extensions/outlook-export-buttons
 * GitHub Plugin URI: https://github.com/mt-support/tec-labs-outlook-export-buttons
 * Description:       The extension adds Export to Outlook Live / 365 buttons to the single event page.
 * Version:           1.1.3
 * Author:            The Events Calendar
 * Author URI:        https://evnt.is/1971
 * License:           GPL version 3 or any later version
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       tec-labs-outlook-export-buttons
 *
 *     This plugin is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation, either version 3 of the License, or
 *     any later version.
 *
 *     This plugin is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *     GNU General Public License for more details.
 */

/**
 * Resource for Office 365 API
 * https://docs.microsoft.com/en-us/previous-versions/office/office-365-api/api/version-2.0/calendar-rest-operations#CreateEvents
 */

/**
 * Define the base file that loaded the plugin for determining plugin path and other variables.
 *
 * @since 1.0.0
 *
 * @var string Base file that loaded the plugin.
 */
define( 'TRIBE_EXTENSION_OUTLOOK_EXPORT_BUTTONS_FILE', __FILE__ );

/**
 * Register and load the service provider for loading the extension.
 *
 * @since 1.0.0
 */
function tribe_extension_outlook_export_buttons() {
	// When we dont have autoloader from common we bail.
	if  ( ! class_exists( 'Tribe__Autoloader' ) ) {
		return;
	}

	// Register the namespace so we can the plugin on the service provider registration.
	Tribe__Autoloader::instance()->register_prefix(
		'\\Tribe\\Extensions\\OutlookExportButtons\\',
		__DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Tribe',
		'outlook-export-buttons'
	);

	// Deactivates the plugin in case of the main class didn't autoload.
	if ( ! class_exists( '\Tribe\Extensions\OutlookExportButtons\Plugin' ) ) {
		tribe_transient_notice(
			'outlook-export-buttons',
			'<p>' . esc_html__( 'Couldn\'t properly load "The Events Calendar Extension: Outlook Export Buttons", the extension was deactivated.', 'tec-labs-outlook-export-buttons' ) . '</p>',
			[],
			// 1 second after that make sure the transient is removed.
			1 
		);

		if ( ! function_exists( 'deactivate_plugins' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		deactivate_plugins( __FILE__, true );
		return;
	}

	tribe_register_provider( '\Tribe\Extensions\OutlookExportButtons\Plugin' );
}

// Loads after common is already properly loaded.
add_action( 'tribe_common_loaded', 'tribe_extension_outlook_export_buttons' );
