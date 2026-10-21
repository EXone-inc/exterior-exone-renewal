<?php
/** Pure synthetic data; no database or network. */
declare(strict_types=1);
function fixture_post(int $id, string $kind, string $slug, int $parent = 0): array {
    return ['ID' => $id, 'post_type' => $kind, 'post_status' => $kind === 'attachment' ? 'inherit' : 'publish', 'post_name' => $slug, 'post_title' => 'Synthetic ' . $slug, 'post_content' => '', 'post_excerpt' => '', 'post_date' => '2026-10-01 01:02:03', 'post_date_gmt' => '2026-09-30 16:02:03', 'post_modified' => '2026-10-01 01:02:03', 'post_modified_gmt' => '2026-09-30 16:02:03', 'post_mime_type' => $kind === 'attachment' ? 'image/jpeg' : '', 'comment_status' => 'open', 'ping_status' => 'open', 'menu_order' => 0, 'post_parent' => $parent, 'source_parent_type' => $parent === 900 ? 'column' : ($parent ? 'page' : '')];
}
function fixture_manifest(): array {
    $posts = [fixture_post(1, 'page', 'contact'), fixture_post(2, 'page', 'thanks', 1), fixture_post(3, 'works', 'work'), fixture_post(4, 'attachment', 'photo', 900), fixture_post(5, 'attachment', 'photo-two', 1), fixture_post(6, 'wpcf7_contact_form', 'form')];
    $posts[1]['post_content'] = '<!-- wp:image {"id":4,"url":"http://exterior-exone-renewal.local/wp-content/uploads/2026/01/photo.jpg"} --><img class="wp-image-4" src="/wp-content/uploads/2026/01/photo.jpg" srcset="/wp-content/uploads/2026/01/photo-small.jpg 300w, /wp-content/uploads/2026/02/photo.jpg 800w">[gallery ids="4,5,4"][contact-form-7 id="6"]<a href="http://exterior-exone-renewal.local/contact/thanks/?x=1&amp;y=2#ok">Done</a>';
    $meta = [];
    $put = static function (int $id, string $key, string $value) use (&$meta): void { $meta[] = ['post_id' => $id, 'meta_key' => $key, 'meta_value' => $value]; };
    $put(3, '_exterior_exone_works_image_main', '4');
    $put(3, '_exterior_exone_works_gallery', serialize([4, '5', 4]));
    $put(4, '_wp_attached_file', '2026/01/photo.jpg');
    $put(4, '_wp_attachment_metadata', serialize(['file' => '2026/01/photo.jpg', 'width' => 800, 'height' => 600, 'original_image' => 'photo-original.jpg', 'sizes' => ['small' => ['file' => 'photo-small.jpg', 'width' => 300, 'height' => 200, 'mime-type' => 'image/jpeg']]]));
    $put(5, '_wp_attached_file', '2026/02/photo.jpg');
    $put(6, '_exterior_exone_contact_form', 'personal');
    $put(6, '_form', '[text* your-name]');
    $put(6, '_mail', serialize(['recipient' => 'fixture@example.invalid', 'body' => 'Private synthetic mail']));
    $records = exterior_exone_production_records(['posts' => $posts, 'meta' => $meta, 'terms' => [
        ['term_id' => 1, 'term_taxonomy_id' => 10, 'taxonomy' => 'works_category', 'parent' => 0, 'name' => 'Parent', 'slug' => 'parent', 'description' => ''],
        ['term_id' => 2, 'term_taxonomy_id' => 11, 'taxonomy' => 'works_category', 'parent' => 1, 'name' => 'Child', 'slug' => 'child', 'description' => ''],
    ], 'relationships' => [['object_id' => 3, 'term_taxonomy_id' => 11, 'term_order' => 2]]]);
    foreach ($records as &$record) {
        if ($record['kind'] === 'attachment') {
            foreach (exterior_exone_production_media_paths($record) as $path => $role) {
                $bytes = 'synthetic:' . $path;
                $record['files'][] = ['source_relative_path' => $path, 'package_path' => 'media/' . $record['source_id'] . '/' . basename($path), 'bytes' => strlen($bytes), 'sha256' => hash('sha256', $bytes), 'role' => $role];
            }
        }
        $record = exterior_exone_production_seal($record);
    }
    unset($record);
    return ['schema_version' => 1, 'tool_version' => 'fixture-1', 'run_id' => 'synthetic', 'state' => 'complete', 'package_type' => 'exone-coexistence-preparation', 'origin' => EXTERIOR_EXONE_PRODUCTION_ORIGIN, 'created_utc' => '2026-10-15T12:00:00+00:00', 'production_ready' => false, 'preparation_only' => true, 'scope' => ['columns' => 'excluded', 'users' => 'excluded', 'options' => 'excluded', 'comments' => 'excluded', 'received_submissions' => 'excluded', 'cf7_hash_and_webhooks' => 'excluded', 'all_attachments' => 'included'], 'snapshot' => ['mode' => 'synthetic; no database'], 'theme_dependencies' => ['files' => [], 'payload' => 'synthetic fixture; not captured'], 'unknown_meta' => [], 'excluded_column_count' => 1, 'excluded_status_counts' => [], 'counts' => ['works' => 1, 'news' => 0, 'page' => 2, 'attachment' => 2, 'wpcf7_contact_form' => 1, 'term' => 2], 'status_counts' => ['works:publish' => 1, 'page:publish' => 2, 'attachment:inherit' => 2, 'wpcf7_contact_form:publish' => 1], 'records' => array_values($records), 'source_fingerprint' => exterior_exone_production_hash(array_values($records)), 'analysis' => exterior_exone_production_analysis($records)];
}
function write_fixture(array $manifest, string $directory): void {
    mkdir($directory, 0700);
    foreach ($manifest['records'] as $record) {
        foreach ($record['files'] as $file) {
            $path = $directory . '/' . $file['package_path'];
            if (!is_dir(dirname($path))) { mkdir(dirname($path), 0700, true); }
            exterior_exone_production_write($path, 'synthetic:' . $file['source_relative_path']);
        }
    }
    $raw = exterior_exone_production_json($manifest);
    exterior_exone_production_write($directory . '/manifest.json', $raw);
    exterior_exone_production_write($directory . '/manifest.sha256', hash('sha256', $raw) . "  manifest.json\n");
    exterior_exone_production_write($directory . '/COMPLETE', $manifest['source_fingerprint'] . "\n");
    exterior_exone_production_write($directory . '/summary.txt', exterior_exone_production_summary($manifest));
}
