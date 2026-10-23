<?php
/**
 * Import demo content Haka.
 *
 * Menyiapkan portal berita siap pakai: kategori, tag, artikel, gambar
 * (dibuat lokal dengan GD — tanpa unduhan eksternal dan tanpa aset berhak
 * cipta), halaman statis, menu, widget dan pengaturan Customizer.
 *
 * Seluruh objek yang dibuat dicatat pada opsi haka_demo_imported sehingga
 * dapat dihapus kembali tanpa menyentuh konten milik pengguna.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

define( 'HAKA_DEMO_OPTION', 'haka_demo_imported' );

/**
 * Daftarkan halaman admin di bawah menu Tampilan.
 */
function haka_demo_menu() {
	add_theme_page(
		__( 'Import Demo Haka', 'haka' ),
		__( 'Import Demo Haka', 'haka' ),
		'manage_options',
		'haka-demo',
		'haka_demo_page'
	);
}
add_action( 'admin_menu', 'haka_demo_menu' );

/**
 * Notice setelah theme diaktifkan.
 */
function haka_demo_activation_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$screen = get_current_screen();

	if ( ! $screen || ! in_array( $screen->id, array( 'themes', 'dashboard' ), true ) ) {
		return;
	}

	if ( get_option( HAKA_DEMO_OPTION ) ) {
		return;
	}

	printf(
		'<div class="notice notice-info is-dismissible"><p><strong>%1$s</strong> %2$s <a class="button button-primary" href="%3$s">%4$s</a></p></div>',
		esc_html__( 'Haka aktif.', 'haka' ),
		esc_html__( 'Ingin melihat tampilan portal secara utuh? Import demo content sekali klik.', 'haka' ),
		esc_url( admin_url( 'themes.php?page=haka-demo' ) ),
		esc_html__( 'Import Demo', 'haka' )
	);
}
add_action( 'admin_notices', 'haka_demo_activation_notice' );

/**
 * Render halaman import demo.
 */
function haka_demo_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Anda tidak memiliki izin untuk mengakses halaman ini.', 'haka' ) );
	}

	$imported = get_option( HAKA_DEMO_OPTION );
	$notice   = '';
	$status   = 'success';

	// Proses import.
	if ( isset( $_POST['haka_demo_action'] ) ) {
		check_admin_referer( 'haka_demo_action', 'haka_demo_nonce' );

		$action = sanitize_key( wp_unslash( $_POST['haka_demo_action'] ) );

		if ( 'import' === $action ) {
			$result = haka_demo_import();

			if ( is_wp_error( $result ) ) {
				$notice = $result->get_error_message();
				$status = 'error';
			} else {
				$notice = sprintf(
					/* translators: 1: jumlah artikel, 2: jumlah kategori, 3: jumlah halaman */
					__( 'Demo berhasil diimport: %1$d artikel, %2$d kategori, %3$d halaman.', 'haka' ),
					(int) $result['posts'],
					(int) $result['categories'],
					(int) $result['pages']
				);
			}

			$imported = get_option( HAKA_DEMO_OPTION );
		} elseif ( 'remove' === $action ) {
			$removed  = haka_demo_remove();
			$notice   = sprintf(
				/* translators: %d: jumlah objek dihapus */
				__( 'Demo content dihapus (%d objek).', 'haka' ),
				(int) $removed
			);
			$imported = false;
		}
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Import Demo Haka', 'haka' ); ?></h1>

		<?php haka_render_update_box(); ?>

		<?php if ( $notice ) : ?>
			<div class="notice notice-<?php echo esc_attr( $status ); ?>"><p><?php echo esc_html( $notice ); ?></p></div>
		<?php endif; ?>

		<div class="card" style="max-width:760px;padding:20px 24px;">
			<h2 style="margin-top:0;"><?php esc_html_e( 'Apa yang akan dibuat', 'haka' ); ?></h2>
			<ul style="list-style:disc;padding-left:20px;">
				<li><?php esc_html_e( '9 kategori berita: Nasional, Metropolitan, Politik, Ekonomi, Teknologi, Otomotif, Lifestyle, Sport, Dunia — plus kategori Video Berita.', 'haka' ); ?></li>
				<li>
					<?php
					require_once get_theme_file_path( 'inc/demo-articles.php' );
					printf(
						/* translators: %d: jumlah artikel */
						esc_html__( '%d artikel contoh lengkap dengan excerpt, tag, dan gambar utama, termasuk galeri, audio, video (YouTube, TikTok, Instagram, Facebook), Video Story, artikel disematkan, artikel berkata sandi, dan contoh komentar.', 'haka' ),
						count( haka_demo_articles() )
					);
					?>
				</li>
				<li><?php esc_html_e( 'Gambar dibuat langsung di server (gradien + inisial kategori), bukan diunduh dari internet, sehingga bebas hak cipta.', 'haka' ); ?></li>
				<li><?php esc_html_e( '5 halaman: Tentang Kami, Kontak, Kebijakan Privasi, Disclaimer, Pedoman Media Siber.', 'haka' ); ?></li>
				<li><?php esc_html_e( 'Menu Utama dan Menu Footer beserta penempatannya.', 'haka' ); ?></li>
				<li><?php esc_html_e( 'Widget sidebar (Terpopuler, Rekomendasi, Tag Terpopuler) dan widget footer.', 'haka' ); ?></li>
				<li><?php esc_html_e( 'Pengaturan Customizer: kategori hero, blok kategori, kategori video, trending tag dan tautan sosial media contoh.', 'haka' ); ?></li>
			</ul>

			<p class="description">
				<?php esc_html_e( 'Import bersifat aman: konten yang sudah ada tidak diubah atau dihapus, dan seluruh objek demo dicatat agar bisa dihapus kembali kapan saja.', 'haka' ); ?>
			</p>

			<?php if ( $imported ) : ?>
				<div class="notice notice-warning inline" style="margin:16px 0;">
					<p>
						<?php
						printf(
							/* translators: %s: tanggal import */
							esc_html__( 'Demo sudah pernah diimport pada %s. Import ulang akan menambah konten baru.', 'haka' ),
							esc_html( isset( $imported['date'] ) ? $imported['date'] : '-' )
						);
						?>
					</p>
				</div>
			<?php endif; ?>

			<form method="post" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
				<?php wp_nonce_field( 'haka_demo_action', 'haka_demo_nonce' ); ?>

				<button type="submit" name="haka_demo_action" value="import" class="button button-primary button-hero">
					<?php esc_html_e( 'Import Demo Content', 'haka' ); ?>
				</button>

				<?php if ( $imported ) : ?>
					<button type="submit"
						name="haka_demo_action"
						value="remove"
						class="button button-secondary"
						onclick="return confirm('<?php echo esc_js( __( 'Hapus seluruh konten demo yang dibuat importer? Konten lain tidak terpengaruh.', 'haka' ) ); ?>');">
						<?php esc_html_e( 'Hapus Demo Content', 'haka' ); ?>
					</button>
				<?php endif; ?>
			</form>
		</div>
	</div>
	<?php
}

