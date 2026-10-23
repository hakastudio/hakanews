<?php
/**
 * Template tag: fungsi output yang dipakai berulang oleh template.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

/**
 * Kumpulan ikon SVG inline (gaya garis, stroke 2, viewBox 24).
 *
 * @return array
 */
function haka_icons() {
	return array(
		'search'    => '<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>',
		'menu'      => '<path d="M4 6h16M4 12h16M4 18h16"/>',
		'close'     => '<path d="M18 6 6 18M6 6l12 12"/>',
		'chevron-right' => '<path d="m9 18 6-6-6-6"/>',
		'chevron-left'  => '<path d="m15 18-6-6 6-6"/>',
		'chevron-down'  => '<path d="m6 9 6 6 6-6"/>',
		'clock'     => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		'user'      => '<path d="M20 21a8 8 0 1 0-16 0"/><circle cx="12" cy="8" r="4"/>',
		'eye'       => '<path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
		'comment'   => '<path d="M21 15a3 3 0 0 1-3 3H8l-5 4V6a3 3 0 0 1 3-3h12a3 3 0 0 1 3 3Z"/>',
		'play'      => '<circle cx="12" cy="12" r="9"/><path d="M10 8.5v7l6-3.5Z"/>',
		'flame'     => '<path d="M12 3s5 4 5 8a5 5 0 0 1-10 0c0-1.6.8-3 1.7-4 .2 1.3 1 2 1.8 2 1 0 1.5-.8 1.5-2 0-1.6-.5-3-0-4Z"/>',
		'folder'    => '<path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z"/>',
		'tag'       => '<path d="M20.6 13.4 12 4.8H4.8V12l8.6 8.6a2 2 0 0 0 2.8 0l4.4-4.4a2 2 0 0 0 0-2.8Z"/><circle cx="8.5" cy="8.5" r="1.2"/>',
		'link'      => '<path d="M10 13a5 5 0 0 0 7 0l2-2a5 5 0 0 0-7-7l-1 1"/><path d="M14 11a5 5 0 0 0-7 0l-2 2a5 5 0 0 0 7 7l1-1"/>',
		'share'     => '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 13.5 6.8 4M15.4 6.5 8.6 10.5"/>',
		'arrow-up'  => '<path d="M12 19V5M5 12l7-7 7 7"/>',
		'arrow-right' => '<path d="M5 12h14M12 5l7 7-7 7"/>',
		'home'      => '<path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/>',
		'rss'       => '<path d="M4 11a9 9 0 0 1 9 9"/><path d="M4 4a16 16 0 0 1 16 16"/><circle cx="5" cy="19" r="1.5"/>',
		'calendar'  => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 11h18"/>',
		'facebook'  => '<path d="M14 8.5V7a1.5 1.5 0 0 1 1.5-1.5H17V2.5h-2.5A4.5 4.5 0 0 0 10 7v1.5H7.5V12H10v9.5h4V12h2.6l.4-3.5Z"/>',
		'instagram' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r="1"/>',
		'x'         => '<path d="M4 4l16 16M20 4 4 20"/>',
		'youtube'   => '<rect x="2.5" y="5.5" width="19" height="13" rx="4"/><path d="M10.5 9.5v5l4.5-2.5Z"/>',
		'tiktok'    => '<path d="M15 3v9.5a4 4 0 1 1-3-3.9"/><path d="M15 3c.6 2.4 2.2 3.8 4.5 4"/>',
		'whatsapp'  => '<path d="M3.5 20.5 5 16.6A8 8 0 1 1 8 19.4Z"/><path d="M9 9.5c0 3 2.5 5.5 5.5 5.5"/>',
		'sun'       => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M19.1 4.9l-1.4 1.4M6.3 17.7l-1.4 1.4"/>',
		'moon'      => '<path d="M20 14.5A8.5 8.5 0 0 1 9.5 4a8.5 8.5 0 1 0 10.5 10.5Z"/>',
		'telegram'  => '<path d="m21 4-9.5 16-2-6.5L3 11Z"/><path d="m21 4-11 9.5"/>',
	);
}

