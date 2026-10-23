<?php
/**
 * Widget area dan widget bawaan theme.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registrasi sidebar.
 */
function haka_widgets_init() {
	register_sidebar(
		array(
			'name'          => __( 'Sidebar Utama', 'haka' ),
			'id'            => 'sidebar-primary',
			'description'   => __( 'Tampil di samping konten pada homepage, arsip dan artikel.', 'haka' ),
			'before_widget' => '<section id="%1$s" class="widget hk-widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="hk-widget__title">',
			'after_title'   => '</h2>',
		)
	);

	for ( $i = 1; $i <= 3; $i++ ) {
		register_sidebar(
			array(
				/* translators: %d: nomor kolom footer */
				'name'          => sprintf( __( 'Footer Kolom %d', 'haka' ), $i ),
				'id'            => 'footer-' . $i,
				'description'   => __( 'Area widget di footer.', 'haka' ),
				'before_widget' => '<section id="%1$s" class="widget hk-footer__widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h2 class="hk-footer__title">',
				'after_title'   => '</h2>',
			)
		);
	}
}
add_action( 'widgets_init', 'haka_widgets_init' );

/**
 * Widget: Berita Terpopuler dengan nomor ranking.
 */
class Haka_Popular_Widget extends WP_Widget {

	/**
	 * Konstruktor.
	 */
	public function __construct() {
		parent::__construct(
			'haka_popular',
			__( 'Haka: Terpopuler', 'haka' ),
			array(
				'description' => __( 'Daftar artikel terpopuler dengan nomor ranking.', 'haka' ),
				'classname'   => 'hk-widget--popular',
			)
		);
	}