/**
 * Definisi kategori demo.
 *
 * @return array
 */
function haka_demo_categories() {
	return array(
		'nasional'     => array( 'Nasional', 'Peristiwa dan kebijakan tingkat nasional.' ),
		'metropolitan' => array( 'Metropolitan', 'Kabar dari Jakarta dan kota besar sekitarnya.' ),
		'politik'      => array( 'Politik', 'Dinamika partai, parlemen dan pemerintahan.' ),
		'ekonomi'      => array( 'Ekonomi', 'Makro, keuangan, bisnis dan pasar.' ),
		'teknologi'    => array( 'Teknologi', 'Gawai, internet, startup dan riset.' ),
		'otomotif'     => array( 'Otomotif', 'Mobil, motor, dan industri kendaraan.' ),
		'lifestyle'    => array( 'Lifestyle', 'Gaya hidup, kesehatan, kuliner dan wisata.' ),
		'sport'        => array( 'Sport', 'Sepak bola, bulu tangkis dan olahraga lainnya.' ),
		'dunia'        => array( 'Dunia', 'Peristiwa internasional dan diplomasi.' ),
		'video-berita' => array( 'Video Berita', 'Liputan dalam format video.' ),
		'video-story'  => array( 'Video Story', 'Video vertikal pendek: TikTok, Reels, dan Shorts.' ),
	);
}

/**
 * Batasi ukuran turunan gambar saat import agar proses cepat.
 *
 * @param array $sizes Ukuran gambar.
 * @return array
 */
function haka_demo_limit_sizes( $sizes ) {
	$keep = array( 'thumbnail', 'medium', 'large', 'haka-hero', 'haka-card', 'haka-list', 'haka-thumb', 'haka-story' );

	return array_intersect_key( $sizes, array_flip( $keep ) );
}

/**
 * Menyalin gambar demo yang ikut dalam theme ke pustaka media.
 *
 * Gambar disertakan sebagai berkas, bukan digambar ulang saat impor, supaya
 * hasilnya sama persis dengan situs demo. Nama berkas diberi akhiran waktu
 * agar impor berulang tidak menimpa berkas yang sudah ada.
 *
 * @param string $berkas Nama berkas di assets/demo.
 * @param string $alt    Teks alternatif.
 * @param int    $parent Id tulisan pemilik.
 * @return int Id lampiran, atau 0 bila gagal.
 */
