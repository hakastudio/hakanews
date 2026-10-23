<?php
/**
 * Tombol suka artikel.
 *
 * Mode inline dipakai saat tombol berdiri sebaris dengan tombol bagikan;
 * mode blok menampilkan ajakan lengkap berlatar.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

$haka_post_id = get_the_ID();
$haka_count   = haka_get_likes( $haka_post_id );
$haka_inline  = ! empty( $args['inline'] );
$haka_class   = 'hk-likes' . ( $haka_inline ? ' hk-likes--inline' : '' );
?>
<div class="<?php echo esc_attr( $haka_class ); ?>" data-hk-likes data-post="<?php echo esc_attr( (string) $haka_post_id ); ?>">
	<?php if ( ! $haka_inline ) : ?>
		<p class="hk-likes__label">
			<?php echo esc_html( haka_get_option( 'likes_label', __( 'Suka artikel ini?', 'haka' ) ) ); ?>
		</p>
	<?php endif; ?>

	<button type="button" class="hk-likes__btn" data-hk-like aria-pressed="false">
		<span class="hk-likes__icon" aria-hidden="true">
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false">
				<path d="M20.5 8.6A4.6 4.6 0 0 0 12 6a4.6 4.6 0 0 0-8.5 2.6c0 4.4 6.2 8 8.5 9.9 2.3-1.9 8.5-5.5 8.5-9.9Z"/>
			</svg>
		</span>
		<span class="hk-likes__count" data-hk-like-count><?php echo esc_html( number_format_i18n( $haka_count ) ); ?></span>
		<span class="screen-reader-text"><?php esc_html_e( 'Sukai artikel ini', 'haka' ); ?></span>
	</button>
</div>
