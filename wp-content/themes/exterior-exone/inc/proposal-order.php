<?php
/** 提案事例の手動表示順。保存時は対象投稿の menu_order だけを変更する。 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function exterior_exone_proposal_orderby() {
	return array( 'menu_order' => 'ASC', 'date' => 'DESC', 'ID' => 'DESC' );
}

function exterior_exone_proposal_order_coexistence() {
	return defined( 'EXR_COEXISTENCE' ) && true === EXR_COEXISTENCE;
}

/** 通常ローカルと共存モードの対象を混ぜない。 */
function exterior_exone_proposal_order_records( $lock = false ) {
	global $wpdb;
	$coexistence = exterior_exone_proposal_order_coexistence();
	$where = $coexistence ? "post_type='exr_works' AND post_status='exr_stored'" : "post_type='works' AND post_status IN ('publish','draft','pending','private','future')";
	$posts = exterior_exone_proposal_rows( "SELECT * FROM {$wpdb->posts} WHERE {$where} ORDER BY menu_order ASC,post_date DESC,ID DESC" . ( $lock ? ' FOR UPDATE' : '' ) );
	if ( count( $posts ) > 1000 ) { throw new RuntimeException( 'record' ); }
	$records = array();
	foreach ( $posts as $post ) {
		$id = (int) $post['ID'];
		if ( $coexistence ) {
			$record = exterior_exone_proposal_read( $id, $lock );
			if ( ! exterior_exone_proposal_valid( $record ) ) { throw new RuntimeException( 'record' ); }
			if ( 'trash' === exterior_exone_proposal_meta( $record, '_exr_source_status' ) ) { continue; }
			if ( $lock ) { exterior_exone_proposal_unique_key( exterior_exone_proposal_meta( $record, '_exr_source_key' ), $id ); }
		} else {
			$record = array( 'post' => $post );
		}
		$records[ $id ] = $record;
	}
	return $records;
}

function exterior_exone_proposal_order_input( $raw ) {
	foreach ( array( 'ids', 'version', 'nonce' ) as $key ) {
		if ( ! isset( $raw[ $key ] ) || ! is_string( $raw[ $key ] ) ) { return exterior_exone_proposal_error( 'record' ); }
	}
	if ( ! preg_match( '/\A[a-f0-9]{64}\z/D', $raw['version'] ) || strlen( $raw['ids'] ) > 12000 ) { return exterior_exone_proposal_error( 'record' ); }
	$ids = explode( ',', $raw['ids'] );
	if ( count( $ids ) > 1000 ) { return exterior_exone_proposal_error( 'record' ); }
	foreach ( $ids as $id ) {
		if ( ! preg_match( '/\A[1-9][0-9]{0,9}\z/D', $id ) ) { return exterior_exone_proposal_error( 'record' ); }
	}
	$ids = array_map( 'intval', $ids );
	if ( count( $ids ) !== count( array_unique( $ids ) ) ) { return exterior_exone_proposal_error( 'record' ); }
	return array( 'ids' => $ids, 'version' => $raw['version'], 'nonce' => $raw['nonce'] );
}

