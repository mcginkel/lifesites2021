<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package LifeSites2021
 */

?>
	<footer id="colophon" class="site-footer">
		<div class="site-info">
			<?php $footer_page = get_page_by_path( 'footer' );echo apply_filters('the_content', $footer_page->post_content); ?>
		</div><!-- .site-info -->
	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
