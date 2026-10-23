<?php
/**
 * Jadwal shalat dan imsakiyah.
 *
 * Dihitung di server dari koordinat memakai posisi matahari, sehingga tidak
 * memerlukan layanan luar dan tetap bekerja saat internet server terbatas.
 * Sudut fajar 20° dan isya 18° mengikuti kriteria yang lazim dipakai di
 * Indonesia.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

/**
 * Normalkan sudut ke rentang 0-360.
 *
 * @param float $degrees Sudut.
 * @return float
 */
function haka_fix_angle( $degrees ) {
	$degrees = fmod( $degrees, 360 );

	return $degrees < 0 ? $degrees + 360 : $degrees;
}

/**
 * Normalkan jam ke rentang 0-24.
 *
 * @param float $hours Jam desimal.
 * @return float
 */
function haka_fix_hour( $hours ) {
	$hours = fmod( $hours, 24 );

	return $hours < 0 ? $hours + 24 : $hours;
}

/**
 * Posisi matahari: deklinasi dan persamaan waktu.
 *
 * @param float $jd Julian day.
 * @return array
 */
function haka_sun_position( $jd ) {
	$d = $jd - 2451545.0;

	$g = haka_fix_angle( 357.529 + 0.98560028 * $d );
	$q = haka_fix_angle( 280.459 + 0.98564736 * $d );
	$l = haka_fix_angle( $q + 1.915 * sin( deg2rad( $g ) ) + 0.020 * sin( deg2rad( 2 * $g ) ) );

	$e = 23.439 - 0.00000036 * $d;

	$declination = rad2deg( asin( sin( deg2rad( $e ) ) * sin( deg2rad( $l ) ) ) );
	$right_asc   = rad2deg( atan2( cos( deg2rad( $e ) ) * sin( deg2rad( $l ) ), cos( deg2rad( $l ) ) ) ) / 15;
	$right_asc   = haka_fix_hour( $right_asc );

	return array(
		'declination' => $declination,
		'equation'    => $q / 15 - $right_asc,
	);
}

/**
 * Selisih jam dari tengah hari untuk sudut ketinggian matahari tertentu.
 *
 * @param float $angle       Sudut ketinggian (negatif di bawah ufuk).
 * @param float $latitude    Lintang.
 * @param float $declination Deklinasi matahari.
 * @return float|null Null bila matahari tidak pernah mencapai sudut itu.
 */
function haka_sun_hour_angle( $angle, $latitude, $declination ) {
	$numerator   = -sin( deg2rad( $angle ) ) - sin( deg2rad( $latitude ) ) * sin( deg2rad( $declination ) );
	$denominator = cos( deg2rad( $latitude ) ) * cos( deg2rad( $declination ) );

	if ( 0.0 === (float) $denominator ) {
		return null;
	}

	$ratio = $numerator / $denominator;

	// Wilayah lintang tinggi: fenomena tidak terjadi pada tanggal tersebut.
	if ( $ratio > 1 || $ratio < -1 ) {
		return null;
	}

	return rad2deg( acos( $ratio ) ) / 15;
}

/**
 * Hitung jadwal shalat untuk satu hari.
 *
 * @param float       $latitude  Lintang.
 * @param float       $longitude Bujur.
 * @param int|null    $timestamp Waktu (unix). Null memakai hari ini.
 * @return array Nama waktu => "HH:MM", atau '-' bila tidak terdefinisi.
 */
