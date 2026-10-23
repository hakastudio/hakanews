<?php
/**
 * Halaman 404.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="primary" class="site-main hk-404" role="main">
	<div class="hk-container">

		<div class="hk-404__inner">
			<p class="hk-404__code">404</p>
			<h1 class="hk-404__title"><?php esc_html_e( 'Halaman tidak ditemukan', 'haka' ); ?></h1>
			<p class="hk-404__text">
				<?php esc_html_e( 'Halaman yang Anda cari mungkin sudah dipindahkan atau dihapus. Coba cari beritanya di bawah ini.', 'haka' ); ?>
			</p>

			<div class="hk-404__search">
				<?php get_search_form( array( 'aria_label' => __( 'Cari halaman lain', 'haka' ) ) ); ?>
			</div>

			<p class="hk-404__actions">
				<a class="hk-btn" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php haka_icon( 'home', array( 'size' => 16 ) ); ?>
					<?php esc_html_e( 'Kembali ke beranda', 'haka' ); ?>
				</a>
			</p>
		</div>

		<section class="hk-section">
			<?php haka_section_title( __( 'Berita Terpopuler', 'haka' ), '', 'h2' ); ?>
			<div class="hk-404__popular">
				<?php get_template_part( 'template-parts/post/popular', null, array( 'number' => 5 ) ); ?>
			</div>
		</section>

	</div>
</main>

<?php
get_footer();
