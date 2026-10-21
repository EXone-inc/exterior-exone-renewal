<?php
/** PHP 7.4: synthetic WordPress API double; no database, HTTP or mail. */
if ( PHP_SAPI !== 'cli' ) { exit; }
define( 'ABSPATH', __DIR__ . '/' );
define( 'EXR_COEXISTENCE', ! in_array( '--disabled', $argv, true ) );
$GLOBALS['slack_checks'] = 0; $GLOBALS['slack_hooks'] = array(); $GLOBALS['slack_writes'] = array(); $GLOBALS['slack_allowed'] = true;
class WP_Error { private $message; public function __construct( $code, $message ) { $this->message = $message; } public function get_error_message() { return $this->message; } }
class WP_Post { public $ID, $post_type = 'exr_form', $post_status = 'exr_stored', $post_date = '2026-10-15 00:00:00'; public function __construct( $id ) { $this->ID = $id; } }
class SlackDenied extends RuntimeException { public $status; public function __construct( $status ) { $this->status = $status; } }
class CFTZ_Module_CF7 {}
class SlackForm { private $id; public function __construct( $id ) { $this->id = $id; } public function id() { return $this->id; } }
class WPCF7_Submission { public static $instance; public $data = array(); public static function get_instance() { return self::$instance; } public function get_posted_data() { return $this->data; } }
function slack_check( $ok, $label ) { if ( ! $ok ) { throw new RuntimeException( $label ); } $GLOBALS['slack_checks']++; }
function is_wp_error( $v ) { return $v instanceof WP_Error; }
function add_action( $hook, $callback, $priority = 10, $count = 1 ) { $GLOBALS['slack_hooks'][] = array( $hook, $callback, $priority, $count ); }
function add_filter( $hook, $callback, $priority = 10, $count = 1 ) { add_action( $hook, $callback, $priority, $count ); }
function current_user_can() { return $GLOBALS['slack_allowed']; }
function current_time() { return '2026-10-19 00:00:00'; }
function get_post_stati() { return array( 'exr_stored', 'publish', 'draft', 'private', 'trash' ); }
function get_posts( $args ) { return array_values( array_filter( $GLOBALS['slack_posts'], function ( $p ) { return 'exr_form' === $p->post_type && in_array( 'exterior-exone-renewal:v1', get_post_meta( $p->ID, '_exr_dataset', false ), true ); } ) ); }
function get_post_type( $id ) { return isset( $GLOBALS['slack_posts'][$id] ) ? $GLOBALS['slack_posts'][$id]->post_type : false; }
function get_post_meta( $id, $key, $single = false ) { $v = $GLOBALS['slack_meta'][$id][$key] ?? array(); return $single ? ( $v[0] ?? '' ) : $v; }
function wp_slash( $v ) { return is_array( $v ) ? array_map( 'wp_slash', $v ) : ( is_string( $v ) ? addslashes( $v ) : $v ); }
function wp_unslash( $v ) { return is_array( $v ) ? array_map( 'wp_unslash', $v ) : ( is_string( $v ) ? stripslashes( $v ) : $v ); }
function slack_write( $id, $key, $value, $previous, $add = false ) {
	if ( '_ctz_zapier' !== $key || ! in_array( $id, array( 301, 302, 303 ), true ) ) { throw new RuntimeException( 'Out-of-scope write' ); }
	$GLOBALS['slack_writes'][] = $id; $value = wp_unslash( $value );
	if ( isset( $GLOBALS['slack_fail_id'] ) && $id === $GLOBALS['slack_fail_id'] ) { return false; }
	$old = get_post_meta( $id, $key, false );
	if ( $add ? ! empty( $old ) : $old !== array( $previous ) ) { return false; }
	$GLOBALS['slack_meta'][$id][$key] = array( $value );
	if ( isset( $GLOBALS['slack_after_write'] ) ) { $GLOBALS['slack_after_write']( $id ); }
	return 1;
}
function update_post_meta( $id, $key, $v, $prev ) { return slack_write( $id, $key, $v, $prev ); }
function add_post_meta( $id, $key, $v, $unique ) { return slack_write( $id, $key, $v, null, true ); }
function delete_post_meta( $id, $key, $v ) { if ( get_post_meta( $id, $key, false ) !== array( $v ) ) { return false; } unset( $GLOBALS['slack_meta'][$id][$key] ); return true; }
function wp_check_invalid_utf8( $v, $strip = false ) { return preg_match( '//u', $v ) ? $v : ''; }
function wp_json_encode( $v ) { return json_encode( $v, JSON_UNESCAPED_UNICODE ); }
function exterior_exone_contact_store_label( $v ) { return 'aomori' === $v ? '青森店' : '支店未選択'; }
function esc_html( $v ) { return htmlspecialchars( $v, ENT_QUOTES, 'UTF-8' ); }
function esc_attr( $v ) { return esc_html( $v ); }
function esc_url( $v ) { return esc_html( $v ); }
function admin_url( $path ) { return 'http://127.0.0.1/wp-admin/' . $path; }
function wp_nonce_field() { echo '<input type="hidden" name="_wpnonce" value="fixture">'; }
function checked( $a, $b, $echo ) { return $a === $b ? ' checked="checked"' : ''; }
function submit_button( $label ) { echo '<button>' . esc_html( $label ) . '</button>'; }
function add_theme_page( ...$args ) { $GLOBALS['slack_menu'] = $args; }
function wp_die( $message, $title, $args ) { throw new SlackDenied( $args['response'] ); }
function check_admin_referer() { throw new SlackDenied( 403 ); }
function wp_remote_request() { throw new RuntimeException( 'Forbidden HTTP' ); }
function wp_mail() { throw new RuntimeException( 'Forbidden mail' ); }
require dirname( __DIR__, 3 ) . '/inc/coexistence-slack.php';
slack_check( PHP_VERSION === '7.4.33', 'Actual PHP 7.4.33' );
if ( ! EXR_COEXISTENCE ) {
	slack_check( ! $GLOBALS['slack_hooks'] && is_wp_error( exterior_exone_slack_save( array() ) ), 'Disabled theme has no hooks or writes' );
	$args = array( 'legacy' => true ); $form = new SlackForm( 1 );
	slack_check( exterior_exone_slack_request_args( $args, array(), $form, 'https://example.invalid/' ) === $args && exterior_exone_slack_form_properties( $args, $form ) === $args, 'Disabled runtime passes through' );
	echo 'PASS disabled Slack checks: ' . $GLOBALS['slack_checks'] . "\n"; exit;
}
foreach ( array( 301 => array( 'personal', 79 ), 302 => array( 'corporate', 80 ), 303 => array( 'entry', 107 ) ) as $id => $source ) {
	$GLOBALS['slack_posts'][$id] = new WP_Post( $id );
	$GLOBALS['slack_meta'][$id] = array( '_exr_dataset' => array( 'exterior-exone-renewal:v1' ), '_exr_source_status' => array( 'publish' ), '_exr_import_state' => array( 'complete' ), '_exr_form_role' => array( $source[0] ), '_exr_source_key' => array( 'exterior-exone-renewal:v1:post:wpcf7_contact_form:' . $source[1] ), '_mail' => array( 'UNCHANGED' ), '_mail_2' => array( 'UNCHANGED' ) );
}
$GLOBALS['slack_posts'][1] = new WP_Post( 1 ); $GLOBALS['slack_posts'][1]->post_type = 'wpcf7_contact_form'; $GLOBALS['slack_meta'][1] = array( '_ctz_zapier' => array( array( 'legacy' => true ) ) );
$base = $GLOBALS['slack_meta'];
$contact = 'https://hooks.slack.com/services/T000TEST/B000TEST/FAKECONTACTTOKEN';
$entry = 'https://hooks.slack.com/services/T000TEST/B000TEST/FAKEENTRYTOKEN';
$input = array( 'contact_url' => $contact, 'entry_url' => $entry, 'contact_active' => '1', 'entry_active' => '1' );
slack_check( exterior_exone_slack_save( $input ) === true, 'All three save' );
$saved = $GLOBALS['slack_meta'];
foreach ( array( 301 => $contact, 302 => $contact, 303 => $entry ) as $id => $url ) {
	$v = get_post_meta( $id, '_ctz_zapier', true ); slack_check( $v['hook_url'] === array( $url ) && $v['send_mail'] === '1' && $v['error_mails'] === array() && $v['activate'] === '1', 'Correct role URL and mail preservation' );
	unset( $GLOBALS['slack_meta'][$id]['_ctz_zapier'] );
}
slack_check( $GLOBALS['slack_meta'] === $base, 'Only new notification metadata changed' ); $GLOBALS['slack_meta'] = $saved;
slack_check( exterior_exone_slack_save( array( 'contact_url' => '', 'entry_url' => '', 'contact_active' => '1', 'entry_active' => '1' ) ) === true && $GLOBALS['slack_meta'] === $saved, 'Empty input retains URLs' );
slack_check( exterior_exone_slack_save( array() ) === true && ! exterior_exone_slack_read_settings( 301 )['active'] && exterior_exone_slack_read_settings( 301 )['url'] === $contact, 'Explicit unchecked notification disables and retains URL' ); $GLOBALS['slack_meta'] = $saved;
foreach ( array( 'http://hooks.slack.com/services/A/B/C', 'https://hooks.slack.com.evil.invalid/services/A/B/C', 'https://hooks.slack.com:443/services/A/B/C', 'https://user@hooks.slack.com/services/A/B/C', $contact . '?x=1', $contact . '#x', $contact . "\n", 'https://hooks.slack.com/services/A/B', 'https://hooks.slack.com/services/A/B/%43', array( $contact ) ) as $bad ) {
	$i = $input; $i['contact_url'] = $bad; slack_check( is_wp_error( exterior_exone_slack_save( $i ) ) && $GLOBALS['slack_meta'] === $saved, 'Invalid URL rejected without changes' );
}
foreach ( array( '_exr_form_role' => array( 'corporate' ), '_exr_source_key' => $base[302]['_exr_source_key'], '_exr_dataset' => array( 'foreign' ), '_exr_source_status' => array( 'draft' ), '_exr_import_state' => array( 'partial' ), '_ctz_zapier' => array( array(), array() ), '_exr_form_role_duplicate' => array( 'personal', 'personal' ) ) as $field => $v ) {
	$field = str_replace( '_duplicate', '', $field ); $GLOBALS['slack_meta'][301][$field] = $v;
	$before = $GLOBALS['slack_meta']; slack_check( is_wp_error( exterior_exone_slack_save( $input ) ) && $before === $GLOBALS['slack_meta'], 'Malformed identity or duplicate metadata rejected' ); $GLOBALS['slack_meta'] = $saved;
}
$GLOBALS['slack_posts'][301]->post_status = 'publish'; slack_check( is_wp_error( exterior_exone_slack_save( $input ) ), 'Wrong storage state rejected' ); $GLOBALS['slack_posts'][301]->post_status = 'exr_stored';
$GLOBALS['slack_meta'] = $base; $GLOBALS['slack_fail_id'] = 302; slack_check( is_wp_error( exterior_exone_slack_save( $input ) ) && $GLOBALS['slack_meta'] === $base, 'Partial save restores initial absence' ); unset( $GLOBALS['slack_fail_id'] );
$GLOBALS['slack_after_write'] = function ( $id ) { if ( 303 === $id ) { $GLOBALS['slack_meta'][301]['_ctz_zapier'] = array( array( 'concurrent' => true ) ); } };
slack_check( is_wp_error( exterior_exone_slack_save( $input ) ) && get_post_meta( 301, '_ctz_zapier', true ) === array( 'concurrent' => true ) && ! get_post_meta( 302, '_ctz_zapier', false ), 'Final recheck detects earlier concurrent modification and preserves it' ); unset( $GLOBALS['slack_after_write'] ); $GLOBALS['slack_meta'] = $saved;
$GLOBALS['slack_allowed'] = false; slack_check( is_wp_error( exterior_exone_slack_save( $input ) ), 'Unauthorized save denied' );
try { exterior_exone_slack_admin_render(); throw new RuntimeException( 'Missing denial' ); } catch ( SlackDenied $e ) { slack_check( 403 === $e->status, 'Unauthorized UI denied' ); } $GLOBALS['slack_allowed'] = true;
foreach ( array( 'GET', 'POST' ) as $method ) { $_SERVER['REQUEST_METHOD'] = $method; try { exterior_exone_slack_admin_save(); throw new RuntimeException( 'Missing denial' ); } catch ( SlackDenied $e ) { slack_check( 403 === $e->status, 'GET or nonce denied' ); } }
ob_start(); exterior_exone_slack_admin_render(); $html = ob_get_clean();
slack_check( strpos( $html, $contact ) === false && strpos( $html, $entry ) === false && strpos( $html, 'FAKECONTACTTOKEN' ) === false && substr_count( $html, 'type="password"' ) === 2, 'No existing URL or token in DOM' );
$form = new SlackForm( 301 ); $old = new SlackForm( 1 ); $args = array( 'legacy' => true );
slack_check( exterior_exone_slack_form_properties( $args, $old ) === $args && exterior_exone_slack_request_args( $args, array(), $old, 'https://example.invalid' ) === $args && false === exterior_exone_slack_ignore_errors( false, $contact, null, $old ), 'Old form fully passes through' );
WPCF7_Submission::$instance = new WPCF7_Submission();
WPCF7_Submission::$instance->data = array( 'store-slug' => 'aomori', 'your-name' => '名前 "引用"', 'your-message' => "日本語\n<!channel> <@U123> & [your-email]", 'your-email' => 'fixture@example.invalid', 'your-job' => array( '営業', '設計', array( 'nested' ) ), 'unapproved-secret' => 'MUSTNOTAPPEAR' );
foreach ( array( 301 => $contact, 302 => $contact, 303 => $entry ) as $id => $url ) {
	$sent = new SlackForm( $id ); $a = exterior_exone_slack_request_args( $args, array(), $sent, $url ); $decoded = json_decode( $a['body'], true );
	slack_check( is_array( $decoded ) && $a['redirection'] === 0 && $a['sslverify'] === true && $a['method'] === 'POST' && false === $decoded['mrkdwn'] && false === $decoded['link_names'] && strpos( $a['body'], 'MUSTNOTAPPEAR' ) === false, 'Safe JSON / no unapproved fields / TLS / no redirect' );
	slack_check( exterior_exone_slack_ignore_errors( false, $url, new WP_Error( 'test', 'test' ), $sent ) === true, 'Webhook failure does not change mail success' );
}
exterior_exone_slack_request_args( $args, array(), $form, $contact );
$GLOBALS['slack_meta'][301]['_ctz_zapier'][0]['activate'] = '0';
slack_check( exterior_exone_slack_ignore_errors( false, $entry, null, $form ) === false && exterior_exone_slack_ignore_errors( false, $contact, null, $form ) === true && exterior_exone_slack_ignore_errors( false, $contact, null, $form ) === false, 'Only prior verified response is suppressed, once, even after settings change' );
$GLOBALS['slack_meta'] = $saved;
$payload = json_decode( exterior_exone_slack_payload( 'personal', WPCF7_Submission::$instance->data ), true );
slack_check( strpos( $payload['text'], '&lt;!channel&gt;' ) !== false && strpos( $payload['text'], '[your-email]' ) !== false && strpos( $payload['text'], '青森店' ) !== false && strpos( $payload['text'], '<@' ) === false, 'Mentions escaped and placeholder input retained literally' );
slack_check( strpos( json_decode( exterior_exone_slack_payload( 'entry', WPCF7_Submission::$instance->data ), true )['text'], '営業 / 設計' ) !== false, 'Flat arrays join; nested array omitted' );
slack_check( strpos( exterior_exone_slack_value( str_repeat( '長', 5000 ) ), '全文はメール' ) !== false, 'Long answer explicitly shortened' );
foreach ( array( $entry, 'https://example.invalid/' ) as $url ) { try { exterior_exone_slack_request_args( $args, array(), $form, $url ); throw new RuntimeException( 'Missing stop' ); } catch ( Exterior_Exone_Slack_Blocked $e ) { slack_check( exterior_exone_slack_blocked_mails( array( 'legacy' ), $e ) === array() && '' === exterior_exone_slack_blocked_message( 'mail failed', $e ), 'Mismatched URL blocked without exposing or failing mail' ); } }
$GLOBALS['slack_meta'][302]['_exr_form_role'] = array( 'personal' );
slack_check( exterior_exone_slack_form_properties( array(), $form )['ctz_zapier']['activate'] === '0', 'Duplicate role disables runtime notification' );
try { exterior_exone_slack_request_args( $args, array(), $form, $contact ); throw new RuntimeException( 'Missing stop' ); } catch ( Exterior_Exone_Slack_Blocked $e ) { slack_check( true, 'Duplicate role blocks final send' ); }
slack_check( exterior_exone_slack_blocked_message( 'legacy failure', new Exception( 'legacy' ) ) === 'legacy failure', 'Legacy error handling unchanged' );
echo 'PASS Slack synthetic checks: ' . $GLOBALS['slack_checks'] . "; DB/HTTP/mail=0\n";
