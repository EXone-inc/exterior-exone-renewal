<?php
/**
 * ハイエンドページ（固定ページ high-end。親 plans → /plans/high-end/）。
 *
 * カンプ: PC 713:31（1920x13761）/ SP 733:290（375x7164）/ guide 733:415
 * 設計メモ: docs/figma-highend-page.md
 * 仕様書: docs/spec-20261005-highend-page.md（決定事項 docs/spec-20261005-highend-page-decisions.md）
 *
 * スラッグ high-end の固定ページに WordPress のテンプレート階層で自動的に当たる。
 * 文言・画像は inc/highend-data.php。各セクションには $args['data'] で渡す。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// FV は画面 2 つ分の走路をスクロールする間その場に留まり、夕方 → 夜 → 暗幕 → コピーと
// 移る（guide 713:213 / 713:218）。進捗は js/fv-mask.js（data-fv-mask-span のオプトイン）が
// .l-pin の 1 つ目（舞台）と 2 つ目（走路）から算出する。
?>
<div class="l-pin p-hepin" data-fv-mask-span="2">
	<?php get_template_part( 'template-parts/highend/section', 'fv', array( 'data' => exterior_exone_highend_fv() ) ); ?>
	<div class="p-hepfv__runway" aria-hidden="true"></div>
</div>
<?php

// 設計メモ §0 の並び。未実装のセクションは get_template_part が黙って飛ばす。
$exterior_exone_highend_sections = array(
	'philosophy' => 'exterior_exone_highend_philosophy',
	'details'    => 'exterior_exone_highend_details',
	'plans'      => 'exterior_exone_highend_plans',
	'dx'         => 'exterior_exone_highend_dx',
	'gallery'    => 'exterior_exone_highend_gallery',
	'contact'    => 'exterior_exone_highend_contact',
);

foreach ( $exterior_exone_highend_sections as $exterior_exone_section => $exterior_exone_data_fn ) {
	get_template_part(
		'template-parts/highend/section',
		$exterior_exone_section,
		array( 'data' => call_user_func( $exterior_exone_data_fn ) )
	);
}

get_footer();
