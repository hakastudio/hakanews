<?php
/**
 * Kartu video berita.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;
?>
<article id="<?php echo esc_attr( haka_entry_id() ); ?>" <?php post_class( 'hk-video' ); ?>>
	<div class="hk-video__media">
		<?php
		haka_post_thumbnail(
			'haka-card',
			array(
				'class' => 'hk-video__thumb',
				'sizes' => '(max-width: 640px) 100vw, 25vw',
			)
		);
		?>
		<span class="hk-video__play" aria-hidden="true">
			<?php haka_icon( 'play', array( 'size' => 34 ) ); ?>
		</span>
	</div>

	<div class="hk-video__body">
		<h3 class="hk-video__title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h3>
		<?php
		haka_post_meta(
			array(
				'author' => false,
				'class'  => 'hk-meta hk-meta--sm hk-meta--invert',
			)
		);
		?>
	</div>
</article>
