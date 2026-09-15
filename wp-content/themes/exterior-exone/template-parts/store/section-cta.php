<?php
/**
 * 支店: お問い合わせ CTA。
 *
 * カンプ: PC Group 373（669:964）= 夜景写真 645:3 + 左からの暗幕 639:407 /
 *         639:366 英字 / 639:364 和文見出し / 639:406 本文 3 行 /
 *         Group 340（645:5）= LINE（#06c755）と電話（#d2832f）の 2 ボタン
 *         SP カンプなし（2 ボタンを縦積み）
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

		<h2 class="p-scta__heading"><?php echo esc_html( $exterior_exone_cta['heading'] ); ?></h2>

		<p class="p-scta__lead">
			<?php echo wp_kses( exterior_exone_store_lines( $exterior_exone_cta['lead'] ), array( 'br' => array() ) ); ?>
		</p>

		<ul class="p-scta__buttons">
			<?php foreach ( $exterior_exone_cta['buttons'] as $exterior_exone_button ) : ?>
				<li class="p-scta__button-item">
					<a
						class="p-scta__button p-scta__button--<?php echo esc_attr( $exterior_exone_button['type'] ); ?>"
						href="<?php echo esc_url( $exterior_exone_button['url'] ); ?>"
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
