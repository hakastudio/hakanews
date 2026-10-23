<?php
/**
 * Tampilan saat tidak ada post ditemukan.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="hk-none">
	<h2 class="hk-none__title"><?php esc_html_e( 'Belum ada berita', 'haka' ); ?></h2>

	<?php if ( is_search() ) : ?>
		<p><?php esc_html_e( 'Kata kunci yang Anda cari tidak ditemukan. Coba gunakan kata kunci lain.', 'haka' ); ?></p>
		<?php get_search_form( array( 'aria_label' => __( 'Cari dengan kata lain', 'haka' ) ) ); ?>
	<?php elseif ( current_user_can( 'publish_posts' ) ) : ?>
		<p>
			<?php
			printf(
				/* translators: %s: tautan tambah post */
				wp_kses_post( __( 'Siap menulis berita pertama? <a href="%s">Buat artikel baru</a>.', 'haka' ) ),
				esc_url( admin_url( 'post-new.php' ) )
			);
			?>
		</p>
	<?php else : ?>
		<p><?php esc_html_e( 'Silakan kembali lagi nanti atau gunakan pencarian.', 'haka' ); ?></p>
		<?php get_search_form( array( 'aria_label' => __( 'Cari dengan kata lain', 'haka' ) ) ); ?>
	<?php endif; ?>
</section>
