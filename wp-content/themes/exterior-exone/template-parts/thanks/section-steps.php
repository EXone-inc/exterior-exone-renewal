<?php
/**
 * サンクス: Next Step（見出し + 白カード 3 枚）。
 *
 * カンプ: PC 966:121・966:120 / カード 966:124・966:130・966:132
 *         SP 966:196・966:197 / カード 966:199・966:205・966:219
 *
 * 番号（01〜03）はカードの白地と影より前面で、上部がカードの外にはみ出す。
 * PC はアイコン下端揃え・中央揃え、SP は「左に番号 + アイコン、右に題 + 説明」の横組み。
 * カードはリンクではない（ホバーで変化しない）。
 *
 * $args: heading（en / ja）/ steps（title / text）/ icons（exterior_exone_thanks_step_icons()）
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_tk_heading = isset( $args['heading'] ) ? (array) $args['heading'] : array();
$exterior_exone_tk_steps   = isset( $args['steps'] ) ? (array) $args['steps'] : array();
$exterior_exone_tk_icons   = isset( $args['icons'] ) ? (array) $args['icons'] : array();
?>
<section class="p-thankssteps" data-section="thanks-steps">
	<h2 class="p-thankshead" data-reveal>
		<span class="p-thankshead__en"><?php echo esc_html( $exterior_exone_tk_heading['en'] ); ?></span>
		<span class="p-thankshead__ja"><?php echo exterior_exone_thanks_lines( $exterior_exone_tk_heading['ja'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses 済み。 ?></span>
	</h2>

	<ol class="p-thankssteps__list">
		<?php
		foreach ( $exterior_exone_tk_steps as $exterior_exone_tk_index => $exterior_exone_tk_step ) :
			$exterior_exone_tk_icon = isset( $exterior_exone_tk_icons[ $exterior_exone_tk_index ] ) ? $exterior_exone_tk_icons[ $exterior_exone_tk_index ] : null;
			?>
			<li class="p-thankssteps__item" data-reveal data-reveal-delay="<?php echo esc_attr( (string) ( $exterior_exone_tk_index * 120 ) ); ?>">
				<div class="p-thankscard">
					<span class="p-thankscard__num" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $exterior_exone_tk_index + 1 ) ); ?></span>
					<?php if ( $exterior_exone_tk_icon ) : ?>
						<span class="p-thankscard__icon">
							<img
								class="p-thankscard__img p-thankscard__img--<?php echo esc_attr( $exterior_exone_tk_icon['key'] ); ?>"
								src="<?php echo esc_url( exterior_exone_asset_url( 'images/thanks/' . $exterior_exone_tk_icon['file'] ) ); ?>"
								width="<?php echo esc_attr( (string) $exterior_exone_tk_icon['size'][0] ); ?>"
								height="<?php echo esc_attr( (string) $exterior_exone_tk_icon['size'][1] ); ?>"
								alt=""
								loading="lazy"
							>
						</span>
					<?php endif; ?>
					<div class="p-thankscard__body">
						<h3 class="p-thankscard__title"><?php echo esc_html( $exterior_exone_tk_step['title'] ); ?></h3>
						<p class="p-thankscard__text"><?php echo exterior_exone_thanks_lines( $exterior_exone_tk_step['text'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses 済み。 ?></p>
					</div>
				</div>
			</li>
		<?php endforeach; ?>
	</ol>
</section>
