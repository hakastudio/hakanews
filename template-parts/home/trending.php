<?php
/**
 * Trending topics (tag).
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

if ( ! haka_get_option( 'trending_enable', true ) ) {
	return;
}

$haka_tags = haka_get_trending_tags();

if ( empty( $haka_tags ) ) {
	return;
}

$haka_label = haka_get_option( 'trending_label', __( 'Trending', 'haka' ) );
?>
<section class="hk-trending" aria-label="<?php echo esc_attr( $haka_label ); ?>">
	<div class="hk-container hk-trending__inner">
		<span class="hk-trending__label">
			<?php haka_icon( 'flame', array( 'size' => 16 ) ); ?>
			<?php echo esc_html( $haka_label ); ?>
		</span>

		<ul class="hk-trending__list">
			<?php foreach ( $haka_tags as $haka_tag ) : ?>
				<li>
					<a class="hk-tag" href="<?php echo esc_url( get_tag_link( $haka_tag ) ); ?>">
						#<?php echo esc_html( $haka_tag->name ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
