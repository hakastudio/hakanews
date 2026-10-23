<?php
/**
 * Kompatibilitas dengan plugin SEO dan plugin cache.
 *
 * Prinsipnya: kalau ada plugin yang sudah mengurus sesuatu (schema, Open
 * Graph, breadcrumb, sitemap berita), theme mundur supaya tidak ada keluaran
 * ganda. Untuk cache halaman, semua yang berubah per pengunjung atau per
 * waktu (hitungan view, suka, jadwal shalat, ticker saham) dipindah ke
 * permintaan terpisah yang tidak ikut ter-cache, dan cache dibersihkan saat
 * pengaturan theme disimpan.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

/* ==========================================================================
   Deteksi plugin
   ========================================================================== */

/**
 * Plugin SEO yang aktif.
 *
 * @return string Kunci plugin, atau '' bila tidak ada.
 */
function haka_seo_plugin() {
	static $hasil = null;

	if ( null !== $hasil ) {
		return $hasil;
	}

	$daftar = array(
		'yoast'    => defined( 'WPSEO_VERSION' ),
		'rankmath' => defined( 'RANK_MATH_VERSION' ),
		'aioseo'   => function_exists( 'aioseo' ) || class_exists( 'All_in_One_SEO_Pack' ),
		'seopress' => defined( 'SEOPRESS_VERSION' ),
		'tsf'      => defined( 'THE_SEO_FRAMEWORK_VERSION' ),
		'slimseo'  => defined( 'SLIM_SEO_VER' ),
		'squirrly' => defined( 'SQ_VERSION' ),
	);

	$hasil = '';

	foreach ( $daftar as $kunci => $aktif ) {
		if ( $aktif ) {
			$hasil = $kunci;
			break;
		}
	}

	/**
	 * Paksa nama plugin SEO (atau '' untuk menonaktifkan deteksi).
	 *
	 * @param string $hasil Kunci plugin.
	 */
	$hasil = (string) apply_filters( 'haka_seo_plugin', $hasil );

	return $hasil;
}

/**
 * Apakah plugin SEO yang sudah menangani schema/meta sedang aktif.
 *
 * @return bool
 */
function haka_seo_plugin_active() {
	$active = '' !== haka_seo_plugin();

	/**
	 * Paksa aktif atau nonaktifkan deteksi plugin SEO.
	 *
	 * @param bool $active Hasil deteksi.
	 */
	return (bool) apply_filters( 'haka_seo_plugin_active', $active );
}

/**
 * Plugin cache halaman yang aktif.
 *
 * @return string Kunci plugin, atau '' bila tidak ada.
 */
function haka_cache_plugin() {
	static $hasil = null;

	if ( null !== $hasil ) {
		return $hasil;
	}

	$daftar = array(
		'wprocket'      => defined( 'WP_ROCKET_VERSION' ),
		'w3tc'          => defined( 'W3TC' ),
		'litespeed'     => defined( 'LSCWP_V' ),
		'wpsc'          => function_exists( 'wp_cache_clear_cache' ) && defined( 'WPCACHEHOME' ),
		'wpfc'          => class_exists( 'WpFastestCache' ),
		'cache-enabler' => defined( 'CACHE_ENABLER_VERSION' ),
		'hummingbird'   => defined( 'WPHB_VERSION' ),
		'sg'            => defined( 'SiteGround_Optimizer\VERSION' ) || function_exists( 'sg_cachepress_purge_cache' ),
		'breeze'        => defined( 'BREEZE_VERSION' ),
		'swift'         => defined( 'SWIFT_PERFORMANCE_VER' ),
		'comet'         => defined( 'COMET_CACHE_VERSION' ) || defined( 'COMET_CACHE_PRO_VERSION' ),
		'nginx-helper'  => class_exists( 'Nginx_Helper' ),
		'wpe'           => class_exists( 'WpeCommon' ),
		'kinsta'        => class_exists( '\Kinsta\Cache' ),
		'pantheon'      => function_exists( 'pantheon_wp_clear_edge_all' ),
		'cloudflare'    => defined( 'CLOUDFLARE_PLUGIN_DIR' ),
	);

	$hasil = '';

	foreach ( $daftar as $kunci => $aktif ) {
		if ( $aktif ) {
			$hasil = $kunci;
			break;
		}
	}

	/**
	 * Paksa nama plugin cache (atau '' untuk menonaktifkan deteksi).
	 *
	 * @param string $hasil Kunci plugin.
	 */
	$hasil = (string) apply_filters( 'haka_cache_plugin', $hasil );

	return $hasil;
}

