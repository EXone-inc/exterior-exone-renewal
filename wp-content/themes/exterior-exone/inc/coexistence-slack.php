<?php
/** 新テーマ専用フォームの Slack 通知。既存フォーム・メール設定は変更しない。 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Exterior_Exone_Slack_Blocked extends Exception {}

function exterior_exone_slack_enabled() {
	return defined( 'EXR_COEXISTENCE' ) && true === EXR_COEXISTENCE;
}

function exterior_exone_slack_error() {
	return new WP_Error( 'exone_slack_invalid', 'フォームまたは通知設定を確認できませんでした。現在の設定をもう一度確認してください。' );
}

/** ID・タイトルの代用検索をせず、取り込み済みの3役割を一意に解決する。 */
function exterior_exone_slack_forms() {
	if ( ! exterior_exone_slack_enabled() ) { return exterior_exone_slack_error(); }
	$posts = get_posts( array(
		'post_type' => 'exr_form', 'post_status' => array_values( get_post_stati() ), 'posts_per_page' => -1, 'orderby' => 'ID', 'order' => 'ASC',
		'suppress_filters' => true, 'meta_query' => array( array( 'key' => '_exr_dataset', 'value' => 'exterior-exone-renewal:v1' ) ),
	) );
	if ( 3 !== count( $posts ) ) { return exterior_exone_slack_error(); }
	$forms = array(); $keys = array();
	foreach ( $posts as $post ) {
		if ( ! $post instanceof WP_Post || 'exr_form' !== $post->post_type || 'exr_stored' !== $post->post_status || $post->post_date > current_time( 'mysql' ) ) { return exterior_exone_slack_error(); }
		$values = array();
		foreach ( array( '_exr_dataset', '_exr_source_status', '_exr_import_state', '_exr_source_key', '_exr_form_role' ) as $key ) {
			$rows = get_post_meta( $post->ID, $key, false );
			if ( 1 !== count( $rows ) || ! is_string( $rows[0] ) ) { return exterior_exone_slack_error(); }
			$values[ $key ] = $rows[0];
		}
		$role = $values['_exr_form_role']; $key = $values['_exr_source_key'];
		if ( 'exterior-exone-renewal:v1' !== $values['_exr_dataset'] || 'publish' !== $values['_exr_source_status'] || 'complete' !== $values['_exr_import_state']
			|| ! in_array( $role, array( 'personal', 'corporate', 'entry' ), true ) || isset( $forms[ $role ] ) || isset( $keys[ $key ] )
			|| ! preg_match( '/\Aexterior-exone-renewal:v1:post:wpcf7_contact_form:[1-9][0-9]*\z/', $key ) ) { return exterior_exone_slack_error(); }
		$forms[ $role ] = (int) $post->ID; $keys[ $key ] = true;
	}
	return $forms;
}

function exterior_exone_slack_url( $url ) {
	return is_string( $url ) && strlen( $url ) <= 512 && 1 === preg_match( '~\Ahttps://hooks\.slack\.com/services/[A-Za-z0-9]+/[A-Za-z0-9]+/[A-Za-z0-9]+\z~', $url );
}

/** この配列だけを _ctz_zapier に保存する。メール送信は必ず維持する。 */
function exterior_exone_slack_settings( $url, $active ) {
	return array( 'activate' => $active ? '1' : '0', 'hook_url' => $url ? array( $url ) : array(), 'send_mail' => '1', 'custom_method' => 'POST',
		'files_send_content' => '0', 'special_mail_tags' => '', 'custom_headers' => '', 'custom_body' => '', 'error_mails' => array(), 'accepted_statuses' => array( 200, 201, 202, 204, 205 ) );
}

