<?php
/**
 * WORKS 一覧（投稿タイプ works のアーカイブ /works/）。
 *
 * カンプ: PC 821:113（1920x4076）/ SP は 836:37（詳細）からパンフレットを除いて補間 / guide 821:337
 * 設計メモ: docs/figma-works-page.md
 * 仕様書: docs/spec-20261008-works-page.md（決定事項 docs/spec-20261008-works-page-decisions.md）
 *
 * FV → 一覧ブロック（見出し・絞り込み・写真グリッド）→ COLUMN。
 * 一覧は全件・日付の新しい順（inc/works-page-data.php の pre_get_posts）。ページ送りは無い。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

get_template_part(
	'template-parts/works/section',
	'fv',
	array(
		'data'      => exterior_exone_works_page_fv(),
		'title_tag' => 'h1',
	)
);
?>
<div class="p-wkp">
	<?php
	get_template_part(
		'template-parts/works/section',
		'list',
		array(
			'tastes' => exterior_exone_works_page_tastes(),
			'cards'  => array_map( 'exterior_exone_works_page_card', $wp_query->posts ),
			'filter' => exterior_exone_works_page_filter(),
		)
	);
	?>
</div>
<?php
// COLUMN（共通部品。支店モードは支店ページと同じ地域名入りの文言）。
$exterior_exone_wkp_store = exterior_exone_store_mode();

get_template_part(
	'template-parts/common/section',
	'column',
	array(
		'column'  => $exterior_exone_wkp_store ? exterior_exone_store_column( $exterior_exone_wkp_store ) : exterior_exone_works_page_column(),
		'section' => 'works-column',
	)
);

get_footer();
