<?php
/**
 * 支店: 施工イメージ帯。
 *
 * カンプ: PC 612:569（612:570〜612:574 の線画パース 5 枚 + 612:575 下端グラデ）
 *         SP 638:134〜638:136（2 枚 + 見切れ 1 枚。グラデ無し）
 *         guide 617:18「エンドレスで自動スクロール(プランページと同じ)」
 *
 * 5 枚は大きさが不揃いで下端が揃う。幅・高さはカンプ 1920 での実測値を
 * --strip-w / --strip-h で渡し、CSS 側で単位（PC: --st-vu / SP: 縮小率）を掛ける。
 * 04 は PLANS の img list 04 と同じ画像を枠で切り抜く（--crop-*）。
 *
 * 流れる帯（roll）は PLANS の img list と同じ部品で動かす: 列（ul）に
 * data-plp-imglist を付けると js/plans-imglist.js が列を複製して右から左へ流し、
 * 親（.p-sstrip）が見える枠になる。動きを減らす設定・JS 無効ではカンプの並びで静止。
 *
 * PLANS（PACKAGE 下）でも使うため、$args['variant'] に 'plans' を渡すと修飾クラスが
 * 付き、写真は PACKAGE 下の 5 枚（03 だけ別）になる。流すかどうかは $args['roll']
 *（既定: variant 無しなら流す）。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_strip_variant = isset( $args['variant'] ) ? (string) $args['variant'] : '';
$exterior_exone_strip_roll    = isset( $args['roll'] ) ? (bool) $args['roll'] : '' === $exterior_exone_strip_variant;

$exterior_exone_strip_class = 'p-sstrip';

if ( $exterior_exone_strip_variant ) {
	$exterior_exone_strip_class .= ' p-sstrip--' . $exterior_exone_strip_variant;
}

if ( $exterior_exone_strip_roll ) {
	$exterior_exone_strip_class .= ' p-sstrip--roll';
}
?>
<div
	class="<?php echo esc_attr( $exterior_exone_strip_class ); ?>"
	data-section="store-strip"
	aria-hidden="true"
>
	<ul class="p-sstrip__list"<?php echo $exterior_exone_strip_roll ? ' data-plp-imglist' : ''; ?>>
		<?php
		foreach ( exterior_exone_store_strip_images( $exterior_exone_strip_variant ) as $exterior_exone_strip ) :
			$exterior_exone_strip_style = '--strip-w:' . $exterior_exone_strip['width'] . ';--strip-h:' . $exterior_exone_strip['height'];
			$exterior_exone_strip_size  = array( (int) round( $exterior_exone_strip['width'] ), (int) round( $exterior_exone_strip['height'] ) );

			// 枠で切り抜く 1 枚（04。PLANS の img list と同じ）。width / height は画像そのものの寸法。
			if ( isset( $exterior_exone_strip['crop'] ) ) {
				$exterior_exone_strip_style .= ';--crop-w:' . $exterior_exone_strip['crop'][0] . ';--crop-x:' . $exterior_exone_strip['crop'][1] . ';--crop-y:' . $exterior_exone_strip['crop'][2];
				$exterior_exone_strip_size   = $exterior_exone_strip['img'];
			}
			?>
			<li
				class="p-sstrip__item<?php echo isset( $exterior_exone_strip['crop'] ) ? ' p-sstrip__item--crop' : ''; ?>"
				style="<?php echo esc_attr( $exterior_exone_strip_style ); ?>"
			>
				<img
					src="<?php echo esc_url( isset( $exterior_exone_strip['url'] ) ? $exterior_exone_strip['url'] : exterior_exone_store_image( $exterior_exone_strip['file'] ) ); ?>"
					width="<?php echo esc_attr( (string) $exterior_exone_strip_size[0] ); ?>"
					height="<?php echo esc_attr( (string) $exterior_exone_strip_size[1] ); ?>"
					alt=""
					loading="lazy"
				>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
