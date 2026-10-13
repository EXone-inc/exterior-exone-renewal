<?php
/**
 * 採用ページ: FAQ（よくある質問）。
 *
 * 現サイト: .rsec--faq（.faq-item / .faq-q / .faq-a）
 *
 * 開閉は js/recruit-faq.js（複数同時に開ける。現サイトどおり）。
 * JS が無いときは回答を開いた状態で見せる（functions.php の noscript。仕様書 Q11）。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_rc_faq = exterior_exone_recruit_faq();
?>
<section class="p-rcsec p-rcsec--white p-rcfaq" data-section="recruit-faq">
	<div class="p-rcsec__inner">
		<?php exterior_exone_recruit_heading( $exterior_exone_rc_faq['en'], $exterior_exone_rc_faq['ja'] ); ?>

		<div class="p-rcfaq__list">
			<?php foreach ( $exterior_exone_rc_faq['items'] as $exterior_exone_rc_index => $exterior_exone_rc_item ) : ?>
				<?php $exterior_exone_rc_id = 'p-rcfaq-a' . ( $exterior_exone_rc_index + 1 ); ?>
				<div class="p-rcfaq__item">
					<button type="button" class="p-rcfaq__q" data-recruit-faq aria-expanded="false" aria-controls="<?php echo esc_attr( $exterior_exone_rc_id ); ?>">
						<span class="p-rcfaq__mark" aria-hidden="true">Q</span>
						<span class="p-rcfaq__qtext"><?php echo esc_html( $exterior_exone_rc_item['q'] ); ?></span>
						<?php exterior_exone_recruit_icon( 'plus', 'p-rcfaq__icon' ); ?>
					</button>
					<div class="p-rcfaq__a" id="<?php echo esc_attr( $exterior_exone_rc_id ); ?>">
						<p class="p-rcfaq__inner"><?php echo esc_html( $exterior_exone_rc_item['a'] ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
