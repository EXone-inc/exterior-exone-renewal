<?php
/**
 * DX: summary（円環図）。
 *
 * カンプ: PC 437:450（夕景 436:241 / コピー 436:243 / 円環図 437:452）
 *         SP  439:937〜439:968（円環図 Group 499 439:944）
 *
 * 夕景写真の上に 2 行のコピーと本文 3 行を置き、その下に円環図を敷く。
 * 円環図は TOP 理念セクション（template-parts/top/section-philosophy.php）の
 * .p-diagram をそのまま流用し、差分（ラベル 6 個・アイコン・円内下部の英字・
 * 円径・位置）だけを .p-diagram--dx と inc/dx-data.php で上書きしている。
 * 出現順（線描画 → ふわっとフェード → 浮遊）も TOP と同じ js/line-draw.js +
 * js/philosophy-diagram.js が動かす（決定事項 2026-09-19）。
 *
 * 「私たちが届けたいのは、〜」は PC が円の中の上部・SP が円の下だが、
 * 文言は 1 つなので同じ要素を CSS の位置指定だけで動かしている。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_dx_summary = exterior_exone_dx_summary();
?>
<section class="p-dxsum" data-section="dx-summary">
	<?php // 436:241-246 / 439:938-940。写真の上端は黒から、下端は背景色へ抜く。 ?>
	<div class="p-dxsum__bg" aria-hidden="true">
		<img src="<?php echo esc_url( exterior_exone_dx_image( $exterior_exone_dx_summary['image'] ) ); ?>" alt="" loading="lazy">
		<span class="p-dxsum__gradation"></span>
		<span class="p-dxsum__fade"></span>
	</div>

	<div class="p-dxsum__inner">
		<?php // 436:245 / 439:941 ?>
		<h2 class="p-dxsum__copy">
			<?php foreach ( $exterior_exone_dx_summary['copy'] as $exterior_exone_line ) : ?>
				<span class="p-dxsum__copy-line"><?php echo esc_html( $exterior_exone_line ); ?></span>
			<?php endforeach; ?>
		</h2>

		<?php // 436:244 / 439:943 ?>
		<p class="p-dxsum__body">
			<?php foreach ( $exterior_exone_dx_summary['body'] as $exterior_exone_line ) : ?>
				<span class="p-dxsum__body-line"><?php echo esc_html( $exterior_exone_line ); ?></span>
			<?php endforeach; ?>
		</p>

		<?php
		// 画面に入ったら中央のライン画（line_animation.svg）を描き、描き終わってから
		// リング・ロゴ・文言・6 項目がフェードインし、その後に浮遊が始まる。
		// data-philosophy-diagram は js/philosophy-diagram.js が探す目印、
		// data-reveal="stage" は器を動かさず合図だけ受け取る指定（TOP と同じ）。
		?>
		<figure class="p-dxsum__diagram" data-reveal="stage" data-philosophy-diagram>
			<div class="p-diagram p-diagram--dx">
				<?php // 436:261 / 439:956。弧の切れ目の位置が幅で違うので 2 枚を出し分ける。 ?>
				<img
					class="p-diagram__ring p-diagram__ring--sp"
					src="<?php echo esc_url( exterior_exone_dx_image( $exterior_exone_dx_summary['rings']['sp'] ) ); ?>"
					width="281"
					height="281"
					alt=""
					loading="lazy"
				>
				<img
					class="p-diagram__ring p-diagram__ring--pc"
					src="<?php echo esc_url( exterior_exone_dx_image( $exterior_exone_dx_summary['rings']['pc'] ) ); ?>"
					width="1200"
					height="1200"
					alt=""
					loading="lazy"
				>

				<?php // 436:257 / 439:953。TOP の円環図と同じロゴ。 ?>
				<p class="p-diagram__logo">
					<img
						src="<?php echo esc_url( exterior_exone_top_image( 'logo.svg' ) ); ?>"
						width="164"
						height="25"
						alt="<?php bloginfo( 'name' ); ?>"
						loading="lazy"
					>
				</p>

				<?php // 436:249 / 439:942。PC は円の中、SP は円の下（位置は CSS）。 ?>
				<p class="p-diagram__lead">
					<?php echo esc_html( $exterior_exone_dx_summary['lead'][0] ); ?><br><?php echo esc_html( $exterior_exone_dx_summary['lead'][1] ); ?>
				</p>

				<?php
				// 436:290 / 439:967「SVG animation」。線描画アニメーションのため画像では
				// なくインライン SVG で出す（TOP 理念セクションと同一アセット）。
				echo exterior_exone_inline_line_svg( 'images/top/line_animation.svg', 'p-diagram__art' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- テーマ同梱の SVG をヘルパー内で整形済み。
				?>

				<?php foreach ( $exterior_exone_dx_summary['items'] as $exterior_exone_index => $exterior_exone_item ) : ?>
					<?php $exterior_exone_no = $exterior_exone_index + 1; ?>

					<span class="p-diagram__icon p-diagram__icon--<?php echo esc_attr( (string) $exterior_exone_no ); ?>" aria-hidden="true">
						<img
							src="<?php echo esc_url( exterior_exone_dx_image( $exterior_exone_item['icon'] ) ); ?>"
							alt=""
							loading="lazy"
						>
					</span>

					<p class="p-diagram__copy p-diagram__copy--<?php echo esc_attr( (string) $exterior_exone_no ); ?>">
						<?php echo esc_html( $exterior_exone_item['label'] ); ?>
					</p>
				<?php endforeach; ?>

				<?php // 436:260 / 436:259。円の中の下部（TOP には無い DX 固有の 2 行）。 ?>
				<p class="c-display p-diagram__display"><?php echo esc_html( $exterior_exone_dx_summary['display'] ); ?></p>
				<p class="c-display p-diagram__tagline"><?php echo esc_html( $exterior_exone_dx_summary['tagline'] ); ?></p>
			</div>
		</figure>
	</div>
</section>
