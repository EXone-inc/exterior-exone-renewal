<?php
/** Read-only admin inventory boundaries; no bootstrap, DB, files or network writes. */
if ( PHP_SAPI !== 'cli' ) { exit; }
if ( in_array( '--disabled', $argv, true ) ) {
	define( 'ABSPATH', __DIR__ . '/' ); define( 'EXR_COEXISTENCE', false );
	$GLOBALS['admin_hooks'] = array();
	function add_action( $hook, $callback ) { $GLOBALS['admin_hooks'][] = $hook; }
	function current_user_can() { throw new RuntimeException( 'Disabled screen checked user permissions' ); }
	require dirname( __DIR__, 3 ) . '/inc/coexistence-admin.php';
	if ( $GLOBALS['admin_hooks'] || exterior_exone_coexistence_admin_allowed() || exterior_exone_coexistence_admin_records() ) { throw new RuntimeException( 'Disabled admin screen changed behavior' ); }
	echo "PASS disabled admin has zero hooks, queries and permission calls.\n"; exit;
}
require __DIR__ . '/run.php';
$beforeChecks = $GLOBALS['tests'];
class ReviewAdminDenied extends RuntimeException { public $status; public function __construct( $status ) { $this->status = $status; } }
function wp_die( $message, $title, $args ) { throw new ReviewAdminDenied( $args['response'] ); }
function add_theme_page( $title, $menu, $capability, $slug, $callback ) { $GLOBALS['review_admin_menu'] = compact( 'title', 'menu', 'capability', 'slug', 'callback' ); }
function add_query_arg( $args, $url ) { return $url . '&' . http_build_query( $args ); }
function admin_url( $path ) { return 'http://127.0.0.1:19274/retest/wp-admin/' . $path; }
function esc_html( $value ) { return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' ); }
function esc_url( $value ) { return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ); }
$beforeHooks = count( $GLOBALS['hooks'] );
require dirname( __DIR__, 3 ) . '/inc/coexistence-admin.php';
check( count( $GLOBALS['hooks'] ) === $beforeHooks + 11 && end( $GLOBALS['hooks'] )[0] === 'admin_menu', 'Read-only inventory menu, isolated Slack and news hooks are registered' );
$beforeQueries = $GLOBALS['fixture_query_count'];
check( exterior_exone_coexistence_admin_records() === array(), 'Unauthorized callers receive no inventory' );
exterior_exone_coexistence_admin_menu();
check( empty( $GLOBALS['review_admin_menu'] ), 'Unauthorized user receives no menu' );
try { exterior_exone_coexistence_admin_render(); throw new RuntimeException( 'Unauthorized rendering did not fail' ); }
catch ( ReviewAdminDenied $error ) { check( $error->status === 403, 'Direct page callback rejects unauthorized access' ); }
check( $beforeQueries === $GLOBALS['fixture_query_count'], 'Rejected authorization performs no content queries' );
$GLOBALS['fixture_capabilities'][0]['manage_options'] = true;
$GLOBALS['fixture_capabilities'][501]['read_post'] = true;
foreach ( $GLOBALS['posts'] as $id => $post ) { $post->post_title = '確認用タイトル ' . $id; $post->post_content = 'PRIVATE-BODY-MUST-NOT-RENDER'; }
$GLOBALS['posts'][101]->post_title = '<script>injected-title</script>';
$GLOBALS['meta'][401]['_mail'] = 'PRIVATE-MAIL-MUST-NOT-RENDER';
exterior_exone_coexistence_admin_menu();
check( $GLOBALS['review_admin_menu']['capability'] === 'manage_options' && $GLOBALS['review_admin_menu']['menu'] === '新テーマ用コンテンツ', 'Appearance page requires administrator capability and Japanese label' );
$records = exterior_exone_coexistence_admin_records();
$ids = array_map( static function ( $post ) { return $post->ID; }, $records ); sort( $ids );
check( $ids === array( 101, 102, 105, 201, 202, 203, 204, 205, 301, 401, 402, 501 ), 'Inventory includes draft/future new items and excludes legacy, partial, foreign and wrong-status items' );
check( exterior_exone_coexistence_admin_link( $GLOBALS['posts'][204] ) === array(), 'Draft remains visible without public link' );
check( exterior_exone_coexistence_admin_link( $GLOBALS['posts'][105] ) === array(), 'Future-dated item has no current public link' );
check( exterior_exone_coexistence_admin_link( $GLOBALS['posts'][203] )['url'] === home_url( '/store/store-aomori/' ), 'Branch link uses production-compatible route' );
check( exterior_exone_coexistence_admin_link( $GLOBALS['posts'][202] )['url'] === home_url( '/recruit/thanks/' ), 'Full parent path retained in page link' );
check( exterior_exone_coexistence_admin_link( $GLOBALS['posts'][402] )['url'] === home_url( '/recruit/entry/' ), 'Entry form points to its display page without revealing configuration' );
check( exterior_exone_coexistence_admin_link( $GLOBALS['posts'][501] )['url'] === admin_url( 'upload.php?item=501' ), 'Image points to standard authorized media library' );
$GLOBALS['fixture_capabilities'][501]['read_post'] = false;
check( exterior_exone_coexistence_admin_link( $GLOBALS['posts'][501] ) === array(), 'Image link still requires per-record read permission' );
$GLOBALS['fixture_capabilities'][501]['read_post'] = true;
$GLOBALS['meta'][205]['_exr_source_path'] = 'https://outside.invalid/';
check( exterior_exone_coexistence_admin_link( $GLOBALS['posts'][205] ) === array(), 'Invalid page path cannot create an external confirmation link' );
$GLOBALS['meta'][205]['_exr_source_path'] = '/company/';
$GLOBALS['meta'][204]['_exr_source_status'] = 'UNKNOWN-INTERNAL-STATUS';
check( exterior_exone_coexistence_admin_status( $GLOBALS['posts'][204] ) === '確認が必要', 'Unknown source state is not printed as an internal term' );
$GLOBALS['meta'][204]['_exr_source_status'] = array( 'malformed' );
check( exterior_exone_coexistence_admin_status( $GLOBALS['posts'][204] ) === '確認が必要', 'Malformed metadata cannot trigger unsafe status rendering' );
$GLOBALS['meta'][204]['_exr_source_status'] = 'UNKNOWN-INTERNAL-STATUS';
$sourceKey = $GLOBALS['meta'][205]['_exr_source_key']; $GLOBALS['meta'][205]['_exr_source_key'] = array( 'malformed' );
check( ! exterior_exone_coexistence_admin_is_record( $GLOBALS['posts'][205] ), 'Non-string source identity is excluded' );
$GLOBALS['meta'][205]['_exr_source_key'] = $sourceKey;
ob_start(); exterior_exone_coexistence_admin_render(); $html = ob_get_clean();
check( strpos( $html, '&lt;script&gt;injected-title&lt;/script&gt;' ) !== false && strpos( $html, '<script>' ) === false, 'Imported titles are HTML escaped' );
check( strpos( $html, '<form' ) === false && strpos( $html, '<button' ) === false && strpos( $html, 'action=edit' ) === false, 'Inventory has no write form, save button or content edit action' );
foreach ( array( 'PRIVATE-BODY', 'PRIVATE-MAIL', 'UNKNOWN-INTERNAL-STATUS', 'exr_stored', '_exr_', 'fixture:', 'exr_page' ) as $privateTerm ) {
	check( strpos( $html, $privateTerm ) === false, 'UI omits private values/internal identifiers: ' . $privateTerm );
}
foreach ( array( '固定ページ', '施工事例', 'お知らせ', 'フォーム', '画像', '元の公開状態', 'この画面では内容の編集・保存はできません', '一般公開の準備が完了したことを示すものではありません' ) as $label ) {
	check( strpos( $html, $label ) !== false, 'Inventory explains purpose with Japanese labels: ' . $label );
}
$GLOBALS['fixture_capabilities'] = array();
check( exterior_exone_coexistence_admin_records() === array(), 'Inventory authorization is reevaluated after user loses permission' );
echo 'PASS ' . ( $GLOBALS['tests'] - $beforeChecks ) . " read-only admin checks; no DB/bootstrap/network writes.\n";
