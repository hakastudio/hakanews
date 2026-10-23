<?php
/**
 * Ticker indeks saham.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

$haka_items = haka_get_stocks();

if ( empty( $haka_items ) ) {
	return;
}
?>
<section class="hk-stocks" data-hk-live="stocks" aria-label="<?php esc_attr_e( 'Indeks pasar', 'haka' ); ?>">
	<div class="hk-container hk-stocks__inner">
		<span class="hk-stocks__label"><?php echo esc_html( haka_get_option( 'stocks_title', __( 'Bursa', 'haka' ) ) ); ?></span>

		<div class="hk-stocks__viewport">
			<ul class="hk-stocks__track">
				<?php foreach ( $haka_items as $haka_item ) : ?>
					<?php $haka_dir = haka_stock_direction( $haka_item['change'] ); ?>
					<li class="hk-stocks__item hk-stocks__item--<?php echo esc_attr( $haka_dir ); ?>">
						<span class="hk-stocks__name"><?php echo esc_html( $haka_item['name'] ); ?></span>
						<span class="hk-stocks__value"><?php echo esc_html( $haka_item['value'] ); ?></span>
						<?php if ( '' !== $haka_item['change'] ) : ?>
							<span class="hk-stocks__change"><?php echo esc_html( $haka_item['change'] ); ?></span>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</section>
