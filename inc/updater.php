<?php
/**
 * Pembaruan theme langsung dari GitHub Releases.
 *
 * Theme mendeteksi rilis/tag baru dari repositori GitHub secara otomatis,
 * membaca catatan rilis (changelog Markdown), dan menyerahkan paket zip
 * ke mekanisme pembaruan bawaan WordPress.
 *
 * Mendukung aset berkas zip yang diunggah ke GitHub Releases maupun
 * arsip zip otomatis dari GitHub (zipball). Direktori hasil ekstraksi
 * otomatis dinormalisasi ke slug theme aktif melalui filter
 * upgrader_source_selection.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

define( 'HAKA_UPDATE_TRANSIENT', 'haka_update_info' );

/**
 * Repositori default (dapat ditimpa melalui konstanta HAKA_GITHUB_REPO
 * di wp-config.php atau pengaturan di Customizer).
 */
if ( ! defined( 'HAKA_GITHUB_REPO' ) ) {
	define( 'HAKA_GITHUB_REPO', 'hakastudio/hakanews' );
}

/**
 * Ambil nama repositori GitHub yang aktif (format: username/repository).
 *
 * Prioritas:
 * 1. Pengaturan Customizer (haka_github_repo)
 * 2. Konstanta HAKA_GITHUB_REPO di wp-config.php
 *
 * @return string
 */
function haka_github_repo() {
	$repo = haka_get_option( 'github_repo', '' );

	if ( empty( $repo ) && defined( 'HAKA_GITHUB_REPO' ) ) {
		$repo = HAKA_GITHUB_REPO;
	}

	$repo = trim( (string) $repo );

	// Bersihkan bila diinput sebagai URL lengkap (e.g. https://github.com/owner/repo.git)
	if ( preg_match( '#github\.com[:/]([A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+?)(?:\.git)?(?:/.*)?$#i', $repo, $matches ) ) {
		return $matches[1];
	}

	// Format standar: owner/repo
	$repo = trim( $repo, " /\t\n\r\0\x0B" );
	$repo = preg_replace( '#\.git$#i', '', $repo );

	if ( ! preg_match( '#^[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+$#', $repo ) ) {
		return '';
	}

	return $repo;
}

/**
 * Ambil GitHub Personal Access Token (opsional).
 *
 * Berguna bila repositori bersifat privat atau untuk kuota API lebih tinggi.
 *
 * @return string
 */
function haka_github_token() {
	$token = haka_get_option( 'github_token', '' );

	if ( empty( $token ) && defined( 'HAKA_GITHUB_TOKEN' ) ) {
		$token = HAKA_GITHUB_TOKEN;
	}

	return trim( (string) $token );
}

/**
 * Konversi Markdown sederhana dari GitHub Release notes ke format HTML aman.
 *
 * @param string $markdown Teks catatan rilis format Markdown.
 * @return string
 */
