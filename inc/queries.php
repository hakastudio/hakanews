<?php
/**
 * Helper WP_Query untuk seluruh section theme.
 *
 * Semua helper mengembalikan WP_Query baru dan memakai wp_reset_postdata()
 * di sisi template, sehingga global $post tidak pernah rusak.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

/**
 * Simpan / ambil ID post yang sudah tampil di homepage agar tidak dobel.
 *
 * @param array $ids ID yang ingin ditandai terpakai.
 * @return array Seluruh ID yang sudah terpakai.
 */
function haka_used_ids( $ids = array() ) {
	static $used = array();

	if ( ! empty( $ids ) ) {
		$used = array_unique( array_merge( $used, array_map( 'absint', (array) $ids ) ) );
	}

	return $used;
}

/**
 * Argumen dasar query berita.
 *
 * @param array $args Argumen tambahan.
 * @return array
 */
function haka_base_query_args( $args = array() ) {
	$defaults = array(
		'post_type'              => 'post',
		'post_status'            => 'publish',
		'ignore_sticky_posts'    => true,
		'no_found_rows'          => true,
		'update_post_term_cache' => true,
		'update_post_meta_cache' => false,
	);

	return wp_parse_args( $args, $defaults );
}

/**
 * Post untuk breaking news ticker.
 *
 * @return WP_Query
 */
function haka_query_breaking() {
	$args = haka_base_query_args(
		array(
			'posts_per_page' => (int) haka_get_option( 'breaking_count', 6 ),
		)
	);

	$category = (int) haka_get_option( 'breaking_category', 0 );

	if ( $category ) {
		$args['cat'] = $category;
	}

	return new WP_Query( apply_filters( 'haka_query_breaking_args', $args ) );
}

/**
 * Post untuk hero carousel.
 *
 * Sumber mengikuti pengaturan Customizer: berita terbaru, kategori pilihan,
 * atau post yang disematkan. Setiap sumber punya fallback ke berita terbaru
 * supaya hero tidak pernah kosong.
 *
 * @param int|null $number Jumlah slide. Null memakai nilai Customizer.
 * @return WP_Query
 */
function haka_query_hero( $number = null ) {
	$number = ( null === $number ) ? (int) haka_get_option( 'hero_count', 5 ) : absint( $number );
	$number = max( 1, min( 10, $number ) );

	if ( ! haka_get_option( 'hero_carousel', true ) ) {
		$number = 1;
	}

	$source = haka_get_option( 'hero_source', 'latest' );

	$args = haka_base_query_args(
		array(
			'posts_per_page' => $number,
		)
	);

	if ( 'category' === $source ) {
		$category = (int) haka_get_option( 'featured_category', 0 );

		if ( $category ) {
			$args['cat'] = $category;
		}
	} elseif ( 'sticky' === $source ) {
		$sticky = get_option( 'sticky_posts' );

		if ( ! empty( $sticky ) ) {
			$args['post__in'] = array_map( 'absint', (array) $sticky );
			$args['orderby']  = 'date';
		}
	}

	$query = new WP_Query( apply_filters( 'haka_query_hero_args', $args ) );

	// Fallback: sumber pilihan kosong, pakai berita terbaru.
	if ( ! $query->have_posts() && 'latest' !== $source ) {
		unset( $args['cat'], $args['post__in'] );
		$query = new WP_Query( $args );
	}

	return $query;
}

/**
 * Post terbaru untuk section "Berita Terbaru" dengan pagination.
 *
 * @param int $paged Halaman aktif.
 * @return WP_Query
 */
function haka_query_latest( $paged = 1 ) {
	$paged   = max( 1, absint( $paged ) );
	$exclude = haka_used_ids();

	// Artikel yang disematkan tampil paling atas di halaman pertama, seperti
	// perilaku bawaan WordPress pada daftar tulisan.
	$sticky = array();

	if ( 1 === $paged ) {
		$sticky = array_diff( array_map( 'absint', (array) get_option( 'sticky_posts' ) ), $exclude );
	}

	$args = haka_base_query_args(
		array(
			'posts_per_page' => max( 5, min( 30, (int) haka_get_option( 'latest_count', 12 ) ) ),
			'paged'          => $paged,
			'no_found_rows'  => false,
			'post__not_in'   => array_merge( $exclude, $sticky ),
		)
	);

	$query = new WP_Query( apply_filters( 'haka_query_latest_args', $args ) );

	if ( $sticky ) {
		$sticky_posts = get_posts(
			haka_base_query_args(
				array(
					'post__in'       => array_values( $sticky ),
					'orderby'        => 'post__in',
					'posts_per_page' => count( $sticky ),
				)
			)
		);

		if ( $sticky_posts ) {
			$query->posts      = array_merge( $sticky_posts, $query->posts );
			$query->post_count = count( $query->posts );
		}
	}

	return $query;
}