function haka_demo_attach_image( $berkas, $alt, $parent ) {
	if ( ! $berkas ) {
		return 0;
	}

	// Nama berkas berasal dari data theme, bukan dari masukan pengguna, tetapi
	// tetap dibersihkan agar tidak bisa keluar dari direktori demo.
	$aman = basename( (string) $berkas );
	$asal = get_theme_file_path( 'assets/demo/' . $aman );

	if ( ! $aman || ! file_exists( $asal ) ) {
		return 0;
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$isi = file_get_contents( $asal ); // phpcs:ignore WordPress.WP.AlternativeFunctions

	if ( false === $isi ) {
		return 0;
	}

	$ekstensi = strtolower( pathinfo( $aman, PATHINFO_EXTENSION ) );
	$mime     = array(
		'jpg' => 'image/jpeg',
		'png' => 'image/png',
		'wav' => 'audio/wav',
		'mp3' => 'audio/mpeg',
	);

	if ( empty( $mime[ $ekstensi ] ) ) {
		return 0;
	}

	$nama   = pathinfo( $aman, PATHINFO_FILENAME ) . '-' . time() . '.' . $ekstensi;
	$unggah = wp_upload_bits( $nama, null, $isi );

	if ( ! empty( $unggah['error'] ) ) {
		return 0;
	}

	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => $mime[ $ekstensi ],
			'post_title'     => $alt ? $alt : pathinfo( $aman, PATHINFO_FILENAME ),
			'post_status'    => 'inherit',
		),
		$unggah['file'],
		$parent
	);

	if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
		return 0;
	}

	wp_update_attachment_metadata(
		$attachment_id,
		wp_generate_attachment_metadata( $attachment_id, $unggah['file'] )
	);

	// Penanda yang sama dengan tulisan, supaya "Hapus demo" ikut membuang berkasnya.
	update_post_meta( $attachment_id, '_haka_demo', 1 );

	if ( $alt && 0 === strpos( $mime[ $ekstensi ], 'image/' ) ) {
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt );
	}

	return (int) $attachment_id;
}

/**
 * Blok galeri dari daftar ID lampiran.
 *
 * @param int[] $ids ID lampiran.
 * @return string Markup blok.
 */
function haka_demo_gallery_block( $ids ) {
	$ids = array_values( array_filter( array_map( 'absint', (array) $ids ) ) );

	if ( ! $ids ) {
		return '';
	}

	$items = '';

	foreach ( $ids as $id ) {
		$src = wp_get_attachment_image_url( $id, 'large' );
		$alt = (string) get_post_meta( $id, '_wp_attachment_image_alt', true );

		$items .= sprintf(
			'<!-- wp:image {"id":%1$d,"sizeSlug":"large","linkDestination":"none"} --><figure class="wp-block-image size-large"><img src="%2$s" alt="%3$s" class="wp-image-%1$d"/><figcaption class="wp-element-caption">%3$s</figcaption></figure><!-- /wp:image -->',
			$id,
			esc_url( $src ),
			esc_attr( $alt )
		) . "\n";
	}

	return '<!-- wp:gallery {"columns":3,"linkTo":"none","sizeSlug":"large"} --><figure class="wp-block-gallery has-nested-images columns-3 is-cropped">' . "\n" . $items . '</figure><!-- /wp:gallery -->';
}

/**
 * Sisipkan satu artikel demo beserta media, format, dan komentarnya.
 *
 * Kunci wajib: judul, isi, kategori, hari, jam. Kunci opsional: ringkasan,
 * tag, poin, gambar, alt, format, sticky, sandi, galeri, gambar_tambahan,
 * audio, komentar. Isi boleh memakai penanda {{gambar_utama}},
 * {{gambar_utama_id}}, {{gambar:nama}}, {{gambar_id:nama}}, {{galeri}},
 * dan {{audio}} yang diganti dengan URL/ID media setelah diunggah.
 *
 * @param array $article   Data artikel.
 * @param array $cat_ids   Peta slug kategori => ID.
 * @param int   $author_id ID penulis.
 * @param array $log       Catatan objek yang dibuat (diubah di tempat).
 * @return int ID post, 0 bila gagal.
 */
