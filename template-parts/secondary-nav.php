<?php
/**
 * Navigasi kedua.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

if ( ! haka_enabled( 'secondnav_enable' ) || ! has_nav_menu( 'secondary' ) ) {
	return;
}

$haka_pos = haka_get_option( 'secondnav_position', 'below' );
?>
<nav class="hk-secondnav hk-secondnav--<?php echo esc_attr( $haka_pos ); ?>"
	aria-label="<?php esc_attr_e( 'Menu kedua', 'haka' ); ?>">
	<div class="hk-container">
		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'secondary',
				'menu_class'     => 'hk-secondnav__list',
				'container'      => false,
				'depth'          => 1,
			)
		);
		?>
	</div>
</nav>
