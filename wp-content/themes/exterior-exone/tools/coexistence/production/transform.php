<?php
/** 保存先IDを受け取り、新用の値だけを変換する。DB・HTTPアクセスなし。 */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/reader.php';

const EXTERIOR_EXONE_PRODUCTION_DATASET = 'exterior-exone-renewal:v1';
const EXTERIOR_EXONE_PRODUCTION_STATE = 'exr_stored';

function exterior_exone_production_storage_type(string $kind): string {
    // CF7はstatusだけでは旧REST送信経路から隔離できないため、保存タイプも分ける。
    return $kind === 'wpcf7_contact_form' ? 'exr_form' : exterior_exone_production_destination_type($kind);
}

function exterior_exone_production_storage_status(string $kind): string {
    // WP core treats an attachment with an unknown status as publish. Native private
    // closes its attachment page while the explicitly referenced image file remains usable.
    return $kind === 'attachment' ? 'private' : EXTERIOR_EXONE_PRODUCTION_STATE;
}

function exterior_exone_production_context(array $manifest, array $ids, string $home, string $siteurl, int $author): array {
    exterior_exone_production_assert($home === 'https://exterior-exone.com' && $siteurl === 'https://exterior-exone.com/retest', 'Only the fixed production URLs are permitted.');
    exterior_exone_production_assert($author > 0, 'A verified destination author is required.');
    $context = ['manifest' => $manifest, 'records' => [], 'ids' => $ids, 'post_ids' => [], 'urls' => [], 'files' => [], 'home' => $home, 'siteurl' => $siteurl, 'author' => $author];
    $seenPostIds = [];
    $seenTermIds = [];
    $seenTermTaxonomyIds = [];
    foreach ($manifest['records'] as $record) {
        $key = $record['source_key'];
        exterior_exone_production_assert(isset($ids[$key]) && is_array($ids[$key]) && is_int($ids[$key]['id'] ?? null) && $ids[$key]['id'] > 0, 'Every source needs an explicit destination ID.');
        $seen = $record['kind'] === 'term' ? $seenTermIds : $seenPostIds;
        exterior_exone_production_assert(!isset($seen[$ids[$key]['id']]), 'Duplicate destination ID.');
        if ($record['kind'] === 'term') {
            $seenTermIds[$ids[$key]['id']] = true;
            exterior_exone_production_fields($ids[$key], ['id', 'term_taxonomy_id'], 'destination term mapping');
            exterior_exone_production_assert(is_int($ids[$key]['term_taxonomy_id']) && $ids[$key]['term_taxonomy_id'] > 0, 'Missing destination taxonomy ID.');
            exterior_exone_production_assert(!isset($seenTermTaxonomyIds[$ids[$key]['term_taxonomy_id']]), 'Duplicate destination taxonomy ID.');
            $seenTermTaxonomyIds[$ids[$key]['term_taxonomy_id']] = true;
        } else {
            exterior_exone_production_fields($ids[$key], ['id'], 'destination post mapping');
            $seenPostIds[$ids[$key]['id']] = true;
            $context['post_ids'][$record['source_id']] = $ids[$key]['id'];
        }
        $context['records'][$key] = $record;
        foreach ($record['files'] as $file) {
            $relative = 'exone-renewal/' . $manifest['source_fingerprint'] . '/' . $record['source_id'] . '/' . $file['source_relative_path'];
            exterior_exone_production_relative($relative);
            $context['files'][$key][$file['source_relative_path']] = $relative;
            $url = $siteurl . '/wp-content/uploads/' . implode('/', array_map('rawurlencode', explode('/', $relative)));
            exterior_exone_production_assert(!isset($context['urls'][$file['source_relative_path']]) || $context['urls'][$file['source_relative_path']] === $url, 'Ambiguous source media URL.');
            $context['urls'][$file['source_relative_path']] = $url;
        }
    }
    exterior_exone_production_assert(count($ids) === count($context['records']), 'Unexpected destination mappings.');
    return $context;
}

function exterior_exone_production_mapped_id($id, array $context, string $kind): int {
    exterior_exone_production_assert(is_int($id) || is_string($id) && preg_match('/\A[0-9]+\z/', $id) === 1, 'Invalid source reference ID.');
    if ((int) $id === 0) { return 0; }
    $key = exterior_exone_production_key('post', (int) $id, $kind);
    exterior_exone_production_assert(isset($context['records'][$key], $context['ids'][$key]), 'Unmapped source reference.');
    return $context['ids'][$key]['id'];
}

