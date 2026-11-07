<?php
/**
 * REST API: expose raw block markup as a 'blocks' field.
 *
 * The Angular frontend (life-frontend, ParseHelper.parsePageToDocument) reads
 * `blocks` from /wp/v2/pages, /wp/v2/posts and /wp/v2/frontpage and parses the
 * <!-- wp:... --> comments itself, so it needs post_content, not
 * content.rendered.
 *
 * The field is limited to the 'view' and 'embed' contexts. The block editor
 * loads entities with context=edit and treats a `blocks` property as its own
 * parsed block list; a raw string there crashes the editor
 * ("Spread syntax requires ...iterable not be null or undefined", WP 6.9+/7.x).
 *
 * This code also lives in the Code Snippets plugin as snippet #7
 * ("Gutenberg blocks in rest calls"). Once this theme version is deployed,
 * that snippet can be deactivated. Registering the field twice is harmless:
 * the last registration wins and both are identical.
 *
 * @package LifeSites2021
 */

add_action(
	'rest_api_init',
	function () {
		foreach ( array( 'post', 'page' ) as $post_type ) {
			register_rest_field(
				$post_type,
				'blocks',
				array(
					'get_callback' => function ( array $post ) {
						return $post['content']['raw'] ?? '';
					},
					'schema'       => array(
						'description' => 'Raw block markup (post_content).',
						'type'        => 'string',
						'context'     => array( 'view', 'embed' ),
						'readonly'    => true,
					),
				)
			);
		}
	}
);
