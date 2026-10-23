<?php
/**
 * Section Video Berita.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

if ( ! haka_get_option( 'video_enable', true ) ) {
	return;
}

$haka_video = haka_query_video( (int) haka_get_option( 'video_count', 4 ) );

if ( ! $haka_video->have_posts() ) {
	wp_reset_postdata();
	return;
}

$haka_video_cat = (int) haka_get_option( 'video_category', 0 );
$haka_video_url = $haka_video_cat ? get_category_link( $haka_video_cat ) : '';
?>
<section class="hk-section hk-videos">
	<?php haka_section_title( __( 'Video Berita', 'haka' ), $haka_video_url ); ?>

	<div class="hk-grid hk-grid--4">
		<?php
		while ( $haka_video->have_posts() ) :
			$haka_video->the_post();
			get_template_part( 'template-parts/content', 'video' );
		endwhile;
		?>
	</div>
</section>
<?php
wp_reset_postdata();
