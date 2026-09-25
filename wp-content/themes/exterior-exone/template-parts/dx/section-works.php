<?php
/**
 * DX: WORKS。
 *
 * カンプ: PC 437:460（436:326-341 / 437:462 / 437:463）/
 *         SP 439:992-1019 + 見積モーダル 439:1020-1062
 *
 * 中身は支店ページと同じ共通部品（template-parts/common/section-works.php）。
 * ここは DX 側の文言・写真・3D 枠の下敷きを渡すだけで、配色の差分
 *（見出し帯 #454545・罫線 #dadada・ラベル 232x55）は .p-cworks--dx が持つ。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_dx_works = exterior_exone_dx_works();

get_template_part(
	'template-parts/common/section',
	'works',
	array(
		'variant'  => 'dx',
		// カンプの写真を固定で出し、右列を押すとメインが切り替わる（2026-09-25）。
		'gallery'  => true,
		'section'  => 'dx-works',
		'title'    => $exterior_exone_dx_works['title'],
		'jp'       => $exterior_exone_dx_works['jp'],
		'lead'     => $exterior_exone_dx_works['lead'],
		'lead_sp'  => $exterior_exone_dx_works['lead_sp'],
		'photos'   => array_map( 'exterior_exone_dx_image', $exterior_exone_dx_works['photos'] ),
		'thumbs'   => array_map( 'exterior_exone_dx_image', $exterior_exone_dx_works['thumbs'] ),
		'render'   => array(
			'image'    => exterior_exone_dx_image( $exterior_exone_dx_works['render']['image'] ),
			'label'    => $exterior_exone_dx_works['render']['label'],
			'backdrop' => exterior_exone_dx_image( $exterior_exone_dx_works['render']['backdrop'] ),
			// 押したときにメインでドラッグして回せる 3D（js/works-gallery.js が読み込む）。
			'model'    => array_merge(
				$exterior_exone_dx_works['render']['model'],
				array(
					'file'   => exterior_exone_asset_url( $exterior_exone_dx_works['render']['model']['file'] ),
					'viewer' => exterior_exone_asset_url( 'js/vendor/model-viewer/model-viewer-umd.min.js' ),
					'draco'  => trailingslashit( get_theme_file_uri( 'js/vendor/draco' ) ),
				)
			),
		),
		'request'  => $exterior_exone_dx_works['request'],
		'proposal' => $exterior_exone_dx_works['proposal'],
		'estimate' => $exterior_exone_dx_works['estimate'],
		'total'    => $exterior_exone_dx_works['total'],
		'modal'    => array_merge(
			$exterior_exone_dx_works['modal'],
			array( 'image' => exterior_exone_dx_image( $exterior_exone_dx_works['modal']['image'] ) )
		),
	)
);
