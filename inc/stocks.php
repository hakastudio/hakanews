<?php
/**
 * Ticker indeks saham.
 *
 * Data diisi manual di Customizer, atau diambil dari URL JSON milik
 * pengguna sendiri. Theme sengaja tidak menempel ke penyedia data tertentu
 * supaya tidak bergantung pada layanan yang bisa berubah atau berbayar.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

/**
 * Ambil data ticker.
 *
 * @return array Daftar array dengan kunci name, value, change.
 */
function haka_get_stocks() {
	if ( ! haka_enabled( 'stocks_enable' ) ) {
		return array();
	}

	$source = haka_get_option( 'stocks_source', 'manual' );

	if ( 'endpoint' === $source ) {
		return haka_fetch_stocks_endpoint();
	}

	$rows  = haka_parse_lines( haka_get_option( 'stocks_items', '' ), 2 );
	$items = array();

	foreach ( $rows as $row ) {
		$items[] = array(
			'name'   => $row[0],
			'value'  => isset( $row[1] ) ? $row[1] : '',
			'change' => isset( $row[2] ) ? $row[2] : '',
		);
	}

	return $items;
}

/**
 * Ambil data dari URL JSON, disimpan sementara sesuai pengaturan.
 *
 * @return array
 */
function haka_fetch_stocks_endpoint() {
	$url = haka_get_option( 'stocks_endpoint', '' );

	if ( ! $url || ! wp_http_validate_url( $url ) ) {
		return array();
	}

	$minutes = max( 1, (int) haka_get_option( 'stocks_cache', 15 ) );
	$key     = 'haka_stocks_' . md5( $url );
	$cached  = get_transient( $key );

	if ( is_array( $cached ) ) {
		return $cached;
	}

	$response = wp_safe_remote_get(
		$url,
		array(
			'timeout' => 8,
			'headers' => array( 'Accept' => 'application/json' ),
		)
	);

	if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
		// Simpan hasil kosong sebentar agar situs tidak menunggu sumber yang mati.
		set_transient( $key, array(), 5 * MINUTE_IN_SECONDS );
		return array();
	}

	$data = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( ! is_array( $data ) ) {
		set_transient( $key, array(), 5 * MINUTE_IN_SECONDS );
		return array();
	}

	$items = array();

	foreach ( $data as $row ) {
		if ( ! is_array( $row ) || empty( $row['name'] ) ) {
			continue;
		}

		$items[] = array(
			'name'   => sanitize_text_field( (string) $row['name'] ),
			'value'  => isset( $row['value'] ) ? sanitize_text_field( (string) $row['value'] ) : '',
			'change' => isset( $row['change'] ) ? sanitize_text_field( (string) $row['change'] ) : '',
		);
	}

	$items = array_slice( $items, 0, 30 );
	set_transient( $key, $items, $minutes * MINUTE_IN_SECONDS );

	return $items;
}

/**
 * Tentukan arah perubahan dari teks, untuk pewarnaan.
 *
 * @param string $change Teks perubahan, misal "+0,84%".
 * @return string naik|turun|datar
 */
function haka_stock_direction( $change ) {
	$change = trim( (string) $change );

	if ( '' === $change ) {
		return 'datar';
	}

	if ( str_starts_with( $change, '-' ) || str_starts_with( $change, '−' ) ) {
		return 'turun';
	}

	if ( str_starts_with( $change, '+' ) ) {
		return 'naik';
	}

	return 'datar';
}

/**
 * Daftarkan pengaturan modul ini ke registry fitur.
 *
 * @param array $registry Registry fitur.
 * @return array
 */
function haka_stocks_registry( $registry ) {
	$registry = haka_registry_add(
		$registry,
		'widgets_extra',
		array(
			'stocks_enable' => array(
				'label'   => __( 'Ticker indeks saham', 'haka' ),
				'type'    => 'checkbox',
				'default' => false,
			),
			'stocks_title'  => array(
				'label'   => __( 'Label ticker', 'haka' ),
				'type'    => 'text',
				'default' => 'Bursa',
			),
			'stocks_source' => array(
				'label'   => __( 'Sumber data', 'haka' ),
				'type'    => 'select',
				'default' => 'manual',
				'choices' => array(
					'manual'   => __( 'Isi manual', 'haka' ),
					'endpoint' => __( 'Ambil dari URL JSON', 'haka' ),
				),
			),
			'stocks_items'  => array(
				'label'   => __( 'Data manual', 'haka' ),
				'desc'    => __( 'Satu baris satu instrumen, format: Nama|Nilai|Perubahan. Contoh: IHSG|7.245,10|+0,84%', 'haka' ),
				'type'    => 'textarea',
				'default' => '',
				'sanitize' => 'haka_sanitize_lines',
			),
			'stocks_endpoint' => array(
				'label'   => __( 'URL JSON', 'haka' ),
				'desc'    => __( 'Harus mengembalikan array objek berisi kunci name, value, change.', 'haka' ),
				'type'    => 'url',
				'default' => '',
			),
			'stocks_cache'  => array(
				'label'   => __( 'Simpan data berapa menit', 'haka' ),
				'type'    => 'number',
				'default' => 15,
				'min'     => 1,
				'max'     => 240,
			),
		),
		array(
			'title'    => __( 'Ticker Saham & Imsakiyah', 'haka' ),
			'priority' => 94,
		)
	);
	return $registry;
}
add_filter( 'haka_feature_registry', 'haka_stocks_registry' );

/**
 * Ticker saham tampil di bawah navigasi kedua.
 */
function haka_stocks_render() {
	get_template_part( 'template-parts/stocks' );
}
add_action( 'haka_after_secondary_nav', 'haka_stocks_render' );

/**
 * Ticker boleh disegarkan lewat REST di balik cache halaman.
 *
 * @param array $fragments Daftar fragmen.
 * @return array
 */
function haka_stocks_fragment( $fragments ) {
	$fragments['stocks'] = 'template-parts/stocks';

	return $fragments;
}
add_filter( 'haka_live_fragments', 'haka_stocks_fragment' );
