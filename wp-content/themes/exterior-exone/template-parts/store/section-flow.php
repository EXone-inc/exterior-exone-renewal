<?php
/**
 * 支店: FLOW。
 *
 * カンプ: PC 661:12（#f3f3f4 背景）/ 661:13-15（見出し 3 点セット）/
 *         Group 371（669:962）= 矢羽根 7 個（235.7x55.1・ピッチ 229.4）/
 *         Group 370（669:961）= 行 7 本（1200x149.3・ピッチ 169.3）/
 *         669:259（7 行目だけ下辺が弧で次セクションの写真に食い込む）
 *         SP カンプなし（矢羽根・行とも縦積み）
 *
 * 矢羽根の形と段階色、7 行目の弧は CSS（clip-path とトークン）で作る。
 * 矢羽根のラベルは下の行の見出しと同じ文字列なので、読み上げでは重複させない。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_flow = exterior_exone_store_flow();
?>
<section class="p-sflow" data-section="store-flow">
	<h2 class="c-store-title"><?php echo esc_html( $exterior_exone_flow['title'] ); ?></h2>
	<p class="c-store-jp"><?php echo esc_html( $exterior_exone_flow['jp'] ); ?></p>

	<p class="c-store-lead">
		<?php echo wp_kses( exterior_exone_store_lines( $exterior_exone_flow['lead'] ), array( 'br' => array() ) ); ?>
	</p>

	<?php // Group 371。下の行と同じ内容なので支援技術には出さない。 ?>
	<ul class="p-sflow__chevrons" aria-hidden="true">
		<?php foreach ( $exterior_exone_flow['steps'] as $exterior_exone_step ) : ?>
			<li class="p-sflow__chevron"><?php echo esc_html( $exterior_exone_step['label'] ); ?></li>
		<?php endforeach; ?>
	</ul>

	<ol class="p-sflow__rows">
		<?php foreach ( $exterior_exone_flow['steps'] as $exterior_exone_step ) : ?>
			<li class="p-sflow__row">
				<span class="p-sflow__number"><?php echo esc_html( $exterior_exone_step['number'] ); ?></span>

				<p class="p-sflow__row-title"><?php echo esc_html( $exterior_exone_step['label'] ); ?></p>

				<p class="p-sflow__row-desc">
					<?php echo wp_kses( exterior_exone_store_lines( $exterior_exone_step['lines'] ), array( 'br' => array() ) ); ?>
				</p>
			</li>
		<?php endforeach; ?>
	</ol>
</section>
