<?php
/**
 * PLANS: Design Process（共通セクション）。
 *
 * カンプ: PC 586:573（1920x1470。title 586:570 / img 586:397 / flow 586:571 / card list 586:572）
 *         SP  586:1304 ほか（375x999）
 *
 * 工程ごとに 1 つの li（番号・工程名・写真カード・次の工程への矢印）。
 * PC は番号 + 工程名の下に写真カードで横 4 列、SP は左に写真カード・右に番号 + 工程名で縦 4 段。
 * カード 02 の影の欠け・寸法の不揃い・x+10 のずれ・SP の矢印の長さの不揃いは揃える（決定 C）。
 * 動きは付けない（決定 B）。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_plans_process = exterior_exone_plans_process();
$exterior_exone_steps_last    = count( $exterior_exone_plans_process['steps'] ) - 1;
?>
<section class="p-plpprocess" data-section="plans-process">
	<div class="p-plpprocess__inner">
		<div class="p-plpprocess__title">
			<p class="p-plpprocess__eng"><?php echo esc_html( $exterior_exone_plans_process['title'] ); ?></p>
			<h2 class="p-plpprocess__heading"><?php echo esc_html( $exterior_exone_plans_process['heading'] ); ?></h2>
		</div>

		<div class="p-plpprocess__image">
			<img
				src="<?php echo esc_url( exterior_exone_plans_image( $exterior_exone_plans_process['image']['file'] ) ); ?>"
				width="<?php echo esc_attr( (string) $exterior_exone_plans_process['image']['img'][0] ); ?>"
				height="<?php echo esc_attr( (string) $exterior_exone_plans_process['image']['img'][1] ); ?>"
				alt="外構の 3D パース"
				loading="lazy"
			>
		</div>

		<ol class="p-plpprocess__steps">
			<?php foreach ( $exterior_exone_plans_process['steps'] as $exterior_exone_i => $exterior_exone_step ) : ?>
				<li class="p-plpprocess__step">
					<span class="p-plpprocess__num"><?php echo esc_html( $exterior_exone_step['num'] ); ?></span>
					<p class="p-plpprocess__label"><?php echo esc_html( $exterior_exone_step['label'] ); ?></p>
					<div
						class="p-plpprocess__card"
						style="--crop-w:<?php echo esc_attr( $exterior_exone_step['crop_sp'][0] ); ?>;--crop-x:<?php echo esc_attr( $exterior_exone_step['crop_sp'][2] ); ?>;--crop-y:<?php echo esc_attr( $exterior_exone_step['crop_sp'][3] ); ?>"
					>
						<img
							src="<?php echo esc_url( exterior_exone_plans_image( $exterior_exone_step['file'] ) ); ?>"
							width="<?php echo esc_attr( (string) $exterior_exone_step['img'][0] ); ?>"
							height="<?php echo esc_attr( (string) $exterior_exone_step['img'][1] ); ?>"
							alt="<?php echo esc_attr( $exterior_exone_step['label'] ); ?>の様子"
							loading="lazy"
						>
					</div>
					<?php if ( $exterior_exone_i < $exterior_exone_steps_last ) : ?>
						<?php // 次の工程への矢印（PC 右向き 586:517〜519 / SP 下向き 586:1322・1330・1345。長さは揃える） ?>
						<span class="p-plpprocess__arrow" aria-hidden="true">
							<img class="p-plpprocess__arrow-img p-plpprocess__arrow-img--pc" src="<?php echo esc_url( exterior_exone_asset_url( 'images/plans/pc-process-arrow-586-517.svg' ) ); ?>" width="313" height="15" alt="">
							<img class="p-plpprocess__arrow-img p-plpprocess__arrow-img--sp" src="<?php echo esc_url( exterior_exone_asset_url( 'images/plans/sp-process-arrow1-586-1322.svg' ) ); ?>" width="70" height="15" alt="">
						</span>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>
