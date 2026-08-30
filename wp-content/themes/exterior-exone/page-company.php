<?php
/**
 * 企業情報ページ（固定ページ company）。
 *
 * カンプ: PC 917:1408 / SP 917:901
 * PC と SP でセクション構成が異なるため、両方を出力して切り替える。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$exterior_exone_company_sections = array(
	'fv',
	'why',
	'experience', // PC のみ（ブランドストーリー + VISION/MISSION）
	'visible',    // PC のみ（見える価格/提案/施工管理/品質基準）
	'principles',
	'quality',
	'ecosystem',
	'partner',
	'profile',
);

foreach ( $exterior_exone_company_sections as $exterior_exone_section ) {
	get_template_part( 'template-parts/company/section', $exterior_exone_section );
}

get_footer();
