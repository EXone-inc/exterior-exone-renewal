<?php
/**
 * 支店: 実績数字。
 *
 * カンプ: PC Group 316（535:911）— 円形写真 + 数字 + 単位 + ラベルの 4 項目
 *         SP カンプなし（2 列 2 段）
 *
 * 数字は TOP の実績バンドと同じく js/scroll-reveal.js の data-countup で
 * 0 からカウントアップする（JS 無効時は実数値のまま）。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="p-sstats" data-section="store-stats">
	<ul class="p-sstats__list">
		<?php foreach ( exterior_exone_store_stats() as $exterior_exone_stat ) : ?>
			<li class="p-sstats__item">
				<?php // 230 のリングの内側に 200 の円形写真 + 黒 30%（535:895 / 535:881）。 ?>
				<div class="p-sstats__ring">
					<img
						class="p-sstats__photo"
						src="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_stat['image'] ) ); ?>"
						width="400"
						height="400"
						alt=""
						loading="lazy"
					>
					<p class="p-sstats__value">
						<span class="p-sstats__number" data-countup><?php echo esc_html( $exterior_exone_stat['number'] ); ?></span>
						<span class="p-sstats__unit"><?php echo esc_html( $exterior_exone_stat['unit'] ); ?></span>
					</p>
				</div>

				<p class="p-sstats__label"><?php echo esc_html( $exterior_exone_stat['label'] ); ?></p>
			</li>
		<?php endforeach; ?>
	</ul>
</section>
