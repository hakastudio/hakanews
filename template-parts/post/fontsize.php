<?php
/**
 * Pengatur ukuran huruf artikel.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

$haka_inline = ! empty( $args['inline'] );
?>
<div class="hk-fontsize<?php echo $haka_inline ? ' hk-fontsize--inline' : ''; ?>"
	role="group"
	aria-label="<?php esc_attr_e( 'Ukuran huruf artikel', 'haka' ); ?>">

	<?php if ( ! $haka_inline ) : ?>
		<span class="hk-fontsize__label"><?php esc_html_e( 'Ukuran teks', 'haka' ); ?></span>
	<?php endif; ?>

	<button type="button" class="hk-fontsize__btn" data-hk-font="down">
		<span aria-hidden="true">A&minus;</span>
		<span class="screen-reader-text"><?php esc_html_e( 'Perkecil huruf', 'haka' ); ?></span>
	</button>
	<button type="button" class="hk-fontsize__btn" data-hk-font="reset">
		<span aria-hidden="true">A</span>
		<span class="screen-reader-text"><?php esc_html_e( 'Ukuran normal', 'haka' ); ?></span>
	</button>
	<button type="button" class="hk-fontsize__btn" data-hk-font="up">
		<span aria-hidden="true">A+</span>
		<span class="screen-reader-text"><?php esc_html_e( 'Perbesar huruf', 'haka' ); ?></span>
	</button>
</div>
