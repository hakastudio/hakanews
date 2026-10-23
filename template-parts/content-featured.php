<?php
/**
 * Berita utama (hero besar).
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;
?>
<article id="<?php echo esc_attr( haka_entry_id() ); ?>" <?php post_class( 'hk-hero__main' ); ?>>
	<div class="hk-hero__media">
		<?php
		haka_post_thumbnail(
			'haka-hero',
			array(
				'class' => 'hk-hero__thumb',
				'lazy'  => false,
				'sizes' => '(max-width: 1024px) 100vw, 66vw',
			)
		);
		?>
	</div>

	<div class="hk-hero__body">
		<?php haka_category_badge( get_the_ID() ); ?>

		<h2 class="hk-hero__title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h2>

		<?php if ( get_the_excerpt() ) : ?>
			<p class="hk-hero__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 26 ) ); ?></p>
		<?php endif; ?>

		<?php haka_post_meta( array( 'reading' => true ) ); ?>
	</div>
</article>
