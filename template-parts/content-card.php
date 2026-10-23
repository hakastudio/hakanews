<?php
/**
 * Kartu berita (grid).
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

$haka_compact = ! empty( $args['compact'] );
?>
<article id="<?php echo esc_attr( haka_entry_id() ); ?>" <?php post_class( 'hk-card' . ( $haka_compact ? ' hk-card--compact' : '' ) ); ?>>
	<div class="hk-card__media">
		<?php
		haka_post_thumbnail(
			'haka-card',
			array(
				'class' => 'hk-card__thumb',
				'sizes' => '(max-width: 640px) 50vw, 240px',
			)
		);
		?>
	</div>

	<div class="hk-card__body">
		<h3 class="hk-card__title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h3>

		<p class="hk-card__date">
			<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>">
				<?php echo esc_html( haka_relative_date() ); ?>
			</time>
		</p>
	</div>
</article>
