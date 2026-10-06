<?php
/**
 * ハイエンド: Design Plans（プランカード 2 枚・価格・注記）。
 *
 * カンプ: PC 713:209（見出し 713:46・コピー 713:84・plan list 713:87・カード 713:88 / 713:102・注記 713:85）
 *         SP 733:323・733:335・733:346・733:348〜371
 *
 * カードの名前・英名・写真・価格は PLANS ページの HIGH-END と同じデータ
 * （exterior_exone_highend_plans() が exterior_exone_plans_highend() から引く）。
 * 価格注記は PC のみ（SP カンプに無い）。ボタンは無い。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_hep_plans = isset( $args['data'] ) ? $args['data'] : null;

if ( ! is_array( $exterior_exone_hep_plans ) ) {
	return;
}
?>
<section class="p-hepplan" data-section="highend-plans">
	<?php
	get_template_part(
		'template-parts/highend/heading',
		null,
		array(
			'eng'     => $exterior_exone_hep_plans['eng'],
			'heading' => $exterior_exone_hep_plans['heading'],
		)
	);
	?>

	<p class="p-hepplan__lead"><?php echo exterior_exone_highend_lines( $exterior_exone_hep_plans['lead'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses 済み。 ?></p>

	<div class="p-hepplan__list">
		<?php foreach ( $exterior_exone_hep_plans['cards'] as $exterior_exone_hep_card ) : ?>
			<article class="p-hepplan__card p-hepplan__card--<?php echo esc_attr( $exterior_exone_hep_card['key'] ); ?>">
				<div class="p-hepplan__media">
					<img
						src="<?php echo esc_url( exterior_exone_plans_image( $exterior_exone_hep_card['file'] ) ); ?>"
						width="<?php echo esc_attr( (string) $exterior_exone_hep_card['img'][0] ); ?>"
						height="<?php echo esc_attr( (string) $exterior_exone_hep_card['img'][1] ); ?>"
						alt="<?php echo esc_attr( $exterior_exone_hep_card['name'] ); ?>の施工イメージ"
						loading="lazy"
					>
				</div>

				<div class="p-hepplan__content">
					<h3 class="p-hepplan__eng"><?php echo esc_html( $exterior_exone_hep_card['eng'] ); ?></h3>
					<p class="p-hepplan__name"><?php echo esc_html( $exterior_exone_hep_card['name'] ); ?></p>
					<p class="p-hepplan__body"><?php echo exterior_exone_highend_lines( $exterior_exone_hep_card['body'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses 済み。 ?></p>
				</div>

				<p class="p-hepplan__price">
					<?php // 円記号は PC / SP で別の書き出し（713:101 / 733:359。色は gray2） ?>
					<img class="p-hepplan__yen p-hepplan__yen--pc" src="<?php echo esc_url( exterior_exone_asset_url( 'images/highend/pc-price-yen-713-101.svg' ) ); ?>" width="25" height="28" alt="¥">
					<img class="p-hepplan__yen p-hepplan__yen--sp" src="<?php echo esc_url( exterior_exone_asset_url( 'images/highend/sp-price-yen-733-359.svg' ) ); ?>" width="11" height="12" alt="¥">
					<span class="p-hepplan__price-num"><?php echo esc_html( $exterior_exone_hep_card['price'] ); ?></span>
					<span class="p-hepplan__price-from">～</span>
				</p>
			</article>
		<?php endforeach; ?>
	</div>

	<p class="p-hepplan__note"><?php echo exterior_exone_highend_lines( $exterior_exone_hep_plans['note'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses 済み。 ?></p>
</section>
