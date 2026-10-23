<?php
/**
 * Baris aksi artikel: tombol bagikan dan tombol suka.
 *
 * Keduanya adalah reaksi pembaca terhadap artikel yang sama, jadi
 * ditempatkan sebaris supaya tidak memakan dua blok terpisah.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

$haka_show_share = haka_enabled( 'share_enable' );
$haka_show_like  = (bool) apply_filters( 'haka_actions_inline_active', false );

if ( ! $haka_show_share && ! $haka_show_like ) {
	return;
}
?>
<div class="hk-actions">
	<?php if ( $haka_show_share ) : ?>
		<?php haka_share_buttons(); ?>
	<?php endif; ?>

	<?php do_action( 'haka_actions_inline' ); ?>
</div>
