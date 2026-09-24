<?php
/**
 * DX EXPERIENCE ページ（固定ページ dx）。
 *
 * カンプ: PC 436:95（1920x18029）/ SP 439:1070（本体 439:752 Frame 14・375 基準）
 * 設計メモ: docs/figma-dx-page.md
 * 仕様書: docs/spec-20260919-dx-page.md
 *
 * スラッグ dx の固定ページに WordPress のテンプレート階層で自動的に当たるため、
 * 支店ページのような template_include での振り分けは要らない。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// 設計メモ §0 の並び。未実装のセクションは get_template_part が黙って飛ばす。
$exterior_exone_dx_sections = array(
	'fv',       // F-03
	'why',      // F-04
	'explorer', // F-05・F-06: 本番映像と固定本文を一つの体験に統合。
	'steps',    // F-07・F-08・F-09
	'summary',  // F-10
	'works',    // F-11・F-12
);

foreach ( $exterior_exone_dx_sections as $exterior_exone_section ) {
	get_template_part( 'template-parts/dx/section', $exterior_exone_section );
}

get_footer();
