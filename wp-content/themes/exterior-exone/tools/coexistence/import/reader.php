<?php
/** PHP 7.4専用。WordPress・元DBを起動しないパッケージ検証。 */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/schema.php';

const EXTERIOR_EXONE_IMPORT_ORIGIN = 'exterior-exone-renewal';
const EXTERIOR_EXONE_IMPORT_PRIVATE_ROOT = '/Users/exone-se/.codex/visualizations/2026/10/15/01a13e27-de2a-7213-8884-dcd614a97848/migration-private';
const EXTERIOR_EXONE_IMPORT_TYPES = ['works', 'news', 'page', 'attachment', 'wpcf7_contact_form'];
const EXTERIOR_EXONE_IMPORT_TAXONOMIES = ['works_category', 'works_package', 'works_tag'];
const EXTERIOR_EXONE_IMPORT_STATUSES = ['publish', 'draft', 'pending', 'private', 'future', 'inherit'];
const EXTERIOR_EXONE_IMPORT_EXCLUDED_STATUSES = ['trash', 'auto-draft'];
final class ExteriorExoneImportException extends RuntimeException {}

function exterior_exone_import_is_list(array $value): bool {
    return $value === [] || array_keys($value) === range(0, count($value) - 1);
}
function exterior_exone_import_contains(string $haystack, string $needle): bool {
    return $needle === '' || strpos($haystack, $needle) !== false;
}
function exterior_exone_import_starts(string $haystack, string $needle): bool {
    return $needle === '' || strpos($haystack, $needle) === 0;
}
function exterior_exone_import_ends(string $haystack, string $needle): bool {
    return $needle === '' || substr($haystack, -strlen($needle)) === $needle;
}
function exterior_exone_import_meta_keys(string $type): array {
    $keys = [
        'works' => ['_exterior_exone_works_image_main', '_exterior_exone_works_image_exterior', '_exterior_exone_works_image_perspective', '_exterior_exone_works_image_plan', '_exterior_exone_works_gallery', '_exterior_exone_works_area', '_exterior_exone_works_on_top'],
        'news' => ['_thumbnail_id'],
        'page' => ['_thumbnail_id', '_wp_page_template', '_wp_old_date'],
        'attachment' => ['_wp_attached_file', '_wp_attachment_metadata', '_wp_attachment_backup_sizes', '_wp_attachment_image_alt'],
        'wpcf7_contact_form' => ['_form', '_mail', '_mail_2', '_messages', '_locale', '_additional_settings', '_exterior_exone_contact_form', '_exterior_exone_recruit_form'],
    ];
    return $keys[$type] ?? [];
}

function exterior_exone_import_assert(bool $condition, string $message): void {
    if (!$condition) {
        throw new ExteriorExoneImportException($message);
    }
}


function exterior_exone_import_arguments(array $parts): array {
    $result = [];
    for ($i = 0; $i < count($parts); $i += 2) {
        exterior_exone_import_assert((bool) preg_match('/\A--[a-z-]+\z/', $parts[$i]) && isset($parts[$i + 1]), 'Options require --name value pairs.');
        $name = substr($parts[$i], 2);
        exterior_exone_import_assert(!isset($result[$name]), 'Duplicate option.');
        $result[$name] = $parts[$i + 1];
    }
    return $result;
}


function exterior_exone_import_options(array $options, array $required): void {
    $keys = array_keys($options);
    sort($keys);
    sort($required);
    exterior_exone_import_assert($keys === $required, 'Missing or unsupported option; host/socket/database overrides are unavailable.');
}


function exterior_exone_import_canonical($value) {
    if (is_array($value)) {
        if (!exterior_exone_import_is_list($value)) {
            ksort($value, SORT_STRING);
        }
        foreach ($value as &$item) {
            $item = exterior_exone_import_canonical($item);
        }
    }
    return $value;
}


