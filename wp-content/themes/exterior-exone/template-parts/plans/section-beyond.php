<?php
/**
 * PLANS: Beyond the Plan（共通セクション）。
 *
 * カンプ: PC 586:576（1920x1844。背景 586:393 / title 586:574 / plan text 586:525 / point 586:575）
 *         SP  586:1423 ほか（375x734。背景は縦構図の別アセット）
 *
 * point は 4 項目のリスト全体を 1 要素（ul）にまとめてある（S6 で下からフェードイン）。
 * 重複見出し 586:1383 は実装しない。項目の間隔の不揃い・04 の中心ずれは揃える（決定 C）。
 * 下はそのまま既存フッター（CTA は置かない。決定 B）。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_plans_beyond = exterior_exone_plans_beyond();
?>
<section class="p-plpbeyond" data-section="plans-beyond">
	<picture class="p-plpbeyond__bg">
		<source media="(max-width: 1024px)" srcset="<?php echo esc_url( exterior_exone_plans_image( $exterior_exone_plans_beyond['bg_sp'] ) ); ?>">
		<img
			src="<?php echo esc_url( exterior_exone_plans_image( $exterior_exone_plans_beyond['bg_pc']['file'] ) ); ?>"
			width="<?php echo esc_attr( (string) $exterior_exone_plans_beyond['bg_pc']['img'][0] ); ?>"
			height="<?php echo esc_attr( (string) $exterior_exone_plans_beyond['bg_pc']['img'][1] ); ?>"
			alt=""
			loading="lazy"
		>
	</picture>

	<div class="p-plpbeyond__inner">
		<div class="p-plpbeyond__title">
			<p class="p-plpbeyond__eng"><?php echo esc_html( $exterior_exone_plans_beyond['title'] ); ?></p>
			<h2 class="p-plpbeyond__heading"><?php echo esc_html( $exterior_exone_plans_beyond['heading'] ); ?></h2>
		</div>

		<?php // PC は 4 行・中央揃え、SP は段落の中を流して段落の間を 1 行空ける ?>
		<div class="p-plpbeyond__body">
			<?php foreach ( $exterior_exone_plans_beyond['body'] as $exterior_exone_lines ) : ?>
				<p><?php echo wp_kses( implode( '<br class="u-br-pc">', array_map( 'esc_html', $exterior_exone_lines ) ), array( 'br' => array( 'class' => array() ) ) ); ?></p>
			<?php endforeach; ?>
		</div>

		<?php // リスト全体を下からフェードイン（guide 586:619） ?>
		<ul class="p-plpbeyond__points" data-reveal>
			<?php foreach ( $exterior_exone_plans_beyond['points'] as $exterior_exone_point ) : ?>
				<?php
				$exterior_exone_crop    = $exterior_exone_point['crop'];
				$exterior_exone_crop_sp = isset( $exterior_exone_point['crop_sp'] ) ? $exterior_exone_point['crop_sp'] : $exterior_exone_crop;
				$exterior_exone_style   = sprintf(
					'--pc-w:%1$s;--pc-h:%2$s;--sp-w:%3$s;--sp-h:%4$s;--art-opacity:%5$s',
					$exterior_exone_point['size_pc'][0],
					$exterior_exone_point['size_pc'][1],
					$exterior_exone_point['size_sp'][0],
					$exterior_exone_point['size_sp'][1],
					$exterior_exone_point['opacity']
				);
				if ( $exterior_exone_crop ) {
					$exterior_exone_style .= sprintf(
						';--crop-w:%1$s;--crop-x:%2$s;--crop-y:%3$s;--crop-w-sp:%4$s;--crop-x-sp:%5$s;--crop-y-sp:%6$s',
						$exterior_exone_crop[0],
						$exterior_exone_crop[1],
						$exterior_exone_crop[2],
						$exterior_exone_crop_sp[0],
						$exterior_exone_crop_sp[1],
						$exterior_exone_crop_sp[2]
					);
				}
				?>
				<li class="p-plpbeyond__point">
					<div class="p-plpbeyond__art">
						<div class="p-plpbeyond__art-frame<?php echo $exterior_exone_crop ? ' is-crop' : ''; ?>" style="<?php echo esc_attr( $exterior_exone_style ); ?>">
							<img
								src="<?php echo esc_url( exterior_exone_plans_image( $exterior_exone_point['file'] ) ); ?>"
								width="<?php echo esc_attr( (string) $exterior_exone_point['img'][0] ); ?>"
								height="<?php echo esc_attr( (string) $exterior_exone_point['img'][1] ); ?>"
								alt=""
								loading="lazy"
							>
						</div>
					</div>
					<p class="p-plpbeyond__point-eng"><?php echo esc_html( $exterior_exone_point['eng'] ); ?></p>
					<p class="p-plpbeyond__point-label"><?php echo esc_html( $exterior_exone_point['label'] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
