<?php
/**
 * Customizer: dibangun otomatis dari registry fitur (inc/features.php).
 *
 * Tidak ada kontrol yang ditulis manual di sini, sehingga setiap fitur yang
 * terdaftar dijamin punya pengaturan dan tidak ada setting tanpa kontrol.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------- Sanitasi */

/**
 * Sanitasi checkbox.
 *
 * @param mixed $value Nilai mentah.
 * @return bool
 */
function haka_sanitize_checkbox( $value ) {
	return (bool) $value;
}

/**
 * Sanitasi ID kategori.
 *
 * @param mixed $value Nilai mentah.
 * @return int
 */
function haka_sanitize_category( $value ) {
	$value = absint( $value );

	if ( ! $value ) {
		return 0;
	}

	return term_exists( $value, 'category' ) ? $value : 0;
}

/**
 * Sanitasi daftar ID kategori dipisahkan koma.
 *
 * @param mixed $value Nilai mentah.
 * @return string
 */
function haka_sanitize_category_list( $value ) {
	$ids   = array_filter( array_map( 'absint', explode( ',', (string) $value ) ) );
	$valid = array();

	foreach ( $ids as $id ) {
		if ( term_exists( $id, 'category' ) ) {
			$valid[] = $id;
		}
	}

	return implode( ',', array_slice( array_unique( $valid ), 0, 6 ) );
}

/**
 * Sanitasi daftar slug dipisahkan koma.
 *
 * @param mixed $value Nilai mentah.
 * @return string
 */
function haka_sanitize_slug_list( $value ) {
	$slugs = array_filter( array_map( 'sanitize_title', explode( ',', (string) $value ) ) );

	return implode( ',', array_slice( array_unique( $slugs ), 0, 12 ) );
}

/**
 * Sanitasi angka sesuai batas yang didefinisikan di registry.
 *
 * @param mixed                $value   Nilai mentah.
 * @param WP_Customize_Setting $setting Objek setting.
 * @return int
 */
function haka_sanitize_number( $value, $setting = null ) {
	$value = absint( $value );
	$min   = 0;
	$max   = PHP_INT_MAX;

	if ( $setting instanceof WP_Customize_Setting ) {
		$key    = str_replace( 'haka_', '', $setting->id );
		$fields = haka_all_fields();

		if ( isset( $fields[ $key ]['min'] ) ) {
			$min = (int) $fields[ $key ]['min'];
		}

		if ( isset( $fields[ $key ]['max'] ) ) {
			$max = (int) $fields[ $key ]['max'];
		}
	}

	return max( $min, min( $max, $value ) );
}

/**
 * Sanitasi select/radio: harus salah satu pilihan terdaftar.
 *
 * @param mixed                $value   Nilai mentah.
 * @param WP_Customize_Setting $setting Objek setting.
 * @return string
 */
function haka_sanitize_select( $value, $setting = null ) {
	$value = sanitize_key( $value );

	if ( ! $setting instanceof WP_Customize_Setting ) {
		return $value;
	}

	$key     = str_replace( 'haka_', '', $setting->id );
	$fields  = haka_all_fields();
	$choices = isset( $fields[ $key ]['choices'] ) ? haka_resolve_choices( $fields[ $key ]['choices'] ) : array();

	return array_key_exists( $value, $choices ) ? $value : $setting->default;
}

/**
 * Terjemahkan definisi choices menjadi array.
 *
 * Registry boleh memakai nama fungsi supaya daftar panjang (misal daftar
 * font) tidak ikut dimuat pada setiap permintaan halaman.
 *
 * @param mixed $choices Array atau nama fungsi.
 * @return array
 */
function haka_resolve_choices( $choices ) {
	if ( is_string( $choices ) && function_exists( $choices ) ) {
		$choices = call_user_func( $choices );
	}

	return is_array( $choices ) ? $choices : array();
}

/**
 * Sanitasi daftar ID dipisahkan koma.
 *
 * @param mixed $value Nilai mentah.
 * @return string
 */
function haka_sanitize_id_list( $value ) {
	$ids = array_filter( array_map( 'absint', explode( ',', (string) $value ) ) );

	return implode( ',', array_slice( array_unique( $ids ), 0, 20 ) );
}

/**
 * Sanitasi teks banyak baris (satu entri per baris).
 *
 * @param mixed $value Nilai mentah.
 * @return string
 */
function haka_sanitize_lines( $value ) {
	$lines = preg_split( '/\r\n|\r|\n/', (string) $value );
	$clean = array();

	foreach ( $lines as $line ) {
		$line = sanitize_text_field( trim( $line ) );

		if ( '' !== $line ) {
			$clean[] = $line;
		}
	}

	return implode( "\n", array_slice( $clean, 0, 30 ) );
}

/**
 * Sanitasi koordinat desimal.
 *
 * @param mixed $value Nilai mentah.
 * @return string
 */
function haka_sanitize_coord( $value ) {
	$value = str_replace( ',', '.', trim( (string) $value ) );

	if ( ! is_numeric( $value ) ) {
		return '0';
	}

	return (string) round( (float) $value, 6 );
}