function haka_markdown_to_html( $markdown ) {
	$markdown = trim( (string) $markdown );

	if ( '' === $markdown ) {
		return '';
	}

	// Normalisasi baris baru.
	$markdown = str_replace( array( "\r\n", "\r" ), "\n", $markdown );

	// Konversi judul markdown (###, ##, #).
	$markdown = preg_replace( '/^###\s+(.*)$/m', '<h4>$1</h4>', $markdown );
	$markdown = preg_replace( '/^##\s+(.*)$/m', '<h3>$1</h3>', $markdown );
	$markdown = preg_replace( '/^#\s+(.*)$/m', '<h2>$1</h2>', $markdown );

	// Bold dan Italic.
	$markdown = preg_replace( '/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $markdown );
	$markdown = preg_replace( '/\*(.*?)\*/s', '<em>$1</em>', $markdown );

	// Inline code.
	$markdown = preg_replace( '/`([^`]+)`/', '<code>$1</code>', $markdown );

	// Tautan [teks](url).
	$markdown = preg_replace(
		'/\[([^\]]+)\]\((https?:\/\/[^\)]+)\)/',
		'<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>',
		$markdown
	);

	// Parse daftar butir (- atau *).
	$lines   = explode( "\n", $markdown );
	$in_list = false;
	$output  = array();

	foreach ( $lines as $line ) {
		$trimmed = trim( $line );

		if ( preg_match( '/^[-*+]\s+(.+)$/', $trimmed, $matches ) ) {
			if ( ! $in_list ) {
				$output[] = '<ul style="list-style:disc;margin:8px 0 12px 20px;">';
				$in_list  = true;
			}
			$output[] = '<li>' . $matches[1] . '</li>';
		} else {
			if ( $in_list ) {
				$output[] = '</ul>';
				$in_list  = false;
			}
			if ( '' !== $trimmed ) {
				if ( 0 === strpos( $trimmed, '<h' ) ) {
					$output[] = $trimmed;
				} else {
					$output[] = '<p style="margin:6px 0;">' . $trimmed . '</p>';
				}
			}
		}
	}

	if ( $in_list ) {
		$output[] = '</ul>';
	}

	return wp_kses_post( implode( "\n", $output ) );
}

/**
 * Ambil keterangan rilis terbaru dan riwayat dari GitHub Releases API.
 *
 * @param bool $force Abaikan cache transient.
 * @return array|false
 */
function haka_fetch_update_info( $force = false ) {
	if ( ! haka_enabled( 'update_enable' ) ) {
		return false;
	}

	$repo = haka_github_repo();

	if ( ! $repo ) {
		return false;
	}

	if ( ! $force ) {
		$cached = get_transient( HAKA_UPDATE_TRANSIENT );

		if ( is_array( $cached ) ) {
			return empty( $cached ) ? false : $cached;
		}
	}

	$hours = max( 1, min( 168, (int) haka_get_option( 'update_interval', 12 ) ) );

	$headers = array(
		'Accept'     => 'application/vnd.github.v3+json',
		'User-Agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . home_url(),
	);

	$token = haka_github_token();
	if ( ! empty( $token ) ) {
		$headers['Authorization'] = 'Bearer ' . $token;
	}

	$api_url  = 'https://api.github.com/repos/' . $repo . '/releases?per_page=5';
	$response = wp_safe_remote_get(
		$api_url,
		array(
			'timeout' => 15,
			'headers' => $headers,
		)
	);

	if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
		// Simpan hasil kosong selama 15 menit agar server yang lambat atau limit API
		// tidak mengganggu pemuatan halaman dashboard admin.
		set_transient( HAKA_UPDATE_TRANSIENT, array(), 15 * MINUTE_IN_SECONDS );
		return false;
	}

	$releases = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( ! is_array( $releases ) || empty( $releases ) ) {
		set_transient( HAKA_UPDATE_TRANSIENT, array(), 15 * MINUTE_IN_SECONDS );
		return false;
	}

	$latest = $releases[0];

	if ( ! is_array( $latest ) || empty( $latest['tag_name'] ) ) {
		set_transient( HAKA_UPDATE_TRANSIENT, array(), 15 * MINUTE_IN_SECONDS );
		return false;
	}

	// Normalisasi nomor versi (buang prefix 'v' atau 'V').
	$version = ltrim( trim( (string) $latest['tag_name'] ), 'vV ' );

	// Tentukan tautan paket unduhan (.zip).
	$package = '';

	// 1. Cek bila ada berkas .zip yang diunggah secara khusus sebagai Release Asset.
	if ( ! empty( $latest['assets'] ) && is_array( $latest['assets'] ) ) {
		foreach ( $latest['assets'] as $asset ) {
			if ( ! empty( $asset['name'] ) && substr( strtolower( (string) $asset['name'] ), -4 ) === '.zip' ) {
				$package = isset( $asset['browser_download_url'] ) ? esc_url_raw( (string) $asset['browser_download_url'] ) : '';
				break;
			}
		}
	}

	// 2. Jika tidak ada aset zip khusus, gunakan zip otomatis (zipball) dari GitHub.
	if ( empty( $package ) ) {
		if ( ! empty( $latest['zipball_url'] ) ) {
			$package = esc_url_raw( (string) $latest['zipball_url'] );
		} else {
			$package = esc_url_raw( 'https://api.github.com/repos/' . $repo . '/zipball/' . rawurlencode( $latest['tag_name'] ) );
		}
	}

	// Susun riwayat rilis untuk widget dashboard Haka.
	$history = array();
	foreach ( $releases as $rel ) {
		if ( empty( $rel['tag_name'] ) ) {
			continue;
		}

		$rel_version = ltrim( trim( (string) $rel['tag_name'] ), 'vV ' );
		$rel_date    = ! empty( $rel['published_at'] ) ? substr( (string) $rel['published_at'], 0, 10 ) : '';
		$rel_title   = ! empty( $rel['name'] ) ? sanitize_text_field( (string) $rel['name'] ) : '';

		$perubahan = array();
		if ( ! empty( $rel['body'] ) ) {
			$lines = explode( "\n", (string) $rel['body'] );
			foreach ( $lines as $line ) {
				$trimmed = trim( $line );
				if ( preg_match( '/^[-*+]\s+(.+)$/', $trimmed, $m ) ) {
					$perubahan[] = array(
						'jenis' => 'perubahan',
						'teks'  => sanitize_text_field( $m[1] ),
					);
					if ( count( $perubahan ) >= 8 ) {
						break;
					}
				}
			}
		}

		$history[] = array(
			'versi'     => $rel_version,
			'tanggal'   => $rel_date,
			'ringkas'   => $rel_title,
			'perubahan' => $perubahan,
		);
	}

	$theme = wp_get_theme( get_template() );

	$info = array(
		'version'      => sanitize_text_field( $version ),
		'package'      => $package,
		'url'          => ! empty( $latest['html_url'] ) ? esc_url_raw( (string) $latest['html_url'] ) : 'https://github.com/' . $repo,
		'requires'     => $theme->get( 'RequiresWP' ) ? (string) $theme->get( 'RequiresWP' ) : '6.0',
		'requires_php' => $theme->get( 'RequiresPHP' ) ? (string) $theme->get( 'RequiresPHP' ) : '7.4',
		'tested'       => '7.1',
		'changelog'    => ! empty( $latest['body'] ) ? haka_markdown_to_html( (string) $latest['body'] ) : '',
		'history'      => $history,
		'checked_at'   => time(),
	);

	set_transient( HAKA_UPDATE_TRANSIENT, $info, $hours * HOUR_IN_SECONDS );

	return $info;
}

