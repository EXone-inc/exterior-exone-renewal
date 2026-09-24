<?php
/**
 * DX: Exterior Explorer（3D section 436:388 / 439:753 + 3D sample 436:390-393）。
 *
 * スクロール連動の本番映像 1 本で、カンプの「3D section」と「3D sample 1〜4」を
 * まとめて表現する（決定事項 2026-09-20）。
 *
 * ・映像は画面に固定（sticky）し、下へスクロールすると進む。左右に余白は付けず
 *   画面幅いっぱいに出す（object-fit: cover）
 * ・全景（進行 0 付近）では 436:388 どおり、上に英字ラベルと 2 行コピー（白）を
 *   重ねる（カンプ下部の本文はユーザー指示で出さない。2026-09-20）
 * ・章 2〜5（アプローチ / カーポート / 植栽 / 門柱）では 3D sample 436:390-393
 *   どおり、右下に和文 + 英字の大ラベルを出す。章が変わるとフェードで切り替わる
 * ・カンプに無い操作 UI（章ナビ・静止画切替・スキップ）は置かない
 * ・JS 無効・動きを減らす設定・映像の読み込み失敗のときだけ、五つの見せ場を
 *   静止画（.p-dxex__fallback）で縦に並べる
 *
 * 文言は inc/dx-data.php の exterior_exone_dx_explorer()、映像と章の構成は
 * exterior_exone_dx_explorer_media() が単一の出典。挙動は js/dx-explorer.js。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_dx_explorer = exterior_exone_dx_explorer();
$exterior_exone_dx_media    = exterior_exone_dx_explorer_media();
?>
<section class="p-dxex p-dxex--scroll" data-section="dx-explorer" data-dx-explorer aria-labelledby="dx-explorer-heading">
	<div class="p-dxex__stage" data-ex-stage>
		<?php // 映像。ポスターは全景の静止画で、映像の 1 フレーム目が出るまでのつなぎ。 ?>
		<div class="p-dxex__media" aria-hidden="true">
			<picture class="p-dxex__poster">
				<source media="(max-aspect-ratio: 1/1)" srcset="<?php echo esc_url( $exterior_exone_dx_media['sources']['sp']['poster'] ); ?>">
				<img data-ex-poster src="<?php echo esc_url( $exterior_exone_dx_media['sources']['pc']['poster'] ); ?>" alt="" decoding="async">
			</picture>
			<video class="p-dxex__video" data-ex-video muted playsinline preload="none" tabindex="-1" disablepictureinpicture></video>
		</div>

		<?php // 436:388 / 439:753。全景のときだけ見える固定コピー。 ?>
		<div class="p-dxex__inner">
			<div class="p-dxex__intro">
				<p class="p-dxex__label"><?php echo esc_html( $exterior_exone_dx_explorer['label'] ); ?></p>

				<h2 class="p-dxex__copy" id="dx-explorer-heading">
					<?php foreach ( $exterior_exone_dx_explorer['copy'] as $exterior_exone_line ) : ?>
						<span class="p-dxex__copy-line"><?php echo esc_html( $exterior_exone_line ); ?></span>
					<?php endforeach; ?>
				</h2>
			</div>
		</div>

		<?php
		// 436:390-393 / 439:982・439:978。章 2〜5 の右下ラベル（和文の上に英字を重ねる）。
		// 全景（1 つ目）にはカンプにラベルが無いので出さない。
		?>
		<div class="p-dxex__chapters" aria-hidden="true">
			<?php foreach ( $exterior_exone_dx_media['chapters'] as $exterior_exone_index => $exterior_exone_chapter ) : ?>
				<?php if ( 0 === $exterior_exone_index ) : ?>
					<?php continue; ?>
				<?php endif; ?>
				<p class="p-dxex__chapter" data-ex-chapter="<?php echo esc_attr( $exterior_exone_index ); ?>">
					<span class="p-dxex__chapter-ja"><?php echo esc_html( $exterior_exone_chapter['label'] ); ?></span>
					<span class="p-dxex__chapter-en" lang="en"><?php echo esc_html( $exterior_exone_chapter['en'] ); ?></span>
				</p>
			<?php endforeach; ?>
		</div>

		<?php // 支援技術向け。いまの章と、映像の状態（読み込み中 / 静止画に切替 など）。 ?>
		<p class="p-dxex__sr" data-ex-current><?php echo esc_html( $exterior_exone_dx_media['chapters'][0]['label'] ); ?></p>
		<p class="p-dxex__sr" data-ex-status role="status"></p>
	</div>

	<?php // 静止画フォールバック。JS が映像を使えるときは hidden にする。 ?>
	<div class="p-dxex__fallback" data-ex-fallback>
		<?php foreach ( $exterior_exone_dx_media['chapters'] as $exterior_exone_index => $exterior_exone_chapter ) : ?>
			<figure class="p-dxex__still" id="dx-view-<?php echo esc_attr( $exterior_exone_chapter['id'] ); ?>">
				<picture>
					<source media="(max-aspect-ratio: 1/1)" srcset="<?php echo esc_url( exterior_exone_asset_url( 'images/dx/explorer/sp-' . $exterior_exone_chapter['id'] . '.webp' ) ); ?>" width="1080" height="1920">
					<img src="<?php echo esc_url( exterior_exone_asset_url( 'images/dx/explorer/pc-' . $exterior_exone_chapter['id'] . '.webp' ) ); ?>" width="1920" height="1080" alt="<?php echo esc_attr( '住宅と外構の' . $exterior_exone_chapter['label'] ); ?>" loading="lazy" decoding="async">
				</picture>

				<?php if ( 0 !== $exterior_exone_index ) : ?>
					<figcaption class="p-dxex__chapter is-current">
						<span class="p-dxex__chapter-ja"><?php echo esc_html( $exterior_exone_chapter['label'] ); ?></span>
						<span class="p-dxex__chapter-en" lang="en"><?php echo esc_html( $exterior_exone_chapter['en'] ); ?></span>
					</figcaption>
				<?php endif; ?>
			</figure>
		<?php endforeach; ?>
	</div>

	<script type="application/json" data-ex-config><?php echo wp_json_encode( $exterior_exone_dx_media, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); ?></script>
</section>