function haka_demo_insert_article( $article, $cat_ids, $author_id, &$log ) {
	// Kategori boleh lebih dari satu; yang pertama dianggap kategori utama.
	$slugs = array_values( (array) $article['kategori'] );
	$slug  = isset( $slugs[0] ) ? $slugs[0] : '';

	if ( empty( $cat_ids[ $slug ] ) ) {
		return 0;
	}

	$kategori_ids = array();

	foreach ( $slugs as $slug_kategori ) {
		if ( ! empty( $cat_ids[ $slug_kategori ] ) ) {
			$kategori_ids[] = (int) $cat_ids[ $slug_kategori ];
		}
	}

	// Tanggal dihitung mundur dari hari pemasangan, supaya isi demo tidak
	// terlihat basi pada pemasangan yang dilakukan jauh di kemudian hari.
	$sekarang = strtotime( current_time( 'mysql' ) );
	$tanggal  = gmdate(
		'Y-m-d ' . $article['jam'] . ':00',
		$sekarang - ( (int) $article['hari'] * DAY_IN_SECONDS )
	);

	// Jam artikel "hari ini" bisa jatuh setelah jam pemasangan; kalau
	// dibiarkan, WordPress menjadwalkannya alih-alih menerbitkan.
	if ( strtotime( $tanggal ) > $sekarang ) {
		$tanggal = gmdate( 'Y-m-d H:i:s', $sekarang - HOUR_IN_SECONDS );
	}

	$meta = array(
		'_haka_demo'  => 1,
		'_haka_views' => wp_rand( 40, 4200 ),
	);

	// Poin penting hanya ada pada sebagian artikel demo, sama seperti di
	// situs demo. Yang kosong tidak diberi meta sama sekali, supaya blok
	// ringkasannya tidak muncul sebagai kotak kosong.
	if ( ! empty( $article['poin'] ) ) {
		$meta[ HAKA_SUMMARY_META ] = haka_summary_sanitize( $article['poin'] );
	}

	$args = array(
		'post_title'    => $article['judul'],
		'post_content'  => $article['isi'],
		'post_excerpt'  => isset( $article['ringkasan'] ) ? $article['ringkasan'] : '',
		'post_status'   => 'publish',
		'post_author'   => $author_id,
		'post_type'     => 'post',
		'post_category' => $kategori_ids,
		'post_date'     => $tanggal,
		'meta_input'    => $meta,
	);

	if ( ! empty( $article['sandi'] ) ) {
		$args['post_password'] = (string) $article['sandi'];
	}

	$post_id = wp_insert_post( $args, true );

	if ( is_wp_error( $post_id ) ) {
		return 0;
	}

	$post_id        = (int) $post_id;
	$log['posts'][] = $post_id;

	if ( ! empty( $article['tag'] ) ) {
		wp_set_post_terms( $post_id, $article['tag'], 'post_tag', false );
	}

	$format = isset( $article['format'] ) ? (string) $article['format'] : '';

	if ( in_array( $slug, array( 'video-berita', 'video-story' ), true ) ) {
		$format = 'video';
	}

	if ( $format ) {
		set_post_format( $post_id, $format );
	}

	if ( 'video' === $format ) {
		update_post_meta( $post_id, '_haka_has_video', 1 );
	}

	if ( ! empty( $article['sticky'] ) ) {
		stick_post( $post_id );
	}

	/* ------------------------------------------------------------ Media */
	$ganti = array();

	$attachment_id = haka_demo_attach_image( isset( $article['gambar'] ) ? $article['gambar'] : '', isset( $article['alt'] ) ? $article['alt'] : '', $post_id );

	if ( $attachment_id ) {
		set_post_thumbnail( $post_id, $attachment_id );
		$log['attachments'][]       = $attachment_id;
		$ganti['{{gambar_utama}}']    = wp_get_attachment_image_url( $attachment_id, 'full' );
		$ganti['{{gambar_utama_id}}'] = $attachment_id;
	}

	if ( ! empty( $article['gambar_tambahan'] ) ) {
		foreach ( (array) $article['gambar_tambahan'] as $nama => $berkas ) {
			$id = haka_demo_attach_image( $berkas, $article['judul'] . ' – ' . $nama, $post_id );

			if ( $id ) {
				$log['attachments'][]                  = $id;
				$ganti[ '{{gambar:' . $nama . '}}' ]    = wp_get_attachment_image_url( $id, 'full' );
				$ganti[ '{{gambar_id:' . $nama . '}}' ] = $id;
			}
		}
	}

	if ( ! empty( $article['galeri'] ) ) {
		$ids = array();

		foreach ( (array) $article['galeri'] as $keterangan => $berkas ) {
			$id = haka_demo_attach_image( $berkas, is_string( $keterangan ) ? $keterangan : '', $post_id );

			if ( $id ) {
				$ids[]                = $id;
				$log['attachments'][] = $id;
			}
		}

		$ganti['{{galeri}}'] = haka_demo_gallery_block( $ids );
	}

	if ( ! empty( $article['audio'] ) ) {
		$id = haka_demo_attach_image( $article['audio'], $article['judul'], $post_id );

		if ( $id ) {
			$log['attachments'][] = $id;
			$ganti['{{audio}}']   = wp_get_attachment_url( $id );
		}
	}

	if ( $ganti ) {
		$isi = strtr( (string) $article['isi'], $ganti );

		// Penanda yang medianya gagal diunggah dibuang, jangan sampai tercetak.
		$isi = preg_replace( '/\{\{[a-z_]+(:[a-z0-9_-]+)?\}\}/', '', $isi );

		wp_update_post(
			array(
				'ID'           => $post_id,
				'post_content' => $isi,
			)
		);
	}

	/* --------------------------------------------------------- Komentar */
	if ( ! empty( $article['komentar'] ) ) {
		$urut   = 0;
		$induk  = array();
		$waktu  = strtotime( $tanggal );

		foreach ( (array) $article['komentar'] as $komentar ) {
			$urut++;
			$parent = ! empty( $komentar['balas'] ) && isset( $induk[ $komentar['balas'] ] ) ? $induk[ $komentar['balas'] ] : 0;

			$comment_id = wp_insert_comment(
				array(
					'comment_post_ID'      => $post_id,
					'comment_author'       => $komentar['nama'],
					'comment_author_email' => sanitize_title( $komentar['nama'] ) . '@example.com',
					'comment_content'      => $komentar['isi'],
					'comment_parent'       => $parent,
					'comment_approved'     => 1,
					'comment_date'         => gmdate( 'Y-m-d H:i:s', $waktu + $urut * 1700 ),
					'comment_meta'         => array( '_haka_demo' => 1 ),
				)
			);

			if ( $comment_id ) {
				$induk[ $urut ]     = (int) $comment_id;
				$log['comments'][] = (int) $comment_id;
			}
		}

		wp_update_comment_count( $post_id );
	}

	// Tanggal ubah disamakan dengan tanggal terbit. Tanpa ini artikel demo
	// lama tampil berlabel "Diperbarui" karena baru saja ditulis ke basis data.
	global $wpdb;

	$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->posts,
		array(
			'post_modified'     => $tanggal,
			'post_modified_gmt' => get_gmt_from_date( $tanggal ),
		),
		array( 'ID' => $post_id ),
		array( '%s', '%s' ),
		array( '%d' )
	);
	clean_post_cache( $post_id );

	return $post_id;
}

