<?php
/**
 * Artikel terkait.
 *
 * Bawaannya daftar ringkas: relevansinya tinggi dan pembaca sudah tertarik
 * pada topiknya, jadi judul lebih berguna daripada gambar besar.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

$haka_related = haka_query_related( get_the_ID(), (int) haka_get_option( 'related_count', 4 ) );

if ( ! $haka_related->have_posts() ) {
	wp_reset_postdata();
	return;
}

$haka_layout  = haka_get_option( 'related_layout', 'list' );
$haka_grid    = ( 'grid' === $haka_layout );
$haka_own_cat = haka_primary_category( get_the_ID() );
$haka_own_id  = $haka_own_cat ? (int) $haka_own_cat->term_id : 0;
?>
<section class="hk-section hk-related">
	<?php haka_section_title( haka_get_option( 'related_title', __( 'Artikel Terkait', 'haka' ) ), '', 'h2' ); ?>

	<div class="<?php echo esc_attr( $haka_grid ? 'hk-grid hk-grid--4' : 'hk-stack hk-stack--related' ); ?>">
		<?php
		while ( $haka_related->have_posts() ) :
			$haka_related->the_post();

			if ( $haka_grid ) {
				get_template_part( 'template-parts/content', 'card', array( 'compact' => true ) );
			} else {
				get_template_part(
					'template-parts/content',
					'list',
					array(
						'size'        => 'sm',
						'badge'       => 'auto',
						'current_cat' => $haka_own_id,
					)
				);
			}
		endwhile;
		?>
	</div>
</section>
<?php
wp_reset_postdata();
