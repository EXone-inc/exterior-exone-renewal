<?php
/**
 * 支店: DX EXPERIENCE。
 *
 * カンプ: PC 637:16 / SP 641:251〜641:267（guide 617:38「TOPと同じ内容、アニメーション」）
 *
 * TOP の DX（template-parts/top/section-dx.php + js/dx-slider.js）をそのまま使う
 *（決定 B「DX セクション」）。ピン留めしてスクロールで 01 → 03、動画・タグ・VIEW MORE
 * の遷移先も TOP と同じ。SP も TOP の SP を正とする。支店用に DOM・クラスは書き換えない。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_template_part( 'template-parts/top/section', 'dx' );
