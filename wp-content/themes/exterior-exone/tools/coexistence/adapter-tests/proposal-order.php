<?php
/** In-memory boundary/transaction regression. No WordPress, disk, DB or network writes. */
if ( PHP_SAPI !== 'cli' ) { exit( 1 ); }
define( 'ABSPATH', __DIR__ . '/' ); define( 'DB_NAME', 'fixture' ); define( 'ARRAY_A', 'ARRAY_A' );
define( 'EXR_COEXISTENCE', in_array( '--coexistence', $argv, true ) );
$checks = 0; $allowed = true; $nonce = true; $cleaned = array(); $_SERVER['REQUEST_METHOD'] = 'POST';
class WP_Error { private $code; function __construct( $code, $message ) { $this->code = $code; } function get_error_code() { return $this->code; } }
function check( $ok, $name ) { global $checks; if ( ! $ok ) { throw new RuntimeException( $name ); } $checks++; }
function current_user_can( $cap ) { return $GLOBALS['allowed']; }
function wp_verify_nonce( $nonce, $action ) { return $GLOBALS['nonce'] && 'exone_proposal_order' === $action; }
function wp_salt( $kind ) { return 'fixture-salt'; }
function is_wp_error( $value ) { return $value instanceof WP_Error; }
function add_action( $hook, $callback ) {}
function add_filter( $hook, $callback, $priority = 10 ) { if ( 'pre_option_WpFastestCacheCSP' === $hook ) { $GLOBALS['cache_filter'] = $callback; } }
function remove_filter( $hook, $callback, $priority = 10 ) { if ( 'pre_option_WpFastestCacheCSP' === $hook && $GLOBALS['cache_filter'] === $callback ) { unset( $GLOBALS['cache_filter'] ); } }
function home_url( $path ) { return 'https://fixture.invalid' . $path; }
function clean_post_cache( $id ) { $GLOBALS['cleaned'][] = $id; }
require dirname( __DIR__, 3 ) . '/inc/coexistence-proposal-admin.php';
require dirname( __DIR__, 3 ) . '/inc/proposal-order.php';

