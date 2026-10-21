<?php
/**
 * 応募フォーム（/recruit/entry/。Contact Form 7「採用フォーム」）。
 *
 * カンプは無い。正は現サイト https://exterior-exone.com/recruit/entry/ と参照物
 * docs/reference-20261013-recruit/prod-entry-body.html（CF7 のレンダリング結果）・entry-form.css
 * 仕様書: docs/spec-20261013-recruit-page.md F-10・F-11（決定事項 §2-7・§2 末尾・§5）
 *
 * フォームの雛形（フォームタグとその間の HTML・メールの文面・送信先）はここが正。
 * exterior_exone_recruit_install_form() を wp eval で実行して CF7 に登録する（本番も同じ手順）:
 *   wp eval 'var_export( exterior_exone_recruit_install_form() );'
 * 登録したフォームは post meta（EXTERIOR_EXONE_RECRUIT_FORM_META）の識別子で探す。DB の ID はコードに書かない。
 * CF7 の JS を読むページの条件は inc/contact-forms.php（exterior_exone_contact_cf7_load_js()）。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 登録したフォームの識別子を持つ post meta のキーと値。
const EXTERIOR_EXONE_RECRUIT_FORM_META = '_exterior_exone_recruit_form';
const EXTERIOR_EXONE_RECRUIT_FORM_KEY  = 'entry';

/**
 * 応募フォームのページ（固定ページ entry。親が recruit のものだけ）かどうか。
 *
 * @return bool
 */
function exterior_exone_is_recruit_entry_page() {
	if ( ! is_page( 'entry' ) ) {
		return false;
	}

	$parent = (int) wp_get_post_parent_id( get_queried_object_id() );

	return $parent > 0 && 'recruit' === get_post_field( 'post_name', $parent );
}

/**
 * ページの文言（見出し・CF7 が無効なときの案内。仕様書 Q14）。
 *
 * @return array<string, string>
 */
function exterior_exone_recruit_entry_texts() {
	return array(
		'title_en' => 'ENTRY',
		'title_ja' => '応募フォーム',
		'fallback' => 'ただいま応募フォームを準備中です。お手数ですが、recruit@exone-exterior.jp までメールでご応募ください。',
	);
}

/**
 * CF7 に登録するフォームの雛形（決定 §2-7・§2 末尾。現サイトの CF7「採用フォーム」と同じ項目・markup・メール）。
 *
 * フォームタグの間の HTML は現サイトのクラス（.page_form-layout / .page_form-item01 …）のまま。
 * 「お名前」の必須の閉じ忘れは直し、label の中の p は span にする（仕様書 Q8）。
 * 本文の [your-subject] はフォームに無い項目（現サイトの設定のまま。空で出る）。
 *
 * @return array<string, mixed>
 */
function exterior_exone_recruit_form_definition() {
	$label = static function ( $text ) {
		return '<div class="p_form-labelarea"><label class="p_form"><span class="is_label">' . $text . '</span><span class="is_req">必須</span></label></div>';
	};

	$item = static function ( $text, $control ) use ( $label ) {
		return "<div class=\"page_form-item01\">\n" . $label( $text ) . "\n" . $control . "\n</div>";
	};

	$name = '<div class="p_form-namearea">'
		. '<div class="p_form-nameblock"><label class="p_form-name">姓</label>[text* your-name-last placeholder "例）　山田"]</div>'
		. '<div class="p_form-nameblock"><label class="p_form-name">名</label>[text* your-name-first placeholder "例）　太郎"]</div>'
		. '</div>';

	$form = implode(
		"\n",
		array(
			'<div class="page_form-layout">',
			$item( '希望職種', '[checkbox* your-job exclusive use_label_element "施工管理職" "提案営業職" "支店長候補" "エクステリアプランナー" "店舗事務スタッフ" "営業責任者(執行役員)"]' ),
			$item( '性別', '[checkbox* your-gender exclusive use_label_element "男性" "女性" "無回答"]' ),
			$item( 'お名前', $name ),
			$item( 'メールアドレス', '[email* your-email placeholder "例）　exone@gmail.com"]' ),
			$item( '電話番号', '[tel* your-tel placeholder "例）　0800-170-5955"]' ),
			$item( '郵便番号', '[text* your-postcode class:p-postal-code placeholder "例）　105-0001"]' ),
			$item( 'ご住所', '[text* your-address class:p-region class:p-locality class:p-street-address placeholder "例）　東京都港区芝公園"]' ),
			$item( '以降のご住所', '[text* your-extended-address class:p-extended-address placeholder "例）　3丁目1-22 JMAビル6F"]' ),
			$item( '応募先へのメッセージ', '[textarea* your-contact placeholder "メッセージや質問などご記入下さい"]' ),
			'<div class="page_form-submit"><div class="form-btn"><div class="form-btn form-btn-flat form-btn-border">[submit "送信する"]</div></div></div>',
			'</div>',
			'[response]',
		)
	);

	$body = "お名前: [your-name-last] [your-name-first]\n\n"
		. "希望職種：[your-job]\n\n"
		. "性別：[your-gender]\n\n"
		. "メールアドレス：[your-email]\n\n"
		. "電話番号：[your-tel]\n\n"
		. "題名：[your-subject]\n\n"
		. "住所：[your-postcode][your-address][your-extended-address]\n\n\n"
		. "メッセージ本文:\n[your-contact]";

	return array(
		'title'  => '採用フォーム',
		'form'   => $form,
		'mail'   => array(
			'active'             => true,
			'subject'            => 'HPからのお問い合わせ"[your-contact]"',
			'sender'             => '株式会社EXone<info@exterior-exone.com>',
			'recipient'          => 'recruit@exone-exterior.jp',
			'body'               => $body,
			'additional_headers' => 'Reply-To: [your-email]',
			'attachments'        => '',
			'use_html'           => false,
			'exclude_blank'      => false,
		),
		'mail_2' => array(
			'active' => false,
		),
	);
}

