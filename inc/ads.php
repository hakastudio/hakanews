<?php
/**
 * Slot iklan yang sudah tersedia di posisi umum.
 *
 * Kode iklan disimpan apa adanya hanya bila penyuntingnya punya izin
 * unfiltered_html (lihat haka_sanitize_ad_code), sehingga editor tanpa izin
 * tidak dapat menyuntikkan script.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

/**
 * Cetak satu slot iklan.
 *
 * @param string $slot  Nama slot tanpa prefix, misal 'header'.
 * @param string $class Kelas tambahan.
 */
function haka_ad_slot( $slot, $class = '' ) {
	if ( ! haka_enabled( 'ads_enable' ) ) {
		return;
	}

	$code = trim( (string) haka_get_option( 'ad_' . $slot, '' ) );

	if ( '' === $code ) {
		return;
	}

	// Tiap landmark harus punya nama yang berbeda; beberapa <aside> dengan
	// label "Iklan" yang sama membuat navigasi landmark membingungkan.
	$names = array(
		'header'         => __( 'Iklan bawah header', 'haka' ),
		'before_content' => __( 'Iklan sebelum artikel', 'haka' ),
		'after_content'  => __( 'Iklan setelah artikel', 'haka' ),
		'sidebar'        => __( 'Iklan sidebar', 'haka' ),
		'footer'         => __( 'Iklan atas footer', 'haka' ),
	);

	$name = isset( $names[ $slot ] ) ? $names[ $slot ] : __( 'Iklan', 'haka' );

	printf(
		'<aside class="hk-ad hk-ad--%1$s %2$s" aria-label="%3$s"><span class="hk-ad__label">%4$s</span><div class="hk-ad__body">%5$s</div></aside>',
		esc_attr( $slot ),
		esc_attr( $class ),
		esc_attr( $name ),
		esc_html__( 'Iklan', 'haka' ),
		$code // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- disanitasi saat disimpan sesuai izin pengguna.
	);
}

/**
 * Blok Ad Awareness khusus ponsel.
 *
 * Disembunyikan lewat CSS di layar besar, bukan dihapus, agar tidak
 * bergantung pada deteksi perangkat di sisi server yang mudah meleset.
 */
function haka_ad_awareness() {
	if ( ! haka_enabled( 'ads_enable' ) || ! haka_enabled( 'ad_awareness' ) ) {
		return;
	}

	if ( ! is_front_page() && ! is_home() && ! is_singular( 'post' ) ) {
		return;
	}

	// Dibungkus <aside> agar seluruh isi halaman berada di dalam landmark.
	printf(
		'<aside class="hk-adaware" aria-label="%1$s"><span>%2$s</span></aside>',
		esc_attr__( 'Pemberitahuan iklan', 'haka' ),
		esc_html( haka_get_option( 'ad_awareness_text', __( 'Iklan - Scroll ke bawah untuk melanjutkan', 'haka' ) ) )
	);
}

/**
 * Daftarkan pengaturan modul ini ke registry fitur.
 *
 * @param array $registry Registry fitur.
 * @return array
 */
function haka_ads_registry( $registry ) {
	$registry = haka_registry_add(
		$registry,
		'ads',
		array(
			'ads_enable'      => array(
				'label'   => __( 'Aktifkan slot iklan', 'haka' ),
				'type'    => 'checkbox',
				'default' => false,
			),
			'ad_awareness'    => array(
				'label'   => __( 'Ad Awareness (hanya ponsel)', 'haka' ),
				'desc'    => __( 'Blok pemberitahuan iklan di atas konten, tampil hanya di layar kecil.', 'haka' ),
				'type'    => 'checkbox',
				'default' => false,
			),
			'ad_awareness_text' => array(
				'label'   => __( 'Teks Ad Awareness', 'haka' ),
				'type'    => 'text',
				'default' => 'Iklan - Scroll ke bawah untuk melanjutkan',
			),
			'ad_header'       => array(
				'label'   => __( 'Slot: bawah header', 'haka' ),
				'desc'    => __( 'Tempel kode iklan di sini. Hanya administrator yang boleh menyunting.', 'haka' ),
				'type'    => 'textarea',
				'default' => '',
				'sanitize' => 'haka_sanitize_ad_code',
			),
			'ad_before_content' => array(
				'label'   => __( 'Slot: sebelum isi artikel', 'haka' ),
				'type'    => 'textarea',
				'default' => '',
				'sanitize' => 'haka_sanitize_ad_code',
			),
			'ad_after_content'  => array(
				'label'   => __( 'Slot: setelah isi artikel', 'haka' ),
				'type'    => 'textarea',
				'default' => '',
				'sanitize' => 'haka_sanitize_ad_code',
			),
			'ad_sidebar'      => array(
				'label'   => __( 'Slot: sidebar', 'haka' ),
				'type'    => 'textarea',
				'default' => '',
				'sanitize' => 'haka_sanitize_ad_code',
			),
			'ad_footer'       => array(
				'label'   => __( 'Slot: atas footer', 'haka' ),
				'type'    => 'textarea',
				'default' => '',
				'sanitize' => 'haka_sanitize_ad_code',
			),
		),
		array(
			'title'    => __( 'Posisi Iklan', 'haka' ),
			'priority' => 96,
		)
	);
	return $registry;
}
add_filter( 'haka_feature_registry', 'haka_ads_registry' );

add_action( 'haka_ad_slot', 'haka_ad_slot', 10, 2 );
add_action( 'haka_ad_awareness', 'haka_ad_awareness' );