/**
 * Cetak ikon SVG inline.
 *
 * @param string $name  Nama ikon.
 * @param array  $args  Atribut tambahan (size, class, label).
 * @return string
 */
function haka_get_icon( $name, $args = array() ) {
	$icons = haka_icons();

	if ( ! isset( $icons[ $name ] ) ) {
		return '';
	}

	$args = wp_parse_args(
		$args,
		array(
			'size'  => 18,
			'class' => '',
			'label' => '',
		)
	);

	$classes = trim( 'hk-icon hk-icon--' . $name . ' ' . $args['class'] );

	// Ikon berlabel dibaca pembaca layar lewat aria-label; ikon tanpa label
	// murni dekoratif dan disembunyikan dari pohon aksesibilitas.
	$a11y = $args['label']
		? 'role="img" aria-label="' . esc_attr( $args['label'] ) . '"'
		: 'role="presentation" aria-hidden="true"';

	return sprintf(
		'<svg class="%1$s" width="%2$d" height="%2$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" %3$s focusable="false">%4$s</svg>',
		esc_attr( $classes ),
		absint( $args['size'] ),
		$a11y,
		$icons[ $name ]
	);
}

/**
 * Cetak ikon.
 *
 * @param string $name Nama ikon.
 * @param array  $args Argumen.
 */
