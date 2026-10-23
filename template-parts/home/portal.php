<?php
/**
 * Susunan homepage portal berita.
 *
 * Dua kolom dimulai sejak hero: kolom kiri berisi seluruh section berita,
 * kolom kanan berisi sidebar yang menempel saat digulir.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;
?>
<main id="primary" class="site-main hk-home<?php echo haka_enabled( 'sidebar_enable' ) ? '' : ' hk-home--full'; ?>" role="main">

	<?php get_template_part( 'template-parts/home/trending' ); ?>

	<div class="hk-container">
		<div class="hk-layout">

			<div class="hk-layout__main">
				<?php
				$haka_sections = array(
					'hero_enable'      => 'template-parts/home/hero',
					'secondary_enable' => 'template-parts/home/secondary',
					'editors_enable'   => 'template-parts/home/editors',
					'multicat_enable'  => 'template-parts/home/category-blocks',
					'story_enable'     => 'template-parts/home/story',
					'dynamic_enable'   => 'template-parts/home/dynamic',
					'latest_enable'    => 'template-parts/home/latest',
					'recommend_enable' => 'template-parts/home/recommend',
					'video_enable'     => 'template-parts/home/video',
					'network_enable'   => 'template-parts/home/network',
				);

				foreach ( $haka_sections as $haka_flag => $haka_part ) {
					if ( haka_enabled( $haka_flag ) ) {
						get_template_part( $haka_part );
					}
				}
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
