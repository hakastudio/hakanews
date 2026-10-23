<?php
/**
 * Rekomendasi: sengaja dari kategori lain agar pembaca menemukan topik baru.
 *
 * Bawaannya grid kartu — bentuk yang berbeda dari Artikel Terkait supaya
 * kedua blok tidak terbaca sebagai pengulangan.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

$haka_discover = haka_query_discover( get_the_ID(), (int) haka_get_option( 'recommended_count', 4 ) );

if ( ! $haka_discover->have_posts() ) {
	wp_reset_postdata();
	return;
}

$haka_layout = haka_get_option( 'recommended_layout', 'grid' );
$haka_grid   = ( 'grid' === $haka_layout );
?>
<section class="hk-section hk-discover">
	<?php haka_section_title( haka_get_option( 'recommended_title', __( 'Mungkin Anda Suka', 'haka' ) ), '', 'h2' ); ?>

	<div class="<?php echo esc_attr( $haka_grid ? 'hk-grid hk-grid--4' : 'hk-stack hk-stack--related' ); ?>">
		<?php
		while ( $haka_discover->have_posts() ) :
			$haka_discover->the_post();

			if ( $haka_grid ) {
				get_template_part( 'template-parts/content', 'card', array( 'compact' => true ) );
			} else {
				get_template_part(
					'template-parts/content',
					'list',
					array(
						'size'  => 'sm',
						'badge' => true,
					)
				);
			}
		endwhile;
		?>
	</div>
</section>
<?php
wp_reset_postdata();