/**
 * Apakah ada plugin cache halaman yang aktif.
 *
 * @return bool
 */
function haka_cache_plugin_active() {
	return '' !== haka_cache_plugin();
}

/**
 * Nama tampil plugin untuk dashboard.
 *
 * @param string $kunci Kunci plugin.
 * @return string
 */
function haka_plugin_label( $kunci ) {
	$nama = array(
		'yoast'         => 'Yoast SEO',
		'rankmath'      => 'Rank Math',
		'aioseo'        => 'All in One SEO',
		'seopress'      => 'SEOPress',
		'tsf'           => 'The SEO Framework',
		'slimseo'       => 'Slim SEO',
		'squirrly'      => 'Squirrly SEO',
		'wprocket'      => 'WP Rocket',
		'w3tc'          => 'W3 Total Cache',
		'litespeed'     => 'LiteSpeed Cache',
		'wpsc'          => 'WP Super Cache',
		'wpfc'          => 'WP Fastest Cache',
		'cache-enabler' => 'Cache Enabler',
		'hummingbird'   => 'Hummingbird',
		'sg'            => 'SiteGround Optimizer',
		'breeze'        => 'Breeze',
		'swift'         => 'Swift Performance',
		'comet'         => 'Comet Cache',
		'nginx-helper'  => 'Nginx Helper',
		'wpe'           => 'WP Engine Cache',
		'kinsta'        => 'Kinsta Cache',
		'pantheon'      => 'Pantheon Edge Cache',
		'cloudflare'    => 'Cloudflare',
	);

	return isset( $nama[ $kunci ] ) ? $nama[ $kunci ] : $kunci;
}

/* ==========================================================================
   SEO: mundur bila plugin sudah menangani
   ========================================================================== */

/**
 * Breadcrumb dari plugin SEO, bila fiturnya dinyalakan di plugin itu.
 *
 * @return bool True bila breadcrumb plugin sudah dicetak.
 */
function haka_plugin_breadcrumbs() {
	$html = '';

	if ( function_exists( 'yoast_breadcrumb' ) && class_exists( 'WPSEO_Options' ) && WPSEO_Options::get( 'breadcrumbs-enable' ) ) {
		$html = yoast_breadcrumb( '<div class="hk-breadcrumb__plugin">', '</div>', false );
	} elseif ( function_exists( 'rank_math_get_breadcrumbs' ) && class_exists( 'RankMath\Helper' ) && RankMath\Helper::get_settings( 'general.breadcrumbs' ) ) {
		$html = rank_math_get_breadcrumbs();
	} elseif ( function_exists( 'aioseo_breadcrumbs' ) ) {
		$html = (string) aioseo_breadcrumbs( false );
	} elseif ( function_exists( 'seopress_display_breadcrumbs' ) ) {
		$html = (string) seopress_display_breadcrumbs( false );
	}

	/**
	 * HTML breadcrumb dari plugin; kembalikan '' untuk memakai breadcrumb theme.
	 *
	 * @param string $html Keluaran plugin.
	 */
	$html = (string) apply_filters( 'haka_plugin_breadcrumbs_html', $html );

	if ( '' === trim( $html ) ) {
		return false;
	}

	echo '<nav class="hk-breadcrumb hk-breadcrumb--plugin" aria-label="' . esc_attr__( 'Breadcrumb', 'haka' ) . '">' . wp_kses_post( $html ) . '</nav>';

	return true;
}

/**
 * Kategori utama yang dipilih di plugin SEO (Yoast, Rank Math, SEOPress).
 *
 * @param WP_Term|null $category Pilihan theme.
 * @param int          $post_id  ID post.
 * @return WP_Term|null
 */
