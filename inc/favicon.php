<?php
/**
 * Ikon situs bawaan bila pengguna belum menetapkan Site Icon.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

/**
 * Ikon situs bawaan bila pengguna belum menetapkan Site Icon.
 *
 * Tanpa ini browser meminta /favicon.ico dan menerima 404, yang tercatat
 * sebagai error di konsol.
 */
function haka_default_favicon() {
	if ( has_site_icon() ) {
		return;
	}

	$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><rect width="64" height="64" rx="12" fill="#ff3300"/><text x="32" y="45" font-family="system-ui,sans-serif" font-size="40" font-weight="700" fill="#fff" text-anchor="middle">R</text></svg>';

	printf(
		'<link rel="icon" href="data:image/svg+xml,%s">' . "\n",
		rawurlencode( $svg )
	);
}
add_action( 'wp_head', 'haka_default_favicon', 2 );
