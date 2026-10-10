<?php
/**
 * CONTACT のフォーム（Contact Form 7）。
 *
 * カンプ: 個人フォーム 864:294 / 法人フォーム 860:166 / 送信ボタン 869:602・869:601
 * 仕様書: docs/spec-20261010-contact-page.md F-05（決定事項 §3「Contact Form 7 の使い方」・§5 末尾「CF7 フォームの雛形」）
 *
 * フォームの雛形（フォームタグ・メールの文面・送信先）はここが正。
 * exterior_exone_contact_install_forms() を wp eval で実行して CF7 に 2 本登録する（本番も同じ手順）:
 *   wp eval 'var_export( exterior_exone_contact_install_forms() );'
 * 登録したフォームは post meta（EXTERIOR_EXONE_CONTACT_FORM_META）の識別子で探す。DB の ID はコードに書かない。
 *
 * 個人フォームの宛先は隠しフィールド store-slug の支店のアドレス（exterior_exone_contact_recipient()）に
 * 送信時に差し替える。送られてきた値は 6 支店のスラッグとの照合だけに使い、宛先・件名にはデータの値だけを使う。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 登録したフォームの識別子（personal / corporate）を持つ post meta のキー。
const EXTERIOR_EXONE_CONTACT_FORM_META = '_exterior_exone_contact_form';

/**
 * 受信アドレス（2026-10-10 にユーザー支給）。
 *
 * head … 本社（法人フォーム・支店が空 / 不明の個人フォーム）/ stores … 支店スラッグ => アドレス。
 * sender … 管理者宛・自動返信の送信元（既存サイトの CF7 と同じ「株式会社EXone <info@exterior-exone.com>」。2026-10-10 の指示）。
 *
 * @return array{head: string, sender: string, stores: array<string, string>}
 */
function exterior_exone_contact_addresses() {
	return array(
		'head'   => 'info@exterior-exone.com',
		'sender' => 'info@exterior-exone.com',
		'stores' => array(
			'aomori'    => 'aomori@exterior-exone.com',
			'hirosaki'  => 'hirosaki@exterior-exone.com',
			'hachinohe' => 'hachinohe@exterior-exone.com',
			'morioka'   => 'morioka@exterior-exone.com',
			'sendai'    => 'sendai@exterior-exone.com',
			'totsuka'   => 'yokohama-totsuka@exterior-exone.com',
		),
	);
}

/**
 * 支店スラッグとして正しい値ならそのスラッグ、それ以外は ''（CONTACT の並びの 6 支店と照合）。
 *
 * @param mixed $slug 送られてきた値。
 * @return string
 */
function exterior_exone_contact_valid_store( $slug ) {
	if ( ! is_string( $slug ) || '' === $slug ) {
		return '';
	}

	$stores = exterior_exone_contact_stores();

	return isset( $stores[ $slug ] ) ? $slug : '';
}

/**
 * 問い合わせの宛先。支店なら支店のアドレス、空・不明（改ざんを含む）・法人は本社（仕様書 Q6）。
 *
 * @param mixed $slug 支店スラッグ（法人は ''）。
 * @return string
 */
function exterior_exone_contact_recipient( $slug ) {
	$addresses = exterior_exone_contact_addresses();
	$slug      = exterior_exone_contact_valid_store( $slug );

	if ( '' !== $slug && isset( $addresses['stores'][ $slug ] ) ) {
		return $addresses['stores'][ $slug ];
	}

	return $addresses['head'];
}

/**
 * メールに出す支店名（空・不明は「支店未選択」）。
 *
 * @param mixed $slug 支店スラッグ。
 * @return string
 */
function exterior_exone_contact_store_label( $slug ) {
	$slug = exterior_exone_contact_valid_store( $slug );

	if ( '' === $slug ) {
		return exterior_exone_contact_form_texts()['unselected'];
	}

	return exterior_exone_contact_stores()[ $slug ]['name'];
}

