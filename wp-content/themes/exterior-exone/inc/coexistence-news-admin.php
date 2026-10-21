<?php
/** 新テーマ専用のお知らせ。旧投稿と取り込み識別は変更しない。 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function exterior_exone_news_admin_allowed() {
	return defined( 'EXR_COEXISTENCE' ) && true === EXR_COEXISTENCE && current_user_can( 'manage_options' );
}
function exterior_exone_news_admin_error( $code ) {
	$messages = array(
		'permission' => 'この操作を行う権限がありません。',
		'nonce' => '画面の有効期限が切れました。画面を開き直してください。',
		'input' => '入力内容を確認してください。タイトルと表示日を確認してください。',
		'date' => '表示日には今日以前の正しい日付を入力してください。',
		'image' => '選択した画像を利用できません。メディアから選び直してください。',
		'record' => 'このお知らせは編集できません。対象や保存状態を確認してください。',
		'conflict' => '別の操作で内容が変わりました。画面を開き直して最新の内容を確認してください。',
		'duplicate' => 'この操作は既に保存済みか、同じ識別情報が存在します。一覧を確認してください。',
		'busy' => '別のお知らせを保存中です。少し待ってからもう一度操作してください。',
		'storage' => '保存できませんでした。内容を確認してからもう一度操作してください。',
	);
	return new WP_Error( $code, isset( $messages[ $code ] ) ? $messages[ $code ] : $messages['storage'] );
}
function exterior_exone_news_admin_uuid( $value ) {
	return is_string( $value ) && 1 === preg_match( '/\A[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}\z/D', $value );
}
function exterior_exone_news_admin_meta( $record, $key, $required = true ) {
	$values = array();
	foreach ( $record['meta'] as $row ) { if ( $key === $row['meta_key'] ) { $values[] = $row['meta_value']; } }
	return 1 === count( $values ) ? $values[0] : ( ! $required && ! $values ? '' : null );
}
/** キャッシュではなくDBの現在値を競合判定に使う。 */
function exterior_exone_news_admin_read( $id, $lock = false ) {
	global $wpdb;
	if ( ! is_int( $id ) || $id < 1 ) { return null; }
	$suffix = $lock ? ' FOR UPDATE' : '';
	$post = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE ID=%d" . $suffix, $id ), ARRAY_A );
	if ( ! $post ) { return null; }
	$meta = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->postmeta} WHERE post_id=%d ORDER BY meta_id" . $suffix, $id ), ARRAY_A );
	return is_array( $meta ) ? array( 'post' => $post, 'meta' => $meta ) : null;
}
function exterior_exone_news_admin_valid( $record ) {
	if ( ! is_array( $record ) || 'exr_news' !== $record['post']['post_type'] || 'exr_stored' !== $record['post']['post_status'] ) { return false; }
	if ( 'exterior-exone-renewal:v1' !== exterior_exone_news_admin_meta( $record, '_exr_dataset' ) || 'complete' !== exterior_exone_news_admin_meta( $record, '_exr_import_state' ) ) { return false; }
	$key = exterior_exone_news_admin_meta( $record, '_exr_source_key' );
	$prefix = 'exterior-exone-renewal:v1:admin:news:';
	if ( ! is_string( $key ) || ( ! preg_match( '/\Aexterior-exone-renewal:v1:post:news:[1-9][0-9]*\z/D', $key ) && ( 0 !== strpos( $key, $prefix ) || ! exterior_exone_news_admin_uuid( substr( $key, strlen( $prefix ) ) ) ) ) ) { return false; }
	$status = exterior_exone_news_admin_meta( $record, '_exr_source_status' );
	if ( ! in_array( $status, array( 'publish', 'draft', 'trash' ), true ) || null === exterior_exone_news_admin_meta( $record, '_thumbnail_id', false ) ) { return false; }
	$previous = exterior_exone_news_admin_meta( $record, '_exr_news_previous_status', false );
	return 'trash' === $status ? in_array( $previous, array( 'publish', 'draft' ), true ) : '' === $previous;
}
function exterior_exone_news_admin_version( $record ) {
	return hash_hmac( 'sha256', serialize( $record ), wp_salt( 'nonce' ) );
}
function exterior_exone_news_admin_nonce_action( $id, $creation ) {
	return 'exone_news_manage_' . $id . ( $id ? '' : '_' . $creation );
}
function exterior_exone_news_admin_input( $input ) {
	foreach ( array( 'id', 'intent', 'nonce', 'version', 'creation' ) as $key ) {
		if ( ! isset( $input[ $key ] ) || ! is_string( $input[ $key ] ) ) { return exterior_exone_news_admin_error( 'input' ); }
	}
	if ( ! preg_match( '/\A(?:0|[1-9][0-9]{0,9})\z/D', $input['id'] ) || ! in_array( $input['intent'], array( 'save', 'trash', 'restore' ), true ) ) { return exterior_exone_news_admin_error( 'input' ); }
	$id = (int) $input['id'];
	if ( ( ! $id && ( ! exterior_exone_news_admin_uuid( $input['creation'] ) || 'save' !== $input['intent'] || '' !== $input['version'] ) ) || ( $id && ( '' !== $input['creation'] || ! preg_match( '/\A[a-f0-9]{64}\z/D', $input['version'] ) ) ) ) { return exterior_exone_news_admin_error( 'input' ); }
	$result = array( 'id' => $id, 'intent' => $input['intent'], 'nonce' => $input['nonce'], 'version' => $input['version'], 'creation' => $input['creation'] );
	if ( 'save' === $input['intent'] ) {
		foreach ( array( 'title', 'body', 'date', 'image', 'status' ) as $key ) { if ( ! isset( $input[ $key ] ) || ! is_string( $input[ $key ] ) ) { return exterior_exone_news_admin_error( 'input' ); } }
		$title = sanitize_text_field( $input['title'] ); $body = sanitize_textarea_field( $input['body'] );
		if ( '' === trim( $title ) || strlen( $title ) > 600 || strlen( $body ) > 60000 || ! in_array( $input['status'], array( 'publish', 'draft' ), true ) || ! preg_match( '/\A(?:0|[1-9][0-9]{0,9})\z/D', $input['image'] ) ) { return exterior_exone_news_admin_error( 'input' ); }
		$date = DateTimeImmutable::createFromFormat( '!Y-m-d', $input['date'], wp_timezone() );
		if ( ! $date || $date->format( 'Y-m-d' ) !== $input['date'] || $input['date'] > current_time( 'Y-m-d' ) || $input['date'] < '1970-01-01' ) { return exterior_exone_news_admin_error( 'date' ); }
		$result += array( 'title' => $title, 'body' => $body, 'date' => $input['date'], 'image' => (int) $input['image'], 'status' => $input['status'] );
	}
	return $result;
}
function exterior_exone_news_admin_image_allowed( $id ) {
	if ( ! $id ) { return true; }
	$post = get_post( $id );
	return $post instanceof WP_Post && 'attachment' === $post->post_type && in_array( $post->post_status, array( 'inherit', 'private', 'publish' ), true ) && wp_attachment_is_image( $id ) && current_user_can( 'read_post', $id );
}
/** 元の取り込み識別と任意の追加metaを保持する差分確認。 */
function exterior_exone_news_admin_protected( $record ) {
	$post = $record['post'];
	foreach ( array( 'post_title', 'post_content', 'post_date', 'post_date_gmt', 'post_modified', 'post_modified_gmt' ) as $key ) { unset( $post[ $key ] ); }
	$meta = array_values( array_filter( $record['meta'], static function( $row ) { return ! in_array( $row['meta_key'], array( '_exr_source_status', '_thumbnail_id', '_exr_news_previous_status' ), true ); } ) );
	return array( 'post' => $post, 'meta' => $meta );
}
/** 同画面の保存だけを直列化。サイト全体の書き込みは止めない。 */
function exterior_exone_news_admin_save( $raw ) {
	global $wpdb;
	if ( ! exterior_exone_news_admin_allowed() ) { return exterior_exone_news_admin_error( 'permission' ); }
	$input = exterior_exone_news_admin_input( $raw );
	if ( is_wp_error( $input ) ) { return $input; }
	if ( ! wp_verify_nonce( $input['nonce'], exterior_exone_news_admin_nonce_action( $input['id'], $input['creation'] ) ) ) { return exterior_exone_news_admin_error( 'nonce' ); }
	if ( 'save' === $input['intent'] && ! exterior_exone_news_admin_image_allowed( $input['image'] ) ) { return exterior_exone_news_admin_error( 'image' ); }
	$lock = 'exr_news_' . substr( hash( 'sha256', DB_NAME . ':' . $wpdb->prefix ), 0, 40 );
	$locked = false; $transaction = false; $id = $input['id']; $commit_attempted = false;
	$reconnect_retries = $wpdb->reconnect_retries;
	// 切断後にautocommit接続で書き込みを再試行させない。
	$wpdb->reconnect_retries = 0;
	try {
		$engines = $wpdb->get_results( $wpdb->prepare( 'SELECT TABLE_NAME,ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN (%s,%s)', $wpdb->posts, $wpdb->postmeta ), ARRAY_A );
		if ( 2 !== count( $engines ) || array( 'InnoDB' ) !== array_values( array_unique( array_column( $engines, 'ENGINE' ) ) ) ) { throw new RuntimeException( 'storage' ); }
		if ( '1' !== (string) $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s,0)', $lock ) ) ) { throw new RuntimeException( 'busy' ); }
		$locked = true;
		if ( false === $wpdb->query( 'START TRANSACTION' ) ) { throw new RuntimeException( 'storage' ); }
		$transaction = true;
		$before = $id ? exterior_exone_news_admin_read( $id, true ) : null;
		if ( $id && ( ! exterior_exone_news_admin_valid( $before ) || ! hash_equals( exterior_exone_news_admin_version( $before ), $input['version'] ) ) ) { throw new RuntimeException( 'conflict' ); }
		$status = $id ? exterior_exone_news_admin_meta( $before, '_exr_source_status' ) : 'draft';
		if ( ( 'trash' === $input['intent'] && 'trash' === $status ) || ( 'restore' === $input['intent'] && 'trash' !== $status ) || ( 'save' === $input['intent'] && 'trash' === $status ) ) { throw new RuntimeException( 'record' ); }
		$key = $id ? exterior_exone_news_admin_meta( $before, '_exr_source_key' ) : 'exterior-exone-renewal:v1:admin:news:' . $input['creation'];
		$slug = $id ? $before['post']['post_name'] : 'notice-' . str_replace( '-', '', $input['creation'] );
		$identities = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_exr_source_key' AND BINARY meta_value=%s", $key ) );
		$names = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type='exr_news' AND post_name=%s", $slug ) );
		if ( ( $id && ( array( (string) $id ) !== $identities || array( (string) $id ) !== $names ) ) || ( ! $id && ( $identities || $names ) ) ) { throw new RuntimeException( 'duplicate' ); }
		if ( $id ) { clean_post_cache( $id ); }
		$post = array( 'post_type' => 'exr_news', 'post_status' => 'exr_stored', 'post_name' => $slug, 'comment_status' => 'closed', 'ping_status' => 'closed', 'comment_count' => 0 );
		if ( $id ) { $post['ID'] = $id; } else { $post['guid'] = home_url( '/news/' . $slug . '/' ); }
		if ( 'save' === $input['intent'] ) {
			$time = $id && substr( $before['post']['post_date'], 0, 10 ) === $input['date'] ? substr( $before['post']['post_date'], 10 ) : ' 00:00:00';
			$post += array( 'post_title' => $input['title'], 'post_content' => $input['body'], 'post_date' => $input['date'] . $time, 'post_date_gmt' => get_gmt_from_date( $input['date'] . $time ) );
		}
		// WordPress標準処理が同名の旧ゴミ箱記事を改名する副作用を、この保存だけ止める。
		$keep_trashed_slug = static function( $change, $name, $post_id ) use ( $slug, $input ) { return $name === $slug && (int) $post_id === $input['id'] ? false : $change; };
		add_filter( 'add_trashed_suffix_to_trashed_posts', $keep_trashed_slug, PHP_INT_MAX, 3 );
		// 更新APIの自動補正で既存の識別URL・GUIDを変えない。
		$keep_guid = static function( $data, $postarr ) use ( $before, $input ) { if ( $before && (int) ( $postarr['ID'] ?? 0 ) === $input['id'] && 'exr_news' === $data['post_type'] ) { $data['guid'] = wp_slash( $before['post']['guid'] ); $data['post_name'] = wp_slash( $before['post']['post_name'] ); } return $data; };
		add_filter( 'wp_insert_post_data', $keep_guid, PHP_INT_MAX, 2 );
		try { $id = $input['id'] ? wp_update_post( wp_slash( $post ), true ) : wp_insert_post( wp_slash( $post ), true ); }
		finally { remove_filter( 'add_trashed_suffix_to_trashed_posts', $keep_trashed_slug, PHP_INT_MAX ); remove_filter( 'wp_insert_post_data', $keep_guid, PHP_INT_MAX ); }
		if ( is_wp_error( $id ) || ! $id ) { $id = $input['id']; throw new RuntimeException( 'storage' ); }
		if ( ! $input['id'] ) {
			foreach ( array( '_exr_dataset' => 'exterior-exone-renewal:v1', '_exr_source_key' => $key, '_exr_import_state' => 'complete', '_exr_source_path' => '/news/' . $slug . '/' ) as $meta_key => $value ) { if ( ! add_post_meta( $id, $meta_key, $value, true ) ) { throw new RuntimeException( 'storage' ); } }
		}
		if ( 'trash' === $input['intent'] ) { update_post_meta( $id, '_exr_news_previous_status', $status ); $next_status = 'trash'; }
		elseif ( 'restore' === $input['intent'] ) { $next_status = exterior_exone_news_admin_meta( $before, '_exr_news_previous_status' ); delete_post_meta( $id, '_exr_news_previous_status' ); }
		else { $next_status = $input['status']; if ( $input['image'] ) { update_post_meta( $id, '_thumbnail_id', $input['image'] ); } else { delete_post_meta( $id, '_thumbnail_id' ); } }
		update_post_meta( $id, '_exr_source_status', $next_status );
		$after = exterior_exone_news_admin_read( $id, true );
		if ( ! exterior_exone_news_admin_valid( $after ) || $next_status !== exterior_exone_news_admin_meta( $after, '_exr_source_status' ) || ( $before && exterior_exone_news_admin_protected( $before ) !== exterior_exone_news_admin_protected( $after ) ) ) { throw new RuntimeException( 'storage' ); }
		if ( 'save' === $input['intent'] && ( $after['post']['post_title'] !== $input['title'] || $after['post']['post_content'] !== $input['body'] || $after['post']['post_date'] !== $post['post_date'] || (int) exterior_exone_news_admin_meta( $after, '_thumbnail_id', false ) !== $input['image'] ) ) { throw new RuntimeException( 'storage' ); }
		$commit_attempted = true;
		if ( false === $wpdb->query( 'COMMIT' ) ) { throw new RuntimeException( 'storage' ); }
		$transaction = false;
		return array( 'id' => $id, 'status' => $next_status );
	} catch ( Throwable $error ) {
		if ( $transaction && ! $commit_attempted ) { $wpdb->query( 'ROLLBACK' ); }
		return exterior_exone_news_admin_error( in_array( $error->getMessage(), array( 'record', 'conflict', 'duplicate', 'busy' ), true ) ? $error->getMessage() : 'storage' );
	} finally {
		if ( $id && is_int( $id ) ) { clean_post_cache( $id ); }
		if ( $locked ) { $wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock ) ); }
		$wpdb->reconnect_retries = $reconnect_retries;
	}
}
function exterior_exone_news_admin_menu() {
	if ( exterior_exone_news_admin_allowed() ) { add_menu_page( '新テーマのお知らせ', 'お知らせ（新テーマ）', 'manage_options', 'exone-renewal-news', 'exterior_exone_news_admin_render', 'dashicons-megaphone', 21 ); }
}
function exterior_exone_news_admin_url( $args = array() ) { return add_query_arg( $args, admin_url( 'admin.php?page=exone-renewal-news' ) ); }
function exterior_exone_news_admin_assets( $hook ) {
	if ( 'toplevel_page_exone-renewal-news' !== $hook || ! exterior_exone_news_admin_allowed() ) { return; }
	wp_enqueue_media();
	wp_enqueue_style( 'exone-renewal-news-admin', get_theme_file_uri( 'css/coexistence-news-admin.css' ), array(), filemtime( get_theme_file_path( 'css/coexistence-news-admin.css' ) ) );
	wp_enqueue_script( 'exone-renewal-news-admin', get_theme_file_uri( 'js/coexistence-news-admin.js' ), array( 'jquery', 'media-views' ), filemtime( get_theme_file_path( 'js/coexistence-news-admin.js' ) ), true );
}
function exterior_exone_news_admin_submit() {
	if ( ! exterior_exone_news_admin_allowed() || 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) { wp_die( esc_html( 'この操作を行う権限がありません。' ), '', array( 'response' => 403 ) ); }
	$result = exterior_exone_news_admin_save( wp_unslash( $_POST ) );
	if ( is_wp_error( $result ) ) { wp_die( esc_html( $result->get_error_message() ), '', array( 'response' => in_array( $result->get_error_code(), array( 'permission', 'nonce' ), true ) ? 403 : 409, 'back_link' => true ) ); }
	wp_safe_redirect( exterior_exone_news_admin_url( array( 'view' => 'trash' === $result['status'] ? 'trash' : 'all', 'saved' => '1' ) ) ); exit;
}
function exterior_exone_news_admin_hidden( $record, $creation ) {
	$id = $record ? (int) $record['post']['ID'] : 0;
	echo '<input type="hidden" name="action" value="exone_renewal_news"><input type="hidden" name="id" value="' . esc_attr( $id ) . '"><input type="hidden" name="creation" value="' . esc_attr( $creation ) . '"><input type="hidden" name="version" value="' . esc_attr( $record ? exterior_exone_news_admin_version( $record ) : '' ) . '">';
	wp_nonce_field( exterior_exone_news_admin_nonce_action( $id, $creation ), 'nonce' );
}
function exterior_exone_news_admin_render() {
	global $wpdb;
	if ( ! exterior_exone_news_admin_allowed() ) { wp_die( esc_html( 'この画面を表示する権限がありません。' ), '', array( 'response' => 403 ) ); }
	$edit = isset( $_GET['edit'] ) && is_string( $_GET['edit'] ) && preg_match( '/\A[1-9][0-9]{0,9}\z/D', $_GET['edit'] ) ? (int) $_GET['edit'] : 0;
	$new = isset( $_GET['new'] ) && '1' === $_GET['new'];
	$view = isset( $_GET['view'] ) && is_string( $_GET['view'] ) && in_array( $_GET['view'], array( 'draft', 'publish', 'trash' ), true ) ? $_GET['view'] : 'all';
	echo '<div class="wrap exone-news-admin"><h1>新テーマのお知らせ</h1><p>ここで編集した内容は新テーマの「お知らせ一覧」に表示されます。旧テーマのお知らせは変更されません。</p>';
	if ( isset( $_GET['saved'] ) && '1' === $_GET['saved'] ) { echo '<div class="notice notice-success"><p>保存しました。</p></div>'; }
	if ( $edit || $new ) {
		$record = $edit ? exterior_exone_news_admin_read( $edit ) : null;
		if ( $edit && ( ! exterior_exone_news_admin_valid( $record ) || 'trash' === exterior_exone_news_admin_meta( $record, '_exr_source_status' ) ) ) { echo '<div class="notice notice-error"><p>このお知らせは編集できません。一覧から確認してください。</p></div></div>'; return; }
		$status = $record ? exterior_exone_news_admin_meta( $record, '_exr_source_status' ) : 'draft'; $image = $record ? (int) exterior_exone_news_admin_meta( $record, '_thumbnail_id', false ) : 0;
		echo '<p><a href="' . esc_url( exterior_exone_news_admin_url() ) . '">お知らせ一覧へ戻る</a></p><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="exone-news-editor">';
		exterior_exone_news_admin_hidden( $record, $record ? '' : wp_generate_uuid4() );
		echo '<label for="exone-news-title">タイトル <span>必須</span></label><input type="text" id="exone-news-title" name="title" class="large-text" maxlength="200" required value="' . esc_attr( $record ? $record['post']['post_title'] : '' ) . '">';
		echo '<label for="exone-news-body">本文</label><textarea id="exone-news-body" name="body" rows="12" class="large-text" maxlength="20000">' . esc_textarea( $record ? $record['post']['post_content'] : '' ) . '</textarea><p class="description">文章を入力してください。改行や空行はそのまま表示されます。HTMLやショートコードは使用しません。</p>';
		echo '<label>画像</label><div class="exone-news-image"><input type="hidden" name="image" value="' . esc_attr( $image ) . '"><div class="exone-news-image-preview">' . ( $image ? wp_get_attachment_image( $image, 'medium' ) : '' ) . '</div><button type="button" class="button exone-news-select-image">画像を選ぶ</button> <button type="button" class="button exone-news-remove-image">画像を外す</button><p class="description">画像を外してもメディアライブラリの画像は削除されません。</p></div>';
		echo '<label for="exone-news-date">表示日 <span>必須</span></label><input id="exone-news-date" type="date" name="date" min="1970-01-01" max="' . esc_attr( current_time( 'Y-m-d' ) ) . '" required value="' . esc_attr( $record ? substr( $record['post']['post_date'], 0, 10 ) : current_time( 'Y-m-d' ) ) . '">';
		echo '<label for="exone-news-status">公開状態</label><select id="exone-news-status" name="status"><option value="draft"' . selected( $status, 'draft', false ) . '>下書き</option><option value="publish"' . selected( $status, 'publish', false ) . '>公開</option></select><p class="description">公開にすると新テーマのお知らせ一覧に表示されます。</p><p><button class="button button-primary" name="intent" value="save">保存する</button></p></form></div>'; return;
	}
	echo '<p><a class="page-title-action" href="' . esc_url( exterior_exone_news_admin_url( array( 'new' => '1' ) ) ) . '">新規追加</a> <a href="' . esc_url( home_url( '/news/' ) ) . '" target="_blank" rel="noopener">公開一覧を確認</a></p><nav class="exone-news-tabs" aria-label="公開状態">';
	$labels = array( 'all' => 'すべて', 'publish' => '公開', 'draft' => '下書き', 'trash' => 'ゴミ箱' );
	foreach ( $labels as $name => $label ) { echo '<a' . ( $view === $name ? ' aria-current="page"' : '' ) . ' href="' . esc_url( exterior_exone_news_admin_url( array( 'view' => $name ) ) ) . '">' . esc_html( $label ) . '</a>'; }
	echo '</nav><table class="widefat striped"><thead><tr><th>タイトル</th><th>公開状態</th><th>表示日</th><th>画像</th><th>操作</th></tr></thead><tbody>';
	$ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type='exr_news' AND post_status='exr_stored' ORDER BY post_date DESC,ID DESC" ); $count = 0;
	foreach ( $ids as $id ) {
		$record = exterior_exone_news_admin_read( (int) $id ); if ( ! exterior_exone_news_admin_valid( $record ) ) { continue; }
		$status = exterior_exone_news_admin_meta( $record, '_exr_source_status' ); if ( 'all' === $view ? 'trash' === $status : $status !== $view ) { continue; } $count++;
		echo '<tr><th scope="row">' . esc_html( $record['post']['post_title'] ) . '</th><td>' . esc_html( $labels[ $status ] ) . '</td><td>' . esc_html( substr( $record['post']['post_date'], 0, 10 ) ) . '</td><td>' . ( exterior_exone_news_admin_meta( $record, '_thumbnail_id', false ) ? 'あり' : 'なし' ) . '</td><td>';
		if ( 'trash' !== $status ) { echo '<a href="' . esc_url( exterior_exone_news_admin_url( array( 'edit' => (int) $id ) ) ) . '">編集</a> '; }
		echo '<form class="exone-news-row-action" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">'; exterior_exone_news_admin_hidden( $record, '' );
		echo '<button class="button-link' . ( 'trash' === $status ? '' : ' button-link-delete' ) . '" name="intent" value="' . ( 'trash' === $status ? 'restore' : 'trash' ) . '"' . ( 'trash' === $status ? '' : ' data-news-trash="true"' ) . '>' . ( 'trash' === $status ? '元に戻す' : 'ゴミ箱へ移動' ) . '</button></form></td></tr>';
	}
	if ( ! $count ) { echo '<tr><td colspan="5">該当するお知らせはありません。</td></tr>'; }
	echo '</tbody></table><p class="description">ゴミ箱のお知らせは公開されません。「元に戻す」で移動前の公開状態へ戻せます。完全削除は行いません。</p></div>';
}
if ( defined( 'EXR_COEXISTENCE' ) && true === EXR_COEXISTENCE ) {
	add_action( 'admin_menu', 'exterior_exone_news_admin_menu' );
	add_action( 'admin_enqueue_scripts', 'exterior_exone_news_admin_assets' );
	add_action( 'admin_post_exone_renewal_news', 'exterior_exone_news_admin_submit' );
}
