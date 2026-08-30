<?php
/**
 * 企業情報: 私たちが作る体験（4 カード）。
 *
 * カンプ: PC 917:1414・917:1590-1607 のみ。SP カンプには存在しないため
 *         css/company.css で 768px 未満は非表示にしている。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_visible_cards = exterior_exone_company_visible_cards();
?>
<section class="p-cvisible" data-section="company-visible">
	<div class="p-cvisible__inner">
		<h2 class="c-company-eng">The Experience We Build</h2>
		<p class="c-company-jp">私たちが作る体験</p>

		<ul class="p-cvisible__cards">
			<?php foreach ( $exterior_exone_visible_cards as $exterior_exone_card ) : ?>
				<li class="p-cvisible__card">
					<img
						class="p-cvisible__card-image"
						src="<?php echo esc_url( exterior_exone_company_image( $exterior_exone_card['image'] ) ); ?>"
						width="378"
						height="481"
						alt=""
						loading="lazy"
					>
					<p class="c-display p-cvisible__card-eng"><?php echo esc_html( $exterior_exone_card['eng'] ); ?></p>
					<p class="p-cvisible__card-jp"><?php echo esc_html( $exterior_exone_card['jp'] ); ?></p>
					<p class="p-cvisible__card-desc"><?php echo nl2br( esc_html( $exterior_exone_card['desc'] ) ); ?></p>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