function exterior_exone_production_url(string $url, array $context): string {
    $parts = parse_url($url);
    exterior_exone_production_assert($parts !== false, 'Invalid reference URL.');
    $local = !isset($parts['host']) || $parts['host'] === 'exterior-exone-renewal.local';
    $path = $parts['path'] ?? '';
    $uploadPosition = strpos($path, '/wp-content/uploads/');
    if ($local && $uploadPosition !== false) {
        $relative = rawurldecode(substr($path, $uploadPosition + strlen('/wp-content/uploads/')));
        exterior_exone_production_assert(isset($context['urls'][$relative]), 'Unmapped local media URL.');
        return $context['urls'][$relative] . (isset($parts['query']) ? '?' . $parts['query'] : '') . (isset($parts['fragment']) ? '#' . $parts['fragment'] : '');
    }
    if (($parts['host'] ?? '') === 'exterior-exone-renewal.local') {
        // 既知の新ページへのリンクだけを解決。未知の外部参照は保持して台帳へ。
        $normalized = '/' . trim($path, '/') . '/';
        if (isset($context['manifest']['analysis']['page_paths'][$normalized]) || $path === '/') {
            return $context['home'] . $path . (isset($parts['query']) ? '?' . $parts['query'] : '') . (isset($parts['fragment']) ? '#' . $parts['fragment'] : '');
        }
    }
    return $url;
}

