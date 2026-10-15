<?php
/**
 * サンクス: Explore EXone（見出し + 写真カード 3 枚。カード全体が 1 つのリンク）。
 *
 * カンプ: PC 966:157・966:156 / カード 966:164・966:177・966:172（暗幕 966:183）
 *         SP 966:226・966:227 / カード 966:229・966:233・966:243（暗幕 966:244）
 *
 * 写真は各ページの既存画像（DX も静止画。動画は読み込まない）。DX だけ 10% の暗幕。
 * ホバーは TOP の PLANS カードと同じ（少し浮いて影が深くなる。タッチ端末では変化なし）。
 *
 * $args: heading（en / ja）/ cards（exterior_exone_thanks_explore()）
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_tk_heading = isset( $args['heading'] ) ? (array) $args['heading'] : array();
$exterior_exone_tk_cards   = isset( $args['cards'] ) ? (array) $args['cards'] : array();
?>
<section class="p-thanksexplore" data-section="thanks-explore">
	<h2 class="p-thankshead" data-reveal>
		<span class="p-thankshead__en"><?php echo esc_html( $exterior_exone_tk_heading['en'] ); ?></span>
		<span class="p-thankshead__ja"><?php echo exterior_exone_thanks_lines( $exterior_exone_tk_heading['ja'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses 済み。 ?></span>
	</h2>

	<ul class="p-thanksexplore__list">
		<?php foreach ( $exterior_exone_tk_cards as $exterior_exone_tk_index => $exterior_exone_tk_card ) : ?>
			<li class="p-thanksexplore__item" data-reveal data-reveal-delay="<?php echo esc_attr( (string) ( $exterior_exone_tk_index * 120 ) ); ?>">
				<a class="p-thankslink<?php echo $exterior_exone_tk_card['scrim'] ? ' p-thankslink--scrim' : ''; ?>" href="<?php echo esc_url( $exterior_exone_tk_card['url'] ); ?>">
					<img
						class="p-thankslink__img"
						src="<?php echo esc_url( exterior_exone_thanks_image( $exterior_exone_tk_card['image'] ) ); ?>"
						width="<?php echo esc_attr( (string) $exterior_exone_tk_card['img'][0] ); ?>"
						height="<?php echo esc_attr( (string) $exterior_exone_tk_card['img'][1] ); ?>"
						alt=""
						loading="lazy"
					>
					<span class="p-thankslink__en"><?php echo esc_html( $exterior_exone_tk_card['en'] ); ?></span>
					<span class="p-thankslink__ja"><?php echo esc_html( $exterior_exone_tk_card['ja'] ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</section>