function exterior_exone_slack_read_settings( $id ) {
	$rows = get_post_meta( $id, '_ctz_zapier', false );
	if ( ! $rows ) { return array( 'url' => '', 'active' => false, 'rows' => array() ); }
	if ( 1 !== count( $rows ) || ! is_array( $rows[0] ) ) { return exterior_exone_slack_error(); }
	$value = $rows[0]; $urls = isset( $value['hook_url'] ) ? $value['hook_url'] : null;
	if ( ! is_array( $urls ) || count( $urls ) > 1 || array_values( $urls ) !== $urls
		|| ! isset( $value['activate'] ) || ! in_array( $value['activate'], array( '0', '1' ), true ) ) { return exterior_exone_slack_error(); }
	$url = $urls ? $urls[0] : '';
	if ( ( '' !== $url && ! exterior_exone_slack_url( $url ) ) || ( '1' === $value['activate'] && '' === $url ) ) { return exterior_exone_slack_error(); }
	return array( 'url' => $url, 'active' => '1' === $value['activate'], 'rows' => $rows );
}

function exterior_exone_slack_group_settings( $forms ) {
	$settings = array();
	foreach ( $forms as $role => $id ) {
		$settings[ $role ] = exterior_exone_slack_read_settings( $id );
		if ( is_wp_error( $settings[ $role ] ) ) { return exterior_exone_slack_error(); }
	}
	if ( $settings['personal']['url'] !== $settings['corporate']['url'] || $settings['personal']['active'] !== $settings['corporate']['active'] ) { return exterior_exone_slack_error(); }
	return $settings;
}

/** 認可済み画面からだけ呼ぶ保存処理。全件検査後、競合を見ながら1メタだけ変更する。 */
function exterior_exone_slack_save( $input ) {
	if ( ! exterior_exone_slack_enabled() || ! current_user_can( 'manage_options' ) || ! class_exists( 'CFTZ_Module_CF7' ) || ! is_array( $input ) ) { return exterior_exone_slack_error(); }
	$forms = exterior_exone_slack_forms();
	if ( is_wp_error( $forms ) ) { return $forms; }
	$settings = exterior_exone_slack_group_settings( $forms );
	if ( is_wp_error( $settings ) ) { return $settings; }
	$next = array();
	foreach ( array( 'contact' => 'personal', 'entry' => 'entry' ) as $group => $role ) {
		$raw = isset( $input[ $group . '_url' ] ) ? $input[ $group . '_url' ] : '';
		$active = isset( $input[ $group . '_active' ] ) ? $input[ $group . '_active' ] : '0';
		if ( ! is_string( $raw ) || ! in_array( $active, array( '0', '1' ), true ) ) { return exterior_exone_slack_error(); }
		$url = '' === $raw ? $settings[ $role ]['url'] : $raw;
		if ( ( '' !== $url && ! exterior_exone_slack_url( $url ) ) || ( '1' === $active && '' === $url ) ) { return exterior_exone_slack_error(); }
		$next[ $role ] = exterior_exone_slack_settings( $url, '1' === $active );
	}
	$next['corporate'] = $next['personal'];
	$changed = array(); $failed = false;
	foreach ( $forms as $role => $id ) {
		if ( exterior_exone_slack_forms() !== $forms || get_post_meta( $id, '_ctz_zapier', false ) !== $settings[ $role ]['rows'] ) { $failed = true; break; }
		$old = $settings[ $role ]['rows'];
		if ( $old === array( $next[ $role ] ) ) { continue; }
		$ok = $old ? update_post_meta( $id, '_ctz_zapier', wp_slash( $next[ $role ] ), $old[0] ) : add_post_meta( $id, '_ctz_zapier', wp_slash( $next[ $role ] ), true );
		if ( $ok ) { $changed[ $role ] = $id; }
		if ( ! $ok || get_post_meta( $id, '_ctz_zapier', false ) !== array( $next[ $role ] ) ) { $failed = true; break; }
	}
	if ( ! $failed ) {
		$failed = exterior_exone_slack_forms() !== $forms;
		foreach ( $forms as $role => $id ) {
			if ( get_post_meta( $id, '_ctz_zapier', false ) !== array( $next[ $role ] ) ) { $failed = true; }
		}
	}
	if ( ! $failed ) { return true; }
	foreach ( $changed as $role => $id ) {
		if ( get_post_meta( $id, '_ctz_zapier', false ) !== array( $next[ $role ] ) ) { continue; }
		$old = $settings[ $role ]['rows'];
		if ( $old ) { update_post_meta( $id, '_ctz_zapier', wp_slash( $old[0] ), $next[ $role ] ); }
		else { delete_post_meta( $id, '_ctz_zapier', $next[ $role ] ); }
	}
	return exterior_exone_slack_error();
}

