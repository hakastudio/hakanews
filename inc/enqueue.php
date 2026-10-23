<?php
/**
 * Enqueue asset frontend.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

/**
 * Versi asset berbasis filemtime supaya cache busting akurat saat development.
 *
 * @param string $relative_path Path relatif terhadap root theme.
 * @return string
 */
function haka_asset_version( $relative_path ) {
	$file = HAKA_DIR . '/' . ltrim( $relative_path, '/' );

	if ( file_exists( $file ) ) {
		return (string) filemtime( $file );
	}

	return HAKA_VERSION;
}

/**
 * Muat CSS dan JS theme.
 */
function haka_enqueue_assets() {
	// Font mengikuti pilihan Customizer; sumbernya diatur inc/fonts.php.
	haka_enqueue_fonts();

	// style.css hanya berisi theme header, tetap dimuat agar child theme rapi.
	wp_enqueue_style( 'haka-style', get_stylesheet_uri(), array(), haka_asset_version( 'style.css' ) );

	wp_enqueue_style(
		'haka-main',
		HAKA_URI . '/assets/css/main.css',
		array( 'haka-style' ),
		haka_asset_version( 'assets/css/main.css' )
	);

	wp_add_inline_style( 'haka-main', haka_css_variables() );

	wp_enqueue_script(
		'haka-main',
		HAKA_URI . '/assets/js/main.js',
		array(),
		haka_asset_version( 'assets/js/main.js' ),
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	wp_localize_script(
		'haka-main',
		'hakaData',
		/**
		 * Data untuk skrip theme. Modul (suka, view) menambahkan endpoint-nya
		 * sendiri. Tanpa nonce: endpoint ini publik, dan nonce yang ikut
		 * tersimpan di cache halaman akan kedaluwarsa lalu membuat REST menolak.
		 *
		 * @param array $data Data skrip.
		 */
		apply_filters(
			'haka_script_data',
			array(
				'fragmentEndpoint' => esc_url_raw( rest_url( 'haka/v1/fragment/' ) ),
				'liveFragments'    => haka_cache_plugin_active(),
			)
		)
	);

	wp_localize_script(
		'haka-main',
		'hakaI18n',
		array(
			'openMenu'    => __( 'Buka menu', 'haka' ),
			'openSubmenu' => __( 'Buka submenu', 'haka' ),
			'closeMenu'  => __( 'Tutup menu', 'haka' ),
			'openSearch' => __( 'Buka pencarian', 'haka' ),
			'copied'      => __( 'Tautan disalin', 'haka' ),
			'pauseSlider' => __( 'Hentikan geser otomatis', 'haka' ),
			'playSlider'  => __( 'Jalankan geser otomatis', 'haka' ),
			'darkOn'      => __( 'Ganti ke mode terang', 'haka' ),
			'darkOff'     => __( 'Ganti ke mode gelap', 'haka' ),
			'source'      => __( 'Sumber', 'haka' ),
			'loading'     => __( 'Memuat…', 'haka' ),
			'noMore'      => __( 'Semua berita sudah dimuat.', 'haka' ),
			'loadFailed'  => __( 'Gagal memuat. Coba lagi.', 'haka' ),
		)
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'haka_enqueue_assets' );

/**
 * Bangun CSS custom property dari nilai Customizer.
 *
 * @return string
 */
function haka_css_variables() {
	$primary = haka_get_option( 'primary_color', '#ff3300' );
	$primary = sanitize_hex_color( $primary ) ? $primary : '#ff3300';

	$vars = array(
		'--hk-primary'       => $primary,
		'--hk-primary-ink'   => haka_accessible_ink( $primary ),
		'--hk-primary-dark'  => haka_shade_color( $primary, -18 ),
		'--hk-primary-soft'  => haka_hex_to_rgba( $primary, 0.10 ),
		'--hk-primary-ring'  => haka_hex_to_rgba( $primary, 0.35 ),
	);

	// Kecepatan ticker dalam piksel per detik. JS menghitung durasi dari
	// lebar isi dibagi angka ini, jadi pilihan Customizer benar-benar terasa
	// berapa pun jumlah judulnya. --hk-ticker-speed hanya cadangan tanpa JS.
	$speeds = array(
		'slow'      => array( 30, '58s' ),
		'normal'    => array( 50, '40s' ),
		'fast'      => array( 85, '26s' ),
		'very_fast' => array( 130, '17s' ),
		'ultra'     => array( 200, '11s' ),
		'whoosh'    => array( 320, '7s' ),
	);
	$speed  = haka_get_option( 'breaking_speed', 'normal' );
	$speed  = isset( $speeds[ $speed ] ) ? $speeds[ $speed ] : $speeds['normal'];

	$vars['--hk-ticker-pps']   = (string) $speed[0];
	$vars['--hk-ticker-speed'] = $speed[1];
	$vars['--hk-archive-cols'] = (string) max( 2, min( 4, (int) haka_get_option( 'archive_columns', 3 ) ) );
	$vars['--hk-menu-gap']     = max( 10, min( 40, (int) haka_get_option( 'menu_gap', 20 ) ) ) . 'px';

	$vars = array_merge( $vars, haka_typography_vars() );

	$css = ':root{';
	foreach ( $vars as $name => $value ) {
		$css .= $name . ':' . $value . ';';
	}
	$css .= '}';

	return $css;
}

/**
 * Luminansi relatif sebuah warna (WCAG).
 *
 * @param array $rgb Array RGB 0-255.
 * @return float
 */
function haka_relative_luminance( $rgb ) {
	$channels = array();

	foreach ( $rgb as $value ) {
		$value      = $value / 255;
		$channels[] = ( $value <= 0.03928 ) ? $value / 12.92 : pow( ( $value + 0.055 ) / 1.055, 2.4 );
	}

	return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
}

/**
 * Rasio kontras antara dua warna.
 *
 * @param array $rgb1 Warna pertama.
 * @param array $rgb2 Warna kedua.
 * @return float
 */
function haka_contrast_ratio( $rgb1, $rgb2 ) {
	$l1 = haka_relative_luminance( $rgb1 );
	$l2 = haka_relative_luminance( $rgb2 );

	$light = max( $l1, $l2 );
	$dark  = min( $l1, $l2 );

	return ( $light + 0.05 ) / ( $dark + 0.05 );
}

/**
 * Varian warna aksen yang aman untuk teks.
 *
 * Warna aksen cerah (oranye, kuning, hijau muda) tidak pernah lolos rasio
 * kontras 4.5:1 terhadap putih. Fungsi ini menggelapkannya bertahap sampai
 * lolos, sehingga warna pilihan pengguna apa pun tetap terbaca.
 *
 * @param string $hex    Warna aksen.
 * @param float  $target Rasio minimum.
 * @return string
 */
function haka_accessible_ink( $hex, $target = 4.5 ) {
	$rgb = haka_hex_to_rgb( $hex );

	if ( ! $rgb ) {
		return $hex;
	}

	// Diukur terhadap permukaan paling terang tempat teks aksen muncul, yaitu
	// kotak berlatar --hk-bg-alt. Mengukur terhadap putih saja membuat teks
	// di dalam kotak tetap gagal karena latarnya sedikit lebih gelap.
	$surface = array( 237, 243, 245 );

	for ( $step = 0; $step <= 20; $step++ ) {
		if ( haka_contrast_ratio( $rgb, $surface ) >= $target ) {
			break;
		}

		foreach ( $rgb as $key => $value ) {
			$rgb[ $key ] = (int) max( 0, round( $value * 0.92 ) );
		}
	}

	return sprintf( '#%02x%02x%02x', $rgb[0], $rgb[1], $rgb[2] );
}

/**
 * Variabel CSS untuk tipografi.
 *
 * @return array
 */
function haka_typography_vars() {
	$fallback = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif';

	$heading = haka_font_family( 'heading' );
	$body    = haka_font_family( 'body' );

	$tracking = array(
		'normal'  => '0',
		'tight'   => '-.02em',
		'tighter' => '-.035em',
	);
	$track = haka_get_option( 'heading_tracking', 'tight' );

	$base    = max( 11, min( 18, (int) haka_get_option( 'base_size', 13 ) ) );
	$article = max( 14, min( 22, (int) haka_get_option( 'article_size', 16 ) ) );
	$scale   = max( 60, min( 100, (int) haka_get_option( 'mobile_scale', 78 ) ) ) / 100;

	$sizes = array();

	foreach ( array( 'h1' => 34, 'h2' => 24, 'h3' => 18, 'h4' => 16 ) as $key => $default ) {
		$sizes[ $key ] = (int) haka_get_option( $key . '_size', $default );
	}

	return array(
		'--hk-font-body'    => $body ? '"' . $body . '", ' . $fallback : $fallback,
		'--hk-font-head'    => $heading ? '"' . $heading . '", ' . $fallback : $fallback,
		'--hk-fs-base'      => $base . 'px',
		'--hk-fs-article'   => $article . 'px',
		'--hk-lh-article'   => number_format( (int) haka_get_option( 'body_line_height', '18' ) / 10, 1, '.', '' ),
		'--hk-fs-h1'        => $sizes['h1'] . 'px',
		'--hk-fs-h2'        => $sizes['h2'] . 'px',
		'--hk-fs-h3'        => $sizes['h3'] . 'px',
		'--hk-fs-h4'        => $sizes['h4'] . 'px',
		'--hk-fs-h1-mobile' => round( $sizes['h1'] * $scale ) . 'px',
		'--hk-fs-h2-mobile' => round( $sizes['h2'] * $scale ) . 'px',
		'--hk-fs-h3-mobile' => round( $sizes['h3'] * $scale ) . 'px',
		'--hk-weight-head'  => (string) (int) haka_get_option( 'heading_weight', '800' ),
		'--hk-weight-body'  => (string) (int) haka_get_option( 'body_weight', '400' ),
		'--hk-track-head'   => isset( $tracking[ $track ] ) ? $tracking[ $track ] : '-.02em',
	);
}

/**
 * Gelapkan atau terangkan warna hex.
 *
 * @param string $hex     Warna hex.
 * @param int    $percent Persentase (-100..100).
 * @return string
 */
function haka_shade_color( $hex, $percent ) {
	$rgb = haka_hex_to_rgb( $hex );

	if ( ! $rgb ) {
		return $hex;
	}

	foreach ( $rgb as $key => $value ) {
		$adjusted    = $value + ( $value * ( $percent / 100 ) );
		$rgb[ $key ] = (int) max( 0, min( 255, round( $adjusted ) ) );
	}

	return sprintf( '#%02x%02x%02x', $rgb[0], $rgb[1], $rgb[2] );
}

/**
 * Ubah hex menjadi rgba() string.
 *
 * @param string $hex   Warna hex.
 * @param float  $alpha Nilai alpha.
 * @return string
 */
function haka_hex_to_rgba( $hex, $alpha ) {
	$rgb = haka_hex_to_rgb( $hex );

	if ( ! $rgb ) {
		return $hex;
	}

	return sprintf( 'rgba(%d,%d,%d,%s)', $rgb[0], $rgb[1], $rgb[2], (string) $alpha );
}

/**
 * Ubah hex menjadi array RGB.
 *
 * @param string $hex Warna hex.
 * @return array|false
 */
function haka_hex_to_rgb( $hex ) {
	$hex = ltrim( (string) $hex, '#' );

	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}

	if ( 6 !== strlen( $hex ) || ! ctype_xdigit( $hex ) ) {
		return false;
	}

	return array(
		hexdec( substr( $hex, 0, 2 ) ),
		hexdec( substr( $hex, 2, 2 ) ),
		hexdec( substr( $hex, 4, 2 ) ),
	);
}

/**
 * Tetapkan tema terang/gelap sebelum halaman digambar.
 *
 * Skrip kecil ini sengaja inline dan berjalan lebih dulu supaya tidak ada
 * kedipan warna terang saat pengunjung memilih mode gelap.
 */
function haka_theme_boot_script() {
	if ( ! haka_enabled( 'dark_mode' ) ) {
		return;
	}

	$start = haka_get_option( 'dark_mode_start', 'system' );

	printf(
		'<script id="haka-theme-boot" data-cfasync="false" data-no-optimize="1" data-no-minify="1" data-no-defer="1" data-pagespeed-no-defer>(function(){try{var s=%s,t=localStorage.getItem("haka-theme");if(!t){t=s==="system"?(window.matchMedia("(prefers-color-scheme: dark)").matches?"dark":"light"):s;}document.documentElement.setAttribute("data-theme",t);}catch(e){}})();</script>' . "\n",
		wp_json_encode( $start )
	);
}
add_action( 'wp_head', 'haka_theme_boot_script', 1 );

/**
 * Dukungan RTL: WordPress otomatis memuat main-rtl.css pada locale RTL.
 */
function haka_register_rtl() {
	if ( haka_enabled( 'rtl_support' ) ) {
		wp_style_add_data( 'haka-main', 'rtl', 'replace' );
	}
}
add_action( 'wp_enqueue_scripts', 'haka_register_rtl', 20 );

/**
 * Style untuk editor blok agar mendekati tampilan frontend.
 */
function haka_editor_assets() {
	add_editor_style( 'assets/css/editor.css' );
}
add_action( 'after_setup_theme', 'haka_editor_assets' );
