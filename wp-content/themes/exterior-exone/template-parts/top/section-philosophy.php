<?php
/**
 * TOP: 理念セクション（実績数値バンドを内包）。
 *
 * カンプ: PC 917:32-33・917:254・917:279（FV 写真 + 70% 暗幕の上に MVV と円環図）
 *         SP  16:16 Frame 5（722.81-1922.25。PC と同じ構成を縦積みにしたもの）
 *
 * 以前の SP カンプは「白背景に VISION のみ」の簡略版だったが、現行カンプでは
 * PC と同じ MISSION / VISION / VALUE + 円環図になったため、出し分けをやめて
 * 1 つの構成をメディアクエリで並べ替えるだけにしてある。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_philosophy_items = exterior_exone_philosophy_items();
?>
<section class="p-philosophy" data-section="philosophy">
	<div class="p-philosophy__bg" aria-hidden="true">
		<img src="<?php echo esc_url( exterior_exone_top_image( 'FV-bg.jpg' ) ); ?>" alt="" loading="lazy">
	</div>

	<div class="p-philosophy__inner">
		<div class="p-philosophy__body">
			<div class="p-philosophy__mvv">
				<?php foreach ( $exterior_exone_philosophy_items as $exterior_exone_index => $exterior_exone_item ) : ?>
					<?php // MISSION / VISION / VALUE は個別に下からフェードインさせる。 ?>
					<?php $exterior_exone_delay = $exterior_exone_index * 180; ?>

					<?php if ( 0 !== $exterior_exone_index ) : ?>
						<hr class="p-philosophy__rule" data-reveal data-reveal-delay="<?php echo esc_attr( (string) $exterior_exone_delay ); ?>">
					<?php endif; ?>

					<div class="p-philosophy__item" data-reveal data-reveal-delay="<?php echo esc_attr( (string) $exterior_exone_delay ); ?>">
						<h2 class="c-display p-philosophy__eng"><?php echo esc_html( $exterior_exone_item['eng'] ); ?></h2>
						<p class="p-philosophy__jp"><?php echo esc_html( $exterior_exone_item['jp'] ); ?></p>
						<p class="p-philosophy__sub"><?php echo nl2br( esc_html( $exterior_exone_item['lead'] ) ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>

			<?php
			// 円環図は「リング・中央のロゴと文言・住宅のライン画・6 項目」を
			// 個別の要素として組む。円周のアイコンだけを浮かせるため、
			// 焼き込みの 1 枚画像ではなく支給素材を配置している。
			//
			// 位置は Figma の実測値を枠に対する % に直したもの（CSS 側）。
			?>
			<?php
			// 画面に入ったら中央のライン画（line_animation.svg）を描き、描き終わってから
			// リング・ロゴ・6 項目がフェードイン、その後に浮遊が始まる（js/philosophy-diagram.js）。
			// data-reveal="stage" は figure 自体を動かさず、合図（.is-inview）だけ受け取る指定。
			?>
			<figure class="p-philosophy__diagram" data-reveal="stage" data-philosophy-diagram>
				<div class="p-diagram">
					<?php
					// リングは PC / SP とも、6 つのコピーの位置で切れた 6 本の弧。
					// SP（16:187）は Figma のパスをそのまま、PC（16:550）はマスクの
					// 掛かった円なのでパスが取れず、支給カンプ画像から弧の角度を実測した。
					?>
					<svg class="p-diagram__ring p-diagram__ring--sp" viewBox="0 0 255.598 260.969" preserveAspectRatio="none" aria-hidden="true" focusable="false">
						<path d="M255.108 156.85C251.264 175.512 243.418 192.714 232.508 207.519"></path>
						<path d="M145.9 259.219C139.984 260.043 133.941 260.469 127.799 260.469C121.657 260.469 115.614 260.043 109.699 259.219"></path>
						<path d="M23.118 207.557C12.1934 192.744 4.33782 175.529 0.489716 156.85"></path>
						<path d="M6.07956 84.7725C8.90275 77.2586 12.4018 70.0753 16.5026 63.2969"></path>
						<path d="M76.6675 10.9426C92.3612 4.22128 109.645 0.5 127.798 0.5C145.952 0.5 163.236 4.22128 178.93 10.9426"></path>
						<path d="M239.095 63.2969C243.196 70.0753 246.695 77.2586 249.518 84.7725"></path>
					</svg>

					<svg class="p-diagram__ring p-diagram__ring--pc" viewBox="0 0 889.426 889.426" preserveAspectRatio="none" aria-hidden="true" focusable="false">
						<path d="M285.70 30.47A443.713 443.713 0 0 1 603.73 30.47"></path>
						<path d="M812.57 196.59A443.713 443.713 0 0 1 871.24 322.41"></path>
						<path d="M882.96 514.13A443.713 443.713 0 0 1 784.62 729.93"></path>
						<path d="M559.55 873.31A443.713 443.713 0 0 1 329.87 873.31"></path>
						<path d="M104.81 729.93A443.713 443.713 0 0 1 6.46 514.13"></path>
						<path d="M18.19 322.41A443.713 443.713 0 0 1 76.86 196.59"></path>
					</svg>

					<p class="p-diagram__logo">
						<img
							src="<?php echo esc_url( exterior_exone_top_image( 'logo.svg' ) ); ?>"
							width="164"
							height="25"
							alt="<?php bloginfo( 'name' ); ?>"
							loading="lazy"
						>
					</p>

					<?php // 中央の文言は PC カンプのみ（SP 16:171 には無い）。 ?>
					<p class="p-diagram__lead">私たちのすべての行動は<br>この思想から生まれています</p>

					<?php
					// 住宅のライン画。線描画アニメーションのため画像ではなくインライン SVG
					//（参照実装 test_cp の FV と同じ stroke-dashoffset 方式）。
					echo exterior_exone_inline_line_svg( 'images/top/line_animation.svg', 'p-diagram__art' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- テーマ同梱の SVG をヘルパー内で整形済み。
					?>

					<?php foreach ( exterior_exone_philosophy_diagram_items() as $exterior_exone_index => $exterior_exone_item ) : ?>
						<?php $exterior_exone_no = $exterior_exone_index + 1; ?>

						<span class="p-diagram__icon p-diagram__icon--<?php echo esc_attr( (string) $exterior_exone_no ); ?>" aria-hidden="true">
							<img
								src="<?php echo esc_url( exterior_exone_top_image( $exterior_exone_item['icon'] ) ); ?>"
								alt=""
								loading="lazy"
							>
						</span>

						<p class="p-diagram__copy p-diagram__copy--<?php echo esc_attr( (string) $exterior_exone_no ); ?>">
							<?php echo nl2br( esc_html( implode( "\n", $exterior_exone_item['lines'] ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc_html 済みの文字列に改行タグを足すだけ。 ?>
						</p>
					<?php endforeach; ?>
				</div>
			</figure>
		</div>

		<?php get_template_part( 'template-parts/top/section', 'stats' ); ?>
	</div>
</section>
