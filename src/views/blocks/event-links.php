<?php
/**
 * Block: Event Links
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/blocks/event-links.php
 *
 * See more documentation about our Blocks Editor templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 4.7
 *
 */

// don't show on password protected posts
if ( post_password_required() ) {
	return;
}

$has_google_cal = $this->attr( 'hasGoogleCalendar' );
$has_ical       = $this->attr( 'hasiCal' );

$should_render = $has_google_cal || $has_ical;

$plugin = tribe( \Tribe\Extensions\OutlookExportButtons\Plugin::class );

remove_filter( 'the_content', 'do_blocks', 9 );

if ( $should_render ) :
?>
	<div class="tribe-block tribe-block__events-link">
		<?php
		/**
		 * Filters whether the Outlook Live button should be shown or not.
		 */
		if ( apply_filters( 'tec_show_outlook_live_button', true ) ) :
		?>
		<div class="tribe-block__btn--link tribe-block__events-gcal">
			<a
				href="<?php echo $plugin->generate_outlook_full_url( 'live' ); ?>"
				title="<?php esc_attr_e( 'Add to Outlook Live Calendar', 'the-events-calendar' ); ?>"
				target="_blank"
			>
				<img src="<?php echo Tribe__Main::instance()->plugin_url  . 'src/modules/icons/link.svg'; ?>" />
				<?php esc_html_e( 'Outlook Live', 'tec-labs-outlook-export-buttons' ); ?>
			</a>
		</div>
		<?php endif; ?>
		<?php
		/**
		 * Filters whether the Outlook 365 button should be shown or not.
		 */
		if ( apply_filters( 'tec_show_outlook_365_button', true ) ) :
		?>
		<div class="tribe-block__btn--link tribe-block__events-gcal">
			<a
				href="<?php echo $plugin->generate_outlook_full_url( 'office' ); ?>"
				title="<?php esc_attr_e( 'Add to Outlook 365 Calendar', 'the-events-calendar' ); ?>"
				target="_blank"
			>
				<img src="<?php echo Tribe__Main::instance()->plugin_url  . 'src/modules/icons/link.svg'; ?>" />
				<?php esc_html_e( 'Outlook 365', 'tec-labs-outlook-export-buttons' ); ?>
			</a>
		</div>
		<?php endif; ?>
		<?php if ( $has_google_cal ) : ?>
			<div class="tribe-block__btn--link tribe-block__events-gcal">
				<a
					href="<?php echo Tribe__Events__Main::instance()->esc_gcal_url( tribe_get_gcal_link() ); ?>"
					title="<?php esc_attr_e( 'Add to Google Calendar', 'the-events-calendar' ); ?>"
				>
					<img src="<?php echo Tribe__Main::instance()->plugin_url  . 'src/modules/icons/link.svg'; ?>" />
					<?php echo esc_html( $this->attr( 'googleCalendarLabel' ) ) ?>
				</a>
			</div>
		<?php endif; ?>
		<?php if ( $has_ical ) : ?>
			<div class="tribe-block__btn--link tribe-block__-events-ical">
				<a
					href="<?php echo esc_url( tribe_get_single_ical_link() ); ?>"
					title="<?php esc_attr_e( 'Download .ics file', 'the-events-calendar' ); ?>"
				>
					<img src="<?php echo Tribe__Main::instance()->plugin_url  . 'src/modules/icons/link.svg'; ?>" />
					<?php echo esc_html( $this->attr( 'iCalLabel' ) ) ?>
				</a>
			</div>
		<?php endif; ?>
	</div>
<?php endif; ?>

<?php add_filter( 'the_content', 'do_blocks', 9 );
