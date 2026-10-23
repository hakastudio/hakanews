<?php
/**
 * Google Fonts sebagai sumber font pilihan.
 *
 * Bawaan theme memakai font yang dibundel (inc/fonts.php). Modul ini
 * menambahkan setelan "Sumber font" di Customizer; bila diatur ke Google
 * Fonts, daftar pilihan bertambah dan font dimuat dari server Google.
 * Catatan privasi: setiap pengunjung mengirim alamat IP-nya ke Google.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

/**
 * Daftar font Google yang disediakan.
 *
 * Dipilih yang terbukti nyaman untuk portal berita: rapat, banyak bobot,
 * dan mendukung karakter Latin yang dipakai bahasa Indonesia.
 *
 * @return array
 */
function haka_google_font_list() {
	return array(
		'Plus Jakarta Sans' => 'Plus Jakarta Sans',
		'Inter'             => 'Inter',
		'Open Sans'         => 'Open Sans',
		'Roboto'            => 'Roboto',
		'Source Sans 3'     => 'Source Sans 3',
		'Noto Sans'         => 'Noto Sans',
		'IBM Plex Sans'     => 'IBM Plex Sans',
		'Figtree'           => 'Figtree',
		'Manrope'           => 'Manrope',
		'Work Sans'         => 'Work Sans',
		'DM Sans'           => 'DM Sans',
		'Nunito Sans'       => 'Nunito Sans',
		'Lato'              => 'Lato',
		'Poppins'           => 'Poppins',
		'Montserrat'        => 'Montserrat',
		'Rubik'             => 'Rubik',
		'Karla'             => 'Karla',
		'Outfit'            => 'Outfit',
		'Sora'              => 'Sora',
		'Space Grotesk'     => 'Space Grotesk',
		'Archivo'           => 'Archivo',
		'Barlow'            => 'Barlow',
		'Public Sans'       => 'Public Sans',
		'Libre Franklin'    => 'Libre Franklin',

		'Merriweather'      => 'Merriweather (serif)',
		'Playfair Display'  => 'Playfair Display (serif)',
		'Lora'              => 'Lora (serif)',
		'Source Serif 4'    => 'Source Serif 4 (serif)',
		'Newsreader'        => 'Newsreader (serif)',
		'Bitter'            => 'Bitter (serif)',
		'Roboto Slab'       => 'Roboto Slab (slab)',
	);
}

/**
 * Saat sumber font Google aktif, daftar pilihan diperluas.
 *
 * @param array $fonts Daftar font lokal.
 * @return array
 */
function haka_google_font_choices( $fonts ) {
	if ( 'google' !== haka_font_source() ) {
		return $fonts;
	}

	$fonts = array_merge( $fonts, haka_google_font_list() );

	$fonts['custom'] = __( '— Font lain (tulis namanya di bawah) —', 'haka' );

	return $fonts;
}
add_filter( 'haka_font_choices', 'haka_google_font_choices' );

/**
 * Pilihan "Font lain": nama diambil dari kolom teks.
 *
 * @param string $family Nama font hasil pencocokan daftar.
 * @param string $role   Peran.
 * @param string $choice Nilai pilihan mentah.
 * @return string
 */
function haka_google_custom_family( $family, $role, $choice ) {
	if ( 'custom' === $choice && 'google' === haka_font_source() ) {
		return trim( (string) haka_get_option( $role . '_font_custom' ) );
	}

	return $family;
}
add_filter( 'haka_font_family', 'haka_google_custom_family', 10, 3 );

/**
 * URL Google Fonts untuk font yang dipilih.
 *
 * @return string Kosong bila kedua peran memakai font perangkat.
 */
function haka_google_fonts_url() {
	// Bobot diminta per peran, bukan borongan: font judul tidak memerlukan
	// bobot teks, dan sebaliknya. Setiap bobot tambahan adalah satu berkas
	// yang harus diunduh pembaca.
	$heading_weight = (int) haka_get_option( 'heading_weight', '800' );
	$body_weight    = (int) haka_get_option( 'body_weight', '400' );

	$needs = array(
		'heading' => array( $heading_weight, 700 ),
		'body'    => array( $body_weight, 600, 700 ),
	);

	$families = array();

	foreach ( array( 'heading', 'body' ) as $role ) {
		$name = haka_font_family( $role );

		if ( ! $name ) {
			continue;
		}

		if ( ! isset( $families[ $name ] ) ) {
			$families[ $name ] = array();
		}

		$families[ $name ] = array_merge( $families[ $name ], $needs[ $role ] );
	}

	if ( empty( $families ) ) {
		return '';
	}

	$parts = array();

	foreach ( $families as $name => $weights ) {
		$slug = preg_replace( '/[^A-Za-z0-9 \-]/', '', $name );
		$slug = str_replace( ' ', '+', trim( $slug ) );

		if ( '' === $slug ) {
			continue;
		}

		$weights = array_values( array_unique( array_filter( array_map( 'intval', $weights ) ) ) );
		sort( $weights );

		$parts[] = 'family=' . $slug . ':wght@' . implode( ';', $weights );
	}

	if ( empty( $parts ) ) {
		return '';
	}

	return 'https://fonts.googleapis.com/css2?' . implode( '&', $parts ) . '&display=swap';
}

/**
 * Muat stylesheet Google Fonts bila sumber font diatur ke Google.
 *
 * @param string $source Sumber font.
 */