/**
 * Sisipkan informasi pembaruan ke mekanisme update bawaan WordPress.
 *
 * @param mixed $transient Data transient update_themes.
 * @return mixed
 */
function haka_inject_theme_update( $transient ) {
	if ( ! is_object( $transient ) ) {
		return $transient;
	}

	$theme_slug    = get_template();
	$theme         = wp_get_theme( $theme_slug );
	$current       = $theme->exists() ? (string) $theme->get( 'Version' ) : '';
	$current_clean = ltrim( trim( $current ), 'vV ' );

	if ( '' === $current_clean ) {
		return $transient;
	}

	$info = haka_fetch_update_info();

	if ( ! $info ) {
		return $transient;
	}

	if ( ! isset( $transient->response ) || ! is_array( $transient->response ) ) {
		$transient->response = array();
	}

	if ( ! isset( $transient->no_update ) || ! is_array( $transient->no_update ) ) {
		$transient->no_update = array();
	}

	$entry = array(
		'theme'        => $theme_slug,
		'new_version'  => $info['version'],
		'url'          => $info['url'],
		'package'      => $info['package'],
		'requires'     => $info['requires'],
		'requires_php' => $info['requires_php'],
	);

	if ( version_compare( $info['version'], $current_clean, '>' ) ) {
		$transient->response[ $theme_slug ] = $entry;
		unset( $transient->no_update[ $theme_slug ] );
	} else {
		// Didaftarkan sebagai "tidak ada pembaruan" supaya WordPress tetap
		// menampilkan saklar pembaruan otomatis untuk theme ini.
		$entry['new_version']               = $current;
		$transient->no_update[ $theme_slug ] = $entry;
		unset( $transient->response[ $theme_slug ] );
	}

	return $transient;
}
add_filter( 'site_transient_update_themes', 'haka_inject_theme_update' );

