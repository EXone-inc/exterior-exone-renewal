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

// FV は Why We Exist が重なる間その場に留まる（TOP と同じ、固定された FV に
// 暗幕がかぶさる演出）。sticky の効き幅をこの 2 セクションに限るため、ここだけ包む。
// 暗転の進捗は js/fv-mask.js が .l-pin の 1 つ目・2 つ目の子から算出する。
?>
<div class="l-pin">
	<?php
	get_template_part( 'template-parts/company/section', 'fv' );
	get_template_part( 'template-parts/company/section', 'why' );
	?>
</div>
<?php

$exterior_exone_company_sections = array(
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