/**
 * CF7 に登録するフォームの雛形（決定 §5 末尾）。
 *
 * form の行は .p-contactform__row（ラベル + 入力欄）。確認表示（js/contact-form.js）は
 * .p-contactform__label-text の文言と入力値で一覧を作る。CF7 の自動整形はしない（wpcf7_autop_or_not）。
 * メール本文の [_exone_store_name] は検証済みの支店名、[_exone_datetime] は送信日時
 *（exterior_exone_contact_special_mail_tag()）。
 *
 * @return array<string, array<string, mixed>>
 */
function exterior_exone_contact_form_definitions() {
	$texts     = exterior_exone_contact_form_texts();
	$addresses = exterior_exone_contact_addresses();
	$sender    = '株式会社EXone <' . $addresses['sender'] . '>'; // 既存サイトの送信元と同じ表示名
	$submit    = '<div class="p-contactform__actions">[submit class:p-contactform__submit "' . $texts['submit'] . '"]</div>';
	$response  = '[response]';

	$row = static function ( $id, $label, $tag, $note = '' ) {
		$note_html = '' !== $note ? '<span class="p-contactform__note">' . $note . '</span>' : '';

		return '<div class="p-contactform__row">'
			. '<label class="p-contactform__label" for="' . $id . '"><span class="p-contactform__label-text">' . $label . '</span>' . $note_html . '</label>'
			. '<div class="p-contactform__field">' . $tag . '</div>'
			. '</div>';
	};

	$footer = "--\n送信元ページ：[_url]\n送信日時：[_exone_datetime]";
	// 送信元は info@（返信が届く）なので「送信専用」の注意書きは付けない。
	$notice = "※ このメールにお心当たりの無い場合は、お手数ですが破棄してください。\n\n"
		. "エクステリア工房 EXone\n[_site_url]";

	$personal_fields = "■ お問い合わせ先\n[_exone_store_name]\n\n"
		. "■ お名前\n[your-name]\n\n"
		. "■ メールアドレス\n[your-email]\n\n"
		. "■ 郵便番号\n[your-zip]\n\n"
		. "■ ご住所\n[your-address]\n\n"
		. "■ お問い合わせ内容\n[your-message]";

	$corporate_fields = "■ ご担当者名\n[your-name]\n\n"
		. "■ 企業名\n[your-company]\n\n"
		. "■ 企業先郵便番号\n[your-zip]\n\n"
		. "■ 企業先ご住所\n[your-address]\n\n"
		. "■ 電話番号\n[your-tel]\n\n"
		. "■ メールアドレス\n[your-email]\n\n"
		. "■ お問い合わせ内容\n[your-message]";

	$reply_lead = "このたびは、エクステリア工房 EXone へお問い合わせいただき、誠にありがとうございます。\n"
		. "以下の内容でお問い合わせを受け付けました。\n"
		. "内容を確認のうえ、担当者より改めてご連絡いたします。\n\n"
		. "────────────────────\n";
	$reply_tail = "\n────────────────────\n\n";

	return array(
		// 個人（864:294。5 行 + 支店 / 方法の隠しフィールド）
		'personal'  => array(
			'title'  => 'お問い合わせ（個人）',
			'form'   => implode(
				"\n",
				array(
					'<div class="p-contactform__rows">',
					$row( 'contact-personal-name', 'お名前', '[text* your-name id:contact-personal-name autocomplete:name]' ),
					$row( 'contact-personal-email', 'メールアドレス', '[email* your-email id:contact-personal-email autocomplete:email]' ),
					$row( 'contact-personal-zip', '郵便番号', '[text* your-zip id:contact-personal-zip minlength:7 maxlength:7 autocomplete:postal-code]', '（ハイフンなし）' ),
					$row( 'contact-personal-address', 'ご住所', '[text* your-address id:contact-personal-address autocomplete:street-address]' ),
					$row( 'contact-personal-message', 'お問い合わせ内容', '[textarea* your-message id:contact-personal-message]' ),
					'</div>',
					'[hidden store-slug]',
					'[hidden contact-method "mail"]',
					$response,
					$submit,
				)
			),
			'mail'   => array(
				'subject'            => '【お問い合わせ】個人（[_exone_store_name]）',
				'sender'             => $sender,
				'recipient'          => exterior_exone_contact_recipient( '' ), // 送信時に支店のアドレスへ差し替える
				'body'               => "コーポレートサイトのお問い合わせフォームから送信がありました。\n\n■ 種別\n個人のお客様\n\n" . $personal_fields . "\n\n" . $footer,
				'additional_headers' => 'Reply-To: [your-email]',
			),
			'mail_2' => array(
				'active'    => true,
				'subject'   => '【EXone】お問い合わせを受け付けました',
				'sender'    => $sender,
				'recipient' => '[your-email]',
				'body'      => "[your-name] 様\n\n" . $reply_lead . $personal_fields . $reply_tail . $notice,
			),
		),
		// 法人（860:166。7 行）
		'corporate' => array(
			'title'  => 'お問い合わせ（法人）',
			'form'   => implode(
				"\n",
				array(
					'<div class="p-contactform__rows">',
					$row( 'contact-corporate-name', 'ご担当者名', '[text* your-name id:contact-corporate-name autocomplete:name]' ),
					$row( 'contact-corporate-company', '企業名', '[text* your-company id:contact-corporate-company autocomplete:organization]' ),
					$row( 'contact-corporate-zip', '企業先郵便番号', '[text* your-zip id:contact-corporate-zip minlength:7 maxlength:7 autocomplete:postal-code]', '（ハイフンなし）' ),
					$row( 'contact-corporate-address', '企業先ご住所', '[text* your-address id:contact-corporate-address autocomplete:street-address]' ),
					$row( 'contact-corporate-tel', '電話番号', '[tel* your-tel id:contact-corporate-tel autocomplete:tel]' ),
					$row( 'contact-corporate-email', 'メールアドレス', '[email* your-email id:contact-corporate-email autocomplete:email]' ),
					$row( 'contact-corporate-message', 'お問い合わせ内容', '[textarea* your-message id:contact-corporate-message]' ),
					'</div>',
					$response,
					$submit,
				)
			),
			'mail'   => array(
				'subject'            => '【お問い合わせ】法人',
				'sender'             => $sender,
				'recipient'          => exterior_exone_contact_recipient( '' ),
				'body'               => "コーポレートサイトのお問い合わせフォームから送信がありました。\n\n■ 種別\n法人のお客様\n\n" . $corporate_fields . "\n\n" . $footer,
				'additional_headers' => 'Reply-To: [your-email]',
			),
			'mail_2' => array(
				'active'    => true,
				'subject'   => '【EXone】お問い合わせを受け付けました',
				'sender'    => $sender,
				'recipient' => '[your-email]',
				'body'      => "[your-company]\n[your-name] 様\n\n" . $reply_lead . $corporate_fields . $reply_tail . $notice,
			),
		),
	);
}

