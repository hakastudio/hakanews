<?php
/**
 * Haka functions and definitions.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

// Versi dibaca dari header style.css, bukan ditulis ulang di sini. Dua
// tempat yang harus diubah bersamaan cepat atau lambat akan berbeda isi.
define( 'HAKA_VERSION', wp_get_theme( get_template() )->get( 'Version' ) );
define( 'HAKA_DIR', get_template_directory() );
define( 'HAKA_URI', get_template_directory_uri() );

require_once HAKA_DIR . '/inc/features.php';
require_once HAKA_DIR . '/inc/compat.php';
require_once HAKA_DIR . '/inc/setup.php';
require_once HAKA_DIR . '/inc/fonts.php';
require_once HAKA_DIR . '/inc/fonts-google.php';
require_once HAKA_DIR . '/inc/enqueue.php';
require_once HAKA_DIR . '/inc/favicon.php';
require_once HAKA_DIR . '/inc/template-tags.php';
require_once HAKA_DIR . '/inc/template-functions.php';
require_once HAKA_DIR . '/inc/queries.php';
require_once HAKA_DIR . '/inc/widgets.php';
require_once HAKA_DIR . '/inc/customizer.php';
require_once HAKA_DIR . '/inc/views.php';
require_once HAKA_DIR . '/inc/likes.php';
require_once HAKA_DIR . '/inc/inline-related.php';
require_once HAKA_DIR . '/inc/article-extras.php';
require_once HAKA_DIR . '/inc/social-embed.php';
require_once HAKA_DIR . '/inc/news-sitemap.php';
require_once HAKA_DIR . '/inc/dashboard.php';
require_once HAKA_DIR . '/inc/ads.php';
require_once HAKA_DIR . '/inc/schema.php';
require_once HAKA_DIR . '/inc/stocks.php';
require_once HAKA_DIR . '/inc/prayer-times.php';
require_once HAKA_DIR . '/inc/updater.php';
require_once HAKA_DIR . '/inc/demo-content.php';