function haka_prayer_times( $latitude, $longitude, $timestamp = null ) {
	$timestamp = $timestamp ? (int) $timestamp : (int) current_time( 'timestamp' );

	$year  = (int) wp_date( 'Y', $timestamp );
	$month = (int) wp_date( 'n', $timestamp );
	$day   = (int) wp_date( 'j', $timestamp );

	// Offset zona waktu situs dalam jam.
	$offset = (float) get_option( 'gmt_offset', 7 );

	// Julian day untuk tengah malam waktu setempat.
	if ( $month <= 2 ) {
		$year  -= 1;
		$month += 12;
	}

	$a  = floor( $year / 100 );
	$b  = 2 - $a + floor( $a / 4 );
	$jd = floor( 365.25 * ( $year + 4716 ) ) + floor( 30.6001 * ( $month + 1 ) ) + $day + $b - 1524.5;
	$jd = $jd - $longitude / ( 15 * 24 );

	$sun         = haka_sun_position( $jd );
	$declination = $sun['declination'];
	$equation    = $sun['equation'];

	$dhuhr = haka_fix_hour( 12 - $equation ) + $offset - $longitude / 15;

	$definitions = array(
		'imsak'   => array( 'angle', 20.0, -1 ),
		'subuh'   => array( 'angle', 20.0, -1 ),
		'terbit'  => array( 'angle', 0.833, -1 ),
		'dzuhur'  => array( 'noon', 0, 0 ),
		'ashar'   => array( 'asr', 1, 1 ),
		'maghrib' => array( 'angle', 0.833, 1 ),
		'isya'    => array( 'angle', 18.0, 1 ),
	);

	$times = array();

	foreach ( $definitions as $name => $def ) {
		list( $mode, $param, $direction ) = $def;

		if ( 'noon' === $mode ) {
			$times[ $name ] = $dhuhr;
			continue;
		}

		if ( 'asr' === $mode ) {
			$shadow = (float) $param;
			$angle  = -rad2deg(
				atan( 1 / ( $shadow + tan( deg2rad( abs( $latitude - $declination ) ) ) ) )
			);
			$hours  = haka_sun_hour_angle( $angle, $latitude, $declination );
		} else {
			$hours = haka_sun_hour_angle( (float) $param, $latitude, $declination );
		}

		if ( null === $hours ) {
			$times[ $name ] = null;
			continue;
		}

		$times[ $name ] = $dhuhr + ( $direction * $hours );
	}

	// Imsak: 10 menit sebelum subuh.
	if ( null !== $times['imsak'] ) {
		$times['imsak'] = $times['subuh'] - ( 10 / 60 );
	}

	$formatted = array();

	foreach ( $times as $name => $value ) {
		if ( null === $value ) {
			$formatted[ $name ] = '-';
			continue;
		}

		$value   = haka_fix_hour( $value + 0.5 / 60 ); // Pembulatan ke menit terdekat.
		$hour    = (int) floor( $value );
		$minutes = (int) floor( ( $value - $hour ) * 60 );

		$formatted[ $name ] = sprintf( '%02d:%02d', $hour, $minutes );
	}

	return $formatted;
}

/**
 * Jadwal hari ini, disimpan sementara agar tidak dihitung ulang tiap muat.
 *
 * @return array
 */
function haka_prayer_today() {
	$lat = (float) haka_get_option( 'imsak_lat', '-6.2088' );
	$lng = (float) haka_get_option( 'imsak_lng', '106.8456' );

	$key    = 'haka_prayer_' . md5( $lat . '|' . $lng . '|' . wp_date( 'Y-m-d' ) );
	$cached = get_transient( $key );

	if ( is_array( $cached ) ) {
		return $cached;
	}

	$times = haka_prayer_times( $lat, $lng );
	set_transient( $key, $times, DAY_IN_SECONDS );

	return $times;
}

/**
 * Daftarkan pengaturan modul ini ke registry fitur.
 *
 * @param array $registry Registry fitur.
 * @return array
 */
function haka_imsak_registry( $registry ) {
	$registry = haka_registry_add(
		$registry,
		'widgets_extra',
		array(
			'imsak_enable'  => array(
				'label'   => __( 'Jadwal Imsakiyah', 'haka' ),
				'desc'    => __( 'Dihitung di server dari koordinat, tanpa layanan luar.', 'haka' ),
				'type'    => 'checkbox',
				'default' => false,
			),
			'imsak_city'    => array(
				'label'   => __( 'Nama kota', 'haka' ),
				'type'    => 'text',
				'default' => 'Jakarta',
			),
			'imsak_lat'     => array(
				'label'   => __( 'Lintang (latitude)', 'haka' ),
				'desc'    => __( 'Contoh Jakarta: -6.2088. Gunakan tanda minus untuk Lintang Selatan.', 'haka' ),
				'type'    => 'text',
				'default' => '-6.2088',
				'sanitize' => 'haka_sanitize_coord',
			),
			'imsak_lng'     => array(
				'label'   => __( 'Bujur (longitude)', 'haka' ),
				'desc'    => __( 'Contoh Jakarta: 106.8456.', 'haka' ),
				'type'    => 'text',
				'default' => '106.8456',
				'sanitize' => 'haka_sanitize_coord',
			),
		),
		array(
			'title'    => __( 'Ticker Saham & Imsakiyah', 'haka' ),
			'priority' => 94,
		)
	);
	return $registry;
}
add_filter( 'haka_feature_registry', 'haka_imsak_registry' );

/**
 * Jadwal imsakiyah tampil di puncak sidebar.
 */
function haka_imsak_render() {
	get_template_part( 'template-parts/imsak' );
}
add_action( 'haka_sidebar_start', 'haka_imsak_render' );

/**
 * Jadwal boleh disegarkan lewat REST di balik cache halaman.
 *
 * @param array $fragments Daftar fragmen.
 * @return array
 */
function haka_imsak_fragment( $fragments ) {
	$fragments['imsak'] = 'template-parts/imsak';

	return $fragments;
}
add_filter( 'haka_live_fragments', 'haka_imsak_fragment' );
