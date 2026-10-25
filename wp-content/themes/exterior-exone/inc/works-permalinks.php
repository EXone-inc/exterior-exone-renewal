<?php
/** 2026-10-24 承認済み提案事例URL。旧URLは公開中の移転先だけへ301転送する。 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function exterior_exone_works_permalink_map() {
	return array(
		array( 69, 3945, 'works-sample-01', 'black-carport-entrance' ),
		array( 70, 3946, 'works-sample-02', 'wood-look-fence-deck' ),
		array( 71, 3947, 'works-sample-03', 'japanese-modern-lattice' ),
		array( 72, 3948, 'works-sample-04', 'modern-frame-carport' ),
		array( 73, 3949, 'works-sample-05', 'stone-look-wall-lighting' ),
		array( 74, 3950, 'works-sample-06', 'stone-look-open-exterior' ),
		array( 31, 3944, '【トップ非掲載】物置とフェンスの目隠し外構', 'wood-look-black-lattice' ),
		array( 30, 3943, 'コンクリートとガレージで魅せる外構', 'modern-entrance-step-lighting' ),
		array( 29, 3942, 'カーポートとタイルテラスのある住まい', 'wood-look-lattice-carport' ),
		array( 28, 3941, 'ウッドデッキと人工芝で作る快適なエクステリア', 'gated-entrance-planting' ),
		array( 27, 3940, 'ロックガーデンを活用した個性的なエントランス', 'single-story-courtyard' ),
		array( 26, 3939, 'グレー基調のモダンエクステリアと植栽との調和', 'dark-house-planting' ),
		array( 0, 4004, 'proposal-b2d617675bf842c38b3000c4f7fcc94d', 'wood-look-frame-screen' ),
		array( 0, 4005, 'proposal-422cc73ad91e42829ca6c9589b01f762', 'wood-look-wall-planting' ),
	);
}

/** 固定の旧パスのみ。存在しない／非公開の移転先には転送しない。 */
function exterior_exone_works_permalink_target( $request ) {
	$path = exterior_exone_coexistence_normalize_path( $request );
	foreach ( exterior_exone_works_permalink_map() as $item ) {
		if ( '/works/' . $item[2] . '/' !== $path ) { continue; }
		$posts = exterior_exone_content_posts( array( 'post_type' => 'works', 'post_status' => 'publish', 'name' => $item[3], 'posts_per_page' => 2 ) );
		if ( 1 !== count( $posts ) ) { return ''; }
		return exterior_exone_store_link( home_url( '/works/' . $item[3] . '/' ) );
	}
	return '';
}
function exterior_exone_works_permalink_redirect() {
	global $wp;
	if ( ! in_array( $_SERVER['REQUEST_METHOD'] ?? '', array( 'GET', 'HEAD' ), true ) || is_admin() || is_preview() || is_feed() ) { return; }
	$url = exterior_exone_works_permalink_target( $wp->request );
	if ( $url ) { wp_safe_redirect( $url, 301, 'EXone Works Permalinks' ); exit; }
}
add_action( 'template_redirect', 'exterior_exone_works_permalink_redirect', 0 );

