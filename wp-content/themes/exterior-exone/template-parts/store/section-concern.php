<?php
/**
 * 支店: 不安提示。
 *
 * カンプ: PC 535:932（黒帯）/ 535:917（見出し）/ 535:919（悩み 3 行）/
 *         535:922（補足 2 行）/ 535:933（スケッチ）
 *         SP カンプなし（縦積み。スケッチは背景に回す）
 *
 * スケッチは Figma 上の見た目どおり見出しの右側に置く（左右反転で配置されている）。
 * 文字の可読性を守るため、テキストより下のレイヤーに敷く。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_concern = exterior_exone_store_concern();
?>
<section class="p-sconcern" data-section="store-concern">
	<img
		class="p-sconcern__sketch"
		src="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_concern['sketch'] ) ); ?>"
		width="1200"
		height="800"
		alt=""
		aria-hidden="true"
		loading="lazy"
	>

	<div class="p-sconcern__body">
		<h2 class="p-sconcern__heading"><?php echo esc_html( $exterior_exone_concern['heading'] ); ?></h2>

		<ul class="p-sconcern__list">
			<?php foreach ( $exterior_exone_concern['worries'] as $exterior_exone_worry ) : ?>
				<li class="p-sconcern__item">「 <?php echo esc_html( $exterior_exone_worry ); ?> 」</li>
			<?php endforeach; ?>
		</ul>

		<p class="p-sconcern__note">
			<?php foreach ( $exterior_exone_concern['notes'] as $exterior_exone_index => $exterior_exone_note ) : ?>
				<?php echo 0 === $exterior_exone_index ? '' : '<br>'; ?>
				<?php echo esc_html( $exterior_exone_note ); ?>
			<?php endforeach; ?>
		</p>
	</div>
</section>
