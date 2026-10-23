<?php
/**
 * Editor's Choice: pilihan redaksi dalam kotak berlatar.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

// Situs yang masih sedikit isinya: section pelengkap dilewati supaya
// artikel yang sama tidak terulang di beberapa tempat.
if ( ! haka_has_enough_posts( 6 ) ) {
	return;
}

$haka_editors = haka_query_editors();

if ( ! $haka_editors->have_posts() ) {
	wp_reset_postdata();
	return;
}

haka_used_ids( wp_list_pluck( $haka_editors->posts, 'ID' ) );
?>
<section class="hk-section hk-catbox hk-editors">
	<h2 class="hk-catbox__title">
		<?php echo esc_html( haka_get_option( 'editors_title', __( 'Pilihan Redaksi', 'haka' ) ) ); ?>
	</h2>

	<div class="hk-grid hk-grid--4">
		<?php
		while ( $haka_editors->have_posts() ) :
			$haka_editors->the_post();
			get_template_part( 'template-parts/content', 'card', array( 'compact' => true ) );
		endwhile;
		?>
	</div>
</section>
<?php
wp_reset_postdata();
