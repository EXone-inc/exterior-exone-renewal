<?php
/**
 * WORKS 詳細（投稿タイプ works の個別 /works/<投稿名>/）。
 *
 * カンプ: PC 821:174（1920x5316）/ SP 836:37 / guide 821:336
 * 設計メモ: docs/figma-works-page.md
 * 仕様書: docs/spec-20261008-works-page.md F-04（決定事項 docs/spec-20261008-works-page-decisions.md）
 *
 * FV（一覧と同じ。「WORKS」は p）→ 詳細（見出し #works-detail・パンフレット・名前・サムネ・本文）
 * → 一覧ブロック（一覧と同じ部品。表示中の事例も含む）→ COLUMN。
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
		'title_tag' => 'p',
	)
);
?>
<div class="p-wkp">
	<?php
	while ( have_posts() ) {
		the_post();

		get_template_part(
			'template-parts/works/section',
			'detail',
			array(
				'data' => exterior_exone_works_page_detail( get_post() ),
			)
		);
	}

	get_template_part(
		'template-parts/works/section',
		'list',
		array(
			'tastes' => exterior_exone_works_page_tastes(),
			'cards'  => exterior_exone_works_page_cards(),
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
