<?php
/**
 * Hero carousel: berita utama.
 *
 * Dibangun di atas scroll-snap native, jadi tetap bisa digeser dengan jari
 * maupun keyboard meski JavaScript gagal dimuat. JavaScript hanya menambah
 * tombol panah, indikator, dan geser otomatis.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

$haka_hero = haka_query_hero();

if ( ! $haka_hero->have_posts() ) {
	wp_reset_postdata();
	return;
}

haka_used_ids( wp_list_pluck( $haka_hero->posts, 'ID' ) );

$haka_total    = (int) $haka_hero->post_count;
$haka_multi    = $haka_total > 1 && haka_get_option( 'hero_carousel', true );
$haka_autoplay = $haka_multi && haka_get_option( 'hero_autoplay', true );
$haka_interval = max( 3, min( 20, (int) haka_get_option( 'hero_interval', 6 ) ) );
$haka_arrows   = $haka_multi && haka_get_option( 'hero_arrows', true );
$haka_dots     = $haka_multi && haka_get_option( 'hero_dots', true );
$haka_ratio    = haka_get_option( 'hero_ratio', '16-9' );
$haka_uid      = wp_unique_id( 'hk-hero-' );

$haka_classes = array( 'hk-hero', 'hk-hero--ratio-' . sanitize_html_class( $haka_ratio ) );

if ( $haka_multi ) {
	$haka_classes[] = 'hk-hero--carousel';
}
?>
<section class="<?php echo esc_attr( implode( ' ', $haka_classes ) ); ?>"
	id="<?php echo esc_attr( $haka_uid ); ?>"
	<?php if ( $haka_multi ) : ?>
		data-hk-carousel
		data-autoplay="<?php echo $haka_autoplay ? 'true' : 'false'; ?>"
		data-interval="<?php echo esc_attr( (string) ( $haka_interval * 1000 ) ); ?>"
		role="region"
		aria-roledescription="<?php esc_attr_e( 'carousel', 'haka' ); ?>"
		aria-label="<?php esc_attr_e( 'Berita utama', 'haka' ); ?>"
	<?php endif; ?>>

	<div class="hk-hero__viewport">
		<div class="hk-hero__track" id="<?php echo esc_attr( $haka_uid ); ?>-track">
			<?php
			$haka_i = 0;

			while ( $haka_hero->have_posts() ) :
				$haka_hero->the_post();
				$haka_i++;
				?>
				<div class="hk-hero__slide"
					<?php if ( $haka_multi ) : ?>
						role="group"
						aria-roledescription="<?php esc_attr_e( 'slide', 'haka' ); ?>"
						aria-label="<?php echo esc_attr( sprintf( /* translators: 1: nomor slide, 2: jumlah slide */ __( '%1$d dari %2$d', 'haka' ), $haka_i, $haka_total ) ); ?>"
					<?php endif; ?>>

					<article id="<?php echo esc_attr( haka_entry_id() ); ?>" <?php post_class( 'hk-hero__item' ); ?>>
						<?php
						haka_post_thumbnail(
							'haka-hero',
							array(
								'class' => 'hk-hero__thumb',
								'lazy'  => $haka_i > 1,
								'sizes' => '(max-width: 800px) 100vw, 748px',
							)
						);
						?>

						<div class="hk-hero__overlay">
							<?php haka_category_badge( get_the_ID() ); ?>

							<h2 class="hk-hero__title">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h2>

							<p class="hk-hero__meta">
								<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>">
									<?php echo esc_html( haka_relative_date() ); ?>
								</time>
							</p>
						</div>
					</article>
				</div>
				<?php
			endwhile;
			?>
		</div>
	</div>

	<?php if ( $haka_arrows ) : ?>
		<button type="button" class="hk-hero__arrow hk-hero__arrow--prev" data-hk-prev aria-controls="<?php echo esc_attr( $haka_uid ); ?>-track">
			<?php haka_icon( 'chevron-left', array( 'size' => 20 ) ); ?>
			<span class="screen-reader-text"><?php esc_html_e( 'Berita sebelumnya', 'haka' ); ?></span>
		</button>
		<button type="button" class="hk-hero__arrow hk-hero__arrow--next" data-hk-next aria-controls="<?php echo esc_attr( $haka_uid ); ?>-track">
			<?php haka_icon( 'chevron-right', array( 'size' => 20 ) ); ?>
			<span class="screen-reader-text"><?php esc_html_e( 'Berita berikutnya', 'haka' ); ?></span>
		</button>
	<?php endif; ?>

	<?php if ( $haka_dots || $haka_autoplay ) : ?>
		<div class="hk-hero__controls">
			<?php if ( $haka_autoplay ) : ?>
				<button type="button" class="hk-hero__playpause" data-hk-playpause aria-pressed="false">
					<span class="hk-hero__playpause-icon" aria-hidden="true"></span>
					<span class="screen-reader-text" data-hk-playpause-label><?php esc_html_e( 'Hentikan geser otomatis', 'haka' ); ?></span>
				</button>
			<?php endif; ?>

			<?php if ( $haka_dots ) : ?>
				<div class="hk-hero__dots" role="tablist" aria-label="<?php esc_attr_e( 'Pilih berita utama', 'haka' ); ?>">
					<?php for ( $haka_d = 1; $haka_d <= $haka_total; $haka_d++ ) : ?>
						<button type="button"
							class="hk-hero__dot<?php echo ( 1 === $haka_d ) ? ' is-active' : ''; ?>"
							data-hk-dot="<?php echo esc_attr( (string) ( $haka_d - 1 ) ); ?>"
							role="tab"
							aria-selected="<?php echo ( 1 === $haka_d ) ? 'true' : 'false'; ?>">
							<span class="screen-reader-text">
								<?php
								printf(
									/* translators: %d: nomor slide */
									esc_html__( 'Ke berita %d', 'haka' ),
									(int) $haka_d
								);
								?>
							</span>
						</button>
					<?php endfor; ?>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( $haka_autoplay ) : ?>
		<div class="hk-hero__progress" aria-hidden="true"><span data-hk-progress></span></div>
	<?php endif; ?>

</section>
<?php
wp_reset_postdata();
