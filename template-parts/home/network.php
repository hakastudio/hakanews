<?php
/**
 * Network News: tautan ke situs lain dalam satu jaringan media.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

$haka_sites = haka_parse_lines( haka_get_option( 'network_items', '' ), 2 );

if ( empty( $haka_sites ) ) {
	return;
}
?>
<section class="hk-section hk-network">
	<?php haka_section_title( haka_get_option( 'network_title', __( 'Jaringan Media', 'haka' ) ) ); ?>

	<ul class="hk-network__list">
		<?php foreach ( $haka_sites as $haka_site ) : ?>
			<?php
			$haka_name = $haka_site[0];
			$haka_url  = esc_url( $haka_site[1] );

			if ( ! $haka_url ) {
				continue;
			}
			?>
			<li>
				<a class="hk-network__item" href="<?php echo esc_url( $haka_url ); ?>" target="_blank" rel="noopener noreferrer">
					<span class="hk-network__mark" aria-hidden="true"><?php echo esc_html( mb_substr( $haka_name, 0, 1 ) ); ?></span>
					<span class="hk-network__name"><?php echo esc_html( $haka_name ); ?></span>
					<?php haka_icon( 'arrow-right', array( 'size' => 15 ) ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</section>