function haka_plugin_primary_category( $category, $post_id ) {
	foreach ( array( '_yoast_wpseo_primary_category', 'rank_math_primary_category', '_seopress_robots_primary_cat' ) as $meta ) {
		$id = (int) get_post_meta( $post_id, $meta, true );

		if ( $id > 0 ) {
			$term = get_term( $id, 'category' );

			if ( $term instanceof WP_Term ) {
				return $term;
			}
		}
	}

	return $category;
}
add_filter( 'haka_primary_category', 'haka_plugin_primary_category', 10, 2 );

/* ==========================================================================
   Cache halaman
   ========================================================================== */

/**
 * Tandai permintaan ini agar tidak di-cache oleh plugin mana pun.
 */
function haka_jangan_cache() {
	if ( ! defined( 'DONOTCACHEPAGE' ) ) {
		define( 'DONOTCACHEPAGE', true );
	}

	if ( ! defined( 'DONOTCACHEOBJECT' ) ) {
		define( 'DONOTCACHEOBJECT', true );
	}

	do_action( 'litespeed_control_set_nocache', 'haka' );
	nocache_headers();
}

/**
 * Bersihkan cache halaman dari plugin yang dikenali.
 *
 * Dipanggil saat pengaturan theme disimpan: warna, tipografi, susunan
 * section, dan sebagainya tertanam di HTML, jadi halaman yang sudah
 * tersimpan di cache harus dibuat ulang.
 */
