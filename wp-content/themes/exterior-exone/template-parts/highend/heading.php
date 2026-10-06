<?php
/**
 * ハイエンド: セクション見出し（英字小 + 和文）。5 セクション共通の組み。
 *
 * カンプ: PC 713:40（713:42 SF Pro Medium 32 → +55.86 に 713:41 Noto Bold 40）
 *         SP 733:320・733:321（14px → +24.23 に 16px）
 *
 * $args: eng（英字）/ heading（行の配列。exterior_exone_highend_lines() の書式）
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $args['eng'] ) || empty( $args['heading'] ) ) {
	return;
}
?>
<div class="p-hephead">
	<p class="p-hephead__eng"><?php echo esc_html( $args['eng'] ); ?></p>
	<h2 class="p-hephead__heading"><?php echo exterior_exone_highend_lines( $args['heading'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses 済み。 ?></h2>
</div>
