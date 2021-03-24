<?php

namespace Tribe\Extensions\OutlookExportButtons;

use tad_DI52_ServiceProvider;

/**
 * Class Plugin
 *
 * @package Tribe\Extensions\OutlookExportButtons
 * @since   1.0.0
 *
 */
class Plugin extends tad_DI52_ServiceProvider {
	/**
	 * Stores the version for the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const VERSION = '1.0.0';

	/**
	 * Stores the base slug for the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const SLUG = 'outlook-export-buttons';

	/**
	 * Stores the base slug for the extension.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const FILE = TRIBE_EXTENSION_OUTLOOK_EXPORT_BUTTONS_FILE;

	/**
	 * @since 1.0.0
	 *
	 * @var string Plugin Directory.
	 */
	public $plugin_dir;

	/**
	 * @since 1.0.0
	 *
	 * @var string Plugin path.
	 */
	public $plugin_path;

	/**
	 * @since 1.0.0
	 *
	 * @var string Plugin URL.
	 */
	public $plugin_url;

	/**
	 * Setup the Extension's properties.
	 *
	 * This always executes even if the required plugins are not present.
	 *
	 * @since 1.0.0
	 */
	public function register() {
		// Set up the plugin provider properties.
		$this->plugin_path = trailingslashit( dirname( static::FILE ) );
		$this->plugin_dir  = trailingslashit( basename( $this->plugin_path ) );
		$this->plugin_url  = plugins_url( $this->plugin_dir, $this->plugin_path );

		// Register this provider as the main one and use a bunch of aliases.
		$this->container->singleton( static::class, $this );
		$this->container->singleton( 'extension.outlook_export_buttons', $this );
		$this->container->singleton( 'extension.outlook_export_buttons.plugin', $this );
		$this->container->register( PUE::class );

		if ( ! $this->check_plugin_dependencies() ) {
			// If the plugin dependency manifest is not met, then bail and stop here.
			return;
		}

		// Start binds.

		add_filter( 'tribe_events_ical_single_event_links', [ $this, 'generate_outlook_markup' ], 10, 1 );

		// End binds.

		$this->container->register( Hooks::class );
		$this->container->register( Assets::class );
	}

	/**
	 * Generate the URL for the Outlook export buttons.
	 *
	 * @return string Part of the URL containing the event information.
	 */
	public function generate_outlook_add_url() {
		$event = tribe_get_event();

		$add_url = [];

		$add_url['base'] = 'calendar/0/deeplink/compose/?path=/calendar/action/compose&rru=addevent';

		$startdt            = $event->start_date_utc;
		$add_url['startdt'] = 'startdt=' . date( 'c', strtotime( $startdt ) );

		$enddt = $event->end_date_utc;

		/**
		 * If event is an all day event, then adjust the end time.
		 * Using the 'allday' parameter doesn't work well through time zones.
		 */
		if ( $event->all_day ) {
			$add_url['enddt'] =
				'enddt='
				. date( 'Y-m-d', strtotime( $enddt ) )
				. 'T'
				. date( 'H:i:s', strtotime( $startdt ) )
				. date( 'P', strtotime( $enddt ) );
		} else {
			$add_url['enddt'] = 'enddt=' . date( 'c', strtotime( $enddt ) );
		}

		$add_url['subject'] = 'subject=' . esc_html( $event->post_title );

		/**
		 * A filter to hide or show the event description
		 *
		 * @param bool $include_event_description
		 */
		$include_event_description = (bool) apply_filters( 'tribe_events_ical_outlook_include_event_description', true );

		/**
		 * Allows the filtering the length of the event description
		 *
		 * @param bool|int $num_words
		 */
		$num_words = apply_filters( 'tribe_events_ical_outlook_event_description_num_words', '20' );

		if ( $include_event_description ) {
			if ( ! $num_words ) {
				$add_url['body'] = 'body=' . esc_html( $event->post_content );
			} else {
				$add_url['body'] = 'body=' . wp_trim_words( esc_html( $event->post_content ), $num_words );
			}
		}

		$outlook_url = implode( '&', $add_url );

		return $outlook_url;
	}

	/**
	 * Generate the markup of the export buttons.
	 *
	 * @param $calendar_links
	 *
	 * @return string The full markup of the export buttons.
	 */
	public function generate_outlook_markup( $calendar_links ) {
		$outlook_add_url = $this->generate_outlook_add_url();

		// Outlook Live URL
		$outlook_live_url = 'https://outlook.live.com/' . $outlook_add_url;

		// Outlook 365 URL
		$outlook_365_url = 'https://outlook.office.com/' . $outlook_add_url;

		// Button markups
		$outlook_live_button = sprintf(
			'<a target="_blank" class="tribe-events-gcal tribe-events-outlook-live tribe-events-button" title="' . esc_attr__( 'Add to Outlook Live Calendar', 'tec-labs-outlook-export-buttons' ) . '" href="%1$s">%2$s</a>',
			$outlook_live_url,
			esc_html( '+ Outlook Live', 'tec-labs-outlook-export-buttons' )
		);
		$outlook_365_button  = sprintf(
			'<a target="_blank" class="tribe-events-gcal tribe-events-outlook-365 tribe-events-button" title="' . esc_attr__( 'Add to Outlook 365 Calendar', 'tec-labs-outlook-export-buttons' ) . '" href="%1$s">%2$s</a>',
			$outlook_365_url,
			esc_html( '+ Outlook 365', 'tec-labs-outlook-export-buttons' )
		);

		// Get the position of the opening div
		$opening_div_end = strpos( $calendar_links, '>' ) + 1;

		// Inject the Outlook export buttons at the beginning.
		$new_calendar_links =
			substr( $calendar_links, 0, $opening_div_end )
			. $outlook_live_button
			. $outlook_365_button
			. substr( $calendar_links, $opening_div_end );

		return $new_calendar_links;
	}

	/**
	 * Checks whether the plugin dependency manifest is satisfied or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Whether the plugin dependency manifest is satisfied or not.
	 */
	protected
	function check_plugin_dependencies() {
		$this->register_plugin_dependencies();

		return tribe_check_plugin( static::class );
	}

	/**
	 * Registers the plugin and dependency manifest among those managed by Tribe Common.
	 *
	 * @since 1.0.0
	 */
	protected
	function register_plugin_dependencies() {
		$plugin_register = new Plugin_Register();
		$plugin_register->register_plugin();

		$this->container->singleton( Plugin_Register::class, $plugin_register );
		$this->container->singleton( 'extension.outlook_export_buttons', $plugin_register );
	}
}