function haka_icon( $name, $args = array() ) {
	echo haka_get_icon( $name, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG dibangun internal.
}

/**
 * ID DOM unik untuk sebuah entri.
 *
 * Satu artikel bisa tampil di lebih dari satu section homepage (misal
 * "Berita Terbaru" dan blok kategori). Kemunculan pertama memakai
 * post-<ID> sesuai konvensi WordPress, kemunculan berikutnya diberi
 * akhiran agar tidak ada atribut id ganda di halaman.
 *
 * @param int $post_id ID post.
 * @return string
 */
function haka_entry_id( $post_id = 0 ) {
	static $seen = array();

	$post_id = $post_id ? absint( $post_id ) : (int) get_the_ID();

	if ( ! isset( $seen[ $post_id ] ) ) {
		$seen[ $post_id ] = 0;

		return 'post-' . $post_id;
	}

	$seen[ $post_id ]++;

	return 'post-' . $post_id . '-' . $seen[ $post_id ];
}

/**
 * Kategori utama sebuah post.
 *
 * @param int $post_id ID post.
 * @return WP_Term|null
 */
function haka_primary_category( $post_id = 0 ) {
	$post_id    = $post_id ? absint( $post_id ) : get_the_ID();
	$categories = get_the_category( $post_id );

	if ( empty( $categories ) ) {
		return null;
	}

	$default = (int) get_option( 'default_category' );
	$pilihan = $categories[0];

	foreach ( $categories as $category ) {
		if ( $category->term_id !== $default ) {
			$pilihan = $category;
			break;
		}
	}

	/**
	 * Kategori utama sebuah artikel. Plugin SEO yang punya pilihan
	 * "kategori utama" menimpanya lewat filter ini (lihat inc/compat.php).
	 *
	 * @param WP_Term $pilihan Pilihan theme.
	 * @param int     $post_id ID post.
	 */
	return apply_filters( 'haka_primary_category', $pilihan, $post_id );
}

/**
 * Badge kategori.
 *
 * @param int    $post_id ID post.
 * @param string $class   Kelas tambahan.
 */
function haka_category_badge( $post_id = 0, $class = '' ) {
	$category = haka_primary_category( $post_id );

	if ( ! $category ) {
		return;
	}

	printf(
		'<a class="hk-badge %1$s" href="%2$s" rel="category">%3$s</a>',
		esc_attr( $class ),
		esc_url( get_category_link( $category ) ),
		esc_html( $category->name )
	);
}

/**
 * Gambar placeholder SVG deterministik (tanpa request eksternal).
 *
 * @param int $post_id ID post.
 * @return string
 */
function haka_placeholder_svg( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	$title   = get_the_title( $post_id );
	$initial = $title ? mb_strtoupper( mb_substr( wp_strip_all_tags( $title ), 0, 1 ) ) : 'R';
	$hue     = ( $post_id * 47 ) % 360;

	// ID gradien harus unik di seluruh dokumen, termasuk bila satu post
	// dirender lebih dari sekali.
	static $counter = 0;
	$counter++;
	$gradient_id = 'hk-grad-' . $post_id . '-' . $counter;

	$svg = sprintf(
		'<svg class="hk-placeholder" viewBox="0 0 640 400" role="img" aria-label="%1$s" preserveAspectRatio="xMidYMid slice"><defs><linearGradient id="%2$s" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="hsl(%3$d 16%% 74%%)"/><stop offset="1" stop-color="hsl(%4$d 20%% 52%%)"/></linearGradient></defs><rect width="640" height="400" fill="url(#%2$s)"/><text x="50%%" y="50%%" dy=".35em" text-anchor="middle" font-family="system-ui,sans-serif" font-size="140" font-weight="700" fill="rgba(255,255,255,.45)">%5$s</text></svg>',
		esc_attr__( 'Gambar tidak tersedia', 'haka' ),
		esc_attr( $gradient_id ),
		$hue,
		( $hue + 40 ) % 360,
		esc_html( $initial )
	);

	return $svg;
}

/**
 * Thumbnail post dengan fallback placeholder.
 *
 * @param string $size Ukuran gambar.
 * @param array  $args Argumen (class, link, lazy, sizes).
 */
function haka_post_thumbnail( $size = 'haka-card', $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'class' => 'hk-thumb',
			'link'  => true,
			'lazy'  => true,
			'sizes' => '',
		)
	);

	$post_id = get_the_ID();
	$open    = $args['link'] ? sprintf( '<a class="%1$s" href="%2$s" tabindex="-1" aria-hidden="true">', esc_attr( $args['class'] ), esc_url( get_permalink() ) ) : sprintf( '<div class="%s">', esc_attr( $args['class'] ) );
	$close   = $args['link'] ? '</a>' : '</div>';

	echo $open; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- dibangun dengan esc_* di atas.

	if ( has_post_thumbnail( $post_id ) ) {
		$lazy = $args['lazy'] && haka_enabled( 'lazy_load' );

		$attr = array(
			'loading'  => $lazy ? 'lazy' : 'eager',
			'decoding' => 'async',
		);

		if ( ! $lazy ) {
			$attr['fetchpriority'] = 'high';
		}

		if ( $args['sizes'] ) {
			$attr['sizes'] = $args['sizes'];
		}

		the_post_thumbnail( $size, $attr );
	} else {
		echo haka_placeholder_svg( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG internal.
	}

	echo $close; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Estimasi waktu baca dalam menit.
 *
 * @param int $post_id ID post.
 * @return int
 */
function haka_reading_time( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	$content = get_post_field( 'post_content', $post_id );
	$words   = str_word_count( wp_strip_all_tags( strip_shortcodes( $content ) ) );

	return max( 1, (int) ceil( $words / 200 ) );
}

/**
 * Meta post: tanggal, author, waktu baca.
 *
 * @param array $args Bagian yang ditampilkan.
 */
function haka_post_meta( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'date'      => true,
			'author'    => true,
			'reading'   => false,
			'views'     => false,
			'relative'  => true,
			'date_icon' => false,
			'class'     => 'hk-meta',
		)
	);

	echo '<div class="' . esc_attr( $args['class'] ) . '">';

	if ( $args['author'] ) {
		printf(
			'<span class="hk-meta__item hk-meta__author">%1$s<a href="%2$s" rel="author">%3$s</a></span>',
			haka_get_icon( 'user', array( 'size' => 14 ) ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			esc_url( get_author_posts_url( (int) get_post_field( 'post_author', get_the_ID() ) ) ),
			esc_html( get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', get_the_ID() ) ) )
		);
	}

	if ( $args['date'] ) {
		$label = ! empty( $args['relative'] ) ? haka_relative_date() : get_the_date();
		$icon  = ! empty( $args['date_icon'] ) ? haka_get_icon( 'calendar', array( 'size' => 14 ) ) : '';

		printf(
			'<span class="hk-meta__item hk-meta__date">%1$s<time datetime="%2$s">%3$s</time></span>',
			$icon, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			esc_attr( get_the_date( DATE_W3C ) ),
			esc_html( $label )
		);
	}

	if ( $args['reading'] ) {
		printf(
			'<span class="hk-meta__item">%1$s%2$s</span>',
			haka_get_icon( 'clock', array( 'size' => 14 ) ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			esc_html( sprintf( /* translators: %d: jumlah menit */ __( '%d menit baca', 'haka' ), haka_reading_time() ) )
		);
	}

	if ( $args['views'] ) {
		do_action( 'haka_meta_views' );
	}

	echo '</div>';
}

/**
 * Judul section dengan garis aksen.
 *
 * @param string $title Judul.
 * @param string $link  URL "lihat semua".
 * @param string $tag   Tag heading.
 */
function haka_section_title( $title, $link = '', $tag = 'h2' ) {
	$tag = in_array( $tag, array( 'h1', 'h2', 'h3' ), true ) ? $tag : 'h2';

	echo '<div class="hk-section__head">';
	printf( '<%1$s class="hk-section__title">%2$s</%1$s>', esc_attr( $tag ), esc_html( $title ) );

	if ( $link ) {
		printf(
			'<a class="hk-section__more" href="%1$s">%2$s%3$s</a>',
			esc_url( $link ),
			esc_html__( 'Lihat semua', 'haka' ),
			haka_get_icon( 'chevron-right', array( 'size' => 16 ) ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}

	echo '</div>';
}

/**
 * Tombol berbagi artikel (tanpa script pihak ketiga).
 */
function haka_share_buttons() {
	$url   = rawurlencode( get_permalink() );
	$title = rawurlencode( get_the_title() );

	$links = array(
		'facebook' => array(
			'label' => __( 'Bagikan ke Facebook', 'haka' ),
			'url'   => 'https://www.facebook.com/sharer/sharer.php?u=' . $url,
		),
		'x'        => array(
			'label' => __( 'Bagikan ke X', 'haka' ),
			'url'   => 'https://x.com/intent/tweet?text=' . $title . '&url=' . $url,
		),
		'whatsapp' => array(
			'label' => __( 'Bagikan ke WhatsApp', 'haka' ),
			'url'   => 'https://api.whatsapp.com/send?text=' . $title . '%20' . $url,
		),
		'telegram' => array(
			'label' => __( 'Bagikan ke Telegram', 'haka' ),
			'url'   => 'https://t.me/share/url?url=' . $url . '&text=' . $title,
		),
	);

	echo '<div class="hk-share">';
	printf( '<span class="hk-share__label">%s</span>', esc_html__( 'Bagikan', 'haka' ) );
	echo '<div class="hk-share__list">';

	foreach ( $links as $key => $item ) {
		printf(
			'<a class="hk-share__btn hk-share__btn--%1$s" href="%2$s" target="_blank" rel="noopener noreferrer">%3$s<span class="screen-reader-text">%4$s</span></a>',
			esc_attr( $key ),
			esc_url( $item['url'] ),
			haka_get_icon( $key, array( 'size' => 18 ) ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			esc_html( $item['label'] )
		);
	}

	printf(
		'<button type="button" class="hk-share__btn hk-share__btn--copy" data-hk-copy="%1$s">%2$s<span class="screen-reader-text">%3$s</span></button>',
		esc_url( get_permalink() ),
		haka_get_icon( 'link', array( 'size' => 18 ) ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		esc_html__( 'Salin tautan', 'haka' )
	);

	echo '</div></div>';
}

/**
 * Daftar tautan sosial media dari Customizer.
 *
 * @param string $class Kelas pembungkus.
 */
function haka_social_links( $class = 'hk-social' ) {
	$socials = array(
		'facebook'  => __( 'Facebook', 'haka' ),
		'instagram' => __( 'Instagram', 'haka' ),
		'x'         => __( 'X', 'haka' ),
		'youtube'   => __( 'YouTube', 'haka' ),
		'tiktok'    => __( 'TikTok', 'haka' ),
		'whatsapp'  => __( 'WhatsApp', 'haka' ),
	);

	$items = array();

	foreach ( $socials as $key => $label ) {
		$url = haka_get_option( 'social_' . $key, '' );

		if ( $url ) {
			$items[ $key ] = array(
				'label' => $label,
				'url'   => $url,
			);
		}
	}

	if ( empty( $items ) ) {
		return;
	}

	printf( '<ul class="%s">', esc_attr( $class ) );

	foreach ( $items as $key => $item ) {
		printf(
			'<li><a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s<span class="screen-reader-text">%3$s</span></a></li>',
			esc_url( $item['url'] ),
			haka_get_icon( $key, array( 'size' => 18 ) ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			esc_html( $item['label'] )
		);
	}

	echo '</ul>';
}

/**
 * Pagination arsip.
 *
 * @param WP_Query|null $query Query khusus.
 */
function haka_pagination( $query = null ) {
	$args = array(
		'mid_size'  => 1,
		'end_size'  => 1,
		'prev_text' => haka_get_icon( 'chevron-left', array( 'size' => 16 ) ) . '<span>' . esc_html__( 'Sebelumnya', 'haka' ) . '</span>',
		'next_text' => '<span>' . esc_html__( 'Berikutnya', 'haka' ) . '</span>' . haka_get_icon( 'chevron-right', array( 'size' => 16 ) ),
		'type'      => 'list',
	);

	if ( $query instanceof WP_Query ) {
		$args['total']   = (int) $query->max_num_pages;
		$args['current'] = max( 1, (int) $query->get( 'paged' ) );
	}

	$links = paginate_links( $args );

	if ( ! $links ) {
		return;
	}

	printf(
		'<nav class="hk-pagination" aria-label="%1$s">%2$s</nav>',
		esc_attr__( 'Navigasi halaman', 'haka' ),
		wp_kses_post( $links )
	);
}

/**
 * Atribut penanda wadah daftar, hanya bila infinite scroll aktif.
 *
 * Tanpa penjagaan ini, atribut tetap tercetak walau fitur dimatikan.
 */
function haka_infinite_list_attr() {
	if ( haka_enabled( 'infinite_scroll' ) ) {
		echo ' data-hk-infinite-list';
	}
}

/**
 * Penanda untuk infinite scroll.
 *
 * Pagination biasa tetap dicetak sebagai cadangan; JavaScript yang
 * menyembunyikannya dan menggantinya dengan pemuatan bertahap.
 */
function haka_infinite_marker( $query = null ) {
	if ( ! haka_enabled( 'infinite_scroll' ) ) {
		return;
	}

	// Jangan cetak tombol bila memang tidak ada halaman berikutnya. Tanpa
	// penjagaan ini tombolnya sempat tampil dan baru disembunyikan
	// JavaScript — pembaca tanpa JavaScript melihat tombol yang mati.
	$source = ( $query instanceof WP_Query ) ? $query : $GLOBALS['wp_query'];

	if ( (int) $source->max_num_pages < 2 ) {
		return;
	}

	printf(
		'<div class="hk-infinite" data-hk-infinite data-mode="%1$s"><button type="button" class="hk-btn hk-infinite__btn" data-hk-infinite-btn>%2$s</button><span class="hk-infinite__status" role="status" aria-live="polite"></span></div>',
		esc_attr( haka_get_option( 'infinite_mode', 'button' ) ),
		esc_html__( 'Muat lebih banyak', 'haka' )
	);
}

/**
 * Breadcrumb sederhana (tanpa schema agar tidak bentrok dengan plugin SEO).
 * Bila plugin SEO menyalakan breadcrumb-nya sendiri, itu yang dipakai.
 */
function haka_breadcrumbs() {
	if ( is_front_page() || ! haka_enabled( 'breadcrumbs' ) ) {
		return;
	}

	if ( haka_plugin_breadcrumbs() ) {
		return;
	}

	echo '<nav class="hk-breadcrumb" aria-label="' . esc_attr__( 'Breadcrumb', 'haka' ) . '"><ol>';

	printf(
		'<li><a href="%1$s">%2$s</a></li>',
		esc_url( home_url( '/' ) ),
		esc_html__( 'Beranda', 'haka' )
	);

	if ( is_singular( 'post' ) ) {
		$category = haka_primary_category();

		if ( $category ) {
			printf(
				'<li><a href="%1$s">%2$s</a></li>',
				esc_url( get_category_link( $category ) ),
				esc_html( $category->name )
			);
		}

		printf( '<li aria-current="page">%s</li>', esc_html( wp_trim_words( get_the_title(), 8 ) ) );
	} elseif ( is_category() || is_tag() || is_tax() ) {
		printf( '<li aria-current="page">%s</li>', esc_html( single_term_title( '', false ) ) );
	} elseif ( is_search() ) {
		printf( '<li aria-current="page">%s</li>', esc_html( sprintf( /* translators: %s: kata kunci */ __( 'Pencarian: %s', 'haka' ), get_search_query() ) ) );
	} elseif ( is_author() ) {
		printf( '<li aria-current="page">%s</li>', esc_html( get_the_author() ) );
	} elseif ( is_404() ) {
		printf( '<li aria-current="page">%s</li>', esc_html__( 'Halaman tidak ditemukan', 'haka' ) );
	} elseif ( is_page() ) {
		printf( '<li aria-current="page">%s</li>', esc_html( get_the_title() ) );
	} elseif ( is_archive() ) {
		printf( '<li aria-current="page">%s</li>', esc_html( wp_strip_all_tags( get_the_archive_title() ) ) );
	}

	echo '</ol></nav>';
}

/**
 * Logo situs: custom logo bila ada, jika tidak logo teks Haka.
 *
 * @param array $args Argumen. 'tag' memaksa tag heading tertentu — dipakai
 *                    footer agar tidak menghasilkan <h1> kedua di halaman depan.
 */
function haka_site_branding( $args = array() ) {
	if ( has_custom_logo() ) {
		the_custom_logo();
		return;
	}

	$forced = isset( $args['tag'] ) ? $args['tag'] : '';
	$tag    = ( is_front_page() && ! is_paged() ) ? 'h1' : 'p';

	if ( in_array( $forced, array( 'h1', 'h2', 'p', 'span', 'div' ), true ) ) {
		$tag = $forced;
	}

	/*
	 * Nama diambil dari judul situs, bukan ditulis mati. Sebelumnya "R" dan
	 * "News" tertanam di kode, sehingga siapa pun yang memasang theme ini
	 * melihat nama Haka di situsnya sendiri.
	 *
	 * Bagian pertama diberi warna aksen agar tetap terbaca sebagai logo:
	 * kata pertama bila namanya lebih dari satu kata, huruf pertama bila
	 * hanya satu kata.
	 */
	$nama = trim( wp_strip_all_tags( get_bloginfo( 'name', 'display' ) ) );

	if ( '' === $nama ) {
		$nama = __( 'Situs Saya', 'haka' );
	}

	$kata = preg_split( '/\s+/u', $nama, 2 );

	if ( count( $kata ) > 1 ) {
		$mark = $kata[0];
		$sisa = ' ' . $kata[1];
	} else {
		$huruf = function_exists( 'mb_substr' ) ? mb_substr( $nama, 0, 1 ) : substr( $nama, 0, 1 );
		$mark  = $huruf;
		$sisa  = function_exists( 'mb_substr' ) ? mb_substr( $nama, 1 ) : substr( $nama, 1 );
	}

	printf(
		'<%1$s class="site-title"><a href="%2$s" rel="home"><span class="site-title__mark">%3$s</span><span class="site-title__text">%4$s</span></a></%1$s>',
		esc_attr( $tag ),
		esc_url( home_url( '/' ) ),
		esc_html( $mark ),
		esc_html( $sisa )
	);
}