/**
 * Jalankan import demo.
 *
 * @return array|WP_Error
 */
function haka_demo_import() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return new WP_Error( 'haka_forbidden', __( 'Izin tidak mencukupi.', 'haka' ) );
	}

	@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	wp_raise_memory_limit( 'image' );

	add_filter( 'intermediate_image_sizes_advanced', 'haka_demo_limit_sizes' );

	$log = array(
		'date'        => current_time( 'mysql' ),
		'posts'       => array(),
		'pages'       => array(),
		'terms'       => array(),
		'tags'        => array(),
		'attachments' => array(),
		'comments'    => array(),
		'menus'       => array(),
	);

	$author_id = get_current_user_id();
	$cat_ids   = array();

	/* ------------------------------------------------------------ Kategori */
	foreach ( haka_demo_categories() as $slug => $data ) {
		$existing = get_term_by( 'slug', $slug, 'category' );

		if ( $existing instanceof WP_Term ) {
			$cat_ids[ $slug ] = (int) $existing->term_id;
			continue;
		}

		$created = wp_insert_term(
			$data[0],
			'category',
			array(
				'slug'        => $slug,
				'description' => $data[1],
			)
		);

		if ( ! is_wp_error( $created ) ) {
			$cat_ids[ $slug ] = (int) $created['term_id'];
			$log['terms'][]   = (int) $created['term_id'];
		}
	}

	/* ----------------------------------------------------------- Tag & post */

	/*
	 * Isi artikel diambil dari inc/demo-articles.php, yang dihasilkan langsung
	 * dari situs demo. Jadi hasil impor sama persis dengan yang terlihat di
	 * sana, bukan teks contoh yang dibuat acak.
	 */
	require_once get_theme_file_path( 'inc/demo-articles.php' );

	$post_ids = array();

	foreach ( haka_demo_articles() as $article ) {
		$post_id = haka_demo_insert_article( $article, $cat_ids, $author_id, $log );

		if ( $post_id ) {
			$post_ids[] = $post_id;
		}
	}

	/* -------------------------------------------------------------- Halaman */
	$pages = array(
		'tentang-kami'        => array(
			'Tentang Kami',
			'<p>Haka adalah portal berita contoh yang dibangun dengan theme Haka untuk WordPress. Halaman ini menjelaskan identitas redaksi, cakupan liputan, dan cara menghubungi tim.</p><h2>Redaksi</h2><p>Susun struktur redaksi, alamat kantor, dan kontak resmi pada halaman ini.</p>',
		),
		'kontak'              => array(
			'Kontak',
			// Isi halaman sengaja tidak mengulang alamat, email dan telepon:
			// ketiganya sudah tampil di kolom samping dari Customizer.
			'<h2>Redaksi</h2>'
			. '<p>Untuk informasi berita, undangan liputan, atau data pendukung, hubungi redaksi lewat kanal di samping. Sertakan nama, institusi, dan nomor yang bisa dihubungi agar tim kami dapat menindaklanjuti.</p>'
			. '<h2>Koreksi dan Hak Jawab</h2>'
			. '<p>Jika menemukan kekeliruan pada artikel kami, sampaikan tautan artikel beserta bagian yang dianggap keliru dan data pembandingnya. Pengajuan hak jawab mengikuti Pedoman Media Siber dan ditangani sesuai tenggat yang tercantum di halaman tersebut.</p>'
			. '<h2>Iklan dan Kerja Sama</h2>'
			. '<p>Permintaan pemasangan iklan, kerja sama konten, maupun sindikasi dapat dikirim ke alamat email yang sama dengan subjek yang menyebutkan jenis kerja samanya.</p>',
			'template-contact.php',
		),
		'kebijakan-privasi'   => array(
			'Kebijakan Privasi',
			'<p>Halaman ini menjelaskan data apa yang dikumpulkan situs, bagaimana data dipakai, dan hak pembaca atas datanya. Sesuaikan isinya dengan praktik yang benar-benar dijalankan.</p><h2>Data yang Dikumpulkan</h2><p>Uraikan jenis data, dasar hukum, dan masa simpan.</p>',
		),
		'disclaimer'          => array(
			'Disclaimer',
			'<p>Seluruh artikel pada instalasi demo ini adalah contoh dan tidak merepresentasikan peristiwa nyata. Ganti isi halaman ini dengan penafian resmi milik media Anda.</p>',
		),
		'indeks'              => array(
			'Indeks Berita',
			'<p>Seluruh berita Haka tersusun menurut waktu terbit. Gunakan penyaring di bawah untuk membatasi menurut kategori maupun tanggal.</p>',
			'template-indeks.php',
		),
		'pedoman-media-siber' => array(
			'Pedoman Media Siber',
			'<p>Cantumkan pedoman media siber yang menjadi acuan redaksi, termasuk mekanisme verifikasi, ralat, dan hak jawab pembaca.</p><h2>Hak Jawab</h2><p>Jelaskan prosedur pengajuan hak jawab dan tenggat penanganannya.</p>',
		),
	);

	$page_ids = array();

	foreach ( $pages as $slug => $data ) {
		$existing = get_page_by_path( $slug );

		if ( $existing instanceof WP_Post ) {
			$page_ids[ $slug ] = (int) $existing->ID;
			continue;
		}

		$page_args = array(
			'post_title'   => $data[0],
			'post_name'    => $slug,
			'post_content' => $data[1],
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_author'  => $author_id,
			'meta_input'   => array( '_haka_demo' => 1 ),
		);

		// Halaman tertentu memakai template khusus.
		if ( ! empty( $data[2] ) ) {
			$page_args['meta_input']['_wp_page_template'] = $data[2];
		}

		$page_id = wp_insert_post( $page_args, true );

		if ( ! is_wp_error( $page_id ) ) {
			$page_ids[ $slug ] = (int) $page_id;
			$log['pages'][]    = (int) $page_id;
		}
	}

	/* ----------------------------------------------------------------- Menu */
	$menu_ids = haka_demo_build_menus( $cat_ids, $page_ids );
	$log['menus'] = $menu_ids;

	/* --------------------------------------------------------------- Widget */
	haka_demo_build_widgets();

	/* ---------------------------------------------------------- Customizer */
	haka_demo_apply_settings( $cat_ids );

	/* ------------------------------------------------------ Opsi situs dasar */
	if ( 'posts' !== get_option( 'show_on_front' ) ) {
		update_option( 'show_on_front', 'posts' );
	}

	remove_filter( 'intermediate_image_sizes_advanced', 'haka_demo_limit_sizes' );

	// Gabungkan dengan catatan import sebelumnya bila ada.
	$previous = get_option( HAKA_DEMO_OPTION );

	if ( is_array( $previous ) ) {
		foreach ( array( 'posts', 'pages', 'terms', 'attachments', 'menus' ) as $key ) {
			$log[ $key ] = array_values( array_unique( array_merge( (array) ( $previous[ $key ] ?? array() ), $log[ $key ] ) ) );
		}
	}

	update_option( HAKA_DEMO_OPTION, $log, false );

	delete_transient( 'haka_popular_5_30' );

	return array(
		'posts'      => count( $post_ids ),
		'categories' => count( $cat_ids ),
		'pages'      => count( $page_ids ),
	);
}