/**
 * Sediakan data popup detail versi ketika tautan "Lihat detail versi" diklik di WordPress.
 *
 * @param mixed  $result Hasil API default.
 * @param string $action Tindakan (misal: 'theme_information').
 * @param object $args   Argumen permintaan.
 * @return mixed
 */
function haka_theme_api_call( $result, $action, $args ) {
	if ( 'theme_information' !== $action || empty( $args->slug ) ) {
		return $result;
	}

	$theme_slug = get_template();

	if ( $args->slug !== $theme_slug && $args->slug !== 'haka' ) {
		return $result;
	}

	$info = haka_fetch_update_info();

	if ( ! $info ) {
		return $result;
	}

	$theme = wp_get_theme( $theme_slug );

	return (object) array(
		'name'          => $theme->get( 'Name' ),
		'slug'          => $theme_slug,
		'version'       => $info['version'],
		'author'        => sprintf( '<a href="%s" target="_blank" rel="noopener">%s</a>', esc_url( $theme->get( 'AuthorURI' ) ), esc_html( $theme->get( 'Author' ) ) ),
		'preview_url'   => $theme->get( 'ThemeURI' ),
		'requires'      => $info['requires'],
		'requires_php'  => $info['requires_php'],
		'sections'      => array(
			'description' => $theme->get( 'Description' ),
			'changelog'   => ! empty( $info['changelog'] ) ? $info['changelog'] : '<p>' . esc_html__( 'Tidak ada catatan perubahan.', 'haka' ) . '</p>',
		),
		'download_link' => $info['package'],
	);
}
add_filter( 'themes_api', 'haka_theme_api_call', 10, 3 );

/**
 * Normalisasi nama folder theme setelah diekstrak dari arsip GitHub.
 *
 * GitHub zipball secara bawaan mengekstrak berkas ke folder bernama:
 * username-repo-hash/. Fungsi ini memindahkan isinya ke nama folder theme
 * yang benar (get_template()) sehingga tidak membuat theme duplikat atau nonaktif.
 *
 * @param string      $source        Path direktori hasil ekstrak sementara.
 * @param string      $remote_source Path direktori induk sementara.
 * @param WP_Upgrader $upgrader      Objek upgrader WordPress.
 * @param array       $hook_extra    Data tambahan hook.
 * @return string|WP_Error
 */
function haka_upgrader_source_selection( $source, $remote_source, $upgrader, $hook_extra = array() ) {
	global $wp_filesystem;

	if ( ! isset( $hook_extra['theme'] ) ) {
		return $source;
	}

	$theme_slug = get_template();

	if ( $hook_extra['theme'] !== $theme_slug && $hook_extra['theme'] !== 'haka' ) {
		return $source;
	}

	$correct_source = trailingslashit( $remote_source ) . $theme_slug . '/';

	if ( $source !== $correct_source ) {
		if ( $wp_filesystem->exists( $correct_source ) ) {
			$wp_filesystem->delete( $correct_source, true );
		}

		$moved = $wp_filesystem->move( $source, $correct_source );

		if ( ! $moved ) {
			return new WP_Error(
				'haka_rename_failed',
				esc_html__( 'Gagal merapikan folder theme setelah ekstraksi dari GitHub.', 'haka' )
			);
		}

		return $correct_source;
	}

	return $source;
}
add_filter( 'upgrader_source_selection', 'haka_upgrader_source_selection', 10, 4 );