/**
 * CF7 が有効か。
 *
 * @return bool
 */
function exterior_exone_contact_cf7_active() {
	return class_exists( 'WPCF7_ContactForm' ) && function_exists( 'wpcf7_save_contact_form' );
}

/**
 * 登録済みのフォーム（識別子の post meta で探し、無ければタイトルで探す）。
 *
 * @param string $key     personal / corporate。
 * @param bool   $refresh 覚えている結果を捨てて探し直すか（登録直後）。
 * @return WPCF7_ContactForm|null
 */
function exterior_exone_contact_get_form( $key, $refresh = false ) {
	static $cache = array();

	if ( ! exterior_exone_contact_cf7_active() ) {
		return null;
	}

	if ( ! $refresh && array_key_exists( $key, $cache ) ) {
		return $cache[ $key ];
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
			'meta_key'         => EXTERIOR_EXONE_CONTACT_FORM_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_query_meta_key
			'meta_value'       => $key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_query_meta_value
		)
	);

	$form = null;

	if ( $ids ) {
		$form = WPCF7_ContactForm::get_instance( $ids[0] );
	} else {
		$definitions = exterior_exone_contact_form_definitions();

		if ( isset( $definitions[ $key ] ) ) {
			$form = wpcf7_get_contact_form_by_title( $definitions[ $key ]['title'] );
		}
	}

	$cache[ $key ] = $form ? $form : null;

	return $cache[ $key ];
}

