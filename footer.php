<?php
/**
 * Footer theme.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

$haka_about = haka_get_option( 'footer_about', '' );

if ( ! $haka_about ) {
	$haka_about = get_bloginfo( 'description', 'display' );
}

$haka_copyright = haka_get_option( 'footer_copyright', '' );
?>

	<?php do_action( 'haka_ad_slot', 'footer', 'hk-ad--wide' ); ?>

	<footer id="colophon" class="hk-footer" role="contentinfo">
		<div class="hk-container">

			<?php
			// Jumlah kolom mengikuti isi yang benar-benar ada, supaya blok
			// terakhir tidak turun sendirian ke baris baru.
			$haka_widget_cols = 0;

			for ( $haka_i = 1; $haka_i <= 3; $haka_i++ ) {
				if ( is_active_sidebar( 'footer-' . $haka_i ) ) {
					$haka_widget_cols++;
				}
			}

			if ( ! $haka_widget_cols ) {
				$haka_widget_cols = 2; // Dua kolom cadangan: Navigasi + Kategori.
			}

			$haka_total_cols = min( 4, 1 + $haka_widget_cols );
			?>
			<div class="hk-footer__grid hk-footer__grid--<?php echo esc_attr( (string) $haka_total_cols ); ?>">

				<div class="hk-footer__col hk-footer__col--about">
					<?php if ( haka_enabled( 'footer_show_logo' ) ) : ?>
						<div class="hk-footer__brand"><?php haka_site_branding( array( 'tag' => 'p' ) ); ?></div>
					<?php endif; ?>
					<?php if ( $haka_about ) : ?>
						<div class="hk-footer__about"><?php echo wp_kses_post( wpautop( $haka_about ) ); ?></div>
					<?php endif; ?>
					<?php
					if ( haka_enabled( 'footer_show_social' ) ) {
						haka_social_links( 'hk-social hk-social--footer' );
					}
					?>
				</div>

				<?php if ( is_active_sidebar( 'footer-1' ) || is_active_sidebar( 'footer-2' ) || is_active_sidebar( 'footer-3' ) ) : ?>

					<?php for ( $haka_i = 1; $haka_i <= 3; $haka_i++ ) : ?>
						<?php if ( is_active_sidebar( 'footer-' . $haka_i ) ) : ?>
							<div class="hk-footer__col">
								<?php dynamic_sidebar( 'footer-' . $haka_i ); ?>
							</div>
						<?php endif; ?>
					<?php endfor; ?>

				<?php else : ?>

					<div class="hk-footer__col">
						<h2 class="hk-footer__title"><?php esc_html_e( 'Navigasi', 'haka' ); ?></h2>
						<?php
						if ( has_nav_menu( 'footer' ) ) {
							wp_nav_menu(
								array(
									'theme_location' => 'footer',
									'menu_class'     => 'hk-footer__menu',
									'container'      => false,
									'depth'          => 1,
								)
							);
						} else {
							wp_list_pages(
								array(
									'title_li' => '',
									'depth'    => 1,
									'number'   => 6,
								)
							);
						}
						?>
					</div>

					<div class="hk-footer__col">
						<h2 class="hk-footer__title"><?php esc_html_e( 'Kategori', 'haka' ); ?></h2>
						<ul class="hk-footer__menu">
							<?php
							$haka_footer_cats = get_categories(
								array(
									'orderby'    => 'count',
									'order'      => 'DESC',
									'number'     => max( 3, min( 12, (int) haka_get_option( 'footer_cat_count', 6 ) ) ),
									'hide_empty' => true,
								)
							);

							foreach ( $haka_footer_cats as $haka_cat ) {
								printf(
									'<li><a href="%1$s">%2$s</a></li>',
									esc_url( get_category_link( $haka_cat ) ),
									esc_html( $haka_cat->name )
								);
							}
							?>
						</ul>
					</div>

				<?php endif; ?>

			</div>

			<div class="hk-footer__bottom hk-footer__bottom--<?php echo esc_attr( (string) haka_get_option( 'footer_bottom_align', 'apart' ) ); ?>">
				<p class="hk-footer__copy">
					<?php
					if ( $haka_copyright ) {
						echo wp_kses_post( $haka_copyright );
					} else {
						printf(
							/* translators: 1: tahun, 2: nama situs */
							esc_html__( '© %1$s %2$s. All Right Reserved.', 'haka' ),
							esc_html( wp_date( 'Y' ) ),
							esc_html( get_bloginfo( 'name' ) )
						);
					}
					?>
				<?php if ( haka_enabled( 'footer_show_rss' ) ) : ?>
					<p class="hk-footer__credit">
						<a href="<?php echo esc_url( get_feed_link() ); ?>">
							<?php haka_icon( 'rss', array( 'size' => 14 ) ); ?>
							<?php esc_html_e( 'RSS Feed', 'haka' ); ?>
						</a>
					</p>
				<?php endif; ?>
			</div>

		</div>
	</footer>

	<?php if ( haka_enabled( 'scroll_to_top' ) ) : ?>
	<button type="button" class="hk-totop" id="hk-totop" hidden>
		<?php haka_icon( 'arrow-up', array( 'size' => 20 ) ); ?>
		<span class="screen-reader-text"><?php esc_html_e( 'Kembali ke atas', 'haka' ); ?></span>
	</button>
	<?php endif; ?>

</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>
