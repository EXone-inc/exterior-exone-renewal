<?php
/**
 * 支店: お問い合わせ CTA。
 *
 * カンプ: PC 612:863 = 夜景写真 612:112 + 左からの暗幕 612:113 /
 *         612:367 英字 / 612:366 和文見出し / 612:368 本文 3 行 /
 *         ボタン 612:369〜612:375 = LINE（#06c755）と電話（#d2832f）の 2 ボタン
 *         SP 641:1428〜641:1445（本文は左揃え、2 ボタンを縦積み 305x50）
 *
 * TOP の RECRUIT と骨格は同じだが文字サイズとボタンが別物なので別実装。
 * リンク先は未定のため支店データの仮値（決定事項 Q9）。
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

$exterior_exone_cta = exterior_exone_store_cta( $exterior_exone_store );
?>
<section class="p-scta" data-section="store-cta">
	<div class="p-scta__bg" aria-hidden="true">
		<img
			src="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_cta['image'] ) ); ?>"
			width="1920"
			height="983"
			alt=""
			loading="lazy"
		>
	</div>

	<div class="p-scta__inner">
		<p class="p-scta__eng"><?php echo esc_html( $exterior_exone_cta['eng'] ); ?></p>

		<?php // PC は 1 行（改行は CSS で消す）、SP は 2 行（641:1433）。 ?>
		<h2 class="p-scta__heading"><?php echo wp_kses( exterior_exone_store_lines( $exterior_exone_cta['heading'] ), array( 'br' => array() ) ); ?></h2>

		<p class="p-scta__lead">
			<?php echo wp_kses( exterior_exone_store_lines( $exterior_exone_cta['lead'] ), array( 'br' => array() ) ); ?>
		</p>

		<ul class="p-scta__buttons">
			<?php foreach ( $exterior_exone_cta['buttons'] as $exterior_exone_button ) : ?>
				<li class="p-scta__button-item">
					<a
						class="p-scta__button p-scta__button--<?php echo esc_attr( $exterior_exone_button['type'] ); ?>"
						href="<?php echo esc_url( $exterior_exone_button['url'] ); ?>"
						<?php // LINE の友だち追加（外部）は別タブで開く。電話（tel:）と仮の「#」はそのまま。 ?>
						<?php if ( 0 === strpos( $exterior_exone_button['url'], 'http' ) ) : ?>
							target="_blank"
							rel="noopener"
						<?php endif; ?>
					>
						<img
							class="p-scta__button-icon"
							src="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_button['icon'] ) ); ?>"
							width="164"
							height="164"
							alt=""
							loading="lazy"
						>
						<span class="p-scta__button-label"><?php echo esc_html( $exterior_exone_button['label'] ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
