<?php
/**
 * 支店: 導入コピー。
 *
 * カンプ: PC 612:763（アイコン 612:227・見出し 612:182・本文 612:183）
 *         SP 638:95（638:98・638:97・638:96。見出し 2 行・本文は左揃え）
 *
 * 本文の「EXone［支店名］」「［地域名］」は支店ごとに入れ替わる。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_store = isset( $args['store'] ) ? $args['store'] : exterior_exone_current_store();

if ( ! $exterior_exone_store ) {
	return;
}

$exterior_exone_intro = exterior_exone_store_intro( $exterior_exone_store );

// 1 行目のあとは両幅で改行、2 行目のあとは PC だけ改行（SP は 1 段落につなぐ）。
$exterior_exone_intro_lead = '';

foreach ( $exterior_exone_intro['lead'] as $exterior_exone_index => $exterior_exone_line ) {
	if ( 1 === $exterior_exone_index ) {
		$exterior_exone_intro_lead .= '<br>';
	} elseif ( 1 < $exterior_exone_index ) {
		$exterior_exone_intro_lead .= '<br class="p-sintro__br-pc">';
	}

	$exterior_exone_intro_lead .= esc_html( $exterior_exone_line );
}
?>
<section class="p-sintro" data-section="store-intro">
	<p class="p-sintro__icon">
		<?php // 612:227。線画の実体に合わせて切り出し済み。 ?>
		<img
			src="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_intro['icon'] ) ); ?>"
			width="171"
			height="90"
			alt=""
			loading="lazy"
		>
	</p>

	<h2 class="p-sintro__heading"><?php foreach ( $exterior_exone_intro['heading'] as $exterior_exone_part ) : ?><span class="p-sintro__heading-line"><?php echo esc_html( $exterior_exone_part ); ?></span><?php endforeach; ?></h2>

	<p class="p-sintro__lead"><?php echo wp_kses( $exterior_exone_intro_lead, array( 'br' => array( 'class' => array() ) ) ); ?></p>
</section>
