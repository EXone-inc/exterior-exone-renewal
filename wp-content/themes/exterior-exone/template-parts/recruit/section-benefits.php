<?php
/**
 * 採用ページ: Benefits（待遇・福利厚生）。
 *
 * 現サイト: .rsec--benefit（.benefit-hero / .benefit-hero__copybox / .benefit-grid）
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_rc_benefits = exterior_exone_recruit_benefits();
?>
<section class="p-rcsec p-rcsec--gray p-rcbenefits" data-section="recruit-benefits">
	<div class="p-rcsec__inner">
		<?php exterior_exone_recruit_heading( $exterior_exone_rc_benefits['en'], $exterior_exone_rc_benefits['ja'] ); ?>

		<figure class="p-rcimg p-rcbenefits__hero">
			<img
				src="<?php echo esc_url( exterior_exone_recruit_image( $exterior_exone_rc_benefits['image']['file'] ) ); ?>"
				width="<?php echo esc_attr( (string) $exterior_exone_rc_benefits['image']['size'][0] ); ?>"
				height="<?php echo esc_attr( (string) $exterior_exone_rc_benefits['image']['size'][1] ); ?>"
				alt="<?php echo esc_attr( $exterior_exone_rc_benefits['image']['alt'] ); ?>"
				loading="lazy"
				decoding="async"
			>
			<figcaption class="p-rcbenefits__copy">
				<span class="p-rcbenefits__kicker"><?php echo esc_html( $exterior_exone_rc_benefits['kicker'] ); ?></span>
				<span class="p-rcbenefits__lead"><?php echo esc_html( $exterior_exone_rc_benefits['lead'][0] ); ?><br class="p-rcbenefits__br"><?php echo esc_html( $exterior_exone_rc_benefits['lead'][1] ); ?></span>
				<span class="p-rcbenefits__sub"><?php echo esc_html( $exterior_exone_rc_benefits['sub'] ); ?></span>
			</figcaption>
		</figure>

		<ul class="p-rcbenefits__grid">
			<?php foreach ( $exterior_exone_rc_benefits['cards'] as $exterior_exone_rc_card ) : ?>
				<li class="p-rcbenefits__card">
					<span class="p-rcbenefits__icon"><?php exterior_exone_recruit_icon( $exterior_exone_rc_card['icon'] ); ?></span>
					<p class="p-rcbenefits__label"><?php echo esc_html( $exterior_exone_rc_card['label'] ); ?></p>
					<p class="p-rcbenefits__value">
						<?php
						foreach ( $exterior_exone_rc_card['value'] as $exterior_exone_rc_part ) {
							echo $exterior_exone_rc_part[1]
								? '<small>' . esc_html( $exterior_exone_rc_part[0] ) . '</small>'
								: esc_html( $exterior_exone_rc_part[0] );
						}
						?>
					</p>
					<p class="p-rcbenefits__note"><?php echo esc_html( $exterior_exone_rc_card['note'] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
