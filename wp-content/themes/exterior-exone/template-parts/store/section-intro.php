<?php
/**
 * 支店: 導入コピー。
 *
 * カンプ: PC 535:842（背景）/ 535:966（線画アイコン）/ 535:853（見出し）/ 535:855（本文）
 *         SP カンプなし（中央揃えのまま縮小）
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
?>
<section class="p-sintro" data-section="store-intro">
	<p class="p-sintro__icon">
		<?php // 535:966。カンプの枠は 171.2x89.9（線画の実体に合わせて切り出し済み）。 ?>
		<img
			src="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_intro['icon'] ) ); ?>"
			width="171"
			height="90"
			alt=""
			loading="lazy"
		>
	</p>

	<h2 class="p-sintro__heading"><?php echo esc_html( $exterior_exone_intro['heading'] ); ?></h2>

	<p class="p-sintro__lead">
		<?php foreach ( $exterior_exone_intro['lead'] as $exterior_exone_index => $exterior_exone_line ) : ?>
			<?php echo 0 === $exterior_exone_index ? '' : '<br>'; ?>
			<?php echo esc_html( $exterior_exone_line ); ?>
		<?php endforeach; ?>
	</p>
</section>