/**
 * Buat menu utama dan menu footer, lalu tempatkan pada lokasi theme.
 *
 * @param array $cat_ids  Peta slug kategori ke term ID.
 * @param array $page_ids Peta slug halaman ke post ID.
 * @return array ID menu yang dibuat.
 */
function haka_demo_build_menus( $cat_ids, $page_ids ) {
	$created   = array();
	$locations = get_theme_mod( 'nav_menu_locations', array() );

	/* Menu utama */
	$primary = wp_get_nav_menu_object( 'Haka Utama' );

	if ( ! $primary ) {
		$primary_id = wp_create_nav_menu( 'Haka Utama' );

		if ( ! is_wp_error( $primary_id ) ) {
			$created[] = (int) $primary_id;

			wp_update_nav_menu_item(
				$primary_id,
				0,
				array(
					'menu-item-title'  => __( 'Home', 'haka' ),
					'menu-item-url'    => home_url( '/' ),
					'menu-item-status' => 'publish',
					'menu-item-type'   => 'custom',
				)
			);

			$order = array( 'nasional', 'metropolitan', 'politik', 'ekonomi', 'teknologi', 'otomotif', 'lifestyle', 'sport', 'dunia' );

			foreach ( $order as $slug ) {
				if ( empty( $cat_ids[ $slug ] ) ) {
					continue;
				}

				wp_update_nav_menu_item(
					$primary_id,
					0,
					array(
						'menu-item-object'    => 'category',
						'menu-item-object-id' => $cat_ids[ $slug ],
						'menu-item-type'      => 'taxonomy',
						'menu-item-status'    => 'publish',
					)
				);
			}

			$locations['primary'] = (int) $primary_id;
		}
	} else {
		$locations['primary'] = (int) $primary->term_id;
	}

	/* Menu footer */
	$footer = wp_get_nav_menu_object( 'Haka Footer' );

	if ( ! $footer ) {
		$footer_id = wp_create_nav_menu( 'Haka Footer' );

		if ( ! is_wp_error( $footer_id ) ) {
			$created[] = (int) $footer_id;

			foreach ( array( 'tentang-kami', 'kontak', 'kebijakan-privasi', 'disclaimer', 'pedoman-media-siber' ) as $slug ) {
				if ( empty( $page_ids[ $slug ] ) ) {
					continue;
				}

				wp_update_nav_menu_item(
					$footer_id,
					0,
					array(
						'menu-item-object'    => 'page',
						'menu-item-object-id' => $page_ids[ $slug ],
						'menu-item-type'      => 'post_type',
						'menu-item-status'    => 'publish',
					)
				);
			}

			$locations['footer'] = (int) $footer_id;
		}
	} else {
		$locations['footer'] = (int) $footer->term_id;
	}

	set_theme_mod( 'nav_menu_locations', $locations );

	return $created;
}

