<?php
/**
 * コラム一覧（投稿タイプ column のアーカイブ /column/）。
 *
 * カンプ: ボード 875:50（指示書き 875:47 / 参考画像 875:30。見出し 875:36・875:39 / タブ列 875:43 / 記事リスト 875:31）
 * 仕様書: docs/spec-20261010-column-page.md（決定事項 docs/spec-20261010-column-page-decisions.md）
 * 構成要素は現サイトの一覧（docs/reference-20261010-column/page-column.php）と同じ。
 *
 * 見出し → カテゴリーの絞り込み（?cat=<スラッグ>。サーバー側）→ 記事カード 10 件 → ページ送り。
 * FV（写真帯）は置かない。クエリは inc/column-page-data.php の pre_get_posts。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$exterior_exone_colp_texts = exterior_exone_column_page_texts();
$exterior_exone_colp_tabs  = exterior_exone_column_page_tabs();
$exterior_exone_colp_query = function_exists( 'exterior_exone_coexistence_column_is_list' ) && exterior_exone_coexistence_column_is_list()
	? exterior_exone_coexistence_column_list_query()
	: $wp_query;
$exterior_exone_colp_cards = array_map( 'exterior_exone_column_page_card', $exterior_exone_colp_query->posts );
$exterior_exone_colp_pager = exterior_exone_column_page_pager( max( 1, (int) get_query_var( 'paged' ) ), (int) $exterior_exone_colp_query->max_num_pages );
?>
<div class="p-colp" data-section="column-list">
	<div class="p-colp__inner">
		<h1 class="p-colp__title">
			<span class="p-colp__title-en"><?php echo esc_html( $exterior_exone_colp_texts['title'] ); ?></span>
			<span class="p-colp__title-jp"><?php echo exterior_exone_highend_lines( $exterior_exone_colp_texts['lead'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses 済み。 ?></span>
		</h1>

		<div class="p-colp__body">
			<div class="p-colp__filter">
				<p class="p-colp__filter-head"><?php echo esc_html( $exterior_exone_colp_texts['filter'] ); ?></p>
				<nav class="p-colp__tabs" aria-label="<?php echo esc_attr( $exterior_exone_colp_texts['filter_aria'] ); ?>">
					<?php foreach ( $exterior_exone_colp_tabs as $exterior_exone_colp_tab ) : ?>
						<a
							class="p-colp__tab<?php echo $exterior_exone_colp_tab['current'] ? ' is-current' : ''; ?>"
							href="<?php echo esc_url( $exterior_exone_colp_tab['url'] ); ?>"
							<?php echo $exterior_exone_colp_tab['current'] ? 'aria-current="page"' : ''; ?>
						><?php echo esc_html( $exterior_exone_colp_tab['label'] ); ?></a>
					<?php endforeach; ?>
				</nav>
			</div>

			<?php if ( $exterior_exone_colp_cards ) : ?>
				<div class="p-colp__list">
					<?php foreach ( $exterior_exone_colp_cards as $exterior_exone_colp_card ) : ?>
						<article class="p-colp__card" data-reveal>
							<a class="p-colp__link" href="<?php echo esc_url( $exterior_exone_colp_card['url'] ); ?>">
								<figure class="p-colp__thumb">
									<?php
									if ( $exterior_exone_colp_card['image_id'] ) {
										echo wp_get_attachment_image(
											$exterior_exone_colp_card['image_id'],
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
										<time class="p-colp__date" datetime="<?php echo esc_attr( $exterior_exone_colp_card['datetime'] ); ?>"><?php echo esc_html( $exterior_exone_colp_card['date'] ); ?></time>
										<?php if ( $exterior_exone_colp_card['cats'] ) : ?>
											<span class="p-colp__cats">
												<?php foreach ( $exterior_exone_colp_card['cats'] as $exterior_exone_colp_cat ) : ?>
													<span class="p-colp__cat"><?php echo esc_html( $exterior_exone_colp_cat ); ?></span>
												<?php endforeach; ?>
											</span>
										<?php endif; ?>
									</div>
									<h2 class="p-colp__card-title"><?php echo esc_html( $exterior_exone_colp_card['title'] ); ?></h2>
									<?php if ( '' !== $exterior_exone_colp_card['excerpt'] ) : ?>
										<p class="p-colp__excerpt"><?php echo esc_html( $exterior_exone_colp_card['excerpt'] ); ?></p>
									<?php endif; ?>
								</div>
							</a>
						</article>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<p class="p-colp__empty"><?php echo esc_html( $exterior_exone_colp_texts['empty'] ); ?></p>
			<?php endif; ?>

			<?php if ( $exterior_exone_colp_pager ) : ?>
				<nav class="p-colp__pager" aria-label="<?php echo esc_attr( $exterior_exone_colp_texts['pager_aria'] ); ?>">
					<ul class="p-colp__pages">
						<?php foreach ( $exterior_exone_colp_pager as $exterior_exone_colp_item ) : ?>
							<li>
								<?php if ( 'current' === $exterior_exone_colp_item['type'] ) : ?>
									<span class="p-colp__page is-current" aria-current="page"><?php echo esc_html( $exterior_exone_colp_item['label'] ); ?></span>
								<?php elseif ( 'dots' === $exterior_exone_colp_item['type'] ) : ?>
									<span class="p-colp__page p-colp__page--dots" aria-hidden="true"><?php echo esc_html( $exterior_exone_colp_item['label'] ); ?></span>
								<?php else : ?>
									<a
										class="p-colp__page p-colp__page--<?php echo esc_attr( $exterior_exone_colp_item['type'] ); ?>"
										href="<?php echo esc_url( $exterior_exone_colp_item['url'] ); ?>"
										<?php echo 'prev' === $exterior_exone_colp_item['type'] ? 'rel="prev"' : ''; ?>
										<?php echo 'next' === $exterior_exone_colp_item['type'] ? 'rel="next"' : ''; ?>
									><?php echo esc_html( $exterior_exone_colp_item['label'] ); ?></a>
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
