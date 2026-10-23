<?php
/**
 * Structured data JSON-LD.
 *
 * Sengaja mundur bila plugin SEO besar aktif, supaya tidak ada dua schema
 * yang saling bertabrakan di satu halaman.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

/**
 * Cetak JSON-LD di head.
 */
function haka_print_schema() {
	if ( ! haka_enabled( 'structured_data' ) || haka_seo_plugin_active() ) {
		return;
	}

	$graph = array();

	$publisher = array(
		'@type' => 'Organization',
		'@id'   => home_url( '/#organization' ),
		'name'  => get_bloginfo( 'name' ),
		'url'   => home_url( '/' ),
	);

	$logo_id = (int) get_theme_mod( 'custom_logo' );

	if ( $logo_id ) {
		$logo = wp_get_attachment_image_src( $logo_id, 'full' );

		if ( $logo ) {
			$publisher['logo'] = array(
				'@type'  => 'ImageObject',
				'url'    => $logo[0],
				'width'  => (int) $logo[1],
				'height' => (int) $logo[2],
			);
		}
	}

	$graph[] = $publisher;

	$graph[] = array(
		'@type'           => 'WebSite',
		'@id'             => home_url( '/#website' ),
		'url'             => home_url( '/' ),
		'name'            => get_bloginfo( 'name' ),
		'description'     => get_bloginfo( 'description' ),
		'publisher'       => array( '@id' => home_url( '/#organization' ) ),
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => array(
				'@type'       => 'EntryPoint',
				'urlTemplate' => home_url( '/?s={search_term_string}' ),
			),
			'query-input' => 'required name=search_term_string',
		),
	);

	if ( is_singular( 'post' ) ) {
		$post_id  = get_queried_object_id();
		$category = haka_primary_category( $post_id );

		$article = array(
			'@type'            => 'NewsArticle',
			'@id'              => get_permalink( $post_id ) . '#article',
			'headline'         => wp_strip_all_tags( get_the_title( $post_id ) ),
			'mainEntityOfPage' => get_permalink( $post_id ),
			'datePublished'    => get_the_date( DATE_W3C, $post_id ),
			'dateModified'     => get_the_modified_date( DATE_W3C, $post_id ),
			'author'           => array(
				'@type' => 'Person',
				'name'  => get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $post_id ) ),
				'url'   => get_author_posts_url( (int) get_post_field( 'post_author', $post_id ) ),
			),
			'publisher'        => array( '@id' => home_url( '/#organization' ) ),
			'wordCount'        => str_word_count( wp_strip_all_tags( strip_shortcodes( get_post_field( 'post_content', $post_id ) ) ) ),
		);

		$excerpt = get_the_excerpt( $post_id );

		if ( $excerpt ) {
			$article['description'] = wp_strip_all_tags( $excerpt );
		}

		if ( $category ) {
			$article['articleSection'] = $category->name;
		}

		if ( has_post_thumbnail( $post_id ) ) {
			$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'haka-hero' );

			if ( $image ) {
				$article['image'] = array(
					'@type'  => 'ImageObject',
					'url'    => $image[0],
					'width'  => (int) $image[1],
					'height' => (int) $image[2],
				);
			}
		}

		$graph[] = $article;

		// Breadcrumb: Beranda › Kategori › Judul.
		$items = array(
			array(
				'@type'    => 'ListItem',
				'position' => 1,
				'name'     => __( 'Beranda', 'haka' ),
				'item'     => home_url( '/' ),
			),
		);

		if ( $category ) {
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => 2,
				'name'     => $category->name,
				'item'     => get_category_link( $category ),
			);
		}

		$items[] = array(
			'@type'    => 'ListItem',
			'position' => count( $items ) + 1,
			'name'     => wp_strip_all_tags( get_the_title( $post_id ) ),
		);

		$graph[] = array(
			'@type'           => 'BreadcrumbList',
			'@id'             => get_permalink( $post_id ) . '#breadcrumb',
			'itemListElement' => $items,
		);
	}

	$data = array(
		'@context' => 'https://schema.org',
		'@graph'   => $graph,
	);

	printf(
		'<script type="application/ld+json">%s</script>' . "\n",
		wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
	);
}
add_action( 'wp_head', 'haka_print_schema', 20 );