function exterior_exone_slack_form_role( $form ) {
	if ( ! exterior_exone_slack_enabled() || ! is_object( $form ) || ! method_exists( $form, 'id' ) ) { return ''; }
	$forms = exterior_exone_slack_forms();
	return is_wp_error( $forms ) ? '' : (string) array_search( (int) $form->id(), $forms, true );
}

function exterior_exone_slack_runtime_settings( $form ) {
	$forms = exterior_exone_slack_forms();
	if ( is_wp_error( $forms ) || ! in_array( (int) $form->id(), $forms, true ) ) { return exterior_exone_slack_error(); }
	$values = exterior_exone_slack_group_settings( $forms );
	return is_wp_error( $values ) ? $values : $values[ array_search( (int) $form->id(), $forms, true ) ];
}

/** 読み出した複製のプロパティだけを制限する。DBは変更しない。 */
function exterior_exone_slack_form_properties( $properties, $form ) {
	if ( ! exterior_exone_slack_enabled() || ! is_object( $form ) || ! method_exists( $form, 'id' ) || 'exr_form' !== get_post_type( $form->id() ) ) { return $properties; }
	$role = exterior_exone_slack_form_role( $form );
	$value = $role ? exterior_exone_slack_runtime_settings( $form ) : exterior_exone_slack_error();
	$properties['ctz_zapier'] = is_wp_error( $value ) ? exterior_exone_slack_settings( '', false ) : exterior_exone_slack_settings( $value['url'], $value['active'] );
	return $properties;
}

function exterior_exone_slack_value( $value ) {
	if ( is_array( $value ) ) { $value = implode( ' / ', array_filter( $value, 'is_scalar' ) ); }
	$value = is_scalar( $value ) ? (string) $value : '';
	$value = wp_check_invalid_utf8( $value, true );
	$value = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value );
	$value = str_replace( array( '&', '<', '>' ), array( '&amp;', '&lt;', '&gt;' ), $value );
	preg_match( '/\A.{0,3000}/us', $value, $match );
	return isset( $match[0] ) && $match[0] !== $value ? $match[0] . '…（省略。全文はメールでご確認ください）' : $value;
}

function exterior_exone_slack_payload( $role, $data ) {
	$labels = array( 'personal' => '個人のお問い合わせ', 'corporate' => '法人のお問い合わせ', 'entry' => '採用への応募' );
	$fields = array(
		'personal' => array( 'store-slug' => 'お問い合わせ先', 'your-name' => 'お名前', 'your-email' => 'メールアドレス', 'your-zip' => '郵便番号', 'your-address' => 'ご住所', 'your-message' => 'お問い合わせ内容' ),
		'corporate' => array( 'your-name' => 'ご担当者名', 'your-company' => '企業名', 'your-zip' => '郵便番号', 'your-address' => 'ご住所', 'your-tel' => '電話番号', 'your-email' => 'メールアドレス', 'your-message' => 'お問い合わせ内容' ),
		'entry' => array( 'your-name-last' => '姓', 'your-name-first' => '名', 'your-job' => '希望職種', 'your-gender' => '性別', 'your-email' => 'メールアドレス', 'your-tel' => '電話番号', 'your-postcode' => '郵便番号', 'your-address' => 'ご住所', 'your-extended-address' => '以降のご住所', 'your-contact' => '応募先へのメッセージ' ),
	);
	if ( ! isset( $fields[ $role ] ) || ! is_array( $data ) ) { return ''; }
	$text = '【' . $labels[ $role ] . '】';
	foreach ( $fields[ $role ] as $name => $label ) {
		$value = isset( $data[ $name ] ) ? $data[ $name ] : '';
		if ( 'store-slug' === $name ) { $value = function_exists( 'exterior_exone_contact_store_label' ) ? exterior_exone_contact_store_label( is_string( $value ) ? $value : '' ) : '支店未選択'; }
		$text .= "\n\n" . $label . "\n" . exterior_exone_slack_value( $value );
	}
	return wp_json_encode( array( 'text' => $text, 'mrkdwn' => false, 'parse' => 'none', 'link_names' => false, 'unfurl_links' => false, 'unfurl_media' => false ) );
}

