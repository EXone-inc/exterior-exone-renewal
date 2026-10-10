<?php
/**
 * ハイエンド: CONTACT。
 *
 * カンプ: PC 713:151（写真 713:153・暗幕 713:154・英字 713:156・text area 713:157・ボタン 713:160〜713:163）
 *         SP 733:405〜413
 *
 * 写真は支店 CTA と同じもの（images/store/。決定）。暗幕はカンプの値（左 → 85.577% で停止）。
 * 本文は PC 中央 4 行、SP は鍵括弧を中央 3 行 + 残りを左揃え 1 段落。
 * ボタンはお問い合わせページ /contact/ へ（支店モードは ?store= 付き）。ホバーは a:hover のみ。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_hep_cta = isset( $args['data'] ) ? $args['data'] : null;

if ( ! is_array( $exterior_exone_hep_cta ) ) {
	return;
}

// SP だけ鍵括弧ごとに改行する（PC は 1 行に 3 つ並ぶ）。
$exterior_exone_hep_cta_quotes = array();

foreach ( $exterior_exone_hep_cta['quotes'] as $exterior_exone_quote ) {
	$exterior_exone_hep_cta_quotes[] = array( $exterior_exone_quote, 'sp' );
}
?>
<section class="p-hepcta" data-section="highend-contact">
	<div class="p-hepcta__bg" aria-hidden="true">
		<img
			src="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_hep_cta['bg'] ) ); ?>"
			width="1448"
			height="1086"
			alt=""
			loading="lazy"
		>
	</div>

	<div class="p-hepcta__inner">
		<p class="p-hepcta__eng"><?php echo esc_html( $exterior_exone_hep_cta['eng'] ); ?></p>
		<h2 class="p-hepcta__heading"><?php echo esc_html( $exterior_exone_hep_cta['heading'] ); ?></h2>
		<p class="p-hepcta__quotes"><?php echo exterior_exone_highend_lines( $exterior_exone_hep_cta_quotes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses 済み。 ?></p>
		<p class="p-hepcta__body"><?php echo exterior_exone_highend_lines( $exterior_exone_hep_cta['body'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses 済み。 ?></p>
		<div class="p-hepcta__more">
			<a class="p-hepcta__btn" href="<?php echo esc_url( exterior_exone_store_link( $exterior_exone_hep_cta['url'] ) ); ?>"><?php echo esc_html( $exterior_exone_hep_cta['button'] ); ?></a>
		</div>
	</div>
</section>