function exterior_exone_production_text(string $text, array $context): string {
    $text = preg_replace_callback('/\b(wp-image-|attachment_)(\d+)\b/', static function (array $m) use ($context): string {
        return $m[1] . exterior_exone_production_mapped_id($m[2], $context, 'attachment');
    }, $text);
    $text = preg_replace_callback('/\[gallery\b[^\]]*\]/i', static function (array $m) use ($context): string {
        return preg_replace_callback('/(\bids\s*=\s*)(["\x27])([0-9,\s]+)\2/i', static function (array $a) use ($context): string {
            $value = preg_replace_callback('/\d+/', static function (array $n) use ($context): string { return (string) exterior_exone_production_mapped_id($n[0], $context, 'attachment'); }, $a[3]);
            return $a[1] . $a[2] . $value . $a[2];
        }, $m[0]);
    }, $text);
    $text = preg_replace_callback('/(<!--\s+wp:(?:image|media-text|cover)\s+)(\{.*?\})(\s*(?:\/)?-->)/s', static function (array $m) use ($context): string {
        exterior_exone_production_unique_json_keys($m[2]);
        $attributes = json_decode($m[2], true, 64, JSON_THROW_ON_ERROR);
        foreach (['id', 'mediaId'] as $name) {
            if (isset($attributes[$name])) { $attributes[$name] = exterior_exone_production_mapped_id($attributes[$name], $context, 'attachment'); }
        }
        $attributes = exterior_exone_production_walk_strings($attributes, static function (string $value) use ($context): string { return exterior_exone_production_url($value, $context); });
        return $m[1] . json_encode($attributes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . $m[3];
    }, $text);
    $text = preg_replace_callback('/\[contact-form-7\b[^\]]*\]/i', static function (array $m) use ($context): string {
        return preg_replace_callback('/(\bid\s*=\s*)(?:"([0-9]+)"|\x27([0-9]+)\x27|([0-9]+))(?=\s|\])/i', static function (array $a) use ($context): string {
            $id = $a[2] !== '' ? $a[2] : (($a[3] ?? '') !== '' ? $a[3] : $a[4]);
            $quote = $a[2] !== '' ? '"' : (($a[3] ?? '') !== '' ? "'" : '');
            return $a[1] . $quote . exterior_exone_production_mapped_id($id, $context, 'wpcf7_contact_form') . $quote;
        }, $m[0]);
    }, $text);
    // URLトークン単位で処理する。HTML/srcsetの区切りやquery/fragmentは維持する。
    $text = preg_replace_callback('~(?:(?:https?:)?//[^\s"\x27<>,)]+)?/wp-content/uploads/[^\s"\x27<>,)]+|https?://exterior-exone-renewal\.local/[^\s"\x27<>,)]*~u', static function (array $m) use ($context): string {
        $decoded = html_entity_decode($m[0], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = exterior_exone_production_url($decoded, $context);
        return strpos($m[0], '&amp;') !== false ? str_replace('&', '&amp;', $value) : $value;
    }, $text);
    exterior_exone_production_assert(is_string($text), 'Reference transformation failed.');
    return $text;
}

function exterior_exone_production_walk_strings($value, callable $transform, int $depth = 0) {
    exterior_exone_production_assert($depth < 40 && !is_object($value) && !is_resource($value), 'Unsupported metadata structure.');
    if (is_string($value)) { return $transform($value); }
    if (is_array($value)) {
        foreach ($value as &$child) { $child = exterior_exone_production_walk_strings($child, $transform, $depth + 1); }
        unset($child);
    }
    return $value;
}

function exterior_exone_production_store_value($source, string $original): string {
    // 直列化文字列の長さを再計算し、元の保存形式を保つ。
    $serialized = preg_match('/\A(?:a|s|i|b|d):|\AN;\z/', $original) === 1;
    return $serialized ? serialize($source) : (string) $source;
}

function exterior_exone_production_media_meta(string $name, $value, array $record, array $context) {
    $paths = $context['files'][$record['source_key']] ?? [];
    $path = static function (string $old) use ($paths): string {
        exterior_exone_production_assert(isset($paths[$old]), 'Attachment metadata path is not in the payload.');
        return $paths[$old];
    };
    if ($name === '_wp_attached_file') { return $path($value); }
    if (!is_array($value)) { return $value; }
    if ($name === '_wp_attachment_metadata') {
        $base = $value['file'] ?? exterior_exone_production_meta_value($record, '_wp_attached_file');
        $directory = dirname($base) === '.' ? '' : dirname($base) . '/';
        $newBase = $path($base);
        $value['file'] = $newBase;
        if (!empty($value['original_image'])) { $value['original_image'] = basename($path($directory . $value['original_image'])); }
        foreach (($value['sizes'] ?? []) as $sizeName => $size) {
            $value['sizes'][$sizeName]['file'] = basename($path($directory . $size['file']));
            foreach (($size['sources'] ?? []) as $format => $source) {
                if (isset($source['file'])) { $value['sizes'][$sizeName]['sources'][$format]['file'] = basename($path($directory . $source['file'])); }
            }
        }
    }
    if ($name === '_wp_attachment_backup_sizes') {
        $base = exterior_exone_production_meta_value($record, '_wp_attached_file');
        $directory = dirname($base) === '.' ? '' : dirname($base) . '/';
        foreach ($value as &$backup) { $backup['file'] = basename($path($directory . $backup['file'])); }
        unset($backup);
    }
    return $value;
}

function exterior_exone_production_transform(array $record, array $context): array {
    $key = $record['source_key'];
    $id = $context['ids'][$key]['id'];
    $meta = [];
    foreach ($record['meta'] as $name => $values) {
        if (in_array($name, ['_exterior_exone_contact_form', '_exterior_exone_recruit_form'], true)) { continue; }
        foreach ($values as $raw) {
            $value = exterior_exone_production_decode($raw);
            if ($name === '_thumbnail_id' || strpos($name, '_exterior_exone_works_image_') === 0 || $name === '_exterior_exone_works_gallery') {
                $map = static function ($old) use ($context) { return is_int($old) ? exterior_exone_production_mapped_id($old, $context, 'attachment') : (string) exterior_exone_production_mapped_id(trim((string) $old) === '' ? '0' : trim((string) $old), $context, 'attachment'); };
                if (is_array($value)) { $value = array_map($map, $value); }
                elseif (is_int($value)) { $value = $map($value); }
                elseif ($value !== '' && $value !== null && $value !== false) { $value = implode(',', array_map($map, explode(',', (string) $value))); }
            } elseif (in_array($name, ['_wp_attached_file', '_wp_attachment_metadata', '_wp_attachment_backup_sizes'], true)) {
                $value = exterior_exone_production_media_meta($name, $value, $record, $context);
            } else {
                $value = exterior_exone_production_walk_strings($value, static function (string $text) use ($context): string { return exterior_exone_production_text($text, $context); });
            }
            $meta[$name][] = exterior_exone_production_store_value($value, $raw);
        }
    }
    $meta['_exr_dataset'] = [EXTERIOR_EXONE_PRODUCTION_DATASET];
    $meta['_exr_source_key'] = [$key];
    $meta['_exr_source_hash'] = [$record['content_sha256']];
    $meta['_exr_source_fingerprint'] = [$context['manifest']['source_fingerprint']];
    $meta['_exr_import_state'] = ['complete'];
    $meta['_exr_source_parent_id'] = [(string) $record['source_parent_id']];
    $parent = 0;
    $relationships = [];
    foreach ($record['references'] as $ref) {
        if ($ref['field'] === 'parent') { $parent = $context['ids'][$ref['target']]['id']; }
        if ($ref['field'] === 'term_relationship') { $relationships[] = ['term_taxonomy_id' => $context['ids'][$ref['target']]['term_taxonomy_id'], 'term_order' => $ref['order']]; }
    }
    if ($record['kind'] === 'term') {
        $data = $record['data'] + ['term_group' => 0, 'taxonomy' => 'exr_' . $record['taxonomy'], 'parent' => $parent, 'count' => 0];
    } else {
        $data = $record['data'];
        $data['post_type'] = exterior_exone_production_storage_type($record['kind']);
        $data['post_parent'] = $parent;
        $data['post_author'] = $context['author'];
        $data['post_content'] = exterior_exone_production_text($data['post_content'], $context);
        $data['post_excerpt'] = exterior_exone_production_text($data['post_excerpt'], $context);
        $data['post_status'] = exterior_exone_production_storage_status($record['kind']);
        $data['comment_status'] = 'closed';
        $data['ping_status'] = 'closed';
        $data['comment_count'] = 0;
        $data['post_password'] = '';
        $data['to_ping'] = '';
        $data['pinged'] = '';
        $data['post_content_filtered'] = '';
        $data['guid'] = 'urn:exone-renewal:' . $record['kind'] . ':' . $id;
        $meta['_exr_source_status'] = [$record['data']['post_status']];
        $meta['_exr_source_parent_type'] = [$record['source_parent_type']];
        if ($record['kind'] === 'attachment') {
            $data['guid'] = $context['urls'][exterior_exone_production_meta_value($record, '_wp_attached_file')];
        }
        if ($record['kind'] === 'page') {
            $path = array_search($key, $context['manifest']['analysis']['page_paths'], true);
            if ($path !== false) { $meta['_exr_source_path'] = [$path]; }
        }
        if ($record['kind'] === 'wpcf7_contact_form') {
            $role = array_search($key, $context['manifest']['analysis']['form_roles'], true);
            exterior_exone_production_assert($role !== false, 'Missing dedicated form role.');
            $meta['_exr_form_role'] = [$role];
        }
    }
    $result = ['source_key' => $key, 'kind' => $record['kind'], 'data' => $data, 'meta' => $meta, 'relationships' => $relationships];
    $result['destination_sha256'] = exterior_exone_production_hash($result);
    return $result;
}

function exterior_exone_production_ledger(array $manifest, array $context): array {
    $items = [];
    foreach ($manifest['unknown_meta'] as $item) {
        $items[] = ['source_key' => $item['source_key'], 'code' => $item['meta_key'], 'decision' => $item['meta_key'] === '_wp_old_date' ? 'excluded-history-metadata' : 'unresolved', 'reason' => 'Value was excluded by the export and is not fetched or restored.'];
    }
    foreach ($manifest['analysis']['review'] as $item) {
        $decision = 'retained-unverified';
        $reason = 'Preserved as data; isolated rendering remains required.';
        if ($item['code'] === 'mail-and-additional-settings-review') {
            $decision = 'isolated-settings-unverified';
            $reason = 'Stored without executing CF7; network/mail blocking and form behavior require isolated runtime tests.';
        } elseif (strpos($item['code'], ':local-link') !== false) {
            $record = $context['records'][$item['source_key']];
            $resolved = true;
            foreach ($record['references'] as $ref) {
                if ($ref['field'] === $item['code'] && isset($ref['external_url']) && exterior_exone_production_url($ref['external_url'], $context) === $ref['external_url']) { $resolved = false; }
            }
            $decision = $resolved ? 'mapped-known-page-link' : 'retained-unresolved-link';
            $reason = $resolved ? 'Exact source page path mapped to isolated home; route behavior awaits adapter tests.' : 'No exported page mapping; preserved without retrieval.';
        }
        $items[] = ['source_key' => $item['source_key'], 'code' => $item['code'], 'decision' => $decision, 'reason' => $reason];
    }
    return ['schema_version' => 1, 'production_ready' => false, 'items' => $items];
}
