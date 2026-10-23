<?php
/**
 * Theme setup: theme support, menu, image size.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

/**
 * Daftarkan dukungan fitur inti WordPress.
 */
function haka_setup() {
	load_theme_textdomain( 'haka', HAKA_DIR . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'post-formats', array( 'video', 'gallery', 'audio' ) );
	add_theme_support( 'wp-block-styles' );
	add_theme_support(
		'custom-background',
		array(
			'default-color' => 'ffffff',
		)
	);

	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);

	add_theme_support(
		'custom-logo',
		array(
			'height'               => 48,
			'width'                => 220,
			'flex-height'          => true,
			'flex-width'           => true,
			'header-text'          => array( 'site-title', 'site-description' ),
			'unlink-homepage-logo' => false,
		)
	);

	// Ukuran gambar yang benar-benar dipakai theme.
	add_image_size( 'haka-hero', 1120, 630, true );
	add_image_size( 'haka-card', 640, 400, true );
	add_image_size( 'haka-list', 320, 200, true );
	add_image_size( 'haka-thumb', 160, 110, true );
	add_image_size( 'haka-story', 400, 711, true ); // Kartu Video Story 9:16.

	register_nav_menus(
		array(
			'primary'   => __( 'Menu Utama', 'haka' ),
			'secondary' => __( 'Menu Kedua', 'haka' ),
			'footer'    => __( 'Menu Footer', 'haka' ),
		)
	);
}
add_action( 'after_setup_theme', 'haka_setup' );

/**
 * Lebar konten untuk oEmbed dan gambar besar.
 */
function haka_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'haka_content_width', 800 );
}
add_action( 'after_setup_theme', 'haka_content_width', 0 );

/**
 * Ukuran gambar khusus theme agar bisa dipilih di editor.
 *
 * @param array $sizes Ukuran yang tersedia.
 * @return array
 */
function haka_custom_image_sizes( $sizes ) {
	return array_merge(
		$sizes,
		array(
			'haka-card' => __( 'Haka Card', 'haka' ),
			'haka-hero' => __( 'Haka Hero', 'haka' ),
		)
	);
}
add_filter( 'image_size_names_choose', 'haka_custom_image_sizes' );
