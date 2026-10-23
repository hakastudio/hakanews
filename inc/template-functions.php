<?php
/**
 * Filter dan penyesuaian perilaku WordPress.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

/**
 * Artikel tanpa judul tetap harus bisa diklik: beri judul pengganti di
 * front end supaya tautan kartu, daftar, dan navigasi artikel tidak kosong.
 *
 * @param string $title Judul.
 * @param int    $id    ID post.
 * @return string
 */
function haka_empty_title_fallback( $title, $id = 0 ) {
	if ( '' !== trim( (string) $title ) || is_admin() || ! $id ) {
		return $title;
	}

	$post = get_post( $id );

	if ( ! $post instanceof WP_Post || ! is_post_type_viewable( $post->post_type ) ) {
		return $title;
	}

	return __( '(Tanpa judul)', 'haka' );
}
add_filter( 'the_title', 'haka_empty_title_fallback', 10, 2 );

/**
 * Tambahkan class kontekstual ke body.
 *
 * @param array $classes Class yang ada.
 * @return array
 */
function haka_body_classes( $classes ) {
	if ( ! is_active_sidebar( 'sidebar-primary' ) ) {
		$classes[] = 'no-sidebar';
	}

	if ( haka_get_option( 'sticky_header', true ) ) {
		$classes[] = 'has-sticky-header';
	}

	if ( is_singular() && has_post_thumbnail() ) {
		$classes[] = 'has-featured-image';
	}

	if ( is_front_page() || is_home() ) {
		foreach ( array( 'secondary', 'editors', 'multicat', 'story', 'tabs', 'recommend', 'videos', 'network' ) as $haka_sec ) {
			if ( haka_enabled( 'hm_' . $haka_sec ) ) {
				$classes[] = 'hk-hm-' . $haka_sec;
			}
		}
	}

	if ( haka_enabled( 'hover_zoom' ) ) {
		$classes[] = 'hk-hover-zoom';
	}

	if ( haka_enabled( 'dark_mode' ) ) {
		$classes[] = 'hk-has-darkmode';
	}

	$classes[] = 'hk-theme';

	return $classes;
}
add_filter( 'body_class', 'haka_body_classes' );

/**
 * Panjang excerpt.
 *
 * @return int
 */
function haka_excerpt_length() {
	return is_singular() ? 40 : 22;
}
add_filter( 'excerpt_length', 'haka_excerpt_length', 999 );

/**
 * Akhiran excerpt.
 *
 * @return string
 */
function haka_excerpt_more() {
	return '&hellip;';
}
add_filter( 'excerpt_more', 'haka_excerpt_more' );

/**
 * Judul arsip tanpa prefix "Kategori:" bawaan.
 *
 * @param string $title Judul.
 * @return string
 */
function haka_archive_title( $title ) {
	if ( is_category() || is_tag() || is_tax() ) {
		$title = single_term_title( '', false );
	} elseif ( is_author() ) {
		$title = get_the_author();
	} elseif ( is_post_type_archive() ) {
		$title = post_type_archive_title( '', false );
	}

	return $title;
}
add_filter( 'get_the_archive_title', 'haka_archive_title' );

/**
 * Tandai post yang memuat embed video agar bisa dipakai section video.
 *
 * @param int     $post_id ID post.
 * @param WP_Post $post    Objek post.
 */
function haka_flag_video_post( $post_id, $post ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	$content  = (string) $post->post_content;
	$patterns = array( 'youtube.com', 'youtu.be', 'vimeo.com', 'wp-block-video', '<video', 'dailymotion.com' );
	$found    = false;

	foreach ( $patterns as $needle ) {
		if ( false !== stripos( $content, $needle ) ) {
			$found = true;
			break;
		}
	}

	if ( $found || has_post_format( 'video', $post_id ) ) {
		update_post_meta( $post_id, '_haka_has_video', 1 );
	} else {
		delete_post_meta( $post_id, '_haka_has_video' );
	}
}
add_action( 'save_post_post', 'haka_flag_video_post', 10, 2 );

/**
 * Nama bulan dalam bahasa Indonesia.
 *
 * @param int  $month Nomor bulan 1-12.
 * @param bool $short Bentuk singkat.
 * @return string
 */
function haka_month_id( $month, $short = false ) {
	$month = (int) $month;
	if ( $month < 1 || $month > 12 ) {
		return '';
	}

	global $wp_locale;
	if ( ! empty( $wp_locale ) ) {
		$month_name = $wp_locale->get_month( $month );
		return $short ? $wp_locale->get_month_abbrev( $month_name ) : $month_name;
	}

	$names = array(
		1  => array( 'Januari', 'Jan' ),
		2  => array( 'Februari', 'Feb' ),
		3  => array( 'Maret', 'Mar' ),
		4  => array( 'April', 'Apr' ),
		5  => array( 'Mei', 'Mei' ),
		6  => array( 'Juni', 'Jun' ),
		7  => array( 'Juli', 'Jul' ),
		8  => array( 'Agustus', 'Agu' ),
		9  => array( 'September', 'Sep' ),
		10 => array( 'Oktober', 'Okt' ),
		11 => array( 'November', 'Nov' ),
		12 => array( 'Desember', 'Des' ),
	);

	if ( ! isset( $names[ $month ] ) ) {
		return '';
	}

	return $short ? $names[ $month ][1] : $names[ $month ][0];
}

