/**
 * Live preview di Customizer.
 */
( function ( $ ) {
	'use strict';

	if ( ! window.wp || ! window.wp.customize ) {
		return;
	}

	wp.customize( 'blogname', function ( value ) {
		value.bind( function ( to ) {
			var target = document.querySelector( '.site-title__text' );

			if ( target ) {
				// Logo teks memakai huruf pertama sebagai mark.
				var mark = document.querySelector( '.site-title__mark' );
				if ( mark ) {
					mark.textContent = to.charAt( 0 );
				}
				target.textContent = to.slice( 1 );
			}
		} );
	} );

	wp.customize( 'blogdescription', function ( value ) {
		value.bind( function ( to ) {
			var target = document.querySelector( '.site-description' );

			if ( target ) {
				target.textContent = to;
			}
		} );
	} );

	wp.customize( 'haka_primary_color', function ( value ) {
		value.bind( function ( to ) {
			if ( to ) {
				document.documentElement.style.setProperty( '--hk-primary', to );
			}
		} );
	} );

	wp.customize( 'haka_footer_copyright', function ( value ) {
		value.bind( function ( to ) {
			var target = document.querySelector( '.hk-footer__copy' );

			if ( target && to ) {
				target.textContent = to;
			}
		} );
	} );
}( window.jQuery ) );
