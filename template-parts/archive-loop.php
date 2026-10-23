<?php
/**
 * Loop arsip: dipakai kategori, tag, pencarian, penulis dan arsip tanggal.
 *
 * Tata letaknya mengikuti pilihan Customizer — daftar (bawaan) atau grid.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

$haka_layout = haka_get_option( 'archive_layout', 'list' );
$haka_is_grid = ( 'grid' === $haka_layout );

$haka_wrap_class = $haka_is_grid
	? 'hk-grid hk-grid--cards'
	: 'hk-stack hk-stack--archive';
?>
<div class="hk-layout">

	<div class="hk-layout__main">
		<?php if ( have_posts() ) : ?>

			<h2 class="screen-reader-text"><?php esc_html_e( 'Daftar berita', 'haka' ); ?></h2>

			<div class="<?php echo esc_attr( $haka_wrap_class ); ?>"<?php haka_infinite_list_attr(); ?>>
				<?php
				while ( have_posts() ) :
					the_post();

					if ( $haka_is_grid ) {
						get_template_part( 'template-parts/content', 'card' );
					} else {
						get_template_part(
							'template-parts/content',
							'list',
							array(
								'size'    => 'lg',
								'badge'   => true,
								'excerpt' => haka_enabled( 'archive_excerpt' ),
							)
						);
					}
				endwhile;
				?>
			</div>

			<?php haka_pagination(); ?>
			<?php haka_infinite_marker(); ?>

		<?php else : ?>
			<?php get_template_part( 'template-parts/content', 'none' ); ?>
		<?php endif; ?>
	</div>

	<?php if ( haka_enabled( 'sidebar_enable' ) ) : ?>
		<div class="hk-layout__aside">
			<?php get_sidebar(); ?>
		</div>
	<?php endif; ?>

</div>
