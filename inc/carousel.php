<?php
/**
 * Carousel Slider Block: multi-slide carousels behave like the app's.
 *
 * The Angular app shows a multi-slide carousel as a row of fixed 330px cards
 * (swiper), left to right, without dots, arrows or autoplay. The plugin initialises slick
 * straight from its init.js with the settings in data-slick, so we rewrite
 * those settings just before init.js runs. Single-slide carousels keep the
 * editor's settings. css/app-look.css sizes the cards.
 *
 * @package LifeSites2021
 */

defined( 'ABSPATH' ) || exit;

/**
 * Attach the settings rewrite to the plugin's init script, if it is on the page.
 */
function lifesites2021_carousel_settings() {
	if ( ! wp_script_is( 'carousel-block-slick-init', 'enqueued' ) ) {
		return;
	}

	$js = <<<'JS'
document.querySelectorAll('.wp-block-cb-carousel:not(.cb-single-slide)[data-slick]').forEach(function (el) {
	var s;
	try { s = JSON.parse(el.getAttribute('data-slick')) || {}; } catch (e) { s = {}; }
	s.variableWidth = true;
	s.slidesToShow = 1;
	s.slidesToScroll = 1;
	s.infinite = false;
	s.dots = false;
	s.arrows = false;
	s.autoplay = false;
	s.responsive = [];
	s.rtl = false; // the app always runs left to right
	el.removeAttribute('dir');
	el.setAttribute('data-slick', JSON.stringify(s));
	el.classList.add('cb-cards');
});
JS;

	wp_add_inline_script( 'carousel-block-slick-init', $js, 'before' );
}
// Footer scripts print at wp_footer priority 20; blocks are rendered by then.
add_action( 'wp_footer', 'lifesites2021_carousel_settings', 1 );
