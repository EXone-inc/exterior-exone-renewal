<?php
/**
 * 支店: WORKS。
 *
 * カンプ: PC 612:813（見出し 612:705 / 612:707 / 612:706）/ SP 641:362〜641:391 + モーダル 641:421
 *         guide 617:44「DX下部と同じ（上部テキストのみ違いあり）」
 *
 * DX ページ（template-parts/dx/section-works.php）と同じモードで共通部品を呼ぶ:
 * variant dx（配色・寸法も DX と同じ）・カンプ写真固定、右列を押すとメイン切替、
 * 3D モデル。違うのは見出し 3 点の文言だけ（inc/store-data.php）。
 * 支店側の上書きは css/store.css の .is-store-page .p-cworks（影・罫線）だけ。
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
		'variant'  => 'dx',
		'section'  => 'store-works',
		'title'    => $exterior_exone_store_works['title'],
		'jp'       => $exterior_exone_store_works['jp'],
		'lead'     => $exterior_exone_store_works['lead'],
		'lead_sp'  => $exterior_exone_store_works['lead_sp'],
		'photos'   => array_map( 'exterior_exone_dx_image', $exterior_exone_store_works['photos'] ),
		'thumbs'   => array_map( 'exterior_exone_dx_image', $exterior_exone_store_works['thumbs'] ),
		'render'   => array(
			'image'    => exterior_exone_dx_image( $exterior_exone_store_works['render']['image'] ),
			'label'    => $exterior_exone_store_works['render']['label'],
			'backdrop' => exterior_exone_dx_image( $exterior_exone_store_works['render']['backdrop'] ),
			'model'    => array_merge(
				$exterior_exone_store_works['render']['model'],
				array(
					'file'   => exterior_exone_asset_url( $exterior_exone_store_works['render']['model']['file'] ),
					'viewer' => exterior_exone_asset_url( 'js/vendor/model-viewer/model-viewer-umd.min.js' ),
					'draco'  => trailingslashit( get_theme_file_uri( 'js/vendor/draco' ) ),
				)
			),
		),
		'request'  => $exterior_exone_store_works['request'],
		'proposal' => $exterior_exone_store_works['proposal'],
		'estimate' => $exterior_exone_store_works['estimate'],
		'total'    => $exterior_exone_store_works['total'],
		'modal'    => array_merge(
			$exterior_exone_store_works['modal'],
			array( 'image' => exterior_exone_dx_image( $exterior_exone_store_works['modal']['image'] ) )
		),
	)
);
