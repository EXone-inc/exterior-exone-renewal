<?php
/**
 * お知らせ一覧（投稿タイプ news のアーカイブ /news/）。
 *
 * カンプ: 無し。デザイン・構成の正はコラム一覧（archive-column.php）。クラス名と css/column-page.css を共有し、
 * お知らせだけの差分（説明文の全文・押せないカード）は css/news-page.css に持つ。
 * 仕様書: docs/spec-20261014-news-page.md（決定事項 docs/spec-20261014-news-page-decisions.md）
 *
 * 見出し → 年月の絞り込み → お知らせ 10 件（全文・押せないカード）→ ページ送り。
 * 詳細ページは無いので、カードにはリンクを置かない（個別 URL は一覧へ 301）。クエリ・?ym= の絞り込みは inc/news-page-data.php。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$exterior_exone_newsp_texts = exterior_exone_news_page_texts();
$exterior_exone_newsp_tabs  = exterior_exone_news_page_tabs();
$exterior_exone_newsp_cards = array_map( 'exterior_exone_news_page_card', $wp_query->posts );
$exterior_exone_newsp_pager = exterior_exone_news_page_pager( max( 1, (int) get_query_var( 'paged' ) ), (int) $wp_query->max_num_pages );
?>
<div class="p-colp p-newsp" data-section="news-list">
	<div class="p-colp__inner">
		<h1 class="p-colp__title">
			<span class="p-colp__title-en"><?php echo esc_html( $exterior_exone_newsp_texts['title'] ); ?></span>
			<span class="p-colp__title-jp"><?php echo esc_html( $exterior_exone_newsp_texts['lead'] ); ?></span>
		</h1>

		<div class="p-colp__body">
			<div class="p-colp__filter">
				<p class="p-colp__filter-head"><?php echo esc_html( $exterior_exone_newsp_texts['filter'] ); ?></p>
				<nav class="p-colp__tabs" aria-label="<?php echo esc_attr( $exterior_exone_newsp_texts['filter_aria'] ); ?>">
					<?php foreach ( $exterior_exone_newsp_tabs as $exterior_exone_newsp_tab ) : ?>
						<a
							class="p-colp__tab<?php echo $exterior_exone_newsp_tab['current'] ? ' is-current' : ''; ?>"
							href="<?php echo esc_url( $exterior_exone_newsp_tab['url'] ); ?>"
							<?php echo $exterior_exone_newsp_tab['current'] ? 'aria-current="page"' : ''; ?>
						><?php echo esc_html( $exterior_exone_newsp_tab['label'] ); ?></a>
					<?php endforeach; ?>
				</nav>
			</div>

			<?php if ( $exterior_exone_newsp_cards ) : ?>
				<div class="p-colp__list">
					<?php foreach ( $exterior_exone_newsp_cards as $exterior_exone_newsp_card ) : ?>
						<article class="p-colp__card" data-reveal>
							<div class="p-colp__link">
								<figure class="p-colp__thumb">
									<?php
									if ( $exterior_exone_newsp_card['image_id'] ) {
										echo wp_get_attachment_image(
											$exterior_exone_newsp_card['image_id'],
											'medium_large',
											false,
											array(
												'class'   => 'p-colp__img',
												'alt'     => '',
												'loading' => 'lazy',
												'sizes'   => '(min-width: 1920px) 400px, (min-width: 1025px) 21vw, 100vw',
											)
										);
									} else {
										echo '<span class="p-colp__img p-colp__img--empty"></span>';
									}
									?>
								</figure>
								<div class="p-colp__text">
									<div class="p-colp__meta">
										<time class="p-colp__date" datetime="<?php echo esc_attr( $exterior_exone_newsp_card['datetime'] ); ?>"><?php echo esc_html( $exterior_exone_newsp_card['date'] ); ?></time>
									</div>
									<h2 class="p-colp__card-title"><?php echo esc_html( $exterior_exone_newsp_card['title'] ); ?></h2>
									<?php if ( $exterior_exone_newsp_card['paragraphs'] ) : ?>
										<div class="p-colp__excerpt p-newsp__desc">
											<?php echo exterior_exone_news_paragraphs_html( $exterior_exone_newsp_card['paragraphs'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- 行ごとに esc_html 済み。 ?>
										</div>
									<?php endif; ?>
								</div>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<p class="p-colp__empty"><?php echo esc_html( $exterior_exone_newsp_texts['empty'] ); ?></p>
			<?php endif; ?>

			<?php if ( $exterior_exone_newsp_pager ) : ?>
				<nav class="p-colp__pager" aria-label="<?php echo esc_attr( $exterior_exone_newsp_texts['pager_aria'] ); ?>">
					<ul class="p-colp__pages">
						<?php foreach ( $exterior_exone_newsp_pager as $exterior_exone_newsp_item ) : ?>
							<li>
								<?php if ( 'current' === $exterior_exone_newsp_item['type'] ) : ?>
									<span class="p-colp__page is-current" aria-current="page"><?php echo esc_html( $exterior_exone_newsp_item['label'] ); ?></span>
								<?php elseif ( 'dots' === $exterior_exone_newsp_item['type'] ) : ?>
									<span class="p-colp__page p-colp__page--dots" aria-hidden="true"><?php echo esc_html( $exterior_exone_newsp_item['label'] ); ?></span>
								<?php else : ?>
									<a
										class="p-colp__page p-colp__page--<?php echo esc_attr( $exterior_exone_newsp_item['type'] ); ?>"
										href="<?php echo esc_url( $exterior_exone_newsp_item['url'] ); ?>"
										<?php echo 'prev' === $exterior_exone_newsp_item['type'] ? 'rel="prev"' : ''; ?>
										<?php echo 'next' === $exterior_exone_newsp_item['type'] ? 'rel="next"' : ''; ?>
									><?php echo esc_html( $exterior_exone_newsp_item['label'] ); ?></a>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</nav>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php
get_footer();