/**
 * Post dari satu kategori untuk blok kategori.
 *
 * @param int $category_id ID kategori.
 * @param int $number      Jumlah post.
 * @return WP_Query
 */
function haka_query_category( $category_id, $number = 5, $avoid_repeat = false ) {
	$args = haka_base_query_args(
		array(
			'cat'            => absint( $category_id ),
			'posts_per_page' => absint( $number ),
		)
	);

	if ( ! $avoid_repeat ) {
		return new WP_Query( $args );
	}

	// Hindari mengulang artikel yang sudah tampil di section lain. Bila
	// kategorinya jadi terlalu sepi, tampilkan apa adanya daripada kosong.
	$args['post__not_in'] = haka_used_ids();
	$query                = new WP_Query( $args );

	if ( $query->post_count < 2 ) {
		unset( $args['post__not_in'] );
		$query = new WP_Query( $args );
	}

	return $query;
}

/**
 * Kategori yang dipakai untuk blok homepage.
 *
 * @param int $limit Jumlah maksimum blok.
 * @return WP_Term[]
 */
function haka_get_block_categories( $limit = 4 ) {
	$configured = haka_get_option( 'block_categories', '' );
	$terms      = array();

	if ( $configured ) {
		$ids = array_filter( array_map( 'absint', explode( ',', $configured ) ) );

		foreach ( $ids as $id ) {
			$term = get_term( $id, 'category' );

			if ( $term && ! is_wp_error( $term ) ) {
				$terms[] = $term;
			}
		}
	}

	if ( empty( $terms ) ) {
		$auto = get_terms(
			array(
				'taxonomy'   => 'category',
				'hide_empty' => true,
				'orderby'    => 'count',
				'order'      => 'DESC',
				'number'     => absint( $limit ),
				'exclude'    => array( (int) get_option( 'default_category' ) ),
			)
		);

		if ( ! is_wp_error( $auto ) ) {
			$terms = $auto;
		}
	}

	// Kategori yang isinya terlalu sedikit dilewati: blok berisi satu artikel
	// yang sudah tampil di atasnya justru terlihat seperti kesalahan.
	$minimum = (int) apply_filters( 'haka_block_min_posts', 3 );
	$terms   = array_filter(
		$terms,
		function ( $term ) use ( $minimum ) {
			return isset( $term->count ) && (int) $term->count >= $minimum;
		}
	);
	$terms = array_values( $terms );

	return array_slice( $terms, 0, absint( $limit ) );
}

/**
 * Post video: berdasarkan kategori video, atau post format video.
 *
 * @param int $number Jumlah post.
 * @return WP_Query
 */
function haka_query_video( $number = 4 ) {
	$category = (int) haka_get_option( 'video_category', 0 );

	$args = haka_base_query_args(
		array(
			'posts_per_page' => absint( $number ),
		)
	);

	if ( $category ) {
		$args['cat'] = $category;
	} else {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'post_format',
				'field'    => 'slug',
				'terms'    => array( 'post-format-video' ),
			),
		);
	}

	$query = new WP_Query( $args );

	// Fallback: kalau tidak ada post format video, pakai post yang punya embed video.
	if ( ! $query->have_posts() && ! $category ) {
		$fallback = haka_base_query_args(
			array(
				'posts_per_page' => absint( $number ),
				's'              => '',
				'meta_query'     => array(
					array(
						'key'     => '_haka_has_video',
						'compare' => 'EXISTS',
					),
				),
			)
		);

		$query = new WP_Query( $fallback );
	}

	return $query;
}

/**
 * Post terpopuler: view count, lalu dilengkapi jumlah komentar.
 *
 * @param int $number Jumlah post.
 * @return int[] Daftar ID post.
 */
