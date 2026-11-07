<?php
/**
 * Functions which enhance the theme by hooking into WordPress
 *
 * @package LifeSites2021
 */

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function lifesites2021_body_classes( $classes ) {
	// Adds a class of hfeed to non-singular pages.
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}

	// Adds a class of no-sidebar when there is no sidebar present.
	if ( ! is_active_sidebar( 'sidebar-1' ) ) {
		$classes[] = 'no-sidebar';
	}

	return $classes;
}
//add_filter( 'body_class', 'lifesites2021_body_classes' );

/**
 * Add a pingback url auto-discovery header for single posts, pages, or attachments.
 */
function lifesites2021_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">', esc_url( get_bloginfo( 'pingback_url' ) ) );
	}
}
//add_action( 'wp_head', 'lifesites2021_pingback_header' );

/**
 * Split a page title into title and subtitle.
 *
 * Editors write titles like "Nieuw hier?<br/><h2>Van harte welkom</h2>":
 * the part before <br> is the title, the rest the subtitle. The Angular app
 * shows both on the header image; echoing the raw title would nest an <h2>
 * inside the <h1>, which browsers pull out of the header.
 *
 * @param int|WP_Post|null $post Post; defaults to the current post.
 * @return string[] { 0: title, 1: subtitle } as plain text (tags stripped).
 */
function lifesites2021_title_parts( $post = null ) {
	return lifesites2021_split_title( get_the_title( $post ) );
}

/**
 * Split a title string at the first <br> into plain-text title and subtitle.
 *
 * @param string $title Title HTML.
 * @return string[] { 0: title, 1: subtitle }.
 */
function lifesites2021_split_title( $title ) {
	$parts = preg_split( '/<br\s*\/?>/i', (string) $title, 2 );

	return array(
		trim( wp_strip_all_tags( $parts[0] ) ),
		isset( $parts[1] ) ? trim( wp_strip_all_tags( $parts[1] ) ) : '',
	);
}

/**
 * Menu items show only the title, never the "<br/><h2>subtitle</h2>" part.
 *
 * For the current page the menu item label is the full page title, which put
 * the subtitle as a large <h2> inside the dropdown. Only affects the theme's
 * menu; the app reads menus via REST, which does not run this filter.
 *
 * @param string $title Menu item title.
 * @return string
 */
function lifesites2021_nav_menu_item_title( $title ) {
	if ( ! preg_match( '/<br\s*\/?>/i', $title ) ) {
		return $title;
	}
	return esc_html( lifesites2021_split_title( $title )[0] );
}
add_filter( 'nav_menu_item_title', 'lifesites2021_nav_menu_item_title' );
