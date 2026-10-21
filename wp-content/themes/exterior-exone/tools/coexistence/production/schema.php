<?php
/** schema 1 の入力を型と許可キーで制限する。 */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

function exterior_exone_production_fields($value, array $keys, string $label): void {
    exterior_exone_production_assert(is_array($value), 'Invalid ' . $label . ' structure.');
    $actual = array_keys($value);
    sort($actual, SORT_STRING);
    sort($keys, SORT_STRING);
    exterior_exone_production_assert($actual === $keys, 'Missing or unknown ' . $label . ' fields.');
}

function exterior_exone_production_list($value, string $label): void {
    exterior_exone_production_assert(is_array($value) && exterior_exone_production_is_list($value), 'Invalid ' . $label . ' list.');
}

function exterior_exone_production_digest($value): void {
    exterior_exone_production_assert(is_string($value) && preg_match('/\A[a-f0-9]{64}\z/', $value) === 1, 'Invalid SHA-256.');
}

function exterior_exone_production_timestamp($value): void {
    exterior_exone_production_assert(is_string($value), 'Invalid timestamp type.');
    $date = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i:sP', $value);
    exterior_exone_production_assert($date !== false && $date->format('Y-m-d\TH:i:sP') === $value && substr($value, -6) === '+00:00', 'Invalid UTC timestamp.');
}

