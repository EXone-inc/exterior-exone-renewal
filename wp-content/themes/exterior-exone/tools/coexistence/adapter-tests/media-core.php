<?php
/** Exact WP 6.8.1 pure-function compatibility checks; no bootstrap, DB or network. */
if (PHP_SAPI !== 'cli') { exit; }
$core = '/Users/exone-se/.codex/visualizations/2026/10/15/01a13e27-de2a-7213-8884-dcd614a97848/migration-private/coexistence-lab-20261016/site/retest';
require $core . '/wp-includes/version.php';
if ($wp_version !== '6.8.1') { throw new RuntimeException('Exact production core required'); }
define('MEDIA_TRASH', false);
$checks = 0; $GLOBALS['review_role'] = 'administrator';
function media_assert($condition, $label) { global $checks; if (!$condition) { throw new RuntimeException($label); } $checks++; }
/** Load only a named function declaration from the inspected core, never its bootstrap. */
function media_load_function($file, $name) {
    $tokens = token_get_all(file_get_contents($file));
    foreach ($tokens as $start => $token) {
        if (!is_array($token) || $token[0] !== T_FUNCTION) { continue; }
        $next = $start + 1;
        while (isset($tokens[$next]) && is_array($tokens[$next]) && $tokens[$next][0] === T_WHITESPACE) { $next++; }
        if (!isset($tokens[$next]) || !is_array($tokens[$next]) || $tokens[$next][0] !== T_STRING || $tokens[$next][1] !== $name) { continue; }
        $code = ''; $depth = 0; $body = false;
        for ($i = $start; $i < count($tokens); $i++) {
            $part = $tokens[$i]; $code .= is_array($part) ? $part[1] : $part;
            if ($part === '{') { $body = true; $depth++; }
            if ($part === '}' && --$depth === 0 && $body) { eval($code); return; }
        }
    }
    throw new RuntimeException('Missing selected core declaration');
}
class WP_Post {
    public $ID, $post_type = 'attachment', $post_parent = 0, $post_status, $filter = 'raw';
    public function __construct($id, $status) { $this->ID = $id; $this->post_status = $status; }
}
class ReviewQueryCaptured extends RuntimeException { public $args; public function __construct($args) { $this->args = $args; } }
class WP_Query { public function __construct($args) { throw new ReviewQueryCaptured($args); } }
class WP_REST_Controller {}
class WP_REST_Post_Meta_Fields { public function __construct($type) {} }
function get_post($post) { return $post instanceof WP_Post ? $post : ($GLOBALS['media_posts'][$post] ?? null); }
function get_post_meta($id, $key, $single = true) { return ''; }
function current_user_can($cap, $id = 0) { return $GLOBALS['review_role'] === 'administrator' || ($cap === 'upload_files' && $GLOBALS['review_role'] === 'author'); }
function get_post_type_object($type) { return (object) ['name' => $type, 'show_in_rest' => true, 'cap' => (object) ['read_private_posts' => 'read_private_posts']]; }
function get_post_status_object($status) { return (object) ['public' => $status === 'publish']; }
function get_user_option($name) { return 20; }
function apply_filters($name, $value, ...$args) { return $value; }
function get_post_mime_types() { return ['image' => []]; }
function get_taxonomies_for_attachments($output) { return []; }
function wp_send_json_error() { throw new RuntimeException('Unexpected Ajax permission failure'); }
function wp_attachment_is_image($id) { return true; }
function wp_get_attachment_url($id) { return 'https://example.invalid/retest/wp-content/uploads/exone-renewal/photo.jpg'; }
function wp_get_attachment_metadata($id) { return ['width' => 1200, 'height' => 800]; }
function wp_basename($path) { return basename($path); }
function image_get_intermediate_size($id, $size) { return $size === 'thumbnail' ? ['file' => 'photo-150x150.jpg', 'width' => 150, 'height' => 150] : false; }
function image_constrain_size_for_editor($width, $height, $size) { return [$width, $height]; }
foreach (['get_post_status' => '/wp-includes/post.php', 'wp_edit_attachments_query_vars' => '/wp-admin/includes/post.php', 'wp_ajax_query_attachments' => '/wp-admin/includes/ajax-actions.php', 'image_downsize' => '/wp-includes/media.php', 'wp_get_attachment_image_src' => '/wp-includes/media.php'] as $function => $path) { media_load_function($core . $path, $function); }
require $core . '/wp-includes/rest-api/endpoints/class-wp-rest-posts-controller.php';
$GLOBALS['media_posts'] = [1 => new WP_Post(1, 'private'), 2 => new WP_Post(2, 'inherit'), 3 => new WP_Post(3, 'exr_stored')];
media_assert(get_post_status(1) === 'private', 'Standard private attachment remains private');
media_assert(get_post_status(2) === 'publish', 'Unattached inherit attachment is public');
media_assert(get_post_status(3) === 'publish', 'Custom attachment state is normalized to publish');
$query = wp_edit_attachments_query_vars([]);
media_assert($query['post_status'] === 'inherit,private', 'Administrator list includes existing and private attachments without theme hooks');
$_REQUEST = ['query' => []];
try { wp_ajax_query_attachments(); throw new RuntimeException('Core did not query attachments'); }
catch (ReviewQueryCaptured $captured) { media_assert($captured->args['post_status'] === 'inherit,private', 'Administrator grid and modal query includes private attachments'); }
$GLOBALS['review_role'] = 'author';
media_assert(wp_edit_attachments_query_vars([])['post_status'] === 'inherit', 'Role without read_private_posts does not list all private attachments');
$controller = new WP_REST_Posts_Controller('attachment');
$GLOBALS['review_role'] = 'anonymous';
media_assert($controller->check_read_permission(get_post(1)) === false, 'Core anonymous REST rejects private attachment');
$GLOBALS['review_role'] = 'administrator';
media_assert($controller->check_read_permission(get_post(1)) === true, 'Core authenticated private read permission permits management REST');
$GLOBALS['review_role'] = 'anonymous';
media_assert(wp_get_attachment_image_src(1, 'full') === ['https://example.invalid/retest/wp-content/uploads/exone-renewal/photo.jpg', 1200, 800, false], 'Private attachment still produces public full image source');
media_assert(wp_get_attachment_image_src(1, 'thumbnail') === ['https://example.invalid/retest/wp-content/uploads/exone-renewal/photo-150x150.jpg', 150, 150, true], 'Private attachment still produces derivative image source');
echo 'PASS ' . $checks . " exact WordPress 6.8.1 media function checks; SQL boundary stubbed, no bootstrap/DB/network.\n";
