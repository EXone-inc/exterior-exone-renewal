<?php
/**
 * 支店: COLUMN。共通部品 template-parts/common/section-column.php（CSS は css/column.css）を
 * 支店の文言（「<地域>の外構づくりに役立つ情報」）で呼ぶ。
 *
 * カンプ: PC 612:869 / SP 641:1447〜641:1459
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_store = isset( $args['store'] ) ? $args['store'] : exterior_exone_current_store();

if ( ! $exterior_exone_store ) {
	return;
}

get_template_part(
	'template-parts/common/section',
	'column',
	array(
		'column'  => exterior_exone_store_column( $exterior_exone_store ),
		'section' => 'store-column',
	)
);
