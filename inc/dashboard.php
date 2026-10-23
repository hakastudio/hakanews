<?php
/**
 * Kotak informasi Haka di Dashboard WordPress.
 *
 * Urutannya sengaja dimulai dari yang berguna: versi yang terpasang, apakah
 * ada pembaruan, apa saja yang berubah, dan ke mana mencari panduan. Ajakan
 * membeli ditaruh paling bawah dalam satu kalimat.
 *
 * Alasannya sederhana: kotak dashboard yang isinya jualan lebih sering
 * ditutup daripada dibaca, dan pemilik situs yang merasa dijejali iklan
 * cenderung mengganti theme, bukan membeli. Kotak ini juga bisa
 * disembunyikan lewat Opsi Layar bawaan WordPress — tidak perlu tombol
 * tutup buatan sendiri.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

/**
 * Daftarkan kotak dashboard.
 */
function haka_dashboard_widget() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$theme = wp_get_theme( get_template() );

	wp_add_dashboard_widget(
		'haka_dashboard',
		sprintf(
			/* translators: %s: nama theme. */
			esc_html__( '%s — informasi theme', 'haka' ),
			esc_html( $theme->get( 'Name' ) )
		),
		'haka_dashboard_widget_render'
	);
}
add_action( 'wp_dashboard_setup', 'haka_dashboard_widget' );

/**
 * Isi kotak dashboard.
 */
