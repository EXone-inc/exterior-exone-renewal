<?php
/**
 * フォールバックテンプレート。
 *
 * 専用テンプレートを持たない表示はすべてここに来る。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="l-section">
	<div class="l-inner">
		<?php if ( have_posts() ) : ?>
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article <?php post_class( 'entry' ); ?>>
					<h1 class="c-display c-display--lg">
						<?php if ( is_singular() ) : ?>
							<?php the_title(); ?>
						<?php else : ?>
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						<?php endif; ?>
					</h1>

					<div class="entry-content">
						<?php
						if ( is_singular() ) {
							the_content();
						} else {
							the_excerpt();
						}
						?>
					</div>
				</article>
				<?php
			endwhile;

			the_posts_pagination();
			?>
		<?php else : ?>
			<p>コンテンツがありません。</p>
		<?php endif; ?>
	</div>
</div>

<?php
get_footer();
