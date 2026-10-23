<?php
/**
 * Jadwal shalat & imsakiyah hari ini.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

if ( ! haka_enabled( 'imsak_enable' ) ) {
	return;
}

$haka_times = haka_prayer_today();
$haka_city  = haka_get_option( 'imsak_city', 'Jakarta' );

$haka_labels = array(
	'imsak'   => __( 'Imsak', 'haka' ),
	'subuh'   => __( 'Subuh', 'haka' ),
	'terbit'  => __( 'Terbit', 'haka' ),
	'dzuhur'  => __( 'Dzuhur', 'haka' ),
	'ashar'   => __( 'Ashar', 'haka' ),
	'maghrib' => __( 'Maghrib', 'haka' ),
	'isya'    => __( 'Isya', 'haka' ),
);
?>
<section class="widget hk-widget hk-imsak" data-hk-live="imsak" data-hk-date="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>">
	<h2 class="hk-widget__title"><?php esc_html_e( 'Jadwal Shalat', 'haka' ); ?></h2>

	<p class="hk-imsak__meta">
		<?php echo esc_html( $haka_city ); ?>
		<span aria-hidden="true">&middot;</span>
		<?php echo esc_html( haka_absolute_date_now() ); ?>
	</p>

	<ul class="hk-imsak__list">
		<?php foreach ( $haka_labels as $haka_key => $haka_label ) : ?>
			<li class="hk-imsak__row">
				<span class="hk-imsak__name"><?php echo esc_html( $haka_label ); ?></span>
				<span class="hk-imsak__time"><?php echo esc_html( isset( $haka_times[ $haka_key ] ) ? $haka_times[ $haka_key ] : '-' ); ?></span>
			</li>
		<?php endforeach; ?>
	</ul>

	<p class="hk-imsak__note"><?php esc_html_e( 'Perhitungan astronomi, dapat berbeda beberapa menit dari jadwal resmi setempat.', 'haka' ); ?></p>
</section>
