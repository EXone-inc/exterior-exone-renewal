<?php
/** 新テーマの提案事例・デザインテイスト。旧データには書き込まない。 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function exterior_exone_proposal_allowed() {
	return defined( 'EXR_COEXISTENCE' ) && true === EXR_COEXISTENCE && current_user_can( 'manage_options' );
}
function exterior_exone_proposal_error( $code ) {
	$messages = array(
		'permission' => 'この操作を行う権限がありません。', 'nonce' => '画面の有効期限が切れました。画面を開き直してください。',
		'input' => '入力内容を確認してください。タイトルまたは名称は必須です。', 'image' => '選択した画像を利用できません。メディアから選び直してください。',
		'record' => 'この項目は変更できません。対象と保存状態を確認してください。', 'conflict' => '別の操作で内容が変わりました。画面を開き直してください。',
		'duplicate' => '同じ識別情報または名称が既に存在します。一覧を確認してください。', 'busy' => '別の提案事例やテイストを保存中です。少し待ってからやり直してください。',
		'related' => 'このテイストには管理対象外の関連や子分類があるため変更できません。', 'storage' => '保存できませんでした。一覧で現在の内容を確認してください。',
	);
	return new WP_Error( $code, isset( $messages[ $code ] ) ? $messages[ $code ] : $messages['storage'] );
}
function exterior_exone_proposal_uuid( $value ) { return is_string( $value ) && 1 === preg_match( '/\A[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}\z/D', $value ); }
function exterior_exone_proposal_meta( $record, $key, $required = true ) {
	$values = array(); foreach ( $record['meta'] as $row ) { if ( $row['meta_key'] === $key ) { $values[] = $row['meta_value']; } }
	return count( $values ) === 1 ? $values[0] : ( ! $required && ! $values ? '' : null );
}
function exterior_exone_proposal_version( $record ) { return hash_hmac( 'sha256', serialize( $record ), wp_salt( 'nonce' ) ); }
function exterior_exone_proposal_fields() {
	return array( 'main' => 'メイン画像', 'exterior' => '外観写真', 'perspective' => '3Dパース', 'plan' => '図面' );
}
function exterior_exone_proposal_mutable_meta() {
	$keys = array( '_exr_source_status', '_exr_works_previous_status', '_exterior_exone_works_gallery', '_exterior_exone_works_on_top' );
	foreach ( exterior_exone_proposal_fields() as $key => $label ) { $keys[] = '_exterior_exone_works_image_' . $key; }
	return $keys;
}
/** SQLエラーを空の一覧として扱わず、値をログへ出さず停止する。 */
function exterior_exone_proposal_rows( $sql ) {
	global $wpdb; $rows = $wpdb->get_results( $sql, ARRAY_A );
	if ( ! is_array( $rows ) || $wpdb->last_error ) { throw new RuntimeException( 'storage' ); } return $rows;
}
function exterior_exone_proposal_exec( $sql, $expected = null ) {
	global $wpdb; $count = $wpdb->query( $sql );
	if ( false === $count || ( null !== $expected && $count !== $expected ) ) { throw new RuntimeException( 'storage' ); } return $count;
}
function exterior_exone_proposal_read( $id, $lock = false ) {
	global $wpdb; if ( ! is_int( $id ) || $id < 1 ) { return null; } $suffix = $lock ? ' FOR UPDATE' : '';
	$posts = exterior_exone_proposal_rows( $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE ID=%d" . $suffix, $id ) ); if ( ! $posts ) { return null; }
	$meta = exterior_exone_proposal_rows( $wpdb->prepare( "SELECT * FROM {$wpdb->postmeta} WHERE post_id=%d ORDER BY meta_id" . $suffix, $id ) );
	$relations = exterior_exone_proposal_rows( $wpdb->prepare( "SELECT r.*,tt.taxonomy,tt.term_id FROM {$wpdb->term_relationships} r LEFT JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id=r.term_taxonomy_id WHERE r.object_id=%d ORDER BY r.term_taxonomy_id" . $suffix, $id ) );
	return array( 'post' => $posts[0], 'meta' => $meta, 'relations' => $relations );
}
function exterior_exone_proposal_identity( $record, $kind ) {
	if ( 'exterior-exone-renewal:v1' !== exterior_exone_proposal_meta( $record, '_exr_dataset' ) || 'complete' !== exterior_exone_proposal_meta( $record, '_exr_import_state' ) ) { return false; }
	$key = exterior_exone_proposal_meta( $record, '_exr_source_key' );
	$source = 'term' === $kind ? 'term:works_package:' : 'post:works:'; $admin = 'term' === $kind ? 'admin:term:works_package:' : 'admin:works:';
	$prefix = 'exterior-exone-renewal:v1:';
	if ( is_string( $key ) && preg_match( '/\A' . preg_quote( $prefix . $source, '/' ) . '[1-9][0-9]*\z/D', $key ) ) {
		foreach ( array( '_exr_source_hash', '_exr_source_fingerprint' ) as $field ) { if ( ! is_string( exterior_exone_proposal_meta( $record, $field ) ) || ! preg_match( '/\A[a-f0-9]{64}\z/D', exterior_exone_proposal_meta( $record, $field ) ) ) { return false; } } return true;
	}
	return is_string( $key ) && 0 === strpos( $key, $prefix . $admin ) && exterior_exone_proposal_uuid( substr( $key, strlen( $prefix . $admin ) ) );
}
function exterior_exone_proposal_valid( $record ) {
	if ( ! is_array( $record ) || 'exr_works' !== $record['post']['post_type'] || 'exr_stored' !== $record['post']['post_status'] || ! exterior_exone_proposal_identity( $record, 'post' ) ) { return false; }
	$status = exterior_exone_proposal_meta( $record, '_exr_source_status' ); $prior = exterior_exone_proposal_meta( $record, '_exr_works_previous_status', false );
	if ( ! in_array( $status, array( 'publish', 'draft', 'trash' ), true ) || ( 'trash' === $status ? ! in_array( $prior, array( 'publish', 'draft' ), true ) : '' !== $prior ) ) { return false; }
	foreach ( exterior_exone_proposal_mutable_meta() as $key ) { if ( null === exterior_exone_proposal_meta( $record, $key, false ) ) { return false; } }
	return true;
}
function exterior_exone_proposal_term_read( $id, $lock = false ) {
	global $wpdb; if ( ! is_int( $id ) || $id < 1 ) { return null; } $suffix = $lock ? ' FOR UPDATE' : '';
	$terms = exterior_exone_proposal_rows( $wpdb->prepare( "SELECT * FROM {$wpdb->terms} WHERE term_id=%d" . $suffix, $id ) ); if ( ! $terms ) { return null; }
	$tax = exterior_exone_proposal_rows( $wpdb->prepare( "SELECT * FROM {$wpdb->term_taxonomy} WHERE term_id=%d ORDER BY term_taxonomy_id" . $suffix, $id ) );
	$meta = exterior_exone_proposal_rows( $wpdb->prepare( "SELECT * FROM {$wpdb->termmeta} WHERE term_id=%d ORDER BY meta_id" . $suffix, $id ) );
	$rels = exterior_exone_proposal_rows( $wpdb->prepare( "SELECT r.* FROM {$wpdb->term_relationships} r JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id=r.term_taxonomy_id WHERE tt.term_id=%d ORDER BY r.object_id,r.term_taxonomy_id" . $suffix, $id ) );
	$children = exterior_exone_proposal_rows( $wpdb->prepare( "SELECT term_taxonomy_id,taxonomy FROM {$wpdb->term_taxonomy} WHERE parent=%d ORDER BY term_taxonomy_id" . $suffix, $id ) );
	return array( 'term' => $terms[0], 'taxonomies' => $tax, 'meta' => $meta, 'relations' => $rels, 'children' => $children );
}
function exterior_exone_proposal_term_valid( $record ) {
	return is_array( $record ) && 1 === count( $record['taxonomies'] ) && 'exr_works_package' === $record['taxonomies'][0]['taxonomy'] && exterior_exone_proposal_identity( $record, 'term' );
}
function exterior_exone_proposal_term_safe( $record ) {
	if ( ! exterior_exone_proposal_term_valid( $record ) || $record['children'] ) { throw new RuntimeException( 'related' ); }
	foreach ( $record['relations'] as $relation ) { $post = exterior_exone_proposal_read( (int) $relation['object_id'], true ); if ( ! exterior_exone_proposal_valid( $post ) ) { throw new RuntimeException( 'related' ); } exterior_exone_proposal_unique_key( exterior_exone_proposal_meta( $post, '_exr_source_key' ), (int) $relation['object_id'] ); }
}
function exterior_exone_proposal_terms( $lock = false ) {
	global $wpdb; $rows = exterior_exone_proposal_rows( "SELECT term_id FROM {$wpdb->term_taxonomy} WHERE taxonomy='exr_works_package' ORDER BY term_id" . ( $lock ? ' FOR UPDATE' : '' ) ); $terms = array();
	foreach ( $rows as $row ) { $term = exterior_exone_proposal_term_read( (int) $row['term_id'], $lock ); if ( ! exterior_exone_proposal_term_valid( $term ) ) { throw new RuntimeException( 'record' ); } $terms[] = $term; } return $terms;
}
function exterior_exone_proposal_nonce( $kind, $id, $creation ) { return 'exone_proposal_' . $kind . '_' . $id . ( $id ? '' : '_' . $creation ); }
function exterior_exone_proposal_input( $raw, $kind ) {
	foreach ( array( 'id', 'intent', 'nonce', 'version', 'creation' ) as $key ) { if ( ! isset( $raw[ $key ] ) || ! is_string( $raw[ $key ] ) ) { return exterior_exone_proposal_error( 'input' ); } }
	if ( ! preg_match( '/\A(?:0|[1-9][0-9]{0,9})\z/D', $raw['id'] ) || ! in_array( $raw['intent'], 'term' === $kind ? array( 'save', 'delete' ) : array( 'save', 'trash', 'restore' ), true ) ) { return exterior_exone_proposal_error( 'input' ); }
	$id = (int) $raw['id'];
	if ( $id ? ( '' !== $raw['creation'] || ! preg_match( '/\A[a-f0-9]{64}\z/D', $raw['version'] ) ) : ( ! exterior_exone_proposal_uuid( $raw['creation'] ) || 'save' !== $raw['intent'] || '' !== $raw['version'] ) ) { return exterior_exone_proposal_error( 'input' ); }
	$result = array( 'id' => $id, 'intent' => $raw['intent'], 'nonce' => $raw['nonce'], 'version' => $raw['version'], 'creation' => $raw['creation'] );
	if ( 'save' !== $raw['intent'] ) { return $result; }
	if ( 'term' === $kind ) {
		if ( ! isset( $raw['name'] ) || ! is_string( $raw['name'] ) ) { return exterior_exone_proposal_error( 'input' ); } $name = sanitize_text_field( $raw['name'] );
		return '' === trim( $name ) || strlen( $name ) > 200 ? exterior_exone_proposal_error( 'input' ) : $result + array( 'name' => $name );
	}
	foreach ( array( 'title', 'body', 'status', 'taste', 'tastes_version', 'on_top' ) as $key ) { if ( ! isset( $raw[ $key ] ) || ! is_string( $raw[ $key ] ) ) { return exterior_exone_proposal_error( 'input' ); } }
	$title = sanitize_text_field( $raw['title'] ); $body = sanitize_textarea_field( $raw['body'] );
	if ( '' === trim( $title ) || strlen( $title ) > 600 || strlen( $body ) > 60000 || ! in_array( $raw['status'], array( 'publish', 'draft' ), true ) || ! in_array( $raw['on_top'], array( '0', '1' ), true ) || ! preg_match( '/\A(?:0|[1-9][0-9]{0,9})\z/D', $raw['taste'] ) || ! preg_match( '/\A[a-f0-9]{64}\z/D', $raw['tastes_version'] ) ) { return exterior_exone_proposal_error( 'input' ); }
	$images = array(); foreach ( exterior_exone_proposal_fields() as $key => $label ) { if ( ! isset( $raw[ 'image_' . $key ] ) || ! is_string( $raw[ 'image_' . $key ] ) || ! preg_match( '/\A(?:0|[1-9][0-9]{0,9})\z/D', $raw[ 'image_' . $key ] ) ) { return exterior_exone_proposal_error( 'image' ); } $images[ $key ] = (int) $raw[ 'image_' . $key ]; }
	return $result + array( 'title' => $title, 'body' => $body, 'status' => $raw['status'], 'taste' => (int) $raw['taste'], 'tastes_version' => $raw['tastes_version'], 'on_top' => $raw['on_top'], 'images' => $images );
}
function exterior_exone_proposal_image_allowed( $id ) {
	if ( ! $id ) { return true; } $post = get_post( $id );
	return $post instanceof WP_Post && 'attachment' === $post->post_type && in_array( $post->post_status, array( 'inherit', 'private', 'publish' ), true ) && wp_attachment_is_image( $id ) && current_user_can( 'read_post', $id );
}
/** 同画面だけ直列化。再接続後のautocommitで処理を続けない。 */
function exterior_exone_proposal_transaction( $callback ) {
	global $wpdb; $locked = false; $started = false; $commit = false; $retry = $wpdb->reconnect_retries; $silent = $wpdb->suppress_errors( true ); $wpdb->reconnect_retries = 0;
	$lock = 'exr_proposal_' . substr( hash( 'sha256', DB_NAME . ':' . $wpdb->prefix ), 0, 40 );
	try {
		$tables = array( $wpdb->posts, $wpdb->postmeta, $wpdb->terms, $wpdb->termmeta, $wpdb->term_taxonomy, $wpdb->term_relationships );
		$engines = exterior_exone_proposal_rows( $wpdb->prepare( 'SELECT TABLE_NAME,ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN (%s,%s,%s,%s,%s,%s)', $tables ) );
		if ( count( $engines ) !== 6 || array( 'InnoDB' ) !== array_values( array_unique( array_column( $engines, 'ENGINE' ) ) ) ) { throw new RuntimeException( 'storage' ); }
		if ( '1' !== (string) $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s,0)', $lock ) ) ) { throw new RuntimeException( 'busy' ); } $locked = true;
		exterior_exone_proposal_exec( 'START TRANSACTION' ); $started = true; $result = $callback(); $commit = true;
		exterior_exone_proposal_exec( 'COMMIT' ); $started = false; return $result;
	} catch ( Throwable $error ) {
		if ( $started && ! $commit ) { $wpdb->query( 'ROLLBACK' ); }
		return exterior_exone_proposal_error( in_array( $error->getMessage(), array( 'record', 'conflict', 'duplicate', 'busy', 'related', 'image' ), true ) ? $error->getMessage() : 'storage' );
	} finally {
		if ( $locked ) { $wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock ) ); } $wpdb->reconnect_retries = $retry; $wpdb->suppress_errors( $silent );
	}
}
/** metaは対象ID・キー・行数を限定し、取り込み識別には触れない。 */
function exterior_exone_proposal_set_meta( $id, $key, $value, $term = false ) {
	global $wpdb; $table = $term ? $wpdb->termmeta : $wpdb->postmeta; $column = $term ? 'term_id' : 'post_id';
	$rows = exterior_exone_proposal_rows( $wpdb->prepare( "SELECT * FROM {$table} WHERE {$column}=%d AND meta_key=%s FOR UPDATE", $id, $key ) );
	if ( count( $rows ) > 1 ) { throw new RuntimeException( 'record' ); }
	if ( null === $value ) { if ( $rows ) { exterior_exone_proposal_exec( $wpdb->prepare( "DELETE FROM {$table} WHERE meta_id=%d AND {$column}=%d AND meta_key=%s", $rows[0]['meta_id'], $id, $key ), 1 ); } }
	elseif ( $rows ) { if ( $rows[0]['meta_value'] !== (string) $value ) { exterior_exone_proposal_exec( $wpdb->prepare( "UPDATE {$table} SET meta_value=%s WHERE meta_id=%d AND {$column}=%d AND meta_key=%s", $value, $rows[0]['meta_id'], $id, $key ), 1 ); } }
	else { exterior_exone_proposal_exec( $wpdb->prepare( "INSERT INTO {$table} ({$column},meta_key,meta_value) VALUES (%d,%s,%s)", $id, $key, $value ), 1 ); }
}
function exterior_exone_proposal_protected( $record ) {
	$post = $record['post']; foreach ( array( 'post_title', 'post_content', 'post_modified', 'post_modified_gmt' ) as $key ) { unset( $post[ $key ] ); }
	$meta = array_values( array_filter( $record['meta'], static function( $row ) { return ! in_array( $row['meta_key'], exterior_exone_proposal_mutable_meta(), true ); } ) );
	$relations = array_values( array_filter( $record['relations'], static function( $row ) { return 'exr_works_package' !== $row['taxonomy']; } ) );
	return array( 'post' => $post, 'meta' => $meta, 'relations' => $relations );
}
function exterior_exone_proposal_cache( $ids, $terms ) {
	foreach ( array_unique( $ids ) as $id ) { clean_post_cache( $id ); wp_cache_delete( $id, 'exr_works_package_relationships' ); }
	if ( $terms ) { clean_term_cache( array_unique( $terms ), 'exr_works_package', false ); } // children optionなど共有設定には書き込まない。
}
function exterior_exone_proposal_unique_key( $key, $id, $term = false ) {
	global $wpdb; $table = $term ? $wpdb->termmeta : $wpdb->postmeta; $column = $term ? 'term_id' : 'post_id';
	$rows = exterior_exone_proposal_rows( $wpdb->prepare( "SELECT {$column} AS id FROM {$table} WHERE meta_key='_exr_source_key' AND BINARY meta_value=%s FOR UPDATE", $key ) );
	if ( $id ? ( count( $rows ) !== 1 || (int) $rows[0]['id'] !== $id ) : (bool) $rows ) { throw new RuntimeException( 'duplicate' ); }
}
function exterior_exone_proposal_recount( $term ) {
	global $wpdb; $current = exterior_exone_proposal_term_read( (int) $term['term']['term_id'], true ); exterior_exone_proposal_term_safe( $current ); $count = 0;
	foreach ( $current['relations'] as $row ) { $post = exterior_exone_proposal_read( (int) $row['object_id'], true ); if ( 'publish' === exterior_exone_proposal_meta( $post, '_exr_source_status' ) && $post['post']['post_date'] <= current_time( 'mysql' ) ) { $count++; } }
	if ( (int) $current['taxonomies'][0]['count'] !== $count ) { exterior_exone_proposal_exec( $wpdb->prepare( "UPDATE {$wpdb->term_taxonomy} SET count=%d WHERE term_taxonomy_id=%d AND term_id=%d AND taxonomy='exr_works_package'", $count, $current['taxonomies'][0]['term_taxonomy_id'], $current['term']['term_id'] ), 1 ); }
}
function exterior_exone_proposal_save( $raw ) {
	global $wpdb; if ( ! exterior_exone_proposal_allowed() ) { return exterior_exone_proposal_error( 'permission' ); }
	$input = exterior_exone_proposal_input( $raw, 'post' ); if ( is_wp_error( $input ) ) { return $input; }
	if ( ! wp_verify_nonce( $input['nonce'], exterior_exone_proposal_nonce( 'post', $input['id'], $input['creation'] ) ) ) { return exterior_exone_proposal_error( 'nonce' ); }
	if ( 'save' === $input['intent'] ) { foreach ( $input['images'] as $image ) { if ( ! exterior_exone_proposal_image_allowed( $image ) ) { return exterior_exone_proposal_error( 'image' ); } } }
	$id = $input['id']; $cache_terms = array();
	try {
		return exterior_exone_proposal_transaction( static function() use ( $input, &$id, &$cache_terms, $wpdb ) {
			$terms = exterior_exone_proposal_terms( true ); $by_id = array();
			foreach ( $terms as $term ) { $by_id[ (int) $term['term']['term_id'] ] = $term; }
			if ( 'save' === $input['intent'] && ! hash_equals( exterior_exone_proposal_version( $terms ), $input['tastes_version'] ) ) { throw new RuntimeException( 'conflict' ); }
			$before = $id ? exterior_exone_proposal_read( $id, true ) : null;
			if ( $id && ( ! exterior_exone_proposal_valid( $before ) || ! hash_equals( exterior_exone_proposal_version( $before ), $input['version'] ) ) ) { throw new RuntimeException( 'conflict' ); }
			$status = $before ? exterior_exone_proposal_meta( $before, '_exr_source_status' ) : 'draft';
			if ( ( 'save' === $input['intent'] && 'trash' === $status ) || ( 'trash' === $input['intent'] && 'trash' === $status ) || ( 'restore' === $input['intent'] && 'trash' !== $status ) ) { throw new RuntimeException( 'record' ); }
			$key = $before ? exterior_exone_proposal_meta( $before, '_exr_source_key' ) : 'exterior-exone-renewal:v1:admin:works:' . $input['creation'];
			$slug = $before ? $before['post']['post_name'] : 'proposal-' . str_replace( '-', '', $input['creation'] ); exterior_exone_proposal_unique_key( $key, $id );
			$names = exterior_exone_proposal_rows( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type='exr_works' AND post_name=%s FOR UPDATE", $slug ) );
			if ( $id ? ( count( $names ) !== 1 || (int) $names[0]['ID'] !== $id ) : (bool) $names ) { throw new RuntimeException( 'duplicate' ); }
			$old_tastes = array(); $touched = array();
			foreach ( $before ? $before['relations'] : array() as $row ) {
				if ( null === $row['taxonomy'] ) { throw new RuntimeException( 'record' ); }
				if ( 'exr_works_package' === $row['taxonomy'] ) { $term_id = (int) $row['term_id']; if ( ! isset( $by_id[ $term_id ] ) ) { throw new RuntimeException( 'record' ); } $old_tastes[] = $row; $touched[ $term_id ] = $by_id[ $term_id ]; }
			}
			if ( 'save' === $input['intent'] && $input['taste'] ) { if ( ! isset( $by_id[ $input['taste'] ] ) ) { throw new RuntimeException( 'record' ); } $touched[ $input['taste'] ] = $by_id[ $input['taste'] ]; }
			foreach ( $touched as $term_id => $term ) { exterior_exone_proposal_term_safe( $term ); exterior_exone_proposal_unique_key( exterior_exone_proposal_meta( $term, '_exr_source_key' ), $term_id, true ); $cache_terms[] = $term_id; }
			$now = current_time( 'mysql' ); $gmt = get_gmt_from_date( $now );
			if ( ! $id ) {
				$post = array( 'post_author' => get_current_user_id(), 'post_date' => $now, 'post_date_gmt' => $gmt, 'post_content' => $input['body'], 'post_title' => $input['title'], 'post_excerpt' => '', 'post_status' => 'exr_stored', 'comment_status' => 'closed', 'ping_status' => 'closed', 'post_password' => '', 'post_name' => $slug, 'to_ping' => '', 'pinged' => '', 'post_modified' => $now, 'post_modified_gmt' => $gmt, 'post_content_filtered' => '', 'post_parent' => 0, 'guid' => home_url( '/works/' . $slug . '/' ), 'menu_order' => 0, 'post_type' => 'exr_works', 'post_mime_type' => '', 'comment_count' => 0 );
				if ( 1 !== $wpdb->insert( $wpdb->posts, $post ) ) { throw new RuntimeException( 'storage' ); } $id = (int) $wpdb->insert_id;
				foreach ( array( '_exr_dataset' => 'exterior-exone-renewal:v1', '_exr_source_key' => $key, '_exr_import_state' => 'complete', '_exr_source_path' => '/works/' . $slug . '/' ) as $name => $value ) { exterior_exone_proposal_set_meta( $id, $name, $value ); }
			} elseif ( 'save' === $input['intent'] ) {
				exterior_exone_proposal_exec( $wpdb->prepare( "UPDATE {$wpdb->posts} SET post_title=%s,post_content=%s,post_modified=%s,post_modified_gmt=%s WHERE ID=%d AND post_type='exr_works' AND post_status='exr_stored'", $input['title'], $input['body'], $now, $gmt, $id ) );
			}
			if ( 'trash' === $input['intent'] ) { exterior_exone_proposal_set_meta( $id, '_exr_works_previous_status', $status ); $next = 'trash'; }
			elseif ( 'restore' === $input['intent'] ) { $next = exterior_exone_proposal_meta( $before, '_exr_works_previous_status' ); exterior_exone_proposal_set_meta( $id, '_exr_works_previous_status', null ); }
			else {
				$next = $input['status']; foreach ( $input['images'] as $name => $image ) { exterior_exone_proposal_set_meta( $id, '_exterior_exone_works_image_' . $name, $image ? (string) $image : null ); }
				exterior_exone_proposal_set_meta( $id, '_exterior_exone_works_gallery', null ); exterior_exone_proposal_set_meta( $id, '_exterior_exone_works_on_top', '1' === $input['on_top'] ? '1' : null );
				$selected_tt = $input['taste'] ? (int) $by_id[ $input['taste'] ]['taxonomies'][0]['term_taxonomy_id'] : 0; $already = false;
				foreach ( $old_tastes as $row ) { if ( (int) $row['term_taxonomy_id'] === $selected_tt ) { $already = true; continue; } exterior_exone_proposal_exec( $wpdb->prepare( "DELETE FROM {$wpdb->term_relationships} WHERE object_id=%d AND term_taxonomy_id=%d", $id, $row['term_taxonomy_id'] ), 1 ); }
				if ( $selected_tt && ! $already ) { exterior_exone_proposal_exec( $wpdb->prepare( "INSERT INTO {$wpdb->term_relationships} (object_id,term_taxonomy_id,term_order) VALUES (%d,%d,0)", $id, $selected_tt ), 1 ); }
			}
			exterior_exone_proposal_set_meta( $id, '_exr_source_status', $next ); $after = exterior_exone_proposal_read( $id, true );
			if ( ! exterior_exone_proposal_valid( $after ) || exterior_exone_proposal_meta( $after, '_exr_source_status' ) !== $next || ( $before && exterior_exone_proposal_protected( $before ) !== exterior_exone_proposal_protected( $after ) ) ) { throw new RuntimeException( 'storage' ); }
			if ( 'save' === $input['intent'] ) {
				if ( $after['post']['post_title'] !== $input['title'] || $after['post']['post_content'] !== $input['body'] || ( '1' === exterior_exone_proposal_meta( $after, '_exterior_exone_works_on_top', false ) ) !== ( '1' === $input['on_top'] ) || '' !== exterior_exone_proposal_meta( $after, '_exterior_exone_works_gallery', false ) ) { throw new RuntimeException( 'storage' ); }
				foreach ( $input['images'] as $name => $image ) { if ( (int) exterior_exone_proposal_meta( $after, '_exterior_exone_works_image_' . $name, false ) !== $image ) { throw new RuntimeException( 'storage' ); } }
				$selected = array(); foreach ( $after['relations'] as $row ) { if ( 'exr_works_package' === $row['taxonomy'] ) { $selected[] = (int) $row['term_id']; } } if ( $selected !== ( $input['taste'] ? array( $input['taste'] ) : array() ) ) { throw new RuntimeException( 'storage' ); }
			}
			foreach ( $touched as $term ) { exterior_exone_proposal_recount( $term ); }
			return array( 'id' => $id, 'status' => $next );
		} );
	} finally { exterior_exone_proposal_cache( $id ? array( $id ) : array(), $cache_terms ); }
}
function exterior_exone_proposal_term_save( $raw ) {
	global $wpdb; if ( ! exterior_exone_proposal_allowed() ) { return exterior_exone_proposal_error( 'permission' ); }
	$input = exterior_exone_proposal_input( $raw, 'term' ); if ( is_wp_error( $input ) ) { return $input; }
	if ( ! wp_verify_nonce( $input['nonce'], exterior_exone_proposal_nonce( 'term', $input['id'], $input['creation'] ) ) ) { return exterior_exone_proposal_error( 'nonce' ); }
	$id = $input['id']; $objects = array();
	try {
		return exterior_exone_proposal_transaction( static function() use ( $input, &$id, &$objects, $wpdb ) {
			$before = $id ? exterior_exone_proposal_term_read( $id, true ) : null;
			if ( $id && ( ! exterior_exone_proposal_term_valid( $before ) || ! hash_equals( exterior_exone_proposal_version( $before ), $input['version'] ) ) ) { throw new RuntimeException( 'conflict' ); }
			if ( $id ) { exterior_exone_proposal_term_safe( $before ); foreach ( $before['relations'] as $row ) { $objects[] = (int) $row['object_id']; } }
			$key = $before ? exterior_exone_proposal_meta( $before, '_exr_source_key' ) : 'exterior-exone-renewal:v1:admin:term:works_package:' . $input['creation']; exterior_exone_proposal_unique_key( $key, $id, true );
			if ( 'delete' === $input['intent'] ) {
				$tt = (int) $before['taxonomies'][0]['term_taxonomy_id'];
				foreach ( $before['relations'] as $row ) { exterior_exone_proposal_exec( $wpdb->prepare( "DELETE FROM {$wpdb->term_relationships} WHERE object_id=%d AND term_taxonomy_id=%d", $row['object_id'], $tt ), 1 ); }
				exterior_exone_proposal_exec( $wpdb->prepare( "DELETE FROM {$wpdb->termmeta} WHERE term_id=%d", $id ), count( $before['meta'] ) );
				exterior_exone_proposal_exec( $wpdb->prepare( "DELETE FROM {$wpdb->term_taxonomy} WHERE term_taxonomy_id=%d AND term_id=%d AND taxonomy='exr_works_package'", $tt, $id ), 1 );
				exterior_exone_proposal_exec( $wpdb->prepare( "DELETE FROM {$wpdb->terms} WHERE term_id=%d", $id ), 1 );
				if ( exterior_exone_proposal_term_read( $id, true ) ) { throw new RuntimeException( 'storage' ); }
				return array( 'id' => $id, 'status' => 'deleted' );
			}
			$names = exterior_exone_proposal_rows( $wpdb->prepare( "SELECT t.term_id FROM {$wpdb->terms} t JOIN {$wpdb->term_taxonomy} tt ON tt.term_id=t.term_id WHERE tt.taxonomy='exr_works_package' AND t.name=%s AND t.term_id<>%d FOR UPDATE", $input['name'], $id ) ); if ( $names ) { throw new RuntimeException( 'duplicate' ); }
			if ( $id ) { if ( $input['name'] !== $before['term']['name'] ) { exterior_exone_proposal_exec( $wpdb->prepare( "UPDATE {$wpdb->terms} SET name=%s WHERE term_id=%d", $input['name'], $id ), 1 ); } }
			else {
				$slug = 'taste-' . str_replace( '-', '', $input['creation'] );
				if ( exterior_exone_proposal_rows( $wpdb->prepare( "SELECT term_id FROM {$wpdb->terms} WHERE slug=%s FOR UPDATE", $slug ) ) ) { throw new RuntimeException( 'duplicate' ); }
				exterior_exone_proposal_exec( $wpdb->prepare( "INSERT INTO {$wpdb->terms} (name,slug,term_group) VALUES (%s,%s,0)", $input['name'], $slug ), 1 ); $id = (int) $wpdb->insert_id;
				exterior_exone_proposal_exec( $wpdb->prepare( "INSERT INTO {$wpdb->term_taxonomy} (term_id,taxonomy,description,parent,count) VALUES (%d,'exr_works_package','',0,0)", $id ), 1 );
				foreach ( array( '_exr_dataset' => 'exterior-exone-renewal:v1', '_exr_source_key' => $key, '_exr_import_state' => 'complete' ) as $name => $value ) { exterior_exone_proposal_set_meta( $id, $name, $value, true ); }
			}
			$after = exterior_exone_proposal_term_read( $id, true ); if ( ! exterior_exone_proposal_term_valid( $after ) || $after['term']['name'] !== $input['name'] ) { throw new RuntimeException( 'storage' ); }
			if ( $before ) { $expected = $before; $expected['term']['name'] = $input['name']; if ( $after !== $expected ) { throw new RuntimeException( 'storage' ); } }
			return array( 'id' => $id, 'status' => 'saved' );
		} );
	} finally { exterior_exone_proposal_cache( $objects, $id ? array( $id ) : array() ); }
}
function exterior_exone_proposal_url( $args = array(), $term = false ) { return add_query_arg( $args, admin_url( 'admin.php?page=' . ( $term ? 'exone-proposal-tastes' : 'exone-proposals' ) ) ); }
function exterior_exone_proposal_menu() {
	if ( ! exterior_exone_proposal_allowed() ) { return; }
	$GLOBALS['exterior_exone_proposal_hooks'] = array( add_menu_page( '提案事例', '提案事例', 'manage_options', 'exone-proposals', 'exterior_exone_proposal_render', 'dashicons-format-gallery', 22 ) );
	add_submenu_page( 'exone-proposals', '提案事例', '提案事例一覧', 'manage_options', 'exone-proposals', 'exterior_exone_proposal_render' );
	$GLOBALS['exterior_exone_proposal_hooks'][] = add_submenu_page( 'exone-proposals', 'デザインテイスト', 'デザインテイスト', 'manage_options', 'exone-proposal-tastes', 'exterior_exone_proposal_terms_render' );
}
function exterior_exone_proposal_old_labels( $labels ) {
	$labels = clone $labels;
	foreach ( array( 'name', 'singular_name', 'menu_name', 'name_admin_bar' ) as $key ) { $labels->$key = '施工事例（旧テーマ）'; }
	$labels->all_items = '施工事例一覧（旧テーマ）'; $labels->add_new_item = '施工事例を追加（旧テーマ）'; $labels->edit_item = '施工事例を編集（旧テーマ）';
	return $labels;
}
function exterior_exone_proposal_assets( $hook ) {
	if ( ! exterior_exone_proposal_allowed() || ! in_array( $hook, $GLOBALS['exterior_exone_proposal_hooks'] ?? array(), true ) ) { return; }
	wp_enqueue_media();
	wp_enqueue_style( 'exone-proposal-admin', get_theme_file_uri( 'css/coexistence-proposal-admin.css' ), array(), filemtime( get_theme_file_path( 'css/coexistence-proposal-admin.css' ) ) );
	wp_enqueue_script( 'exone-proposal-admin', get_theme_file_uri( 'js/coexistence-proposal-admin.js' ), array( 'jquery', 'media-views' ), filemtime( get_theme_file_path( 'js/coexistence-proposal-admin.js' ) ), true );
}
function exterior_exone_proposal_submit( $term = false ) {
	if ( ! exterior_exone_proposal_allowed() || 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) { wp_die( esc_html( 'この操作を行う権限がありません。' ), '', array( 'response' => 403 ) ); }
	$result = $term ? exterior_exone_proposal_term_save( wp_unslash( $_POST ) ) : exterior_exone_proposal_save( wp_unslash( $_POST ) );
	if ( is_wp_error( $result ) ) { wp_die( esc_html( $result->get_error_message() ), '', array( 'response' => in_array( $result->get_error_code(), array( 'permission', 'nonce' ), true ) ? 403 : 409, 'back_link' => true ) ); }
	wp_safe_redirect( exterior_exone_proposal_url( array( 'view' => 'trash' === $result['status'] ? 'trash' : 'all', 'saved' => '1' ), $term ) ); exit;
}
function exterior_exone_proposal_term_submit() { exterior_exone_proposal_submit( true ); }
function exterior_exone_proposal_hidden( $record, $creation, $term = false ) {
	$id = $record ? (int) ( $term ? $record['term']['term_id'] : $record['post']['ID'] ) : 0;
	echo '<input type="hidden" name="action" value="' . ( $term ? 'exone_proposal_taste' : 'exone_proposal' ) . '"><input type="hidden" name="id" value="' . esc_attr( $id ) . '"><input type="hidden" name="creation" value="' . esc_attr( $creation ) . '"><input type="hidden" name="version" value="' . esc_attr( $record ? exterior_exone_proposal_version( $record ) : '' ) . '">';
	wp_nonce_field( exterior_exone_proposal_nonce( $term ? 'term' : 'post', $id, $creation ), 'nonce' );
}
function exterior_exone_proposal_image_field( $name, $label, $id ) {
	echo '<div class="exone-proposal-image"><p class="exone-proposal-label">' . esc_html( $label ) . '</p><div class="exone-proposal-image-preview">';
	if ( $id && exterior_exone_proposal_image_allowed( $id ) ) { echo wp_get_attachment_image( $id, 'medium' ); }
	echo '</div><input type="hidden" name="image_' . esc_attr( $name ) . '" value="' . esc_attr( $id ) . '"><button type="button" class="button exone-proposal-select-image">画像を選ぶ</button> <button type="button" class="button-link exone-proposal-remove-image">画像を解除</button></div>';
}
function exterior_exone_proposal_render() {
	if ( ! exterior_exone_proposal_allowed() ) { wp_die( esc_html( 'この画面を表示する権限がありません。' ), '', array( 'response' => 403 ) ); }
	try { exterior_exone_proposal_render_content(); } catch ( Throwable $error ) { echo '<div class="wrap"><p>' . esc_html( exterior_exone_proposal_error( 'record' )->get_error_message() ) . '</p></div>'; }
}
function exterior_exone_proposal_render_content() {
	global $wpdb; $terms = exterior_exone_proposal_terms(); $by_id = array(); foreach ( $terms as $term ) { $by_id[ (int) $term['term']['term_id'] ] = $term; }
	$edit = isset( $_GET['edit'] ) && is_string( $_GET['edit'] ) && ctype_digit( $_GET['edit'] ) ? (int) $_GET['edit'] : 0; $new = isset( $_GET['new'] ) && '1' === $_GET['new'];
	$view = isset( $_GET['view'] ) && is_string( $_GET['view'] ) && in_array( $_GET['view'], array( 'publish', 'draft', 'trash' ), true ) ? $_GET['view'] : 'all';
	echo '<div class="wrap exone-proposal-admin"><h1>提案事例</h1><p>新テーマの提案事例を管理します。旧テーマの施工事例には変更を加えません。</p>';
	if ( isset( $_GET['saved'] ) && '1' === $_GET['saved'] ) { echo '<div class="notice notice-success"><p>保存しました。</p></div>'; }
	if ( $edit || $new ) {
		$record = $edit ? exterior_exone_proposal_read( $edit ) : null;
		if ( $edit && ( ! exterior_exone_proposal_valid( $record ) || 'trash' === exterior_exone_proposal_meta( $record, '_exr_source_status' ) ) ) { throw new RuntimeException( 'record' ); }
		$taste = 0; foreach ( $record ? $record['relations'] : array() as $row ) { if ( 'exr_works_package' === $row['taxonomy'] ) { $taste = (int) $row['term_id']; break; } }
		echo '<p><a href="' . esc_url( exterior_exone_proposal_url() ) . '">一覧へ戻る</a></p><form class="exone-proposal-editor" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">'; exterior_exone_proposal_hidden( $record, $new && ! $edit ? wp_generate_uuid4() : '' );
		echo '<input type="hidden" name="tastes_version" value="' . esc_attr( exterior_exone_proposal_version( $terms ) ) . '"><label for="proposal-title">タイトル <span>必須</span></label><input class="large-text" id="proposal-title" name="title" required value="' . esc_attr( $record ? $record['post']['post_title'] : '' ) . '"><label for="proposal-body">説明</label><textarea class="large-text" id="proposal-body" name="body" rows="8">' . esc_textarea( $record ? $record['post']['post_content'] : '' ) . '</textarea><p class="description">書式を付けない文章です。改行は表示に反映されます。</p>';
		foreach ( exterior_exone_proposal_fields() as $name => $label ) { exterior_exone_proposal_image_field( $name, $label, $record ? (int) exterior_exone_proposal_meta( $record, '_exterior_exone_works_image_' . $name, false ) : 0 ); }
		echo '<label for="proposal-taste">デザインテイスト</label><select id="proposal-taste" name="taste"><option value="0">未設定</option>';
		foreach ( $terms as $term ) { echo '<option value="' . esc_attr( $term['term']['term_id'] ) . '"' . selected( $taste, (int) $term['term']['term_id'], false ) . '>' . esc_html( $term['term']['name'] ) . '</option>'; }
		echo '</select><p><a href="' . esc_url( exterior_exone_proposal_url( array(), true ) ) . '">デザインテイストを管理</a></p><input type="hidden" name="on_top" value="0"><label><input type="checkbox" name="on_top" value="1"' . checked( $record && '1' === exterior_exone_proposal_meta( $record, '_exterior_exone_works_on_top', false ), true, false ) . '> トップページに掲載する</label><p class="description">公開中でチェックがある事例を、新しい順に最大5件表示します。</p><label for="proposal-status">公開状態</label><select id="proposal-status" name="status"><option value="draft"' . selected( $record ? exterior_exone_proposal_meta( $record, '_exr_source_status' ) : 'draft', 'draft', false ) . '>下書き</option><option value="publish"' . selected( $record ? exterior_exone_proposal_meta( $record, '_exr_source_status' ) : 'draft', 'publish', false ) . '>公開</option></select><p><button class="button button-primary" name="intent" value="save">保存する</button></p></form></div>'; return;
	}
	echo '<p><a class="page-title-action" href="' . esc_url( exterior_exone_proposal_url( array( 'new' => '1' ) ) ) . '">新規追加</a> <a href="' . esc_url( home_url( '/works/' ) ) . '" target="_blank" rel="noopener">公開一覧を確認</a></p><nav class="exone-proposal-tabs" aria-label="公開状態">';
	$labels = array( 'all' => 'すべて', 'publish' => '公開', 'draft' => '下書き', 'trash' => 'ゴミ箱' ); foreach ( $labels as $name => $label ) { echo '<a' . ( $view === $name ? ' aria-current="page"' : '' ) . ' href="' . esc_url( exterior_exone_proposal_url( array( 'view' => $name ) ) ) . '">' . esc_html( $label ) . '</a>'; }
	echo '</nav>';
	$records = array();
	if ( 'all' === $view ) {
		$records = exterior_exone_proposal_order_records();
		exterior_exone_proposal_order_controls( $records );
	} else {
		echo '<p>表示順を変更するには <a href="' . esc_url( exterior_exone_proposal_url() ) . '">すべての提案事例</a>を開いてください。</p>';
		foreach ( exterior_exone_proposal_rows( "SELECT ID FROM {$wpdb->posts} WHERE post_type='exr_works' AND post_status='exr_stored' ORDER BY menu_order ASC,post_date DESC,ID DESC" ) as $row ) {
			$record = exterior_exone_proposal_read( (int) $row['ID'] );
			if ( exterior_exone_proposal_valid( $record ) && $view === exterior_exone_proposal_meta( $record, '_exr_source_status' ) ) { $records[ (int) $row['ID'] ] = $record; }
		}
	}
	echo '<table class="widefat striped' . ( 'all' === $view ? ' exone-proposal-order-table' : '' ) . '"><thead><tr>' . ( 'all' === $view ? '<th>表示順</th>' : '' ) . '<th>タイトル</th><th>公開状態</th><th>テイスト</th><th>画像</th><th>TOP掲載</th><th>操作</th></tr></thead><tbody>';
	foreach ( $records as $id => $record ) {
		$status = exterior_exone_proposal_meta( $record, '_exr_source_status' );
		$names = array(); foreach ( $record['relations'] as $relation ) { if ( 'exr_works_package' === $relation['taxonomy'] && isset( $by_id[ (int) $relation['term_id'] ] ) ) { $names[] = $by_id[ (int) $relation['term_id'] ]['term']['name']; } } $images = 0; foreach ( exterior_exone_proposal_fields() as $key => $label ) { if ( exterior_exone_proposal_meta( $record, '_exterior_exone_works_image_' . $key, false ) ) { $images++; } }
		echo '<tr data-proposal-id="' . esc_attr( $id ) . '">';
		if ( 'all' === $view ) { exterior_exone_proposal_order_handle(); }
		echo '<th scope="row">' . esc_html( $record['post']['post_title'] ) . '</th><td>' . esc_html( $labels[ $status ] ) . '</td><td>' . esc_html( $names ? implode( '、', $names ) : '未設定' ) . '</td><td>' . esc_html( $images . '枚' ) . '</td><td>' . ( '1' === exterior_exone_proposal_meta( $record, '_exterior_exone_works_on_top', false ) ? 'あり' : 'なし' ) . '</td><td>';
		if ( 'trash' !== $status ) { echo '<a href="' . esc_url( exterior_exone_proposal_url( array( 'edit' => $id ) ) ) . '">編集</a> '; }
		echo '<form class="exone-proposal-row-action" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">'; exterior_exone_proposal_hidden( $record, '' ); echo '<button class="button-link' . ( 'trash' === $status ? '' : ' button-link-delete' ) . '" name="intent" value="' . ( 'trash' === $status ? 'restore' : 'trash' ) . '"' . ( 'trash' === $status ? '' : ' data-proposal-trash="true"' ) . '>' . ( 'trash' === $status ? '元に戻す' : 'ゴミ箱へ移動' ) . '</button></form></td></tr>';
	}
	if ( ! $records ) { echo '<tr><td colspan="' . ( 'all' === $view ? '7' : '6' ) . '">該当する提案事例はありません。</td></tr>'; }
	echo '</tbody></table><p>ゴミ箱へ移動した事例は公開されません。復元すると移動前の公開状態へ戻ります。完全削除は行いません。</p></div>';
}
function exterior_exone_proposal_terms_render() {
	if ( ! exterior_exone_proposal_allowed() ) { wp_die( esc_html( 'この画面を表示する権限がありません。' ), '', array( 'response' => 403 ) ); }
	try {
		$terms = exterior_exone_proposal_terms(); $edit = isset( $_GET['edit'] ) && is_string( $_GET['edit'] ) && ctype_digit( $_GET['edit'] ) ? (int) $_GET['edit'] : 0; $record = $edit ? exterior_exone_proposal_term_read( $edit ) : null; if ( $edit && ! exterior_exone_proposal_term_valid( $record ) ) { throw new RuntimeException( 'record' ); }
		echo '<div class="wrap exone-proposal-admin"><h1>デザインテイスト</h1><p>提案事例の選択肢と公開一覧の絞り込みに使う名称です。</p>';
		if ( isset( $_GET['saved'] ) && '1' === $_GET['saved'] ) { echo '<div class="notice notice-success"><p>保存しました。</p></div>'; }
		echo '<form class="exone-proposal-editor" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">'; exterior_exone_proposal_hidden( $record, $record ? '' : wp_generate_uuid4(), true ); echo '<label for="taste-name">名称 <span>必須</span></label><input class="regular-text" id="taste-name" name="name" required value="' . esc_attr( $record ? $record['term']['name'] : '' ) . '"><p><button class="button button-primary" name="intent" value="save">' . ( $record ? '名称を保存' : '追加する' ) . '</button> <a href="' . esc_url( exterior_exone_proposal_url( array(), true ) ) . '">新規追加へ</a></p></form><h2>登録済みのテイスト</h2><table class="widefat striped"><thead><tr><th>名称</th><th>利用事例数</th><th>操作</th></tr></thead><tbody>';
		foreach ( $terms as $term ) { $count = count( $term['relations'] ); echo '<tr><th scope="row">' . esc_html( $term['term']['name'] ) . '</th><td>' . esc_html( $count ) . '件</td><td><a href="' . esc_url( exterior_exone_proposal_url( array( 'edit' => (int) $term['term']['term_id'] ), true ) ) . '">名称を変更</a><form class="exone-proposal-row-action" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">'; exterior_exone_proposal_hidden( $term, '', true ); echo '<button class="button-link button-link-delete" name="intent" value="delete" data-proposal-delete-taste="' . esc_attr( $count ) . '">削除</button></form></td></tr>'; }
		if ( ! $terms ) { echo '<tr><td colspan="3">デザインテイストはありません。</td></tr>'; } echo '</tbody></table><p>利用事例数には下書き・ゴミ箱も含みます。テイストを削除しても事例と画像は残り、そのテイストの関連だけが外れて未設定になります。</p></div>';
	} catch ( Throwable $error ) { echo '<div class="wrap"><p>' . esc_html( exterior_exone_proposal_error( 'record' )->get_error_message() ) . '</p></div>'; }
}
if ( defined( 'EXR_COEXISTENCE' ) && true === EXR_COEXISTENCE ) {
	add_action( 'admin_menu', 'exterior_exone_proposal_menu' );
	add_action( 'admin_enqueue_scripts', 'exterior_exone_proposal_assets' );
	add_action( 'admin_post_exone_proposal', 'exterior_exone_proposal_submit' );
	add_action( 'admin_post_exone_proposal_taste', 'exterior_exone_proposal_term_submit' );
	add_filter( 'post_type_labels_works', 'exterior_exone_proposal_old_labels', 100 );
}