function haka_purge_page_cache() {
	/**
	 * Lewati pembersihan cache otomatis.
	 *
	 * @param bool $lewati Default false.
	 */
	if ( apply_filters( 'haka_skip_cache_purge', false ) ) {
		return;
	}

	try {
		if ( function_exists( 'rocket_clean_domain' ) ) {
			rocket_clean_domain();
		}

		if ( function_exists( 'w3tc_flush_all' ) ) {
			w3tc_flush_all();
		}

		if ( defined( 'LSCWP_V' ) ) {
			do_action( 'litespeed_purge_all' );
		}

		if ( function_exists( 'wp_cache_clear_cache' ) ) {
			wp_cache_clear_cache();
		}

		if ( isset( $GLOBALS['wp_fastest_cache'] ) && is_object( $GLOBALS['wp_fastest_cache'] ) && method_exists( $GLOBALS['wp_fastest_cache'], 'deleteCache' ) ) {
			$GLOBALS['wp_fastest_cache']->deleteCache( true );
		}

		if ( defined( 'CACHE_ENABLER_VERSION' ) ) {
			do_action( 'cache_enabler_clear_complete_cache' );
		}

		if ( defined( 'WPHB_VERSION' ) ) {
			do_action( 'wphb_clear_page_cache' );
		}

		if ( function_exists( 'sg_cachepress_purge_cache' ) ) {
			sg_cachepress_purge_cache();
		}

		if ( defined( 'BREEZE_VERSION' ) ) {
			do_action( 'breeze_clear_all_cache' );
		}

		if ( class_exists( 'Swift_Performance_Cache' ) && method_exists( 'Swift_Performance_Cache', 'clear_all_cache' ) ) {
			Swift_Performance_Cache::clear_all_cache();
		}

		if ( class_exists( 'comet_cache' ) && method_exists( 'comet_cache', 'clear' ) ) {
			comet_cache::clear();
		}

		if ( class_exists( 'Nginx_Helper' ) ) {
			do_action( 'rt_nginx_helper_purge_all' );
		}

		if ( class_exists( 'WpeCommon' ) && method_exists( 'WpeCommon', 'purge_varnish_cache' ) ) {
			WpeCommon::purge_varnish_cache();
		}

		if ( class_exists( '\Kinsta\Cache' ) && isset( $GLOBALS['kinsta_cache'] ) && is_object( $GLOBALS['kinsta_cache'] ) && isset( $GLOBALS['kinsta_cache']->kinsta_cache_purge ) ) {
			$GLOBALS['kinsta_cache']->kinsta_cache_purge->purge_complete_caches();
		}

		if ( function_exists( 'pantheon_wp_clear_edge_all' ) ) {
			pantheon_wp_clear_edge_all();
		}

		if ( class_exists( 'autoptimizeCache' ) && method_exists( 'autoptimizeCache', 'clearall' ) ) {
			autoptimizeCache::clearall();
		}
	} catch ( Throwable $e ) {
		// Cache yang gagal dibersihkan tidak boleh menggagalkan penyimpanan
		// pengaturan; halaman lama hanya bertahan sampai cache kedaluwarsa.
		error_log( '[haka] gagal membersihkan cache: ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
	}

	/**
	 * Titik kait untuk plugin cache yang belum dikenali.
	 */
	do_action( 'haka_after_purge_cache' );
}
add_action( 'customize_save_after', 'haka_purge_page_cache', 20 );
add_action( 'after_switch_theme', 'haka_purge_page_cache', 20 );
// Impor demo menulis opsi HAKA_DEMO_OPTION setelah selesai; itu saatnya cache dibersihkan.
add_action( 'add_option_haka_demo_imported', 'haka_purge_page_cache' );
add_action( 'update_option_haka_demo_imported', 'haka_purge_page_cache' );

/**
 * Skrip penentu tema terang/gelap harus tetap inline dan berjalan lebih
 * dulu; kalau ditunda atau digabung oleh plugin optimasi, halaman berkedip.
 *
 * @param array $daftar Pola pengecualian.
 * @return array
 */
function haka_kecualikan_boot_script( $daftar ) {
	$daftar   = is_array( $daftar ) ? $daftar : array();
	$daftar[] = 'haka-theme-boot';
	$daftar[] = 'haka-theme';

	return array_values( array_unique( $daftar ) );
}
add_filter( 'rocket_delay_js_exclusions', 'haka_kecualikan_boot_script' );
add_filter( 'rocket_excluded_inline_js_content', 'haka_kecualikan_boot_script' );
add_filter( 'rocket_defer_inline_exclusions', 'haka_kecualikan_boot_script' );
add_filter( 'litespeed_optimize_js_excludes', 'haka_kecualikan_boot_script' );
add_filter( 'litespeed_optm_js_defer_exc', 'haka_kecualikan_boot_script' );
add_filter( 'autoptimize_filter_js_exclude', 'haka_kecualikan_boot_script' );
add_filter( 'wp_hummingbird_is_minifiable', 'haka_kecualikan_boot_script' ); // tidak berbahaya bila hook tidak ada.

/* ==========================================================================
   Fragmen dinamis: diambil ulang lewat REST supaya tidak basi di cache
   ========================================================================== */

/**
 * Daftar fragmen yang boleh diminta ulang.
 *
 * @return array Kunci => template part.
 */
function haka_live_fragments() {
	/**
	 * Modul mendaftarkan fragmennya sendiri (jadwal imsak, ticker saham).
	 *
	 * @param array $fragments Kunci => template part.
	 */
	return apply_filters( 'haka_live_fragments', array() );
}

/**
 * Endpoint REST untuk fragmen dinamis.
 */
function haka_register_compat_routes() {
	register_rest_route(
		'haka/v1',
		'/fragment/(?P<nama>[a-z\-]+)',
		array(
			'methods'             => 'GET',
			'callback'            => 'haka_rest_fragment',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'haka_register_compat_routes' );

/**
 * Render ulang sebuah template part.
 *
 * @param WP_REST_Request $request Permintaan.
 * @return WP_REST_Response|WP_Error
 */
function haka_rest_fragment( $request ) {
	$daftar = haka_live_fragments();
	$nama   = (string) $request['nama'];

	if ( ! isset( $daftar[ $nama ] ) ) {
		return new WP_Error( 'haka_no_fragment', __( 'Fragmen tidak dikenal.', 'haka' ), array( 'status' => 404 ) );
	}

	ob_start();
	get_template_part( $daftar[ $nama ] );
	$html = ob_get_clean();

	$response = rest_ensure_response( array( 'html' => $html ) );
	$response->header( 'Cache-Control', 'no-store, max-age=0' );

	return $response;
}