/** 管理者が明示実行する固定移行。閲覧・テーマ切替による自動更新はしない。 */
function exterior_exone_works_permalink_plan( $lock = false ) {
	global $wpdb;
	$coexistence = exterior_exone_coexistence_enabled();
	$host = wp_parse_url( home_url( '/' ), PHP_URL_HOST );
	if ( $host !== ( $coexistence ? 'exterior-exone.com' : 'exterior-exone-renewal.local' ) ) { throw new RuntimeException( 'record' ); }
	$plan = array();
	foreach ( exterior_exone_works_permalink_map() as $item ) {
		$id = $item[ $coexistence ? 1 : 0 ];
		if ( ! $id ) { continue; } // 本番で新規追加した2件はローカルに存在しない。
		$record = exterior_exone_proposal_read( $id, $lock );
		if ( ! $record || ( $coexistence ? ! exterior_exone_proposal_valid( $record ) : ( 'works' !== $record['post']['post_type'] || 'publish' !== $record['post']['post_status'] ) ) ) { throw new RuntimeException( 'record' ); }
		if ( $coexistence && 'publish' !== exterior_exone_proposal_meta( $record, '_exr_source_status' ) ) { throw new RuntimeException( 'record' ); }
		$old = sanitize_title( $item[2] ); $new = $item[3]; $slug = $record['post']['post_name'];
		if ( ! in_array( $slug, array( $old, $new ), true ) ) { throw new RuntimeException( 'conflict' ); }
		$duplicates = exterior_exone_proposal_rows( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type=%s AND ID<>%d AND post_name IN (%s,%s)" . ( $lock ? ' FOR UPDATE' : '' ), $record['post']['post_type'], $id, $old, $new ) );
		if ( $duplicates ) { throw new RuntimeException( 'duplicate' ); }
		$source = exterior_exone_proposal_meta( $record, '_exr_source_path', false );
		if ( null === $source || ( '' !== $source && rawurldecode( '/works/' . $slug . '/' ) !== rawurldecode( $source ) ) ) { throw new RuntimeException( 'record' ); }
		$plan[] = array( 'id' => $id, 'old' => $old, 'new' => $new, 'source' => $source, 'record' => $record );
	}
	return $plan;
}

function exterior_exone_works_permalink_save( $raw ) {
	global $wpdb;
	if ( ! current_user_can( 'manage_options' ) || 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) { return exterior_exone_proposal_error( 'permission' ); }
	if ( ! isset( $raw['nonce'], $raw['version'] ) || ! is_string( $raw['nonce'] ) || ! is_string( $raw['version'] ) || ! wp_verify_nonce( $raw['nonce'], 'exone_works_permalinks_20261024' ) ) { return exterior_exone_proposal_error( 'nonce' ); }
	$touched = array();
	try {
		$result = exterior_exone_proposal_transaction( static function() use ( $raw, &$touched, $wpdb ) {
			$plan = exterior_exone_works_permalink_plan( true );
			if ( ! hash_equals( exterior_exone_proposal_version( $plan ), $raw['version'] ) ) { throw new RuntimeException( 'conflict' ); }
			foreach ( $plan as $item ) {
				$before = $item['record']; $post = $before['post'];
				if ( $post['post_name'] === $item['new'] ) { continue; }
				$touched[] = $item['id'];
				exterior_exone_proposal_exec( $wpdb->prepare( "UPDATE {$wpdb->posts} SET post_name=%s WHERE ID=%d AND post_type=%s AND post_status=%s AND post_name=%s", $item['new'], $item['id'], $post['post_type'], $post['post_status'], $item['old'] ), 1 );
				if ( '' !== $item['source'] ) { exterior_exone_proposal_set_meta( $item['id'], '_exr_source_path', '/works/' . $item['new'] . '/' ); }
				$after = exterior_exone_proposal_read( $item['id'], true );
				$expected = $before; $expected['post']['post_name'] = $item['new'];
				foreach ( $expected['meta'] as &$meta ) { if ( '_exr_source_path' === $meta['meta_key'] && '' !== $item['source'] ) { $meta['meta_value'] = '/works/' . $item['new'] . '/'; } } unset( $meta );
				if ( $expected !== $after ) { throw new RuntimeException( 'record' ); }
			}
			return array( 'changed' => count( $touched ), 'total' => count( $plan ) );
		} );
	} finally { foreach ( $touched as $id ) { clean_post_cache( $id ); } }
	if ( ! is_wp_error( $result ) && $touched ) {
		try { exterior_exone_proposal_order_purge(); }
		catch ( Throwable $error ) { return new WP_Error( 'cache', 'URLは保存しました。公開ページのキャッシュ更新を確認してください。' ); }
	}
	return $result;
}

function exterior_exone_works_permalink_admin() {
	if ( ! current_user_can( 'manage_options' ) ) { wp_die( '権限がありません。', '', array( 'response' => 403 ) ); }
	echo '<div class="wrap"><h1>提案事例のURL変更（2026-10-24）</h1>';
	if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
		$result = exterior_exone_works_permalink_save( wp_unslash( $_POST ) );
		if ( is_wp_error( $result ) ) { echo '<div class="notice notice-error"><p>' . esc_html( $result->get_error_message() ) . '</p></div>'; }
		else { echo '<div class="notice notice-success"><p>' . esc_html( $result['changed'] . '件のURLを変更しました。本文・画像・表示順は保持しています。' ) . '</p></div>'; }
	}
	try { $plan = exterior_exone_works_permalink_plan(); }
	catch ( Throwable $error ) { echo '<p>対象データの状態が一致しません。変更せず停止しました。</p></div>'; return; }
	$pending = 0;
	echo '<table class="widefat striped"><thead><tr><th>タイトル</th><th>新URL</th><th>状態</th></tr></thead><tbody>';
	foreach ( $plan as $item ) {
		$done = $item['record']['post']['post_name'] === $item['new']; if ( ! $done ) { $pending++; }
		echo '<tr><td>' . esc_html( $item['record']['post']['post_title'] ) . '</td><td><code>/works/' . esc_html( $item['new'] ) . '/</code></td><td>' . ( $done ? '変更済み' : '未変更' ) . '</td></tr>';
	}
	echo '</tbody></table><p>承認済みの対応表に従い、既存の提案事例のURLだけを変更します。旧URLは公開中の新URLへ301転送します。</p>';
	if ( $pending ) {
		echo '<form method="post"><input type="hidden" name="version" value="' . esc_attr( exterior_exone_proposal_version( $plan ) ) . '">';
		wp_nonce_field( 'exone_works_permalinks_20261024', 'nonce' );
		submit_button( $pending . '件のURLを変更する' ); echo '</form>';
	} else { echo '<p><strong>対象' . esc_html( count( $plan ) ) . '件のURL変更は完了しています。</strong></p>'; }
	echo '</div>';
}
add_action( 'admin_menu', static function() { add_management_page( '提案事例のURL変更', '提案事例のURL変更', 'manage_options', 'exone-works-permalinks-20261024', 'exterior_exone_works_permalink_admin' ); } );
