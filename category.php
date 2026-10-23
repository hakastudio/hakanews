<?php
/**
 * Arsip kategori.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

get_header();

$haka_term = get_queried_object();
?>

<main id="primary" class="site-main hk-archive hk-archive--category" role="main">
	<div class="hk-container">

		<header class="hk-pagehead">
			<?php haka_breadcrumbs(); ?>
			<p class="hk-pagehead__eyebrow"><?php esc_html_e( 'Kategori', 'haka' ); ?></p>
			<h1 class="hk-pagehead__title"><?php echo esc_html( single_cat_title( '', false ) ); ?></h1>

			<?php if ( $haka_term instanceof WP_Term && $haka_term->description ) : ?>
				<div class="hk-pagehead__desc"><?php echo wp_kses_post( wpautop( $haka_term->description ) ); ?></div>
			<?php endif; ?>

			<?php if ( $haka_term instanceof WP_Term ) : ?>
				<p class="hk-pagehead__count">
					<?php
					printf(
						/* translators: %s: jumlah artikel */
						esc_html( _n( '%s artikel', '%s artikel', (int) $haka_term->count, 'haka' ) ),
						esc_html( number_format_i18n( (int) $haka_term->count ) )
					);
					?>
				</p>
			<?php endif; ?>
		</header>

		<?php get_template_part( 'template-parts/archive-loop' ); ?>

	</div>
</main>

<?php
get_footer();
