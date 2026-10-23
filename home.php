<?php
/**
 * Halaman indeks post (blog).
 *
 * Dipakai saat halaman depan berupa halaman statis dan post dipindahkan
 * ke halaman lain. Tetap memakai layout portal agar konsisten.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( is_home() && ! is_front_page() && get_option( 'page_for_posts' ) ) {
	echo '<div class="hk-container"><header class="hk-pagehead">';
	printf( '<h1 class="hk-pagehead__title">%s</h1>', esc_html( get_the_title( (int) get_option( 'page_for_posts' ) ) ) );
	echo '</header></div>';
}

get_template_part( 'template-parts/home/portal' );

get_footer();
