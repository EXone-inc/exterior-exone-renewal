<?php
/**
 * 採用ページ: Work（働く人を知る）とインタビューのモーダル 3 つ。
 *
 * 現サイト: .content-bg > .recruit-content > .recruit-work / .work_iv-list / #Modal_menu1〜3（popover）
 *
 * モーダルは <dialog>（showModal）で作り直す（決定 §2-6。Popover API は使わない）。
 * 開閉・フェード・背面スクロールの停止は js/recruit-modal.js。
 * JS が無いときは＋ボタンを出さない（functions.php の noscript。仕様書 Q11）。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_rc_work = exterior_exone_recruit_work();
?>
<section class="p-rcbg p-rcwork" data-section="recruit-work">
	<div class="p-rcbg__inner">
		<?php exterior_exone_recruit_heading( $exterior_exone_rc_work['en'], $exterior_exone_rc_work['ja'], 'bg' ); ?>

		<ul class="p-rcwork__list">
			<?php foreach ( $exterior_exone_rc_work['members'] as $exterior_exone_rc_index => $exterior_exone_rc_member ) : ?>
				<?php $exterior_exone_rc_no = (string) ( $exterior_exone_rc_index + 1 ); ?>
				<li class="p-rcwork__card">
					<figure class="p-rcwork__photo">
						<img
							src="<?php echo esc_url( exterior_exone_recruit_image( $exterior_exone_rc_member['card'] ) ); ?>"
							width="764"
							height="818"
							alt=""
							loading="lazy"
							decoding="async"
						>
					</figure>
					<div class="p-rcwork__text">
						<p class="p-rcwork__dept"><?php echo esc_html( $exterior_exone_rc_member['dept'] ); ?></p>
						<div class="p-rcwork__des">
							<p><?php echo esc_html( $exterior_exone_rc_member['role'] ); ?></p>
							<p><?php echo esc_html( $exterior_exone_rc_member['year'] ); ?></p>
						</div>
					</div>
					<button
						type="button"
						class="p-rcwork__open"
						data-recruit-modal-open="<?php echo esc_attr( $exterior_exone_rc_no ); ?>"
						aria-haspopup="dialog"
						aria-label="<?php echo esc_attr( sprintf( '%1$s（%2$s・%3$s）のインタビューを開く', $exterior_exone_rc_member['role'], $exterior_exone_rc_member['dept'], $exterior_exone_rc_member['year'] ) ); ?>"
					>
						<?php exterior_exone_recruit_icon( 'plus', 'p-rcwork__open-icon' ); ?>
					</button>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>

	<?php foreach ( $exterior_exone_rc_work['members'] as $exterior_exone_rc_index => $exterior_exone_rc_member ) : ?>
		<?php
		$exterior_exone_rc_no = (string) ( $exterior_exone_rc_index + 1 );
		$exterior_exone_rc_id = 'p-rcmodal-' . $exterior_exone_rc_no;
		?>
		<dialog class="p-rcmodal" id="<?php echo esc_attr( $exterior_exone_rc_id ); ?>" data-recruit-modal="<?php echo esc_attr( $exterior_exone_rc_no ); ?>" aria-labelledby="<?php echo esc_attr( $exterior_exone_rc_id . '-label ' . $exterior_exone_rc_id . '-name' ); ?>">
			<button type="button" class="p-rcmodal__close" data-recruit-modal-close aria-label="閉じる">
				<?php exterior_exone_recruit_icon( 'xmark', 'p-rcmodal__close-icon' ); ?>
			</button>

			<div class="p-rcmodal__panel" data-recruit-modal-panel>
				<p class="p-rcmodal__label" id="<?php echo esc_attr( $exterior_exone_rc_id . '-label' ); ?>"><?php echo esc_html( $exterior_exone_rc_work['label'] ); ?></p>

				<div class="p-rcmodal__body">
					<div class="p-rcmodal__head">
						<h3 class="p-rcmodal__name" id="<?php echo esc_attr( $exterior_exone_rc_id . '-name' ); ?>">
							<span class="p-rcmodal__role"><?php echo esc_html( $exterior_exone_rc_member['name'] ); ?></span>
							<span class="p-rcmodal__join"><?php echo esc_html( $exterior_exone_rc_member['join'] ); ?></span>
						</h3>
						<figure class="p-rcmodal__photo">
							<img
								src="<?php echo esc_url( exterior_exone_recruit_image( $exterior_exone_rc_member['photo'] ) ); ?>"
								width="<?php echo esc_attr( (string) $exterior_exone_rc_member['size'][0] ); ?>"
								height="<?php echo esc_attr( (string) $exterior_exone_rc_member['size'][1] ); ?>"
								alt=""
								loading="lazy"
								decoding="async"
							>
						</figure>
					</div>

					<ul class="p-rcmodal__qa">
						<?php foreach ( $exterior_exone_rc_member['qa'] as $exterior_exone_rc_qindex => $exterior_exone_rc_qa ) : ?>
							<li class="p-rcmodal__item">
								<p class="p-rcmodal__q">
									<span class="p-rcmodal__qnum"><?php echo esc_html( 'Q' . ( $exterior_exone_rc_qindex + 1 ) ); ?></span>
									<span class="p-rcmodal__qtext"><?php echo esc_html( $exterior_exone_rc_qa['q'] ); ?></span>
								</p>
								<p class="p-rcmodal__a">
									<?php foreach ( $exterior_exone_rc_qa['a'] as $exterior_exone_rc_line ) : ?>
										<?php $exterior_exone_rc_gap = is_array( $exterior_exone_rc_line ) && ! empty( $exterior_exone_rc_line['gap'] ); ?>
										<span class="p-rcmodal__line<?php echo $exterior_exone_rc_gap ? ' p-rcmodal__line--gap' : ''; ?>"><?php echo esc_html( is_array( $exterior_exone_rc_line ) ? $exterior_exone_rc_line['text'] : $exterior_exone_rc_line ); ?></span>
									<?php endforeach; ?>
								</p>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
		</dialog>
	<?php endforeach; ?>
</section>
