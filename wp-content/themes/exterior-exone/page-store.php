<?php
/**
 * 支店ページ（固定ページ store の子・7 拠点共通）。
 *
 * カンプ: PC 534:628（1920x28486・青森支店ベース）/ SP カンプなし
 * 設計メモ: docs/figma-store-page.md
 *
 * どの支店かは固定ページのスラッグで決まる（inc/store-data.php）。
 * このファイルの割り当ては functions.php の exterior_exone_store_template()。
 * 親ページ /store/ はここではなく page-store-index.php で描画する。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_store = exterior_exone_current_store();

get_header();

// 設計メモ §1〜§17 の順。
$exterior_exone_store_sections = array(
	'fv',      // F-03
	'intro',   // F-04
	'strip',   // F-05
	'stats',   // F-06
	'concern', // F-07
	'reasons', // F-08
	'service', // F-09
	'visible', // F-10
	'dx',      // F-10
	'plans',   // F-11
	'works',   // F-12
	'reviews', // F-13
	'flow',    // F-14
	'quality', // F-15
	'faq',     // F-16
	'area',    // F-17
	'cta',     // F-18
	'column',  // F-19
);

foreach ( $exterior_exone_store_sections as $exterior_exone_section ) {
	get_template_part(
		'template-parts/store/section',
		$exterior_exone_section,
		array( 'store' => $exterior_exone_store )
	);
}

get_footer();
