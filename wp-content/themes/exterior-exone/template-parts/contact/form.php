<?php
/**
 * CONTACT: フォームの段の中身（CF7 のフォーム + 確認表示 + 完了表示）。
 *
 * カンプ: 個人 864:294 / 法人 860:166 / 送信ボタン 869:602・869:601
 * 確認表示・完了表示はカンプに無い（決定 §2。フォームの書式を流用。仕様書 Q13）。
 * 入力チェック・確認表示・送信・完了の切り替えは js/contact-form.js。
 * CF7 が無効・未登録のときは案内文だけを出す（仕様書 Q10）。
 *
 * $args: type（personal / corporate）
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exterior_exone_cf_type  = isset( $args['type'] ) && in_array( $args['type'], array( 'personal', 'corporate' ), true ) ? $args['type'] : 'personal';
$exterior_exone_cf_html  = function_exists( 'exterior_exone_contact_form_html' ) ? exterior_exone_contact_form_html( $exterior_exone_cf_type ) : '';
$exterior_exone_cf_texts = exterior_exone_contact_form_texts();
?>
<div
	class="p-contactform"
	data-contact-form="<?php echo esc_attr( $exterior_exone_cf_type ); ?>"
	<?php if ( '' !== $exterior_exone_cf_html ) : ?>
		data-contact-messages="<?php echo esc_attr( (string) wp_json_encode( exterior_exone_contact_form_messages( $exterior_exone_cf_type ) ) ); ?>"
		<?php if ( 'personal' === $exterior_exone_cf_type ) : ?>
			data-contact-store-label="<?php echo esc_attr( $exterior_exone_cf_texts['store_label'] ); ?>"
			data-contact-store-unselected="<?php echo esc_attr( $exterior_exone_cf_texts['unselected'] ); ?>"
		<?php endif; ?>
	<?php endif; ?>
>
	<?php if ( '' === $exterior_exone_cf_html ) : ?>
		<p class="p-contactform__fallback"><?php echo esc_html( exterior_exone_contact_form_fallback() ); ?></p>
	<?php else : ?>
		<div class="p-contactform__input" data-contact-form-view="input">
			<?php echo $exterior_exone_cf_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CF7 が組み立てたフォーム。 ?>
		</div>

		<?php // 確認表示（js/contact-form.js が入力値を文字列として一覧に入れる）。 ?>
		<div class="p-contactform__confirm" data-contact-form-view="confirm" tabindex="-1" hidden>
			<dl class="p-contactform__list" data-contact-confirm-list></dl>
			<div class="p-contactform__buttons">
				<button type="button" class="p-contactform__button p-contactform__button--back" data-contact-confirm-back><?php echo esc_html( $exterior_exone_cf_texts['back'] ); ?></button>
				<button type="button" class="p-contactform__button p-contactform__button--send" data-contact-confirm-send><?php echo esc_html( $exterior_exone_cf_texts['send'] ); ?></button>
			</div>
		</div>

		<?php // 完了表示（文面は CF7 の「送信完了」メッセージ = 決定 §5-8）。 ?>
		<p class="p-contactform__done" data-contact-form-view="done" tabindex="-1" role="status" hidden></p>
	<?php endif; ?>
</div>
