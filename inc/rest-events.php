<?php
/**
 * The Events Calendar REST: give event descriptions bare <p> tags again.
 *
 * Since WP 6.9/7.x paragraphs render as <p class="wp-block-paragraph">.
 * The Angular frontend (calendarDescription pipe) looks for a literal <p>
 * in tribe/events/v1/events -> description, so descriptions came through
 * empty. Only the REST response is changed, not the stored content.
 *
 * This code also lives in the Code Snippets plugin as snippet #8
 * ("Agenda: kale <p> in REST description"). Once this theme version is
 * deployed, that snippet can be deactivated; running both is harmless
 * because the second pass finds nothing left to replace.
 *
 * @package LifeSites2021
 */

defined( 'ABSPATH' ) || exit;

add_filter(
	'tribe_rest_event_data',
	function ( $data ) {
		if ( ! empty( $data['description'] ) && is_string( $data['description'] ) ) {
			$data['description'] = preg_replace( '/<p\s[^>]*>/i', '<p>', $data['description'] );
		}
		return $data;
	}
);
