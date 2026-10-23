<?php
/**
 * Arsip tag.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

get_header();

$haka_term = get_queried_object();
?>

<main id="primary" class="site-main hk-archive hk-archive--tag" role="main">
	<div class="hk-container">

		<header class="hk-pagehead">
			<?php haka_breadcrumbs(); ?>
			<p class="hk-pagehead__eyebrow"><?php esc_html_e( 'Topik', 'haka' ); ?></p>
			<h1 class="hk-pagehead__title">#<?php echo esc_html( single_tag_title( '', false ) ); ?></h1>

			<?php if ( $haka_term instanceof WP_Term && $haka_term->description ) : ?>
				<div class="hk-pagehead__desc"><?php echo wp_kses_post( wpautop( $haka_term->description ) ); ?></div>
			<?php endif; ?>
		</header>

		<?php get_template_part( 'template-parts/archive-loop' ); ?>

	</div>
</main>

<?php
get_footer();