function haka_get_popular_ids( $number = 5 ) {
	$number = absint( $number );
	$days   = (int) haka_get_option( 'popular_range', 30 );

	$cache_key = 'haka_popular_' . $number . '_' . $days;
	$cached    = get_transient( $cache_key );

	if ( is_array( $cached ) ) {
		return $cached;
	}

	$date_query = array(
		array(
			'after'     => $days . ' days ago',
			'inclusive' => true,
		),
	);

	/**
	 * Urutan utama artikel populer. Modul hitungan view mengisinya dari
	 * jumlah pembaca; tanpa modul itu, urutan jatuh ke jumlah komentar.
	 *
	 * @param int[] $ids        ID artikel.
	 * @param int   $number     Jumlah yang diminta.
	 * @param array $date_query Rentang tanggal.
	 */
	$ids = array_map( 'absint', (array) apply_filters( 'haka_popular_ids', array(), $number, $date_query ) );
	$ids = array_slice( array_values( array_unique( $ids ) ), 0, $number );

	// Pelengkap: post dengan komentar terbanyak.
	if ( count( $ids ) < $number ) {
		$by_comments = get_posts(
			haka_base_query_args(
				array(
					'posts_per_page' => $number - count( $ids ),
					'fields'         => 'ids',
					'orderby'        => array(
						'comment_count' => 'DESC',
						'date'          => 'DESC',
					),
					'date_query'     => $date_query,
					'post__not_in'   => $ids,
				)
			)
		);

		$ids = array_merge( $ids, array_map( 'absint', (array) $by_comments ) );
	}

	// Pelengkap terakhir: post terbaru tanpa batas tanggal.
	if ( count( $ids ) < $number ) {
		$recent = get_posts(
			haka_base_query_args(
				array(
					'posts_per_page' => $number - count( $ids ),
					'fields'         => 'ids',
					'post__not_in'   => $ids,
				)
			)
		);

		$ids = array_merge( $ids, array_map( 'absint', (array) $recent ) );
	}

	$ids = array_slice( array_unique( $ids ), 0, $number );

	set_transient( $cache_key, $ids, HOUR_IN_SECONDS );

	return $ids;
}

/**
 * WP_Query untuk post terpopuler.
 *
 * @param int $number Jumlah post.
 * @return WP_Query
 */
function haka_query_popular( $number = 5 ) {
	$ids = haka_get_popular_ids( $number );

	if ( empty( $ids ) ) {
		return new WP_Query( array( 'post__in' => array( 0 ) ) );
	}

	return new WP_Query(
		haka_base_query_args(
			array(
				'post__in'       => $ids,
				'orderby'        => 'post__in',
				'posts_per_page' => count( $ids ),
			)
		)
	);
}

/**
 * Artikel terkait berdasarkan kategori, lalu tag.
 *
 * @param int $post_id ID post.
 * @param int $number  Jumlah post.
 * @return WP_Query
 */
function haka_query_related( $post_id, $number = 4 ) {
	$post_id    = absint( $post_id );
	$categories = wp_get_post_categories( $post_id );
	$tags       = wp_get_post_tags( $post_id, array( 'fields' => 'ids' ) );

	$args = haka_base_query_args(
		array(
			'posts_per_page' => absint( $number ),
			'post__not_in'   => array( $post_id ),
		)
	);

	if ( ! empty( $tags ) ) {
		$args['tag__in'] = $tags;
	} elseif ( ! empty( $categories ) ) {
		$args['category__in'] = $categories;
	}

	$query = new WP_Query( $args );

	if ( ! $query->have_posts() && ! empty( $categories ) ) {
		unset( $args['tag__in'] );
		$args['category__in'] = $categories;
		$query                = new WP_Query( $args );
	}

	return $query;
}

/**
 * Tag untuk section trending.
 *
 * @return WP_Term[]
 */
function haka_get_trending_tags() {
	$slugs = haka_get_option( 'trending_tags', '' );
	$count = (int) haka_get_option( 'trending_count', 8 );

	if ( $slugs ) {
		$terms = get_terms(
			array(
				'taxonomy'   => 'post_tag',
				'slug'       => array_filter( array_map( 'sanitize_title', explode( ',', $slugs ) ) ),
				'hide_empty' => false,
			)
		);

		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			return $terms;
		}
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'post_tag',
			'orderby'    => 'count',
			'order'      => 'DESC',
			'hide_empty' => true,
			'number'     => $count,
		)
	);

	return is_wp_error( $terms ) ? array() : $terms;
}

/**
 * Tag populer untuk widget/section tag cloud.
 *
 * @param int $number Jumlah tag.
 * @return WP_Term[]
 */
function haka_get_popular_tags( $number = 20 ) {
	$terms = get_terms(
		array(
			'taxonomy'   => 'post_tag',
			'orderby'    => 'count',
			'order'      => 'DESC',
			'hide_empty' => true,
			'number'     => absint( $number ),
		)
	);

	return is_wp_error( $terms ) ? array() : $terms;
}

/**
 * Jumlah artikel terbit di situs.
 *
 * @return int
 */
function haka_total_posts() {
	static $total = null;

	if ( null === $total ) {
		$counts = wp_count_posts( 'post' );
		$total  = isset( $counts->publish ) ? (int) $counts->publish : 0;
	}

	return $total;
}

