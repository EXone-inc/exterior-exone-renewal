<?php
/**
 * 支店: WORKS。
 *
 * カンプ: PC 698:1134（背景）/ 698:1136-1138（見出し 3 点セット）/
 *         698:1181・698:1178・698:1180・1153:104 + 698:1197（写真グリッド）/
 *         Group 380・Group 382（要望・提案）/ Group 379（見積テーブル）
 *         SP カンプなし（グリッド・2 ボックスを縦積み。見積はモーダル）
 *
 * 中身は DX ページと同じ共通部品（template-parts/common/section-works.php）。
 * ここは支店側の文言・写真を渡すだけ。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_store_works = exterior_exone_store_works();

get_template_part(
	'template-parts/common/section',
	'works',
	array(
		'variant'  => 'store',
		'section'  => 'store-works',
		'title'    => $exterior_exone_store_works['title'],
		'jp'       => $exterior_exone_store_works['jp'],
		'lead'     => $exterior_exone_store_works['lead'],
		'photos'   => array_map( 'exterior_exone_store_image', $exterior_exone_store_works['photos'] ),
		'render'   => array(
			'image' => exterior_exone_store_image( $exterior_exone_store_works['render']['image'] ),
			'label' => $exterior_exone_store_works['render']['label'],
		),
		'request'  => $exterior_exone_store_works['request'],
		'proposal' => $exterior_exone_store_works['proposal'],
		'estimate' => $exterior_exone_store_works['estimate'],
		'total'    => $exterior_exone_store_works['total'],
		'modal'    => array_merge(
			$exterior_exone_store_works['modal'],
			// SP のモーダル上部には 3D パースを出す（支店は SP カンプが無いので DX と同じ組み）。
			array( 'image' => exterior_exone_store_image( $exterior_exone_store_works['render']['image'] ) )
		),
	)
);
