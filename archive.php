<?php
/**
 * Arsip umum (tanggal, penulis, format).
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
			<?php the_archive_title( '<h1 class="hk-pagehead__title">', '</h1>' ); ?>
			<?php the_archive_description( '<div class="hk-pagehead__desc">', '</div>' ); ?>
		</header>

		<?php get_template_part( 'template-parts/archive-loop' ); ?>

	</div>
</main>

<?php
get_footer();
