<?php
/**
 * TOP ページ。
 *
 * 各セクションは template-parts/top/ に分割し、ここでは読み込み順のみを持つ。
 * セクションの中身は S2 以降のスプリントで実装する。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// FV は理念セクションが重なる間その場に留まる（スクロールで固定された FV に暗幕が
// かぶさる演出）。sticky の効き幅を 2 セクションに限るため、ここだけ包む。
?>
<div class="l-pin">
	<?php
	get_template_part( 'template-parts/top/section', 'fv' );         // S2
	get_template_part( 'template-parts/top/section', 'philosophy' ); // S3（実績数値バンド section-stats を内包）
	?>
</div>
<?php

$exterior_exone_top_sections = array(
	'dx',      // S4
	'vr',      // 表示テスト用（VR ショールーム）
	'copy',    // S5
	'plans',   // S5
	'works',   // S6
	'news',    // S7
	'recruit', // S7
);

foreach ( $exterior_exone_top_sections as $exterior_exone_section ) {
	get_template_part( 'template-parts/top/section', $exterior_exone_section );
}

get_footer();
