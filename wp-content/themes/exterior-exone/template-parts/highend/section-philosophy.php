<?php
/**
 * ハイエンド: Design Philosophy。
 *
 * カンプ: PC 713:207（見出し 713:40・コピー 713:51・住宅 CG 713:50・グラデ 713:33・
 *         text box 713:58・img list 713:80）
 *         SP 733:320・733:321・733:318・733:319・733:336・733:317・733:66・733:316・733:315
 *
 * PC は「コピー左・住宅 CG 右（画面右端に接する）」、SP は CG → コピー → DESIGN GALLERY →
 * 本文 → 写真帯の縦積み。写真帯は js/plans-imglist.js のエンドレスロール（guide 713:221）。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_hep_philo = isset( $args['data'] ) ? $args['data'] : null;

if ( ! is_array( $exterior_exone_hep_philo ) ) {
	return;
}

$exterior_exone_hep_house = $exterior_exone_hep_philo['house'];
?>
<section class="p-hepphilo" data-section="highend-philosophy">
	<?php
	get_template_part(
		'template-parts/highend/heading',
		null,
		array(
			'eng'     => $exterior_exone_hep_philo['eng'],
			'heading' => $exterior_exone_hep_philo['heading'],
		)
	);
	?>

	<div class="p-hepphilo__main">
		<div class="p-hepphilo__visual" aria-hidden="true">
			<div class="p-hepphilo__house">
				<picture>
					<source
						media="(max-width: 1024px)"
						srcset="<?php echo esc_url( exterior_exone_highend_image( $exterior_exone_hep_house['sp']['file'] ) ); ?>"
						width="<?php echo esc_attr( (string) $exterior_exone_hep_house['sp']['img'][0] ); ?>"
						height="<?php echo esc_attr( (string) $exterior_exone_hep_house['sp']['img'][1] ); ?>"
					>
					<img
						src="<?php echo esc_url( exterior_exone_highend_image( $exterior_exone_hep_house['pc']['file'] ) ); ?>"
						width="<?php echo esc_attr( (string) $exterior_exone_hep_house['pc']['img'][0] ); ?>"
						height="<?php echo esc_attr( (string) $exterior_exone_hep_house['pc']['img'][1] ); ?>"
						alt=""
						loading="lazy"
					>
				</picture>
			</div>
			<div class="p-hepphilo__fade"></div>
		</div>

		<p class="p-hepphilo__copy"><?php echo exterior_exone_highend_lines( $exterior_exone_hep_philo['copy'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses 済み。 ?></p>

		<div class="p-hepphilo__text">
			<p class="p-hepphilo__gallery"><?php echo esc_html( $exterior_exone_hep_philo['gallery'] ); ?></p>
			<p class="p-hepphilo__body"><?php echo exterior_exone_highend_lines( $exterior_exone_hep_philo['body'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses 済み。 ?></p>
		</div>
	</div>

	<div class="p-hepphilo__imglist" aria-hidden="true">
		<ul class="p-hepphilo__imglist-row" data-plp-imglist>
			<?php foreach ( $exterior_exone_hep_philo['imglist'] as $exterior_exone_hep_item ) : ?>
				<li class="p-hepphilo__imglist-item">
					<img
						src="<?php echo esc_url( exterior_exone_highend_image( $exterior_exone_hep_item['file'] ) ); ?>"
						width="<?php echo esc_attr( (string) $exterior_exone_hep_item['img'][0] ); ?>"
						height="<?php echo esc_attr( (string) $exterior_exone_hep_item['img'][1] ); ?>"
						alt=""
						loading="lazy"
					>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
