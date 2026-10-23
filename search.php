<?php
/**
 * Halaman hasil pencarian.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

get_header();

global $wp_query;
$haka_found = (int) $wp_query->found_posts;
?>

<main id="primary" class="site-main hk-archive hk-archive--search" role="main">
	<div class="hk-container">

		<header class="hk-pagehead">
			<?php haka_breadcrumbs(); ?>
			<p class="hk-pagehead__eyebrow"><?php esc_html_e( 'Hasil pencarian', 'haka' ); ?></p>
			<h1 class="hk-pagehead__title">&ldquo;<?php echo esc_html( get_search_query() ); ?>&rdquo;</h1>
			<p class="hk-pagehead__count">
				<?php
				printf(
					/* translators: %s: jumlah hasil */
					esc_html( _n( '%s artikel ditemukan', '%s artikel ditemukan', $haka_found, 'haka' ) ),
					esc_html( number_format_i18n( $haka_found ) )
				);
				?>
			</p>
			<div class="hk-pagehead__search">
				<?php get_search_form( array( 'aria_label' => __( 'Cari ulang', 'haka' ) ) ); ?>
			</div>
		</header>

		<?php get_template_part( 'template-parts/archive-loop' ); ?>

	</div>
</main>

<?php
get_footer();