/**
 * Apakah situs punya cukup artikel untuk sebuah section.
 *
 * Section pelengkap sengaja disembunyikan pada situs yang masih sedikit
 * isinya, supaya artikel yang sama tidak terulang di beberapa tempat.
 *
 * @param int $minimum Jumlah artikel minimum.
 * @return bool
 */
function haka_has_enough_posts( $minimum = 3 ) {
	return haka_total_posts() >= (int) $minimum;
}

/**
 * Post untuk Editor's Choice.
 *
 * @return WP_Query
 */
function haka_query_editors() {
	$count  = (int) haka_get_option( 'editors_count', 4 );
	$source = haka_get_option( 'editors_source', 'sticky' );

	$args = haka_base_query_args(
		array(
			'posts_per_page' => $count,
			'post__not_in'   => haka_used_ids(),
		)
	);

	if ( 'manual' === $source ) {
		$ids = array_filter( array_map( 'absint', explode( ',', (string) haka_get_option( 'editors_ids', '' ) ) ) );

		if ( ! empty( $ids ) ) {
			$args['post__in'] = $ids;
			$args['orderby']  = 'post__in';
			unset( $args['post__not_in'] ); // Pilihan manual selalu dihormati.
		}
	} elseif ( 'category' === $source ) {
		$category = (int) haka_get_option( 'editors_category', 0 );

		if ( $category ) {
			$args['cat'] = $category;
		}
	} else {
		$sticky = get_option( 'sticky_posts' );

		if ( ! empty( $sticky ) ) {
			$args['post__in'] = array_map( 'absint', (array) $sticky );
		}
	}

	$query = new WP_Query( $args );

	// Fallback supaya section tidak pernah kosong saat sumber belum diisi.
	if ( ! $query->have_posts() ) {
		$fallback = haka_base_query_args(
			array(
				'posts_per_page' => $count,
				'post__not_in'   => haka_used_ids(),
			)
		);
		$query    = new WP_Query( $fallback );
	}

	return $query;
}

/**
 * Post rekomendasi: diambil di luar yang sudah tampil.
 *
 * @param int $number Jumlah post.
 * @return WP_Query
 */
function haka_query_recommended( $number = 4 ) {
	return new WP_Query(
		haka_base_query_args(
			array(
				'posts_per_page' => absint( $number ),
				'post__not_in'   => haka_used_ids(),
				'orderby'        => 'rand',
			)
		)
	);
}

/**
 * Post untuk Video Story (kartu vertikal).
 *
 * @param int $number Jumlah post.
 * @return WP_Query
 */
function haka_query_story( $number = 8 ) {
	$category = (int) haka_get_option( 'story_category', 0 );

	$args = haka_base_query_args( array( 'posts_per_page' => absint( $number ) ) );

	if ( $category ) {
		$args['cat'] = $category;
	} else {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'post_format',
				'field'    => 'slug',
				'terms'    => array( 'post-format-video' ),
			),
		);
	}

	$query = new WP_Query( $args );

	if ( ! $query->have_posts() && ! $category ) {
		$query = new WP_Query(
			haka_base_query_args(
				array(
					'posts_per_page' => absint( $number ),
					'meta_query'     => array(
						array(
							'key'     => '_haka_has_video',
							'compare' => 'EXISTS',
						),
					),
				)
			)
		);
	}

	return $query;
}

/**
 * Post "Recommended" di bawah artikel: sengaja dari kategori lain.
 *
 * @param int $post_id ID artikel aktif.
 * @param int $number  Jumlah post.
 * @return WP_Query
 */
function haka_query_discover( $post_id, $number = 4 ) {
	$categories = wp_get_post_categories( absint( $post_id ) );

	$args = haka_base_query_args(
		array(
			'posts_per_page' => absint( $number ),
			'post__not_in'   => array( absint( $post_id ) ),
			'orderby'        => 'rand',
		)
	);

	if ( ! empty( $categories ) ) {
		$args['category__not_in'] = $categories;
	}

	$query = new WP_Query( $args );

	if ( ! $query->have_posts() ) {
		unset( $args['category__not_in'] );
		$query = new WP_Query( $args );
	}

	return $query;
}

/**
 * Kategori untuk tab Dynamic Recent Posts.
 *
 * @return WP_Term[]
 */