/**
 * Bersihkan cache transient saat proses upgrade selesai.
 */
function haka_clear_update_cache() {
	delete_transient( HAKA_UPDATE_TRANSIENT );
}
add_action( 'upgrader_process_complete', 'haka_clear_update_cache' );

/**
 * Tangani tombol "Periksa sekarang".
 */
function haka_handle_manual_check() {
	if ( ! current_user_can( 'update_themes' ) ) {
		wp_die( esc_html__( 'Anda tidak memiliki izin untuk memeriksa pembaruan.', 'haka' ) );
	}

	check_admin_referer( 'haka_check_update' );

	delete_transient( HAKA_UPDATE_TRANSIENT );
	delete_site_transient( 'update_themes' );
	haka_fetch_update_info( true );

	$referer = wp_get_referer();
	$target  = $referer ? $referer : admin_url( 'themes.php?page=haka-demo' );

	wp_safe_redirect( add_query_arg( 'haka_checked', '1', $target ) );
	exit;
}
add_action( 'admin_post_haka_check_update', 'haka_handle_manual_check' );

/**
 * Kotak status pembaruan di halaman admin theme.
 */
function haka_render_update_box() {
	if ( ! current_user_can( 'update_themes' ) ) {
		return;
	}

	$theme_slug    = get_template();
	$theme         = wp_get_theme( $theme_slug );
	$current       = (string) $theme->get( 'Version' );
	$current_clean = ltrim( trim( $current ), 'vV ' );
	$repo          = haka_github_repo();
	$info          = haka_fetch_update_info();

	$checked = isset( $_GET['haka_checked'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	?>
	<div class="card" style="max-width:760px;padding:16px 20px;margin-bottom:16px;">
		<h2 style="margin-top:0;"><?php esc_html_e( 'Pembaruan Theme (GitHub Releases)', 'haka' ); ?></h2>

		<p>
			<?php
			printf(
				/* translators: %s: nomor versi */
				esc_html__( 'Versi terpasang: %s', 'haka' ),
				'<strong>' . esc_html( $current ) . '</strong>'
			);
			?>
			<?php if ( $repo ) : ?>
				<span style="color:#646970;margin-left:10px;">
					(GitHub: <code><?php echo esc_html( $repo ); ?></code>)
				</span>
			<?php endif; ?>
		</p>

		<?php if ( ! haka_enabled( 'update_enable' ) ) : ?>
			<p><?php esc_html_e( 'Pemeriksaan pembaruan otomatis sedang dimatikan di Customizer.', 'haka' ); ?></p>

		<?php elseif ( ! $repo ) : ?>
			<div class="notice notice-warning inline" style="margin:8px 0;">
				<p>
					<?php esc_html_e( 'Repositori GitHub belum diatur.', 'haka' ); ?><br>
					<?php
					printf(
						/* translators: %s: tautan ke customizer */
						esc_html__( 'Atur nama repositori (contoh: username/hakanews) di %s → Pembaruan Theme atau melalui konstanta HAKA_GITHUB_REPO di wp-config.php.', 'haka' ),
						'<a href="' . esc_url( admin_url( 'customize.php?autofocus[section]=haka_section_updates' ) ) . '">Customizer</a>'
					);
					?>
				</p>
			</div>

		<?php elseif ( ! $info ) : ?>
			<div class="notice notice-warning inline" style="margin:8px 0;">
				<p>
					<?php esc_html_e( 'Belum ada rilis yang ditemukan di GitHub, atau API GitHub belum dapat dihubungi.', 'haka' ); ?><br>
					<a href="<?php echo esc_url( 'https://github.com/' . $repo . '/releases' ); ?>" target="_blank" rel="noopener">
						<?php echo esc_html( 'https://github.com/' . $repo . '/releases' ); ?>
					</a>
				</p>
			</div>

		<?php elseif ( version_compare( $info['version'], $current_clean, '>' ) ) : ?>
			<div class="notice notice-info inline" style="margin:8px 0;">
				<p>
					<?php
					printf(
						/* translators: %s: nomor versi */
						esc_html__( 'Versi %s tersedia di GitHub Releases. Pasang lewat Dashboard → Pembaruan atau Tampilan → Tema.', 'haka' ),
						'<strong>' . esc_html( $info['version'] ) . '</strong>'
					);
					?>
				</p>
				<?php if ( ! empty( $info['changelog'] ) ) : ?>
					<div style="margin-top:10px;padding:10px 14px;background:#f6f7f7;border-left:4px solid #72aee6;border-radius:2px;">
						<strong><?php esc_html_e( 'Catatan Rilis:', 'haka' ); ?></strong>
						<?php echo wp_kses_post( $info['changelog'] ); ?>
					</div>
				<?php endif; ?>
			</div>

		<?php else : ?>
			<div class="notice notice-success inline" style="margin:8px 0;">
				<p><?php esc_html_e( 'Theme sudah versi terbaru.', 'haka' ); ?></p>
			</div>
		<?php endif; ?>

		<?php if ( $checked ) : ?>
			<p class="description" style="color:#007017;"><?php esc_html_e( 'Pemeriksaan pembaruan baru saja dijalankan.', 'haka' ); ?></p>
		<?php endif; ?>

		<div style="display:flex;gap:8px;align-items:center;margin-top:12px;">
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:0;">
				<input type="hidden" name="action" value="haka_check_update">
				<?php wp_nonce_field( 'haka_check_update' ); ?>
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Periksa pembaruan sekarang', 'haka' ); ?></button>
			</form>

			<?php if ( $repo ) : ?>
				<a href="<?php echo esc_url( 'https://github.com/' . $repo . '/releases' ); ?>" target="_blank" rel="noopener" class="button">
					<?php esc_html_e( 'Buka Rilis di GitHub', 'haka' ); ?> &rarr;
				</a>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

/**
 * Daftarkan pengaturan modul ini ke registry fitur theme (Customizer).
 *
 * @param array $registry Registry fitur.
 * @return array
 */
function haka_updater_registry( $registry ) {
	$registry = haka_registry_add(
		$registry,
		'updates',
		array(
			'github_repo'     => array(
				'label'   => __( 'Repositori GitHub', 'haka' ),
				'desc'    => __( 'Format: username/nama-repo (contoh: user/hakanews).', 'haka' ),
				'type'    => 'text',
				'default' => defined( 'HAKA_GITHUB_REPO' ) ? HAKA_GITHUB_REPO : '',
			),
			'github_token'    => array(
				'label'   => __( 'GitHub Token (opsional)', 'haka' ),
				'desc'    => __( 'Hanya diperlukan jika repositori privat atau untuk kuota GitHub API lebih tinggi.', 'haka' ),
				'type'    => 'text',
				'default' => defined( 'HAKA_GITHUB_TOKEN' ) ? HAKA_GITHUB_TOKEN : '',
			),
			'update_enable'   => array(
				'label'   => __( 'Periksa pembaruan otomatis', 'haka' ),
				'type'    => 'checkbox',
				'default' => true,
			),
			'update_interval' => array(
				'label'   => __( 'Jeda pemeriksaan (jam)', 'haka' ),
				'type'    => 'number',
				'default' => 12,
				'min'     => 1,
				'max'     => 168,
			),
		),
		array(
			'title'    => __( 'Pembaruan Theme (GitHub)', 'haka' ),
			'priority' => 120,
			'desc'     => __( 'Theme memeriksa pembaruan rilis langsung dari GitHub Releases.', 'haka' ),
		)
	);
	return $registry;
}
add_filter( 'haka_feature_registry', 'haka_updater_registry' );
