<?php
/**
 * Sitemap berita dan penanda pratinjau gambar.
 *
 * Google News membaca sitemap berformat khusus yang hanya memuat artikel
 * dua hari terakhir — berbeda dari sitemap umum bawaan WordPress. Berkas ini
 * menerbitkannya di /news-sitemap.xml.
 *
 * Perlu ditegaskan supaya tidak menimbulkan salah paham: sitemap ini TIDAK
 * mendaftarkan situs ke Google News. Pendaftaran tetap dilakukan pemilik
 * situs lewat Publisher Center. Yang dikerjakan di sini hanya menyiapkan
 * bentuk data yang bisa dibaca mesin ketika situs sudah diterima.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

const HAKA_SITEMAP_VAR = 'haka_news_sitemap';

/* ==========================================================================
   Rute
   ========================================================================== */

/**
 * Apakah plugin SEO sudah menerbitkan sitemap berita sendiri.
 *
 * @return bool
 */
function haka_plugin_news_sitemap() {
	if ( defined( 'WPSEO_NEWS_VERSION' ) ) {
		return true;
	}

	if ( class_exists( 'RankMath\Helper' ) && is_callable( array( 'RankMath\Helper', 'is_module_active' ) ) && RankMath\Helper::is_module_active( 'news-sitemap' ) ) {
		return true;
	}

	if ( function_exists( 'aioseo' ) ) {
		try {
			$aio = aioseo();

			if ( isset( $aio->options->sitemap->news->enable ) && $aio->options->sitemap->news->enable ) {
				return true;
			}
		} catch ( Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement
			// Struktur opsi AIOSEO berubah: anggap tidak ada.
		}
	}

	return false;
}

/**
 * Apakah sitemap berita theme boleh terbit.
 *
 * @return bool
 */
function haka_news_sitemap_aktif() {
	$aktif = haka_enabled( 'news_sitemap' ) && ! haka_plugin_news_sitemap();

	/**
	 * Paksa sitemap berita theme aktif/nonaktif.
	 *
	 * @param bool $aktif Hasil.
	 */
	return (bool) apply_filters( 'haka_news_sitemap_enabled', $aktif );
}

/**
 * Daftarkan alamat /news-sitemap.xml.
 */
function haka_sitemap_rute() {
	add_rewrite_rule( '^news-sitemap\.xml$', 'index.php?' . HAKA_SITEMAP_VAR . '=1', 'top' );
}
add_action( 'init', 'haka_sitemap_rute' );

/**
 * Daftarkan query var penanda.
 *
 * @param string[] $vars Daftar query var.
 * @return string[]
 */
function haka_sitemap_query_var( $vars ) {
	$vars[] = HAKA_SITEMAP_VAR;

	return $vars;
}
add_filter( 'query_vars', 'haka_sitemap_query_var' );

/**
 * Segarkan aturan rewrite saat theme diaktifkan.
 */
function haka_sitemap_flush() {
	haka_sitemap_rute();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'haka_sitemap_flush' );

/**
 * Segarkan juga saat pengaturannya diubah, supaya alamatnya langsung hidup
 * atau langsung mati tanpa perlu menyimpan ulang permalink.
 */
add_action( 'customize_save_after', 'haka_sitemap_flush' );

/**
 * Segarkan sekali setiap kali versi theme berganti.
 *
 * Diperlukan untuk jalur pembaruan: after_switch_theme tidak ikut terpicu
 * ketika seseorang memperbarui theme yang sudah aktif, sehingga aturan
 * rewrite baru tidak akan pernah tersimpan dan /news-sitemap.xml menjawab
 * 404 sampai pemilik situs menyimpan ulang pengaturan permalink sendiri.
 */
function haka_sitemap_flush_saat_versi_berganti() {
	if ( get_option( 'haka_rewrite_version' ) === HAKA_VERSION ) {
		return;
	}

	flush_rewrite_rules();
	update_option( 'haka_rewrite_version', HAKA_VERSION, false );
}
add_action( 'init', 'haka_sitemap_flush_saat_versi_berganti', 20 );

/* ==========================================================================
   Keluaran
   ========================================================================== */

/**
 * Cetak sitemap bila alamatnya yang diminta.
 */
