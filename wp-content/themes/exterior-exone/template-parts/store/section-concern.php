<?php
/**
 * 支店: 不安提示。
 *
 * カンプ: PC 612:764（黒帯 612:222・見出し 612:223・悩み 612:176・本文 612:175・
 *         スケッチ 612:153）
 *         SP 638:142（638:147・638:148・638:145・638:150・638:144）
 *
 * PC はスケッチを見出しの右側に置く（左右反転で配置されている）。文字の可読性を
 * 守るため、テキストより下のレイヤーに敷く。SP は悩みと本文の間に挟む
 *（DOM も悩みと本文の間に置き、PC だけ絶対配置で右へ出す）。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_concern = exterior_exone_store_concern();
?>
<section class="p-sconcern" data-section="store-concern">
	<div class="p-sconcern__body">
		<h2 class="p-sconcern__heading"><?php foreach ( $exterior_exone_concern['heading'] as $exterior_exone_part ) : ?><span class="p-sconcern__heading-line"><?php echo esc_html( $exterior_exone_part ); ?></span><?php endforeach; ?></h2>

		<ul class="p-sconcern__list">
			<?php foreach ( $exterior_exone_concern['worries'] as $exterior_exone_worry ) : ?>
				<li class="p-sconcern__item">「 <?php echo esc_html( $exterior_exone_worry ); ?> 」</li>
			<?php endforeach; ?>
		</ul>

		<img
			class="p-sconcern__sketch"
			src="<?php echo esc_url( exterior_exone_store_image( $exterior_exone_concern['sketch'] ) ); ?>"
			width="1200"
			height="800"
			alt=""
			aria-hidden="true"
			loading="lazy"
		>

		<p class="p-sconcern__note"><?php echo wp_kses( exterior_exone_store_lines( $exterior_exone_concern['notes'] ), array( 'br' => array() ) ); ?></p>
	</div>
</section>
