<?php
/**
 * 支店: 見える安心。
 *
 * カンプ: PC 572:502（#1a1a1a 背景）/ Group 360（669:931 スケッチ + コピー）/
 *         Group 376（685:994 写真 4 枚）/ 572:617（本文 5 行）
 *         SP カンプなし（写真 4 枚を 2 列 2 段に折る）
 *
 * 続く DX EXPERIENCE（section-dx.php）と同じ黒背景の中に並ぶ。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_visible = exterior_exone_store_visible();
?>
<section class="p-svisible" data-section="store-visible">
	<div class="p-svisible__head">
		<?php // 572:567。線画スケッチはコピーの背景（opacity .3）。 ?>
		<img
			class="p-svisible__sketch"
			src="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_visible['sketch'] ) ); ?>"
			width="1200"
			height="774"
			alt=""
			loading="lazy"
		>

		<div class="p-svisible__copy">
			<p class="p-svisible__eyebrow"><?php echo esc_html( $exterior_exone_visible['eyebrow'] ); ?></p>
			<h2 class="p-svisible__heading"><?php echo esc_html( $exterior_exone_visible['heading'] ); ?></h2>
		</div>
	</div>

	<ul class="p-svisible__cards">
		<?php foreach ( $exterior_exone_visible['cards'] as $exterior_exone_card ) : ?>
			<li class="p-svisible__card">
				<?php // 写真の下端を黒に溶かし、その上に英字を置く（572:569 ほか）。 ?>
				<div class="p-svisible__media">
					<img
						src="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_card['image'] ) ); ?>"
						width="1200"
						height="800"
						alt=""
						loading="lazy"
					>
				</div>

				<p class="c-display p-svisible__card-eng"><?php echo esc_html( $exterior_exone_card['eng'] ); ?></p>
				<p class="p-svisible__card-label"><?php echo esc_html( $exterior_exone_card['label'] ); ?></p>
			</li>
		<?php endforeach; ?>
	</ul>

	<p class="p-svisible__lead">
		<?php echo wp_kses( exterior_exone_store_lines( $exterior_exone_visible['lead'] ), array( 'br' => array() ) ); ?>
	</p>
</section>
