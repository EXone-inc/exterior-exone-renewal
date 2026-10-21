<?php
/** CLI-only boundary fixtures; no WordPress bootstrap, database, files or network writes. */
if ( PHP_SAPI !== 'cli' ) { exit; }
define( 'ABSPATH', __DIR__ . '/' );
define( 'EXR_COEXISTENCE', ! in_array( '--disabled', $argv, true ) );
$GLOBALS['hooks'] = array();
$GLOBALS['posts'] = array();
$GLOBALS['meta'] = array();
$GLOBALS['registered'] = array();
$GLOBALS['tests'] = 0;
function check( $ok, $name ) { if ( ! $ok ) { throw new RuntimeException( $name ); } ++$GLOBALS['tests']; }
function add_action( $hook, $callback, $priority = 10, $count = 1 ) { $GLOBALS['hooks'][] = array( $hook, $callback ); }
function add_filter( $hook, $callback, $priority = 10, $count = 1 ) { add_action( $hook, $callback, $priority, $count ); }
function remove_action( $hook, $callback ) {}
function apply_filters( $hook, $value, ...$args ) { return $value; }
function do_action( $hook, ...$args ) {}
function update_option() { throw new RuntimeException( 'Unexpected option write' ); }
function update_post_meta() { throw new RuntimeException( 'Unexpected metadata write' ); }
function wp_insert_post() { throw new RuntimeException( 'Unexpected post insert' ); }
function flush_rewrite_rules() { throw new RuntimeException( 'Unexpected rewrite write' ); }
function current_time( $type ) { return '2026-10-16 01:00:00'; }
function is_admin() { return false; }
function is_user_logged_in() { return ! empty( $GLOBALS['fixture_authenticated'] ); }
function current_user_can( $capability, $id = 0 ) { return ! empty( $GLOBALS['fixture_capabilities'][$id][$capability] ); }
function is_front_page() { return false; }
function home_url( $path ) { return 'http://127.0.0.1:19274' . $path; }
function get_post_type_archive_link( $type ) { return 'local:' . $type; }
function get_post( $id ) { return $id instanceof WP_Post ? $id : ( $GLOBALS['posts'][ (int) $id ] ?? null ); }
function get_post_type( $post ) { $post = get_post( $post ); return $post ? $post->post_type : false; }
function get_post_meta( $id, $key, $single = false ) { return $GLOBALS['meta'][$id][$key] ?? ''; }
function sanitize_title( $slug ) { return rawurlencode( strtolower( rawurldecode( $slug ) ) ); }
function register_post_status( $status, $args ) { $GLOBALS['registered']['status'][$status] = $args; }
function register_post_type( $type, $args ) { $GLOBALS['registered']['type'][$type] = $args; }
function register_taxonomy( $type, $post, $args ) { $GLOBALS['registered']['tax'][$type] = $args; }
function get_post_type_object( $type ) { return (object) array( 'name' => $type, 'label' => $type ); }
function status_header( $code ) { $GLOBALS['status_code'] = $code; }
function nocache_headers() {}
function is_404() { return ( $GLOBALS['status_code'] ?? 200 ) === 404; }
function rest_ensure_response( $value ) { return $value; }
function wpcf7_sanitize_unit_tag( $value ) { return preg_replace( '/[^a-z0-9-]/', '', (string) $value ); }
function wpcf7_is_name( $value ) { return (bool) preg_match( '/^[a-z][a-z0-9_.-]*$/', $value ); }
function wpcf7_apply_filters_deprecated( $hook, $values, $version, $new ) { return $values[0]; }
function wpcf7_get_contact_form_by_title() { throw new RuntimeException( 'Unexpected title fallback' ); }
function wpcf7_switch_locale( $locale, $callback, ...$args ) { return call_user_func_array( $callback, $args ); }
function get_taxonomies() { return array( 'category', 'works_category', 'exr_works_package', 'column_category' ); }
function match_meta( $id, $conditions ) {
	foreach ( $conditions as $key => $condition ) {
		if ( $key === 'relation' ) { continue; }
		if ( isset( $condition['key'] ) ) {
			$value = get_post_meta( $id, $condition['key'], true );
			if ( ( $condition['compare'] ?? '' ) === 'EXISTS' ? $value === '' : $value !== $condition['value'] ) { return false; }
		} elseif ( ! match_meta( $id, $condition ) ) { return false; }
	}
	return true;
}
function get_posts( $args ) {
	$GLOBALS['fixture_query_count'] = ( $GLOBALS['fixture_query_count'] ?? 0 ) + 1;
	$result = array();
	foreach ( $GLOBALS['posts'] as $post ) {
		if ( $post->post_type !== $args['post_type'] || $post->post_status !== ( $args['post_status'] ?? 'publish' ) ) { continue; }
		if ( ! empty( $args['date_query'] ) && $post->post_date > current_time( 'mysql' ) ) { continue; }
		if ( isset( $args['name'] ) && $post->post_name !== $args['name'] ) { continue; }
		if ( isset( $args['meta_query'] ) && ! match_meta( $post->ID, $args['meta_query'] ) ) { continue; }
		$result[] = $post;
	}
	return ( $args['posts_per_page'] ?? -1 ) < 0 ? $result : array_slice( $result, 0, $args['posts_per_page'] );
}
class WP_Post {
	public $ID, $post_type, $post_status, $post_date, $post_name;
	public function __construct( $id, $type, $slug, $status = 'exr_stored' ) { $this->ID = $id; $this->post_type = $type; $this->post_name = $slug; $this->post_status = $status; $this->post_date = '2026-09-01 00:00:00'; }
}
class WP_Error { public $code; public function __construct( $code, $message, $data ) { $this->code = $code; } }
class WPCF7_ContactForm {
	const post_type = 'wpcf7_contact_form';
	private $post;
	public static function get_instance( $post ) { $post = get_post( $post ); if ( ! $post || $post->post_type !== self::post_type ) { return null; } $form = new self; $form->post = $post; return $form; }
	public function id() { return $this->post->ID; }
	public function locale() { return 'ja'; }
	public function form_html( $args ) { return '<form class="' . $args['html_class'] . '"></form>'; }
	public function get_schema() { return new class { public function to_array() { return array( 'rules' => array() ); } }; }
	public function submit() { return array( 'status' => 'validation_failed', 'invalid_fields' => array( 'your.name' => array( 'reason' => 'required', 'idref' => 'your-name' ) ) ); }
}
class FixtureQuery {
	public $query_vars, $is_home = false, $is_page = false, $is_single = false, $is_singular = false, $is_archive = false, $is_post_type_archive = false, $is_attachment = false, $is_404 = false, $is_paged = false, $posts = array(), $max_num_pages = 0;
	public function __construct( $vars = array() ) { $this->query_vars = $vars; }
	public function get( $key ) { return $this->query_vars[$key] ?? ''; }
	public function set( $key, $value ) { $this->query_vars[$key] = $value; }
	public function is_main_query() { return true; }
	public function is_search() { return isset( $this->query_vars['s'] ); }
	public function is_feed() { return ! empty( $this->query_vars['feed'] ); }
	public function is_tax( $taxonomy ) { return false; }
	public function set_404() { $this->is_404 = true; }
}
class FixtureRequest {
	private $route, $params, $header, $method;
	public function __construct( $route, $params = array(), $header = 'multipart/form-data; boundary=test', $method = 'GET' ) { $this->route = $route; $this->params = $params; $this->header = $header; $this->method = $method; }
	public function get_route() { return $this->route; }
	public function get_param( $key ) { return $this->params[$key] ?? ''; }
	public function get_header( $key ) { return $this->header; }
	public function get_method() { return $this->method; }
}
function seed( $id, $type, $slug, $meta = array(), $status = 'exr_stored' ) {
	$GLOBALS['posts'][$id] = new WP_Post( $id, $type, $slug, $status );
	$GLOBALS['meta'][$id] = $meta + array( '_exr_dataset' => 'exterior-exone-renewal:v1', '_exr_source_status' => 'publish', '_exr_import_state' => 'complete', '_exr_source_key' => 'fixture:' . $id );
}
function route( $path, $vars = array(), $get = array(), $post = array() ) {
	$_GET = $get;
	$_POST = $post;
	$GLOBALS['wp'] = (object) array( 'request' => $path, 'query_vars' => $vars );
	exterior_exone_coexistence_parse_request( $GLOBALS['wp'] );
	return exterior_exone_coexistence_route();
}
$theme = dirname( __DIR__, 3 );
require $theme . '/inc/coexistence.php';
if ( ! EXR_COEXISTENCE ) {
	check( count( $GLOBALS['hooks'] ) === 0, 'Disabled feature registers no hooks' );
	$args = array( 'post_type' => 'works', 'post_status' => 'publish' );
	check( exterior_exone_coexistence_query_args( $args ) === $args, 'Local query remains unchanged' );
	check( exterior_exone_content_archive_url( 'works' ) === 'local:works', 'Local archive remains unchanged' );
	check( exterior_exone_content_taxonomy( 'works_package' ) === 'works_package', 'Local taxonomy remains unchanged' );
	echo 'PASS ' . $GLOBALS['tests'] . " disabled-mode boundaries\n"; exit;
}
exterior_exone_coexistence_register_storage();
foreach ( $GLOBALS['registered']['type'] as $type => $args ) { check( ! $args['public'] && ! $args['show_in_rest'] && ! $args['rewrite'] && ! $args['query_var'], 'Storage is never a public route: ' . $type ); }
check( ! $GLOBALS['registered']['status']['exr_stored']['public'], 'Storage status is private' );
seed( 1, 'works', 'shared-slug', array(), 'publish' );
seed( 101, 'exr_works', 'shared-slug' );
seed( 102, 'exr_works', 'draft', array( '_exr_source_status' => 'draft' ) );
seed( 103, 'exr_works', 'partial', array( '_exr_import_state' => 'staging' ) );
seed( 104, 'exr_works', 'other', array( '_exr_dataset' => 'other' ) );
seed( 105, 'exr_works', 'future' ); $GLOBALS['posts'][105]->post_date = '2099-01-01 00:00:00';
seed( 106, 'exr_works', 'badstatus', array(), 'publish' );
seed( 201, 'exr_page', 'thanks', array( '_exr_source_path' => '/contact/thanks/' ) );
seed( 202, 'exr_page', 'thanks', array( '_exr_source_path' => '/recruit/thanks/' ) );
seed( 203, 'exr_page', 'aomori', array( '_exr_source_path' => '/store/aomori/' ) );
seed( 204, 'exr_page', 'sample-page', array( '_exr_source_path' => '/sample-page/', '_exr_source_status' => 'draft' ) );
seed( 205, 'exr_page', 'company', array( '_exr_source_path' => '/company/' ) );
seed( 301, 'exr_news', 'local-news' );
seed( 401, 'exr_form', 'contact', array( '_exr_form_role' => 'personal' ) );
seed( 402, 'exr_form', 'entry', array( '_exr_form_role' => 'entry' ) );
seed( 40, 'wpcf7_contact_form', 'contact', array( '_exr_form_role' => 'personal' ), 'publish' );
seed( 501, 'attachment', 'image', array( '_exr_source_status' => 'inherit' ), 'private' );
seed( 601, 'column', 'shared-column', array(), 'publish' );
check( array_map( function( $p ) { return $p->ID; }, exterior_exone_content_posts( array( 'post_type' => 'works' ) ) ) === array( 101 ), 'No old, draft, incomplete, foreign, future or wrong-status records' );
foreach ( array( 1, 102, 103, 104, 105, 106 ) as $id ) { check( ! exterior_exone_coexistence_is_public_record( $id ), 'Direct record validation rejects ' . $id ); }
check( exterior_exone_coexistence_page( '/contact/thanks/' )->ID === 201 && exterior_exone_coexistence_page( '/recruit/thanks/' )->ID === 202, 'Same slug, different parents' );
check( exterior_exone_coexistence_page( '/sample-page/' ) === null, 'Draft page cannot resolve' );
seed( 206, 'exr_page', 'company-copy', array( '_exr_source_path' => '/company/' ) );
check( exterior_exone_coexistence_page( '/company/' ) === null, 'Ambiguous full path fails closed' );
unset( $GLOBALS['posts'][206] );
check( route( 'works/shared-slug' )['id'] === 101, 'New work wins matching slug without reusing old record' );
check( route( 'works/draft' )['kind'] === 'missing', 'Draft work URL rejected' );
check( route( 'works/page/2' )['kind'] === 'missing', 'Unsupported works pagination rejected' );
check( route( 'news/page/3' )['paged'] === 3, 'News pagination is path-scoped' );
check( route( 'news/local-news' )['kind'] === 'news-redirect', 'Only existing new news redirects to list' );
check( route( 'news/unknown' )['kind'] === 'missing', 'Unknown news is not blanket redirected' );
check( route( 'store/store-aomori' )['id'] === 203, 'Production branch path maps to local source page' );
check( route( 'store/aomori' )['canonical'] === '/store/store-aomori/', 'Local branch alias has production canonical' );
check( route( '', array( 'p' => 101 ), array( 'p' => 101 ) )['kind'] === 'missing', 'Direct imported ID cannot bypass route' );
check( route( 'column/example', array( 'p' => 1 ), array( 'p' => 1 ) )['kind'] === 'missing', 'Column-looking path cannot expose old work ID' );
check( route( '', array( 'p' => 1, 's' => 'query' ), array( 'p' => 1, 's' => 'query' ) )['kind'] === 'missing', 'Search parameter cannot bypass old ID boundary' );
check( route( 'company', array( 'post_type' => 'exr_page' ), array( 'post_type' => 'exr_page' ) )['kind'] === 'missing', 'Storage post type cannot be requested directly' );
check( route( '', array( 'p' => 601 ), array( 'p' => 601 ) ) === null, 'Shared column ID delegated unchanged' );
foreach ( array( 'page_id' => 999, 'attachment_id' => 501, 'preview' => 'true', 'pagename' => 'company', 'attachment' => 'image', 'name' => 'old' ) as $selector => $value ) {
	$vars = array( 'p' => 601, $selector => $value, 'post_type' => 'page' );
	check( route( '', $vars, $vars )['kind'] === 'missing', 'Shared column ID cannot mask conflicting selector ' . $selector );
}
check( route( '', array( 'p' => 601, 'post_type' => 'column' ), array( 'p' => 601, 'post_type' => 'column' ) ) === null, 'Unambiguous shared column type and ID remain valid' );
check( route( '', array( 'p' => 601, 'page_id' => 999 ), array( 'p' => 601 ) )['kind'] === 'missing', 'Rewrite-generated page selector cannot override shared column ID' );
foreach ( array( 'sitemap', 'sitemap-stylesheet', 'xml_sitemap', 'robots', 'rest_route' ) as $service ) {
	$vars = array( $service => 'unknown', 'page_id' => 999 );
	check( route( '', $vars, $vars )['kind'] === 'missing', 'Service flag cannot mask direct old page selector ' . $service );
}
foreach ( array( 'page_id' => 999, 'attachment_id' => 501, 'pagename' => 'company', 'name' => 'old', 'attachment' => 'image', 'subpost_id' => 501 ) as $selector => $value ) {
	$vars = array( $selector => $value, 'post_type' => 'page' );
	check( route( 'column', $vars, array(), $vars )['kind'] === 'missing', 'POST selector cannot bypass boundary on a column path: ' . $selector );
	$vars = array( 'sitemap' => 'unknown', $selector => $value );
	check( route( '', $vars, $vars )['kind'] === 'missing', 'Service provider rejects extra GET selector: ' . $selector );
	check( route( '', $vars )['kind'] === 'missing', 'Service provider rejects conflicting resolved selector: ' . $selector );
}
check( route( 'column/page/2' ) === null, 'Column routing delegated unchanged' );
check( route( 'sitemap.xml', array( 'xml_sitemap' => 'main' ) ) === null, 'Plugin sitemap query preserved' );
foreach ( array( '../secret', 'store/%2e%2e/company', 'foo//bar', 'foo%00bar', 'foo%5cbar', 'foo?bar', '%252e%252e' ) as $path ) { check( false === exterior_exone_coexistence_normalize_path( $path ), 'Reject ambiguous path ' . $path ); }
route( 'contact/thanks' );
$query = new FixtureQuery( $GLOBALS['wp']->query_vars ); exterior_exone_coexistence_main_query( $query );
check( $query->is_page && $query->is_singular && ! $query->is_archive && $query->get( 'p' ) === 201, 'Page flags and destination ID use dedicated source' );
$display = exterior_exone_coexistence_display_posts( array( $GLOBALS['posts'][201] ), $query );
check( $display[0]->post_type === 'page' && $display[0]->post_status === 'publish' && $GLOBALS['posts'][201]->post_type === 'exr_page' && $GLOBALS['posts'][201]->post_status === 'exr_stored', 'Projection never mutates cached storage objects' );
route( 'news/page/3' ); $query = new FixtureQuery( $GLOBALS['wp']->query_vars ); exterior_exone_coexistence_main_query( $query ); $query->max_num_pages = 1;
exterior_exone_coexistence_handle_404( false, $query ); check( $query->is_404 && $GLOBALS['status_code'] === 404, 'Out-of-range news pages are 404' );
route( '', array( 's' => 'target' ) ); $query = new FixtureQuery( array( 's' => 'target', 'post_type' => 'any', 'post_status' => 'any' ) ); exterior_exone_coexistence_main_query( $query );
check( $query->get( 'post_type' ) === array( 'column', 'exr_page', 'exr_works', 'exr_news' ) && $query->get( '_exr_search' ), 'Search excludes old content and requires boundary WHERE' );
check( WPCF7_ContactForm::get_instance( 401 ) === null, 'Stored form is rejected by native CF7 type gate' );
check( exterior_exone_coexistence_form( 'personal' )->id() === 401 && $GLOBALS['posts'][401]->post_type === 'exr_form', 'Dedicated selector projects only a copy' );
check( exterior_exone_coexistence_form( 'entry' )->id() === 402 && exterior_exone_coexistence_form( 'corporate' ) === null, 'Missing role never falls back to old form title' );
seed( 403, 'exr_form', 'duplicate', array( '_exr_form_role' => 'personal' ) ); check( exterior_exone_coexistence_form( 'personal' ) === null && exterior_exone_coexistence_form_instance( 401 ) === null, 'Duplicate form role fails closed for display and direct dispatch' ); unset( $GLOBALS['posts'][403] );
check( exterior_exone_coexistence_form_dispatch( null, new FixtureRequest( '/contact-form-7/v1/contact-forms/40/feedback' ) ) === null, 'Existing CF7 request is untouched' );
check( exterior_exone_coexistence_form_dispatch( null, new FixtureRequest( '/contact-form-7/v1/contact-forms/401/feedback', array(), 'application/json' ) )->code === 'wpcf7_unsupported_media_type', 'Invalid submission format rejected' );
check( exterior_exone_coexistence_form_dispatch( null, new FixtureRequest( '/contact-form-7/v1/contact-forms/401/feedback', array( '_wpcf7' => 40, '_wpcf7_unit_tag' => 'wpcf7-f401-o1' ) ) )->code === 'wpcf7_unit_tag_not_found', 'Cross-form payload rejected' );
$reply = exterior_exone_coexistence_form_dispatch( null, new FixtureRequest( '/contact-form-7/v1/contact-forms/401/feedback', array( '_wpcf7' => 401, '_wpcf7_unit_tag' => 'wpcf7-f401-o1' ) ) );
check( $reply['invalid_fields'][0]['field'] === 'your_name' && $reply['invalid_fields'][0]['error_id'] === 'wpcf7-f401-o1-ve-your_name', 'Validation response retains CF7 client contract' );
check( exterior_exone_coexistence_form_dispatch( null, new FixtureRequest( '/contact-form-7/v1/contact-forms/401/feedback/schema' ) ) === array( 'rules' => array() ), 'Schema uses only eligible new form' );
check( exterior_exone_coexistence_form_shortcode( false, 'contact-form-7', array( 'id' => '401' ) ) === '<form class=""></form>', 'Imported numeric form shortcode resolves without hash or title lookup' );
check( exterior_exone_coexistence_rest_boundary( null, null, new FixtureRequest( '/wp/v2/media/501' ) ) instanceof WP_Error, 'New attachment direct REST blocked' );
$GLOBALS['fixture_authenticated'] = true;
check( exterior_exone_coexistence_rest_boundary( null, null, new FixtureRequest( '/wp/v2/media/501' ) ) instanceof WP_Error, 'Authentication alone does not grant private attachment access' );
$GLOBALS['fixture_capabilities'][501]['read_post'] = true;
foreach ( array( 'GET', 'HEAD' ) as $method ) {
	check( exterior_exone_coexistence_rest_boundary( null, null, new FixtureRequest( '/wp/v2/media/501', array(), '', $method ) ) === null, 'Authorized attachment read passes to core permissions: ' . $method );
}
check( exterior_exone_coexistence_rest_boundary( null, null, new FixtureRequest( '/wp/v2/media/501', array(), '', 'POST' ) ) instanceof WP_Error, 'Read permission does not grant attachment editing' );
$GLOBALS['fixture_capabilities'][501]['edit_post'] = true;
foreach ( array( 'POST', 'PUT', 'PATCH', 'DELETE' ) as $method ) {
	check( exterior_exone_coexistence_rest_boundary( null, null, new FixtureRequest( '/wp/v2/media/501', array(), '', $method ) ) === null, 'Authorized attachment edit delegates remaining core permissions: ' . $method );
}
check( exterior_exone_coexistence_rest_boundary( null, null, new FixtureRequest( '/wp/v2/exr_page/201' ) ) instanceof WP_Error, 'Attachment permission cannot expose stored content endpoint' );
$GLOBALS['fixture_authenticated'] = false;
check( exterior_exone_coexistence_rest_boundary( null, null, new FixtureRequest( '/wp/v2/media/501' ) ) instanceof WP_Error, 'Anonymous request cannot reuse a prior capability decision' );
$GLOBALS['fixture_capabilities'] = array();
check( exterior_exone_coexistence_rest_boundary( null, null, new FixtureRequest( '/wp/v2/media/999' ) ) === null, 'Unrelated old media REST untouched' );
$locations = array_column( exterior_exone_coexistence_sitemap_entries(), 'loc' );
check( ! in_array( home_url( '/contact/thanks/' ), $locations, true ) && ! in_array( home_url( '/sample-page/' ), $locations, true ), 'Sitemap excludes thanks and drafts' );
check( in_array( home_url( '/store/store-aomori/' ), $locations, true ) && in_array( home_url( '/works/shared-slug/' ), $locations, true ), 'Sitemap uses public paths' );
check( exterior_exone_coexistence_xml_post_url( 'old', $GLOBALS['posts'][1] ) === '' && exterior_exone_coexistence_xml_post_url( 'shared', $GLOBALS['posts'][601] ) === 'shared', 'XML sitemap has no old works URL' );
check( exterior_exone_coexistence_sitemap_types( array( 'page' => true, 'works' => true, 'column' => true, 'exr_page' => true ) ) === array( 'column' => true ), 'Standard sitemap keeps shared column provider only' );
require $theme . '/inc/post-types.php'; require $theme . '/inc/contact-forms.php'; require $theme . '/inc/recruit-form.php';
exterior_exone_flush_rewrite_rules(); check( true, 'Coexistence activation has no custom rewrite write' );
check( is_string( exterior_exone_contact_install_forms( true ) ) && is_string( exterior_exone_recruit_install_form( true ) ), 'Existing installers cannot modify forms in coexistence mode' );
check( exterior_exone_contact_get_form( 'personal' )->id() === 401 && exterior_exone_recruit_get_form()->id() === 402, 'Template selectors use new forms only' );
echo 'PASS ' . $GLOBALS['tests'] . " enabled-mode boundaries (no DB/bootstrap/network)\n";
