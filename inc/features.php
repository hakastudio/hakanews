<?php
/**
 * Registry fitur theme.
 *
 * Satu-satunya sumber kebenaran untuk seluruh pengaturan Haka. Customizer
 * dibangun otomatis dari daftar ini (inc/customizer.php), sehingga setiap
 * fitur dijamin punya kontrol dan tidak ada setting yatim.
 *
 * Tipe yang didukung: checkbox, select, text, textarea, number, color, url,
 * category, radio.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

/**
 * Seluruh section beserta fiturnya.
 *
 * @return array
 */
function haka_feature_registry() {
	$registry = array(

		/* ------------------------------------------------------- Tipografi */
		'typography' => array(
			'title'    => __( 'Tipografi', 'haka' ),
			'priority' => 5,
			'desc'     => __( 'Pilih font dan atur ukuran tiap tingkat judul.', 'haka' ),
			'fields'   => array(
				'heading_font'        => array(
					'label'   => __( 'Font judul', 'haka' ),
					'type'    => 'select',
					'default' => 'Plus Jakarta Sans',
					'choices' => 'haka_font_choices',
				),
				'body_font'           => array(
					'label'   => __( 'Font teks', 'haka' ),
					'type'    => 'select',
					'default' => 'Inter',
					'choices' => 'haka_font_choices',
				),
				'base_size'           => array(
					'label'   => __( 'Ukuran teks antarmuka (px)', 'haka' ),
					'desc'    => __( 'Judul kartu, meta, menu. Bukan isi artikel.', 'haka' ),
					'type'    => 'number',
					'default' => 13,
					'min'     => 11,
					'max'     => 18,
				),
				'article_size'        => array(
					'label'   => __( 'Ukuran teks artikel (px)', 'haka' ),
					'type'    => 'number',
					'default' => 16,
					'min'     => 14,
					'max'     => 22,
				),
				'body_line_height'    => array(
					'label'   => __( 'Kerapatan baris artikel', 'haka' ),
					'type'    => 'select',
					'default' => '18',
					'choices' => array(
						'15' => __( 'Rapat (1,5)', 'haka' ),
						'16' => __( 'Sedang (1,6)', 'haka' ),
						'17' => __( 'Longgar (1,7)', 'haka' ),
						'18' => __( 'Sangat longgar (1,8)', 'haka' ),
						'20' => __( 'Lega (2,0)', 'haka' ),
					),
				),
				'h1_size'             => array(
					'label'   => __( 'Ukuran H1 / judul artikel (px)', 'haka' ),
					'type'    => 'number',
					'default' => 34,
					'min'     => 22,
					'max'     => 60,
				),
				'h2_size'             => array(
					'label'   => __( 'Ukuran H2 / judul section (px)', 'haka' ),
					'type'    => 'number',
					'default' => 24,
					'min'     => 16,
					'max'     => 44,
				),
				'h3_size'             => array(
					'label'   => __( 'Ukuran H3 (px)', 'haka' ),
					'type'    => 'number',
					'default' => 18,
					'min'     => 14,
					'max'     => 32,
				),
				'h4_size'             => array(
					'label'   => __( 'Ukuran H4 (px)', 'haka' ),
					'type'    => 'number',
					'default' => 16,
					'min'     => 12,
					'max'     => 26,
				),
				'heading_weight'      => array(
					'label'   => __( 'Ketebalan judul', 'haka' ),
					'type'    => 'select',
					'default' => '800',
					'choices' => array(
						'500' => __( 'Sedang (500)', 'haka' ),
						'600' => __( 'Agak tebal (600)', 'haka' ),
						'700' => __( 'Tebal (700)', 'haka' ),
						'800' => __( 'Sangat tebal (800)', 'haka' ),
					),
				),
				'body_weight'         => array(
					'label'   => __( 'Ketebalan teks', 'haka' ),
					'type'    => 'select',
					'default' => '400',
					'choices' => array(
						'300' => __( 'Tipis (300)', 'haka' ),
						'400' => __( 'Normal (400)', 'haka' ),
						'500' => __( 'Agak tebal (500)', 'haka' ),
					),
				),
				'heading_tracking'    => array(
					'label'   => __( 'Kerapatan huruf judul', 'haka' ),
					'type'    => 'select',
					'default' => 'tight',
					'choices' => array(
						'normal' => __( 'Normal', 'haka' ),
						'tight'  => __( 'Rapat', 'haka' ),
						'tighter' => __( 'Sangat rapat', 'haka' ),
					),
				),
				'mobile_scale'        => array(
					'label'   => __( 'Skala judul di ponsel (%)', 'haka' ),
					'desc'    => __( 'Judul diperkecil sekian persen di layar kecil.', 'haka' ),
					'type'    => 'number',
					'default' => 78,
					'min'     => 60,
					'max'     => 100,
				),
			),
		),

		/* ------------------------------------------------ Tampilan & Warna */
		'appearance' => array(
			'title'    => __( 'Tampilan & Warna', 'haka' ),
			'priority' => 10,
			'fields'   => array(
				'primary_color'   => array(
					'label'     => __( 'Warna utama', 'haka' ),
					'desc'      => __( 'Dipakai untuk aksen, label kategori dan tombol.', 'haka' ),
					'type'      => 'color',
					'default'   => '#ff3300',
					'transport' => 'postMessage',
				),
				'dark_mode'       => array(
					'label'   => __( 'Aktifkan Dark Mode', 'haka' ),
					'desc'    => __( 'Menampilkan tombol ganti tema terang/gelap di header.', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'dark_mode_start' => array(
					'label'   => __( 'Tema awal pengunjung', 'haka' ),
					'type'    => 'select',
					'default' => 'system',
					'choices' => array(
						'system' => __( 'Ikuti setelan perangkat', 'haka' ),
						'light'  => __( 'Selalu terang', 'haka' ),
						'dark'   => __( 'Selalu gelap', 'haka' ),
					),
				),
				'sticky_header'   => array(
					'label'   => __( 'Header menempel saat scroll', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'hover_zoom'      => array(
					'label'   => __( 'Efek zoom gambar saat disentuh kursor', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'font_resizer'    => array(
					'label'   => __( 'Pengatur ukuran huruf di artikel', 'haka' ),
					'desc'    => __( 'Tombol A- / A / A+ pada halaman artikel.', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'rtl_support'     => array(
					'label'   => __( 'Dukungan arah kanan-ke-kiri (RTL)', 'haka' ),
					'desc'    => __( 'Aktif otomatis bila bahasa situs memakai arah RTL.', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
			),
		),

		/* --------------------------------------------- Header & Navigasi */
		'navigation' => array(
			'title'    => __( 'Header & Navigasi', 'haka' ),
			'priority' => 20,
			'fields'   => array(
				'navbar_enable'    => array(
					'label'   => __( 'Tampilkan menu utama di header', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'hamburger_enable' => array(
					'label'   => __( 'Tombol hamburger (menu geser)', 'haka' ),
					'desc'    => __( 'Selalu dipakai di layar kecil. Matikan untuk menyembunyikannya di layar besar.', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'search_enable'    => array(
					'label'   => __( 'Tombol pencarian di header', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'menu_uppercase'   => array(
					'label'   => __( 'Menu huruf kapital', 'haka' ),
					'type'    => 'checkbox',
					'default' => false,
				),
				'menu_active'      => array(
					'label'   => __( 'Penanda menu aktif', 'haka' ),
					'type'    => 'select',
					'default' => 'underline',
					'choices' => array(
						'underline' => __( 'Garis bawah', 'haka' ),
						'pill'      => __( 'Latar pil', 'haka' ),
						'color'     => __( 'Warna saja', 'haka' ),
					),
				),
				'menu_gap'         => array(
					'label'   => __( 'Jarak antar item menu (px)', 'haka' ),
					'type'    => 'number',
					'default' => 20,
					'min'     => 10,
					'max'     => 40,
				),
				'submenu_caret'    => array(
					'label'   => __( 'Penanda panah pada menu bersubmenu', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'breadcrumbs'      => array(
					'label'   => __( 'Tampilkan breadcrumb', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'scroll_to_top'    => array(
					'label'   => __( 'Tombol kembali ke atas', 'haka' ),
					'desc'    => __( 'Bawaannya mati; hidupkan bila memang diperlukan.', 'haka' ),
					'type'    => 'checkbox',
					'default' => false,
				),
			),
		),

		/* ----------------------------------------------- Tampilan Ponsel */
		'mobile' => array(
			'title'    => __( 'Tampilan Ponsel', 'haka' ),
			'priority' => 78,
			'desc'     => __( 'Sembunyikan section tertentu di layar kecil agar halaman tidak terlalu panjang. Section tetap tampil di tablet dan desktop.', 'haka' ),
			'fields'   => array(
				'hm_secondary' => array(
					'label'   => __( 'Sembunyikan Secondary News', 'haka' ),
					'type'    => 'checkbox',
					'default' => false,
				),
				'hm_editors'   => array(
					'label'   => __( 'Sembunyikan Pilihan Redaksi', 'haka' ),
					'type'    => 'checkbox',
					'default' => false,
				),
				'hm_multicat'  => array(
					'label'   => __( 'Sembunyikan blok kategori', 'haka' ),
					'type'    => 'checkbox',
					'default' => false,
				),
				'hm_story'     => array(
					'label'   => __( 'Sembunyikan Video Story', 'haka' ),
					'type'    => 'checkbox',
					'default' => false,
				),
				'hm_tabs'      => array(
					'label'   => __( 'Sembunyikan Jelajahi Berita', 'haka' ),
					'type'    => 'checkbox',
					'default' => false,
				),
				'hm_recommend' => array(
					'label'   => __( 'Sembunyikan Rekomendasi', 'haka' ),
					'type'    => 'checkbox',
					'default' => false,
				),
				'hm_videos'    => array(
					'label'   => __( 'Sembunyikan Video Berita', 'haka' ),
					'type'    => 'checkbox',
					'default' => false,
				),
				'hm_network'   => array(
					'label'   => __( 'Sembunyikan Jaringan Media', 'haka' ),
					'type'    => 'checkbox',
					'default' => false,
				),
			),
		),

		/* ------------------------------------------------ Indeks Berita */
		'index' => array(
			'title'    => __( 'Indeks Berita', 'haka' ),
			'priority' => 77,
			'desc'     => __( 'Halaman daftar seluruh berita dengan penyaring kategori dan tanggal. Buat halaman baru lalu pilih template "Indeks Berita".', 'haka' ),
			'fields'   => array(
				'index_per_page'  => array(
					'label'   => __( 'Jumlah berita per halaman', 'haka' ),
					'type'    => 'number',
					'default' => 30,
					'min'     => 10,
					'max'     => 100,
				),
				'index_filters'   => array(
					'label'   => __( 'Tampilkan penyaring', 'haka' ),
					'desc'    => __( 'Pilihan kategori dan tanggal di atas daftar.', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'index_group'     => array(
					'label'   => __( 'Kelompokkan per tanggal', 'haka' ),
					'desc'    => __( 'Beri judul tanggal setiap kali harinya berganti.', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'index_show_time' => array(
					'label'   => __( 'Tampilkan jam terbit', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'index_show_cat'  => array(
					'label'   => __( 'Tampilkan label kategori', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
			),
		),

		/* ------------------------------------------------- Halaman Arsip */
		'archive' => array(
			'title'    => __( 'Halaman Arsip', 'haka' ),
			'priority' => 76,
			'desc'     => __( 'Tampilan kategori, tag, pencarian, penulis dan arsip tanggal.', 'haka' ),
			'fields'   => array(
				'archive_layout'  => array(
					'label'   => __( 'Tata letak daftar', 'haka' ),
					'type'    => 'select',
					'default' => 'list',
					'choices' => array(
						'list' => __( 'Daftar (thumbnail kiri, judul kanan)', 'haka' ),
						'grid' => __( 'Grid kartu', 'haka' ),
					),
				),
				'archive_columns' => array(
					'label'   => __( 'Jumlah kolom grid', 'haka' ),
					'desc'    => __( 'Hanya berlaku bila tata letak diatur ke Grid kartu.', 'haka' ),
					'type'    => 'number',
					'default' => 3,
					'min'     => 2,
					'max'     => 4,
				),
				'archive_excerpt' => array(
					'label'   => __( 'Tampilkan ringkasan artikel', 'haka' ),
					'desc'    => __( 'Beberapa baris pertama artikel pada tata letak daftar.', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
			),
		),

		/* --------------------------------------------------- Tanggal & Waktu */
		'datetime' => array(
			'title'    => __( 'Tanggal & Waktu', 'haka' ),
			'priority' => 30,
			'fields'   => array(
				'time_ago'    => array(
					'label'   => __( 'Tampilkan waktu relatif', 'haka' ),
					'desc'    => __( 'Contoh: "3 jam yang lalu". Bila dimatikan, tanggal ditampilkan penuh.', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'time_ago_max' => array(
					'label'   => __( 'Batas waktu relatif (hari)', 'haka' ),
					'desc'    => __( 'Lebih lama dari ini ditampilkan sebagai tanggal biasa.', 'haka' ),
					'type'    => 'number',
					'default' => 30,
					'min'     => 1,
					'max'     => 365,
				),
				'date_format' => array(
					'label'   => __( 'Format tanggal', 'haka' ),
					'type'    => 'select',
					'default' => 'wp',
					'choices' => array(
						'wp'   => __( 'Ikuti pengaturan WordPress', 'haka' ),
						'id'   => __( 'Indonesia — 6 September 2026', 'haka' ),
						'id_s' => __( 'Indonesia singkat — 6 Sep 2026', 'haka' ),
						'us'   => __( 'Amerika — September 6, 2026', 'haka' ),
						'iso'  => __( 'Angka — 06/09/2026', 'haka' ),
					),
				),
			),
		),

		/* ------------------------------------------------------ Breaking News */
		'breaking' => array(
			'title'    => __( 'Breaking News (Ticker)', 'haka' ),
			'priority' => 40,
			'fields'   => array(
				'breaking_enable'   => array(
					'label'   => __( 'Tampilkan breaking news', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'breaking_label'    => array(
					'label'   => __( 'Label', 'haka' ),
					'type'    => 'text',
					'default' => 'Breaking News',
				),
				'breaking_category' => array(
					'label'   => __( 'Kategori sumber', 'haka' ),
					'desc'    => __( 'Kosongkan untuk memakai post terbaru.', 'haka' ),
					'type'    => 'category',
					'default' => 0,
				),
				'breaking_count'    => array(
					'label'   => __( 'Jumlah judul', 'haka' ),
					'type'    => 'number',
					'default' => 6,
					'min'     => 3,
					'max'     => 12,
				),
				'breaking_speed'    => array(
					'label'   => __( 'Kecepatan gerak', 'haka' ),
					'type'    => 'select',
					'default' => 'normal',
					'choices' => array(
						'slow'      => __( 'Lambat', 'haka' ),
						'normal'    => __( 'Sedang', 'haka' ),
						'fast'      => __( 'Cepat', 'haka' ),
						'very_fast' => __( 'Sangat cepat', 'haka' ),
						'ultra'     => __( 'Ultra cepat', 'haka' ),
						'whoosh'    => __( 'Whoosh', 'haka' ),
					),
				),
			),
		),

		/* ---------------------------------------------------- Trending Topics */
		'trending' => array(
			'title'    => __( 'Trending Topics', 'haka' ),
			'priority' => 50,
			'fields'   => array(
				'trending_enable' => array(
					'label'   => __( 'Tampilkan trending topics', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'trending_label'  => array(
					'label'   => __( 'Label', 'haka' ),
					'type'    => 'text',
					'default' => 'Trending',
				),
				'trending_tags'   => array(
					'label'   => __( 'Slug tag pilihan', 'haka' ),
					'desc'    => __( 'Pisahkan dengan koma. Kosongkan untuk memakai tag terpopuler otomatis.', 'haka' ),
					'type'    => 'text',
					'default' => '',
					'sanitize' => 'haka_sanitize_slug_list',
				),
				'trending_count'  => array(
					'label'   => __( 'Jumlah tag otomatis', 'haka' ),
					'type'    => 'number',
					'default' => 8,
					'min'     => 3,
					'max'     => 15,
				),
			),
		),

		/* ------------------------------------------ Headline News (Carousel) */
		'hero' => array(
			'title'    => __( 'Headline News (Carousel)', 'haka' ),
			'priority' => 60,
			'fields'   => array(
				'hero_enable'   => array(
					'label'   => __( 'Tampilkan headline news', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'hero_carousel' => array(
					'label'   => __( 'Mode carousel', 'haka' ),
					'desc'    => __( 'Bila dimatikan, hanya satu berita utama yang tampil.', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'hero_source'   => array(
					'label'   => __( 'Sumber berita', 'haka' ),
					'type'    => 'select',
					'default' => 'latest',
					'choices' => array(
						'latest'   => __( 'Berita terbaru', 'haka' ),
						'category' => __( 'Kategori pilihan', 'haka' ),
						'sticky'   => __( 'Post disematkan', 'haka' ),
					),
				),
				'featured_category' => array(
					'label'   => __( 'Kategori pilihan', 'haka' ),
					'desc'    => __( 'Dipakai bila sumber di atas diatur ke "Kategori pilihan".', 'haka' ),
					'type'    => 'category',
					'default' => 0,
				),
				'hero_count'    => array(
					'label'   => __( 'Jumlah slide', 'haka' ),
					'type'    => 'number',
					'default' => 5,
					'min'     => 1,
					'max'     => 10,
				),
				'hero_ratio'    => array(
					'label'   => __( 'Proporsi gambar', 'haka' ),
					'type'    => 'select',
					'default' => '16-9',
					'choices' => array(
						'16-9'  => __( 'Lebar (16:9)', 'haka' ),
						'16-10' => __( 'Sedang (16:10)', 'haka' ),
						'3-2'   => __( 'Klasik (3:2)', 'haka' ),
						'4-3'   => __( 'Tinggi (4:3)', 'haka' ),
					),
				),
				'hero_autoplay' => array(
					'label'   => __( 'Geser otomatis', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'hero_interval' => array(
					'label'   => __( 'Jeda antar slide (detik)', 'haka' ),
					'type'    => 'number',
					'default' => 6,
					'min'     => 3,
					'max'     => 20,
				),
				'hero_arrows'   => array(
					'label'   => __( 'Tombol panah', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'hero_dots'     => array(
					'label'   => __( 'Indikator titik', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
			),
		),

		/* ------------------------------------------------- Section Homepage */
		'homepage' => array(
			'title'    => __( 'Section Homepage', 'haka' ),
			'priority' => 70,
			'fields'   => array(
				'secondary_enable'  => array(
					'label'   => __( 'Secondary News (grid di bawah headline)', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'secondary_count'   => array(
					'label'   => __( 'Jumlah berita', 'haka' ),
					'type'    => 'number',
					'default' => 6,
					'min'     => 2,
					'max'     => 12,
				),
				'multicat_enable'   => array(
					'label'   => __( 'Multi-Category Grid', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'block_categories'  => array(
					'label'   => __( 'Kategori untuk blok', 'haka' ),
					'desc'    => __( 'ID kategori dipisahkan koma, contoh: 3,5,8. Kosongkan untuk otomatis.', 'haka' ),
					'type'    => 'text',
					'default' => '',
					'sanitize' => 'haka_sanitize_category_list',
				),
				'multicat_count'    => array(
					'label'   => __( 'Jumlah blok kategori', 'haka' ),
					'type'    => 'number',
					'default' => 2,
					'min'     => 1,
					'max'     => 6,
				),
				'latest_enable'     => array(
					'label'   => __( 'Berita Terbaru (Recent Posts)', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'latest_layout'     => array(
					'label'   => __( 'Tata letak Berita Terbaru', 'haka' ),
					'desc'    => __( 'Ringkas memuat lebih banyak judul pada ruang yang sama.', 'haka' ),
					'type'    => 'select',
					'default' => 'compact',
					'choices' => array(
						'compact' => __( 'Ringkas', 'haka' ),
						'large'   => __( 'Besar (thumbnail lebar)', 'haka' ),
					),
				),
				'latest_count'      => array(
					'label'   => __( 'Jumlah berita terbaru', 'haka' ),
					'desc'    => __( 'Kosongkan pengaturan WordPress bila ingin memakai nilai ini.', 'haka' ),
					'type'    => 'number',
					'default' => 12,
					'min'     => 5,
					'max'     => 30,
				),
				'multicat_min'      => array(
					'label'   => __( 'Minimum artikel per blok kategori', 'haka' ),
					'desc'    => __( 'Blok dengan artikel lebih sedikit dari ini dilewati agar tidak tampil setengah kosong.', 'haka' ),
					'type'    => 'number',
					'default' => 3,
					'min'     => 2,
					'max'     => 6,
				),
				'video_enable'      => array(
					'label'   => __( 'News Videos', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'video_category'    => array(
					'label'   => __( 'Kategori video', 'haka' ),
					'desc'    => __( 'Kosongkan untuk memakai post berformat Video.', 'haka' ),
					'type'    => 'category',
					'default' => 0,
				),
				'video_count'       => array(
					'label'   => __( 'Jumlah video', 'haka' ),
					'type'    => 'number',
					'default' => 4,
					'min'     => 2,
					'max'     => 12,
				),
			),
		),

		/* --------------------------------------------------------- Sidebar */
		'sidebar' => array(
			'title'    => __( 'Discovery Sidebar', 'haka' ),
			'priority' => 80,
			'fields'   => array(
				'sidebar_enable'   => array(
					'label'   => __( 'Tampilkan sidebar', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'sidebar_sticky'   => array(
					'label'   => __( 'Sidebar menempel saat scroll', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'popular_enable'   => array(
					'label'   => __( 'Blok Terpopuler', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'popular_range'    => array(
					'label'   => __( 'Rentang hari "Terpopuler"', 'haka' ),
					'type'    => 'number',
					'default' => 30,
					'min'     => 1,
					'max'     => 365,
				),
				'popular_count'    => array(
					'label'   => __( 'Jumlah artikel terpopuler', 'haka' ),
					'type'    => 'number',
					'default' => 5,
					'min'     => 3,
					'max'     => 10,
				),
				'tagcloud_enable'  => array(
					'label'   => __( 'Blok Tag Terpopuler', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
			),
		),

		/* -------------------------------------------------------- Artikel */
		'single' => array(
			'title'    => __( 'Halaman Artikel', 'haka' ),
			'priority' => 90,
			'fields'   => array(
				'reading_time'     => array(
					'label'   => __( 'Estimasi waktu baca', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'reading_progress' => array(
					'label'   => __( 'Bilah progres baca', 'haka' ),
					'desc'    => __( 'Garis tipis di atas layar yang terisi seiring pembaca menggulir.', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'summary_enable'   => array(
					'label'   => __( 'Poin penting di awal artikel', 'haka' ),
					'desc'    => __( 'Ringkasan singkat yang ditulis redaksi lewat kotak "Poin Penting" di layar edit artikel. Bila kotak itu kosong, tidak ada yang ditampilkan.', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'summary_title'    => array(
					'label'   => __( 'Judul blok poin penting', 'haka' ),
					'type'    => 'text',
					'default' => 'Poin Penting',
				),
				'toc_enable'       => array(
					'label'   => __( 'Daftar isi artikel', 'haka' ),
					'desc'    => __( 'Disusun otomatis dari subjudul (H2 dan H3) di dalam artikel.', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'toc_title'        => array(
					'label'   => __( 'Judul daftar isi', 'haka' ),
					'type'    => 'text',
					'default' => 'Daftar Isi',
				),
				'toc_min'          => array(
					'label'   => __( 'Minimal subjudul agar daftar isi muncul', 'haka' ),
					'desc'    => __( 'Artikel pendek tidak perlu daftar isi. Di bawah angka ini, blok disembunyikan.', 'haka' ),
					'type'    => 'number',
					'default' => 3,
					'min'     => 2,
					'max'     => 12,
				),
				'toc_collapsed'    => array(
					'label'   => __( 'Daftar isi tertutup saat halaman dibuka', 'haka' ),
					'desc'    => __( 'Pembaca membukanya sendiri bila perlu. Berguna untuk artikel panjang di layar ponsel.', 'haka' ),
					'type'    => 'checkbox',
					'default' => false,
				),
				'updated_badge'    => array(
					'label'   => __( 'Tandai artikel yang diperbarui', 'haka' ),
					'desc'    => __( 'Menampilkan tanggal revisi sebagai label, bukan teks kecil.', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'postnav_enable'   => array(
					'label'   => __( 'Navigasi artikel sebelumnya/berikutnya', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'post_tags'        => array(
					'label'   => __( 'Tampilkan tag artikel', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'share_enable'     => array(
					'label'   => __( 'Tombol berbagi', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'sticky_share'     => array(
					'label'   => __( 'Tombol berbagi melayang (sticky)', 'haka' ),
					'desc'    => __( 'Menempel di sisi layar saat artikel digulir.', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'copy_attribution' => array(
					'label'   => __( 'Sisipkan sumber saat teks disalin', 'haka' ),
					'desc'    => __( 'Menambahkan baris "Sumber: judul + tautan" pada teks yang disalin pembaca.', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'related_enable'   => array(
					'label'   => __( 'Artikel terkait', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'related_count'    => array(
					'label'   => __( 'Jumlah artikel terkait', 'haka' ),
					'type'    => 'number',
					'default' => 4,
					'min'     => 2,
					'max'     => 8,
				),
				'authorbox_enable' => array(
					'label'   => __( 'Kotak profil penulis', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'comments_enable'  => array(
					'label'   => __( 'Tampilkan komentar', 'haka' ),
					'desc'    => __( 'Tetap mengikuti pengaturan diskusi WordPress.', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
			),
		),

		/* ------------------------------------------- Section Homepage Lanjutan */
		'curated' => array(
			'title'    => __( 'Editor\'s Choice & Rekomendasi', 'haka' ),
			'priority' => 72,
			'fields'   => array(
				'editors_enable'   => array(
					'label'   => __( 'Tampilkan Editor\'s Choice', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'editors_title'    => array(
					'label'   => __( 'Judul section', 'haka' ),
					'type'    => 'text',
					'default' => 'Pilihan Redaksi',
				),
				'editors_source'   => array(
					'label'   => __( 'Sumber', 'haka' ),
					'type'    => 'select',
					'default' => 'sticky',
					'choices' => array(
						'sticky'   => __( 'Post disematkan', 'haka' ),
						'category' => __( 'Kategori pilihan', 'haka' ),
						'manual'   => __( 'ID post manual', 'haka' ),
					),
				),
				'editors_category' => array(
					'label'   => __( 'Kategori', 'haka' ),
					'type'    => 'category',
					'default' => 0,
				),
				'editors_ids'      => array(
					'label'   => __( 'ID post manual', 'haka' ),
					'desc'    => __( 'Pisahkan dengan koma, contoh: 12,34,56.', 'haka' ),
					'type'    => 'text',
					'default' => '',
					'sanitize' => 'haka_sanitize_id_list',
				),
				'editors_count'    => array(
					'label'   => __( 'Jumlah artikel', 'haka' ),
					'type'    => 'number',
					'default' => 4,
					'min'     => 2,
					'max'     => 8,
				),
				'recommend_enable' => array(
					'label'   => __( 'Tampilkan Rekomendasi', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'recommend_title'  => array(
					'label'   => __( 'Judul Rekomendasi', 'haka' ),
					'type'    => 'text',
					'default' => 'Rekomendasi',
				),
				'recommend_count'  => array(
					'label'   => __( 'Jumlah artikel rekomendasi', 'haka' ),
					'type'    => 'number',
					'default' => 4,
					'min'     => 2,
					'max'     => 8,
				),
				'dynamic_enable'   => array(
					'label'   => __( 'Dynamic Recent Posts (bertab)', 'haka' ),
					'desc'    => __( 'Daftar berita dengan tab kategori yang bisa diganti tanpa memuat ulang.', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'dynamic_title'    => array(
					'label'   => __( 'Judul section bertab', 'haka' ),
					'type'    => 'text',
					'default' => 'Jelajahi Berita',
				),
				'dynamic_cats'     => array(
					'label'   => __( 'Kategori untuk tab', 'haka' ),
					'desc'    => __( 'ID kategori dipisahkan koma. Kosongkan untuk otomatis.', 'haka' ),
					'type'    => 'text',
					'default' => '',
					'sanitize' => 'haka_sanitize_category_list',
				),
				'dynamic_count'    => array(
					'label'   => __( 'Jumlah artikel per tab', 'haka' ),
					'type'    => 'number',
					'default' => 5,
					'min'     => 3,
					'max'     => 10,
				),
			),
		),

		/* -------------------------------------------- Video Story & Network */
		'extras' => array(
			'title'    => __( 'Video Story & Network News', 'haka' ),
			'priority' => 74,
			'fields'   => array(
				'story_enable'   => array(
					'label'   => __( 'Tampilkan Video Story', 'haka' ),
					'desc'    => __( 'Kartu vertikal 9:16 bergaya Shorts, digeser ke samping.', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'story_title'    => array(
					'label'   => __( 'Judul', 'haka' ),
					'type'    => 'text',
					'default' => 'Video Story',
				),
				'story_category' => array(
					'label'   => __( 'Kategori sumber', 'haka' ),
					'desc'    => __( 'Kosongkan untuk memakai post berformat Video.', 'haka' ),
					'type'    => 'category',
					'default' => 0,
				),
				'story_count'    => array(
					'label'   => __( 'Jumlah story', 'haka' ),
					'type'    => 'number',
					'default' => 8,
					'min'     => 3,
					'max'     => 16,
				),
				'network_enable' => array(
					'label'   => __( 'Tampilkan Network News', 'haka' ),
					'type'    => 'checkbox',
					'default' => false,
				),
				'network_title'  => array(
					'label'   => __( 'Judul', 'haka' ),
					'type'    => 'text',
					'default' => 'Jaringan Media',
				),
				'network_items'  => array(
					'label'   => __( 'Daftar situs jaringan', 'haka' ),
					'desc'    => __( 'Satu baris satu situs, format: Nama|https://alamat-situs.com', 'haka' ),
					'type'    => 'textarea',
					'default' => '',
					'sanitize' => 'haka_sanitize_lines',
				),
			),
		),

		/* --------------------------------------------------- Navigasi kedua */
		'secondary_nav' => array(
			'title'    => __( 'Secondary Navigation', 'haka' ),
			'priority' => 22,
			'fields'   => array(
				'secondnav_enable'   => array(
					'label'   => __( 'Aktifkan navigasi kedua', 'haka' ),
					'desc'    => __( 'Atur isinya di Tampilan → Menu, lokasi "Menu Kedua".', 'haka' ),
					'type'    => 'checkbox',
					'default' => false,
				),
				'secondnav_position' => array(
					'label'   => __( 'Posisi', 'haka' ),
					'type'    => 'select',
					'default' => 'below',
					'choices' => array(
						'below' => __( 'Di bawah header', 'haka' ),
						'top'   => __( 'Bilah atas (topbar)', 'haka' ),
					),
				),
			),
		),

		/* -------------------------------------------------------- Artikel+ */
		'engagement' => array(
			'title'    => __( 'Interaksi Artikel', 'haka' ),
			'priority' => 92,
			'fields'   => array(
				'fontsize_position' => array(
					'label'   => __( 'Posisi pengatur ukuran huruf', 'haka' ),
					'type'    => 'select',
					'default' => 'byline',
					'choices' => array(
						'byline' => __( 'Sebaris dengan nama penulis', 'haka' ),
						'block'  => __( 'Baris terpisah di atas gambar', 'haka' ),
					),
				),
				'related_layout'    => array(
					'label'   => __( 'Tata letak Artikel Terkait', 'haka' ),
					'type'    => 'select',
					'default' => 'list',
					'choices' => array(
						'list' => __( 'Daftar ringkas', 'haka' ),
						'grid' => __( 'Grid kartu', 'haka' ),
					),
				),
				'related_title'     => array(
					'label'   => __( 'Judul Artikel Terkait', 'haka' ),
					'type'    => 'text',
					'default' => 'Artikel Terkait',
				),
				'recommended_layout' => array(
					'label'   => __( 'Tata letak Rekomendasi', 'haka' ),
					'type'    => 'select',
					'default' => 'grid',
					'choices' => array(
						'grid' => __( 'Grid kartu', 'haka' ),
						'list' => __( 'Daftar ringkas', 'haka' ),
					),
				),
				'recommended_title' => array(
					'label'   => __( 'Judul Rekomendasi', 'haka' ),
					'type'    => 'text',
					'default' => 'Mungkin Anda Suka',
				),
				'inline_related'    => array(
					'label'   => __( 'Artikel terkait di tengah tulisan', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'inline_after'      => array(
					'label'   => __( 'Sisipkan setelah paragraf ke-', 'haka' ),
					'type'    => 'number',
					'default' => 3,
					'min'     => 1,
					'max'     => 12,
				),
				'inline_count'      => array(
					'label'   => __( 'Jumlah tautan sisipan', 'haka' ),
					'type'    => 'number',
					'default' => 2,
					'min'     => 1,
					'max'     => 4,
				),
				'recommended_posts' => array(
					'label'   => __( 'Recommended Posts di bawah artikel', 'haka' ),
					'desc'    => __( 'Berbeda dari Artikel Terkait: diambil dari kategori lain agar pembaca menemukan topik baru.', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'recommended_count' => array(
					'label'   => __( 'Jumlah', 'haka' ),
					'type'    => 'number',
					'default' => 4,
					'min'     => 2,
					'max'     => 8,
				),
			),
		),

		/* -------------------------------------------------- Performa & SEO */
		'performance' => array(
			'title'    => __( 'Performa & SEO', 'haka' ),
			'priority' => 98,
			'fields'   => array(
				'infinite_scroll' => array(
					'label'   => __( 'Infinite Scroll pada daftar berita', 'haka' ),
					'type'    => 'checkbox',
					'default' => false,
				),
				'infinite_mode'   => array(
					'label'   => __( 'Cara memuat', 'haka' ),
					'type'    => 'select',
					'default' => 'button',
					'choices' => array(
						'button' => __( 'Tombol "Muat lebih banyak"', 'haka' ),
						'auto'   => __( 'Otomatis saat digulir', 'haka' ),
					),
				),
				'lazy_load'       => array(
					'label'   => __( 'Lazy load gambar', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
			),
		),

		/* -------------------------------------------------- Halaman Kontak */
		'contact' => array(
			'title'    => __( 'Halaman Kontak', 'haka' ),
			'priority' => 99,
			'fields'   => array(
				'contact_intro'   => array(
					'label'   => __( 'Kalimat pembuka', 'haka' ),
					'type'    => 'textarea',
					'default' => '',
				),
				'contact_address' => array(
					'label'   => __( 'Alamat redaksi', 'haka' ),
					'type'    => 'textarea',
					'default' => '',
				),
				'contact_email'   => array(
					'label'   => __( 'Email redaksi', 'haka' ),
					'type'    => 'text',
					'default' => '',
					'sanitize' => 'sanitize_email',
				),
				'contact_phone'   => array(
					'label'   => __( 'Telepon', 'haka' ),
					'type'    => 'text',
					'default' => '',
				),
				'contact_maps'    => array(
					'label'   => __( 'URL embed peta', 'haka' ),
					'desc'    => __( 'Tempel alamat "embed" dari layanan peta. Kosongkan bila tidak dipakai.', 'haka' ),
					'type'    => 'url',
					'default' => '',
				),
			),
		),

		/* --------------------------------------------------------- Footer */
		'footer' => array(
			'title'    => __( 'Footer & Sosial Media', 'haka' ),
			'priority' => 100,
			'fields'   => array(
				'footer_show_logo'   => array(
					'label'   => __( 'Tampilkan logo di footer', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'footer_show_social' => array(
					'label'   => __( 'Tampilkan ikon sosial media', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'footer_show_rss'    => array(
					'label'   => __( 'Tampilkan tautan RSS', 'haka' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				'footer_cat_count'   => array(
					'label'   => __( 'Jumlah kategori di footer', 'haka' ),
					'desc'    => __( 'Dipakai bila area widget footer dibiarkan kosong.', 'haka' ),
					'type'    => 'number',
					'default' => 6,
					'min'     => 3,
					'max'     => 12,
				),
				'footer_bottom_align' => array(
					'label'   => __( 'Perataan baris bawah', 'haka' ),
					'type'    => 'select',
					'default' => 'apart',
					'choices' => array(
						'apart'  => __( 'Copyright kiri, RSS kanan', 'haka' ),
						'center' => __( 'Rata tengah', 'haka' ),
					),
				),
				'footer_about'     => array(
					'label'     => __( 'Tentang Haka', 'haka' ),
					'desc'      => __( 'Kosongkan untuk memakai tagline situs.', 'haka' ),
					'type'      => 'textarea',
					'default'   => '',
					'transport' => 'postMessage',
				),
				'footer_copyright' => array(
					'label'     => __( 'Teks copyright', 'haka' ),
					'type'      => 'text',
					'default'   => '',
					'sanitize'  => 'wp_kses_post',
					'transport' => 'postMessage',
				),
				'social_facebook'  => array(
					'label'   => __( 'Facebook', 'haka' ),
					'type'    => 'url',
					'default' => '',
				),
				'social_instagram' => array(
					'label'   => __( 'Instagram', 'haka' ),
					'type'    => 'url',
					'default' => '',
				),
				'social_x'         => array(
					'label'   => __( 'X (Twitter)', 'haka' ),
					'type'    => 'url',
					'default' => '',
				),
				'social_youtube'   => array(
					'label'   => __( 'YouTube', 'haka' ),
					'type'    => 'url',
					'default' => '',
				),
				'social_tiktok'    => array(
					'label'   => __( 'TikTok', 'haka' ),
					'type'    => 'url',
					'default' => '',
				),
				'social_whatsapp'  => array(
					'label'   => __( 'WhatsApp Channel', 'haka' ),
					'type'    => 'url',
					'default' => '',
				),
			),
		),
	);

	/**
	 * Ubah atau tambah fitur theme.
	 *
	 * @param array $registry Registry fitur.
	 */
	return apply_filters( 'haka_feature_registry', $registry );
}

/**
 * Tambahkan field ke sebuah section registry, membuat section-nya bila belum ada.
 *
 * Dipakai modul opsional (iklan, suka, ticker, dan sebagainya) supaya
 * pengaturannya ikut hilang ketika berkas modulnya tidak dimuat.
 *
 * @param array  $registry Registry fitur.
 * @param string $id       ID section.
 * @param array  $fields   Field yang ditambahkan.
 * @param array  $section  Judul/prioritas/deskripsi bila section baru.
 * @param string $before   Sisipkan sebelum field ini (opsional).
 * @return array
 */
function haka_registry_add( $registry, $id, $fields, $section = array(), $before = '' ) {
	if ( ! isset( $registry[ $id ] ) ) {
		$registry[ $id ] = array_merge(
			array(
				'title'    => $id,
				'priority' => 100,
				'fields'   => array(),
			),
			$section
		);
	}

	if ( $before && isset( $registry[ $id ]['fields'][ $before ] ) ) {
		$baru = array();

		foreach ( $registry[ $id ]['fields'] as $key => $field ) {
			if ( $key === $before ) {
				$baru = array_merge( $baru, $fields );
			}
			$baru[ $key ] = $field;
		}

		$registry[ $id ]['fields'] = $baru;
	} else {
		$registry[ $id ]['fields'] = array_merge( $registry[ $id ]['fields'], $fields );
	}

	return $registry;
}

/**
 * Seluruh field dalam bentuk datar: kunci => definisi.
 *
 * @return array
 */
function haka_all_fields() {
	static $flat = null;

	if ( null !== $flat ) {
		return $flat;
	}

	$flat = array();

	foreach ( haka_feature_registry() as $section ) {
		foreach ( $section['fields'] as $key => $field ) {
			$flat[ $key ] = $field;
		}
	}

	return $flat;
}

/**
 * Nilai default seluruh opsi.
 *
 * @return array
 */
function haka_default_options() {
	$defaults = array();

	foreach ( haka_all_fields() as $key => $field ) {
		$defaults[ $key ] = isset( $field['default'] ) ? $field['default'] : '';
	}

	return $defaults;
}

/**
 * Ambil nilai satu opsi.
 *
 * @param string $key     Kunci opsi tanpa prefix.
 * @param mixed  $default Nilai default eksplisit.
 * @return mixed
 */
function haka_get_option( $key, $default = null ) {
	if ( null === $default ) {
		$fields  = haka_all_fields();
		$default = isset( $fields[ $key ]['default'] ) ? $fields[ $key ]['default'] : null;
	}

	return get_theme_mod( 'haka_' . $key, $default );
}

/**
 * Apakah sebuah fitur aktif.
 *
 * @param string $key Kunci fitur.
 * @return bool
 */
function haka_enabled( $key ) {
	return (bool) haka_get_option( $key );
}