function exterior_exone_import_json($value): string {
    return json_encode(exterior_exone_import_canonical($value), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . "\n";
}


function exterior_exone_import_hash($value): string {
    return hash('sha256', exterior_exone_import_json($value));
}


function exterior_exone_import_key(string $kind, int $id, string $taxonomy = ''): string {
    exterior_exone_import_assert($id > 0, 'Source IDs must be positive integers.');
    return EXTERIOR_EXONE_IMPORT_ORIGIN . ':v1:' . $kind . ':' . ($taxonomy !== '' ? $taxonomy . ':' : '') . $id;
}


function exterior_exone_import_destination_type(string $type): string {
    return in_array($type, ['works', 'news', 'page'], true) ? 'exr_' . $type : $type;
}

/** メタは投稿種別ごとの許可リスト。ユーザー、編集ロック、CF7 hash、Webhook を除外。 */

function exterior_exone_import_decode(string $value) {
    if (!preg_match('/\A(?:(?:a|s|i|b|d|O|C|R|r):|N;)/', $value)) {
        return $value;
    }
    set_error_handler(static function (): void { throw new ExteriorExoneImportException('Invalid serialized metadata.'); });
    try {
        $decoded = unserialize($value, ['allowed_classes' => false]);
    } finally {
        restore_error_handler();
    }
    $check = static function ($item, int $depth = 0) use (&$check): void {
        exterior_exone_import_assert($depth < 40 && !is_object($item) && !is_resource($item), 'Unsupported metadata structure.');
        if (is_array($item)) {
            foreach ($item as $child) {
                $check($child, $depth + 1);
            }
        }
    };
    $check($decoded);
    exterior_exone_import_assert(serialize($decoded) === $value, 'Non-canonical or trailing serialized metadata.');
    return $decoded;
}


function exterior_exone_import_relative(string $path): string {
    exterior_exone_import_assert($path !== '' && !exterior_exone_import_contains($path, "\0") && !exterior_exone_import_contains($path, '\\') && !exterior_exone_import_contains($path, ':') && $path[0] !== '/', 'Unsafe relative file path.');
    foreach (explode('/', $path) as $part) {
        exterior_exone_import_assert($part !== '' && $part !== '.' && $part !== '..' && !preg_match('/[\x00-\x1f]/', $part), 'Unsafe relative file path.');
    }
    return $path;
}


function exterior_exone_import_under(string $path, string $root): bool {
    return $path === $root || exterior_exone_import_starts($path, rtrim($root, '/') . '/');
}


function exterior_exone_import_no_symlinks(string $path): void {
    exterior_exone_import_assert(exterior_exone_import_starts($path, '/'), 'Absolute paths are required.');
    $current = '';
    foreach (explode('/', trim($path, '/')) as $part) {
        exterior_exone_import_assert($part !== '' && $part !== '.' && $part !== '..', 'Path traversal is not allowed.');
        $current .= '/' . $part;
        exterior_exone_import_assert(!is_link($current), 'Symbolic links are not accepted.');
    }
}

/** 公開ルート配下には package / report を作らない。出力先は毎回新規。 */

function exterior_exone_import_output_guard(string $path): void {
    exterior_exone_import_no_symlinks($path);
    exterior_exone_import_assert($path !== EXTERIOR_EXONE_IMPORT_PRIVATE_ROOT && exterior_exone_import_under($path, EXTERIOR_EXONE_IMPORT_PRIVATE_ROOT), 'Output must be inside the fixed private preparation root.');
    exterior_exone_import_assert(!exterior_exone_import_under($path, EXTERIOR_EXONE_IMPORT_PRIVATE_ROOT . '/coexistence-lab-20261016/site'), 'Reports and source payloads must stay outside the isolated web document root.');
    exterior_exone_import_assert(is_dir(EXTERIOR_EXONE_IMPORT_PRIVATE_ROOT) && (fileperms(EXTERIOR_EXONE_IMPORT_PRIVATE_ROOT) & 0777) === 0700, 'Private preparation root must already exist with mode 0700.');
    exterior_exone_import_assert(!file_exists($path), 'Output already exists; choose a new private directory.');
    $parent = dirname($path);
    exterior_exone_import_assert(is_dir($parent) && realpath($parent) === $parent, 'Output parent must already exist and use its real absolute path.');
    exterior_exone_import_assert((fileperms($parent) & 0777) === 0700, 'Output parent must have mode 0700.');
}


function exterior_exone_import_private_directory(string $path): string {
    exterior_exone_import_output_guard($path);
    $old = umask(0077);
    try {
        exterior_exone_import_assert(mkdir($path, 0700), 'Could not create private output directory.');
        exterior_exone_import_assert(chmod($path, 0700), 'Could not restrict output directory.');
    } finally {
        umask($old);
    }
    return $path;
}


function exterior_exone_import_write(string $path, string $bytes): void {
    $old = umask(0077);
    $handle = fopen($path, 'xb');
    umask($old);
    exterior_exone_import_assert($handle !== false, 'Refusing to replace an existing output file.');
    try {
        exterior_exone_import_assert(chmod($path, 0600), 'Could not restrict output file.');
        $offset = 0;
        while ($offset < strlen($bytes)) {
            $written = fwrite($handle, substr($bytes, $offset));
            exterior_exone_import_assert($written !== false && $written > 0, 'Output write failed.');
            $offset += $written;
        }
        fflush($handle);
    } finally {
        fclose($handle);
    }
}


function exterior_exone_import_read_json(string $path): array {
    exterior_exone_import_no_symlinks($path);
    exterior_exone_import_assert(is_file($path) && filesize($path) < 64 * 1024 * 1024, 'JSON input must be a regular file below 64 MiB.');
    $bytes = (string) file_get_contents($path);
    $data = json_decode($bytes, true, 64, JSON_THROW_ON_ERROR);
    exterior_exone_import_assert(is_array($data), 'JSON input must contain an object.');
    exterior_exone_import_unique_json_keys($bytes);
    return $data;
}

/** json_decode が黙って採用する同一object内の重複キーを検出する。 */

function exterior_exone_import_unique_json_keys(string $json): void {
    preg_match_all('/"(?:[^"\\\\]|\\\\.)*"|[{}\[\]:,]|-?\d+(?:\.\d+)?(?:[eE][+-]?\d+)?|true|false|null/s', $json, $matches);
    $stack = [];
    $tokens = $matches[0];
    foreach ($tokens as $index => $token) {
        if ($token === '{' || $token === '[') {
            $stack[] = ['object' => $token === '{', 'keys' => []];
        } elseif ($token === '}' || $token === ']') {
            array_pop($stack);
        } elseif ($token[0] === '"' && ($tokens[$index + 1] ?? '') === ':' && $stack) {
            $last = count($stack) - 1;
            $key = (string) json_decode($token, true, 2, JSON_THROW_ON_ERROR);
            exterior_exone_import_assert(!isset($stack[$last]['keys'][$key]), 'Duplicate JSON object key.');
            $stack[$last]['keys'][$key] = true;
        }
    }
}


function exterior_exone_import_meta_value(array $record, string $key) {
    return isset($record['meta'][$key][0]) ? exterior_exone_import_decode($record['meta'][$key][0]) : null;
}


function exterior_exone_import_seal(array $record): array {
    $record['content_sha256'] = exterior_exone_import_hash($record);
    return $record;
}

/** 元画像・生成サイズ・編集前の画像を同じ添付に記録。パスは uploads 相対。 */

function exterior_exone_import_media_paths(array $record): array {
    $attached = exterior_exone_import_meta_value($record, '_wp_attached_file');
    exterior_exone_import_assert(is_string($attached) && $attached !== '', 'Attachment is missing its source file path.');
    $paths = [$attached => 'attached'];
    $metadata = exterior_exone_import_meta_value($record, '_wp_attachment_metadata');
    if (is_array($metadata)) {
        $base = $metadata['file'] ?? $attached;
        exterior_exone_import_assert(is_string($base), 'Invalid attachment metadata file.');
        $paths[$base] = 'metadata-original';
        $directory = dirname($base) === '.' ? '' : dirname($base) . '/';
        if (!empty($metadata['original_image'])) {
            $paths[$directory . $metadata['original_image']] = 'original-image';
        }
        foreach (($metadata['sizes'] ?? []) as $size) {
            exterior_exone_import_assert(is_array($size) && isset($size['file']) && is_string($size['file']), 'Invalid derivative metadata.');
            $paths[$directory . $size['file']] = 'derivative';
            foreach (($size['sources'] ?? []) as $format) {
                if (is_array($format) && isset($format['file'])) {
                    $paths[$directory . $format['file']] = 'derivative-format';
                }
            }
        }
    }
    $directory = dirname($attached) === '.' ? '' : dirname($attached) . '/';
    $backups = exterior_exone_import_meta_value($record, '_wp_attachment_backup_sizes');
    if (is_array($backups)) {
        foreach ($backups as $backup) {
            exterior_exone_import_assert(is_array($backup) && isset($backup['file']) && is_string($backup['file']), 'Invalid attachment backup metadata.');
            $paths[$directory . $backup['file']] = 'backup-size';
        }
    }
    ksort($paths, SORT_STRING);
    foreach (array_keys($paths) as $path) {
        exterior_exone_import_relative($path);
        exterior_exone_import_assert(!preg_match('/\.(?:php\d*|phtml|phar|sql|sh|bash|zsh|py|exe|bat|cmd|com)\z/i', $path), 'Executable or database-dump media payload is not permitted.');
    }
    return $paths;
}


function exterior_exone_import_records(array $snapshot): array {
    $records = [];
    $ids = [];
    foreach ($snapshot['posts'] as $post) {
        $type = $post['post_type'];
        if (!in_array($type, EXTERIOR_EXONE_IMPORT_TYPES, true)) {
            continue;
        }
        if (!in_array($post['post_status'], EXTERIOR_EXONE_IMPORT_STATUSES, true)) {
            continue;
        }
        $id = (int) $post['ID'];
        exterior_exone_import_assert(!isset($ids[$id]), 'Duplicate source post ID.');
        $key = exterior_exone_import_key('post', $id, $type);
        $data = [];
        foreach (['post_status', 'post_name', 'post_title', 'post_content', 'post_excerpt', 'post_date', 'post_date_gmt', 'post_modified', 'post_modified_gmt', 'post_mime_type', 'comment_status', 'ping_status'] as $field) {
            $data[$field] = (string) ($post[$field] ?? '');
        }
        $data['menu_order'] = (int) ($post['menu_order'] ?? 0);
        $records[$key] = ['source_key' => $key, 'kind' => $type, 'source_id' => $id, 'source_parent_id' => (int) ($post['post_parent'] ?? 0), 'source_parent_type' => (string) ($post['source_parent_type'] ?? ''), 'data' => $data, 'meta' => [], 'references' => [], 'files' => []];
        $ids[$id] = $key;
    }
    foreach ($snapshot['meta'] as $meta) {
        $key = $ids[(int) $meta['post_id']] ?? null;
        if ($key === null || !in_array($meta['meta_key'], exterior_exone_import_meta_keys($records[$key]['kind']), true)) {
            continue;
        }
        $value = (string) $meta['meta_value'];
        exterior_exone_import_decode($value);
        $records[$key]['meta'][$meta['meta_key']][] = $value;
    }
    $taxonomyIds = [];
    foreach ($snapshot['terms'] as $term) {
        if (!in_array($term['taxonomy'], EXTERIOR_EXONE_IMPORT_TAXONOMIES, true)) {
            continue;
        }
        $key = exterior_exone_import_key('term', (int) $term['term_id'], $term['taxonomy']);
        exterior_exone_import_assert(!isset($records[$key]), 'Duplicate source term.');
        $records[$key] = ['source_key' => $key, 'kind' => 'term', 'source_id' => (int) $term['term_id'], 'source_term_taxonomy_id' => (int) $term['term_taxonomy_id'], 'taxonomy' => $term['taxonomy'], 'source_parent_id' => (int) $term['parent'], 'data' => ['name' => $term['name'], 'slug' => $term['slug'], 'description' => $term['description']], 'meta' => [], 'references' => [], 'files' => []];
        exterior_exone_import_assert(!isset($taxonomyIds[(int) $term['term_taxonomy_id']]), 'Duplicate term taxonomy ID.');
        $taxonomyIds[(int) $term['term_taxonomy_id']] = $key;
    }
    foreach ($snapshot['relationships'] as $relationship) {
        $key = $ids[(int) $relationship['object_id']] ?? null;
        $term = $taxonomyIds[(int) $relationship['term_taxonomy_id']] ?? null;
        exterior_exone_import_assert($key !== null && $term !== null && $records[$key]['kind'] === 'works', 'Unresolved taxonomy relationship.');
        $records[$key]['references'][] = ['field' => 'term_relationship', 'target' => $term, 'order' => (int) ($relationship['term_order'] ?? 0)];
    }
    foreach ($records as $key => &$record) {
        $parent = $record['source_parent_id'];
        if ($parent === 0) {
            $record['parent_policy'] = 'root';
        } elseif ($record['kind'] === 'term') {
            $target = exterior_exone_import_key('term', $parent, $record['taxonomy']);
            exterior_exone_import_assert(isset($records[$target]), 'Missing parent term.');
            $record['parent_policy'] = 'remap';
            $record['references'][] = ['field' => 'parent', 'target' => $target];
        } elseif (isset($ids[$parent])) {
            $record['parent_policy'] = 'remap';
            $record['references'][] = ['field' => 'parent', 'target' => $ids[$parent]];
        } else {
            exterior_exone_import_assert($record['kind'] === 'attachment', 'Missing parent of non-media source record.');
            $record['parent_policy'] = 'excluded-parent-destination-zero';
        }
        foreach ($record['meta'] as $name => $values) {
            if ($name === '_thumbnail_id' || exterior_exone_import_starts($name, '_exterior_exone_works_image_') || $name === '_exterior_exone_works_gallery') {
                foreach ($values as $value) {
                    $decoded = exterior_exone_import_decode($value);
                    $refs = is_array($decoded) ? $decoded : explode(',', (string) $decoded);
                    foreach ($refs as $position => $ref) {
                        exterior_exone_import_assert(is_scalar($ref) && preg_match('/\A\d*\z/', trim((string) $ref)) === 1, 'Invalid attachment reference.');
                        if ((int) $ref === 0) {
                            continue;
                        }
                        $target = $ids[(int) $ref] ?? null;
                        exterior_exone_import_assert($target !== null && $records[$target]['kind'] === 'attachment', 'Unresolved attachment reference.');
                        $record['references'][] = ['field' => $name, 'target' => $target, 'order' => $position];
                    }
                }
            }
        }
        if ($record['kind'] !== 'term') {
            exterior_exone_import_content_references($record, $records, $ids);
        }
        usort($record['references'], static fn(array $a, array $b): int => strcmp(exterior_exone_import_json($a), exterior_exone_import_json($b)));
    }
    unset($record);
    ksort($records, SORT_STRING);
    return $records;
}

/** 本文の参照は書き換えずに一覧化。未知の添付・フォームを推測しない。 */

function exterior_exone_import_content_references(array &$record, array $records, array $ids): void {
    $texts = ['post_content' => $record['data']['post_content'], 'post_excerpt' => $record['data']['post_excerpt']];
    if ($record['kind'] === 'wpcf7_contact_form') {
        foreach ($record['meta'] as $key => $values) {
            $texts[$key] = implode("\n", $values);
        }
    }
    foreach ($texts as $field => $text) {
        preg_match_all('/\b(?:wp-image-|attachment_)(\d+)\b/', $text, $matches);
        $attachmentIds = $matches[1];
        preg_match_all('/\[gallery\b[^\]]*\bids=["\x27]([0-9,\s]+)["\x27]/i', $text, $galleries);
        foreach ($galleries[1] as $gallery) {
            $attachmentIds = array_merge($attachmentIds, preg_split('/[,\s]+/', trim($gallery)));
        }
        preg_match_all('/<!--\s+wp:(?:image|media-text|cover)\s+(\{.*?\})\s*(?:\/)?-->/s', $text, $blocks);
        foreach ($blocks[1] as $block) {
            $attributes = json_decode($block, true);
            foreach (['id', 'mediaId'] as $key) {
                if (isset($attributes[$key])) {
                    $attachmentIds[] = $attributes[$key];
                }
            }
        }
        foreach (array_unique($attachmentIds) as $id) {
            $target = $ids[(int) $id] ?? null;
            exterior_exone_import_assert($target !== null && $records[$target]['kind'] === 'attachment', 'Unresolved body image reference.');
            $record['references'][] = ['field' => $field . ':image-id', 'target' => $target];
        }
        preg_match_all('/\[contact-form-7\b[^\]]*\]/i', $text, $forms);
        foreach ($forms[0] as $shortcode) {
            exterior_exone_import_assert(preg_match('/\bid=(?:"([^"]+)"|\x27([^\x27]+)\x27|([^\s\]]+))/i', $shortcode, $form) === 1, 'CF7 shortcode lacks an explicit source ID.');
            $id = $form[1] !== '' ? $form[1] : ($form[2] !== '' ? $form[2] : $form[3]);
            $target = preg_match('/\A[0-9]+\z/', $id) === 1 ? ($ids[(int) $id] ?? null) : null;
            exterior_exone_import_assert($target !== null && $records[$target]['kind'] === 'wpcf7_contact_form', 'Unresolved CF7 shortcode; hash IDs require an explicit source-ID mapping.');
            $record['references'][] = ['field' => $field . ':form-id', 'target' => $target];
        }
        preg_match_all('~(?:(?:https?:)?//[^\s"\x27<>,)]+)?/wp-content/uploads/[^\s"\x27<>,)]+~u', html_entity_decode($text, ENT_QUOTES | ENT_HTML5), $urls);
        foreach (array_unique($urls[0]) as $url) {
            $host = parse_url($url, PHP_URL_HOST);
            if ($host !== null && $host !== 'exterior-exone-renewal.local') {
                $record['references'][] = ['field' => $field . ':external-media-url', 'external_url' => $url, 'policy' => 'retain-and-review'];
                continue;
            }
            $path = (string) parse_url($url, PHP_URL_PATH);
            $relative = rawurldecode(substr($path, strpos($path, '/wp-content/uploads/') + strlen('/wp-content/uploads/')));
            exterior_exone_import_relative($relative);
            $target = null;
            foreach ($records as $candidateKey => $candidate) {
                if ($candidate['kind'] === 'attachment' && array_key_exists($relative, exterior_exone_import_media_paths($candidate))) {
                    $target = $candidateKey;
                    break;
                }
            }
            exterior_exone_import_assert($target !== null, 'Local uploads URL has no exported attachment.');
            $record['references'][] = ['field' => $field . ':media-url', 'target' => $target, 'source_relative_path' => $relative];
        }
        foreach (exterior_exone_import_markup_media_references($text, $field) as $reference) {
            $record['references'][] = $reference;
        }
        preg_match_all('~https?://exterior-exone-renewal\.local/[^\s"\x27<>,)]*~u', html_entity_decode($text, ENT_QUOTES | ENT_HTML5), $links);
        foreach (array_unique($links[0]) as $url) {
            if (!exterior_exone_import_contains($url, '/wp-content/uploads/')) {
                $record['references'][] = ['field' => $field . (exterior_exone_import_contains($url, '/column/') ? ':shared-column-link' : ':local-link'), 'external_url' => $url, 'policy' => 'retain-and-review'];
            }
        }
    }
}

/** 外部・相対・独自属性を参照なしにしない。URLは文字列として扱い、一切取得しない。 */

function exterior_exone_import_markup_media_references(string $text, string $field): array {
    $references = [];
    preg_match_all('/<(img|source|video|audio|iframe|embed|object)\b[^>]*>/is', $text, $elements, PREG_SET_ORDER);
    foreach ($elements as $element) {
        $parsed = false;
        preg_match_all('/\b(src|srcset|poster|data-src|data-srcset|data-lazy-src|data-lazy-srcset)\s*=\s*(?:"([^"]*)"|\x27([^\x27]*)\x27|([^\s"\x27=<>`]+))/is', $element[0], $attributes, PREG_SET_ORDER);
        foreach ($attributes as $attribute) {
            $value = html_entity_decode($attribute[2] !== '' ? $attribute[2] : (($attribute[3] ?? '') !== '' ? $attribute[3] : ($attribute[4] ?? '')), ENT_QUOTES | ENT_HTML5);
            $parsed = true;
            $label = $field . ':media-attribute-' . strtolower($attribute[1]);
            if ($value === '' || preg_match('/\A(?:data|blob):/i', $value)) {
                $references[] = ['field' => $label, 'unresolved_media' => $value === '' ? 'empty-media-attribute' : 'inline-or-blob-media-not-separately-inventoried', 'policy' => 'retain-and-review'];
                continue;
            }
            $candidates = exterior_exone_import_ends(strtolower($attribute[1]), 'srcset') ? preg_split('/\s*,\s*/', $value) : [$value];
            foreach ($candidates as $candidate) {
                $url = trim(preg_split('/\s+/', trim($candidate))[0]);
                if ($url === '') {
                    $references[] = ['field' => $label, 'unresolved_media' => 'unparsed-srcset-candidate', 'policy' => 'retain-and-review'];
                    continue;
                }
                $host = parse_url($url, PHP_URL_HOST);
                // この形式のuploads参照は上の厳密な添付照合で既に処理している。
                if (exterior_exone_import_contains($url, '/wp-content/uploads/') && ($host === null || $host === 'exterior-exone-renewal.local')) {
                    continue;
                }
                if ($host !== null && $host !== 'exterior-exone-renewal.local') {
                    $references[] = ['field' => $label, 'external_url' => $url, 'policy' => 'retain-and-review'];
                } else {
                    $references[] = ['field' => $label, 'source_url' => $url, 'policy' => 'retain-and-review'];
                }
            }
        }
        // 属性を解釈できた場合も、この簡易解析が全メディア記法を網羅したとはしない。
        $references[] = ['field' => $field . ':media-markup-review', 'unresolved_media' => $parsed ? 'verify-media-attributes-and-responsive-candidates' : 'media-element-without-supported-source-attribute', 'policy' => 'retain-and-review'];
    }
    if (preg_match('/\burl\s*\(|<(?:picture|svg)\b|<img\b(?![^>]*>)/is', $text)) {
        $references[] = ['field' => $field . ':other-media-syntax', 'unresolved_media' => 'css-picture-svg-or-incomplete-media-markup', 'policy' => 'retain-and-review'];
    }
    $unique = [];
    foreach ($references as $reference) {
        $unique[exterior_exone_import_hash($reference)] = $reference;
    }
    return array_values($unique);
}


function exterior_exone_import_validate(string $directory): array {
    exterior_exone_import_no_symlinks($directory);
    exterior_exone_import_assert(exterior_exone_import_under($directory, EXTERIOR_EXONE_IMPORT_PRIVATE_ROOT), 'Package must be inside the fixed private root.');
    exterior_exone_import_assert(!exterior_exone_import_under($directory, EXTERIOR_EXONE_IMPORT_PRIVATE_ROOT . '/coexistence-lab-20261016/site'), 'Source packages must stay outside the isolated web document root.');
    exterior_exone_import_assert(is_dir($directory) && realpath($directory) === $directory, 'Package must be an existing real directory.');
    $manifest = exterior_exone_import_read_json($directory . '/manifest.json');
    exterior_exone_import_schema($manifest);
    exterior_exone_import_no_symlinks($directory . '/manifest.sha256');
    $expected = hash_file('sha256', $directory . '/manifest.json') . "  manifest.json\n";
    exterior_exone_import_assert(is_file($directory . '/manifest.sha256') && file_get_contents($directory . '/manifest.sha256') === $expected, 'Manifest checksum mismatch.');
    exterior_exone_import_assert(($manifest['schema_version'] ?? null) === 1 && ($manifest['package_type'] ?? null) === 'exone-coexistence-preparation' && ($manifest['origin'] ?? null) === EXTERIOR_EXONE_IMPORT_ORIGIN && ($manifest['production_ready'] ?? null) === false, 'Unsupported manifest schema or scope.');
    exterior_exone_import_assert(isset($manifest['records']) && is_array($manifest['records']) && exterior_exone_import_is_list($manifest['records']), 'Invalid records array.');
    $keys = [];
    exterior_exone_import_assert(($manifest['state'] ?? null) === 'complete' && ($manifest['preparation_only'] ?? null) === true, 'Package is incomplete.');
    $manifestKeys = ['schema_version', 'tool_version', 'run_id', 'state', 'package_type', 'origin', 'created_utc', 'production_ready', 'preparation_only', 'scope', 'snapshot', 'theme_dependencies', 'unknown_meta', 'excluded_column_count', 'excluded_status_counts', 'counts', 'status_counts', 'records', 'source_fingerprint', 'analysis'];
    exterior_exone_import_assert(!array_diff(array_keys($manifest), $manifestKeys), 'Unknown top-level payload field.');
    $scope = ['columns' => 'excluded', 'users' => 'excluded', 'options' => 'excluded', 'comments' => 'excluded', 'received_submissions' => 'excluded', 'cf7_hash_and_webhooks' => 'excluded', 'all_attachments' => 'included'];
    exterior_exone_import_assert(exterior_exone_import_hash($scope) === exterior_exone_import_hash($manifest['scope']), 'Invalid exclusion policy.');
    exterior_exone_import_assert(!array_diff(array_keys($manifest['snapshot']), ['started_utc', 'completed_utc', 'mode', 'local_site_id', 'database']), 'Unknown snapshot field.');
    exterior_exone_import_assert(in_array($manifest['snapshot']['mode'] ?? null, ['synthetic; no database', 'REPEATABLE READ; CONSISTENT SNAPSHOT; READ ONLY'], true), 'Invalid snapshot mode.');
    if ($manifest['snapshot']['mode'] !== 'synthetic; no database') {
        exterior_exone_import_assert(($manifest['snapshot']['local_site_id'] ?? null) === 'GRbmaPCLC' && ($manifest['snapshot']['database'] ?? null) === 'local', 'Invalid snapshot source identity.');
        foreach (['started_utc', 'completed_utc'] as $time) {
            exterior_exone_import_assert(is_string($manifest['snapshot'][$time] ?? null) && preg_match('/\A\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+00:00\z/', $manifest['snapshot'][$time]) === 1, 'Invalid snapshot timestamp.');
        }
    } else {
        exterior_exone_import_assert(array_keys($manifest['snapshot']) === ['mode'], 'Synthetic snapshot must not contain extra fields.');
    }
    exterior_exone_import_assert(!array_diff(array_keys($manifest['theme_dependencies']), ['captured_utc', 'files', 'fingerprint', 'payload']), 'Unknown dependency field.');
    exterior_exone_import_assert(is_array($manifest['theme_dependencies']['files'] ?? null) && exterior_exone_import_is_list($manifest['theme_dependencies']['files']) && in_array($manifest['theme_dependencies']['payload'] ?? null, ['not-included; separate theme copy required', 'synthetic fixture; not captured'], true), 'Invalid dependency structure.');
    if ($manifest['theme_dependencies']['payload'] === 'synthetic fixture; not captured') {
        exterior_exone_import_assert(count($manifest['theme_dependencies']) === 2 && $manifest['theme_dependencies']['files'] === [], 'Synthetic dependency record must contain no real files.');
    } else {
        exterior_exone_import_assert(is_string($manifest['theme_dependencies']['captured_utc'] ?? null) && is_string($manifest['theme_dependencies']['fingerprint'] ?? null), 'Missing dependency timestamp or hash.');
        exterior_exone_import_assert(preg_match('/\A\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+00:00\z/', $manifest['theme_dependencies']['captured_utc']) === 1, 'Invalid dependency timestamp.');
    }
    foreach ($manifest['theme_dependencies']['files'] as $dependency) {
        exterior_exone_import_assert(!array_diff(array_keys($dependency), ['relative_path', 'bytes', 'sha256']), 'Unknown dependency file field.');
        exterior_exone_import_relative($dependency['relative_path']);
        exterior_exone_import_assert(is_int($dependency['bytes']) && $dependency['bytes'] >= 0 && preg_match('/\A[a-f0-9]{64}\z/', $dependency['sha256']) === 1, 'Invalid dependency size or hash.');
    }
    if (isset($manifest['theme_dependencies']['fingerprint'])) {
        exterior_exone_import_assert(exterior_exone_import_hash($manifest['theme_dependencies']['files']) === $manifest['theme_dependencies']['fingerprint'], 'Theme dependency fingerprint mismatch.');
    }
    foreach ($manifest['unknown_meta'] as $unknown) {
        exterior_exone_import_assert(count($unknown) === 4 && !array_diff(array_keys($unknown), ['source_key', 'meta_key', 'count', 'status']) && is_string($unknown['source_key'] ?? null) && is_string($unknown['meta_key'] ?? null) && is_int($unknown['count'] ?? null) && $unknown['count'] > 0 && ($unknown['status'] ?? null) === 'needs_review; value-excluded' && !in_array($unknown['meta_key'], ['_hash', '_edit_lock', '_edit_last'], true), 'Unknown metadata report may only contain identity, key and count.');
    }
    exterior_exone_import_assert(is_array($manifest['excluded_status_counts']) && exterior_exone_import_is_list($manifest['excluded_status_counts']), 'Excluded-state counts must be a list.');
    $excludedStateKeys = [];
    foreach ($manifest['excluded_status_counts'] as $excludedState) {
        exterior_exone_import_assert(is_array($excludedState) && count($excludedState) === 3 && !array_diff(array_keys($excludedState), ['post_type', 'post_status', 'count']), 'Invalid excluded-state fields.');
        exterior_exone_import_assert(is_string($excludedState['post_type'] ?? null) && in_array($excludedState['post_type'], EXTERIOR_EXONE_IMPORT_TYPES, true), 'Invalid excluded-state post type.');
        exterior_exone_import_assert(is_string($excludedState['post_status'] ?? null) && in_array($excludedState['post_status'], EXTERIOR_EXONE_IMPORT_EXCLUDED_STATUSES, true), 'Invalid or unsupported excluded post state.');
        exterior_exone_import_assert(is_int($excludedState['count'] ?? null) && $excludedState['count'] >= 0, 'Excluded-state count must be a nonnegative integer.');
        $stateKey = $excludedState['post_type'] . ':' . $excludedState['post_status'];
        exterior_exone_import_assert(!isset($excludedStateKeys[$stateKey]), 'Duplicate excluded-state count.');
        $excludedStateKeys[$stateKey] = true;
    }
    $allowedFiles = ['manifest.json' => true, 'manifest.sha256' => true, 'summary.txt' => true, 'COMPLETE' => true];
    exterior_exone_import_no_symlinks($directory . '/COMPLETE');
    exterior_exone_import_assert(is_file($directory . '/COMPLETE') && file_get_contents($directory . '/COMPLETE') === $manifest['source_fingerprint'] . "\n", 'Package has no valid completion marker.');
    $counts = array_fill_keys(array_merge(EXTERIOR_EXONE_IMPORT_TYPES, ['term']), 0);
    $statusCounts = [];
    foreach ($manifest['records'] as $record) {
        exterior_exone_import_assert(is_array($record) && isset($counts[$record['kind'] ?? '']), 'Disallowed source entity, including columns or users.');
        $kind = $record['kind'];
        exterior_exone_import_assert(is_int($record['source_id']) && is_int($record['source_parent_id']) && $record['source_parent_id'] >= 0, 'Invalid source identifier type.');
        $key = $record['source_key'];
        exterior_exone_import_assert(!isset($keys[$key]) && $key === exterior_exone_import_key($kind === 'term' ? 'term' : 'post', $record['source_id'], $record['taxonomy'] ?? $kind), 'Duplicate or invalid source identity.');
        if ($kind === 'term') {
            exterior_exone_import_assert(in_array($record['taxonomy'], EXTERIOR_EXONE_IMPORT_TAXONOMIES, true), 'Disallowed taxonomy.');
        }
        $keys[$key] = $record;
        $counts[$kind]++;
        $hash = $record['content_sha256'];
        unset($record['content_sha256']);
        exterior_exone_import_assert(exterior_exone_import_hash($record) === $hash, 'Source record checksum mismatch.');
        $recordKeys = ['source_key', 'kind', 'source_id', 'source_parent_id', 'source_parent_type', 'source_term_taxonomy_id', 'taxonomy', 'parent_policy', 'data', 'meta', 'references', 'files'];
        exterior_exone_import_assert(!array_diff(array_keys($record), $recordKeys), 'Unknown record fields are not permitted.');
        $dataKeys = $kind === 'term' ? ['name', 'slug', 'description'] : ['post_status', 'post_name', 'post_title', 'post_content', 'post_excerpt', 'post_date', 'post_date_gmt', 'post_modified', 'post_modified_gmt', 'post_mime_type', 'comment_status', 'ping_status', 'menu_order'];
        exterior_exone_import_assert(!array_diff(array_keys($record['data']), $dataKeys), 'Disallowed post data field.');
        foreach ($record['data'] as $name => $value) {
            exterior_exone_import_assert($name === 'menu_order' ? is_int($value) : is_string($value), 'Invalid content field type.');
        }
        if ($kind !== 'term') {
            exterior_exone_import_assert(in_array($record['data']['post_status'] ?? null, EXTERIOR_EXONE_IMPORT_STATUSES, true), 'Disallowed payload post status.');
            $statusKey = $kind . ':' . $record['data']['post_status'];
            $statusCounts[$statusKey] = ($statusCounts[$statusKey] ?? 0) + 1;
        }
        exterior_exone_import_assert(!array_diff(array_keys($record['meta']), exterior_exone_import_meta_keys($kind)), 'Disallowed metadata field.');
        foreach ($record['meta'] as $values) {
            exterior_exone_import_assert(is_array($values) && exterior_exone_import_is_list($values), 'Metadata must contain value lists.');
            foreach ($values as $value) {
                exterior_exone_import_assert(is_string($value), 'Metadata storage values must be strings.');
                exterior_exone_import_decode($value);
            }
        }
        exterior_exone_import_assert($kind === 'attachment' || $record['files'] === [], 'Only attachments can own media files.');
        $foundSourceFiles = [];
        foreach ($record['files'] as $file) {
            exterior_exone_import_assert(!array_diff(array_keys($file), ['source_relative_path', 'package_path', 'bytes', 'sha256', 'role']) && is_int($file['bytes']) && $file['bytes'] >= 0 && preg_match('/\A[a-f0-9]{64}\z/', $file['sha256']) === 1, 'Invalid media file metadata.');
            $relative = exterior_exone_import_relative($file['package_path']);
            exterior_exone_import_assert(exterior_exone_import_starts($relative, 'media/' . $record['source_id'] . '/') && !isset($allowedFiles[$relative]), 'Duplicate or misplaced media file.');
            $full = $directory . '/' . $relative;
            exterior_exone_import_no_symlinks($full);
            exterior_exone_import_assert(is_file($full) && filesize($full) === $file['bytes'] && hash_file('sha256', $full) === $file['sha256'], 'Missing, truncated or changed media file.');
            $allowedFiles[$relative] = true;
            exterior_exone_import_relative($file['source_relative_path']);
            exterior_exone_import_assert(!isset($foundSourceFiles[$file['source_relative_path']]), 'Duplicate source media file.');
            $foundSourceFiles[$file['source_relative_path']] = true;
        }
        if ($kind === 'attachment') {
            $declared = array_keys(exterior_exone_import_media_paths($record));
            $actual = array_keys($foundSourceFiles);
            sort($declared);
            sort($actual);
            exterior_exone_import_assert($declared === $actual, 'Attachment metadata and file inventory differ.');
        }
    }
    foreach ($keys as $key => $record) {
        foreach ($record['references'] as $reference) {
            if (isset($reference['target'])) {
                exterior_exone_import_assert(isset($keys[$reference['target']]), 'Dangling source reference.');
                exterior_exone_import_assert($reference['target'] !== $key || $reference['field'] !== 'parent', 'Self-parent is not allowed.');
            } else {
                exterior_exone_import_assert((isset($reference['external_url']) || isset($reference['source_url']) || isset($reference['unresolved_media'])) && ($reference['policy'] ?? '') === 'retain-and-review', 'Invalid unresolved reference.');
            }
        }
        $seen = [$key => true];
        $current = $record;
        while ($current['parent_policy'] === 'remap') {
            $parents = array_values(array_filter($current['references'], static fn(array $ref): bool => $ref['field'] === 'parent'));
            exterior_exone_import_assert(count($parents) === 1, 'Exactly one remapped parent is required.');
            $target = $parents[0]['target'];
            exterior_exone_import_assert(!isset($seen[$target]), 'Parent cycle detected.');
            $seen[$target] = true;
            $current = $keys[$target];
        }
    }
    foreach ($manifest['unknown_meta'] as $unknown) {
        exterior_exone_import_assert(isset($keys[$unknown['source_key']]) && !in_array($unknown['meta_key'], exterior_exone_import_meta_keys($keys[$unknown['source_key']]['kind']), true), 'Invalid unknown metadata identity.');
    }
    exterior_exone_import_semantics($keys);
    exterior_exone_import_assert(exterior_exone_import_hash(exterior_exone_import_analysis($keys)) === exterior_exone_import_hash($manifest['analysis']), 'Analysis does not match package records.');
    // 元値から参照を再構築して、参照一覧だけを落とした入力も検出する。
    $rebuild = ['posts' => [], 'meta' => [], 'terms' => [], 'relationships' => []];
    foreach ($keys as $record) {
        if ($record['kind'] === 'term') {
            $rebuild['terms'][] = ['term_id' => $record['source_id'], 'term_taxonomy_id' => $record['source_term_taxonomy_id'], 'taxonomy' => $record['taxonomy'], 'parent' => $record['source_parent_id'], 'name' => $record['data']['name'], 'slug' => $record['data']['slug'], 'description' => $record['data']['description']];
        } else {
            $rebuild['posts'][] = $record['data'] + ['ID' => $record['source_id'], 'post_type' => $record['kind'], 'post_parent' => $record['source_parent_id'], 'source_parent_type' => $record['source_parent_type']];
            foreach ($record['meta'] as $name => $values) {
                foreach ($values as $value) {
                    $rebuild['meta'][] = ['post_id' => $record['source_id'], 'meta_key' => $name, 'meta_value' => $value];
                }
            }
            foreach ($record['references'] as $ref) {
                if ($ref['field'] === 'term_relationship') {
                    $rebuild['relationships'][] = ['object_id' => $record['source_id'], 'term_taxonomy_id' => $keys[$ref['target']]['source_term_taxonomy_id'], 'term_order' => $ref['order']];
                }
            }
        }
    }
    $rebuiltRecords = exterior_exone_import_records($rebuild);
    $originalKeys = array_keys($keys);
    sort($originalKeys, SORT_STRING);
    exterior_exone_import_assert(array_keys($rebuiltRecords) === $originalKeys, 'Reconstructed source record set differs from the manifest.');
    foreach ($rebuiltRecords as $key => $rebuilt) {
        exterior_exone_import_assert(exterior_exone_import_hash($rebuilt['references']) === exterior_exone_import_hash($keys[$key]['references']) && $rebuilt['parent_policy'] === $keys[$key]['parent_policy'], 'Stored references differ from the source data.');
    }
    exterior_exone_import_assert($counts === $manifest['counts'] || exterior_exone_import_canonical($counts) === exterior_exone_import_canonical($manifest['counts']), 'Manifest counts do not match.');
    exterior_exone_import_assert(exterior_exone_import_canonical($statusCounts) === exterior_exone_import_canonical($manifest['status_counts']), 'Status counts do not match the payload.');
    exterior_exone_import_assert(exterior_exone_import_hash($manifest['records']) === $manifest['source_fingerprint'], 'Source fingerprint mismatch.');
    exterior_exone_import_no_symlinks($directory . '/summary.txt');
    exterior_exone_import_assert(is_file($directory . '/summary.txt') && file_get_contents($directory . '/summary.txt') === exterior_exone_import_summary($manifest), 'Summary content mismatch.');
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
    exterior_exone_import_assert((fileperms($directory) & 0777) === 0700, 'Package root permissions must be 0700.');
    foreach ($iterator as $file) {
        exterior_exone_import_assert(!$file->isLink(), 'Package must not contain symbolic links.');
        $relative = substr($file->getPathname(), strlen($directory) + 1);
        exterior_exone_import_assert(($file->getPerms() & 0777) === ($file->isDir() ? 0700 : 0600), 'Package permissions must be 0700/0600.');
        if (!$file->isDir()) {
            exterior_exone_import_assert($file->isFile() && isset($allowedFiles[$relative]), 'Package contains an unlisted file.');
        }
    }
    return $manifest;
}

/** destination inventory だけを読み、予定と衝突を出す。更新/削除を計画しない。 */

function exterior_exone_import_semantics(array $records): array {
    $paths = [];
    $roles = [];
    foreach ($records as $key => $record) {
        exterior_exone_import_assert(in_array($record['parent_policy'], ['root', 'remap', 'excluded-parent-destination-zero'], true), 'Invalid parent policy.');
        exterior_exone_import_assert($record['parent_policy'] !== 'excluded-parent-destination-zero' || $record['kind'] === 'attachment', 'Only excluded media parents may be detached.');
        foreach ($record['references'] as $ref) {
            if (!isset($ref['target'])) {
                continue;
            }
            $target = $records[$ref['target']] ?? null;
            exterior_exone_import_assert($target !== null, 'Dangling reference.');
            if ($ref['field'] === 'parent' && $record['kind'] !== 'attachment') {
                exterior_exone_import_assert($target['kind'] === $record['kind'] && ($record['kind'] !== 'term' || $target['taxonomy'] === $record['taxonomy']), 'Parent type or taxonomy mismatch.');
            }
            if ($ref['field'] === 'term_relationship') {
                exterior_exone_import_assert($record['kind'] === 'works' && $target['kind'] === 'term', 'Invalid taxonomy reference.');
            }
            if (exterior_exone_import_contains($ref['field'], 'image') || exterior_exone_import_contains($ref['field'], 'gallery') || exterior_exone_import_contains($ref['field'], 'thumbnail') || exterior_exone_import_ends($ref['field'], ':media-url')) {
                exterior_exone_import_assert($target['kind'] === 'attachment', 'Media reference points to a non-attachment.');
            }
            if (exterior_exone_import_ends($ref['field'], ':form-id')) {
                exterior_exone_import_assert($target['kind'] === 'wpcf7_contact_form', 'Form reference points to a non-form.');
            }
        }
        if ($record['kind'] === 'page') {
            $parts = [$record['data']['post_name']];
            $current = $record;
            $seen = [$key => true];
            while ($current['parent_policy'] === 'remap') {
                $parents = array_values(array_filter($current['references'], static fn(array $ref): bool => $ref['field'] === 'parent'));
                exterior_exone_import_assert(count($parents) === 1 && !isset($seen[$parents[0]['target']]), 'Missing or cyclic page parent.');
                $seen[$parents[0]['target']] = true;
                $current = $records[$parents[0]['target']];
                array_unshift($parts, $current['data']['post_name']);
            }
            // 未保存スラッグの下書きは公開URLとして扱わない。
            if (!in_array('', $parts, true)) {
                $path = '/' . implode('/', $parts) . '/';
                exterior_exone_import_assert(!isset($paths[$path]), 'Duplicate full page path.');
                $paths[$path] = $key;
            }
        }
        if ($record['kind'] === 'wpcf7_contact_form') {
            $contact = exterior_exone_import_meta_value($record, '_exterior_exone_contact_form');
            $recruit = exterior_exone_import_meta_value($record, '_exterior_exone_recruit_form');
            exterior_exone_import_assert(!($contact && $recruit), 'Form has conflicting role markers.');
            $role = $contact ?: $recruit;
            exterior_exone_import_assert(in_array($role, ['personal', 'corporate', 'entry'], true) && !isset($roles[$role]), 'Missing or duplicate form role.');
            $roles[$role] = $key;
        }
    }
    return ['page_paths' => $paths, 'form_roles' => $roles];
}


function exterior_exone_import_summary(array $manifest): string {
    $files = 0;
    $bytes = 0;
    foreach ($manifest['records'] as $record) {
        foreach ($record['files'] as $file) {
            $files++;
            $bytes += $file['bytes'];
        }
    }
    return "S3 preparation only; production has not been modified.\n" .
        'Source counts: ' . json_encode(exterior_exone_import_canonical($manifest['counts']), JSON_THROW_ON_ERROR) . "\n" .
        'State counts: ' . json_encode(exterior_exone_import_canonical($manifest['status_counts']), JSON_THROW_ON_ERROR) . "\n" .
        "Media files: $files; bytes: $bytes; every file checked with SHA-256.\n" .
        'Excluded local columns: ' . $manifest['excluded_column_count'] . "; users/options/comments/submissions excluded.\n" .
        'Unknown metadata groups requiring review: ' . count($manifest['unknown_meta']) . " (values excluded).\n" .
        "Database writes: 0; form sends: 0; comments migrated: 0.\n" .
        "Planned comments: closed; ping: closed; count: 0.\n" .
        "Theme dependency files are listed only; separate theme copy required.\n" .
        "Importer, runtime adapter, live theme switching, SEO and production compatibility remain unverified.\n";
}


function exterior_exone_import_analysis(array $records): array {
    $semantics = exterior_exone_import_semantics($records);
    $usage = [];
    $review = [];
    $destinations = [];
    foreach ($records as $key => $record) {
        $destinations[$key] = $record['kind'] === 'term' ? 'exr_' . $record['taxonomy'] : exterior_exone_import_destination_type($record['kind']);
        foreach ($record['references'] as $reference) {
            if (isset($reference['target']) && $records[$reference['target']]['kind'] === 'attachment' && $reference['field'] !== 'parent') {
                $usage[$reference['target']][] = ['source_key' => $key, 'field' => $reference['field']];
            }
            if (($reference['policy'] ?? '') === 'retain-and-review') {
                $review[] = ['source_key' => $key, 'code' => $reference['field'], 'status' => 'needs_review; retain source reference without retrieval or automatic replacement'];
            }
        }
        if ($record['kind'] !== 'term') {
            $content = $record['data']['post_content'];
            if (preg_match('/<script\b|<!--\s+wp:(?!image\b|media-text\b|cover\b)|\[[a-z][a-z0-9_-]*/i', $content)) {
                $review[] = ['source_key' => $key, 'code' => 'body-markup-or-shortcode-review', 'status' => 'needs_review; only listed reference syntaxes were resolved'];
            }
            if ($record['kind'] === 'wpcf7_contact_form') {
                $review[] = ['source_key' => $key, 'code' => 'mail-and-additional-settings-review', 'status' => 'needs_review; data only, no sending or external execution'];
            }
        }
    }
    $media = [];
    foreach ($records as $key => $record) {
        if ($record['kind'] === 'attachment') {
            $media[$key] = ['payload' => 'included', 'references' => $usage[$key] ?? [], 'classification' => isset($usage[$key]) ? 'referenced-by-new-content' : (($record['source_parent_type'] ?? '') === 'column' ? 'excluded-column-parent; other-use-uninspected' : 'needs_review; no-exported-content-reference'), 'destination_parent' => $record['parent_policy'] === 'remap' ? 'resolve-source-key' : 0];
        }
    }
    return $semantics + ['destination_types' => $destinations, 'media_usage' => $media, 'review' => $review, 'comments_migrated' => 0, 'destination_comments' => ['comment_status' => 'closed', 'ping_status' => 'closed', 'comment_count' => 0], 'author_policy' => 'destination-user-assignment-required'];
}
