<?php
/**
 * PLANS: PLAN select（左右 2 分割）。
 *
 * カンプ: PC 586:555（1920x1080。左 586:554 / 右 586:553）
 *         SP  586:1029・586:1036（375x723。VIEW MORE 586:1050・586:1043）
 *
 * 左右それぞれが 1 つの操作要素。JS が動かないときは縦に並んだ該当プランへの
 * ページ内リンク（決定事項 B-2 Q6）。プラン名・画像・コピー・本文・VIEW MORE は
 * 選択の演出で別々に動かすため、要素を分けて data-plp-select-part で名前を付ける。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_plans_select = exterior_exone_plans_select();
?>
<section class="p-plpselect" data-section="plans-select" aria-label="プランを選ぶ">
	<?php foreach ( $exterior_exone_plans_select as $exterior_exone_plan ) : ?>
		<a
			class="p-plpselect__half p-plpselect__half--<?php echo esc_attr( $exterior_exone_plan['key'] ); ?>"
			href="<?php echo esc_url( $exterior_exone_plan['href'] ); ?>"
			data-plp-select="<?php echo esc_attr( $exterior_exone_plan['key'] ); ?>"
		>
			<div class="p-plpselect__stage">
				<div class="p-plpselect__name" data-plp-select-part="name">
					<p class="p-plpselect__jp"><?php echo esc_html( $exterior_exone_plan['name'] ); ?></p>
					<p class="p-plpselect__eng"><?php echo esc_html( $exterior_exone_plan['eng'] ); ?></p>
				</div>

				<div class="p-plpselect__media" data-plp-select-part="media" aria-hidden="true">
					<?php if ( '' !== $exterior_exone_plan['image_sp'] ) : ?>
						<picture>
							<source
								media="(max-width: 1024px)"
								srcset="<?php echo esc_url( exterior_exone_plans_image( $exterior_exone_plan['image_sp'] ) ); ?>"
							>
							<img
								src="<?php echo esc_url( exterior_exone_plans_image( $exterior_exone_plan['image'] ) ); ?>"
								width="<?php echo esc_attr( (string) $exterior_exone_plan['image_w'] ); ?>"
								height="<?php echo esc_attr( (string) $exterior_exone_plan['image_h'] ); ?>"
								alt=""
								loading="lazy"
							>
						</picture>
					<?php else : ?>
						<img
							src="<?php echo esc_url( exterior_exone_plans_image( $exterior_exone_plan['image'] ) ); ?>"
							width="<?php echo esc_attr( (string) $exterior_exone_plan['image_w'] ); ?>"
							height="<?php echo esc_attr( (string) $exterior_exone_plan['image_h'] ); ?>"
							alt=""
							loading="lazy"
						>
					<?php endif; ?>
				</div>

				<?php // 画像の足元を背景に溶かす（PC のみ。586:1097 / 586:578） ?>
				<div class="p-plpselect__fade" data-plp-select-part="fade" aria-hidden="true"></div>

				<p class="p-plpselect__copy" data-plp-select-part="copy">
					<?php echo esc_html( $exterior_exone_plan['copy'][0] ); ?><br class="u-br-sp"><?php echo esc_html( $exterior_exone_plan['copy'][1] ); ?>
				</p>

				<div class="p-plpselect__body" data-plp-select-part="body">
					<?php foreach ( $exterior_exone_plan['body'] as $exterior_exone_lines ) : ?>
						<p><?php echo wp_kses( implode( '<br class="u-br-pc">', array_map( 'esc_html', $exterior_exone_lines ) ), array( 'br' => array( 'class' => array() ) ) ); ?></p>
					<?php endforeach; ?>
				</div>

				<?php // SP のみ。見た目だけで、押すと半分を押したのと同じ（中に別のリンクは置けない）。 ?>
				<span class="c-btn c-btn--<?php echo 'package' === $exterior_exone_plan['key'] ? 'black' : 'white'; ?> p-plpselect__more" data-plp-select-part="more" aria-hidden="true">VIEW MORE</span>
			</div>
		</a>
	<?php endforeach; ?>
</section>