function haka_google_enqueue_fonts( $source ) {
	if ( 'google' !== $source ) {
		return;
	}

	// Hanya bobot yang benar-benar dipakai yang diminta, supaya unduhan
	// tetap kecil.
	$font_url = haka_google_fonts_url();

	if ( $font_url ) {
		wp_enqueue_style( 'haka-fonts', $font_url, array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
	}
}
add_action( 'haka_enqueue_fonts_external', 'haka_google_enqueue_fonts' );

/**
 * Preconnect ke domain font agar Open Sans terunduh lebih cepat.
 *
 * @param array  $urls          Daftar URL.
 * @param string $relation_type Jenis relasi.
 * @return array
 */
function haka_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type && wp_style_is( 'haka-fonts', 'enqueued' ) ) {
		$urls[] = array(
			'href' => 'https://fonts.gstatic.com',
			'crossorigin',
		);
	}

	return $urls;
}
add_filter( 'wp_resource_hints', 'haka_resource_hints', 10, 2 );

/**
 * Sanitasi nama font.
 *
 * Hanya huruf, angka, spasi dan tanda hubung: nilai ini masuk ke URL
 * Google Fonts, jadi tidak boleh memuat karakter lain.
 *
 * @param mixed $value Nilai mentah.
 * @return string
 */
function haka_sanitize_font_name( $value ) {
	$value = preg_replace( '/[^A-Za-z0-9 \-]/', '', (string) $value );

	return trim( substr( $value, 0, 60 ) );
}

/**
 * Setelan sumber font dan nama font kustom di section Tipografi.
 *
 * @param array $registry Registry fitur.
 * @return array
 */
function haka_google_fonts_registry( $registry ) {
	$registry = haka_registry_add(
		$registry,
		'typography',
		array(
			'font_source' => array(
				'label'   => __( 'Sumber font', 'haka' ),
				'desc'    => __( 'Font bawaan dibundel di dalam theme: lebih cepat dan tidak mengirim data pengunjung ke pihak ketiga. Google Fonts membuka pilihan lebih banyak, tetapi setiap pengunjung terhubung ke server Google.', 'haka' ),
				'type'    => 'select',
				'default' => 'local',
				'choices' => array(
					'local'  => __( 'Bawaan theme (9 font, tanpa permintaan ke luar)', 'haka' ),
					'google' => __( 'Google Fonts (33 font, dimuat dari Google)', 'haka' ),
				),
			),
		),
		array(),
		'heading_font'
	);

	return haka_registry_add(
		$registry,
		'typography',
		array(
			'heading_font_custom' => array(
				'label'    => __( 'Nama font judul kustom', 'haka' ),
				'desc'     => __( 'Diisi bila pilihan di atas diatur ke "Font lain". Tulis persis seperti di Google Fonts, contoh: Bricolage Grotesque.', 'haka' ),
				'type'     => 'text',
				'default'  => '',
				'sanitize' => 'haka_sanitize_font_name',
			),
			'body_font_custom'    => array(
				'label'    => __( 'Nama font teks kustom', 'haka' ),
				'type'     => 'text',
				'default'  => '',
				'sanitize' => 'haka_sanitize_font_name',
			),
		),
		array(),
		'base_size'
	);
}
add_filter( 'haka_feature_registry', 'haka_google_fonts_registry' );

/**
 * Kolom nama font kustom diurutkan tepat di bawah pilihan fontnya.
 *
 * @param array $registry Registry fitur.
 * @return array
 */
function haka_google_fonts_registry_order( $registry ) {
	if ( ! isset( $registry['typography']['fields'] ) ) {
		return $registry;
	}

	$fields = $registry['typography']['fields'];
	$urut   = array();

	foreach ( $fields as $key => $field ) {
		if ( in_array( $key, array( 'heading_font_custom', 'body_font_custom' ), true ) ) {
			continue;
		}

		$urut[ $key ] = $field;

		if ( 'heading_font' === $key && isset( $fields['heading_font_custom'] ) ) {
			$urut['heading_font_custom'] = $fields['heading_font_custom'];
		}

		if ( 'body_font' === $key && isset( $fields['body_font_custom'] ) ) {
			$urut['body_font_custom'] = $fields['body_font_custom'];
		}
	}

	$registry['typography']['fields'] = $urut;

	return $registry;
}
add_filter( 'haka_feature_registry', 'haka_google_fonts_registry_order', 20 );

/**
 * Situs yang sudah memakai font Google sebelum bawaan berubah ke lokal
 * tetap memakai Google Fonts, supaya tampilannya tidak berubah diam-diam
 * setelah pembaruan. Dijalankan sekali per situs.
 */
function haka_google_fonts_migrate() {
	if ( get_option( 'haka_font_source_migrated' ) ) {
		return;
	}

	$lokal = haka_local_font_choices();

	foreach ( array( 'heading', 'body' ) as $role ) {
		$choice = (string) get_theme_mod( 'haka_' . $role . '_font', '' );

		if ( '' !== $choice && ! isset( $lokal[ $choice ] ) ) {
			set_theme_mod( 'haka_font_source', 'google' );
			break;
		}
	}

	update_option( 'haka_font_source_migrated', 1, false );
}
add_action( 'init', 'haka_google_fonts_migrate', 5 );
