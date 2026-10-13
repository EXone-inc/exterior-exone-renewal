<?php
/**
 * 採用ページ: Training（教育・研修体制）。
 *
 * 現サイト: .rsec--edu（.rsec-title / .rsec-lead / .edu-layout / .edu-list）
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_rc_training = exterior_exone_recruit_training();
?>
<section class="p-rcsec p-rcsec--white p-rctraining" data-section="recruit-training">
	<div class="p-rcsec__inner">
		<?php exterior_exone_recruit_heading( $exterior_exone_rc_training['en'], $exterior_exone_rc_training['ja'] ); ?>

		<p class="p-rcsec__lead"><?php echo wp_kses( implode( '<br>', array_map( 'esc_html', $exterior_exone_rc_training['lead'] ) ), array( 'br' => array() ) ); ?></p>

		<div class="p-rctraining__layout">
			<figure class="p-rcimg p-rctraining__media">
				<img
					src="<?php echo esc_url( exterior_exone_recruit_image( $exterior_exone_rc_training['image']['file'] ) ); ?>"
					width="<?php echo esc_attr( (string) $exterior_exone_rc_training['image']['size'][0] ); ?>"
					height="<?php echo esc_attr( (string) $exterior_exone_rc_training['image']['size'][1] ); ?>"
					alt="<?php echo esc_attr( $exterior_exone_rc_training['image']['alt'] ); ?>"
					loading="lazy"
					decoding="async"
				>
			</figure>

			<ul class="p-rctraining__list">
				<?php foreach ( $exterior_exone_rc_training['items'] as $exterior_exone_rc_item ) : ?>
					<li class="p-rctraining__item">
						<span class="p-rctraining__icon"><?php exterior_exone_recruit_icon( $exterior_exone_rc_item['icon'] ); ?></span>
						<div class="p-rctraining__text">
							<p class="p-rctraining__cat">
								<?php echo esc_html( $exterior_exone_rc_item['cat'] ); ?><?php if ( '' !== $exterior_exone_rc_item['note'] ) : ?><span class="p-rctraining__note"><?php echo esc_html( $exterior_exone_rc_item['note'] ); ?></span><?php endif; ?>
							</p>
							<p class="p-rctraining__desc"><?php echo esc_html( $exterior_exone_rc_item['desc'] ); ?></p>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</section>
