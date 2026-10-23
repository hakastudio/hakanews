<?php
/**
 * Template Name: Halaman Kontak
 * Template Post Type: page
 *
 * Menampilkan isi halaman ditambah data kontak dari Customizer.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

get_header();

$haka_intro   = haka_get_option( 'contact_intro', '' );
$haka_address = haka_get_option( 'contact_address', '' );
$haka_email   = haka_get_option( 'contact_email', '' );
$haka_phone   = haka_get_option( 'contact_phone', '' );
$haka_maps    = haka_get_option( 'contact_maps', '' );
?>

<main id="primary" class="site-main hk-page hk-contact" role="main">
	<div class="hk-container">

		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article id="<?php echo esc_attr( haka_entry_id() ); ?>" <?php post_class( 'hk-page__article' ); ?>>

				<header class="hk-pagehead">
					<?php haka_breadcrumbs(); ?>
					<h1 class="hk-pagehead__title"><?php the_title(); ?></h1>
					<?php if ( $haka_intro ) : ?>
						<div class="hk-pagehead__desc"><?php echo wp_kses_post( wpautop( $haka_intro ) ); ?></div>
					<?php endif; ?>
				</header>

				<div class="hk-contact__grid">

					<div class="hk-contact__main">
						<div class="hk-prose"><?php the_content(); ?></div>
					</div>

					<aside class="hk-contact__side">
						<?php if ( $haka_address ) : ?>
							<div class="hk-contact__card">
								<h2 class="hk-contact__label"><?php esc_html_e( 'Alamat Redaksi', 'haka' ); ?></h2>
								<div><?php echo wp_kses_post( wpautop( $haka_address ) ); ?></div>
							</div>
						<?php endif; ?>

						<?php if ( $haka_email ) : ?>
							<div class="hk-contact__card">
								<h2 class="hk-contact__label"><?php esc_html_e( 'Email', 'haka' ); ?></h2>
								<p><a href="mailto:<?php echo esc_attr( $haka_email ); ?>"><?php echo esc_html( $haka_email ); ?></a></p>
							</div>
						<?php endif; ?>

						<?php if ( $haka_phone ) : ?>
							<div class="hk-contact__card">
								<h2 class="hk-contact__label"><?php esc_html_e( 'Telepon', 'haka' ); ?></h2>
								<p><?php echo esc_html( $haka_phone ); ?></p>
							</div>
						<?php endif; ?>

						<?php haka_social_links( 'hk-social hk-social--contact' ); ?>
					</aside>

				</div>

				<?php if ( $haka_maps ) : ?>
					<div class="hk-contact__map">
						<iframe src="<?php echo esc_url( $haka_maps ); ?>"
							title="<?php esc_attr_e( 'Peta lokasi redaksi', 'haka' ); ?>"
							loading="lazy"
							referrerpolicy="no-referrer-when-downgrade"
							allowfullscreen></iframe>
					</div>
				<?php endif; ?>

			</article>
			<?php
		endwhile;
		?>

	</div>
</main>

<?php
get_footer();