	/**
	 * Render widget.
	 *
	 * @param array $args     Argumen sidebar.
	 * @param array $instance Setting widget.
	 */
	public function widget( $args, $instance ) {
		// Hormati saklar fitur di Customizer, bukan hanya cabang fallback sidebar.
		if ( ! haka_enabled( 'popular_enable' ) || ! haka_has_enough_posts( 3 ) ) {
			return;
		}

		$title  = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Terpopuler', 'haka' );
		$number = ! empty( $instance['number'] ) ? absint( $instance['number'] ) : (int) haka_get_option( 'popular_count', 5 );

		$query = haka_query_popular( $number );

		if ( ! $query->have_posts() ) {
			wp_reset_postdata();
			return;
		}

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $args['before_title'] . esc_html( apply_filters( 'widget_title', $title, $instance, $this->id_base ) ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		echo '<ol class="hk-rank">';

		$rank = 0;

		while ( $query->have_posts() ) {
			$query->the_post();
			$rank++;
			?>
			<li class="hk-rank__item">
				<span class="hk-rank__num" aria-hidden="true"><?php echo esc_html( str_pad( (string) $rank, 2, '0', STR_PAD_LEFT ) ); ?></span>
				<div class="hk-rank__body">
					<h3 class="hk-rank__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
					<?php haka_post_meta( array( 'author' => false, 'class' => 'hk-meta hk-meta--sm' ) ); ?>
				</div>
			</li>
			<?php
		}

		echo '</ol>';
		wp_reset_postdata();

		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Form pengaturan.
	 *
	 * @param array $instance Setting.
	 */
	public function form( $instance ) {
		$title  = isset( $instance['title'] ) ? $instance['title'] : __( 'Terpopuler', 'haka' );
		$number = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Judul:', 'haka' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"><?php esc_html_e( 'Jumlah artikel:', 'haka' ); ?></label>
			<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" type="number" step="1" min="1" max="10" value="<?php echo esc_attr( $number ); ?>" size="3">
		</p>
		<?php
	}

	/**
	 * Simpan setting.
	 *
	 * @param array $new_instance Data baru.
	 * @param array $old_instance Data lama.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		return array(
			'title'  => sanitize_text_field( $new_instance['title'] ?? '' ),
			'number' => min( 10, max( 1, absint( $new_instance['number'] ?? 5 ) ) ),
		);
	}
}

/**
 * Widget: Tag Terpopuler berbentuk pill.
 */
class Haka_Tags_Widget extends WP_Widget {

	/**
	 * Konstruktor.
	 */
	public function __construct() {
		parent::__construct(
			'haka_tags',
			__( 'Haka: Tag Terpopuler', 'haka' ),
			array(
				'description' => __( 'Tag populer dalam bentuk pill/chip.', 'haka' ),
				'classname'   => 'hk-widget--tags',
			)
		);
	}

	/**
	 * Render widget.
	 *
	 * @param array $args     Argumen sidebar.
	 * @param array $instance Setting widget.
	 */
	public function widget( $args, $instance ) {
		if ( ! haka_enabled( 'tagcloud_enable' ) ) {
			return;
		}

		$title  = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Tag Terpopuler', 'haka' );
		$number = ! empty( $instance['number'] ) ? absint( $instance['number'] ) : 18;
		$tags   = haka_get_popular_tags( $number );

		if ( empty( $tags ) ) {
			return;
		}

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $args['before_title'] . esc_html( apply_filters( 'widget_title', $title, $instance, $this->id_base ) ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		echo '<ul class="hk-tags">';

		foreach ( $tags as $tag ) {
			printf(
				'<li><a class="hk-tag" href="%1$s">#%2$s</a></li>',
				esc_url( get_tag_link( $tag ) ),
				esc_html( $tag->name )
			);
		}

		echo '</ul>';
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Form pengaturan.
	 *
	 * @param array $instance Setting.
	 */
	public function form( $instance ) {
		$title  = isset( $instance['title'] ) ? $instance['title'] : __( 'Tag Terpopuler', 'haka' );
		$number = isset( $instance['number'] ) ? absint( $instance['number'] ) : 18;
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Judul:', 'haka' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"><?php esc_html_e( 'Jumlah tag:', 'haka' ); ?></label>
			<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" type="number" step="1" min="4" max="40" value="<?php echo esc_attr( $number ); ?>" size="3">
		</p>
		<?php
	}

	/**
	 * Simpan setting.
	 *
	 * @param array $new_instance Data baru.
	 * @param array $old_instance Data lama.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		return array(
			'title'  => sanitize_text_field( $new_instance['title'] ?? '' ),
			'number' => min( 40, max( 4, absint( $new_instance['number'] ?? 18 ) ) ),
		);
	}
}

/**
 * Widget: Berita Terbaru dengan thumbnail.
 */
class Haka_Recent_Widget extends WP_Widget {

	/**
	 * Konstruktor.
	 */
	public function __construct() {
		parent::__construct(
			'haka_recent',
			__( 'Haka: Berita Terbaru', 'haka' ),
			array(
				'description' => __( 'Daftar berita terbaru lengkap dengan thumbnail.', 'haka' ),
				'classname'   => 'hk-widget--recent',
			)
		);
	}

	/**
	 * Render widget.
	 *
	 * @param array $args     Argumen sidebar.
	 * @param array $instance Setting widget.
	 */
	public function widget( $args, $instance ) {
		$title    = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Rekomendasi', 'haka' );
		$number   = ! empty( $instance['number'] ) ? absint( $instance['number'] ) : 5;
		$category = ! empty( $instance['category'] ) ? absint( $instance['category'] ) : 0;

		$query_args = haka_base_query_args( array( 'posts_per_page' => $number ) );

		if ( $category ) {
			$query_args['cat'] = $category;
		}

		$query = new WP_Query( $query_args );

		if ( ! $query->have_posts() ) {
			wp_reset_postdata();
			return;
		}

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $args['before_title'] . esc_html( apply_filters( 'widget_title', $title, $instance, $this->id_base ) ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		echo '<ul class="hk-minilist">';

		while ( $query->have_posts() ) {
			$query->the_post();
			echo '<li class="hk-minilist__item">';
			haka_post_thumbnail( 'haka-thumb', array( 'class' => 'hk-minilist__thumb' ) );
			echo '<div class="hk-minilist__body">';
			printf( '<h3 class="hk-minilist__title"><a href="%1$s">%2$s</a></h3>', esc_url( get_permalink() ), esc_html( get_the_title() ) );
			haka_post_meta(
				array(
					'author' => false,
					'class'  => 'hk-meta hk-meta--sm',
				)
			);
			echo '</div></li>';
		}

		echo '</ul>';
		wp_reset_postdata();

		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Form pengaturan.
	 *
	 * @param array $instance Setting.
	 */
	public function form( $instance ) {
		$title    = isset( $instance['title'] ) ? $instance['title'] : __( 'Rekomendasi', 'haka' );
		$number   = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
		$category = isset( $instance['category'] ) ? absint( $instance['category'] ) : 0;
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Judul:', 'haka' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"><?php esc_html_e( 'Jumlah artikel:', 'haka' ); ?></label>
			<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" type="number" step="1" min="1" max="10" value="<?php echo esc_attr( $number ); ?>" size="3">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'category' ) ); ?>"><?php esc_html_e( 'Kategori:', 'haka' ); ?></label>
			<?php
			wp_dropdown_categories(
				array(
					'show_option_all' => __( 'Semua kategori', 'haka' ),
					'selected'        => $category,
					'name'            => $this->get_field_name( 'category' ),
					'id'              => $this->get_field_id( 'category' ),
					'class'           => 'widefat',
					'taxonomy'        => 'category',
					'hide_empty'      => false,
				)
			);
			?>
		</p>
		<?php
	}

	/**
	 * Simpan setting.
	 *
	 * @param array $new_instance Data baru.
	 * @param array $old_instance Data lama.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		return array(
			'title'    => sanitize_text_field( $new_instance['title'] ?? '' ),
			'number'   => min( 10, max( 1, absint( $new_instance['number'] ?? 5 ) ) ),
			'category' => absint( $new_instance['category'] ?? 0 ),
		);
	}
}

/**
 * Daftarkan widget theme.
 */
function haka_register_widgets() {
	register_widget( 'Haka_Popular_Widget' );
	register_widget( 'Haka_Tags_Widget' );
	register_widget( 'Haka_Recent_Widget' );
}
add_action( 'widgets_init', 'haka_register_widgets' );
