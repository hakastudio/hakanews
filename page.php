<?php
/**
 * Halaman statis.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="primary" class="site-main hk-page" role="main">
	<div class="hk-container">

		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article id="<?php echo esc_attr( haka_entry_id() ); ?>" <?php post_class( 'hk-page__article' ); ?>>

				<header class="hk-pagehead">
					<?php haka_breadcrumbs(); ?>
					<h1 class="hk-pagehead__title"><?php the_title(); ?></h1>
				</header>

				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="hk-page__media">
						<?php the_post_thumbnail( 'haka-hero', array( 'decoding' => 'async' ) ); ?>
					</figure>
				<?php endif; ?>

				<div class="hk-prose">
					<?php
					the_content();

					wp_link_pages(
						array(
							'before' => '<div class="hk-pagelinks"><span class="hk-pagelinks__label">' . esc_html__( 'Halaman:', 'haka' ) . '</span>',
							'after'  => '</div>',
						)
					);
					?>
				</div>

			</article>

			<?php
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
			?>
			<?php
		endwhile;
		?>

	</div>
</main>

<?php
get_footer();