/**
 * 登録済みの採用フォーム（識別子の post meta で探し、無ければタイトルで探す）。
 *
 * @param bool $refresh 覚えている結果を捨てて探し直すか（登録直後）。
 * @return WPCF7_ContactForm|null
 */
function exterior_exone_recruit_get_form( $refresh = false ) {
	if ( exterior_exone_coexistence_enabled() ) {
		return exterior_exone_coexistence_form( 'entry' );
	}

	static $cache = false;

	if ( ! exterior_exone_contact_cf7_active() ) {
		return null;
	}

	if ( ! $refresh && false !== $cache ) {
		return $cache;
	}

	$ids = get_posts(
		array(
			'post_type'        => WPCF7_ContactForm::post_type,
			'post_status'      => 'publish',
			'posts_per_page'   => 1,
			'orderby'          => 'ID',
			'order'            => 'ASC',
			'fields'           => 'ids',
			'no_found_rows'    => true,
			'suppress_filters' => true,
			'meta_key'         => EXTERIOR_EXONE_RECRUIT_FORM_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_query_meta_key
			'meta_value'       => EXTERIOR_EXONE_RECRUIT_FORM_KEY, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_query_meta_value
		)
	);

	$form  = $ids ? WPCF7_ContactForm::get_instance( $ids[0] ) : wpcf7_get_contact_form_by_title( exterior_exone_recruit_form_definition()['title'] );
	$cache = $form ? $form : null;

	return $cache;
}

/**
 * テーマの採用フォームかどうか。
 *
 * @param WPCF7_ContactForm|null $contact_form CF7 のフォーム。
 * @return bool
 */
function exterior_exone_recruit_is_form( $contact_form ) {
	if ( ! $contact_form instanceof WPCF7_ContactForm || ! $contact_form->id() ) {
		return false;
	}

	return exterior_exone_coexistence_enabled() ? 'entry' === exterior_exone_coexistence_form_role( $contact_form->id() ) : EXTERIOR_EXONE_RECRUIT_FORM_KEY === get_post_meta( $contact_form->id(), EXTERIOR_EXONE_RECRUIT_FORM_META, true );
}

/**
 * CF7 に採用フォームを登録する（wp eval で実行）。
 *
 * 既にあれば作り直さず ID を返す（管理画面で直した文面・宛先を守る。本番の現行フォームも上書きしない）。
 * $overwrite が true のときだけテーマの雛形で上書きする。お問い合わせの 2 本には触れない。
 *
 * @param bool $overwrite 既存のフォームを雛形で上書きするか。
 * @return int|string フォームの ID。CF7 が無効・登録に失敗したら理由の文字列。
 */
