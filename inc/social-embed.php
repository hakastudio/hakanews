<?php
/**
 * Embed media sosial.
 *
 * WordPress sudah menangani YouTube, TikTok, Vimeo, Spotify, Reddit, dan
 * SoundCloud lewat daftar penyedia oEmbed bawaannya. Berkas ini hanya
 * menambal tiga lubang yang tersisa:
 *
 * - x.com   : daftar bawaan masih menyebut twitter.com saja, sehingga alamat
 *             x.com — yang sekarang disalin semua orang — tidak dikenali.
 *             Endpoint publish.x.com sendiri terbuka dan tidak butuh kunci.
 * - Instagram: tidak ada penyedia oEmbed yang bisa dipakai tanpa token
 *             aplikasi Meta. Karena itu markup sematannya disusun sendiri
 *             dari alamatnya, memakai embed.js resmi Instagram.
 * - Facebook : disematkan lewat social plugin resmi, juga tanpa token.
 *
 * Threads dan Bluesky sengaja tidak disertakan. Threads mensyaratkan token
 * aplikasi, dan oEmbed Bluesky hanya menerima alamat berbentuk DID, bukan
 * alamat yang biasa disalin orang dari peramban.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

/* ==========================================================================
   X (Twitter)
   ========================================================================== */

/**
 * Daftarkan pola alamat x.com ke endpoint oEmbed resmi.
 */
function haka_embed_register_x() {
	if ( ! haka_enabled( 'embed_x' ) ) {
		return;
	}

	$endpoint = 'https://publish.x.com/oembed';

	$pola = array(
		'#https?://(www\.)?x\.com/\w{1,15}/status(es)?/.*#i',
		'#https?://(www\.)?x\.com/\w{1,15}$#i',
		'#https?://(www\.)?x\.com/\w{1,15}/likes$#i',
		'#https?://(www\.)?x\.com/\w{1,15}/lists/.*#i',
		'#https?://(www\.)?x\.com/i/moments/.*#i',
	);

	foreach ( $pola as $satu ) {
		wp_oembed_add_provider( $satu, $endpoint, true );
	}
}
add_action( 'init', 'haka_embed_register_x' );

/* ==========================================================================
   Instagram
   ========================================================================== */

/**
 * Daftarkan penangan sematan Instagram.
 */
function haka_embed_register_instagram() {
	if ( ! haka_enabled( 'embed_instagram' ) ) {
		return;
	}

	wp_embed_register_handler(
		'haka-instagram',
		'#https?://(www\.)?instagram\.com/(p|reel|reels|tv)/([A-Za-z0-9_-]+)#i',
		'haka_embed_instagram'
	);
}
add_action( 'init', 'haka_embed_register_instagram' );

/**
 * Susun markup sematan Instagram.
 *
 * @param array  $cocok Hasil pencocokan pola.
 * @param array  $atrib Atribut embed.
 * @param string $url   Alamat asli.
 * @return string
 */
function haka_embed_instagram( $cocok, $atrib, $url ) {
	// Alamat dinormalkan ulang dari hasil pencocokan, jadi $atrib dan $url
	// mentah tidak dipakai.
	unset( $atrib, $url );

	$tipe = strtolower( $cocok[2] );
	$tipe = ( 'reels' === $tipe ) ? 'reel' : $tipe;
	$kode = $cocok[3];

	$permalink = 'https://www.instagram.com/' . $tipe . '/' . $kode . '/';

	haka_embed_perlu_skrip( 'instagram' );

	$keluar  = '<div class="hk-embed hk-embed--instagram">';
	$keluar .= '<blockquote class="instagram-media" data-instgrm-permalink="' . esc_url( $permalink ) . '" data-instgrm-version="14">';
	$keluar .= '<a href="' . esc_url( $permalink ) . '" rel="noopener nofollow">' . esc_html__( 'Lihat kiriman ini di Instagram', 'haka' ) . '</a>';
	$keluar .= '</blockquote></div>';

	return $keluar;
}

/* ==========================================================================
   Facebook
   ========================================================================== */

/**
 * Daftarkan penangan sematan Facebook.
 */
function haka_embed_register_facebook() {
	if ( ! haka_enabled( 'embed_facebook' ) ) {
		return;
	}

	wp_embed_register_handler(
		'haka-facebook-video',
		'#https?://(www\.|web\.|m\.)?facebook\.com/(.+/videos?/.+|reel/\d+|watch/?\?v=\d+|share/[rv]/[A-Za-z0-9_-]+)#i',
		'haka_embed_facebook_video'
	);

	wp_embed_register_handler(
		'haka-facebook-post',
		'#https?://(www\.|web\.|m\.)?facebook\.com/(permalink\.php|story\.php|[^/]+/(posts|photos)/).+#i',
		'haka_embed_facebook_post'
	);
}
add_action( 'init', 'haka_embed_register_facebook' );

/**
 * Markup social plugin Facebook.
 *
 * Dipakai bentuk div + SDK, bukan iframe langsung. Tinggi kiriman Facebook
 * berbeda-beda dan iframe tidak bisa menyesuaikan diri, sehingga kiriman
 * pendek menyisakan ruang kosong yang lebar. SDK resmi mengukur sendiri.
 *
 * @param string $jenis Jenis plugin: post atau video.
 * @param string $url   Alamat kiriman.
 * @return string
 */