/** pluginの最終送信直前に対象と宛先を再検査する。置換式テンプレートは使わない。 */
function exterior_exone_slack_request_args( $args, $properties, $form, $url ) {
	if ( ! exterior_exone_slack_enabled() || ! is_object( $form ) || ! method_exists( $form, 'id' ) || 'exr_form' !== get_post_type( $form->id() ) ) { return $args; }
	$role = exterior_exone_slack_form_role( $form );
	$value = $role ? exterior_exone_slack_runtime_settings( $form ) : exterior_exone_slack_error();
	$submission = class_exists( 'WPCF7_Submission' ) ? WPCF7_Submission::get_instance() : null;
	if ( is_wp_error( $value ) || ! $value['active'] || $url !== $value['url'] || ! $submission ) { throw new Exterior_Exone_Slack_Blocked( '新フォームの通知設定を確認できませんでした。' ); }
	$body = exterior_exone_slack_payload( $role, $submission->get_posted_data() );
	if ( ! is_string( $body ) || '' === $body ) { throw new Exterior_Exone_Slack_Blocked( '通知文を生成できませんでした。' ); }
	exterior_exone_slack_request_checked( $form, $url, true );
	return array( 'method' => 'POST', 'body' => $body, 'headers' => array( 'Content-Type' => 'application/json; charset=utf-8' ), 'timeout' => 10, 'redirection' => 0, 'sslverify' => true );
}

/** 検査済み送信だけを、その応答まで結び付ける。送信中の設定変更でメール結果を壊さない。 */
function exterior_exone_slack_request_checked( $form, $url, $record = false ) {
	static $pending = array();
	if ( ! is_object( $form ) ) { return false; }
	$key = spl_object_hash( $form );
	if ( $record ) { $pending[ $key ] = array( $form, $url ); return true; }
	if ( isset( $pending[ $key ] ) && $pending[ $key ][0] === $form && $pending[ $key ][1] === $url ) { unset( $pending[ $key ] ); return true; }
	return false;
}

function exterior_exone_slack_ignore_errors( $ignore, $url, $result, $form ) {
	return exterior_exone_slack_enabled() && exterior_exone_slack_request_checked( $form, $url ) ? true : $ignore;
}

function exterior_exone_slack_blocked_mails( $mails, $error ) {
	return exterior_exone_slack_enabled() && $error instanceof Exterior_Exone_Slack_Blocked ? array() : $mails;
}

function exterior_exone_slack_blocked_message( $message, $error ) {
	return exterior_exone_slack_enabled() && $error instanceof Exterior_Exone_Slack_Blocked ? '' : $message;
}

function exterior_exone_slack_admin_menu() {
	if ( exterior_exone_slack_enabled() && current_user_can( 'manage_options' ) ) { add_theme_page( '新テーマの Slack 通知', '新テーマの Slack 通知', 'manage_options', 'exone-renewal-slack', 'exterior_exone_slack_admin_render' ); }
}

