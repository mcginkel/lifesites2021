/**
 * Block editor: colour the editing canvas like the Angular app does for the
 * red / green / yellow page templates.
 *
 * Watches the page's template and puts life-template-{red,green,yellow} on
 * the body of the editor canvas (the iframe in WP 6.3+, or the main document
 * for the non-iframed editor). Styles live in editor-style.css.
 */
( function ( wp ) {
	if ( ! wp || ! wp.data || ! wp.domReady ) {
		return;
	}

	var TEMPLATE_CLASSES = {
		'templates/template-red-page.php': 'life-template-red',
		'templates/template-green-page.php': 'life-template-green',
		'templates/template-yellow-page.php': 'life-template-yellow',
	};
	var ALL = Object.keys( TEMPLATE_CLASSES ).map( function ( k ) {
		return TEMPLATE_CLASSES[ k ];
	} );

	function canvasBody() {
		var frame = document.querySelector( 'iframe[name="editor-canvas"]' );
		if ( frame && frame.contentDocument && frame.contentDocument.body ) {
			return frame.contentDocument.body;
		}
		return document.querySelector( '.editor-styles-wrapper' );
	}

	function apply( template ) {
		var body = canvasBody();
		if ( ! body ) {
			return;
		}
		ALL.forEach( function ( c ) {
			body.classList.remove( c );
		} );
		if ( TEMPLATE_CLASSES[ template ] ) {
			body.classList.add( TEMPLATE_CLASSES[ template ] );
		}
	}

	wp.domReady( function () {
		var last;
		wp.data.subscribe( function () {
			var editor = wp.data.select( 'core/editor' );
			if ( ! editor ) {
				return;
			}
			var template = editor.getEditedPostAttribute( 'template' );
			// Re-apply also when the canvas iframe was (re)created.
			var body = canvasBody();
			var key = template + '|' + ( body ? 'b' : '-' );
			if ( key !== last || ( body && TEMPLATE_CLASSES[ template ] && ! body.classList.contains( TEMPLATE_CLASSES[ template ] ) ) ) {
				last = key;
				apply( template );
			}
		} );
	} );
} )( window.wp );
