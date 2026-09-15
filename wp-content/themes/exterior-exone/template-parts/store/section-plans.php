<?php
/**
 * 支店: PLANS。
 *
 * カンプ: PC 572:620 / 669:975 / 602:129（背景の 3 段）/ 599:50（見出し 3 点セット）/
 *         Group 362（669:933 パッケージ）/ Group 363（669:934 特長 4 列）/
 *         Group 338（610:283 ハイエンド）/ Group 374（669:965 特長 4 列）
 *         SP カンプなし（テキストと画像を縦積み、特長は 2 列 2 段）
 *
 * 中ほどの住宅パース帯は §3（section-strip.php）と同じ 5 枚を使い回す
 *（docs/spec-20260911-store-page-decisions.md「施工イメージ帯の写真 5 枚の再利用」）。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_plans = exterior_exone_store_plans();
?>
<section class="p-splans" data-section="store-plans">
	<h2 class="c-store-title"><?php echo esc_html( $exterior_exone_plans['title'] ); ?></h2>
	<p class="c-store-jp"><?php echo esc_html( $exterior_exone_plans['jp'] ); ?></p>

	<p class="c-store-lead p-splans__lead">
		<?php echo wp_kses( exterior_exone_store_lines( $exterior_exone_plans['lead'] ), array( 'br' => array() ) ); ?>
	</p>

	<?php // 620:286。本文とパッケージプランをつなぐ飾り線。 ?>
	<span class="p-splans__divider" aria-hidden="true"></span>

	<div class="p-splans__block p-splans__block--package">
		<div class="p-splans__text">
			<p class="p-splans__label"><?php echo esc_html( $exterior_exone_plans['package']['label'] ); ?></p>
			<p class="c-display p-splans__eng"><?php echo esc_html( $exterior_exone_plans['package']['eng'] ); ?></p>

			<p class="p-splans__copy">
				<?php echo wp_kses( exterior_exone_store_lines( $exterior_exone_plans['package']['lead'] ), array( 'br' => array() ) ); ?>
			</p>

			<a class="c-btn c-btn--black p-splans__btn" href="<?php echo esc_url( $exterior_exone_plans['package']['more'] ); ?>">VIEW MORE</a>
		</div>

		<?php // 599:13。下端を白に溶かす（669:958）。 ?>
		<div class="p-splans__media">
			<img
				src="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_plans['package']['image'] ) ); ?>"
				width="1200"
				height="626"
				alt=""
				loading="lazy"
			>
		</div>
	</div>

	<?php get_template_part( 'template-parts/store/section', 'strip', array( 'variant' => 'plans' ) ); ?>

	<ul class="p-splans__features">
		<?php foreach ( $exterior_exone_plans['package']['features'] as $exterior_exone_feature ) : ?>
			<li
				class="p-splans__feature"
				style="--icon-w:<?php echo esc_attr( (string) $exterior_exone_feature['icon_w'] ); ?>;--icon-h:<?php echo esc_attr( (string) $exterior_exone_feature['icon_h'] ); ?>"
			>
				<img
					class="p-splans__icon"
					src="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_feature['icon'] ) ); ?>"
					width="<?php echo esc_attr( (string) (int) round( $exterior_exone_feature['icon_w'] ) ); ?>"
					height="<?php echo esc_attr( (string) (int) round( $exterior_exone_feature['icon_h'] ) ); ?>"
					alt=""
					loading="lazy"
				>

				<p class="p-splans__feature-label">
					<?php echo esc_html( $exterior_exone_feature['lines'][0] ); ?><br>
					<?php echo esc_html( $exterior_exone_feature['lines'][1] ); ?>
				</p>
			</li>
		<?php endforeach; ?>
	</ul>

	<div class="p-splans__highend">
		<div class="p-splans__block p-splans__block--highend">
			<?php // 610:273。右端を #1a1a1a に溶かす（610:278）。 ?>
			<div class="p-splans__media">
				<img
					src="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_plans['highend']['image'] ) ); ?>"
					width="1200"
					height="842"
					alt=""
					loading="lazy"
				>
			</div>

			<div class="p-splans__text">
				<p class="p-splans__label"><?php echo esc_html( $exterior_exone_plans['highend']['label'] ); ?></p>
				<p class="c-display p-splans__eng"><?php echo esc_html( $exterior_exone_plans['highend']['eng'] ); ?></p>

				<p class="p-splans__copy">
					<?php echo wp_kses( exterior_exone_store_lines( $exterior_exone_plans['highend']['lead'] ), array( 'br' => array() ) ); ?>
				</p>

				<a class="c-btn c-btn--white p-splans__btn" href="<?php echo esc_url( $exterior_exone_plans['highend']['more'] ); ?>">VIEW MORE</a>
			</div>
		</div>

		<?php // Group 374。区切り線は 3 本（602:104 は hidden だがカンプの見た目では 3 本見える）。 ?>
		<ul class="p-splans__features p-splans__features--dark">
			<?php foreach ( $exterior_exone_plans['highend']['features'] as $exterior_exone_feature ) : ?>
				<li
					class="p-splans__feature"
					style="--icon-w:<?php echo esc_attr( (string) $exterior_exone_feature['icon_w'] ); ?>;--icon-h:<?php echo esc_attr( (string) $exterior_exone_feature['icon_h'] ); ?>"
				>
					<img
						class="p-splans__icon"
						src="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_feature['icon'] ) ); ?>"
						width="<?php echo esc_attr( (string) (int) round( $exterior_exone_feature['icon_w'] ) ); ?>"
						height="<?php echo esc_attr( (string) (int) round( $exterior_exone_feature['icon_h'] ) ); ?>"
						alt=""
						loading="lazy"
					>

					<p class="p-splans__feature-label">
						<?php echo esc_html( $exterior_exone_feature['lines'][0] ); ?><br>
						<?php echo esc_html( $exterior_exone_feature['lines'][1] ); ?>
					</p>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
