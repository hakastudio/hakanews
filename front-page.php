<?php
/**
 * Halaman depan.
 *
 * Bila pengguna memilih halaman statis sebagai halaman depan, tampilan
 * halaman itu yang dihormati. Untuk memakai layout portal pada halaman
 * statis, pilih template halaman "Haka Homepage".
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

if ( 'page' === get_option( 'show_on_front' ) && get_option( 'page_on_front' ) ) {
	get_template_part( 'page' );
	return;
}

get_header();
get_template_part( 'template-parts/home/portal' );
get_footer();
