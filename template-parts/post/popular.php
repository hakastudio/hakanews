<?php
/**
 * Daftar berita terpopuler dengan nomor ranking.
 *
 * @package Haka
 */

defined( 'ABSPATH' ) || exit;

$haka_number  = isset( $args['number'] ) ? absint( $args['number'] ) : 5;
$haka_popular = haka_query_popular( $haka_number );

if ( ! $haka_popular->have_posts() ) {
	wp_reset_postdata();
	return;
}

$haka_rank = 0;
?>
<ol class="hk-rank">
	<?php
	while ( $haka_popular->have_posts() ) :
		$haka_popular->the_post();
		$haka_rank++;
		?>
		<li class="hk-rank__item">
			<span class="hk-rank__num" aria-hidden="true"><?php echo esc_html( str_pad( (string) $haka_rank, 2, '0', STR_PAD_LEFT ) ); ?></span>
			<div class="hk-rank__body">
				<h3 class="hk-rank__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
				<?php
				haka_post_meta(
					array(
						'author' => false,
						'class'  => 'hk-meta hk-meta--sm',
					)
				);
				?>
			</div>
		</li>
		<?php
	endwhile;
	?>
</ol>
<?php
wp_reset_postdata();
