<?php
/**
 * CONTACT（お問い合わせ）ページの文言・画像・並び。
 *
 * カンプ: PC 親 853:581（デフォルト 860:142）/ SP 869:436・869:618 / guide 853:579
 * 設計メモ: docs/figma-contact-page.md
 * 仕様書: docs/spec-20261010-contact-page.md（決定事項 docs/spec-20261010-contact-page-decisions.md）
 *
 * PHP 側の文言はここに集める。支店の名前・電話・受付時間・LINE は inc/store-data.php の
 * exterior_exone_stores() から引く（二重管理しない）。改行の書式は exterior_exone_highend_lines()。
 *
 * @package exterior-exone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * お問い合わせページの URL（ヘッダー・ドロワー・フッターの CONTACT の出典）。
 *
 * 支店モードでは exterior_exone_store_link() で ?store=<slug> が付く。
 *
 * @return string
 */
function exterior_exone_contact_url() {
	return exterior_exone_store_link( home_url( '/contact/' ) );
}

/**
 * FV・タイトル・リード（PC 860:142 / SP 869:618）。
 *
 * @return array<string, mixed>
 */
function exterior_exone_contact_fv() {
	return array(
		// 864:390 / 869:620（1672x941。AI 生成の差し替え前提の仮画像）
		'image' => 'pc-fv-bg-864-390.jpg',
		'img'   => array( 1672, 941 ),
		'title' => 'CONTACT', // 853:390 / 869:671
		'sub'   => 'お問い合わせ', // 853:389 / 869:669
		// 853:372（PC 1 行）/ 869:666（SP 2 行）
		'lead'  => array(
			array( 'お問い合わせ内容に合わせて、', 'sp' ),
			'以下よりお選びください。',
		),
	);
}

/**
 * 個人 / 法人ボタン（860:139・860:138 / 869:640・869:641）。
 *
 * SP の法人の小文字はカンプの誤り（869:659）を直して PC と同じ文言。
 *
 * @return array<string, array{title: string, note: string}>
 */
function exterior_exone_contact_types() {
	return array(
		'personal'  => array(
			'title' => '個人のお客様',
			'note'  => '（外構のご相談・お見積り）',
		),
		'corporate' => array(
			'title' => '法人のお客様',
			'note'  => '（法人・企業のお問い合わせ）',
		),
	);
}

/**
 * 段の見出し（853:454・853:185 / 869:536・869:553）。
 *
 * @return array<string, string>
 */
function exterior_exone_contact_headings() {
	return array(
		'store'  => 'お問い合わせ先店舗',
		'method' => 'お問い合わせ方法',
		// 853:502。{store} は支店名。支店名との間は全角スペース（決定 §5-5）
		'tel'    => '{store}　電話番号',
	);
}

/**
 * お問い合わせ方法のボタン（PC 853:553・853:100・853:98 / SP 869:557〜559）。
 *
 * icon は images/contact/ の黒版（白は CSS の filter。決定 §5-6）。size はカンプの表示寸法。
 *
 * @return array<string, array<string, mixed>>
 */
function exterior_exone_contact_methods() {
	return array(
		'mail' => array(
			'pc'   => 'メールで相談する',
			'sp'   => 'メール相談',
			'icon' => 'icon-mail-864-375.png',
			'size' => array( 41, 24 ),
		),
		'line' => array(
			'pc'   => 'LINEで相談する', // 「LINE」だけ字間 4（CSS）
			'sp'   => 'LINE相談',
			'icon' => 'icon-line-864-379.png',
			'size' => array( 41, 39 ),
		),
		'tel'  => array(
			'pc'   => '電話で相談する',
			'sp'   => '電話相談',
			'icon' => 'icon-tel-864-381.png',
			'size' => array( 38, 38 ),
		),
	);
}

/**
 * 電話ブロックの文言（853:480・853:476）。{hours}・{closed} は支店データの文字列のまま。
 *
 * @return array<string, mixed>
 */
function exterior_exone_contact_tel() {
	return array(
		'hours' => '受付時間 {hours}（定休日：{closed}）',
		'note'  => array(
			'お電話でも、外構のご相談やお見積りを承っております。',
			'お気軽にお問い合わせください。',
		),
	);
}

/**
 * 「お問い合わせ先店舗」の並び（カンプ順・北 → 南。決定 §4）。
 *
 * CONTACT だけこの順。フッター STORE は exterior_exone_stores() の順のまま。
 *
 * @return array<int, string> 支店スラッグ。
 */
function exterior_exone_contact_store_order() {
	return array( 'aomori', 'hirosaki', 'hachinohe', 'morioka', 'sendai', 'totsuka' );
}