function exterior_exone_slack_admin_render() {
	if ( ! exterior_exone_slack_enabled() || ! current_user_can( 'manage_options' ) ) { wp_die( esc_html( 'この画面を表示する権限がありません。' ), '', array( 'response' => 403 ) ); }
	$forms = exterior_exone_slack_forms(); $settings = is_wp_error( $forms ) ? $forms : exterior_exone_slack_group_settings( $forms );
	echo '<div class="wrap"><h1>' . esc_html( '新テーマの Slack 通知' ) . '</h1><p>' . esc_html( 'お問い合わせと採用の通知先を設定します。保存時のテスト送信は行いません。メール通知はこれまでどおり送信します。' ) . '</p>';
	echo '<p>' . esc_html( 'Slack通知は通常のメール送信に成功した後で送ります。この画面は通知先の設定状態を示すもので、実際の受信を確認するものではありません。' ) . '</p>';
	if ( isset( $_GET['slack_saved'] ) && '1' === $_GET['slack_saved'] ) { echo '<div class="notice notice-success"><p>' . esc_html( '通知設定を保存しました。' ) . '</p></div>'; }
	if ( is_wp_error( $settings ) || ! class_exists( 'CFTZ_Module_CF7' ) ) { echo '<p>' . esc_html( 'フォームまたは通知プラグインを確認できないため、設定を保存できません。' ) . '</p></div>'; return; }
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="exone_renewal_slack_save">';
	wp_nonce_field( 'exone_renewal_slack_save' );
	foreach ( array( 'contact' => array( 'personal', 'お問い合わせ（個人・法人）' ), 'entry' => array( 'entry', '採用' ) ) as $group => $labels ) {
		$value = $settings[ $labels[0] ];
		echo '<h2>' . esc_html( $labels[1] ) . '</h2><p>' . esc_html( $value['url'] ? ( $value['active'] ? '通知先：設定済み（非表示）' : '通知：無効（通知先は保持しています）' ) : '通知先：未設定' ) . '</p>';
		echo '<p><label><input type="checkbox" name="' . esc_attr( $group . '_active' ) . '" value="1"' . checked( $value['active'], true, false ) . '> ' . esc_html( 'Slackに通知する' ) . '</label></p>';
		echo '<p><label for="exone-slack-' . esc_attr( $group ) . '">' . esc_html( '通知先URL（変更するときだけ入力）' ) . '</label><br><input class="large-text" type="password" id="exone-slack-' . esc_attr( $group ) . '" name="' . esc_attr( $group . '_url' ) . '" value="" autocomplete="new-password" spellcheck="false"></p>';
		echo '<p class="description">' . esc_html( '空欄で保存すると現在の通知先を保持します。SlackのIncoming Webhook URLを入力してください。' ) . '</p>';
	}
	submit_button( '通知設定を保存' ); echo '</form></div>';
}

function exterior_exone_slack_admin_save() {
	if ( ! exterior_exone_slack_enabled() || ! current_user_can( 'manage_options' ) || 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) { wp_die( esc_html( 'この操作は許可されていません。' ), '', array( 'response' => 403 ) ); }
	check_admin_referer( 'exone_renewal_slack_save' );
	$result = exterior_exone_slack_save( wp_unslash( $_POST ) );
	if ( is_wp_error( $result ) ) { wp_die( esc_html( $result->get_error_message() ), '', array( 'response' => 400 ) ); }
	wp_safe_redirect( admin_url( 'themes.php?page=exone-renewal-slack&slack_saved=1' ) ); exit;
}

if ( exterior_exone_slack_enabled() ) {
	add_action( 'admin_menu', 'exterior_exone_slack_admin_menu' );
	add_action( 'admin_post_exone_renewal_slack_save', 'exterior_exone_slack_admin_save' );
	add_filter( 'wpcf7_contact_form_properties', 'exterior_exone_slack_form_properties', 20, 2 );
	add_filter( 'ctz_post_request_args', 'exterior_exone_slack_request_args', 20, 4 );
	add_filter( 'ctz_post_request_ignore_errors', 'exterior_exone_slack_ignore_errors', 20, 4 );
	add_filter( 'ctz_trigger_webhook_error_mails', 'exterior_exone_slack_blocked_mails', 20, 2 );
	add_filter( 'ctz_trigger_webhook_error_message', 'exterior_exone_slack_blocked_message', 20, 2 );
}
