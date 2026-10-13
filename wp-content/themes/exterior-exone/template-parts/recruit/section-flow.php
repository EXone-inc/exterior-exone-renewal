<?php
/**
 * 採用ページ: Flow（選考フロー）。
 *
 * 現サイト: .content-bg > .recruit-content > .recruit-flow（.flow-list / .flow-item / .flow-note）
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_rc_flow = exterior_exone_recruit_flow();
?>
<section class="p-rcbg p-rcflow" data-section="recruit-flow">
	<div class="p-rcbg__inner">
		<?php exterior_exone_recruit_heading( $exterior_exone_rc_flow['en'], $exterior_exone_rc_flow['ja'], 'bg p-rchead--indent' ); ?>

		<ol class="p-rcflow__list">
			<?php foreach ( $exterior_exone_rc_flow['steps'] as $exterior_exone_rc_step ) : ?>
				<li class="p-rcflow__item">
					<span class="p-rcflow__step"><?php echo esc_html( $exterior_exone_rc_flow['step'] ); ?></span>
					<span class="p-rcflow__num"><?php echo esc_html( $exterior_exone_rc_step['num'] ); ?></span>
					<span class="p-rcflow__icon"><?php exterior_exone_recruit_icon( $exterior_exone_rc_step['icon'] ); ?></span>
					<h3 class="p-rcflow__title"><?php echo esc_html( $exterior_exone_rc_step['title'] ); ?></h3>
				</li>
			<?php endforeach; ?>
		</ol>

		<p class="p-rcflow__note"><?php echo esc_html( $exterior_exone_rc_flow['note'] ); ?></p>
	</div>
</section>