/**
 * 並び順どおりの支店データ（slug を含む）。データに無いスラッグは飛ばす。
 *
 * @return array<string, array<string, string>>
 */
function exterior_exone_contact_stores() {
	$stores = exterior_exone_stores();
	$list   = array();

	foreach ( exterior_exone_contact_store_order() as $slug ) {
		if ( isset( $stores[ $slug ] ) ) {
			$list[ $slug ] = array_merge( array( 'slug' => $slug ), $stores[ $slug ] );
		}
	}

	return $list;
}

/**
 * 電話ブロックに出す 1 支店ぶんの文言（853:502・853:494・853:480。決定 §5-5）。
 *
 * サーバー側の初期表示と、店舗ボタンの data-* 属性（JS が差し替えに使う）の両方の出典。
 *
 * @param array<string, string>|null $store exterior_exone_contact_stores() の 1 件（null なら空）。
 * @return array{title: string, number: string, url: string, hours: string, line: string}
 */
function exterior_exone_contact_tel_view( $store ) {
	if ( ! is_array( $store ) ) {
		return array(
			'title'  => '',
			'number' => '',
			'url'    => '',
			'hours'  => '',
			'line'   => '',
		);
	}

	$headings = exterior_exone_contact_headings();
	$tel      = exterior_exone_contact_tel();

	return array(
		'title'  => str_replace( '{store}', $store['name'], $headings['tel'] ),
		'number' => isset( $store['tel'] ) ? $store['tel'] : '',
		'url'    => isset( $store['tel_url'] ) ? $store['tel_url'] : '',
		'hours'  => str_replace(
			array( '{hours}', '{closed}' ),
			array( isset( $store['hours'] ) ? $store['hours'] : '', isset( $store['closed'] ) ? $store['closed'] : '' ),
			$tel['hours']
		),
		'line'   => isset( $store['line_url'] ) ? $store['line_url'] : '',
	);
}

/**
 * ?store=<slug> で来たときに最初から選ぶ支店のスラッグ（無ければ ''）。
 *
 * 判定は支店モード（exterior_exone_store_mode()。入力値は照合だけ）。CONTACT の並びに無い支店は選ばない。
 *
 * @return string
 */
function exterior_exone_contact_initial_store() {
	$store = exterior_exone_store_mode();

	if ( ! $store || ! in_array( $store['slug'], exterior_exone_contact_store_order(), true ) ) {
		return '';
	}

	return $store['slug'];
}

/**
 * フォームがまだ無いとき（CF7 が無効・未登録）にフォームの段へ出す案内（仕様書 Q10）。
 *
 * @return string
 */
function exterior_exone_contact_form_fallback() {
	return 'ただいまフォームを準備中です。お手数ですが、お電話または LINE でお問い合わせください。';
}

/**
 * フォームの確認表示・完了表示の文言（カンプに無い。仕様書 Q13・決定 §5-8）。
 *
 * store_label … 確認の一覧の先頭行（個人のみ）/ unselected … 支店が空・不明のとき（仕様書 Q6）。
 *
 * @return array<string, string>
 */
function exterior_exone_contact_form_texts() {
	return array(
		'store_label' => 'お問い合わせ先',
		'unselected'  => '支店未選択',
		'back'        => '修正する',
		'send'        => '送信する',
		'submit'      => '送信内容を確認する', // 869:604 / 869:598
		// 決定 §5-8（CF7 の「送信完了」メッセージとして登録する）
		'done'        => 'お問い合わせを受け付けました。内容を確認のうえ、担当者よりご連絡いたします。自動返信メールをお送りしましたので、届かない場合は迷惑メールフォルダをご確認ください。',
		// 郵便番号が半角数字 7 桁でないとき（CF7 に該当する標準文言が無いので追加。管理画面で変更可）
		'invalid_zip' => '郵便番号は半角数字7桁（ハイフンなし）で入力してください。',
	);
}

/**
 * CONTACT ページ用の画像 URL を返す（WebP があれば優先）。
 *
 * images/contact/webp/ に同名の .webp があればそちらを返す（images/works/ と同じ）。
 *
 * @param string $file images/contact/ 配下のファイル名。
 * @return string
 */
function exterior_exone_contact_image( $file ) {
	$webp = 'images/contact/webp/' . preg_replace( '/\.(jpe?g|png)$/i', '.webp', $file );

	if ( $webp !== 'images/contact/webp/' . $file && file_exists( get_theme_file_path( $webp ) ) ) {
		return exterior_exone_asset_url( $webp );
	}

	return exterior_exone_asset_url( 'images/contact/' . $file );
}