function exterior_exone_proposal_order_save( $raw ) {
	global $wpdb;
	if ( ! current_user_can( 'manage_options' ) || 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) { return exterior_exone_proposal_error( 'permission' ); }
	$input = exterior_exone_proposal_order_input( $raw );
	if ( is_wp_error( $input ) ) { return $input; }
	if ( ! wp_verify_nonce( $input['nonce'], 'exone_proposal_order' ) ) { return exterior_exone_proposal_error( 'nonce' ); }
	$touched = array();
	try {
		$result = exterior_exone_proposal_transaction( static function() use ( $input, &$touched, $wpdb ) {
			$records = exterior_exone_proposal_order_records( true );
			$expected = array_keys( $records ); $submitted = $input['ids'];
			sort( $expected, SORT_NUMERIC ); sort( $submitted, SORT_NUMERIC );
			if ( $expected !== $submitted || ! hash_equals( exterior_exone_proposal_version( $records ), $input['version'] ) ) { throw new RuntimeException( 'conflict' ); }
			$rank = -count( $input['ids'] );
			foreach ( $input['ids'] as $id ) {
				$post = $records[ $id ]['post'];
				if ( (int) $post['menu_order'] !== $rank ) {
					$touched[] = $id;
					exterior_exone_proposal_exec( $wpdb->prepare( "UPDATE {$wpdb->posts} SET menu_order=%d WHERE ID=%d AND post_type=%s AND post_status=%s AND menu_order=%d", $rank, $id, $post['post_type'], $post['post_status'], $post['menu_order'] ), 1 );
				}
				$rank++;
			}
			return array( 'version' => exterior_exone_proposal_version( exterior_exone_proposal_order_records( true ) ) );
		} );
	} finally {
		// Unknown COMMIT outcomes also invalidate local object/query caches; never retry automatically.
		foreach ( $touched as $id ) { clean_post_cache( $id ); }
	}
	if ( $touched && ! is_wp_error( $result ) ) {
		try { exterior_exone_proposal_order_purge(); }
		catch ( Throwable $error ) { return new WP_Error( 'cache', '表示順は保存しましたが、公開ページのキャッシュを更新できませんでした。管理者にキャッシュの確認を依頼してください。' ); }
	}
	return $result;
}

/** WP Fastest Cache の提案事例配下だけを更新。全体消去・CDN API・設定保存は行わない。 */
function exterior_exone_proposal_order_purge() {
	global $wp_fastest_cache;
	if ( ! isset( $wp_fastest_cache ) ) { return; }
	if ( ! is_callable( array( $wp_fastest_cache, 'specificDeleteCache' ) ) || ! is_callable( array( $wp_fastest_cache, 'getWpContentDir' ) ) ) { throw new RuntimeException( 'cache' ); }
	$url = home_url( '/works/(.*)' );
	// The plugin derives its path this way. Only the fixed home-site works subtree is permitted.
	if ( ! preg_match( '~\Ahttps?://[^/]+/(.+)/\(\.\*\)\z~', $url, $matches ) || false !== strpos( $matches[1], '..' ) ) { throw new RuntimeException( 'cache' ); }
	$before = array();
	foreach ( array( '/cache/all/', '/cache/wpfc-mobile-cache/' ) as $root ) {
		$path = $wp_fastest_cache->getWpContentDir( $root ) . $matches[1] . '/';
		clearstatcache( true, $path );
		if ( file_exists( $path ) ) {
			$stat = @stat( $path );
			if ( false === $stat ) { throw new RuntimeException( 'cache' ); }
			$before[ $path ] = array( $stat['dev'], $stat['ino'] );
		}
	}
	$only_works = static function( $value ) use ( $url ) { return array( (object) array( 'url' => $url ) ); };
	add_filter( 'pre_option_WpFastestCacheCSP', $only_works, PHP_INT_MAX );
	try {
		$wp_fastest_cache->specificDeleteCache();
		// The plugin has no return status. A failed rename must not be reported as success.
		foreach ( $before as $path => $identity ) {
			clearstatcache( true, $path );
			if ( file_exists( $path ) ) {
				$stat = @stat( $path );
				if ( false === $stat || $identity === array( $stat['dev'], $stat['ino'] ) ) { throw new RuntimeException( 'cache' ); }
			}
		}
	} finally {
		remove_filter( 'pre_option_WpFastestCacheCSP', $only_works, PHP_INT_MAX );
	}
}

function exterior_exone_proposal_order_ajax() {
	$result = exterior_exone_proposal_order_save( wp_unslash( $_POST ) );
	if ( is_wp_error( $result ) ) { wp_send_json_error( array( 'message' => $result->get_error_message() ), 409 ); }
	wp_send_json_success( $result );
}

