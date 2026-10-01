<?php
/**
 * PLANS: HIGH-END PLAN の詳細。
 *
 * カンプ: PC 586:569（1920x2728。plan name 586:562 / box 586:563 / point 586:564 /
 *         designer plan 586:566 / top designer plan 586:568 / btn 586:402）
 *         SP  586:1227 ほか（375x1364）
 *
 * プラン名は PACKAGE と同じ上端 +270 / +294（カンプの +160 はヘッダー無しで描いた値。
 * 決定 B）。そのぶんプラン名より下はカンプの相対位置のまま下がる。
 * designer plan / top designer plan はブロックごとに 1 要素（S6 で左右フェードイン）。
 * SP は main img と帯が無く、デザイナープランは価格がプラン名の直下・区切り線なし。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_plans_highend = exterior_exone_plans_highend();
?>
<section id="high-end" class="p-plphe" data-section="plans-highend" data-plp-plan="high-end">
	<div class="p-plphe__name" data-plp-plan-name>
		<p class="p-plphe__jp"><?php echo esc_html( $exterior_exone_plans_highend['name'] ); ?></p>
		<h2 class="p-plphe__eng"><?php echo esc_html( $exterior_exone_plans_highend['eng'] ); ?></h2>
	</div>

	<div class="p-plphe__box">
		<?php // #272727 の帯と main img は PC のみ。 ?>
		<div class="p-plphe__band" aria-hidden="true"></div>
		<div class="p-plphe__main">
			<img
				src="<?php echo esc_url( exterior_exone_plans_image( $exterior_exone_plans_highend['main']['file'] ) ); ?>"
				width="<?php echo esc_attr( (string) $exterior_exone_plans_highend['main']['img'][0] ); ?>"
				height="<?php echo esc_attr( (string) $exterior_exone_plans_highend['main']['img'][1] ); ?>"
				alt="ハイエンドプランの施工イメージ"
				loading="lazy"
			>
		</div>
		<div class="p-plphe__sub">
			<img
				src="<?php echo esc_url( exterior_exone_plans_image( $exterior_exone_plans_highend['sub']['file'] ) ); ?>"
				width="<?php echo esc_attr( (string) $exterior_exone_plans_highend['sub']['img'][0] ); ?>"
				height="<?php echo esc_attr( (string) $exterior_exone_plans_highend['sub']['img'][1] ); ?>"
				alt="ハイエンドプランの 3D パース"
				loading="lazy"
			>
		</div>
		<div class="p-plphe__copy">
			<h3 class="p-plphe__heading"><?php echo esc_html( $exterior_exone_plans_highend['heading'] ); ?></h3>
			<p class="p-plphe__body">
				<?php echo wp_kses( implode( '<br>', array_map( 'esc_html', $exterior_exone_plans_highend['body'] ) ), array( 'br' => array() ) ); ?>
			</p>
		</div>
	</div>

	<ul class="p-plphe__points">
		<?php foreach ( $exterior_exone_plans_highend['points'] as $exterior_exone_point ) : ?>
			<?php $exterior_exone_br = ( isset( $exterior_exone_point['br'] ) && 'sp' === $exterior_exone_point['br'] ) ? '<br class="p-plphe__br-sp">' : '<br>'; ?>
			<li class="p-plphe__point">
				<span><?php echo wp_kses( implode( $exterior_exone_br, array_map( 'esc_html', $exterior_exone_point['lines'] ) ), array( 'br' => array( 'class' => array() ) ) ); ?></span>
			</li>
		<?php endforeach; ?>
	</ul>

	<?php // designer plan（写真左）は右から、top designer plan（写真右）は左からフェードイン（guide 586:613 / 586:616）。 ?>
	<div class="p-plphe__plans">
		<?php foreach ( $exterior_exone_plans_highend['plans'] as $exterior_exone_plan ) : ?>
			<div class="p-plphe__plan p-plphe__plan--<?php echo esc_attr( $exterior_exone_plan['key'] ); ?>" data-reveal="<?php echo esc_attr( 'designer' === $exterior_exone_plan['key'] ? 'right' : 'left' ); ?>">
				<div class="p-plphe__plan-media">
					<img
						src="<?php echo esc_url( exterior_exone_plans_image( $exterior_exone_plan['file'] ) ); ?>"
						width="<?php echo esc_attr( (string) $exterior_exone_plan['img'][0] ); ?>"
						height="<?php echo esc_attr( (string) $exterior_exone_plan['img'][1] ); ?>"
						alt="<?php echo esc_attr( $exterior_exone_plan['name'] ); ?>の施工イメージ"
						loading="lazy"
					>
				</div>
				<div class="p-plphe__plan-head">
					<p class="p-plphe__plan-jp"><?php echo esc_html( $exterior_exone_plan['name'] ); ?></p>
					<h3 class="p-plphe__plan-eng"><?php echo esc_html( $exterior_exone_plan['eng'] ); ?></h3>
				</div>
				<div class="p-plphe__plan-line" aria-hidden="true"></div>
				<div class="p-plphe__plan-body">
					<?php foreach ( $exterior_exone_plan['body'] as $exterior_exone_para ) : ?>
						<p><?php echo esc_html( $exterior_exone_para ); ?></p>
					<?php endforeach; ?>
				</div>
				<p class="p-plphe__price">
					<?php // 円記号は PC / SP で別の書き出し（586:451 / 586:1293。形と色が違う） ?>
					<img class="p-plphe__yen p-plphe__yen--pc" src="<?php echo esc_url( exterior_exone_asset_url( 'images/plans/pc-price-yen-586-451.svg' ) ); ?>" width="25" height="28" alt="¥">
					<img class="p-plphe__yen p-plphe__yen--sp" src="<?php echo esc_url( exterior_exone_asset_url( 'images/plans/sp-price-yen-586-1293.svg' ) ); ?>" width="8" height="10" alt="¥">
					<span class="p-plphe__price-num"><?php echo esc_html( $exterior_exone_plan['price'] ); ?></span>
					<span class="p-plphe__price-from">～</span>
				</p>
			</div>
		<?php endforeach; ?>
	</div>

	<div class="p-plphe__more">
		<a class="c-btn c-btn--white" href="<?php echo esc_url( $exterior_exone_plans_highend['more'] ); ?>">VIEW MORE</a>
	</div>
</section>