/**
 * Isi sidebar dan footer dengan widget bawaan theme.
 */
function haka_demo_build_widgets() {
	$sidebars = get_option( 'sidebars_widgets', array() );

	// Widget hasil pemetaan dari theme sebelumnya dipindah ke "tidak aktif"
	// agar susunan demo terlihat utuh, bukan dihapus.
	if ( ! empty( $sidebars['sidebar-primary'] ) ) {
		$sidebars['wp_inactive_widgets'] = array_merge(
			(array) ( $sidebars['wp_inactive_widgets'] ?? array() ),
			(array) $sidebars['sidebar-primary']
		);
		$sidebars['sidebar-primary'] = array();
	}

	// Sidebar utama: Terpopuler, Rekomendasi, Tag Terpopuler.
	if ( empty( $sidebars['sidebar-primary'] ) ) {
		$popular = get_option( 'widget_haka_popular', array() );
		$recent  = get_option( 'widget_haka_recent', array() );
		$tags    = get_option( 'widget_haka_tags', array() );

		$popular[2] = array(
			'title'  => __( 'Terpopuler', 'haka' ),
			'number' => 5,
		);
		$recent[2]  = array(
			'title'    => __( 'Rekomendasi', 'haka' ),
			'number'   => 5,
			'category' => 0,
		);
		$tags[2]    = array(
			'title'  => __( 'Tag Terpopuler', 'haka' ),
			'number' => 18,
		);

		foreach ( array( 'widget_haka_popular' => $popular, 'widget_haka_recent' => $recent, 'widget_haka_tags' => $tags ) as $option => $value ) {
			$value['_multiwidget'] = 1;
			update_option( $option, $value );
		}

		$sidebars['sidebar-primary'] = array( 'haka_popular-2', 'haka_recent-2', 'haka_tags-2' );
	}

	// Footer kolom 1: kategori. Kolom 2: halaman. Kolom 3: pencarian.
	if ( empty( $sidebars['footer-1'] ) ) {
		$categories = get_option( 'widget_categories', array() );
		$categories[2] = array(
			'title'        => __( 'Kategori', 'haka' ),
			'count'        => 0,
			'hierarchical' => 0,
			'dropdown'     => 0,
		);
		$categories['_multiwidget'] = 1;
		update_option( 'widget_categories', $categories );

		$sidebars['footer-1'] = array( 'categories-2' );
	}

	if ( empty( $sidebars['footer-2'] ) ) {
		$pages = get_option( 'widget_pages', array() );
		$pages[2] = array(
			'title'    => __( 'Informasi', 'haka' ),
			'sortby'   => 'menu_order',
			'exclude'  => '',
		);
		$pages['_multiwidget'] = 1;
		update_option( 'widget_pages', $pages );

		$sidebars['footer-2'] = array( 'pages-2' );
	}

	// Kotak cari sengaja tidak dipasang di footer: tombol pencarian sudah ada
	// di header yang menempel saat digulir, jadi kolom footer lebih berguna
	// untuk tautan.

	update_option( 'sidebars_widgets', $sidebars );
}

/**
 * Materi iklan contoh.
 *
 * Sengaja berupa kotak berlabel, bukan tiruan iklan sungguhan: tujuannya
 * memperlihatkan posisi dan ukuran slot, lalu diganti kode iklan asli.
 *
 * @param string $label   Nama posisi.
 * @param string $size    Keterangan ukuran.
 * @param int    $ratio_w Lebar rasio.
 * @param int    $ratio_h Tinggi rasio.
 * @return string
 */
function haka_demo_ad_creative( $label, $size, $ratio_w, $ratio_h ) {
	// max-width dibatasi lebar nominalnya. Tanpa itu banner 336x280 ikut
	// melebar selebar kolom dan tingginya membengkak jauh dari ukuran asli.
	return sprintf(
		'<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;'
		. 'aspect-ratio:%1$d/%2$d;width:100%%;max-width:%1$dpx;margin-inline:auto;'
		. 'padding:12px;border:2px dashed #c9ced4;border-radius:8px;'
		. 'background:repeating-linear-gradient(45deg,#f7f8f9,#f7f8f9 10px,#f1f3f5 10px,#f1f3f5 20px);'
		. 'color:#6b7280;font-family:inherit;text-align:center;">'
		. '<strong style="font-size:13px;letter-spacing:.04em;text-transform:uppercase;">%3$s</strong>'
		. '<span style="font-size:12px;">%4$s</span>'
		. '<span style="font-size:11px;opacity:.75;">%5$s</span>'
		. '</div>',
		(int) $ratio_w,
		(int) $ratio_h,
		esc_html( $label ),
		esc_html( $size ),
		esc_html__( 'Ganti dengan kode iklan Anda di Customizer', 'haka' )
	);
}

/**
 * Seluruh slot iklan contoh.
 *
 * @return array
 */
