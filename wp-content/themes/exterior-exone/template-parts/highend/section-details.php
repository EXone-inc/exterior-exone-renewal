<?php
/**
 * ハイエンド: Design Details（4 組）。
 *
 * カンプ: PC 713:208（見出し 713:43、写真 713:124・713:133・713:128・713:137、
 *         番号 713:126・713:135・713:130・713:139、text area 713:52・713:62・713:68・713:74）
 *         SP 733:322〜345・737:31・737:35・737:39 / guide 713:224
 *
 * PC は写真とテキストエリアの左右交互（奇数は写真左）、テキストは写真と上下中央揃え。
 * SP は「小写真 + 英名ブロック」の横並び（奇数は写真左）→ 本文は全幅で下。
 * テキストエリアはそれぞれ下からフェードイン（[data-reveal]）。写真は動かさない。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_hep_details = isset( $args['data'] ) ? $args['data'] : null;

if ( ! is_array( $exterior_exone_hep_details ) ) {
	return;
}
?>
<section class="p-hepdt" data-section="highend-details">
	<?php
	get_template_part(
		'template-parts/highend/heading',
		null,
		array(
			'eng'     => $exterior_exone_hep_details['eng'],
			'heading' => $exterior_exone_hep_details['heading'],
		)
	);
	?>

	<ol class="p-hepdt__list">
		<?php foreach ( $exterior_exone_hep_details['items'] as $exterior_exone_hep_item ) : ?>
			<?php
			// 切り抜き（幅 % / 高さ % / 左 % / 上 %）。カンプの画像塗りの値。
			$exterior_exone_hep_crop = $exterior_exone_hep_item['crop'];
			$exterior_exone_hep_crop_style = sprintf(
				'--crop-w:%1$s;--crop-h:%2$s;--crop-x:%3$s;--crop-y:%4$s',
				$exterior_exone_hep_crop[0],
				$exterior_exone_hep_crop[1],
				$exterior_exone_hep_crop[2],
				$exterior_exone_hep_crop[3]
			);
			?>
			<li class="p-hepdt__item">
				<div class="p-hepdt__media">
					<div class="p-hepdt__photo" style="<?php echo esc_attr( $exterior_exone_hep_crop_style ); ?>">
						<img
							src="<?php echo esc_url( exterior_exone_highend_image( $exterior_exone_hep_item['file'] ) ); ?>"
							width="<?php echo esc_attr( (string) $exterior_exone_hep_item['img'][0] ); ?>"
							height="<?php echo esc_attr( (string) $exterior_exone_hep_item['img'][1] ); ?>"
							alt="<?php echo esc_attr( $exterior_exone_hep_item['heading'] ); ?>"
							loading="lazy"
						>
					</div>
					<p class="p-hepdt__num" aria-hidden="true"><?php echo esc_html( $exterior_exone_hep_item['num'] ); ?></p>
				</div>

				<div class="p-hepdt__text" data-reveal>
					<div class="p-hepdt__head">
						<p class="p-hepdt__eng"><?php echo esc_html( $exterior_exone_hep_item['eng'] ); ?></p>
						<h3 class="p-hepdt__heading"><?php echo esc_html( $exterior_exone_hep_item['heading'] ); ?></h3>
					</div>
					<p class="p-hepdt__body"><?php echo exterior_exone_highend_lines( $exterior_exone_hep_item['body'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses 済み。 ?></p>
				</div>
			</li>
		<?php endforeach; ?>
	</ol>
</section>
