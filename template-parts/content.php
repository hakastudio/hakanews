<?php
/**
 * Konten default (dipakai index/arsip sebagai fallback).
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;
?>
<article id="<?php echo esc_attr( haka_entry_id() ); ?>" <?php post_class( 'hk-entry' ); ?>>
	<header class="hk-entry__header">
		<?php haka_category_badge( get_the_ID(), 'hk-badge--plain' ); ?>
		<?php
		if ( is_singular() ) {
			the_title( '<h1 class="hk-entry__title">', '</h1>' );
		} else {
			the_title( '<h2 class="hk-entry__title"><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' );
		}
		?>
		<?php haka_post_meta( array( 'reading' => true ) ); ?>
	</header>

	<?php if ( ! is_singular() ) : ?>
		<div class="hk-entry__media">
			<?php haka_post_thumbnail( 'haka-card' ); ?>
		</div>
	<?php endif; ?>

	<div class="hk-entry__content">
		<?php
		if ( is_singular() ) {
			the_content();

			wp_link_pages(
				array(
					'before' => '<div class="hk-pagelinks"><span class="hk-pagelinks__label">' . esc_html__( 'Halaman:', 'haka' ) . '</span>',
					'after'  => '</div>',
				)
			);
		} else {
			the_excerpt();
		}
		?>
	</div>
</article>
