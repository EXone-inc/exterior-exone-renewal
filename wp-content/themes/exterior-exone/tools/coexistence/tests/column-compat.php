<?php
/** Synthetic compatibility checks. No WordPress bootstrap, database or HTTP. */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('ABSPATH', __DIR__ . '/');
define('EXR_COEXISTENCE', ($argv[1] ?? '') === 'enabled');
$hooks = [];
$removed_filters = [];
$options = ['blog_public' => '1'];
$vars = ['paged' => 1];
$queried = 3474;
$status = 200;
class WP_Post {
    public $ID = 3474;
    public $post_type = 'page';
    public $post_name = 'column';
    public $post_status = 'publish';
    public $post_title = 'コラム';
    public $post_content = '';
    public $post_excerpt = '';
    public $post_author = 1;
}
class WP_Query {
    public $query;
    public $max_num_pages = 2;
    public $posts = [];
    public $is_404 = false;
    public function __construct(array $query = []) { $this->query = $query; }
    public function set_404() { $this->is_404 = true; }
}
$source = new WP_Post();
$wp_query = new WP_Query();
function add_action($hook, $callback, $priority = 10, $args = 1) { $GLOBALS['hooks'][] = [$hook, $callback]; }
function add_filter($hook, $callback, $priority = 10, $args = 1) { add_action($hook, $callback, $priority, $args); }
function remove_filter($hook, $callback, $priority = 10) { $GLOBALS['removed_filters'][] = [$hook, $callback]; }
function get_post($id) { return $id === 3474 ? $GLOBALS['source'] : null; }
function is_admin() { return false; }
function is_404() { return $GLOBALS['wp_query']->is_404; }
function is_page($id) { return $id === $GLOBALS['queried']; }
function is_singular($type) { return $type === 'column' && $GLOBALS['queried'] !== 3474; }
function is_post_type_archive($type) { return false; }
function get_post_type() { return 'column'; }
function get_permalink($post) { return 'https://example.invalid/column/'; }
function home_url($path = '') { return 'https://example.invalid' . $path; }
function get_query_var($key) { return $GLOBALS['vars'][$key] ?? 0; }
function get_queried_object_id() { return $GLOBALS['queried']; }
function get_post_meta($id, $key, $single) { return $GLOBALS['meta'][$key] ?? ''; }
function get_option($key, $default = false) { return $GLOBALS['options'][$key] ?? $default; }
function get_the_title($id) { return 'コラム'; }
function get_bloginfo($key, $filter = '') { return 'EXone'; }
function sanitize_title($value) { return trim($value); }
function wp_unslash($value) { return $value; }
function trailingslashit($value) { return rtrim($value, '/') . '/'; }
function user_trailingslashit($value, $type) { return trailingslashit($value); }
function status_header($value) { $GLOBALS['status'] = $value; }
function nocache_headers() {}
function wp_strip_all_tags($value) { return strip_tags(preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', '', $value)); }
function strip_shortcodes($value) { return $value; }
function esc_attr($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function esc_html($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
// The minimal CLI omits mbstring; WordPress supplies its own compatibility fallback.
// This fixture only needs UTF-8 slicing and does not bootstrap that entire runtime.
if (!function_exists('mb_substr')) {
    function mb_substr($value, $start, $length = null) { return implode('', array_slice(preg_split('//u', $value, -1, PREG_SPLIT_NO_EMPTY), $start, $length)); }
}
function wp_trim_words($value, $length, $more) { return mb_substr(wp_strip_all_tags($value), 0, $length) . $more; }
function apply_filters($name, $value) { return $value; }
function get_theme_root($name) { return '/nonexistent-column-fixture'; }
function get_theme_root_uri($name) { return 'https://example.invalid/wp-content/themes'; }
function wp_get_attachment_image_src($id, $size) { return ['https://example.invalid/image.jpg', $size === 'thumbnail' ? 150 : 800, $size === 'thumbnail' ? 150 : 600]; }
function wp_get_attachment_caption($id) { return ''; }
function get_userdata($id) { return (object) ['ID' => $id, 'display_name' => 'Fixture author']; }
function get_author_posts_url($id) { return 'https://example.invalid/author/fixture/'; }
function get_the_author_meta($key, $id) { return ''; }
function get_avatar_data($id, $args) { return ['found_avatar' => false]; }
function attachment_url_to_postid($url) { return 0; }
function get_post_thumbnail_id($post) { return 99; }
function wp_get_canonical_url($post) { return 'https://example.invalid/column/fixture/'; }
function get_the_modified_time($format, $post) { return '2026-10-16T10:00:00+09:00'; }
function get_the_time($format, $post) { return '2026-10-01T10:00:00+09:00'; }
require dirname(__DIR__, 3) . '/inc/coexistence-column.php';
$passed = 0;
function check($condition, $name) {
    if (!$condition) { throw new RuntimeException('Failed: ' . $name); }
    $GLOBALS['passed']++;
}
check(PHP_VERSION === '7.4.33', 'real PHP 7.4.33');
if (!EXR_COEXISTENCE) {
    check($hooks === [], 'disabled mode installs no hooks');
    check(!exterior_exone_coexistence_column_is_list(), 'disabled mode list false');
    $wp = (object) ['request' => 'column/page/2', 'query_vars' => ['sentinel' => 1]];
    exterior_exone_coexistence_column_parse_request($wp);
    check($wp->query_vars === ['sentinel' => 1], 'disabled mode query unchanged');
    check(exterior_exone_coexistence_column_title(['title' => 'local']) === ['title' => 'local'], 'disabled mode title unchanged');
    check(exterior_exone_coexistence_column_document_title('local') === 'local', 'disabled final title unchanged');
    exterior_exone_coexistence_column_text_filters();
    check($removed_filters === [], 'disabled mode typography filters unchanged');
    echo 'PASS ' . $passed . " disabled checks; DB/HTTP 0.\n";
    exit;
}
check(count($hooks) === 10, 'enabled hooks');
foreach (['column' => 1, 'column/' => 1, 'column/page/2' => 2, 'column/page/2/' => 2] as $path => $page) {
    $wp = (object) ['request' => $path, 'query_vars' => ['cat' => 'category', 'error' => '404']];
    exterior_exone_coexistence_column_parse_request($wp);
    check($wp->query_vars === ['page_id' => 3474, 'paged' => $page], 'request bridge ' . $path);
}
foreach (['column/article/', 'column-category/example/', 'contact/', 'column/page/not-a-number'] as $path) {
    $wp = (object) ['request' => $path, 'query_vars' => ['sentinel' => 1]];
    exterior_exone_coexistence_column_parse_request($wp);
    check($wp->query_vars === ['sentinel' => 1], 'unrelated request untouched');
}
foreach (['s' => 'search', 'feed' => 'rss2', 'rest_route' => '/wp/v2/posts'] as $name => $value) {
    $wp = (object) ['request' => 'column/', 'query_vars' => [$name => $value]];
    exterior_exone_coexistence_column_parse_request($wp);
    check($wp->query_vars === [$name => $value], 'explicit endpoint remains untouched');
}
$wp = (object) ['request' => 'column/page/0/', 'query_vars' => []];
exterior_exone_coexistence_column_parse_request($wp);
check($wp->query_vars === ['error' => '404'], 'invalid page zero');
$source->post_name = 'other';
$wp->request = 'column/';
exterior_exone_coexistence_column_parse_request($wp);
check($wp->query_vars === ['error' => '404'], 'missing protected page fails closed');
$source->post_name = 'column';
$vars['paged'] = 2;
$_GET['cat'] = 'missing-category';
$query = exterior_exone_coexistence_column_list_query();
check($query->query['post_type'] === 'column' && $query->query['post_status'] === 'publish' && $query->query['posts_per_page'] === 10 && $query->query['paged'] === 2, 'published shared query only');
check($query->query['tax_query'][0]['terms'] === 'missing-category', 'unknown category not converted to all');
check(exterior_exone_coexistence_column_canonical('wrong', $source) === 'https://example.invalid/column/page/2/', 'paged self canonical');
$vars['paged'] = 3;
exterior_exone_coexistence_column_out_of_range();
check($status === 404 && $wp_query->is_404, 'out of range is HTTP404');
$vars['page_id'] = 3474;
check(exterior_exone_coexistence_column_redirect_canonical('https://example.invalid/column/') === false, 'out-of-range 404 cannot become a guessed redirect');
$vars['page_id'] = 123;
check(exterior_exone_coexistence_column_redirect_canonical('unchanged') === 'unchanged', 'other 404 redirects remain untouched');
unset($vars['page_id']);
$vars['paged'] = 2;
check(exterior_exone_coexistence_column_title([]) === ['title' => 'コラム - EXone - 2ページ'], 'legacy title pagination');
check(exterior_exone_coexistence_column_document_title('コラム – EXone – 2ページ') === 'コラム - EXone - 2ページ', 'final title preserves legacy literal separator');
$queried = 3863;
exterior_exone_coexistence_column_text_filters();
check($removed_filters === [['the_title', 'wptexturize'], ['the_content', 'wptexturize']], 'shared column punctuation is preserved at render time');
$queried = 3474;
$meta['st_titlewords'] = 'Custom';
check(exterior_exone_coexistence_column_title([]) === ['title' => 'Custom - 2ページ'], 'custom title no site suffix');
$meta = [];
$robots = exterior_exone_coexistence_column_robots(['max-image-preview' => 'large']);
check(isset($robots['index'], $robots['follow']) && $robots['max-image-preview'] === 'large', 'legacy robots without losing core settings');
$robots = exterior_exone_coexistence_column_robots(['noindex' => true]);
check(isset($robots['noindex']) && !isset($robots['index']), 'isolation noindex preserved');
$options['blog_public'] = '0';
check(!isset(exterior_exone_coexistence_column_robots([])['index']), 'site privacy preserved');
$json = '<script type="application/ld+json">{"@graph":[{"@type":["BreadcrumbList"],"itemListElement":[]}]}</script>';
check(exterior_exone_coexistence_column_has_schema($json, 'BreadcrumbList'), 'nested breadcrumb detected');
check(!exterior_exone_coexistence_column_has_schema($json, 'Article'), 'absent schema not invented');
check(!exterior_exone_coexistence_column_has_schema('<script type="application/ld+json">broken</script>', 'Article'), 'invalid JSON does not remove output');
$options['st-data406'] = 'yes';
$options['st-data407'] = 'yes';
$article = clone $source;
$article->ID = 3863;
$article->post_content = '<p>Fixture article body</p>';
$graphs = exterior_exone_coexistence_column_schema_graphs($article, false);
check(array_column($graphs, '@type') === ['WebSite', 'Article'], 'enabled legacy graphs retained');
check($graphs[1]['datePublished'] === '2026-10-01T10:00:00+09:00' && $graphs[1]['dateModified'] === '2026-10-16T10:00:00+09:00', 'published and modified dates retained');
check($graphs[1]['image']['url'] === 'https://example.invalid/image.jpg' && $graphs[1]['publisher']['@type'] === 'Organization' && $graphs[0]['publisher']['@type'] === 'Person', 'legacy attachment and publisher shape');
$article->post_content = '<script type="application/ld+json">{"@type":"Article"}</script>';
check(array_column(exterior_exone_coexistence_column_schema_graphs($article, false), '@type') === ['WebSite'], 'existing Article is not duplicated');
check(array_column(exterior_exone_coexistence_column_schema_graphs($source, true), '@type') === ['WebSite'], 'listing is not labeled an Article');
$options['st-data406'] = '';
$options['st-data407'] = '';
check(exterior_exone_coexistence_column_schema_graphs($source, false) === [], 'disabled legacy graphs not invented');
require dirname(__DIR__, 3) . '/inc/column-page-data.php';
$queried = 3863;
$body = '<style>.a{color:black}</style><div id="toc">Text</div>' . $json . '<script>window.fixture=1;</script><img src="/fixture.png" loading="lazy">';
$result = exterior_exone_column_display_content($body);
check(strpos($result, '<style>') === false && strpos($result, $json) !== false && strpos($result, '<script>window.fixture=1;</script>') !== false, 'display preserves JSONLD and lightbox scripts');
check(strpos($body, '<style>') === 0, 'source body unchanged');
echo 'PASS ' . $passed . " enabled checks; DB/HTTP 0.\n";
