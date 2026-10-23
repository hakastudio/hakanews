<?php
/**
 * Blok berita per kategori dalam kotak berlatar.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

$haka_categories = haka_get_block_categories( (int) haka_get_option( 'multicat_count', 2 ) );

if ( empty( $haka_categories ) ) {
	return;
}

foreach ( $haka_categories as $haka_cat ) :
	$haka_cat_query = haka_query_category( $haka_cat->term_id, 4, true );

	// Blok yang isinya tinggal sedikit dilewati: grid empat kolom berisi dua
	// kartu terbaca seperti kesalahan, bukan pilihan.
	$haka_min = max( 2, (int) haka_get_option( 'multicat_min', 3 ) );

	if ( $haka_cat_query->post_count < $haka_min ) {
		wp_reset_postdata();
		continue;
	}
	haka_used_ids( wp_list_pluck( $haka_cat_query->posts, 'ID' ) );
	?>
	<section class="hk-section hk-catbox">
		<h2 class="hk-catbox__title"><?php echo esc_html( $haka_cat->name ); ?></h2>

		<div class="hk-grid hk-grid--4">
			<?php
			while ( $haka_cat_query->have_posts() ) :
				$haka_cat_query->the_post();
				get_template_part( 'template-parts/content', 'card', array( 'compact' => true ) );
			endwhile;
			?>
		</div>

		<p class="hk-catbox__more">
			<a href="<?php echo esc_url( get_category_link( $haka_cat ) ); ?>">
				<?php esc_html_e( 'Lihat Selengkapnya', 'haka' ); ?>
				<?php haka_icon( 'chevron-right', array( 'size' => 15 ) ); ?>
			</a>
		</p>
	</section>
	<?php
	wp_reset_postdata();
endforeach;