function haka_sitemap_cetak() {
	if ( ! get_query_var( HAKA_SITEMAP_VAR ) ) {
		return;
	}

	if ( ! haka_news_sitemap_aktif() ) {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );

		return;
	}

	$artikel = get_posts(
		array(
			'post_type'        => 'post',
			'post_status'      => 'publish',
			'posts_per_page'   => 1000,
			'orderby'          => 'date',
			'order'            => 'DESC',
			'has_password'     => false,
			'suppress_filters' => false,
			'no_found_rows'    => true,
			'date_query'       => array(
				array( 'after' => '48 hours ago' ),
			),
		)
	);

	$nama = get_bloginfo( 'name' );
	$nama = '' !== trim( $nama ) ? $nama : wp_parse_url( home_url(), PHP_URL_HOST );

	// Bahasa dalam sitemap berita memakai kode dua huruf saja.
	$bahasa = strtolower( substr( get_locale(), 0, 2 ) );
	$bahasa = preg_match( '/^[a-z]{2}$/', $bahasa ) ? $bahasa : 'id';

	haka_jangan_cache();
	header( 'Content-Type: application/xml; charset=UTF-8' );
	header( 'X-Robots-Tag: noindex, follow', true );

	echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">' . "\n";

	foreach ( $artikel as $satu ) {
		echo "\t<url>\n";
		echo "\t\t<loc>" . esc_url( get_permalink( $satu ) ) . "</loc>\n";
		echo "\t\t<news:news>\n";
		echo "\t\t\t<news:publication>\n";
		echo "\t\t\t\t<news:name>" . esc_html( $nama ) . "</news:name>\n";
		echo "\t\t\t\t<news:language>" . esc_html( $bahasa ) . "</news:language>\n";
		echo "\t\t\t</news:publication>\n";
		echo "\t\t\t<news:publication_date>" . esc_html( get_post_time( 'c', true, $satu ) ) . "</news:publication_date>\n";
		echo "\t\t\t<news:title>" . esc_html( get_the_title( $satu ) ) . "</news:title>\n";
		echo "\t\t</news:news>\n";
		echo "\t</url>\n";
	}

	echo '</urlset>';

	exit;
}
add_action( 'template_redirect', 'haka_sitemap_cetak', 1 );

/**
 * Sebutkan sitemap berita di robots.txt.
 *
 * @param string $keluaran Isi robots.txt.
 * @param bool   $publik   Apakah situs boleh diindeks.
 * @return string
 */
function haka_sitemap_robots_txt( $keluaran, $publik ) {
	if ( ! $publik || ! haka_news_sitemap_aktif() ) {
		return $keluaran;
	}

	return $keluaran . "\nSitemap: " . esc_url( home_url( '/news-sitemap.xml' ) ) . "\n";
}
add_filter( 'robots_txt', 'haka_sitemap_robots_txt', 10, 2 );

/* ==========================================================================
   Pratinjau gambar
   ========================================================================== */

/**
 * Izinkan pratinjau gambar besar di hasil pencarian dan agregator.
 *
 * Tanpa penanda ini, gambar artikel sering ditampilkan sebagai thumbnail
 * kecil atau tidak sama sekali.
 *
 * @param array $robots Arahan robots.
 * @return array
 */
function haka_robots_gambar_besar( $robots ) {
	if ( ! haka_enabled( 'big_image_preview' ) ) {
		return $robots;
	}

	if ( ! empty( $robots['noindex'] ) ) {
		return $robots;
	}

	$robots['max-image-preview'] = 'large';
	$robots['max-snippet']       = '-1';
	$robots['max-video-preview'] = '-1';

	return $robots;
}
add_filter( 'wp_robots', 'haka_robots_gambar_besar' );

/**
 * Daftarkan pengaturan modul ini ke registry fitur.
 *
 * @param array $registry Registry fitur.
 * @return array
 */
function haka_sitemap_registry( $registry ) {
	$registry = haka_registry_add(
		$registry,
		'performance',
		array(
			'news_sitemap'    => array(
				'label'   => __( 'Sitemap berita', 'haka' ),
				'desc'    => __( 'Menerbitkan /news-sitemap.xml berisi artikel dua hari terakhir — format yang dibaca Google News. Otomatis mundur bila plugin SEO sudah menerbitkan sitemap berita. Perlu diingat: pendaftaran situs tetap dilakukan sendiri lewat Publisher Center, tidak ada theme yang bisa mendaftarkan untuk Anda.', 'haka' ),
				'type'    => 'checkbox',
				'default' => true,
			),
			'big_image_preview' => array(
				'label'   => __( 'Izinkan pratinjau gambar besar', 'haka' ),
				'desc'    => __( 'Menambahkan max-image-preview:large agar gambar artikel boleh tampil besar di hasil pencarian dan agregator.', 'haka' ),
				'type'    => 'checkbox',
				'default' => true,
			),
		)
	);
	return $registry;
}
add_filter( 'haka_feature_registry', 'haka_sitemap_registry' );
