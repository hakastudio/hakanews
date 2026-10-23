<?php
/**
 * Berita Terbaru: daftar horizontal dengan pagination.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

$haka_paged  = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
$haka_layout = haka_get_option( 'latest_layout', 'compact' );
$haka_latest = haka_query_latest( $haka_paged );
?>
<section class="hk-section hk-latest">
	<?php haka_section_title( __( 'Berita Terbaru', 'haka' ) ); ?>

	<?php if ( $haka_latest->have_posts() ) : ?>

		<div class="hk-stack hk-stack--<?php echo esc_attr( $haka_layout ); ?>"<?php haka_infinite_list_attr(); ?>>
			<?php
			while ( $haka_latest->have_posts() ) :
				$haka_latest->the_post();
				get_template_part(
					'template-parts/content',
					'list',
					array(
						'size'  => ( 'large' === $haka_layout ) ? 'lg' : 'sm',
						'badge' => true,
					)
				);
			endwhile;
			?>
		</div>

		<?php haka_pagination( $haka_latest ); ?>
		<?php haka_infinite_marker( $haka_latest ); ?>

	<?php else : ?>
		<?php get_template_part( 'template-parts/content', 'none' ); ?>
	<?php endif; ?>
</section>
<?php
wp_reset_postdata();
