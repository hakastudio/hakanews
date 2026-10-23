<?php
/**
 * Single post (artikel berita).
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="primary" class="site-main hk-single" role="main">
	<div class="hk-container">
		<div class="hk-layout">

			<div class="hk-layout__main">
				<?php
				while ( have_posts() ) :
					the_post();

					$haka_author_id = (int) get_post_field( 'post_author', get_the_ID() );
					$haka_modified  = get_the_modified_time( 'U' );
					$haka_published = get_the_time( 'U' );
					?>

					<article id="<?php echo esc_attr( haka_entry_id() ); ?>" <?php post_class( 'hk-article' ); ?>>

						<header class="hk-article__header">
							<?php haka_breadcrumbs(); ?>

							<div class="hk-article__badges">
								<?php haka_category_badge( get_the_ID() ); ?>
							</div>

							<h1 class="hk-article__title"><?php the_title(); ?></h1>

							<?php if ( has_excerpt() ) : ?>
								<p class="hk-article__lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
							<?php endif; ?>

							<?php if ( haka_enabled( 'updated_badge' ) && $haka_modified > $haka_published + MINUTE_IN_SECONDS * 5 ) : ?>
								<p class="hk-article__updated">
									<?php haka_icon( 'clock', array( 'size' => 14 ) ); ?>
									<?php
									printf(
										/* translators: %s: tanggal pembaruan */
										esc_html__( 'Diperbarui %s', 'haka' ),
										esc_html( get_the_modified_date() )
									);
									?>
								</p>
							<?php endif; ?>

							<div class="hk-article__byline">
								<div class="hk-article__avatar"><?php echo get_avatar( $haka_author_id, 44 ); ?></div>
								<div class="hk-article__bylinetext">
									<p class="hk-article__author">
										<a href="<?php echo esc_url( get_author_posts_url( $haka_author_id ) ); ?>" rel="author">
											<?php echo esc_html( get_the_author_meta( 'display_name', $haka_author_id ) ); ?>
										</a>
									</p>
									<p class="hk-article__dates">
										<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>">
											<?php
											printf(
												'%s, %s',
												esc_html( haka_absolute_date() ),
												esc_html( get_the_time() )
											);
											?>
										</time>

										<?php do_action( 'haka_article_meta_views' ); ?>

										<?php if ( haka_enabled( 'reading_time' ) ) : ?>
											<span class="hk-article__reading">
												<?php
												printf(
													/* translators: %d: menit */
													esc_html__( '%d menit baca', 'haka' ),
													(int) haka_reading_time()
												);
												?>
											</span>
										<?php endif; ?>
									</p>
								</div>

								<?php
								if ( haka_enabled( 'font_resizer' ) && 'byline' === haka_get_option( 'fontsize_position', 'byline' ) ) {
									get_template_part( 'template-parts/post/fontsize', null, array( 'inline' => true ) );
								}
								?>
							</div>
						</header>

						<?php
						if ( haka_enabled( 'font_resizer' ) && 'block' === haka_get_option( 'fontsize_position', 'byline' ) ) {
							get_template_part( 'template-parts/post/fontsize' );
						}
						?>

						<?php if ( has_post_thumbnail() ) : ?>
							<figure class="hk-article__media">
								<?php
								the_post_thumbnail(
									'haka-hero',
									array(
										'decoding'      => 'async',
										'fetchpriority' => 'high',
										'sizes'         => '(max-width: 1024px) 100vw, 800px',
									)
								);
								?>
								<?php if ( get_the_post_thumbnail_caption() ) : ?>
									<figcaption class="hk-article__caption"><?php echo esc_html( get_the_post_thumbnail_caption() ); ?></figcaption>
								<?php endif; ?>
							</figure>
						<?php endif; ?>

						<?php do_action( 'haka_ad_slot', 'before_content' ); ?>

						<div class="hk-prose hk-article__content">
							<?php
							the_content();

							wp_link_pages(
								array(
									'before' => '<div class="hk-pagelinks"><span class="hk-pagelinks__label">' . esc_html__( 'Halaman:', 'haka' ) . '</span>',
									'after'  => '</div>',
								)
							);
							?>
						</div>

						<?php do_action( 'haka_ad_slot', 'after_content' ); ?>

						<?php do_action( 'haka_after_article_content' ); ?>

						<footer class="hk-article__footer">
							<?php get_template_part( 'template-parts/post/actions' ); ?>

							<?php
							$haka_tags = haka_enabled( 'post_tags' ) ? get_the_tags() : array();

							if ( ! empty( $haka_tags ) ) :
								?>
								<div class="hk-article__tags">
									<span class="hk-article__tagslabel">
										<?php haka_icon( 'tag', array( 'size' => 15 ) ); ?>
										<?php esc_html_e( 'Tag', 'haka' ); ?>
									</span>
									<ul class="hk-tags">
										<?php foreach ( $haka_tags as $haka_tag ) : ?>
											<li>
												<a class="hk-tag" href="<?php echo esc_url( get_tag_link( $haka_tag ) ); ?>">
													#<?php echo esc_html( $haka_tag->name ); ?>
												</a>
											</li>
										<?php endforeach; ?>
									</ul>
								</div>
							<?php endif; ?>

							<?php
							$haka_cats = get_the_category();

							if ( count( $haka_cats ) > 1 ) :
								?>
								<div class="hk-article__cats">
									<span class="hk-article__tagslabel">
										<?php haka_icon( 'folder', array( 'size' => 15 ) ); ?>
										<?php esc_html_e( 'Kategori', 'haka' ); ?>
									</span>
									<ul class="hk-tags">
										<?php foreach ( $haka_cats as $haka_cat ) : ?>
											<li>
												<a class="hk-tag" href="<?php echo esc_url( get_category_link( $haka_cat ) ); ?>">
													<?php echo esc_html( $haka_cat->name ); ?>
												</a>
											</li>
										<?php endforeach; ?>
									</ul>
								</div>
							<?php endif; ?>
						</footer>

					</article>

					<?php
					if ( haka_enabled( 'share_enable' ) && haka_enabled( 'sticky_share' ) ) {
						get_template_part( 'template-parts/post/sticky-share' );
					}
					?>

					<?php
					$haka_bio = haka_enabled( 'authorbox_enable' ) ? get_the_author_meta( 'description', $haka_author_id ) : '';

					if ( $haka_bio ) :
						?>
						<section class="hk-authorbox">
							<div class="hk-authorbox__avatar"><?php echo get_avatar( $haka_author_id, 64 ); ?></div>
							<div class="hk-authorbox__body">
								<h2 class="hk-authorbox__name">
									<a href="<?php echo esc_url( get_author_posts_url( $haka_author_id ) ); ?>">
										<?php echo esc_html( get_the_author_meta( 'display_name', $haka_author_id ) ); ?>
									</a>
								</h2>
								<p class="hk-authorbox__bio"><?php echo esc_html( $haka_bio ); ?></p>
							</div>
						</section>
					<?php endif; ?>

					<?php if ( haka_enabled( 'postnav_enable' ) ) : ?>
					<nav class="hk-postnav" aria-label="<?php esc_attr_e( 'Navigasi artikel', 'haka' ); ?>">
						<?php
						$haka_prev = get_previous_post();
						$haka_next = get_next_post();
						?>

						<?php if ( $haka_prev ) : ?>
							<a class="hk-postnav__link hk-postnav__link--prev" href="<?php echo esc_url( get_permalink( $haka_prev ) ); ?>">
								<span class="hk-postnav__label"><?php haka_icon( 'chevron-left', array( 'size' => 14 ) ); ?><?php esc_html_e( 'Sebelumnya', 'haka' ); ?></span>
								<span class="hk-postnav__title"><?php echo esc_html( get_the_title( $haka_prev ) ); ?></span>
							</a>
						<?php else : ?>
							<span class="hk-postnav__link hk-postnav__link--empty" aria-hidden="true"></span>
						<?php endif; ?>

						<?php if ( $haka_next ) : ?>
							<a class="hk-postnav__link hk-postnav__link--next" href="<?php echo esc_url( get_permalink( $haka_next ) ); ?>">
								<span class="hk-postnav__label"><?php esc_html_e( 'Berikutnya', 'haka' ); ?><?php haka_icon( 'chevron-right', array( 'size' => 14 ) ); ?></span>
								<span class="hk-postnav__title"><?php echo esc_html( get_the_title( $haka_next ) ); ?></span>
							</a>
						<?php else : ?>
							<span class="hk-postnav__link hk-postnav__link--empty" aria-hidden="true"></span>
						<?php endif; ?>
					</nav>
					<?php endif; ?>

					<?php
					if ( haka_enabled( 'related_enable' ) ) {
						get_template_part( 'template-parts/post/related' );
					}

					if ( haka_enabled( 'recommended_posts' ) ) {
						get_template_part( 'template-parts/post/discover' );
					}
					?>

					<?php
					if ( haka_enabled( 'comments_enable' ) && ( comments_open() || get_comments_number() ) ) {
						comments_template();
					}

				endwhile;
				?>
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