function exterior_exone_production_schema(array $manifest): void {
    exterior_exone_production_fields($manifest, ['schema_version', 'tool_version', 'run_id', 'state', 'package_type', 'origin', 'created_utc', 'production_ready', 'preparation_only', 'scope', 'snapshot', 'theme_dependencies', 'unknown_meta', 'excluded_column_count', 'excluded_status_counts', 'counts', 'status_counts', 'records', 'source_fingerprint', 'analysis'], 'manifest');
    foreach (['tool_version', 'run_id', 'state', 'package_type', 'origin'] as $name) {
        exterior_exone_production_assert(is_string($manifest[$name]) && $manifest[$name] !== '', 'Invalid manifest string.');
    }
    exterior_exone_production_timestamp($manifest['created_utc']);
    exterior_exone_production_digest($manifest['source_fingerprint']);
    exterior_exone_production_assert(is_int($manifest['excluded_column_count']) && $manifest['excluded_column_count'] >= 0, 'Invalid excluded column count.');
    exterior_exone_production_fields($manifest['scope'], ['columns', 'users', 'options', 'comments', 'received_submissions', 'cf7_hash_and_webhooks', 'all_attachments'], 'scope');
    exterior_exone_production_assert(is_array($manifest['snapshot']), 'Invalid snapshot.');
    $synthetic = ($manifest['snapshot']['mode'] ?? null) === 'synthetic; no database';
    exterior_exone_production_fields($manifest['snapshot'], $synthetic ? ['mode'] : ['mode', 'started_utc', 'completed_utc', 'local_site_id', 'database'], 'snapshot');
    if (!$synthetic) {
        exterior_exone_production_timestamp($manifest['snapshot']['started_utc']);
        exterior_exone_production_timestamp($manifest['snapshot']['completed_utc']);
        exterior_exone_production_assert($manifest['snapshot']['started_utc'] <= $manifest['snapshot']['completed_utc'], 'Snapshot timestamps are reversed.');
    }
    $dependencies = $manifest['theme_dependencies'];
    exterior_exone_production_assert(is_array($dependencies), 'Invalid dependencies.');
    exterior_exone_production_fields($dependencies, ($dependencies['payload'] ?? null) === 'synthetic fixture; not captured' ? ['files', 'payload'] : ['captured_utc', 'files', 'fingerprint', 'payload'], 'dependencies');
    exterior_exone_production_list($dependencies['files'], 'dependency files');
    if (isset($dependencies['captured_utc'])) {
        exterior_exone_production_timestamp($dependencies['captured_utc']);
        exterior_exone_production_digest($dependencies['fingerprint']);
    }
    $paths = [];
    foreach ($dependencies['files'] as $file) {
        exterior_exone_production_fields($file, ['relative_path', 'bytes', 'sha256'], 'dependency file');
        exterior_exone_production_assert(is_string($file['relative_path']) && !isset($paths[$file['relative_path']]), 'Invalid or duplicate dependency path.');
        $paths[$file['relative_path']] = true;
        exterior_exone_production_relative($file['relative_path']);
        exterior_exone_production_assert(is_int($file['bytes']) && $file['bytes'] >= 0, 'Invalid dependency size.');
        exterior_exone_production_digest($file['sha256']);
    }
    exterior_exone_production_fields($manifest['counts'], array_merge(EXTERIOR_EXONE_PRODUCTION_TYPES, ['term']), 'counts');
    foreach ($manifest['counts'] as $count) {
        exterior_exone_production_assert(is_int($count) && $count >= 0, 'Invalid entity count.');
    }
    exterior_exone_production_assert(is_array($manifest['status_counts']), 'Invalid status counts.');
    foreach ($manifest['status_counts'] as $key => $count) {
        exterior_exone_production_assert(is_string($key) && preg_match('/\A(works|news|page|attachment|wpcf7_contact_form):(publish|draft|pending|private|future|inherit)\z/', $key) === 1 && is_int($count) && $count > 0, 'Invalid status count.');
    }
    exterior_exone_production_list($manifest['excluded_status_counts'], 'excluded statuses');
    exterior_exone_production_list($manifest['unknown_meta'], 'unknown metadata');
    foreach ($manifest['unknown_meta'] as $unknown) {
        exterior_exone_production_fields($unknown, ['source_key', 'meta_key', 'count', 'status'], 'unknown metadata');
    }
    exterior_exone_production_list($manifest['records'], 'records');
    foreach ($manifest['records'] as $record) {
        exterior_exone_production_assert(is_array($record) && is_string($record['kind'] ?? null) && in_array($record['kind'], array_merge(EXTERIOR_EXONE_PRODUCTION_TYPES, ['term']), true), 'Disallowed entity kind.');
        $term = $record['kind'] === 'term';
        $fields = ['content_sha256', 'source_key', 'kind', 'source_id', 'source_parent_id', 'parent_policy', 'data', 'meta', 'references', 'files'];
        exterior_exone_production_fields($record, array_merge($fields, $term ? ['taxonomy', 'source_term_taxonomy_id'] : ['source_parent_type']), 'record');
        exterior_exone_production_assert(is_int($record['source_id']) && $record['source_id'] > 0 && is_int($record['source_parent_id']) && $record['source_parent_id'] >= 0 && is_string($record['source_key']) && is_string($record['parent_policy']), 'Invalid record identity.');
        exterior_exone_production_digest($record['content_sha256']);
        if ($term) {
            exterior_exone_production_assert(is_string($record['taxonomy']) && is_int($record['source_term_taxonomy_id']) && $record['source_term_taxonomy_id'] > 0, 'Invalid taxonomy identity.');
        } else {
            exterior_exone_production_assert(is_string($record['source_parent_type']), 'Invalid parent type.');
        }
        exterior_exone_production_fields($record['data'], $term ? ['name', 'slug', 'description'] : ['post_status', 'post_name', 'post_title', 'post_content', 'post_excerpt', 'post_date', 'post_date_gmt', 'post_modified', 'post_modified_gmt', 'post_mime_type', 'comment_status', 'ping_status', 'menu_order'], 'content');
        foreach ($record['data'] as $key => $value) {
            exterior_exone_production_assert($key === 'menu_order' ? is_int($value) : is_string($value), 'Invalid content value.');
        }
        exterior_exone_production_assert(is_array($record['meta']), 'Invalid metadata.');
        foreach ($record['meta'] as $key => $values) {
            exterior_exone_production_assert(is_string($key), 'Invalid metadata key.');
            exterior_exone_production_list($values, 'metadata values');
            exterior_exone_production_assert(count($values) > 0, 'Empty metadata group.');
        }
        exterior_exone_production_list($record['references'], 'references');
        foreach ($record['references'] as $ref) {
            exterior_exone_production_assert(is_array($ref) && is_string($ref['field'] ?? null), 'Invalid reference.');
            if (isset($ref['target'])) {
                $keys = ['field', 'target'];
                if (isset($ref['order'])) { $keys[] = 'order'; }
                if (isset($ref['source_relative_path'])) { $keys[] = 'source_relative_path'; }
                exterior_exone_production_fields($ref, $keys, 'resolved reference');
                exterior_exone_production_assert(is_string($ref['target']) && (!isset($ref['order']) || is_int($ref['order']) && $ref['order'] >= 0) && (!isset($ref['source_relative_path']) || is_string($ref['source_relative_path'])), 'Invalid reference value.');
            } else {
                $choices = array_intersect(array_keys($ref), ['external_url', 'source_url', 'unresolved_media']);
                exterior_exone_production_assert(count($choices) === 1, 'Ambiguous unresolved reference.');
                $name = reset($choices);
                exterior_exone_production_fields($ref, ['field', $name, 'policy'], 'unresolved reference');
                exterior_exone_production_assert(is_string($ref[$name]) && $ref['policy'] === 'retain-and-review', 'Invalid unresolved reference value.');
            }
        }
        exterior_exone_production_list($record['files'], 'media files');
        foreach ($record['files'] as $file) {
            exterior_exone_production_fields($file, ['source_relative_path', 'package_path', 'bytes', 'sha256', 'role'], 'media file');
            exterior_exone_production_assert(is_string($file['source_relative_path']) && is_string($file['package_path']) && is_string($file['role']) && in_array($file['role'], ['attached', 'metadata-original', 'original-image', 'derivative', 'derivative-format', 'backup-size'], true) && is_int($file['bytes']) && $file['bytes'] >= 0, 'Invalid media file values.');
            exterior_exone_production_digest($file['sha256']);
            exterior_exone_production_assert(preg_match('/\.(?:jpe?g|png|gif|webp|avif|heic|ico|svg|pdf|mp4|mov|webm|mp3|wav|ogg)\z/i', $file['source_relative_path']) === 1, 'Unsupported media extension.');
        }
    }
    exterior_exone_production_fields($manifest['analysis'], ['page_paths', 'form_roles', 'destination_types', 'media_usage', 'review', 'comments_migrated', 'destination_comments', 'author_policy'], 'analysis');
}
