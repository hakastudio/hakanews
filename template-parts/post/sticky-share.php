<?php
/**
 * Tombol berbagi melayang.
 *
 * Muncul di sisi kiri layar besar dan sebagai bilah bawah di ponsel, hanya
 * saat pembaca berada di dalam badan artikel.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

$haka_url   = rawurlencode( get_permalink() );
$haka_title = rawurlencode( get_the_title() );

$haka_links = array(
	'facebook' => array(
		'label' => __( 'Bagikan ke Facebook', 'haka' ),
		'url'   => 'https://www.facebook.com/sharer/sharer.php?u=' . $haka_url,
	),
	'x'        => array(
		'label' => __( 'Bagikan ke X', 'haka' ),
		'url'   => 'https://x.com/intent/tweet?text=' . $haka_title . '&url=' . $haka_url,
	),
	'whatsapp' => array(
		'label' => __( 'Bagikan ke WhatsApp', 'haka' ),
		'url'   => 'https://api.whatsapp.com/send?text=' . $haka_title . '%20' . $haka_url,
	),
	'telegram' => array(
		'label' => __( 'Bagikan ke Telegram', 'haka' ),
		'url'   => 'https://t.me/share/url?url=' . $haka_url . '&text=' . $haka_title,
	),
);
?>
<div class="hk-stickyshare" id="hk-stickyshare" aria-label="<?php esc_attr_e( 'Bagikan artikel', 'haka' ); ?>" hidden>
	<span class="hk-stickyshare__label"><?php esc_html_e( 'Bagikan', 'haka' ); ?></span>

	<?php foreach ( $haka_links as $haka_key => $haka_item ) : ?>
		<a class="hk-stickyshare__btn hk-stickyshare__btn--<?php echo esc_attr( $haka_key ); ?>"
			href="<?php echo esc_url( $haka_item['url'] ); ?>"
			target="_blank"
			rel="noopener noreferrer">
			<?php haka_icon( $haka_key, array( 'size' => 18 ) ); ?>
			<span class="screen-reader-text"><?php echo esc_html( $haka_item['label'] ); ?></span>
		</a>
	<?php endforeach; ?>

	<button type="button" class="hk-stickyshare__btn hk-stickyshare__btn--copy" data-hk-copy="<?php echo esc_url( get_permalink() ); ?>">
		<?php haka_icon( 'link', array( 'size' => 18 ) ); ?>
		<span class="screen-reader-text"><?php esc_html_e( 'Salin tautan', 'haka' ); ?></span>
	</button>
</div>
