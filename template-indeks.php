<?php
/**
 * Template Name: Indeks Berita
 * Template Post Type: page
 *
 * Daftar seluruh berita secara kronologis, dengan penyaring kategori dan
 * tanggal. Halaman berpindah lewat parameter idx_page, bukan /page/2/,
 * supaya bekerja pada halaman statis tanpa bergantung aturan rewrite.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

get_header();

$haka_filters  = haka_index_filters();
$haka_page     = isset( $_GET['idx_page'] ) ? max( 1, absint( wp_unslash( $_GET['idx_page'] ) ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$haka_index    = haka_query_index( $haka_page );

// Nomor halaman di luar jangkauan dikembalikan ke halaman pertama.
if ( $haka_page > 1 && ! $haka_index->have_posts() ) {
	$haka_page  = 1;
	$haka_index = haka_query_index( $haka_page );
}
$haka_base_url = get_permalink();
$haka_group    = haka_enabled( 'index_group' );
$haka_show_t   = haka_enabled( 'index_show_time' );
$haka_show_c   = haka_enabled( 'index_show_cat' );

// Nilai penyaring dipertahankan saat berpindah halaman.
$haka_keep = array_filter(
	array(
		'idx_cat' => $haka_filters['category'],
		'idx_d'   => $haka_filters['day'],
		'idx_m'   => $haka_filters['month'],
		'idx_y'   => $haka_filters['year'],
	)
);

$haka_last_day = '';
?>

<main id="primary" class="site-main hk-index" role="main">
	<div class="hk-container">

		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<header class="hk-pagehead">
				<?php haka_breadcrumbs(); ?>
				<h1 class="hk-pagehead__title"><?php the_title(); ?></h1>

				<?php if ( get_the_content() ) : ?>
					<div class="hk-pagehead__desc"><?php the_content(); ?></div>
				<?php endif; ?>

				<p class="hk-pagehead__count">
					<?php
					printf(
						/* translators: %s: jumlah berita */
						esc_html( _n( '%s berita', '%s berita', (int) $haka_index->found_posts, 'haka' ) ),
						esc_html( number_format_i18n( (int) $haka_index->found_posts ) )
					);
					?>
				</p>
			</header>
			<?php
		endwhile;
		?>

		<div class="hk-layout">
			<div class="hk-layout__main">

				<?php if ( haka_enabled( 'index_filters' ) ) : ?>
					<form class="hk-indexfilter" method="get" action="<?php echo esc_url( $haka_base_url ); ?>">
						<div class="hk-indexfilter__row">

							<label class="hk-indexfilter__field">
								<span><?php esc_html_e( 'Kategori', 'haka' ); ?></span>
								<?php
								wp_dropdown_categories(
									array(
										'show_option_all' => __( 'Semua kategori', 'haka' ),
										'selected'        => $haka_filters['category'],
										'name'            => 'idx_cat',
										'id'              => 'hk-idx-cat',
										'orderby'         => 'name',
										'hierarchical'    => true,
										'hide_empty'      => true,
									)
								);
								?>
							</label>

							<label class="hk-indexfilter__field">
								<span><?php esc_html_e( 'Tanggal', 'haka' ); ?></span>
								<select name="idx_d" id="hk-idx-d">
									<option value="0"><?php esc_html_e( 'Semua', 'haka' ); ?></option>
									<?php for ( $haka_d = 1; $haka_d <= 31; $haka_d++ ) : ?>
										<option value="<?php echo esc_attr( (string) $haka_d ); ?>" <?php selected( $haka_filters['day'], $haka_d ); ?>>
											<?php echo esc_html( (string) $haka_d ); ?>
										</option>
									<?php endfor; ?>
								</select>
							</label>

							<label class="hk-indexfilter__field">
								<span><?php esc_html_e( 'Bulan', 'haka' ); ?></span>
								<select name="idx_m" id="hk-idx-m">
									<option value="0"><?php esc_html_e( 'Semua', 'haka' ); ?></option>
									<?php for ( $haka_m = 1; $haka_m <= 12; $haka_m++ ) : ?>
										<option value="<?php echo esc_attr( (string) $haka_m ); ?>" <?php selected( $haka_filters['month'], $haka_m ); ?>>
											<?php echo esc_html( haka_month_id( $haka_m ) ); ?>
										</option>
									<?php endfor; ?>
								</select>
							</label>

							<label class="hk-indexfilter__field">
								<span><?php esc_html_e( 'Tahun', 'haka' ); ?></span>
								<select name="idx_y" id="hk-idx-y">
									<option value="0"><?php esc_html_e( 'Semua', 'haka' ); ?></option>
									<?php foreach ( haka_index_years() as $haka_y ) : ?>
										<option value="<?php echo esc_attr( (string) $haka_y ); ?>" <?php selected( $haka_filters['year'], $haka_y ); ?>>
											<?php echo esc_html( (string) $haka_y ); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</label>

							<div class="hk-indexfilter__actions">
								<button type="submit" class="hk-btn"><?php esc_html_e( 'Tampilkan', 'haka' ); ?></button>

								<?php if ( ! empty( $haka_keep ) ) : ?>
									<a class="hk-indexfilter__reset" href="<?php echo esc_url( $haka_base_url ); ?>">
										<?php esc_html_e( 'Atur ulang', 'haka' ); ?>
									</a>
								<?php endif; ?>
							</div>

						</div>
					</form>
				<?php endif; ?>

				<?php if ( $haka_index->have_posts() ) : ?>

					<ol class="hk-index__list">
						<?php
						while ( $haka_index->have_posts() ) :
							$haka_index->the_post();

							$haka_day = get_the_date( 'Y-m-d' );

							if ( $haka_group && $haka_day !== $haka_last_day ) :
								$haka_last_day       = $haka_day;
								$haka_formatted_date = haka_absolute_date();
								?>
								<li class="hk-index__day">
									<h2><?php echo esc_html( $haka_formatted_date ); ?></h2>
								</li>
								<?php
							endif;
							?>

							<li class="hk-index__item">
								<?php if ( $haka_show_t ) : ?>
									<time class="hk-index__time" datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>">
										<?php echo esc_html( get_the_time() ); ?>
									</time>
								<?php endif; ?>

								<div class="hk-index__body">
									<?php
									if ( $haka_show_c ) {
										haka_category_badge( get_the_ID(), 'hk-badge--plain' );
									}
									?>
									<h3 class="hk-index__title">
										<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
									</h3>
								</div>
							</li>
						<?php endwhile; ?>
					</ol>

					<?php
					$haka_links = paginate_links(
						array(
							'base'      => add_query_arg( 'idx_page', '%#%', $haka_base_url ),
							'format'    => '',
							'current'   => $haka_page,
							'total'     => (int) $haka_index->max_num_pages,
							'add_args'  => $haka_keep,
							'mid_size'  => 1,
							'end_size'  => 1,
							'prev_text' => haka_get_icon( 'chevron-left', array( 'size' => 16 ) ) . '<span>' . esc_html__( 'Sebelumnya', 'haka' ) . '</span>',
							'next_text' => '<span>' . esc_html__( 'Berikutnya', 'haka' ) . '</span>' . haka_get_icon( 'chevron-right', array( 'size' => 16 ) ),
							'type'      => 'list',
						)
					);

					if ( $haka_links ) {
						printf(
							'<nav class="hk-pagination" aria-label="%1$s">%2$s</nav>',
							esc_attr__( 'Navigasi indeks', 'haka' ),
							wp_kses_post( $haka_links )
						);
					}
					?>

				<?php else : ?>
					<section class="hk-none">
						<h2 class="hk-none__title"><?php esc_html_e( 'Tidak ada berita', 'haka' ); ?></h2>
						<p><?php esc_html_e( 'Tidak ada berita pada penyaring yang dipilih. Coba ubah kategori atau tanggalnya.', 'haka' ); ?></p>
						<p><a class="hk-btn" href="<?php echo esc_url( $haka_base_url ); ?>"><?php esc_html_e( 'Atur ulang penyaring', 'haka' ); ?></a></p>
					</section>
				<?php endif; ?>

				<?php wp_reset_postdata(); ?>
			</div>

			<?php if ( haka_enabled( 'sidebar_enable' ) ) : ?>
				<div class="hk-layout__aside">
					<?php get_sidebar(); ?>
				</div>
			<?php endif; ?>
		</div>

	</div>
</main>

<?php
get_footer();