class OrderDb {
	public $prefix = 'wp_'; public $posts = 'wp_posts'; public $postmeta = 'wp_postmeta'; public $terms = 'wp_terms'; public $termmeta = 'wp_termmeta'; public $term_taxonomy = 'wp_term_taxonomy'; public $term_relationships = 'wp_term_relationships';
	public $last_error = ''; public $reconnect_retries = 5; public $data = array(); public $snapshot = array(); public $fail_after = 0; public $updates = 0; public $statements = array();
	function __construct() {
		foreach ( array( 11, 22, 33 ) as $id ) { $this->data[ $id ] = array( 'ID' => (string) $id, 'post_type' => EXR_COEXISTENCE ? 'exr_works' : 'works', 'post_status' => EXR_COEXISTENCE ? 'exr_stored' : 'publish', 'post_title' => 'Title ' . $id, 'post_content' => 'unchanged', 'post_date' => '2026-10-21 12:00:00', 'menu_order' => '0' ); }
	}
	function suppress_errors( $value ) { return false; }
	function prepare( $sql, ...$args ) {
		if ( isset( $args[0] ) && is_array( $args[0] ) ) { $args = $args[0]; }
		return preg_replace_callback( '/%[ds]/', function( $match ) use ( &$args ) { $value = array_shift( $args ); return '%d' === $match[0] ? (string) (int) $value : "'" . str_replace( "'", "''", $value ) . "'"; }, $sql );
	}
	function get_var( $sql ) { return '1'; }
	function get_results( $sql, $format ) {
		if ( strpos( $sql, 'information_schema' ) !== false ) { return array_fill( 0, 6, array( 'ENGINE' => 'InnoDB' ) ); }
		if ( strpos( $sql, 'term_relationships' ) !== false ) { return array(); }
		if ( strpos( $sql, 'wp_postmeta' ) !== false ) {
			if ( preg_match( '/post_id=(\d+)/', $sql, $match ) ) {
				$id = (int) $match[1]; $rows = array();
				foreach ( array( '_exr_dataset' => 'exterior-exone-renewal:v1', '_exr_import_state' => 'complete', '_exr_source_key' => 'exterior-exone-renewal:v1:post:works:' . $id, '_exr_source_hash' => str_repeat( 'a', 64 ), '_exr_source_fingerprint' => str_repeat( 'b', 64 ), '_exr_source_status' => 'publish' ) as $key => $value ) { $rows[] = array( 'meta_key' => $key, 'meta_value' => $value ); }
				return $rows;
			}
			if ( preg_match( '/post:works:(\d+)/', $sql, $match ) ) { return array( array( 'id' => $match[1] ) ); }
		}
		if ( preg_match( '/WHERE ID=(\d+)/', $sql, $match ) ) { return isset( $this->data[ (int) $match[1] ] ) ? array( $this->data[ (int) $match[1] ] ) : array(); }
		$rows = array_values( $this->data );
		usort( $rows, function( $a, $b ) { return (int) $a['menu_order'] <=> (int) $b['menu_order'] ?: strcmp( $b['post_date'], $a['post_date'] ) ?: (int) $b['ID'] <=> (int) $a['ID']; } );
		return $rows;
	}
	function query( $sql ) {
		$this->statements[] = $sql;
		if ( 'START TRANSACTION' === $sql ) { $this->snapshot = $this->data; $this->updates = 0; return 0; }
		if ( 'ROLLBACK' === $sql ) { $this->data = $this->snapshot; return 0; }
		if ( 'COMMIT' === $sql ) { return 0; }
		if ( preg_match( '/\ASET/', $sql ) ) { return false; }
		if ( preg_match( '/\AUPDATE wp_posts SET menu_order=(-?\d+) WHERE ID=(\d+) AND post_type=\x27(exr_works|works)\x27 AND post_status=\x27(exr_stored|publish)\x27 AND menu_order=(-?\d+)\z/', $sql, $m ) ) {
			$this->updates++; if ( $this->fail_after && $this->updates === $this->fail_after ) { return false; }
			$id = (int) $m[2]; if ( ! isset( $this->data[ $id ] ) || $this->data[ $id ]['post_type'] !== $m[3] || $this->data[ $id ]['post_status'] !== $m[4] || (int) $this->data[ $id ]['menu_order'] !== (int) $m[5] ) { return 0; }
			$this->data[ $id ]['menu_order'] = $m[1]; return 1;
		}
		throw new RuntimeException( 'Unexpected SQL' );
	}
}
$wpdb = new OrderDb(); $original = $wpdb->data;
check( exterior_exone_proposal_orderby() === array( 'menu_order' => 'ASC', 'date' => 'DESC', 'ID' => 'DESC' ), 'Shared complete ordering' );
$records = exterior_exone_proposal_order_records();
check( array_keys( $records ) === array( 33, 22, 11 ), 'Initial zero keeps newest date/ID order' );
$input = array( 'ids' => '11,22,33', 'version' => exterior_exone_proposal_version( $records ), 'nonce' => 'valid' );
foreach ( array( '', '11,11,33', '0,22,33', '-1,22,33', '01,22,33', '11, 22,33', array( '11' ), '99999999999,22,33' ) as $ids ) {
	$bad = $input; $bad['ids'] = $ids; check( is_wp_error( exterior_exone_proposal_order_save( $bad ) ), 'Invalid ID input' ); check( $wpdb->data === $original, 'Invalid input does not write' );
}
foreach ( array( '11,22', '11,22,99', '11,22,33,44' ) as $ids ) {
	$bad = $input; $bad['ids'] = $ids; check( exterior_exone_proposal_order_save( $bad )->get_error_code() === 'conflict', 'Exact full set required' ); check( $wpdb->data === $original, 'Set mismatch atomic' );
}
$bad = $input; $bad['version'] = str_repeat( '0', 64 ); check( exterior_exone_proposal_order_save( $bad )->get_error_code() === 'conflict', 'Stale version rejected' );
$allowed = false; check( exterior_exone_proposal_order_save( $input )->get_error_code() === 'permission', 'Capability checked' ); $allowed = true;
$_SERVER['REQUEST_METHOD'] = 'GET'; check( exterior_exone_proposal_order_save( $input )->get_error_code() === 'permission', 'POST only' ); $_SERVER['REQUEST_METHOD'] = 'POST';
$nonce = false; check( exterior_exone_proposal_order_save( $input )->get_error_code() === 'nonce', 'Nonce checked' ); $nonce = true;
$wpdb->fail_after = 2; check( is_wp_error( exterior_exone_proposal_order_save( $input ) ), 'Injected midway failure' ); check( $wpdb->data === $original, 'Partial update rolled back' ); $wpdb->fail_after = 0;
$result = exterior_exone_proposal_order_save( $input ); check( ! is_wp_error( $result ), 'Save succeeds' );
check( array_keys( exterior_exone_proposal_order_records() ) === array( 11, 22, 33 ), 'Saved order read back' );
foreach ( $wpdb->data as $id => $post ) { $compare = $post; $compare['menu_order'] = $original[ $id ]['menu_order']; check( $compare === $original[ $id ], 'Only menu_order changed' ); }
check( array_column( $wpdb->data, 'menu_order' ) === array( '-3', '-2', '-1' ), 'Negative ranks allow append' );
check( exterior_exone_proposal_order_save( $input )->get_error_code() === 'conflict', 'Old tab cannot overwrite' );
$new = $original[11]; $new['ID'] = '44'; $wpdb->data[44] = $new;
check( array_keys( exterior_exone_proposal_order_records() ) === array( 11, 22, 33, 44 ), 'New zero appends to saved order' );
$bad = $input; $bad['version'] = $result['version']; check( exterior_exone_proposal_order_save( $bad )->get_error_code() === 'conflict', 'Addition invalidates snapshot' );
check( $wpdb->reconnect_retries === 5, 'Reconnect setting restored' );
class OrderCache {
	public $fail = false; public $calls = 0;
	function getWpContentDir( $path ) { return '/nonexistent-exone-order-fixture' . $path; }
	function specificDeleteCache() {
		$this->calls++; $urls = call_user_func( $GLOBALS['cache_filter'], 'unrelated-original-option' );
		check( count( $urls ) === 1 && $urls[0]->url === 'https://fixture.invalid/works/(.*)', 'Cache selection scoped to works' );
		if ( $this->fail ) { throw new RuntimeException( 'cache failure fixture' ); }
	}
}
$wp_fastest_cache = new OrderCache();
exterior_exone_proposal_order_purge(); check( $wp_fastest_cache->calls === 1 && ! isset( $GLOBALS['cache_filter'] ), 'Scoped purge filter removed on success' );
$wp_fastest_cache->fail = true;
try { exterior_exone_proposal_order_purge(); check( false, 'Cache error must propagate' ); } catch ( RuntimeException $error ) { check( ! isset( $GLOBALS['cache_filter'] ), 'Scoped purge filter removed on exception' ); }
$records = exterior_exone_proposal_order_records(); $input['version'] = exterior_exone_proposal_version( $records ); $input['ids'] = '44,33,22,11';
check( exterior_exone_proposal_order_save( $input )->get_error_code() === 'cache', 'Cache failure not reported as complete success' );
check( array_keys( exterior_exone_proposal_order_records() ) === array( 44, 33, 22, 11 ), 'Cache failure preserves committed DB order' );
echo 'PASS ' . $checks . ' proposal order checks (' . ( EXR_COEXISTENCE ? 'coexistence' : 'local' ) . "); DB/network 0.\n";
