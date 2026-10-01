<?php
/**
 * PLANS: FV。
 *
 * カンプ: PC 586:552（1920x1080・背景 586:403 / コピー 586:422・586:423・586:416）
 *         SP  586:1018・586:1025・586:1023（375x723。上 45 はヘッダーが重なる）
 *
 * 夕暮れの邸宅の静止画を全面に敷き、中央に「PLANS」と和文コピーを置く。
 * 暗幕は足さない（決定 B「FV」）。SP は同じ画像を建物の中央寄りで切り抜く。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_plans_fv = exterior_exone_plans_fv();
?>
<section class="p-plpfv" data-section="plans-fv">
	<div class="p-plpfv__bg" aria-hidden="true">
		<img
			src="<?php echo esc_url( exterior_exone_plans_image( $exterior_exone_plans_fv['image'] ) ); ?>"
			width="1672"
			height="941"
			alt=""
			fetchpriority="high"
		>
	</div>

	<div class="p-plpfv__copy">
		<h1 class="p-plpfv__title"><?php echo esc_html( $exterior_exone_plans_fv['title'] ); ?></h1>
		<p class="p-plpfv__heading"><?php echo esc_html( $exterior_exone_plans_fv['heading'] ); ?></p>
	</div>
</section>