/**
 * Sanitasi kode iklan.
 *
 * Hanya pengguna dengan izin unfiltered_html yang boleh menyimpan script;
 * selebihnya kode dibersihkan seperti konten post biasa.
 *
 * @param mixed $value Nilai mentah.
 * @return string
 */
function haka_sanitize_ad_code( $value ) {
	if ( current_user_can( 'unfiltered_html' ) ) {
		return (string) $value;
	}

	return wp_kses_post( (string) $value );
}

/**
 * Daftar kategori untuk dropdown.
 *
 * @return array
 */
function haka_category_choices() {
	$choices = array( 0 => __( '— Otomatis —', 'haka' ) );

	$terms = get_terms(
		array(
			'taxonomy'   => 'category',
			'hide_empty' => false,
			'number'     => 200,
		)
	);

	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			$choices[ $term->term_id ] = $term->name;
		}
	}

	return $choices;
}

/**
 * Sanitizer default per tipe field.
 *
 * @param string $type Tipe field.
 * @return string
 */
function haka_sanitizer_for_type( $type ) {
	$map = array(
		'checkbox' => 'haka_sanitize_checkbox',
		'select'   => 'haka_sanitize_select',
		'radio'    => 'haka_sanitize_select',
		'number'   => 'haka_sanitize_number',
		'color'    => 'sanitize_hex_color',
		'url'      => 'esc_url_raw',
		'text'     => 'sanitize_text_field',
		'textarea' => 'wp_kses_post',
		'category' => 'haka_sanitize_category',
	);

	return isset( $map[ $type ] ) ? $map[ $type ] : 'sanitize_text_field';
}

/* ---------------------------------------------------------- Registrasi */

/**
 * Bangun panel Customizer dari registry.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 */
function haka_customize_register( $wp_customize ) {
	foreach ( array( 'blogname', 'blogdescription' ) as $core_setting ) {
		$setting = $wp_customize->get_setting( $core_setting );

		if ( $setting ) {
			$setting->transport = 'postMessage';
		}
	}

	$wp_customize->add_panel(
		'haka_panel',
		array(
			'title'       => __( 'Pengaturan Haka', 'haka' ),
			'description' => __( 'Seluruh fitur theme dapat dihidupkan, dimatikan dan disetel dari sini.', 'haka' ),
			'priority'    => 30,
		)
	);

	foreach ( haka_feature_registry() as $section_id => $section ) {
		$wp_customize->add_section(
			'haka_' . $section_id,
			array(
				'title'       => $section['title'],
				'description' => isset( $section['desc'] ) ? $section['desc'] : '',
				'priority'    => isset( $section['priority'] ) ? (int) $section['priority'] : 100,
				'panel'       => 'haka_panel',
			)
		);

		foreach ( $section['fields'] as $key => $field ) {
			$setting_id = 'haka_' . $key;
			$type       = isset( $field['type'] ) ? $field['type'] : 'text';
			$sanitize   = isset( $field['sanitize'] ) ? $field['sanitize'] : haka_sanitizer_for_type( $type );

			$wp_customize->add_setting(
				$setting_id,
				array(
					'default'           => isset( $field['default'] ) ? $field['default'] : '',
					'sanitize_callback' => $sanitize,
					'transport'         => isset( $field['transport'] ) ? $field['transport'] : 'refresh',
				)
			);

			$args = array(
				'label'       => $field['label'],
				'description' => isset( $field['desc'] ) ? $field['desc'] : '',
				'section'     => 'haka_' . $section_id,
			);

			if ( 'color' === $type ) {
				$wp_customize->add_control(
					new WP_Customize_Color_Control( $wp_customize, $setting_id, $args )
				);
				continue;
			}

			if ( 'category' === $type ) {
				$args['type']    = 'select';
				$args['choices'] = haka_category_choices();
			} elseif ( in_array( $type, array( 'select', 'radio' ), true ) ) {
				$args['type']    = $type;
				$args['choices'] = isset( $field['choices'] ) ? haka_resolve_choices( $field['choices'] ) : array();
			} elseif ( 'number' === $type ) {
				$args['type']        = 'number';
				$args['input_attrs'] = array(
					'min' => isset( $field['min'] ) ? (int) $field['min'] : 0,
					'max' => isset( $field['max'] ) ? (int) $field['max'] : 100,
				);
			} else {
				$args['type'] = $type;
			}

			$wp_customize->add_control( $setting_id, $args );
		}
	}

	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial(
			'blogname',
			array(
				'selector'        => '.site-title a',
				'render_callback' => 'haka_customize_partial_blogname',
			)
		);
	}
}
add_action( 'customize_register', 'haka_customize_register' );

/**
 * Render partial nama situs.
 */
function haka_customize_partial_blogname() {
	bloginfo( 'name' );
}

/**
 * Script live preview.
 */
function haka_customize_preview_js() {
	wp_enqueue_script(
		'haka-customizer',
		HAKA_URI . '/assets/js/customizer.js',
		array( 'customize-preview' ),
		haka_asset_version( 'assets/js/customizer.js' ),
		true
	);
}
add_action( 'customize_preview_init', 'haka_customize_preview_js' );
