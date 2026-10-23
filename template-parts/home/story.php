<?php
/**
 * Video Story: kartu vertikal 9:16 yang digeser ke samping.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

$haka_story = haka_query_story( (int) haka_get_option( 'story_count', 8 ) );

if ( ! $haka_story->have_posts() ) {
	wp_reset_postdata();
	return;
}
?>
<section class="hk-section hk-story">
	<?php haka_section_title( haka_get_option( 'story_title', __( 'Video Story', 'haka' ) ) ); ?>

	<ul class="hk-story__track">
		<?php
		while ( $haka_story->have_posts() ) :
			$haka_story->the_post();
			?>
			<li class="hk-story__item">
				<article id="<?php echo esc_attr( haka_entry_id() ); ?>" <?php post_class( 'hk-story__card' ); ?>>
					<?php
					haka_post_thumbnail(
						'haka-story',
						array(
							'class' => 'hk-story__thumb',
							'sizes' => '180px',
						)
					);
					?>
					<span class="hk-story__play" aria-hidden="true">
						<?php haka_icon( 'play', array( 'size' => 28 ) ); ?>
					</span>
					<div class="hk-story__body">
						<h3 class="hk-story__title">
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</h3>
					</div>
				</article>
			</li>
			<?php
		endwhile;
		?>
	</ul>
</section>
<?php
wp_reset_postdata();
