<?php
/**
 * Template fallback utama.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="primary" class="site-main hk-archive" role="main">
	<div class="hk-container">

		<header class="hk-pagehead">
			<?php haka_breadcrumbs(); ?>
			<h1 class="hk-pagehead__title"><?php esc_html_e( 'Berita Terbaru', 'haka' ); ?></h1>
		</header>

		<?php get_template_part( 'template-parts/archive-loop' ); ?>

	</div>
</main>

<?php
get_footer();
