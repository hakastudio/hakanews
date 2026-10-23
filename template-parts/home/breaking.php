<?php
/**
 * Breaking news ticker.
 *
 * Animasi memakai CSS dan otomatis nonaktif pada prefers-reduced-motion.
 * Ticker bisa dijeda lewat tombol, dan seluruh judul tetap bisa di-tab.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

$haka_breaking = haka_query_breaking();

if ( ! $haka_breaking->have_posts() ) {
	wp_reset_postdata();
	return;
}

$haka_label = haka_get_option( 'breaking_label', __( 'Breaking News', 'haka' ) );
?>
<section class="hk-breaking" aria-label="<?php echo esc_attr( $haka_label ); ?>">
	<div class="hk-container hk-breaking__inner">

		<span class="hk-breaking__label">
			<span class="hk-breaking__dot" aria-hidden="true"></span>
			<?php echo esc_html( $haka_label ); ?>
		</span>

		<div class="hk-breaking__viewport">
			<ul class="hk-breaking__track" id="hk-breaking-track">
				<?php
				while ( $haka_breaking->have_posts() ) :
					$haka_breaking->the_post();
					?>
					<li class="hk-breaking__item">
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</li>
					<?php
				endwhile;
				?>
			</ul>
		</div>

		<button type="button" class="hk-breaking__pause" id="hk-breaking-pause" aria-pressed="false">
			<span class="hk-breaking__pause-icon" aria-hidden="true"></span>
			<span class="screen-reader-text"><?php esc_html_e( 'Jeda breaking news', 'haka' ); ?></span>
		</button>

	</div>
</section>
<?php
wp_reset_postdata();
