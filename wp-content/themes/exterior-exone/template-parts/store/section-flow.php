<?php
/**
 * 支店: FLOW。
 *
 * カンプ: PC 612:824（612:260・612:291・612:288 見出し 3 点セット）/
 *         612:376 = 矢羽根 7 個（235.7x55.1・ピッチ 229.4）/
 *         612:416 = 行 7 本（1200x149.3・ピッチ 169.3）/
 *         612:417（7 行目だけ下辺中央が V 字に尖り、次セクションの写真に食い込む）
 *         SP 641:466〜641:514（矢羽根なし。白の縦カード 7 枚とカード間の縦矢印 6 本）
 *
 * 矢羽根の形と段階色、7 行目の V 字、SP の縦矢印は CSS で作る。
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

				<?php // sp_join の行は SP で改行せず 1 段落にする（641:478）。 ?>
				<p class="p-sflow__row-desc<?php echo empty( $exterior_exone_step['sp_join'] ) ? '' : ' p-sflow__row-desc--sp-join'; ?>">
					<?php echo wp_kses( exterior_exone_store_lines( $exterior_exone_step['lines'] ), array( 'br' => array() ) ); ?>
				</p>
			</li>
		<?php endforeach; ?>
	</ol>
</section>