/**
 * フォームの識別子（personal / corporate。テーマのフォームでなければ ''）。
 *
 * @param WPCF7_ContactForm|null $contact_form CF7 のフォーム。
 * @return string
 */
function exterior_exone_contact_form_key( $contact_form ) {
	if ( ! $contact_form instanceof WPCF7_ContactForm || ! $contact_form->id() ) {
		return '';
	}

	$key = get_post_meta( $contact_form->id(), EXTERIOR_EXONE_CONTACT_FORM_META, true );

	return in_array( $key, array( 'personal', 'corporate' ), true ) ? $key : '';
}

/**
 * CF7 にフォーム 2 本を登録する（wp eval で実行。仕様書 Q8）。
 *
 * 既にあれば作り直さず ID を返す（管理画面で直した文面・宛先を守る）。
 * $overwrite が true のときだけテーマの雛形で上書きする。
 *
 * @param bool $overwrite 既存のフォームを雛形で上書きするか。
 * @return array<string, int>|string フォームの識別子 => ID。CF7 が無効なら理由の文字列。
 */
function exterior_exone_contact_install_forms( $overwrite = false ) {
	if ( ! exterior_exone_contact_cf7_active() ) {
		return 'Contact Form 7 が有効ではないため、フォームを登録しませんでした。';
	}

	$texts  = exterior_exone_contact_form_texts();
	$result = array();

	foreach ( exterior_exone_contact_form_definitions() as $key => $definition ) {
		$existing = exterior_exone_contact_get_form( $key, true );

		if ( $existing && ! $overwrite ) {
			$result[ $key ] = (int) $existing->id();
			continue;
		}

		// CF7 標準のメッセージ（サイトの言語）に、送信完了の文面と郵便番号の文言だけを足す。
		$messages = array();

		foreach ( wpcf7_messages() as $message_key => $message ) {
			$messages[ $message_key ] = $message['default'];
		}

		$messages['mail_sent_ok'] = $texts['done'];
		$messages['invalid_zip']  = $texts['invalid_zip'];

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
			continue;
		}

		update_post_meta( $form->id(), EXTERIOR_EXONE_CONTACT_FORM_META, $key );
		$result[ $key ] = (int) $form->id();
		exterior_exone_contact_get_form( $key, true );
	}

	return $result;
}

/**
 * フォームの HTML（CF7 が無効・未登録なら ''）。
 *
 * CF7 のクライアント側の入力チェックは使わない（novalidate。入力チェックは js/contact-form.js が
 * 確認表示の前に行い、送信時はサーバー側の CF7 が改めて確かめる）。
 *
 * @param string $key personal / corporate。
 * @return string
 */
function exterior_exone_contact_form_html( $key ) {
	$form = exterior_exone_contact_get_form( $key );

	if ( ! $form ) {
		return '';
	}

	return do_shortcode(
		sprintf(
			'[contact-form-7 id="%1$s" title="%2$s" html_class="p-contactform__form novalidate"]',
			esc_attr( $form->hash() ),
			esc_attr( $form->title() )
		)
	);
}

/**
 * 確認表示前の入力チェックで使う CF7 の文言（フォームのメッセージ = 管理画面で変更可）。
 *
 * @param string $key personal / corporate。
 * @return array<string, string>
 */
function exterior_exone_contact_form_messages( $key ) {
	$form   = exterior_exone_contact_get_form( $key );
	$texts  = exterior_exone_contact_form_texts();
	$result = array();

	if ( ! $form ) {
		return $result;
	}

	foreach ( array( 'invalid_required', 'invalid_email', 'invalid_tel', 'invalid_too_long', 'invalid_too_short', 'invalid_zip' ) as $status ) {
		$message = $form->message( $status );

		if ( '' === $message && 'invalid_zip' === $status ) {
			$message = $texts['invalid_zip'];
		}

		$result[ $status ] = $message;
	}

	return $result;
}

/* ------------------------------------------------------------
   CF7 のフック
   ------------------------------------------------------------ */

