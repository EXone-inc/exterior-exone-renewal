<?php
/**
 * サンクス: 完了メッセージ（丸チェック・一文・本文 2 段落）。
 *
 * カンプ: PC 964:72 / 966:111 / 966:114、SP 964:102 / 966:190 / 966:194
 *
 * 丸チェックはインライン SVG（964:72 の図形）。js/line-draw.js が開いたとき 1 回、
 * 円（line_01）→ ✓（line_02）の順に線を描く（決定事項 §4 Q2）。線の色・太さは css/thanks.css。
 * 一文と本文はスクロール出現にせず、開いた時点で ✓ に続けてフェードインする（決定事項 §4 Q8。css/thanks.css）。
 *
 * $args: message（一文）/ body（段落ごとの行の配列。exterior_exone_thanks_lines() の書式）
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_tk_message = isset( $args['message'] ) ? (string) $args['message'] : '';
$exterior_exone_tk_body    = isset( $args['body'] ) ? (array) $args['body'] : array();
?>
<section class="p-thanksmsg" data-section="thanks-message">
	<svg class="p-thanksmsg__check" data-line-draw viewBox="0 0 146 146" width="146" height="146" aria-hidden="true" focusable="false">
		<g id="line_01">
			<circle class="p-thanksmsg__circle" cx="73" cy="73" r="72.5" pathLength="1" />
		</g>
		<g id="line_02">
			<line class="p-thanksmsg__tick" x1="44.0607" y1="78.9393" x2="65.0607" y2="99.9393" pathLength="1" />
			<path class="p-thanksmsg__tick" d="M65.2768 101.787L103 47.5" pathLength="1" />
		</g>
	</svg>

	<div class="p-thanksmsg__text">
		<p class="p-thanksmsg__lead"><?php echo esc_html( $exterior_exone_tk_message ); ?></p>
		<?php foreach ( $exterior_exone_tk_body as $exterior_exone_tk_para ) : ?>
			<p class="p-thanksmsg__body"><?php echo exterior_exone_thanks_lines( $exterior_exone_tk_para ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses 済み。 ?></p>
		<?php endforeach; ?>
	</div>
</section>
