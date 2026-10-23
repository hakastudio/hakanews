<?php
/**
 * Dynamic Recent Posts: daftar berita dengan tab kategori.
 *
 * Seluruh panel dirender di server. JavaScript hanya menyembunyikan panel
 * yang tidak aktif, sehingga tanpa JavaScript pembaca tetap melihat isinya.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

$haka_tabs = haka_get_dynamic_categories();

if ( empty( $haka_tabs ) ) {
	return;
}

$haka_per_tab = (int) haka_get_option( 'dynamic_count', 5 );
$haka_uid     = wp_unique_id( 'hk-tabs-' );
?>
<section class="hk-section hk-tabs" data-hk-tabs>
	<?php haka_section_title( haka_get_option( 'dynamic_title', __( 'Jelajahi Berita', 'haka' ) ) ); ?>

	<div class="hk-tabs__nav" role="tablist" aria-label="<?php esc_attr_e( 'Pilih kategori', 'haka' ); ?>">
		<?php foreach ( $haka_tabs as $haka_i => $haka_tab ) : ?>
			<button type="button"
				class="hk-tabs__btn<?php echo 0 === $haka_i ? ' is-active' : ''; ?>"
				id="<?php echo esc_attr( $haka_uid . '-tab-' . $haka_i ); ?>"
				role="tab"
				aria-selected="<?php echo 0 === $haka_i ? 'true' : 'false'; ?>"
				aria-controls="<?php echo esc_attr( $haka_uid . '-panel-' . $haka_i ); ?>">
				<?php echo esc_html( $haka_tab->name ); ?>
			</button>
		<?php endforeach; ?>
	</div>

	<?php foreach ( $haka_tabs as $haka_i => $haka_tab ) : ?>
		<?php
		$haka_tab_query = haka_query_category( $haka_tab->term_id, $haka_per_tab );

		if ( ! $haka_tab_query->have_posts() ) {
			wp_reset_postdata();
			continue;
		}
		?>
		<div class="hk-tabs__panel"
			id="<?php echo esc_attr( $haka_uid . '-panel-' . $haka_i ); ?>"
			role="tabpanel"
			aria-labelledby="<?php echo esc_attr( $haka_uid . '-tab-' . $haka_i ); ?>"
			<?php echo 0 === $haka_i ? '' : 'data-hk-tab-hidden'; ?>>

			<div class="hk-stack">
				<?php
				while ( $haka_tab_query->have_posts() ) :
					$haka_tab_query->the_post();
					get_template_part( 'template-parts/content', 'list', array( 'badge' => false ) );
				endwhile;
				?>
			</div>

			<p class="hk-tabs__more">
				<a href="<?php echo esc_url( get_category_link( $haka_tab ) ); ?>">
					<?php
					printf(
						/* translators: %s: nama kategori */
						esc_html__( 'Lihat semua %s', 'haka' ),
						esc_html( $haka_tab->name )
					);
					?>
					<?php haka_icon( 'chevron-right', array( 'size' => 15 ) ); ?>
				</a>
			</p>
		</div>
		<?php wp_reset_postdata(); ?>
	<?php endforeach; ?>
</section>
