<?php
/**
 * Header theme.
 *
 * Satu baris: logo, menu horizontal, lalu tombol aksi di kanan.
 * Menu berpindah ke panel geser saat lebar layar di bawah 1080px.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">

	<header id="masthead" class="hk-header" role="banner">
		<div class="hk-container hk-header__inner">

			<div class="hk-header__brand">
				<?php haka_site_branding(); ?>
			</div>

			<?php if ( haka_enabled( 'navbar_enable' ) ) : ?>
			<nav class="hk-nav" id="site-navigation" aria-label="<?php esc_attr_e( 'Menu utama', 'haka' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'menu_id'        => 'primary-menu',
						'menu_class'     => 'hk-menu',
						'container'      => false,
						'depth'          => 2,
						'fallback_cb'    => 'haka_menu_fallback',
					)
				);
				?>
			</nav>
			<?php endif; ?>

			<div class="hk-header__actions">
				<?php if ( haka_enabled( 'dark_mode' ) ) : ?>
				<button type="button" class="hk-iconbtn hk-themetoggle" id="hk-theme-toggle" aria-pressed="false">
					<span class="hk-themetoggle__sun"><?php haka_icon( 'sun', array( 'size' => 19 ) ); ?></span>
					<span class="hk-themetoggle__moon"><?php haka_icon( 'moon', array( 'size' => 19 ) ); ?></span>
					<span class="screen-reader-text" data-hk-theme-label><?php esc_html_e( 'Ganti ke mode gelap', 'haka' ); ?></span>
				</button>
				<?php endif; ?>

				<?php if ( haka_enabled( 'search_enable' ) ) : ?>
				<button type="button"
					class="hk-iconbtn"
					id="hk-search-toggle"
					aria-expanded="false"
					aria-controls="hk-search-panel">
					<?php haka_icon( 'search', array( 'size' => 20 ) ); ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Buka pencarian', 'haka' ); ?></span>
				</button>
				<?php endif; ?>

				<?php if ( haka_enabled( 'hamburger_enable' ) ) : ?>
				<button type="button"
					class="hk-iconbtn hk-header__toggle"
					id="hk-menu-toggle"
					aria-expanded="false"
					aria-controls="hk-mobile-nav">
					<?php haka_icon( 'menu', array( 'size' => 22 ) ); ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Buka menu navigasi', 'haka' ); ?></span>
				</button>
				<?php endif; ?>
			</div>

		</div>

		<?php if ( haka_enabled( 'search_enable' ) ) : ?>
		<div class="hk-search-panel" id="hk-search-panel" hidden>
			<div class="hk-container">
				<?php get_search_form( array( 'aria_label' => __( 'Cari berita di header', 'haka' ) ) ); ?>
			</div>
		</div>
		<?php endif; ?>
	</header>

	<div class="hk-mobile-nav" id="hk-mobile-nav" hidden>
		<div class="hk-mobile-nav__head">
			<span class="hk-mobile-nav__title"><?php esc_html_e( 'Menu', 'haka' ); ?></span>
			<button type="button" class="hk-iconbtn" id="hk-menu-close">
				<?php haka_icon( 'close', array( 'size' => 22 ) ); ?>
				<span class="screen-reader-text"><?php esc_html_e( 'Tutup menu', 'haka' ); ?></span>
			</button>
		</div>
		<div class="hk-mobile-nav__body">
			<?php get_search_form( array( 'aria_label' => __( 'Cari berita di menu', 'haka' ) ) ); ?>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'menu_id'        => 'mobile-menu',
					'menu_class'     => 'hk-menu hk-menu--mobile',
					'container'      => false,
					'depth'          => 2,
					'fallback_cb'    => 'haka_menu_fallback',
				)
			);
			?>
			<?php haka_social_links( 'hk-social hk-social--mobile' ); ?>
		</div>
	</div>
	<div class="hk-overlay" id="hk-overlay" hidden></div>

	<?php
	get_template_part( 'template-parts/secondary-nav' );
	do_action( 'haka_after_secondary_nav' );

	if ( ( is_front_page() || is_home() ) && haka_get_option( 'breaking_enable', true ) ) {
		get_template_part( 'template-parts/home/breaking' );
	}

	do_action( 'haka_ad_awareness' );
	do_action( 'haka_ad_slot', 'header', 'hk-ad--wide' );
	?>
