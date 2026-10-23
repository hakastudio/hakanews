<?php
/**
 * Sisipan "Baca juga" di tengah badan artikel.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sisipkan blok tautan setelah paragraf ke-N.
 *
 * @param string $content Isi artikel.
 * @return string
 */
function haka_inject_inline_related( $content ) {
	if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	if ( ! haka_enabled( 'inline_related' ) || post_password_required() ) {
		return $content;
	}

	$after = max( 1, (int) haka_get_option( 'inline_after', 3 ) );
	$count = max( 1, (int) haka_get_option( 'inline_count', 2 ) );

	$paragraphs = explode( '</p>', $content );

	// Tidak disisipkan bila artikel terlalu pendek: akan terlihat mengganggu.
	if ( count( $paragraphs ) < $after + 2 ) {
		return $content;
	}

	$related = haka_query_related( get_the_ID(), $count );

	if ( ! $related->have_posts() ) {
		wp_reset_postdata();
		return $content;
	}

	$block  = '<aside class="hk-inline-related" aria-label="' . esc_attr__( 'Artikel terkait', 'haka' ) . '">';
	$block .= '<span class="hk-inline-related__label">' . esc_html__( 'Baca juga', 'haka' ) . '</span><ul>';

	while ( $related->have_posts() ) {
		$related->the_post();
		$block .= '<li><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></li>';
	}

	$block .= '</ul></aside>';
	wp_reset_postdata();

	// Susun ulang persis seperti aslinya, lalu selipkan blok setelah
	// penutup paragraf ke-N. Cara ini tidak menambah atau menghilangkan </p>.
	$total    = count( $paragraphs );
	$output   = '';
	$dihitung = 0;
	$sisip    = false;

	foreach ( $paragraphs as $index => $part ) {
		$output .= $part;

		if ( $index < $total - 1 ) {
			$output .= '</p>';
		}

		if ( $sisip || $index === $total - 1 ) {
			continue;
		}

		// Paragraf di dalam kutipan, daftar, atau figure tidak dihitung: blok
		// tidak boleh menyela elemen yang belum ditutup.
		$di_dalam = false;

		foreach ( array( 'blockquote', 'li', 'figure', 'aside', 'table' ) as $tag ) {
			if ( substr_count( $output, '<' . $tag ) > substr_count( $output, '</' . $tag . '>' ) ) {
				$di_dalam = true;
				break;
			}
		}

		if ( $di_dalam ) {
			continue;
		}

		$dihitung++;

		if ( $dihitung === $after ) {
			$output .= $block;
			$sisip   = true;
		}
	}

	return $output;
}
add_filter( 'the_content', 'haka_inject_inline_related', 20 );
