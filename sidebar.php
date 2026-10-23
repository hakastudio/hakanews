<?php
/**
 * Sidebar.
 *
 * Bila belum ada widget yang dipasang, tampilkan set default portal berita
 * (Terpopuler + Tag Terpopuler) agar theme tetap utuh sejak diaktifkan.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;
?>
<aside id="secondary" class="hk-sidebar<?php echo haka_enabled( 'sidebar_sticky' ) ? ' hk-sidebar--sticky' : ''; ?> widget-area" role="complementary" aria-label="<?php esc_attr_e( 'Sidebar', 'haka' ); ?>">
	<?php do_action( 'haka_sidebar_start' ); ?>

	<?php if ( is_active_sidebar( 'sidebar-primary' ) ) : ?>
		<?php dynamic_sidebar( 'sidebar-primary' ); ?>
	<?php else : ?>

		<?php if ( haka_enabled( 'popular_enable' ) && haka_has_enough_posts( 3 ) ) : ?>
			<section class="widget hk-widget hk-widget--popular">
				<h2 class="hk-widget__title"><?php esc_html_e( 'Terpopuler', 'haka' ); ?></h2>
				<?php
				get_template_part(
					'template-parts/post/popular',
					null,
					array( 'number' => (int) haka_get_option( 'popular_count', 5 ) )
				);
				?>
			</section>
		<?php endif; ?>

		<?php
		$haka_sidebar_tags = haka_enabled( 'tagcloud_enable' ) ? haka_get_popular_tags( 18 ) : array();

		if ( ! empty( $haka_sidebar_tags ) ) :
			?>
			<section class="widget hk-widget hk-widget--tags">
				<h2 class="hk-widget__title"><?php esc_html_e( 'Tag Terpopuler', 'haka' ); ?></h2>
				<ul class="hk-tags">
					<?php foreach ( $haka_sidebar_tags as $haka_tag ) : ?>
						<li>
							<a class="hk-tag" href="<?php echo esc_url( get_tag_link( $haka_tag ) ); ?>">
								#<?php echo esc_html( $haka_tag->name ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>
		<?php endif; ?>

	<?php endif; ?>

	<?php do_action( 'haka_ad_slot', 'sidebar' ); ?>
</aside>
