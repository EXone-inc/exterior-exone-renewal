<?php
/**
 * 支店: 施工イメージ帯。
 *
 * カンプ: PC 535:860 / 669:916 / 535:861 / 669:920 / 535:871（線画パース 5 枚）
 *         535:869（下端を #f3f3f4 に溶かす縦グラデ）
 *         SP カンプなし（帯ごと画面幅に比例縮小）
 *
 * 5 枚は大きさが不揃いで下端が揃う。幅・高さはカンプ 1920 での実測値を
 * --strip-w / --strip-h で渡し、CSS 側で単位（PC: --st-vu / SP: 縮小率）を掛ける。
 *
 * PLANS（§9 の y12123〜12332）でも同じ 5 枚を使うため、$args['variant'] に
 * 'plans' を渡すと修飾クラスが付く（溶かす先の色と余白だけが変わる）。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_strip_variant = isset( $args['variant'] ) ? (string) $args['variant'] : '';
?>
<div
	class="p-sstrip<?php echo $exterior_exone_strip_variant ? ' p-sstrip--' . esc_attr( $exterior_exone_strip_variant ) : ''; ?>"
	data-section="store-strip"
	aria-hidden="true"
>
	<ul class="p-sstrip__list">
		<?php foreach ( exterior_exone_store_strip_images() as $exterior_exone_strip ) : ?>
			<li
				class="p-sstrip__item"
				style="--strip-w:<?php echo esc_attr( (string) $exterior_exone_strip['width'] ); ?>;--strip-h:<?php echo esc_attr( (string) $exterior_exone_strip['height'] ); ?>"
			>
				<img
					src="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_strip['file'] ) ); ?>"
					width="<?php echo esc_attr( (string) (int) round( $exterior_exone_strip['width'] ) ); ?>"
					height="<?php echo esc_attr( (string) (int) round( $exterior_exone_strip['height'] ) ); ?>"
					alt=""
					loading="lazy"
				>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
