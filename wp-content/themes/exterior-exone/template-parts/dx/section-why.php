<?php
/**
 * DX: why dx。
 *
 * カンプ: PC 436:384（1920x1074・436:97 / 436:289 / 436:272 / 436:101）
 *         SP  439:797〜439:923（375x693）
 *
 * 背後に敷く飾り文字「WHY DX」に見出しを重ね、灰色の円 4 個（アイコン +
 * ラベル。PC 横 1 列 / SP 2x2）と結びの本文 3 行を置く。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_dx_why = exterior_exone_dx_why();
?>
<section class="p-dxwhy" data-section="dx-why">
	<div class="p-dxwhy__head">
		<?php // 436:97 / 439:799。見出しの背後に敷く飾り文字。 ?>
		<p class="p-dxwhy__deco"><?php echo esc_html( $exterior_exone_dx_why['deco'] ); ?></p>

		<?php // 436:289 / 439:800-801。PC は 1 行・SP は 2 行に折る。 ?>
		<h2 class="p-dxwhy__heading"><?php echo esc_html( $exterior_exone_dx_why['heading'][0] ); ?><br class="u-br-sp"><?php echo esc_html( $exterior_exone_dx_why['heading'][1] ); ?></h2>
	</div>

	<?php // 436:272 / 439:798 ほか。円の中にアイコンとラベルを重ねる。 ?>
	<ul class="p-dxwhy__list">
		<?php foreach ( $exterior_exone_dx_why['items'] as $exterior_exone_item ) : ?>
			<li class="p-dxwhy__item">
				<div class="p-dxwhy__circle">
					<?php // 白線画。ラベルと合わせて意味が伝わるので装飾扱い。 ?>
					<img
						class="p-dxwhy__icon"
						src="<?php echo esc_url( exterior_exone_dx_image( $exterior_exone_item['icon'] ) ); ?>"
						alt=""
						loading="lazy"
					>
					<?php // PC は 1 行・SP は 2 行（カンプの改行位置）。 ?>
					<p class="p-dxwhy__label"><?php echo esc_html( $exterior_exone_item['label'][0] ); ?><br class="u-br-sp"><?php echo esc_html( $exterior_exone_item['label'][1] ); ?></p>
				</div>
			</li>
		<?php endforeach; ?>
	</ul>

	<?php // 436:101 / 439:923。PC は中央揃え・SP は左揃え。 ?>
	<p class="p-dxwhy__closing">
		<?php foreach ( $exterior_exone_dx_why['closing'] as $exterior_exone_line ) : ?>
			<span class="p-dxwhy__closing-line"><?php echo esc_html( $exterior_exone_line ); ?></span>
		<?php endforeach; ?>
	</p>
</section>
