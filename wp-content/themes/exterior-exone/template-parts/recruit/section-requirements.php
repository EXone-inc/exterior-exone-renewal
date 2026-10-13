<?php
/**
 * 採用ページ: Requirements（募集要項 6 職種）と、固定表示の「応募する」ボタン。
 *
 * 現サイト: #recruit-req（.req-content / .req-list / .req-outline--list）と .rec-rink_btn.is-fixed
 *
 * 6 職種とも畳んだ状態で始まり、見出しを押すと 1 つだけ開く。/recruit/#<slug> で該当職種を開く（js/recruit-req.js）。
 * JS が無いときは全部開いた状態で見せる（functions.php の noscript。仕様書 Q11）。
 * 現サイトの ul 直下の注記（div）は、表の箱（div）の中で dl の後ろに置き直す（仕様書 Q3）。
 *
 * 固定ボタンは js/recruit-apply.js が body 直下へ移し、Training に入ったら出す。JS が無いときは出さない。
 * 流れる大文字（z-index 0）が見出しの後ろに透けるよう、地はセクション（重なり順に参加しない）に塗り、中身だけ前面に出す。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_rc_req = exterior_exone_recruit_requirements();
?>
<section class="p-rcreq" data-section="recruit-requirements">
	<div class="p-rcreq__inner">
		<?php exterior_exone_recruit_heading( $exterior_exone_rc_req['en'], $exterior_exone_rc_req['ja'], 'bg p-rchead--indent' ); ?>

		<div class="p-rcreq__jobs">
			<?php foreach ( $exterior_exone_rc_req['jobs'] as $exterior_exone_rc_job ) : ?>
				<?php $exterior_exone_rc_body_id = $exterior_exone_rc_job['slug'] . '-body'; ?>
				<div class="p-rcreq__job" id="<?php echo esc_attr( $exterior_exone_rc_job['slug'] ); ?>" data-recruit-job="<?php echo esc_attr( $exterior_exone_rc_job['slug'] ); ?>">
					<h3 class="p-rcreq__head">
						<button type="button" class="p-rcreq__toggle" aria-expanded="false" aria-controls="<?php echo esc_attr( $exterior_exone_rc_body_id ); ?>" data-recruit-job-toggle>
							<span class="p-rcreq__title"><?php echo wp_kses( implode( '<br>', array_map( 'esc_html', $exterior_exone_rc_job['title'] ) ), array( 'br' => array() ) ); ?></span>
							<span class="p-rcreq__icon" aria-hidden="true"></span>
						</button>
					</h3>
					<div class="p-rcreq__body" id="<?php echo esc_attr( $exterior_exone_rc_body_id ); ?>">
						<div class="p-rcreq__table">
							<dl class="p-rcreq__list">
								<?php foreach ( $exterior_exone_rc_job['rows'] as $exterior_exone_rc_row ) : ?>
									<div class="p-rcreq__row">
										<dt class="p-rcreq__label"><?php echo esc_html( $exterior_exone_rc_row['label'] ); ?></dt>
										<dd class="p-rcreq__note">
											<?php foreach ( $exterior_exone_rc_row['lines'] as $exterior_exone_rc_line ) : ?>
												<?php if ( '' === $exterior_exone_rc_line ) : ?>
													<br>
												<?php else : ?>
													<span><?php echo esc_html( $exterior_exone_rc_line ); ?></span>
												<?php endif; ?>
											<?php endforeach; ?>
										</dd>
									</div>
								<?php endforeach; ?>
							</dl>
							<div class="p-rcreq__caption">
								<?php foreach ( $exterior_exone_rc_job['notes'] as $exterior_exone_rc_note ) : ?>
									<p><?php echo esc_html( $exterior_exone_rc_note ); ?></p>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<a class="p-rcbtn p-rcapply" href="<?php echo esc_url( exterior_exone_store_link( exterior_exone_recruit_url( 'entry/' ) ) ); ?>" data-recruit-apply="fixed">
		<span class="p-rcbtn__label"><?php echo esc_html( exterior_exone_recruit_fv()['apply'] ); ?></span>
		<span class="p-rcbtn__icon" aria-hidden="true"></span>
	</a>
</section>
