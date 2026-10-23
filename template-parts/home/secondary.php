<?php
/**
 * Berita pendukung di bawah hero: grid tiga kolom.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

if ( ! haka_has_enough_posts( 3 ) ) {
	return;
}

$haka_secondary = new WP_Query(
	haka_base_query_args(
		array(
			'posts_per_page' => (int) haka_get_option( 'secondary_count', 6 ),
			'post__not_in'   => haka_used_ids(),
		)
	)
);

if ( ! $haka_secondary->have_posts() ) {
	wp_reset_postdata();
	return;
}

haka_used_ids( wp_list_pluck( $haka_secondary->posts, 'ID' ) );
?>
<section class="hk-section hk-secondary">
	<div class="hk-grid hk-grid--3">
		<?php
		while ( $haka_secondary->have_posts() ) :
			$haka_secondary->the_post();
			get_template_part( 'template-parts/content', 'card' );
		endwhile;
		?>
	</div>
</section>
<?php
wp_reset_postdata();
