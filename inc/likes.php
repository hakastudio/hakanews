<?php
/**
 * Tombol "Suka artikel ini".
 *
 * Menyimpan satu angka pada post meta dan mencatat pilihan pembaca di cookie
 * agar tidak menghitung ganda. Tidak menyimpan identitas atau IP pembaca.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

/**
 * Jumlah suka sebuah artikel.
 *
 * @param int $post_id ID post.
 * @return int
 */
function haka_get_likes( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();

	return max( 0, (int) get_post_meta( $post_id, '_haka_likes', true ) );
}

/**
 * Daftarkan endpoint REST untuk menyukai artikel.
 */
function haka_register_like_route() {
	register_rest_route(
		'haka/v1',
		'/like/(?P<id>\d+)',
		array(
			'methods'             => 'POST',
			'callback'            => 'haka_handle_like',
			'permission_callback' => '__return_true',
			'args'                => array(
				'id' => array(
					'validate_callback' => function ( $value ) {
						return is_numeric( $value );
					},
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'haka_register_like_route' );

/**
 * Tangani permintaan suka.
 *
 * @param WP_REST_Request $request Permintaan.
 * @return WP_REST_Response|WP_Error
 */
function haka_handle_like( $request ) {
	if ( ! haka_enabled( 'likes_enable' ) ) {
		return new WP_Error( 'haka_disabled', __( 'Fitur suka tidak aktif.', 'haka' ), array( 'status' => 403 ) );
	}

	$post_id = absint( $request['id'] );
	$post    = get_post( $post_id );

	if ( ! $post || 'publish' !== $post->post_status || 'post' !== $post->post_type ) {
		return new WP_Error( 'haka_not_found', __( 'Artikel tidak ditemukan.', 'haka' ), array( 'status' => 404 ) );
	}

	$action = 'like' === $request->get_param( 'action' ) ? 'like' : 'unlike';
	$count  = haka_get_likes( $post_id );

	// Pembatas sisi server. Cookie saja tidak cukup: siapa pun bisa memanggil
	// endpoint berulang dengan cookie baru dan menggelembungkan angka.
	// IP tidak disimpan mentah, hanya sidik jaringnya.
	if ( ! haka_like_rate_ok() ) {
		return new WP_Error(
			'haka_too_many',
			__( 'Terlalu banyak permintaan. Coba lagi nanti.', 'haka' ),
			array( 'status' => 429 )
		);
	}

	// Satu suka per pembaca, ditandai lewat cookie miliknya sendiri.
	// Sengaja tidak memakai user-agent: nilainya sama untuk banyak orang,
	// sehingga satu pembaca bisa menghalangi pembaca lain.
	$cookie = 'haka_liked_' . $post_id;
	$has    = isset( $_COOKIE[ $cookie ] );

	if ( ( 'like' === $action && $has ) || ( 'unlike' === $action && ! $has ) ) {
		return rest_ensure_response(
			array(
				'count'  => $count,
				'action' => $action,
				'noop'   => true,
			)
		);
	}

	$count = ( 'like' === $action ) ? $count + 1 : max( 0, $count - 1 );
	update_post_meta( $post_id, '_haka_likes', $count );

	if ( ! headers_sent() ) {
		$path = COOKIEPATH ? COOKIEPATH : '/';

		if ( 'like' === $action ) {
			setcookie( $cookie, '1', time() + YEAR_IN_SECONDS, $path, COOKIE_DOMAIN, is_ssl(), true );
		} else {
			setcookie( $cookie, '', time() - 3600, $path, COOKIE_DOMAIN, is_ssl(), true );
		}
	}

	return rest_ensure_response(
		array(
			'count'  => $count,
			'action' => $action,
		)
	);
}

/**
 * Batas jumlah aksi suka per jaringan pengunjung.
 *
 * @return bool True bila masih dalam batas.
 */
function haka_like_rate_ok() {
	$limit = (int) apply_filters( 'haka_like_rate_limit', 15 );

	if ( $limit <= 0 ) {
		return true;
	}

	$ip = '';

	foreach ( array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' ) as $key ) {
		if ( ! empty( $_SERVER[ $key ] ) ) {
			$raw = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );
			$raw = trim( explode( ',', $raw )[0] );

			if ( filter_var( $raw, FILTER_VALIDATE_IP ) ) {
				$ip = $raw;
				break;
			}
		}
	}

	if ( '' === $ip ) {
		return true;
	}

	// Disimpan sebagai hash bergaram: alamat aslinya tidak pernah tersimpan.
	$key   = 'haka_lrate_' . substr( hash_hmac( 'sha256', $ip, wp_salt( 'nonce' ) ), 0, 20 );
	$count = (int) get_transient( $key );

	if ( $count >= $limit ) {
		return false;
	}

	set_transient( $key, $count + 1, HOUR_IN_SECONDS );

	return true;
}

/**
 * Daftarkan meta suka.
 */
function haka_register_like_meta() {
	register_post_meta(
		'post',
		'_haka_likes',
		array(
			'type'          => 'integer',
			'single'        => true,
			'default'       => 0,
			'show_in_rest'  => false,
			'auth_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);
}
add_action( 'init', 'haka_register_like_meta' );

/**
 * Daftarkan pengaturan modul ini ke registry fitur.
 *
 * @param array $registry Registry fitur.
 * @return array
 */
function haka_likes_registry( $registry ) {
	$registry = haka_registry_add(
		$registry,
		'engagement',
		array(
			'likes_enable'      => array(
				'label'   => __( 'Tombol "Suka artikel ini"', 'haka' ),
				'type'    => 'checkbox',
				'default' => true,
			),
			'likes_position'    => array(
				'label'   => __( 'Posisi tombol suka', 'haka' ),
				'type'    => 'select',
				'default' => 'with_share',
				'choices' => array(
					'with_share' => __( 'Sebaris dengan tombol bagikan', 'haka' ),
					'block'      => __( 'Blok terpisah di bawah artikel', 'haka' ),
				),
			),
			'likes_label'       => array(
				'label'   => __( 'Teks ajakan', 'haka' ),
				'type'    => 'text',
				'default' => 'Suka artikel ini?',
			),
		), array(), 'fontsize_position'
	);
	return $registry;
}
add_filter( 'haka_feature_registry', 'haka_likes_registry' );

/**
 * Tombol suka sebaris dengan tombol bagikan?
 *
 * @param bool $active Nilai sebelumnya.
 * @return bool
 */
function haka_likes_inline_active( $active ) {
	if ( haka_enabled( 'likes_enable' ) && 'with_share' === haka_get_option( 'likes_position', 'with_share' ) ) {
		return true;
	}

	return $active;
}
add_filter( 'haka_actions_inline_active', 'haka_likes_inline_active' );

/**
 * Cetak tombol suka sebaris dengan tombol bagikan.
 */
function haka_likes_render_inline() {
	if ( haka_likes_inline_active( false ) ) {
		get_template_part( 'template-parts/post/likes', null, array( 'inline' => true ) );
	}
}
add_action( 'haka_actions_inline', 'haka_likes_render_inline' );

/**
 * Cetak blok suka terpisah di bawah artikel.
 */
function haka_likes_render_block() {
	if ( haka_enabled( 'likes_enable' ) && 'block' === haka_get_option( 'likes_position', 'with_share' ) ) {
		get_template_part( 'template-parts/post/likes' );
	}
}
add_action( 'haka_after_article_content', 'haka_likes_render_block' );

/**
 * Alamat endpoint suka untuk skrip theme.
 *
 * @param array $data Data skrip.
 * @return array
 */
function haka_likes_script_data( $data ) {
	$data['likeEndpoint'] = esc_url_raw( rest_url( 'haka/v1/like/' ) );

	return $data;
}
add_filter( 'haka_script_data', 'haka_likes_script_data' );