/**
 * Tanggal absolut sesuai format pilihan Customizer atau bawaan WordPress.
 *
 * @param int $post_id ID post.
 * @return string
 */
function haka_absolute_date( $post_id = 0 ) {
	$post = get_post( $post_id ? absint( $post_id ) : get_the_ID() );
	if ( ! $post ) {
		return '';
	}

	$format = haka_get_option( 'date_format', 'wp' );

	switch ( $format ) {
		case 'id':
			return get_the_date( 'j F Y', $post );

		case 'id_s':
			return get_the_date( 'j M Y', $post );

		case 'us':
			return get_the_date( 'F j, Y', $post );

		case 'iso':
			return get_the_date( 'd/m/Y', $post );

		case 'wp':
		default:
			return get_the_date( '', $post );
	}
}

/**
 * Tanggal hari ini dalam format pilihan atau bawaan WordPress.
 *
 * @return string
 */
function haka_absolute_date_now() {
	$format = haka_get_option( 'date_format', 'wp' );

	switch ( $format ) {
		case 'id':
			return wp_date( 'j F Y' );

		case 'id_s':
			return wp_date( 'j M Y' );

		case 'us':
			return wp_date( 'F j, Y' );

		case 'iso':
			return wp_date( 'd/m/Y' );

		case 'wp':
		default:
			return wp_date( (string) get_option( 'date_format' ) );
	}
}

/**
 * Tanggal relatif/waktu meta post.
 *
 * @param int $post_id ID post.
 * @return string
 */
function haka_relative_date( $post_id = 0 ) {
	$post = get_post( $post_id ? absint( $post_id ) : get_the_ID() );
	if ( ! $post ) {
		return '';
	}

	if ( ! haka_enabled( 'time_ago' ) ) {
		return haka_absolute_date( $post->ID );
	}

	$timestamp = function_exists( 'get_post_timestamp' ) ? get_post_timestamp( $post ) : strtotime( get_gmt_from_date( $post->post_date ) );
	$now       = time();
	$diff      = $now - $timestamp;
	$max       = (int) haka_get_option( 'time_ago_max', 30 ) * DAY_IN_SECONDS;

	if ( $diff < 0 || $diff > $max ) {
		return haka_absolute_date( $post->ID );
	}

	return sprintf(
		/* translators: %s: selisih waktu, misal "3 jam" */
		__( '%s yang lalu', 'haka' ),
		human_time_diff( $timestamp, $now )
	);
}

/**
 * Fallback menu utama saat belum ada menu yang diatur.
 */
function haka_menu_fallback() {
	echo '<ul id="primary-menu" class="hk-menu">';

	printf(
		'<li class="menu-item%1$s"><a href="%2$s">%3$s</a></li>',
		is_front_page() ? ' current-menu-item' : '',
		esc_url( home_url( '/' ) ),
		esc_html__( 'Home', 'haka' )
	);

	$categories = get_categories(
		array(
			'orderby'    => 'count',
			'order'      => 'DESC',
			'number'     => 8,
			'hide_empty' => true,
		)
	);

	foreach ( $categories as $category ) {
		printf(
			'<li class="menu-item"><a href="%1$s">%2$s</a></li>',
			esc_url( get_category_link( $category ) ),
			esc_html( $category->name )
		);
	}

	echo '</ul>';
}

/**
 * Tambahkan panah pada item menu yang punya submenu.
 *
 * @param string   $output Markup item.
 * @param WP_Post  $item   Item menu.
 * @param int      $depth  Kedalaman.
 * @param stdClass $args   Argumen menu.
 * @return string
 */
function haka_menu_caret( $output, $item, $depth, $args ) {
	if ( ! haka_enabled( 'submenu_caret' ) ) {
		return $output;
	}

	$locations = array( 'primary', 'secondary' );

	if ( empty( $args->theme_location ) || ! in_array( $args->theme_location, $locations, true ) ) {
		return $output;
	}

	if ( ! in_array( 'menu-item-has-children', (array) $item->classes, true ) ) {
		return $output;
	}

	$caret = haka_get_icon( 'chevron-down', array( 'size' => 13, 'class' => 'hk-menu__caret' ) );

	return preg_replace( '/<\/a>(?!.*<\/a>)/s', $caret . '</a>', $output, 1 );
}
add_filter( 'walker_nav_menu_start_el', 'haka_menu_caret', 10, 4 );

/**
 * Class penanda gaya menu pada body.
 *
 * @param array $classes Class body.
 * @return array
 */
function haka_menu_body_classes( $classes ) {
	$classes[] = 'hk-menuactive-' . sanitize_html_class( (string) haka_get_option( 'menu_active', 'underline' ) );

	if ( haka_enabled( 'menu_uppercase' ) ) {
		$classes[] = 'hk-menu-caps';
	}

	return $classes;
}
add_filter( 'body_class', 'haka_menu_body_classes' );

