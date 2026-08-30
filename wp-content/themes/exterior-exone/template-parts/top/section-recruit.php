<?php
/**
 * TOP: RECRUIT セクション。
 *
 * カンプ: PC 917:85-92（写真 + 黒 50% の暗幕）
 *         SP  917:788-794
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="p-recruit" data-section="recruit">
	<div class="p-recruit__bg" aria-hidden="true">
		<img src="<?php echo esc_url( exterior_exone_top_image( 'recruit-bg.jpg' ) ); ?>" alt="" loading="lazy">
	</div>

	<div class="p-recruit__inner">
		<h2 class="c-section-title p-recruit__title">RECRUIT</h2>
		<p class="p-recruit__copy">私たちと共に新たな<br>エクステリア業界を創造しよう！</p>
		<p class="p-recruit__sub">エクステリア業界の古い構造を見直し<br>新たな時代を創る仲間を募集中！<br>業界の変革を実現するため、共に挑戦しましょう。</p>
		<a class="c-btn c-btn--white p-recruit__btn" href="#">VIEW MORE</a>
	</div>
</section>
