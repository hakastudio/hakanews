<?php
/**
 * Penghitung view sederhana untuk section "Terpopuler".
 *
 * Disengaja minimal: satu post meta per artikel, tanpa tabel tambahan dan
 * tanpa tracking pengunjung. Tidak menyimpan IP atau data pribadi.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

/**
 * Tambah satu hitungan view.
 *
 * Dipanggil dari endpoint REST (inc/compat.php) setelah halaman termuat di
 * peramban, bukan saat PHP merender halaman: dengan plugin cache, HTML
 * artikel disajikan tanpa menyentuh PHP sama sekali.
 *
 * @param int $post_id ID post.
 * @return bool True bila terhitung.
 */
function haka_count_view( $post_id ) {
	$post_id = absint( $post_id );

	if ( ! $post_id ) {
		return false;
	}

	// Hindari hitungan ganda dari sesi yang sama dalam waktu singkat.
	$cookie = 'haka_v_' . $post_id;

	if ( isset( $_COOKIE[ $cookie ] ) ) {
		return false;
	}

	// Pembatas per jaringan, supaya angka tidak bisa digelembungkan dengan
	// memanggil endpoint berulang tanpa cookie.
	if ( ! haka_view_rate_ok() ) {
		return false;
	}

	$views = (int) get_post_meta( $post_id, '_haka_views', true );
	update_post_meta( $post_id, '_haka_views', $views + 1 );

	if ( ! headers_sent() ) {
		setcookie( $cookie, '1', time() + HOUR_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), true );
	}

	return true;
}

/**
 * Batas jumlah view yang dicatat per jaringan pengunjung per 10 menit.
 *
 * @return bool True bila masih dalam batas.
 */
function haka_view_rate_ok() {
	$limit = (int) apply_filters( 'haka_view_rate_limit', 60 );

	if ( $limit <= 0 ) {
		return true;
	}

	$ip = '';

	foreach ( array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' ) as $key ) {
		if ( ! empty( $_SERVER[ $key ] ) ) {
			$raw = trim( explode( ',', sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) ) )[0] );

			if ( filter_var( $raw, FILTER_VALIDATE_IP ) ) {
				$ip = $raw;
				break;
			}
		}
	}

	if ( ! $ip ) {
		return true;
	}

	$key   = 'haka_vr_' . substr( hash( 'sha256', $ip . wp_salt( 'nonce' ) ), 0, 20 );
	$count = (int) get_transient( $key );

	if ( $count >= $limit ) {
		return false;
	}

	set_transient( $key, $count + 1, 10 * MINUTE_IN_SECONDS );

	return true;
}

/**
 * Ambil jumlah view sebuah post.
 *
 * @param int $post_id ID post.
 * @return int
 */
function haka_get_views( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();

	return (int) get_post_meta( $post_id, '_haka_views', true );
}

/**
 * Daftarkan meta agar aman diakses REST dan tidak bocor ke query publik.
 */
function haka_register_view_meta() {
	register_post_meta(
		'post',
		'_haka_views',
		array(
			'type'         => 'integer',
			'single'       => true,
			'default'      => 0,
			'show_in_rest' => false,
			'auth_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);
}
add_action( 'init', 'haka_register_view_meta' );

/**
 * Daftarkan pengaturan modul ini ke registry fitur.
 *
 * @param array $registry Registry fitur.
 * @return array
 */
function haka_views_registry( $registry ) {
	$registry = haka_registry_add(
		$registry,
		'single',
		array(
			'show_views'       => array(
				'label'   => __( 'Tampilkan jumlah pembaca', 'haka' ),
				'desc'    => __( 'Angka yang sudah dihitung theme untuk blok Terpopuler.', 'haka' ),
				'type'    => 'checkbox',
				'default' => true,
			),
		), array(), 'updated_badge'
	);
	return $registry;
}
add_filter( 'haka_feature_registry', 'haka_views_registry' );

/**
 * Endpoint hitungan view.
 */
function haka_register_view_route() {
	register_rest_route(
		'haka/v1',
		'/view/(?P<id>\d+)',
		array(
			'methods'             => 'POST',
			'callback'            => 'haka_rest_view',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'haka_register_view_route' );

/**
 * Catat satu view. Dipanggil dari peramban setelah halaman termuat, jadi
 * tetap terhitung walau HTML-nya disajikan dari cache.
 *
 * @param WP_REST_Request $request Permintaan.
 * @return WP_REST_Response|WP_Error
 */
function haka_rest_view( $request ) {
	$post_id = absint( $request['id'] );
	$post    = get_post( $post_id );

	if ( ! $post || 'publish' !== $post->post_status || 'post' !== $post->post_type ) {
		return new WP_Error( 'haka_not_found', __( 'Artikel tidak ditemukan.', 'haka' ), array( 'status' => 404 ) );
	}

	$counted = haka_count_view( $post_id );

	$response = rest_ensure_response( array( 'counted' => $counted, 'views' => haka_get_views( $post_id ) ) );
	$response->header( 'Cache-Control', 'no-store, max-age=0' );

	return $response;
}

/**
 * Jumlah pembaca di kepala artikel.
 */
function haka_views_render_article() {
	$views = haka_get_views();

	if ( ! haka_enabled( 'show_views' ) || $views <= 0 ) {
		return;
	}
	?>
	<span class="hk-article__views">
		<?php haka_icon( 'eye', array( 'size' => 14 ) ); ?>
		<?php
		printf(
			/* translators: %s: jumlah pembaca */
			esc_html__( '%s pembaca', 'haka' ),
			esc_html( number_format_i18n( $views ) )
		);
		?>
	</span>
	<?php
}
add_action( 'haka_article_meta_views', 'haka_views_render_article' );

/**
 * Jumlah pembaca pada baris meta kartu.
 */
function haka_views_render_meta() {
	$views = haka_get_views();

	if ( $views > 0 ) {
		printf(
			'<span class="hk-meta__item">%1$s%2$s</span>',
			haka_get_icon( 'eye', array( 'size' => 14 ) ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			esc_html( number_format_i18n( $views ) )
		);
	}
}
add_action( 'haka_meta_views', 'haka_views_render_meta' );

/**
 * Artikel terpopuler diurutkan dari jumlah view.
 *
 * @param int[] $ids        ID sebelumnya.
 * @param int   $number     Jumlah.
 * @param array $date_query Rentang tanggal.
 * @return int[]
 */
function haka_views_popular_ids( $ids, $number, $date_query ) {
	if ( count( $ids ) >= $number ) {
		return $ids;
	}

	$by_views = get_posts(
		haka_base_query_args(
			array(
				'posts_per_page' => $number - count( $ids ),
				'fields'         => 'ids',
				'meta_key'       => '_haka_views', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'orderby'        => 'meta_value_num',
				'order'          => 'DESC',
				'date_query'     => $date_query,
				'post__not_in'   => $ids,
			)
		)
	);

	return array_merge( $ids, array_map( 'absint', (array) $by_views ) );
}
add_filter( 'haka_popular_ids', 'haka_views_popular_ids', 10, 3 );

/**
 * Data skrip: endpoint view dan ID artikel yang sedang dibaca.
 *
 * @param array $data Data skrip.
 * @return array
 */
function haka_views_script_data( $data ) {
	$data['viewEndpoint'] = esc_url_raw( rest_url( 'haka/v1/view/' ) );
	$data['postId']       = is_singular( 'post' ) ? get_queried_object_id() : 0;

	return $data;
}
add_filter( 'haka_script_data', 'haka_views_script_data' );