/**
 * Open Graph dan Twitter Card sederhana — hanya bila tidak ada plugin SEO.
 */
function haka_print_og() {
	if ( ! haka_enabled( 'og_tags' ) || haka_seo_plugin_active() ) {
		return;
	}

	$judul = wp_get_document_title();
	$url   = '';
	$desc  = get_bloginfo( 'description' );
	$tipe  = 'website';
	$img   = null;

	if ( is_singular() ) {
		$post_id = get_queried_object_id();
		$url     = get_permalink( $post_id );
		$excerpt = wp_strip_all_tags( get_the_excerpt( $post_id ) );
		$desc    = $excerpt ? $excerpt : $desc;
		$tipe    = is_singular( 'post' ) ? 'article' : 'website';

		if ( has_post_thumbnail( $post_id ) ) {
			$img = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'haka-hero' );
		}
	} elseif ( is_front_page() ) {
		$url = home_url( '/' );
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$url  = get_term_link( get_queried_object() );
		$url  = is_wp_error( $url ) ? '' : $url;
		$desc = wp_strip_all_tags( term_description() ) ? wp_strip_all_tags( term_description() ) : $desc;
	}

	if ( ! $img ) {
		$logo_id = (int) get_theme_mod( 'custom_logo' );
		$img     = $logo_id ? wp_get_attachment_image_src( $logo_id, 'full' ) : null;
	}

	$tags = array(
		'og:type'        => $tipe,
		'og:site_name'   => get_bloginfo( 'name' ),
		'og:title'       => $judul,
		'og:description' => wp_trim_words( $desc, 40, '…' ),
		'og:locale'      => get_locale(),
	);

	if ( $url ) {
		$tags['og:url'] = $url;
	}

	if ( $img ) {
		$tags['og:image']        = $img[0];
		$tags['og:image:width']  = (string) $img[1];
		$tags['og:image:height'] = (string) $img[2];
	}

	if ( 'article' === $tipe ) {
		$post_id  = get_queried_object_id();
		$kategori = haka_primary_category( $post_id );

		$tags['article:published_time'] = get_the_date( DATE_W3C, $post_id );
		$tags['article:modified_time']  = get_the_modified_date( DATE_W3C, $post_id );

		if ( $kategori ) {
			$tags['article:section'] = $kategori->name;
		}
	}

	/**
	 * Ubah tag Open Graph bawaan theme.
	 *
	 * @param array $tags Pasangan property => content.
	 */
	$tags = apply_filters( 'haka_og_tags', $tags );

	echo "\n";

	foreach ( $tags as $property => $content ) {
		if ( '' === $content || null === $content ) {
			continue;
		}

		printf( '<meta property="%1$s" content="%2$s">' . "\n", esc_attr( $property ), esc_attr( $content ) );
	}

	printf( '<meta name="twitter:card" content="%s">' . "\n", $img ? 'summary_large_image' : 'summary' );
}
add_action( 'wp_head', 'haka_print_og', 6 );

/**
 * Daftarkan pengaturan modul ini ke registry fitur.
 *
 * @param array $registry Registry fitur.
 * @return array
 */
function haka_schema_registry( $registry ) {
	$registry = haka_registry_add(
		$registry,
		'performance',
		array(
			'structured_data' => array(
				'label'   => __( 'Structured Data (JSON-LD)', 'haka' ),
				'desc'    => __( 'Otomatis dimatikan bila plugin SEO (Yoast, Rank Math, AIOSEO, SEOPress, dll.) aktif, agar tidak ada schema ganda.', 'haka' ),
				'type'    => 'checkbox',
				'default' => true,
			),
			'og_tags'         => array(
				'label'   => __( 'Open Graph & Twitter Card', 'haka' ),
				'desc'    => __( 'Judul, ringkasan, dan gambar saat tautan dibagikan ke media sosial. Otomatis dimatikan bila plugin SEO aktif.', 'haka' ),
				'type'    => 'checkbox',
				'default' => true,
			),
		)
	);
	return $registry;
}
add_filter( 'haka_feature_registry', 'haka_schema_registry' );
