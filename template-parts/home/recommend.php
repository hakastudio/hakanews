<?php
/**
 * Rekomendasi: artikel yang belum tampil di section lain.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

if ( ! haka_has_enough_posts( 8 ) ) {
	return;
}

$haka_rec = haka_query_recommended( (int) haka_get_option( 'recommend_count', 4 ) );

if ( ! $haka_rec->have_posts() ) {
	wp_reset_postdata();
	return;
}

haka_used_ids( wp_list_pluck( $haka_rec->posts, 'ID' ) );
?>
<section class="hk-section hk-recommend">
	<?php haka_section_title( haka_get_option( 'recommend_title', __( 'Rekomendasi', 'haka' ) ) ); ?>

	<div class="hk-grid hk-grid--4">
		<?php
		while ( $haka_rec->have_posts() ) :
			$haka_rec->the_post();
			get_template_part( 'template-parts/content', 'card', array( 'compact' => true ) );
		endwhile;
		?>
	</div>
</section>
<?php
wp_reset_postdata();
