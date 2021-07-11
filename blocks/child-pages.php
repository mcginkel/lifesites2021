<?php
/**
 * Server-side rendering of the `life/child-pages` block.
 *
 * @package WordPress
 */

/**
 * The excerpt length set by the Latest Posts core block
 * set at render time and used by the block itself.
 *
 * @var int
 */
$block_core_child_pages_excerpt_length = 0;

/**
 * Callback for the excerpt_length filter used by
 * the Latest Posts block at render time.
 *
 * @return int Returns the global $block_core_child_pages_excerpt_length variable
 *             to allow the excerpt_length filter respect the Latest Block setting.
 */
function block_core_child_pages_get_excerpt_length() {
	global $block_core_child_pages_excerpt_length;
	return $block_core_child_pages_excerpt_length;
}

/**
 * Renders the `life/child-pages` block on server.
 *
 * @param array $attributes The block attributes.
 *
 * @return string Returns the post content with latest posts added.
 */
function render_block_core_child_pages( $attributes ) {

	global $post, $block_core_child_pages_excerpt_length;

	$args = array(
		'number'           => $attributes['pagesToShow'],
		'post_status'      => 'publish',
		'sort_column'      => $attributes['sort_column'],
		'sort_order'       => $attributes['sort_order'],
        'parent'           => get_the_ID(),
	);

    $block_core_child_pages_excerpt_length = $attributes['excerptLength'];
	add_filter( 'excerpt_length', 'block_core_child_pages_get_excerpt_length', 20 );

	$child_pages = get_pages( $args );
    $useColumnsLayout = false;
    if ( isset( $attributes['postLayout'] ) && 'grid' === $attributes['postLayout'] ) {
		$useColumnsLayout .= true;
	}

	$list_items_markup = '';

	foreach ( $child_pages as $post ) {
		$post_link = esc_url( get_permalink( $post ) );

		$list_items_markup .= '<li>';

		if ( $attributes['displayFeaturedImage'] && has_post_thumbnail( $post ) ) {
			$image_style = '';
			if ( isset( $attributes['featuredImageSizeWidth'] ) ) {
				$image_style .= sprintf( 'max-width:%spx;', $attributes['featuredImageSizeWidth'] );
			}
			if ( isset( $attributes['featuredImageSizeHeight'] ) ) {
				$image_style .= sprintf( 'max-height:%spx;', $attributes['featuredImageSizeHeight'] );
			}

			$image_classes = 'wp-block-child-pages__featured-image';
			if ( isset( $attributes['featuredImageAlign'] ) ) {
				$image_classes .= ' align' . $attributes['featuredImageAlign'];
			}

			$featured_image = get_the_post_thumbnail(
				$post,
				$attributes['featuredImageSizeSlug'],
				array(
					'style' => $image_style,
				)
			);
			if ( $attributes['addLinkToFeaturedImage'] ) {
				$featured_image = sprintf(
					'<a href="%1$s">%2$s</a>',
					$post_link,
					$featured_image
				);
			}
			$list_items_markup .= sprintf(
				'<div class="%1$s">%2$s</div>',
				$image_classes,
				$featured_image
			);
		}

        $list_items_markup .= '<div class="wp-block-child-pages__content">';

		$title = get_the_title( $post );
		if ( ! $title ) {
			$title = __( '(no title)' );
		}
		$list_items_markup .= sprintf(
			'<h3>%1$s</h3>',
			$title
		);

		if ( isset( $attributes['displayPostContent'] ) && $attributes['displayPostContent']
			&& isset( $attributes['displayPostContentRadio'] ) && 'excerpt' === $attributes['displayPostContentRadio'] ) {

			$trimmed_excerpt = get_the_excerpt( $post );

			if ( post_password_required( $post ) ) {
				$trimmed_excerpt = __( 'This content is password protected.' );
			}

			$list_items_markup .= sprintf(
				'<div class="wp-block-child-pages__post-excerpt">%1$s</div>',
				$trimmed_excerpt
			);
		}

		if ( isset( $attributes['displayPostContent'] ) && $attributes['displayPostContent']
			&& isset( $attributes['displayPostContentRadio'] ) && 'full_post' === $attributes['displayPostContentRadio'] ) {

			$post_content = wp_kses_post( html_entity_decode( $post->post_content, ENT_QUOTES, get_option( 'blog_charset' ) ) );

			if ( post_password_required( $post ) ) {
				$post_content = __( 'This content is password protected.' );
			}

			$list_items_markup .= sprintf(
				'<div class="wp-block-child-pages__post-full-content">%1$s</div>',
				$post_content
			);
		}

        $list_items_markup .= sprintf(
			'<a href="%1$s">%2$s</a>',
			$post_link,
			'Lees verder ->'
		);
        $list_items_markup .= '</div>';


		$list_items_markup .= "</li>\n";
	}

	remove_filter( 'excerpt_length', 'block_core_child_pages_get_excerpt_length', 20 );

	$class = 'wp-block-child-pages__list';

	if ( isset( $attributes['postLayout'] ) && 'grid' === $attributes['postLayout'] ) {
		$class .= ' is-grid';
	}

	if ( isset( $attributes['columns'] ) && 'grid' === $attributes['postLayout'] ) {
		$class .= ' columns-' . $attributes['columns'];
	}

	$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => $class ) );

	return sprintf(
		'<ul %1$s>%2$s</ul>',
		$wrapper_attributes,
		$list_items_markup
	);
}

/**
 * Registers the `core/child-pages` block on server.
 */
function register_block_core_child_pages() {

	register_block_type_from_metadata(
		__DIR__ . '/child-pages',
		array(
			'render_callback' => 'render_block_core_child_pages',
		)
	);
}
function register_life_pattern_categories() {
    register_block_pattern_category( 'life',
    array( 'label' => __( 'LIFE', 'life' ) ) );
  }
   
add_action( 'init', 'register_life_pattern_categories' );

add_action( 'init', 'register_block_core_child_pages' );