function exterior_exone_recruit_install_form( $overwrite = false ) {
	if ( exterior_exone_coexistence_enabled() ) {
		return '共存モードではフォームを登録・更新しません。';
	}

	if ( ! exterior_exone_contact_cf7_active() ) {
		return 'Contact Form 7 が有効ではないため、フォームを登録しませんでした。';
	}

	$existing = exterior_exone_recruit_get_form( true );

	if ( $existing && ! $overwrite ) {
		// タイトルで見つけた既存フォームにも識別子を付けて、以後はそれで探す。
		update_post_meta( $existing->id(), EXTERIOR_EXONE_RECRUIT_FORM_META, EXTERIOR_EXONE_RECRUIT_FORM_KEY );

		return (int) $existing->id();
	}

	$definition = exterior_exone_recruit_form_definition();

	// メッセージは CF7 標準（サイトの言語）のまま。
	$messages = array();

	foreach ( wpcf7_messages() as $message_key => $message ) {
		$messages[ $message_key ] = $message['default'];
	}

	$form = wpcf7_save_contact_form(
		array(
			'id'                  => $existing ? $existing->id() : -1,
			'title'               => $definition['title'],
			'locale'              => 'ja',
			'form'                => $definition['form'],
			'mail'                => $definition['mail'],
			'mail_2'              => $definition['mail_2'],
			'messages'            => $messages,
			'additional_settings' => '',
		)
	);

	if ( ! $form || ! $form->id() ) {
		return '採用フォームを登録できませんでした。';
	}

	update_post_meta( $form->id(), EXTERIOR_EXONE_RECRUIT_FORM_META, EXTERIOR_EXONE_RECRUIT_FORM_KEY );
	exterior_exone_recruit_get_form( true );

	return (int) $form->id();
}

/**
 * フォームの HTML（CF7 が無効・未登録なら ''）。
 *
 * @return string
 */
function exterior_exone_recruit_form_html() {
	$form = exterior_exone_recruit_get_form();

	if ( ! $form ) {
		return '';
	}
	if ( exterior_exone_coexistence_enabled() ) {
		return exterior_exone_coexistence_form_html( $form, 'p-rce__cf7' );
	}

	return do_shortcode(
		sprintf(
			'[contact-form-7 id="%1$s" title="%2$s" html_class="p-rce__cf7"]',
			esc_attr( $form->hash() ),
			esc_attr( $form->title() )
		)
	);
}

/**
 * 採用フォームは CF7 の自動整形（p / br）をしない。
 *
 * @param bool                 $autop   既定値。
 * @param array<string, mixed> $options for（form / mail）。
 * @return bool
 */
function exterior_exone_recruit_cf7_autop( $autop, $options ) {
	if ( isset( $options['for'] ) && 'form' === $options['for'] && class_exists( 'WPCF7_ContactForm' ) && exterior_exone_recruit_is_form( WPCF7_ContactForm::get_current() ) ) {
		return false;
	}

	return $autop;
}
add_filter( 'wpcf7_autop_or_not', 'exterior_exone_recruit_cf7_autop', 10, 2 );

/**
 * 本文の [your-subject]（フォームに無い項目）を空で出す（決定 §2-7「空で出る」）。
 * CF7 は該当する入力が無いタグを文字のまま残すため、採用フォームのときだけ空にする。
 *
 * @param string|null        $output   既定値。
 * @param string             $name     タグ名。
 * @param bool               $html     HTML メールか。
 * @param WPCF7_MailTag|null $mail_tag タグ。
 * @return string|null
 */
function exterior_exone_recruit_cf7_subject_tag( $output, $name, $html, $mail_tag = null ) {
	if ( 'your-subject' !== $name || ! class_exists( 'WPCF7_ContactForm' ) || ! exterior_exone_recruit_is_form( WPCF7_ContactForm::get_current() ) ) {
		return $output;
	}

	return '';
}
add_filter( 'wpcf7_special_mail_tags', 'exterior_exone_recruit_cf7_subject_tag', 10, 4 );

/**
 * ローカル環境だけ、送信のたびに宛先・件名をエラーログに 1 行出す（検証用）。
 *
 * @param array<string, mixed>   $components   メールの組み立て結果。
 * @param WPCF7_ContactForm|null $contact_form CF7 のフォーム。
 * @return array<string, mixed>
 */
function exterior_exone_recruit_cf7_log_mail( $components, $contact_form = null ) {
	if ( 'local' !== wp_get_environment_type() || ! exterior_exone_recruit_is_form( $contact_form ) ) {
		return $components;
	}

	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- ローカル検証用。
	error_log(
		sprintf(
			'[exone-recruit] to=%1$s subject=%2$s',
			isset( $components['recipient'] ) ? $components['recipient'] : '',
			isset( $components['subject'] ) ? $components['subject'] : ''
		)
	);

	return $components;
}
add_filter( 'wpcf7_mail_components', 'exterior_exone_recruit_cf7_log_mail', 10, 2 );
