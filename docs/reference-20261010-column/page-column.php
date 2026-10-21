<?php
/*
Template Name: page-column
*/
?>

<?php get_header(); ?>

<div id="page_column">
	<section class="column" id="column">
		<h1 class="c_title">
			<span class="c_title_item">Column</span>
			<span class="c_title-item-bottom">外構・エクステリアの<br>お役立ち情報コラム</span>
		</h1>

		<?php
		// カテゴリ絞り込み（?cat=）。クエリ変数の解釈・件数・WP_Query は functions.php の exone_column_* に集約
		$current_cat = exone_column_current_cat();
		// ループ内で the_post() を回すと get_permalink() が記事側を指すため、固定ページ URL を先に確保
		$column_page_url = get_permalink();
		$column_terms = get_terms(array(
			'taxonomy'   => 'column_category',
			'hide_empty' => false,
		));
		?>
		<div class="c_search_head">
			<p class="c_search_head-title">カテゴリーで絞り込む</p>
			<div class="c_search_type-tab">
				<a href="<?php echo esc_url(get_permalink()); ?>" class="c_search_type-item<?php echo ($current_cat === '') ? ' active' : ''; ?>">すべて</a>
				<?php if (!empty($column_terms) && !is_wp_error($column_terms)) : ?>
					<?php foreach ($column_terms as $column_term) : ?>
						<a href="<?php echo esc_url(add_query_arg('cat', $column_term->slug, get_permalink())); ?>" class="c_search_type-item<?php echo ($current_cat === $column_term->slug) ? ' active' : ''; ?>"><?php echo esc_html($column_term->name); ?></a>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</div>

		<div class="c_list_gallery">
			<?php
			// /column/page/N/ の N（リライトルールで paged に載る）。1 ページ 10 件。
			// 範囲外の N は functions.php 側の template_redirect で 404 済みなのでここには来ない
			$paged        = exone_column_current_paged();
			$column_query = exone_column_list_query();
			?>
			<?php if ($column_query->have_posts()) : ?>
				<div class="c_gallery_list">
					<?php while ($column_query->have_posts()) : $column_query->the_post(); ?>

						<article class="c_gallery_list-item fade-up">
							<a href="<?php the_permalink(); ?>" class="c_gallery_link">
								<figure class="c_gallery_image">
									<?php if (has_post_thumbnail()) : ?>
										<?php the_post_thumbnail('medium_large', array('class' => 'c_gallery_thumb', 'loading' => 'lazy')); ?>
									<?php else : ?>
										<span class="c_gallery_thumb c_gallery_thumb--noimage" role="img" aria-label="NO IMAGE">NO IMAGE</span>
									<?php endif; ?>
								</figure>
								<div class="c_gallery_body">
									<div class="c_gallery_meta">
										<div class="c_gallery_dates">
											<time class="c_gallery_date c_gallery_date--posted" datetime="<?php echo esc_attr(get_the_date('Y-m-d')); ?>">投稿日:<?php echo esc_html(get_the_date('Y.m.d')); ?></time>
											<time class="c_gallery_date c_gallery_date--mod" datetime="<?php echo esc_attr(get_the_modified_date('Y-m-d')); ?>">最終更新日:<?php echo esc_html(get_the_modified_date('Y.m.d')); ?></time>
										</div>
										<?php
										$terms = get_the_terms(get_the_ID(), 'column_category');
										if (!empty($terms) && !is_wp_error($terms)) :
										?>
											<div class="c_gallery_cats">
												<?php foreach ($terms as $column_cat_term) : ?>
													<span class="c_gallery_cat"><?php echo esc_html($column_cat_term->name); ?></span>
												<?php endforeach; ?>
											</div>
										<?php endif; ?>
									</div>
									<h2 class="c_gallery_caption-title"><?php the_title(); ?></h2>
									<?php
									// 本文は wp:html ブロックのため get_the_excerpt() は空になる。本文から直接抜粋を作る。
									$col_excerpt = has_excerpt() ? get_the_excerpt() : get_the_content();
									$col_excerpt = preg_replace( '/<!--.*?-->/s', '', $col_excerpt );        // ブロック区切り等のHTMLコメント除去
									$col_excerpt = wp_strip_all_tags( strip_shortcodes( $col_excerpt ), true ); // <style>/<script>・タグ除去＋空白整理
									$col_excerpt_limit = 90;
									if ( mb_strlen( $col_excerpt ) > $col_excerpt_limit ) {
										$col_excerpt = mb_substr( $col_excerpt, 0, $col_excerpt_limit ) . '…';
									}
									if ( $col_excerpt !== '' ) :
									?>
										<p class="c_gallery_excerpt"><?php echo esc_html( $col_excerpt ); ?></p>
									<?php endif; ?>
								</div>
							</a>
						</article>

					<?php endwhile; ?>
				</div>

				<?php
				if ($column_query->max_num_pages > 1) :
					// 固定ページ /column/page/N/ 形式。%_% を base に置き、1ページ目は /column/ に戻す
					// （前へリンクが /column/page/1/ ではなく /column/ を指す）。?cat= 併用時は cat を引き継ぐ
					$pagenavi_base = trailingslashit($column_page_url) . '%_%';
					$add_args = ($current_cat !== '') ? array('cat' => $current_cat) : array();
					$pagenavi_links = paginate_links(array(
						'base'      => $pagenavi_base,
						'format'    => 'page/%#%/',
						'current'   => $paged,
						'total'     => $column_query->max_num_pages,
						'mid_size'  => 1,
						'end_size'  => 1,
						'type'      => 'list',
						'prev_text' => '<span class="c_pagenavi_arrow">前へ</span>',
						'next_text' => '<span class="c_pagenavi_arrow">次へ</span>',
						'add_args'  => $add_args,
					));
					if ($pagenavi_links) :
				?>
					<nav class="c_pagenavi" aria-label="ページ送り">
						<?php echo $pagenavi_links; ?>
					</nav>
				<?php
					endif;
				endif;
				?>
				<?php wp_reset_postdata(); ?>
			<?php else : ?>
				<p class="c_no_posts">記事がありません。</p>
			<?php endif; ?>
		</div>
	</section>
</div>

<?php get_footer(); ?>
