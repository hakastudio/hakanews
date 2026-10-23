<?php
/**
 * Form pencarian.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

$haka_search_id = wp_unique_id( 'hk-search-field-' );

// Beberapa form pencarian bisa muncul di satu halaman (panel header dan
// kepala hasil pencarian). Tiap landmark search harus bernama berbeda.
$haka_search_label = ! empty( $args['aria_label'] )
	? $args['aria_label']
	: __( 'Cari berita', 'haka' );
?>
<form role="search" method="get" class="hk-searchform"
	aria-label="<?php echo esc_attr( $haka_search_label ); ?>"
	action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $haka_search_id ); ?>">
		<?php esc_html_e( 'Cari berita', 'haka' ); ?>
	</label>
	<div class="hk-searchform__row">
		<input type="search"
			id="<?php echo esc_attr( $haka_search_id ); ?>"
			class="hk-searchform__input"
			placeholder="<?php esc_attr_e( 'Cari berita…', 'haka' ); ?>"
			value="<?php echo esc_attr( get_search_query() ); ?>"
			name="s"
			autocomplete="off">
		<button type="submit" class="hk-searchform__submit">
			<?php haka_icon( 'search', array( 'size' => 18 ) ); ?>
			<span class="screen-reader-text"><?php esc_html_e( 'Cari', 'haka' ); ?></span>
		</button>
	</div>
</form>
