<?php
/**
 * Area komentar.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

if ( post_password_required() ) {
	return;
}
?>

<section id="comments" class="hk-comments">

	<?php if ( have_comments() ) : ?>
		<h2 class="hk-comments__title">
			<?php
			$haka_count = (int) get_comments_number();

			printf(
				/* translators: %s: jumlah komentar */
				esc_html( _n( '%s Komentar', '%s Komentar', $haka_count, 'haka' ) ),
				esc_html( number_format_i18n( $haka_count ) )
			);
			?>
		</h2>

		<ol class="hk-comments__list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'avatar_size' => 44,
					'callback'    => 'haka_comment_item',
					'short_ping'  => true,
				)
			);
			?>
		</ol>

		<?php
		the_comments_pagination(
			array(
				'prev_text' => '<span class="screen-reader-text">' . esc_html__( 'Komentar sebelumnya', 'haka' ) . '</span>' . haka_get_icon( 'chevron-left', array( 'size' => 16 ) ),
				'next_text' => '<span class="screen-reader-text">' . esc_html__( 'Komentar berikutnya', 'haka' ) . '</span>' . haka_get_icon( 'chevron-right', array( 'size' => 16 ) ),
			)
		);
		?>

		<?php if ( ! comments_open() ) : ?>
			<p class="hk-comments__closed"><?php esc_html_e( 'Kolom komentar ditutup.', 'haka' ); ?></p>
		<?php endif; ?>

	<?php endif; ?>

	<?php
	comment_form(
		array(
			'class_container'    => 'hk-commentform',
			'title_reply'        => __( 'Tinggalkan Komentar', 'haka' ),
			'title_reply_to'     => __( 'Balas kepada %s', 'haka' ),
			'cancel_reply_link'  => __( 'Batal balas', 'haka' ),
			'label_submit'       => __( 'Kirim Komentar', 'haka' ),
			'class_submit'       => 'hk-btn hk-btn--primary',
			'comment_notes_before' => '<p class="hk-commentform__notes">' . esc_html__( 'Alamat email tidak akan dipublikasikan. Kolom wajib ditandai *', 'haka' ) . '</p>',
			'comment_field'      => sprintf(
				'<p class="comment-form-comment"><label for="comment">%1$s</label><textarea id="comment" name="comment" cols="45" rows="6" required></textarea></p>',
				esc_html__( 'Komentar *', 'haka' )
			),
		)
	);
	?>

</section>
