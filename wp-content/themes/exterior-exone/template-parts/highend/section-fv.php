<?php
/**
 * ハイエンド: FV（留まる舞台）。
 *
 * カンプ: PC 713:34（夕方）・713:39（夜）・713:116（暗幕 + コピー）
 *         SP 733:292・733:302・733:300 / 733:303 / 733:304〜309
 *
 * 層は下から 夕景 → 夜景 → 暗幕（黒 50%）→ 下端グラデ（PC のみ）→ FV コピー → 暗幕後のコピー。
 * 各層の濃さは .l-pin の --fv-mask-progress から css/highend.css が決める。
 * JS 無効・動きを減らす設定では夜景 + 暗幕 + コピーの完成状態で出る。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_hep_fv = isset( $args['data'] ) ? $args['data'] : null;

if ( ! is_array( $exterior_exone_hep_fv ) ) {
	return;
}
?>
<section class="p-hepfv" data-section="highend-fv">
	<div class="p-hepfv__evening" aria-hidden="true">
		<img
			src="<?php echo esc_url( exterior_exone_highend_image( $exterior_exone_hep_fv['evening'] ) ); ?>"
			width="<?php echo esc_attr( (string) $exterior_exone_hep_fv['img'][0] ); ?>"
			height="<?php echo esc_attr( (string) $exterior_exone_hep_fv['img'][1] ); ?>"
			alt=""
			fetchpriority="high"
		>
	</div>
	<div class="p-hepfv__night" aria-hidden="true">
		<img
			src="<?php echo esc_url( exterior_exone_highend_image( $exterior_exone_hep_fv['night'] ) ); ?>"
			width="<?php echo esc_attr( (string) $exterior_exone_hep_fv['img'][0] ); ?>"
			height="<?php echo esc_attr( (string) $exterior_exone_hep_fv['img'][1] ); ?>"
			alt=""
		>
	</div>
	<div class="p-hepfv__mask" aria-hidden="true"></div>
	<div class="p-hepfv__fade" aria-hidden="true"></div>

	<div class="p-hepfv__copy">
		<h1 class="p-hepfv__title"><?php echo esc_html( $exterior_exone_hep_fv['title'] ); ?></h1>
		<p class="p-hepfv__lead"><?php echo exterior_exone_highend_lines( $exterior_exone_hep_fv['lead'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses 済み。 ?></p>
	</div>

	<div class="p-hepfv__statement">
		<p class="p-hepfv__statement-title"><?php echo esc_html( $exterior_exone_hep_fv['statement']['title'] ); ?></p>
		<p class="p-hepfv__statement-body"><?php echo exterior_exone_highend_lines( $exterior_exone_hep_fv['statement']['lines'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses 済み。 ?></p>
		<img
			class="p-hepfv__logo"
			src="<?php echo esc_url( exterior_exone_asset_url( $exterior_exone_hep_fv['statement']['logo'] ) ); ?>"
			width="164"
			height="25"
			alt="EXONE"
		>
	</div>
</section>
