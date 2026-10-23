<?php
/**
 * Poin penting dan daftar isi artikel.
 *
 * Dua blok bantu baca yang muncul di awal badan artikel:
 *
 * - Poin penting: ringkasan yang DITULIS REDAKSI lewat kotak di layar edit.
 *   Theme sengaja tidak meringkas sendiri. Ringkasan buatan mesin dari
 *   kalimat pertama sering keliru menangkap inti berita, dan untuk media
 *   berita kekeliruan semacam itu merugikan kredibilitas.
 * - Daftar isi: disusun dari subjudul H2/H3 yang benar-benar ada di artikel.
 *
 * Keduanya dipasang lewat filter the_content, bukan lewat template, supaya
 * ikut tampil di template mana pun dan tetap bisa dimatikan dari Customizer.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

const HAKA_SUMMARY_META = '_haka_summary';

/* ==========================================================================
   Kotak "Poin Penting" di layar edit
   ========================================================================== */

/**
 * Daftarkan kotak isian pada tipe konten yang relevan.
 */
function haka_summary_meta_box() {
	if ( ! haka_enabled( 'summary_enable' ) ) {
		return;
	}

	add_meta_box(
		'haka-summary',
		__( 'Poin Penting', 'haka' ),
		'haka_summary_meta_box_render',
		array( 'post' ),
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'haka_summary_meta_box' );

/**
 * Isi kotak: satu baris satu poin.
 *
 * @param WP_Post $post Artikel yang sedang disunting.
 */
function haka_summary_meta_box_render( $post ) {
	wp_nonce_field( 'haka_summary_save', 'haka_summary_nonce' );

	$nilai = get_post_meta( $post->ID, HAKA_SUMMARY_META, true );
	?>
	<p class="description">
		<?php esc_html_e( 'Tulis satu poin per baris, maksimal 6 poin. Kosongkan bila artikel ini tidak perlu ringkasan — blok tidak akan ditampilkan.', 'haka' ); ?>
	</p>
	<textarea
		name="haka_summary"
		rows="5"
		style="width:100%"
		placeholder="<?php esc_attr_e( 'Contoh: Tarif angkutan naik rata-rata 8 persen mulai pekan depan.', 'haka' ); ?>"
	><?php echo esc_textarea( is_string( $nilai ) ? $nilai : '' ); ?></textarea>
	<?php
}

/**
 * Simpan isian kotak.
 *
 * @param int $post_id ID artikel.
 */
function haka_summary_meta_box_save( $post_id ) {
	if ( ! isset( $_POST['haka_summary_nonce'] ) ) {
		return;
	}

	$nonce = sanitize_text_field( wp_unslash( $_POST['haka_summary_nonce'] ) );

	if ( ! wp_verify_nonce( $nonce, 'haka_summary_save' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$mentah = isset( $_POST['haka_summary'] ) ? wp_unslash( $_POST['haka_summary'] ) : '';
	$bersih = haka_summary_sanitize( $mentah );

	if ( '' === $bersih ) {
		delete_post_meta( $post_id, HAKA_SUMMARY_META );
	} else {
		update_post_meta( $post_id, HAKA_SUMMARY_META, $bersih );
	}
}
add_action( 'save_post_post', 'haka_summary_meta_box_save' );

/**
 * Bersihkan isian: teks polos, tanpa baris kosong, dibatasi 6 poin.
 *
 * @param string $mentah Isi textarea.
 * @return string
 */
function haka_summary_sanitize( $mentah ) {
	if ( ! is_string( $mentah ) ) {
		return '';
	}

	$baris  = preg_split( '/\r\n|\r|\n/', $mentah );
	$bersih = array();

	foreach ( $baris as $satu ) {
		$satu = sanitize_text_field( $satu );
		$satu = trim( $satu, " \t-•*" );

		if ( '' === $satu ) {
			continue;
		}

		$bersih[] = mb_substr( $satu, 0, 220 );

		if ( count( $bersih ) >= 6 ) {
			break;
		}
	}

	return implode( "\n", $bersih );
}

/**
 * Poin penting sebuah artikel sebagai larik.
 *
 * @param int $post_id ID artikel.
 * @return string[]
 */
function haka_summary_points( $post_id ) {
	$nilai = get_post_meta( $post_id, HAKA_SUMMARY_META, true );

	if ( ! is_string( $nilai ) || '' === trim( $nilai ) ) {
		return array();
	}

	return array_values( array_filter( array_map( 'trim', explode( "\n", $nilai ) ) ) );
}

/* ==========================================================================
   Daftar isi
   ========================================================================== */

/**
 * Beri id pada tiap subjudul dan kumpulkan daftarnya.
 *
 * Subjudul yang sudah punya id dibiarkan apa adanya supaya tautan lama dari
 * luar tidak putus.
 *
 * @param string $konten Isi artikel yang sudah difilter.
 * @return array{konten:string,daftar:array<int,array{level:int,id:string,teks:string}>}
 */
function haka_toc_scan( $konten ) {
	$daftar = array();
	$pakai  = array();

	$konten = preg_replace_callback(
		'/<h([23])(\s[^>]*)?>(.*?)<\/h\1>/is',
		function ( $cocok ) use ( &$daftar, &$pakai ) {
			$level = (int) $cocok[1];
			$atrib = isset( $cocok[2] ) ? $cocok[2] : '';
			$isi   = $cocok[3];
			$teks  = trim( wp_strip_all_tags( $isi ) );

			if ( '' === $teks ) {
				return $cocok[0];
			}

			if ( preg_match( '/\sid\s*=\s*(["\'])(.*?)\1/i', $atrib, $ada ) ) {
				$id = $ada[2];
			} else {
				$dasar = sanitize_title( $teks );

				if ( '' === $dasar ) {
					$dasar = 'bagian';
				}

				$id = $dasar;
				$n  = 2;

				while ( isset( $pakai[ $id ] ) ) {
					$id = $dasar . '-' . $n;
					$n++;
				}

				$atrib .= ' id="' . esc_attr( $id ) . '"';
			}

			$pakai[ $id ] = true;

			$daftar[] = array(
				'level' => $level,
				'id'    => $id,
				'teks'  => $teks,
			);

			return '<h' . $level . $atrib . '>' . $isi . '</h' . $level . '>';
		},
		$konten
	);

	// Banyak artikel hanya memakai H3 tanpa H2 sama sekali. Kalau kedalaman
	// diambil mentah dari angka heading, seluruh daftar jadi tampak menjorok
	// sebagai anak dari sesuatu yang tidak ada. Karena itu tingkat paling
	// dangkal yang benar-benar dipakai artikel dianggap sebagai tingkat satu.
	$paling_dangkal = 3;

	foreach ( $daftar as $item ) {
		$paling_dangkal = min( $paling_dangkal, $item['level'] );
	}

	foreach ( $daftar as $kunci => $item ) {
		$daftar[ $kunci ]['dalam'] = $item['level'] > $paling_dangkal ? 1 : 0;
	}

	return array(
		'konten' => $konten,
		'daftar' => $daftar,
	);
}

/* ==========================================================================
   Penyisipan ke badan artikel
   ========================================================================== */

/**
 * Tempelkan poin penting dan daftar isi di awal artikel.
 *
 * @param string $konten Isi artikel.
 * @return string
 */
function haka_article_extras( $konten ) {
	if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
		return $konten;
	}

	if ( post_password_required() ) {
		return $konten;
	}

	$post_id = get_the_ID();
	$blok    = '';

	// Poin penting hanya di halaman pertama artikel bersambung.
	$halaman = max( 1, (int) get_query_var( 'page' ) );

	if ( haka_enabled( 'summary_enable' ) && 1 === $halaman ) {
		$poin = haka_summary_points( $post_id );

		if ( ! empty( $poin ) ) {
			$judul = haka_get_option( 'summary_title', __( 'Poin Penting', 'haka' ) );

			$blok .= '<aside class="hk-summary" aria-label="' . esc_attr( $judul ) . '">';
			$blok .= '<h2 class="hk-summary__title">' . esc_html( $judul ) . '</h2><ul class="hk-summary__list">';

			foreach ( $poin as $satu ) {
				$blok .= '<li>' . esc_html( $satu ) . '</li>';
			}

			$blok .= '</ul></aside>';
		}
	}

	if ( haka_enabled( 'toc_enable' ) ) {
		$hasil  = haka_toc_scan( $konten );
		$konten = $hasil['konten'];
		$daftar = $hasil['daftar'];
		$minimal = max( 2, (int) haka_get_option( 'toc_min', 3 ) );

		if ( count( $daftar ) >= $minimal ) {
			$judul   = haka_get_option( 'toc_title', __( 'Daftar Isi', 'haka' ) );
			$tertutup = haka_enabled( 'toc_collapsed' ) ? '' : ' open';

			$blok .= '<details class="hk-toc"' . $tertutup . '>';
			$blok .= '<summary class="hk-toc__title">' . esc_html( $judul ) . '</summary>';
			$blok .= '<ol class="hk-toc__list">';

			// Sub-judul dibungkus <ol> tersendiri di dalam <li> induknya.
			// Kalau semuanya diratakan dalam satu daftar, penomoran tingkat
			// atas ikut terpakai oleh sub-judul dan hasilnya meloncat-loncat.
			$sub_terbuka = false;
			$li_terbuka  = false;

			foreach ( $daftar as $item ) {
				$tautan = '<a href="#' . esc_attr( $item['id'] ) . '">' . esc_html( $item['teks'] ) . '</a>';

				if ( empty( $item['dalam'] ) ) {
					if ( $sub_terbuka ) {
						$blok .= '</ol>';
						$sub_terbuka = false;
					}

					if ( $li_terbuka ) {
						$blok .= '</li>';
					}

					$blok      .= '<li class="hk-toc__item">' . $tautan;
					$li_terbuka = true;

					continue;
				}

				if ( ! $sub_terbuka ) {
					$blok       .= '<ol class="hk-toc__sub">';
					$sub_terbuka = true;
				}

				$blok .= '<li class="hk-toc__item hk-toc__item--sub">' . $tautan . '</li>';
			}

			if ( $sub_terbuka ) {
				$blok .= '</ol>';
			}

			if ( $li_terbuka ) {
				$blok .= '</li>';
			}

			$blok .= '</ol></details>';
		}
	}

	return $blok . $konten;
}
add_filter( 'the_content', 'haka_article_extras', 18 );