// CF7 の既定 CSS はどのページでも読まない（見た目は css/contact.css）。
add_filter( 'wpcf7_load_css', '__return_false' );

/**
 * CF7 の JS は /contact/ だけで読む。
 *
 * @param bool $load 既定値。
 * @return bool
 */
function exterior_exone_contact_cf7_load_js( $load ) {
	return $load && is_page( 'contact' );
}
add_filter( 'wpcf7_load_js', 'exterior_exone_contact_cf7_load_js' );

/**
 * テーマのフォームは CF7 の自動整形（p / br）をしない。
 *
 * @param bool                 $autop   既定値。
 * @param array<string, mixed> $options for（form / mail）。
 * @return bool
 */
function exterior_exone_contact_cf7_autop( $autop, $options ) {
	if ( isset( $options['for'] ) && 'form' === $options['for'] && '' !== exterior_exone_contact_form_key( WPCF7_ContactForm::get_current() ) ) {
		return false;
	}

	return $autop;
}
add_filter( 'wpcf7_autop_or_not', 'exterior_exone_contact_cf7_autop', 10, 2 );

/**
 * 郵便番号の欄に数字キーボードを出す（CF7 の text タグに inputmode の指定が無いため）。
 *
 * @param string $html フォームの中身。
 * @return string
 */
function exterior_exone_contact_cf7_form_elements( $html ) {
	if ( '' === exterior_exone_contact_form_key( WPCF7_ContactForm::get_current() ) ) {
		return $html;
	}

	return str_replace( ' name="your-zip"', ' name="your-zip" inputmode="numeric"', $html ); // data-name には付けない
}
add_filter( 'wpcf7_form_elements', 'exterior_exone_contact_cf7_form_elements' );

/**
 * 個人フォームの送り先支店の初期値（?store= で来たとき。JS が無くても支店宛に送れるように）。
 *
 * @param array<string, mixed> $tag フォームタグ。
 * @return array<string, mixed>
 */
function exterior_exone_contact_cf7_store_tag( $tag ) {
	if ( ! is_array( $tag ) || ! isset( $tag['name'] ) || 'store-slug' !== $tag['name'] || is_admin() || ! is_page( 'contact' ) ) {
		return $tag;
	}

	$initial = exterior_exone_contact_initial_store();

	if ( '' !== $initial ) {
		$tag['values'] = array( $initial );
	}

	return $tag;
}
add_filter( 'wpcf7_form_tag', 'exterior_exone_contact_cf7_store_tag' );

/**
 * 郵便番号の文言を CF7 のメッセージに足す（管理画面の「メッセージ」で変更できる）。
 *
 * @param array<string, array<string, string>> $messages CF7 のメッセージ。
 * @return array<string, array<string, string>>
 */
function exterior_exone_contact_cf7_messages( $messages ) {
	$messages['invalid_zip'] = array(
		'description' => '郵便番号が半角数字7桁ではない（テーマ exterior-exone）',
		'default'     => exterior_exone_contact_form_texts()['invalid_zip'],
	);

	return $messages;
}
add_filter( 'wpcf7_messages', 'exterior_exone_contact_cf7_messages' );

/**
 * 郵便番号は半角数字 7 桁（決定 §5-9。必須・文字数は CF7 が先に確かめる）。
 *
 * @param WPCF7_Validation $result 検証結果。
 * @param WPCF7_FormTag    $tag    フォームタグ。
 * @return WPCF7_Validation
 */
function exterior_exone_contact_cf7_validate_zip( $result, $tag ) {
	if ( 'your-zip' !== $tag->name || ! $result->is_valid( $tag->name ) ) {
		return $result;
	}

	$contact_form = WPCF7_ContactForm::get_current();
	$submission   = WPCF7_Submission::get_instance();

	if ( '' === exterior_exone_contact_form_key( $contact_form ) || ! $submission ) {
		return $result;
	}

	$value = trim( (string) $submission->get_posted_string( $tag->name ) );

	if ( '' !== $value && ! preg_match( '/\A[0-9]{7}\z/', $value ) ) {
		$message = $contact_form->message( 'invalid_zip' );
		$result->invalidate( $tag, '' !== $message ? $message : exterior_exone_contact_form_texts()['invalid_zip'] );
	}

	return $result;
}
add_filter( 'wpcf7_validate_text*', 'exterior_exone_contact_cf7_validate_zip', 20, 2 );