/**
 * Tambahkan class pada link menu agar styling konsisten.
 *
 * @param array   $atts Atribut link.
 * @param WP_Post $item Item menu.
 * @return array
 */
function haka_menu_link_atts( $atts, $item ) {
	if ( ! empty( $item->classes ) && in_array( 'menu-item-has-children', (array) $item->classes, true ) ) {
		$atts['aria-haspopup'] = 'true';
	}

	return $atts;
}
add_filter( 'nav_menu_link_attributes', 'haka_menu_link_atts', 10, 2 );

/**
 * Bilah progres baca di atas layar.
 */
function haka_reading_progress_bar() {
	if ( ! is_singular( 'post' ) || ! haka_enabled( 'reading_progress' ) ) {
		return;
	}

	echo '<div class="hk-progress" id="hk-progress" aria-hidden="true"><span></span></div>';
}
add_action( 'wp_body_open', 'haka_reading_progress_bar', 6 );

/**
 * Skip link ke konten utama.
 */
function haka_skip_link() {
	printf(
		'<a class="skip-link screen-reader-text" href="#primary">%s</a>',
		esc_html__( 'Lompat ke konten utama', 'haka' )
	);
}
add_action( 'wp_body_open', 'haka_skip_link', 5 );

/**
 * Pastikan atribut alt gambar tidak kosong pada thumbnail.
 *
 * @param array $attr       Atribut.
 * @param mixed $attachment Objek attachment.
 * @return array
 */
function haka_thumbnail_alt( $attr, $attachment ) {
	if ( empty( $attr['alt'] ) && $attachment instanceof WP_Post ) {
		$parent      = (int) $attachment->post_parent;
		$attr['alt'] = $parent ? get_the_title( $parent ) : $attachment->post_title;
	}

	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'haka_thumbnail_alt', 10, 2 );

/**
 * Judul tab pencarian yang lebih deskriptif.
 *
 * @param array $title Bagian judul.
 * @return array
 */
function haka_document_title( $title ) {
	if ( haka_seo_plugin_active() ) {
		return $title;
	}

	if ( is_search() ) {
		$title['title'] = sprintf(
			/* translators: %s: kata kunci pencarian */
			__( 'Hasil pencarian: %s', 'haka' ),
			get_search_query()
		);
	}

	return $title;
}
add_filter( 'document_title_parts', 'haka_document_title' );

/**
 * Batasi pencarian ke post agar hasil relevan untuk portal berita.
 *
 * @param WP_Query $query Query utama.
 */
function haka_search_filter( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( $query->is_search() && ! $query->get( 'post_type' ) ) {
		$query->set( 'post_type', array( 'post', 'page' ) );
	}
}
add_action( 'pre_get_posts', 'haka_search_filter' );

/**
 * Format daftar komentar.
 *
 * @param WP_Comment $comment Objek komentar.
 * @param array      $args    Argumen.
 * @param int        $depth   Kedalaman.
 */
function haka_comment_item( $comment, $args, $depth ) {
	$tag = ( 'div' === $args['style'] ) ? 'div' : 'li';

	if ( 'comment' !== $comment->comment_type ) {
		?>
		<<?php echo esc_attr( $tag ); ?> id="comment-<?php comment_ID(); ?>" <?php comment_class( 'hk-comment hk-comment--ping', $comment ); ?>>
			<article class="hk-comment__body">
				<footer class="hk-comment__meta">
					<span class="hk-comment__type"><?php 'pingback' === $comment->comment_type ? esc_html_e( 'Pingback', 'haka' ) : esc_html_e( 'Trackback', 'haka' ); ?></span>
					<span class="hk-comment__author"><?php comment_author_link( $comment ); ?></span>
					<?php edit_comment_link( __( 'Sunting', 'haka' ), '<span class="hk-comment__edit">', '</span>', $comment ); ?>
				</footer>
			</article>
		<?php
		return;
	}
	?>
	<<?php echo esc_attr( $tag ); ?> id="comment-<?php comment_ID(); ?>" <?php comment_class( 'hk-comment', $comment ); ?>>
		<article class="hk-comment__body">
			<footer class="hk-comment__meta">
				<div class="hk-comment__avatar"><?php echo get_avatar( $comment, 44 ); ?></div>
				<div>
					<span class="hk-comment__author"><?php comment_author_link( $comment ); ?></span>
					<time class="hk-comment__date" datetime="<?php comment_time( 'c' ); ?>"><?php echo esc_html( get_comment_date( '', $comment ) ); ?></time>
				</div>
			</footer>
			<div class="hk-comment__content"><?php comment_text(); ?></div>
			<div class="hk-comment__actions">
				<?php
				comment_reply_link(
					array_merge(
						$args,
						array(
							'depth'     => $depth,
							'max_depth' => $args['max_depth'],
							'reply_text' => __( 'Balas', 'haka' ),
						)
					),
					$comment
				);
				?>
			</div>
			<?php if ( '0' === $comment->comment_approved ) : ?>
				<p class="hk-comment__pending"><?php esc_html_e( 'Komentar Anda menunggu moderasi.', 'haka' ); ?></p>
			<?php endif; ?>
		</article>
	<?php
}