function haka_get_dynamic_categories() {
	$configured = haka_get_option( 'dynamic_cats', '' );
	$terms      = array();

	if ( $configured ) {
		foreach ( array_filter( array_map( 'absint', explode( ',', $configured ) ) ) as $id ) {
			$term = get_term( $id, 'category' );

			if ( $term && ! is_wp_error( $term ) ) {
				$terms[] = $term;
			}
		}
	}

	if ( empty( $terms ) ) {
		$auto = get_terms(
			array(
				'taxonomy'   => 'category',
				'hide_empty' => true,
				'orderby'    => 'count',
				'order'      => 'DESC',
				'number'     => 4,
				'exclude'    => array( (int) get_option( 'default_category' ) ),
			)
		);

		if ( ! is_wp_error( $auto ) ) {
			$terms = $auto;
		}
	}

	return array_slice( $terms, 0, 5 );
}

/**
 * Uraikan daftar "Nama|Nilai|Perubahan" atau "Nama|URL" menjadi array.
 *
 * @param string $raw   Teks banyak baris.
 * @param int    $parts Jumlah kolom yang diharapkan.
 * @return array
 */
function haka_parse_lines( $raw, $parts = 2 ) {
	$rows  = array();
	$lines = preg_split( '/\r\n|\r|\n/', (string) $raw );

	foreach ( $lines as $line ) {
		$line = trim( $line );

		if ( '' === $line ) {
			continue;
		}

		$cols = array_map( 'trim', explode( '|', $line ) );

		if ( count( $cols ) < $parts ) {
			continue;
		}

		$rows[] = $cols;
	}

	return $rows;
}

/**
 * Baca penyaring indeks berita dari URL.
 *
 * Seluruh nilai disanitasi dan divalidasi di sini, sehingga template tidak
 * pernah menyentuh $_GET secara langsung.
 *
 * @return array
 */
function haka_index_filters() {
	$category = isset( $_GET['idx_cat'] ) ? absint( wp_unslash( $_GET['idx_cat'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- penyaring baca-saja.
	$day      = isset( $_GET['idx_d'] ) ? absint( wp_unslash( $_GET['idx_d'] ) ) : 0;     // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$month    = isset( $_GET['idx_m'] ) ? absint( wp_unslash( $_GET['idx_m'] ) ) : 0;     // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$year     = isset( $_GET['idx_y'] ) ? absint( wp_unslash( $_GET['idx_y'] ) ) : 0;     // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( $category && ! term_exists( $category, 'category' ) ) {
		$category = 0;
	}

	return array(
		'category' => $category,
		'day'      => ( $day >= 1 && $day <= 31 ) ? $day : 0,
		'month'    => ( $month >= 1 && $month <= 12 ) ? $month : 0,
		'year'     => ( $year >= 1970 && $year <= (int) wp_date( 'Y' ) + 1 ) ? $year : 0,
	);
}

/**
 * Query untuk halaman indeks berita.
 *
 * @param int $paged Halaman aktif.
 * @return WP_Query
 */
function haka_query_index( $paged = 1 ) {
	$filters = haka_index_filters();

	$args = haka_base_query_args(
		array(
			'posts_per_page' => max( 10, min( 100, (int) haka_get_option( 'index_per_page', 30 ) ) ),
			'paged'          => max( 1, absint( $paged ) ),
			'no_found_rows'  => false,
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);

	if ( $filters['category'] ) {
		$args['cat'] = $filters['category'];
	}

	$date = array_filter(
		array(
			'year'     => $filters['year'],
			'monthnum' => $filters['month'],
			'day'      => $filters['day'],
		)
	);

	if ( ! empty( $date ) ) {
		$args['date_query'] = array( $date );
	}

	return new WP_Query( $args );
}

/**
 * Tahun yang punya artikel, untuk pilihan penyaring.
 *
 * @return int[]
 */
function haka_index_years() {
	global $wpdb;

	$cache = get_transient( 'haka_index_years' );

	if ( is_array( $cache ) ) {
		return $cache;
	}

	$years = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		"SELECT DISTINCT YEAR(post_date) FROM {$wpdb->posts}
		 WHERE post_type = 'post' AND post_status = 'publish'
		 ORDER BY post_date DESC"
	);

	$years = array_map( 'absint', (array) $years );
	set_transient( 'haka_index_years', $years, DAY_IN_SECONDS );

	return $years;
}

/**
 * Bersihkan cache "Terpopuler" saat post berubah.
 *
 * @param int $post_id ID post.
 */
function haka_flush_popular_cache( $post_id ) {
	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}

	global $wpdb;

	$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_haka_popular_%' OR option_name LIKE '_transient_timeout_haka_popular_%'"
	);
}
add_action( 'save_post_post', 'haka_flush_popular_cache' );
add_action( 'deleted_post', 'haka_flush_popular_cache' );