function exterior_exone_proposal_order_controls( $records ) {
	echo '<div class="exone-proposal-order-controls" data-version="' . esc_attr( exterior_exone_proposal_version( $records ) ) . '" data-nonce="' . esc_attr( wp_create_nonce( 'exone_proposal_order' ) ) . '" data-endpoint="' . esc_url( admin_url( 'admin-ajax.php' ) ) . '"><p>左端のハンドルをドラッグ、または上下ボタンで移動し、「表示順を保存」を押してください。公開一覧と詳細ページ内の一覧に反映されます。新しく追加した事例は保存済み事例の後ろに並びます。</p><button type="button" class="button button-primary exone-proposal-order-save" disabled>表示順を保存</button> <span class="exone-proposal-order-status" role="status" aria-live="polite">保存済みの順番です。</span><noscript><p>並べ替えには JavaScript を有効にしてください。</p></noscript></div>';
}

function exterior_exone_proposal_order_handle() {
	echo '<td class="exone-proposal-order-cell"><span class="exone-proposal-order-handle" title="ドラッグして移動" aria-hidden="true">☰</span><button type="button" class="button exone-proposal-order-up" aria-label="この事例を上へ移動">↑</button><button type="button" class="button exone-proposal-order-down" aria-label="この事例を下へ移動">↓</button></td>';
}

function exterior_exone_proposal_order_local_page() {
	if ( exterior_exone_proposal_order_coexistence() || ! current_user_can( 'manage_options' ) ) { wp_die( 'この画面を表示する権限がありません。' ); }
	echo '<div class="wrap"><h1>提案事例の表示順</h1>';
	try {
		$records = exterior_exone_proposal_order_records();
		exterior_exone_proposal_order_controls( $records );
		echo '<table class="widefat striped exone-proposal-order-table"><thead><tr><th>表示順</th><th>タイトル</th><th>公開状態</th></tr></thead><tbody>';
		foreach ( $records as $id => $record ) {
			$status = get_post_status_object( $record['post']['post_status'] );
			echo '<tr data-proposal-id="' . esc_attr( $id ) . '">'; exterior_exone_proposal_order_handle();
			echo '<th scope="row"><a href="' . esc_url( get_edit_post_link( $id ) ) . '">' . esc_html( $record['post']['post_title'] ) . '</a></th><td>' . esc_html( $status ? $status->label : $record['post']['post_status'] ) . '</td></tr>';
		}
		echo '</tbody></table>';
	} catch ( Throwable $error ) { echo '<p>' . esc_html( exterior_exone_proposal_error( 'record' )->get_error_message() ) . '</p>'; }
	echo '</div>';
}

function exterior_exone_proposal_order_menu() {
	if ( ! exterior_exone_proposal_order_coexistence() ) { add_submenu_page( 'edit.php?post_type=works', '提案事例の表示順', '表示順', 'manage_options', 'exone-proposal-order', 'exterior_exone_proposal_order_local_page' ); }
}

function exterior_exone_proposal_order_assets() {
	$page = isset( $_GET['page'] ) && is_string( $_GET['page'] ) ? $_GET['page'] : '';
	if ( ! current_user_can( 'manage_options' ) || ( exterior_exone_proposal_order_coexistence() ? 'exone-proposals' !== $page : 'exone-proposal-order' !== $page ) ) { return; }
	wp_enqueue_style( 'exone-proposal-order', get_theme_file_uri( 'css/proposal-order.css' ), array(), filemtime( get_theme_file_path( 'css/proposal-order.css' ) ) );
	wp_enqueue_script( 'exone-proposal-order', get_theme_file_uri( 'js/proposal-order.js' ), array( 'jquery', 'jquery-ui-sortable' ), filemtime( get_theme_file_path( 'js/proposal-order.js' ) ), true );
}

add_action( 'admin_menu', 'exterior_exone_proposal_order_menu' );
add_action( 'admin_enqueue_scripts', 'exterior_exone_proposal_order_assets' );
add_action( 'wp_ajax_exone_proposal_order', 'exterior_exone_proposal_order_ajax' );
