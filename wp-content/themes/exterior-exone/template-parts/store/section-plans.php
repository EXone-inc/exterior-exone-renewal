<?php
/**
 * 支店: PLANS（PACKAGE + HIGH-END）。
 *
 * カンプ: PC 612:804（PACKAGE。背景 612:116 #f3f3f4 一色・帯 612:803・特長 612:295）/
 *         612:807（HIGH-END。写真 612:343・特長 612:355）
 *         SP 641:274〜641:308（PACKAGE）/ 641:310〜641:360（HIGH-END）
 *         guide 617:41「上部と同じエンドレスロール」
 *
 * 中ほどの住宅パース帯は section-strip.php（variant plans）を施工イメージ帯と同じ
 * エンドレスロールで流す（js/plans-imglist.js。2 本はそれぞれ独立に動く）。
 * SP は並び順がカンプで変わる（パースが見出しより上・VIEW MORE が特長の下）ため、
 * DOM は PC の順のまま CSS（display: contents + order）で並べ替える。
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
	<p class="c-store-jp p-splans__jp">
		<span class="p-splans__jp-pc"><?php echo esc_html( $exterior_exone_plans['jp'] ); ?></span>
		<span class="p-splans__jp-sp"><?php echo wp_kses( exterior_exone_store_lines( $exterior_exone_plans['jp_sp'] ), array( 'br' => array() ) ); ?></span>
	</p>

	<p class="c-store-lead p-splans__lead">
		<?php echo wp_kses( exterior_exone_store_lines( $exterior_exone_plans['lead'] ), array( 'br' => array() ) ); ?>
	</p>

	<?php // 612:354。本文とパッケージプランをつなぐ飾り線（SP には無い）。 ?>
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

		<?php // 612:308。下端を背景色に溶かす（612:582）。 ?>
		<div class="p-splans__media">
			<img
				src="<?php echo esc_url( exterior_exone_plans_image( $exterior_exone_plans['package']['image'] ) ); ?>"
				width="1536"
				height="1024"
				alt=""
				loading="lazy"
			>
		</div>
	</div>

	<?php
	get_template_part(
		'template-parts/store/section',
		'strip',
		array(
			'variant' => 'plans',
			'roll'    => true,
		)
	);
	?>

	<ul class="p-splans__features">
		<?php foreach ( $exterior_exone_plans['package']['features'] as $exterior_exone_feature ) : ?>
			<li
				class="p-splans__feature"
				style="--icon-w:<?php echo esc_attr( (string) $exterior_exone_feature['icon_w'] ); ?>;--icon-h:<?php echo esc_attr( (string) $exterior_exone_feature['icon_h'] ); ?>;--icon-sp-w:<?php echo esc_attr( (string) $exterior_exone_feature['icon_sp'][0] ); ?>;--icon-sp-h:<?php echo esc_attr( (string) $exterior_exone_feature['icon_sp'][1] ); ?>"
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
			<?php // 612:343。新カンプでは右端の黒いフェードは無い。 ?>
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

				<a class="c-btn c-btn--white p-splans__btn" href="<?php echo esc_url( exterior_exone_store_link( $exterior_exone_plans['highend']['more'] ) ); ?>">VIEW MORE</a>
			</div>
		</div>

		<?php // 612:355。区切り線は 3 本（PC のみ。SP の 2×2 には無い）。 ?>
		<ul class="p-splans__features p-splans__features--dark">
			<?php foreach ( $exterior_exone_plans['highend']['features'] as $exterior_exone_feature ) : ?>
				<li
					class="p-splans__feature"
					style="--icon-w:<?php echo esc_attr( (string) $exterior_exone_feature['icon_w'] ); ?>;--icon-h:<?php echo esc_attr( (string) $exterior_exone_feature['icon_h'] ); ?>;--icon-sp-w:<?php echo esc_attr( (string) $exterior_exone_feature['icon_sp'][0] ); ?>;--icon-sp-h:<?php echo esc_attr( (string) $exterior_exone_feature['icon_sp'][1] ); ?>"
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