/**
 * メールタグ。
 * [_exone_store_name] … 検証済みの支店名（空・不明は「支店未選択」）。
 * [_exone_datetime] … 送信日時（サイトのタイムゾーン。「2026年10月10日 13:45」の書式）。
 *
 * @param string|null        $output   既定値。
 * @param string             $name     タグ名。
 * @param bool               $html     HTML メールか。
 * @param WPCF7_MailTag|null $mail_tag タグ。
 * @return string|null
 */
function exterior_exone_contact_special_mail_tag( $output, $name, $html, $mail_tag = null ) {
	if ( '_exone_store_name' !== $name && '_exone_datetime' !== $name ) {
		return $output;
	}

	$submission = WPCF7_Submission::get_instance();

	if ( '_exone_datetime' === $name ) {
		$timestamp = $submission ? $submission->get_meta( 'timestamp' ) : 0;
		$label     = $timestamp ? wp_date( 'Y年n月j日 H:i', (int) $timestamp ) : '';
	} else {
		$label = exterior_exone_contact_store_label( $submission ? $submission->get_posted_string( 'store-slug' ) : '' );
	}

	return $html ? esc_html( $label ) : $label;
}
add_filter( 'wpcf7_special_mail_tags', 'exterior_exone_contact_special_mail_tag', 10, 4 );

/**
 * 個人フォームの宛先を選んだ支店のアドレスに差し替える（決定 §3）。
 *
 * @param WPCF7_ContactForm $contact_form CF7 のフォーム。
 * @param bool              $abort        送信を止めるか（参照）。
 * @param WPCF7_Submission  $submission   送信内容。
 */
function exterior_exone_contact_cf7_before_send_mail( $contact_form, &$abort, $submission ) {
	if ( 'personal' !== exterior_exone_contact_form_key( $contact_form ) || ! $submission instanceof WPCF7_Submission ) {
		return;
	}

	$mail              = $contact_form->prop( 'mail' );
	$mail['recipient'] = exterior_exone_contact_recipient( $submission->get_posted_string( 'store-slug' ) );

	$contact_form->set_properties( array( 'mail' => $mail ) );
}
add_action( 'wpcf7_before_send_mail', 'exterior_exone_contact_cf7_before_send_mail', 10, 3 );

/**
 * ローカル環境だけ、送信のたびに 種別・store-slug・宛先・件名 をエラーログに 1 行出す（検証用）。
 *
 * @param array<string, mixed>   $components   メールの組み立て結果。
 * @param WPCF7_ContactForm|null $contact_form CF7 のフォーム。
 * @param WPCF7_Mail|null        $mail         メール（mail / mail_2）。
 * @return array<string, mixed>
 */
function exterior_exone_contact_cf7_log_mail( $components, $contact_form = null, $mail = null ) {
	if ( 'local' !== wp_get_environment_type() ) {
		return $components;
	}

	$key = exterior_exone_contact_form_key( $contact_form );

	if ( '' === $key ) {
		return $components;
	}

	$submission = WPCF7_Submission::get_instance();
	$slug       = $submission ? $submission->get_posted_string( 'store-slug' ) : '';
	$name       = $mail instanceof WPCF7_Mail ? $mail->name() : '';

	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- ローカル検証用。
	error_log(
		sprintf(
			'[exone-contact] %1$s form=%2$s store-slug=%3$s to=%4$s subject=%5$s',
			'mail_2' === $name ? 'autoreply' : 'admin',
			'personal' === $key ? '個人' : '法人',
			wp_json_encode( (string) $slug, JSON_UNESCAPED_UNICODE ),
			isset( $components['recipient'] ) ? $components['recipient'] : '',
			isset( $components['subject'] ) ? $components['subject'] : ''
		)
	);

	return $components;
}
add_filter( 'wpcf7_mail_components', 'exterior_exone_contact_cf7_log_mail', 10, 3 );
