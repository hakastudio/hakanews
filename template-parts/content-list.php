<?php
/**
 * Item berita bentuk daftar: thumbnail kiri, teks kanan.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

$haka_size    = isset( $args['size'] ) ? $args['size'] : 'sm';
// 'auto' menampilkan badge hanya bila kategorinya berbeda dari artikel
// yang sedang dibaca; label yang selalu sama tidak menambah informasi.
$haka_badge_arg = isset( $args['badge'] ) ? $args['badge'] : true;

if ( 'auto' === $haka_badge_arg ) {
	$haka_current = isset( $args['current_cat'] ) ? (int) $args['current_cat'] : 0;
	$haka_own     = haka_primary_category( get_the_ID() );
	$haka_badge   = ( $haka_own && (int) $haka_own->term_id !== $haka_current );
} else {
	$haka_badge = (bool) $haka_badge_arg;
}
$haka_excerpt = ! empty( $args['excerpt'] );
$haka_class = 'hk-list hk-list--' . sanitize_html_class( $haka_size );
?>
<article id="<?php echo esc_attr( haka_entry_id() ); ?>" <?php post_class( $haka_class ); ?>>
	<?php
	haka_post_thumbnail(
		'haka-list',
		array(
			'class' => 'hk-list__thumb',
			'sizes' => '(max-width: 640px) 33vw, 180px',
		)
	);
	?>

	<div class="hk-list__body">
		<?php
		if ( $haka_badge ) {
			haka_category_badge( get_the_ID() );
		}
		?>

		<h3 class="hk-list__title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h3>

		<?php if ( $haka_excerpt && get_the_excerpt() ) : ?>
			<p class="hk-list__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p>
		<?php endif; ?>

		<p class="hk-list__date">
			<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>">
				<?php echo esc_html( haka_relative_date() ); ?>
			</time>
		</p>
	</div>
</article>