function haka_demo_ad_slots() {
	return array(
		'ad_header'         => haka_demo_ad_creative( __( 'Bawah Header', 'haka' ), '970 x 90 — Leaderboard', 970, 90 ),
		'ad_before_content' => haka_demo_ad_creative( __( 'Sebelum Artikel', 'haka' ), '728 x 90 — Banner', 728, 90 ),
		'ad_after_content'  => haka_demo_ad_creative( __( 'Setelah Artikel', 'haka' ), '336 x 280 — Large Rectangle', 336, 280 ),
		'ad_sidebar'        => haka_demo_ad_creative( __( 'Sidebar', 'haka' ), '300 x 250 — Medium Rectangle', 300, 250 ),
		'ad_footer'         => haka_demo_ad_creative( __( 'Atas Footer', 'haka' ), '970 x 250 — Billboard', 970, 250 ),
	);
}

/**
 * Terapkan pengaturan Customizer untuk demo.
 *
 * @param array $cat_ids Peta slug kategori ke term ID.
 */
function haka_demo_apply_settings( $cat_ids ) {
	$blocks = array();

	foreach ( array( 'politik', 'ekonomi', 'teknologi', 'otomotif' ) as $slug ) {
		if ( ! empty( $cat_ids[ $slug ] ) ) {
			$blocks[] = (int) $cat_ids[ $slug ];
		}
	}

	$mods = array(
		'haka_ads_enable'       => true,
		'haka_ad_awareness'     => true,
		'haka_hero_carousel'    => true,
		'haka_hero_source'      => 'category',
		'haka_hero_count'       => 5,
		'haka_hero_autoplay'    => true,
		'haka_hero_interval'    => 6,
		'haka_breaking_enable'  => true,
		'haka_breaking_count'   => 6,
		'haka_trending_enable'  => true,
		'haka_trending_count'   => 8,
		'haka_video_enable'     => true,
		'haka_block_categories' => implode( ',', $blocks ),
		'haka_contact_intro'    => __( 'Punya masukan, koreksi, atau ingin mengajukan hak jawab? Hubungi redaksi Haka lewat kanal di bawah ini.', 'haka' ),
		'haka_contact_address'  => __( "Gedung Redaksi Haka\nJalan Contoh Nomor 12, Lantai 4\nJakarta Pusat 10110", 'haka' ),
		'haka_contact_email'    => 'redaksi@example.com',
		'haka_contact_phone'    => '(021) 5000 1234',
		'haka_footer_about'     => __( 'Haka adalah portal berita contoh berbasis WordPress. Menyajikan kabar nasional, ekonomi, teknologi, olahraga, dan gaya hidup dalam tampilan yang ringan dan cepat.', 'haka' ),
		'haka_social_facebook'  => 'https://facebook.com/',
		'haka_social_instagram' => 'https://instagram.com/',
		'haka_social_x'         => 'https://x.com/',
		'haka_social_youtube'   => 'https://youtube.com/',
	);

	if ( ! empty( $cat_ids['nasional'] ) ) {
		$mods['haka_featured_category'] = (int) $cat_ids['nasional'];
	}

	if ( ! empty( $cat_ids['video-berita'] ) ) {
		$mods['haka_video_category'] = (int) $cat_ids['video-berita'];
	}

	if ( ! empty( $cat_ids['video-story'] ) ) {
		$mods['haka_story_enable']   = true;
		$mods['haka_story_category'] = (int) $cat_ids['video-story'];
	}

	foreach ( haka_demo_ad_slots() as $slot => $creative ) {
		$mods[ 'haka_' . $slot ] = $creative;
	}

	foreach ( $mods as $key => $value ) {
		set_theme_mod( $key, $value );
	}
}

/**
 * Hapus seluruh konten demo yang tercatat.
 *
 * @return int Jumlah objek yang dihapus.
 */
function haka_demo_remove() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return 0;
	}

	$log = get_option( HAKA_DEMO_OPTION );

	if ( ! is_array( $log ) ) {
		return 0;
	}

	$removed = 0;

	foreach ( array( 'posts', 'pages' ) as $key ) {
		foreach ( (array) ( $log[ $key ] ?? array() ) as $post_id ) {
			if ( get_post_meta( (int) $post_id, '_haka_demo', true ) && wp_delete_post( (int) $post_id, true ) ) {
				$removed++;
			}
		}
	}

	foreach ( (array) ( $log['comments'] ?? array() ) as $comment_id ) {
		if ( get_comment_meta( (int) $comment_id, '_haka_demo', true ) && wp_delete_comment( (int) $comment_id, true ) ) {
			$removed++;
		}
	}

	foreach ( (array) ( $log['attachments'] ?? array() ) as $attachment_id ) {
		if ( get_post_meta( (int) $attachment_id, '_haka_demo', true ) && wp_delete_attachment( (int) $attachment_id, true ) ) {
			$removed++;
		}
	}

	foreach ( (array) ( $log['terms'] ?? array() ) as $term_id ) {
		if ( wp_delete_term( (int) $term_id, 'category' ) ) {
			$removed++;
		}
	}

	foreach ( (array) ( $log['menus'] ?? array() ) as $menu_id ) {
		if ( wp_delete_nav_menu( (int) $menu_id ) ) {
			$removed++;
		}
	}

	delete_option( HAKA_DEMO_OPTION );

	return $removed;
}