function haka_embed_facebook_markup( $jenis, $url ) {
	haka_embed_perlu_skrip( 'facebook' );

	$keluar  = '<div class="hk-embed hk-embed--facebook">';
	$keluar .= '<div class="fb-' . esc_attr( $jenis ) . '" data-href="' . esc_url( $url ) . '" data-width="560" data-show-text="true">';
	$keluar .= '<blockquote cite="' . esc_url( $url ) . '" class="fb-xfbml-parse-ignore">';
	$keluar .= '<a href="' . esc_url( $url ) . '" rel="noopener nofollow">' . esc_html__( 'Lihat kiriman ini di Facebook', 'haka' ) . '</a>';
	$keluar .= '</blockquote></div></div>';

	return $keluar;
}

/**
 * Penangan video Facebook.
 *
 * @param array  $cocok Hasil pencocokan pola.
 * @param array  $atrib Atribut embed.
 * @param string $url   Alamat asli.
 * @return string
 */
function haka_embed_facebook_video( $cocok, $atrib, $url ) {
	unset( $cocok, $atrib );

	return haka_embed_facebook_markup( 'video', $url );
}

/**
 * Penangan kiriman Facebook.
 *
 * @param array  $cocok Hasil pencocokan pola.
 * @param array  $atrib Atribut embed.
 * @param string $url   Alamat asli.
 * @return string
 */
function haka_embed_facebook_post( $cocok, $atrib, $url ) {
	unset( $cocok, $atrib );

	return haka_embed_facebook_markup( 'post', $url );
}

/* ==========================================================================
   Skrip pihak ketiga
   --------------------------------------------------------------------------
   Skrip hanya dimuat pada halaman yang benar-benar memuat sematannya, dan
   sekali saja meskipun ada beberapa sematan di satu artikel.
   ========================================================================== */

/**
 * Tandai bahwa sebuah skrip sematan dibutuhkan halaman ini.
 *
 * @param string $nama Nama layanan.
 * @return array Daftar layanan yang tercatat.
 */
function haka_embed_perlu_skrip( $nama = '' ) {
	static $perlu = array();

	if ( '' !== $nama ) {
		$perlu[ $nama ] = true;
	}

	return $perlu;
}

/**
 * Cetak skrip sematan di footer.
 */
function haka_embed_cetak_skrip() {
	$perlu = haka_embed_perlu_skrip();

	if ( isset( $perlu['instagram'] ) ) {
		wp_print_script_tag(
			array(
				'src'   => 'https://www.instagram.com/embed.js',
				'async' => true,
				'id'    => 'haka-instagram-embed',
			)
		);
	}

	if ( isset( $perlu['facebook'] ) ) {
		echo '<div id="fb-root"></div>';

		// Bahasa SDK mengikuti bahasa situs supaya tombol dan label sematan
		// tidak berbahasa Inggris di situs berbahasa Indonesia.
		$lokal = get_locale();
		$lokal = preg_match( '/^[a-z]{2,3}_[A-Z]{2}$/', $lokal ) ? $lokal : 'en_US';

		wp_print_script_tag(
			array(
				'src'         => 'https://connect.facebook.net/' . $lokal . '/sdk.js#xfbml=1&version=v20.0',
				'async'       => true,
				'defer'       => true,
				'crossorigin' => 'anonymous',
				'id'          => 'haka-facebook-sdk',
			)
		);
	}
}
add_action( 'wp_footer', 'haka_embed_cetak_skrip', 30 );

/**
 * Daftarkan pengaturan modul ini ke registry fitur.
 *
 * @param array $registry Registry fitur.
 * @return array
 */
function haka_embed_registry( $registry ) {
	$registry = haka_registry_add(
		$registry,
		'embeds',
		array(
			'embed_x'         => array(
				'label'   => __( 'Embed X (Twitter)', 'haka' ),
				'desc'    => __( 'WordPress baru mengenali alamat twitter.com. Pilihan ini membuat tautan x.com ikut berubah jadi kartu kiriman.', 'haka' ),
				'type'    => 'checkbox',
				'default' => true,
			),
			'embed_instagram' => array(
				'label'   => __( 'Embed Instagram', 'haka' ),
				'desc'    => __( 'Menampilkan kiriman, reel, dan IGTV. Bila situs Anda memakai Content Security Policy, izinkan www.instagram.com.', 'haka' ),
				'type'    => 'checkbox',
				'default' => true,
			),
			'embed_facebook'  => array(
				'label'   => __( 'Embed Facebook', 'haka' ),
				'desc'    => __( 'Menampilkan kiriman, video, dan Reels Facebook yang disetel publik.', 'haka' ),
				'type'    => 'checkbox',
				'default' => true,
			),
		),
		array(
			'title'    => __( 'Embed Media Sosial', 'haka' ),
			'priority' => 96,
			'desc'     => __( 'YouTube, TikTok, Vimeo, Spotify, Reddit, dan SoundCloud sudah ditangani WordPress sendiri. Pilihan di bawah menambal yang belum: tautan x.com, Instagram, dan Facebook.', 'haka' ),
		)
	);
	return $registry;
}
add_filter( 'haka_feature_registry', 'haka_embed_registry' );
