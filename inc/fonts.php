<?php
/**
 * Font: daftar pilihan dan cara memuatnya.
 *
 * Bawaan theme memuat font dari dalam theme sendiri (assets/fonts), jadi
 * tidak ada permintaan ke server pihak ketiga saat halaman dimuat. Modul
 * opsional inc/fonts-google.php menambahkan pilihan Google Fonts.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sumber font yang dipakai: 'local' (bawaan) atau 'google'.
 *
 * Setelan font_source hanya ada bila modul Google Fonts dimuat; tanpa modul
 * itu nilainya selalu 'local'.
 *
 * @return string
 */
function haka_font_source() {
	return 'google' === haka_get_option( 'font_source', 'local' ) ? 'google' : 'local';
}

/**
 * Nama keluarga font yang dipilih untuk sebuah peran.
 *
 * @param string $role 'heading' atau 'body'.
 * @return string Nama font, atau string kosong bila memakai font perangkat.
 */
function haka_font_family( $role ) {
	// null agar nilai default dari registry dipakai, bukan string kosong.
	$choice = (string) haka_get_option( $role . '_font' );

	if ( 'system' === $choice || '' === trim( $choice ) ) {
		return '';
	}

	$choices = haka_font_choices();
	$family  = isset( $choices[ $choice ] ) ? $choice : '';

	/**
	 * Nama font akhir untuk sebuah peran; modul Google Fonts memakai ini
	 * untuk pilihan "Font lain".
	 *
	 * @param string $family Nama font, kosong bila tidak dikenal.
	 * @param string $role   Peran.
	 * @param string $choice Nilai pilihan mentah.
	 */
	return apply_filters( 'haka_font_family', $family, $role, $choice );
}

/**
 * Daftar font yang dibundel theme.
 *
 * @return array
 */
function haka_font_choices() {
	$fonts = haka_local_font_choices();

	/**
	 * Ubah daftar font yang tersedia.
	 *
	 * @param array $fonts Daftar font.
	 */
	return apply_filters( 'haka_font_choices', $fonts );
}

/**
 * Daftar font yang berkasnya ada di dalam theme.
 *
 * @return array
 */
function haka_local_font_choices() {
	return array(
		'system'            => __( '— Font bawaan perangkat —', 'haka' ),
		'Plus Jakarta Sans' => 'Plus Jakarta Sans',
		'Inter'             => 'Inter',
		'Roboto'            => 'Roboto',
		'Poppins'           => 'Poppins',
		'Montserrat'        => 'Montserrat',
		'Figtree'           => 'Figtree',
		'Manrope'           => 'Manrope',
		'Nunito Sans'       => 'Nunito Sans',
		'Merriweather'      => 'Merriweather (serif)',
	);
}

/**
 * Manifest berkas font: keluarga => daftar berkas, bobot, dan unicode-range.
 *
 * @return array
 */
function haka_font_manifest() {
	static $manifest = null;

	if ( null === $manifest ) {
		$file     = HAKA_DIR . '/assets/fonts/fonts.json';
		$manifest = file_exists( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : array(); // phpcs:ignore WordPress.WP.AlternativeFunctions

		if ( ! is_array( $manifest ) ) {
			$manifest = array();
		}
	}

	return $manifest;
}

/**
 * Aturan @font-face untuk keluarga font lokal yang dipilih.
 *
 * @return string CSS, kosong bila kedua peran memakai font perangkat.
 */
function haka_font_face_css() {
	$manifest  = haka_font_manifest();
	$families  = array_unique( array_filter( array( haka_font_family( 'heading' ), haka_font_family( 'body' ) ) ) );
	$css       = '';
	$base_path = HAKA_DIR . '/assets/fonts/';
	$base_url  = HAKA_URI . '/assets/fonts/';

	foreach ( $families as $family ) {
		if ( empty( $manifest[ $family ]['files'] ) ) {
			continue;
		}

		foreach ( $manifest[ $family ]['files'] as $font ) {
			if ( ! file_exists( $base_path . $font['file'] ) ) {
				continue;
			}

			$css .= sprintf(
				"@font-face{font-family:\"%s\";font-style:normal;font-weight:%s;font-display:swap;src:url(%s) format(\"woff2\");unicode-range:%s}\n",
				esc_attr( $family ),
				esc_attr( $font['weight'] ),
				esc_url( $base_url . $font['file'] ),
				esc_attr( $font['range'] )
			);
		}
	}

	return $css;
}

/**
 * Muat font yang dipilih.
 */
function haka_enqueue_fonts() {
	if ( 'local' !== haka_font_source() ) {
		/**
		 * Sumber font selain lokal memuat stylesheet-nya sendiri.
		 *
		 * @param string $source Sumber font.
		 */
		do_action( 'haka_enqueue_fonts_external', haka_font_source() );
		return;
	}

	$css = haka_font_face_css();

	if ( $css ) {
		wp_register_style( 'haka-fonts', false, array(), HAKA_VERSION );
		wp_enqueue_style( 'haka-fonts' );
		wp_add_inline_style( 'haka-fonts', $css );
	}
}

/**
 * Font yang sama dipakai di editor blok agar pratinjau mendekati tampilan situs.
 */
function haka_editor_fonts() {
	if ( 'local' !== haka_font_source() ) {
		return;
	}

	$css = haka_font_face_css();

	if ( $css ) {
		wp_add_inline_style( 'wp-edit-blocks', $css );
	}
}
add_action( 'enqueue_block_editor_assets', 'haka_editor_fonts' );
