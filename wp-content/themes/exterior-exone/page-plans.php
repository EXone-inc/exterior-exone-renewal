<?php
/**
 * PLANS ページ（固定ページ plans）。
 *
 * カンプ: PC 586:390（1920x10871）/ SP 586:850（375x5949）/ guide 586:620
 * 設計メモ: docs/figma-plans-page.md
 * 仕様書: docs/spec-20260930-plans-page.md（決定事項 docs/spec-20260930-plans-page-decisions.md）
 *
 * スラッグ plans の固定ページに WordPress のテンプレート階層で自動的に当たるため、
 * template_include での振り分けは要らない（DX と同じ）。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// F-10: プランの切り替えタブ（ヘッダーの下に固定。選ぶまでは出さない）。
get_template_part( 'template-parts/plans/tabs' );

// 設計メモ §0 の並び。未実装のセクションは get_template_part が黙って飛ばす。
// JS が動かないときはこの並びのまま縦に全部出る（決定 B）。
$exterior_exone_plans_sections = array(
	'fv',      // F-03
	'select',  // F-04・F-09: PLAN select（左右 2 分割）
	'package', // F-05・F-12・F-13: PACKAGE PLAN の詳細
	'highend', // F-06・F-14: HIGH-END PLAN の詳細
	'process', // F-07: Design Process（共通）
	'beyond',  // F-08・F-15: Beyond the Plan（共通）
);

foreach ( $exterior_exone_plans_sections as $exterior_exone_section ) {
	get_template_part( 'template-parts/plans/section', $exterior_exone_section );
}

get_footer();