function haka_dashboard_widget_render() {
	$theme     = wp_get_theme( get_template() );
	$terpasang = $theme->get( 'Version' );

	// Data pembaruan dibaca dari cache yang sudah dikumpulkan updater.
	// Kotak dashboard tidak boleh memicu permintaan HTTP sendiri: dashboard
	// harus tetap cepat meski server pembaruan sedang lambat.
	$info    = get_transient( HAKA_UPDATE_TRANSIENT );
	$info    = is_array( $info ) ? $info : array();
	$terbaru = isset( $info['version'] ) ? $info['version'] : '';
	$riwayat = isset( $info['history'] ) && is_array( $info['history'] ) ? $info['history'] : array();

	$ada_pembaruan = '' !== $terbaru && version_compare( $terbaru, $terpasang, '>' );

	$halaman  = $theme->get( 'ThemeURI' );
	$halaman  = $halaman ? $halaman : 'https://rizqy.id/haka';
	$panduan  = rtrim( $halaman, '/' ) . '/panduan';
	?>
	<p class="haka-dash__versi">
		<strong><?php esc_html_e( 'Versi terpasang', 'haka' ); ?>:</strong>
		<?php echo esc_html( $terpasang ); ?>

		<?php if ( $ada_pembaruan ) : ?>
			<span class="haka-dash__tanda haka-dash__tanda--baru">
				<?php
				printf(
					/* translators: %s: nomor versi terbaru. */
					esc_html__( 'Versi %s tersedia', 'haka' ),
					esc_html( $terbaru )
				);
				?>
			</span>
			<a href="<?php echo esc_url( admin_url( 'themes.php' ) ); ?>">
				<?php esc_html_e( 'Perbarui', 'haka' ); ?>
			</a>
		<?php elseif ( '' !== $terbaru ) : ?>
			<span class="haka-dash__tanda"><?php esc_html_e( 'Sudah paling baru', 'haka' ); ?></span>
		<?php endif; ?>
	</p>

	<?php if ( ! empty( $riwayat ) ) : ?>
		<h3 class="haka-dash__judul"><?php esc_html_e( 'Yang berubah', 'haka' ); ?></h3>

		<?php foreach ( array_slice( $riwayat, 0, 3 ) as $rilis ) : ?>
			<div class="haka-dash__rilis">
				<p class="haka-dash__rilis-versi">
					<strong><?php echo esc_html( $rilis['versi'] ); ?></strong>
					<?php if ( ! empty( $rilis['tanggal'] ) ) : ?>
						<span><?php echo esc_html( haka_dashboard_tanggal( $rilis['tanggal'] ) ); ?></span>
					<?php endif; ?>
				</p>

				<?php if ( ! empty( $rilis['perubahan'] ) ) : ?>
					<ul class="haka-dash__daftar">
						<?php foreach ( array_slice( $rilis['perubahan'], 0, 4 ) as $satu ) : ?>
							<li><?php echo esc_html( $satu['teks'] ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php elseif ( ! empty( $rilis['ringkas'] ) ) : ?>
					<p class="haka-dash__ringkas"><?php echo esc_html( $rilis['ringkas'] ); ?></p>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>

	<h3 class="haka-dash__judul"><?php esc_html_e( 'Plugin SEO & cache', 'haka' ); ?></h3>
	<ul class="haka-dash__daftar">
		<?php foreach ( haka_compat_summary() as $haka_baris ) : ?>
			<li><?php echo esc_html( $haka_baris ); ?></li>
		<?php endforeach; ?>
	</ul>

	<p class="haka-dash__tautan">
		<a href="<?php echo esc_url( $panduan ); ?>" target="_blank" rel="noopener">
			<?php esc_html_e( 'Panduan pengaturan', 'haka' ); ?>
		</a>
		<a href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>">
			<?php esc_html_e( 'Buka Customizer', 'haka' ); ?>
		</a>
		<a href="<?php echo esc_url( $halaman . '#catatan-versi' ); ?>" target="_blank" rel="noopener">
			<?php esc_html_e( 'Catatan versi', 'haka' ); ?>
		</a>
	</p>

	<p class="haka-dash__dukungan">
		<?php esc_html_e( 'Haka gratis diunduh, tanpa syarat. Yang memilih membayar, seluruh hasilnya disalurkan untuk anak yatim, fakir miskin, dan pembangunan masjid — rinciannya terbuka di halaman theme.', 'haka' ); ?>
	</p>
	<?php
}

/**
 * Ubah tanggal ISO menjadi tanggal berbahasa Indonesia.
 *
 * @param string $iso Tanggal bentuk YYYY-MM-DD.
 * @return string
 */
function haka_dashboard_tanggal( $iso ) {
	$waktu = strtotime( $iso );

	if ( ! $waktu ) {
		return $iso;
	}

	return wp_date( get_option( 'date_format' ), $waktu );
}

/**
 * Gaya kotak dashboard.
 *
 * Ditulis langsung di halaman dashboard saja, bukan sebagai berkas terpisah,
 * supaya tidak menambah satu permintaan hanya untuk beberapa baris aturan.
 *
 * @param string $hook Nama layar admin.
 */
function haka_dashboard_style( $hook ) {
	if ( 'index.php' !== $hook || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$css = '
		#haka_dashboard .haka-dash__tanda { display: inline-block; margin-left: 6px; padding: 1px 8px; border-radius: 999px; background: #f0f0f1; font-size: 12px; }
		#haka_dashboard .haka-dash__tanda--baru { background: #d63638; color: #fff; }
		#haka_dashboard .haka-dash__judul { margin: 16px 0 8px; font-size: 13px; text-transform: uppercase; letter-spacing: .04em; color: #646970; }
		#haka_dashboard .haka-dash__rilis { padding: 10px 0; border-top: 1px solid #f0f0f1; }
		#haka_dashboard .haka-dash__rilis-versi { margin: 0 0 4px; }
		#haka_dashboard .haka-dash__rilis-versi span { margin-left: 8px; color: #646970; }
		#haka_dashboard .haka-dash__daftar { margin: 0; padding-left: 18px; list-style: disc; color: #50575e; }
		#haka_dashboard .haka-dash__ringkas { margin: 0; color: #50575e; }
		#haka_dashboard .haka-dash__tautan { margin-top: 14px; display: flex; flex-wrap: wrap; gap: 14px; }
		#haka_dashboard .haka-dash__dukungan { margin: 12px 0 0; padding-top: 12px; border-top: 1px solid #f0f0f1; color: #646970; }
	';

	wp_register_style( 'haka-dashboard', false, array(), HAKA_VERSION );
	wp_enqueue_style( 'haka-dashboard' );
	wp_add_inline_style( 'haka-dashboard', $css );
}
add_action( 'admin_enqueue_scripts', 'haka_dashboard_style' );

/**
 * Ringkasan penyesuaian untuk kotak dashboard.
 *
 * @return string[] Baris keterangan.
 */
function haka_compat_summary() {
	$baris = array();
	$seo   = haka_seo_plugin();
	$cache = haka_cache_plugin();

	if ( $seo ) {
		$baris[] = sprintf(
			/* translators: %s: nama plugin SEO */
			__( '%s terdeteksi: schema JSON-LD, Open Graph, dan judul tab dari theme dimatikan; breadcrumb dan kategori utama mengikuti plugin.', 'haka' ),
			haka_plugin_label( $seo )
		);

		if ( haka_plugin_news_sitemap() ) {
			$baris[] = __( 'Sitemap berita dari plugin dipakai; /news-sitemap.xml milik theme tidak diterbitkan.', 'haka' );
		}
	} else {
		$baris[] = __( 'Tidak ada plugin SEO: theme mencetak schema, Open Graph, dan sitemap berita sendiri. Semua otomatis mundur begitu plugin SEO dipasang.', 'haka' );
	}

	if ( $cache ) {
		$baris[] = sprintf(
			/* translators: %s: nama plugin cache */
			__( '%s terdeteksi: hitungan view dan suka dicatat lewat permintaan terpisah, jadwal shalat dan ticker saham disegarkan dari peramban, dan cache dibersihkan setiap pengaturan theme disimpan.', 'haka' ),
			haka_plugin_label( $cache )
		);
	}

	return $baris;
}
