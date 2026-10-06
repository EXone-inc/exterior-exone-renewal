<?php
/**
 * ハイエンド: DX Experience（静的セクション）。
 *
 * カンプ: PC 713:140（背景 713:142・グラデ 713:143・見出し 713:144・本文 713:149・画像 713:150・ボタン 713:147）
 *         SP 733:291・733:372〜379
 *
 * 夕景の住宅写真（PC / SP で別アセット）の上部を紺 → 透明のグラデで覆う。
 * PC は「本文左 | 機器の画像右」→ ボタン、SP は 見出し → 画像 → 本文 → ボタン。
 * ボタンの遷移先は /dx/（支店モードでは ?store= を引き継ぐ）。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_hep_dx = isset( $args['data'] ) ? $args['data'] : null;

if ( ! is_array( $exterior_exone_hep_dx ) ) {
	return;
}

$exterior_exone_hep_dx_bg      = $exterior_exone_hep_dx['bg'];
$exterior_exone_hep_dx_devices = $exterior_exone_hep_dx['devices'];
?>
<section class="p-hepdx" data-section="highend-dx">
	<div class="p-hepdx__bg" aria-hidden="true">
		<picture>
			<source
				media="(max-width: 1024px)"
				srcset="<?php echo esc_url( exterior_exone_highend_image( $exterior_exone_hep_dx_bg['sp']['file'] ) ); ?>"
				width="<?php echo esc_attr( (string) $exterior_exone_hep_dx_bg['sp']['img'][0] ); ?>"
				height="<?php echo esc_attr( (string) $exterior_exone_hep_dx_bg['sp']['img'][1] ); ?>"
			>
			<img
				src="<?php echo esc_url( exterior_exone_highend_image( $exterior_exone_hep_dx_bg['pc']['file'] ) ); ?>"
				width="<?php echo esc_attr( (string) $exterior_exone_hep_dx_bg['pc']['img'][0] ); ?>"
				height="<?php echo esc_attr( (string) $exterior_exone_hep_dx_bg['pc']['img'][1] ); ?>"
				alt=""
				loading="lazy"
			>
		</picture>
		<div class="p-hepdx__navy"></div>
	</div>

	<div class="p-hepdx__inner">
		<?php
		get_template_part(
			'template-parts/highend/heading',
			null,
			array(
				'eng'     => $exterior_exone_hep_dx['eng'],
				'heading' => $exterior_exone_hep_dx['heading'],
			)
		);
		?>

		<div class="p-hepdx__box">
			<p class="p-hepdx__body"><?php echo exterior_exone_highend_lines( $exterior_exone_hep_dx['body'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses 済み。 ?></p>
			<div class="p-hepdx__devices">
				<img
					src="<?php echo esc_url( exterior_exone_highend_image( $exterior_exone_hep_dx_devices['file'] ) ); ?>"
					width="<?php echo esc_attr( (string) $exterior_exone_hep_dx_devices['img'][0] ); ?>"
					height="<?php echo esc_attr( (string) $exterior_exone_hep_dx_devices['img'][1] ); ?>"
					alt="パソコンとスマートフォンに表示した外構の完成イメージ"
					loading="lazy"
				>
			</div>
		</div>

		<div class="p-hepdx__more">
			<a class="c-btn p-hepdx__btn" href="<?php echo esc_url( exterior_exone_store_link( $exterior_exone_hep_dx['url'] ) ); ?>"><?php echo esc_html( $exterior_exone_hep_dx['button'] ); ?></a>
		</div>
	</div>
</section>
