<?php
/** PHP 7.4.33・合成データだけ。DB/HTTP/元パッケージへの書き込みなし。 */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/plan.php';
require_once dirname(__DIR__) . '/media.php';
require_once dirname(__DIR__) . '/guard.php';

$passed = 0;
function check(bool $value, string $label): void {
    global $passed;
    if (!$value) { throw new RuntimeException('Fixture failed: ' . $label); }
    $passed++;
}
function rejected(callable $callback, string $label): void {
    try { $callback(); } catch (Throwable $error) { check(true, $label); return; }
    check(false, $label);
}
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
    $records = exterior_exone_import_records(['posts' => $posts, 'meta' => $meta, 'terms' => [
        ['term_id' => 1, 'term_taxonomy_id' => 10, 'taxonomy' => 'works_category', 'parent' => 0, 'name' => 'Parent', 'slug' => 'parent', 'description' => ''],
        ['term_id' => 2, 'term_taxonomy_id' => 11, 'taxonomy' => 'works_category', 'parent' => 1, 'name' => 'Child', 'slug' => 'child', 'description' => ''],
    ], 'relationships' => [['object_id' => 3, 'term_taxonomy_id' => 11, 'term_order' => 2]]]);
    foreach ($records as &$record) {
        if ($record['kind'] === 'attachment') {
            foreach (exterior_exone_import_media_paths($record) as $path => $role) {
                $bytes = 'synthetic:' . $path;
                $record['files'][] = ['source_relative_path' => $path, 'package_path' => 'media/' . $record['source_id'] . '/' . basename($path), 'bytes' => strlen($bytes), 'sha256' => hash('sha256', $bytes), 'role' => $role];
            }
        }
        $record = exterior_exone_import_seal($record);
    }
    unset($record);
    return ['schema_version' => 1, 'tool_version' => 'fixture-1', 'run_id' => 'synthetic', 'state' => 'complete', 'package_type' => 'exone-coexistence-preparation', 'origin' => EXTERIOR_EXONE_IMPORT_ORIGIN, 'created_utc' => '2026-10-15T12:00:00+00:00', 'production_ready' => false, 'preparation_only' => true, 'scope' => ['columns' => 'excluded', 'users' => 'excluded', 'options' => 'excluded', 'comments' => 'excluded', 'received_submissions' => 'excluded', 'cf7_hash_and_webhooks' => 'excluded', 'all_attachments' => 'included'], 'snapshot' => ['mode' => 'synthetic; no database'], 'theme_dependencies' => ['files' => [], 'payload' => 'synthetic fixture; not captured'], 'unknown_meta' => [], 'excluded_column_count' => 1, 'excluded_status_counts' => [], 'counts' => ['works' => 1, 'news' => 0, 'page' => 2, 'attachment' => 2, 'wpcf7_contact_form' => 1, 'term' => 2], 'status_counts' => ['works:publish' => 1, 'page:publish' => 2, 'attachment:inherit' => 2, 'wpcf7_contact_form:publish' => 1], 'records' => array_values($records), 'source_fingerprint' => exterior_exone_import_hash(array_values($records)), 'analysis' => exterior_exone_import_analysis($records)];
}
function write_fixture(array $manifest, string $directory): void {
    exterior_exone_import_private_directory($directory);
    foreach ($manifest['records'] as $record) {
        foreach ($record['files'] as $file) {
            $path = $directory . '/' . $file['package_path'];
            if (!is_dir(dirname($path))) { mkdir(dirname($path), 0700, true); }
            exterior_exone_import_write($path, 'synthetic:' . $file['source_relative_path']);
        }
    }
    $raw = exterior_exone_import_json($manifest);
    exterior_exone_import_write($directory . '/manifest.json', $raw);
    exterior_exone_import_write($directory . '/manifest.sha256', hash('sha256', $raw) . "  manifest.json\n");
    exterior_exone_import_write($directory . '/COMPLETE', $manifest['source_fingerprint'] . "\n");
    exterior_exone_import_write($directory . '/summary.txt', exterior_exone_import_summary($manifest));
}

umask(0077);
$root = EXTERIOR_EXONE_IMPORT_PRIVATE_ROOT . '/import-fixtures-' . bin2hex(random_bytes(5));
exterior_exone_import_private_directory($root);
$manifest = fixture_manifest();
write_fixture($manifest, $root . '/valid');
$checked = exterior_exone_import_validate($root . '/valid');
check(count($checked['records']) === 8, 'valid standalone package');
check(PHP_VERSION === '7.4.33', 'actual PHP 7.4.33');
$ids = [];
$i = 1000;
foreach ($manifest['records'] as $record) {
    $ids[$record['source_key']] = $record['kind'] === 'term' ? ['id' => ++$i, 'term_taxonomy_id' => $i + 1000] : ['id' => ++$i];
}
$context = exterior_exone_import_context($manifest, $ids, 'http://127.0.0.1:18774', 'http://127.0.0.1:18774/retest', 1);
$duplicateTaxonomy = $ids;
$duplicateTaxonomy[exterior_exone_import_key('term', 2, 'works_category')]['term_taxonomy_id'] = $duplicateTaxonomy[exterior_exone_import_key('term', 1, 'works_category')]['term_taxonomy_id'];
rejected(static function () use ($manifest, $duplicateTaxonomy) { exterior_exone_import_context($manifest, $duplicateTaxonomy, 'http://127.0.0.1:18774', 'http://127.0.0.1:18774/retest', 1); }, 'duplicate destination taxonomy ID');
$rows = [];
foreach ($manifest['records'] as $record) { $rows[$record['source_key']] = exterior_exone_import_transform($record, $context); }
$key = static function (string $type, int $id): string { return exterior_exone_import_key('post', $id, $type); };
$page = $rows[$key('page', 2)];
check($page['data']['post_parent'] === $ids[$key('page', 1)]['id'], 'page parent remap');
check($page['data']['post_status'] === 'exr_stored' && $page['meta']['_exr_source_status'] === ['publish'], 'nonpublic storage status');
check($page['data']['comment_status'] === 'closed' && $page['data']['ping_status'] === 'closed' && $page['data']['comment_count'] === 0, 'comments closed');
check(strpos($page['data']['post_content'], 'wp-image-' . $ids[$key('attachment', 4)]['id']) !== false, 'HTML image ID');
check(strpos($page['data']['post_content'], '"id":' . $ids[$key('attachment', 4)]['id']) !== false, 'block image ID');
check(strpos($page['data']['post_content'], '[gallery ids="' . $ids[$key('attachment', 4)]['id'] . ',' . $ids[$key('attachment', 5)]['id'] . ',' . $ids[$key('attachment', 4)]['id'] . '"]') !== false, 'gallery order and duplicates');
check(strpos($page['data']['post_content'], '[contact-form-7 id="' . $ids[$key('wpcf7_contact_form', 6)]['id'] . '"]') !== false, 'CF7 numeric shortcode');
check(strpos($page['data']['post_content'], 'http://exterior-exone-renewal.local') === false && strpos($page['data']['post_content'], '?x=1&amp;y=2#ok') !== false, 'known local link query and fragment');
check(strpos($page['data']['post_content'], '/photo-small.jpg 300w') !== false && strpos($page['data']['post_content'], '/5/2026/02/photo.jpg 800w') !== false, 'srcset derivatives');
check($rows[$key('attachment', 4)]['data']['post_parent'] === 0 && $rows[$key('attachment', 4)]['meta']['_exr_source_parent_id'] === ['900'], 'excluded column parent zero');
check($rows[$key('attachment', 4)]['data']['post_status'] === 'private' && $rows[$key('attachment', 5)]['data']['post_status'] === 'private', 'attachments use native private status regardless of parent');
check($rows[$key('attachment', 4)]['meta']['_exr_source_status'] === ['inherit'] && $rows[$key('attachment', 4)]['meta']['_exr_source_hash'] === [$context['records'][$key('attachment', 4)]['content_sha256']], 'attachment source status and source hash stay unchanged');
check($rows[$key('works', 3)]['data']['post_status'] === 'exr_stored', 'attachment exception does not change dedicated content status');
$metadata = exterior_exone_import_decode($rows[$key('attachment', 4)]['meta']['_wp_attachment_metadata'][0]);
check(strpos($metadata['file'], 'exone-renewal/') === 0 && $metadata['sizes']['small']['file'] === 'photo-small.jpg', 'serialized attachment metadata');
$gallery = exterior_exone_import_decode($rows[$key('works', 3)]['meta']['_exterior_exone_works_gallery'][0]);
check($gallery === [$ids[$key('attachment', 4)]['id'], (string) $ids[$key('attachment', 5)]['id'], $ids[$key('attachment', 4)]['id']], 'serialized gallery typed values');
$form = $rows[$key('wpcf7_contact_form', 6)];
check($form['meta']['_exr_form_role'] === ['personal'] && !isset($form['meta']['_exterior_exone_contact_form']) && !isset($form['meta']['_hash']), 'dedicated CF7 identity');
check($form['data']['post_type'] === 'exr_form' && $form['data']['post_status'] === 'exr_stored', 'CF7 storage type isolates the old feedback endpoint');
check($form['meta']['_mail'] === [serialize(['recipient' => 'fixture@example.invalid', 'body' => 'Private synthetic mail'])], 'mail data retained without sending');
check($rows[$key('works', 3)]['relationships'][0]['term_taxonomy_id'] === $ids[exterior_exone_import_key('term', 2, 'works_category')]['term_taxonomy_id'], 'taxonomy remap');
check(exterior_exone_import_text('<img src="https://example.invalid/photo.jpg">', $context) === '<img src="https://example.invalid/photo.jpg">', 'external image retained without HTTP');
check(count(exterior_exone_import_markup_media_references('<img data-unknown="x">', 'post_content')) === 1, 'unknown markup tracked');
rejected(static function () { exterior_exone_import_unique_json_keys('{"a":1,"\u0061":2}'); }, 'duplicate decoded JSON key');
foreach (['O:8:"stdClass":0:{}', 'a:1:{i:0;R:1;}', 's:1:"a";trailing', 'N;trailing'] as $bad) {
    rejected(static function () use ($bad) { exterior_exone_import_decode($bad); }, 'serialized unsafe structure');
}
foreach (['../file.jpg', '/absolute.jpg', 'x/../file.jpg', "x\n/file.jpg", 'x\\file.jpg'] as $path) {
    rejected(static function () use ($path) { exterior_exone_import_relative($path); }, 'unsafe path');
}
foreach (['https://exterior-exone.com', 'http://exterior-exone-renewal.local', 'http://127.0.0.1.evil:18774', 'http://user@127.0.0.1:18774'] as $home) {
    rejected(static function () use ($manifest, $ids, $home) { exterior_exone_import_context($manifest, $ids, $home, $home, 1); }, 'nonisolated destination URL');
}
foreach (['top-extra', 'snapshot-users', 'dependency-options', 'bad-excluded-count', 'bad-record-status', 'record-author', 'unknown-meta', 'comment-count', 'missing-data', 'extra-reference'] as $index => $case) {
    $bad = $manifest;
    if ($case === 'top-extra') { $bad['users'] = []; }
    if ($case === 'snapshot-users') { $bad['snapshot']['users'] = []; }
    if ($case === 'dependency-options') { $bad['theme_dependencies']['options'] = []; }
    if ($case === 'bad-excluded-count') { $bad['excluded_status_counts'][] = ['post_type' => 'page', 'post_status' => 'trash', 'count' => ['users' => 1]]; }
    if ($case === 'bad-record-status') { $bad['records'][0]['data']['post_status'] = 'trash'; }
    if ($case === 'record-author') { $bad['records'][0]['data']['post_author'] = 123; }
    if ($case === 'unknown-meta') { $bad['records'][0]['meta']['_hash'] = ['secret']; }
    if ($case === 'comment-count') { $bad['records'][0]['data']['comment_count'] = 5; }
    if ($case === 'missing-data') { unset($bad['records'][0]['data']['post_title']); }
    if ($case === 'extra-reference') { $bad['records'][0]['references'][] = ['field' => 'parent', 'target' => $key('page', 1), 'users' => []]; }
    foreach ($bad['records'] as &$record) { unset($record['content_sha256']); $record = exterior_exone_import_seal($record); }
    unset($record);
    $bad['source_fingerprint'] = exterior_exone_import_hash($bad['records']);
    write_fixture($bad, $root . '/bad-' . $index);
    rejected(static function () use ($root, $index) { exterior_exone_import_validate($root . '/bad-' . $index); }, $case);
}
$ledger = exterior_exone_import_ledger($manifest, $context);
check($ledger['production_ready'] === false && count($ledger['items']) > 0, 'review ledger not release approval');
$inventory = ['schema_version' => 1, 'complete' => true, 'records' => []];
check(exterior_exone_import_plan($manifest, $inventory)['insert_count'] === 8, 'empty verified destination plans inserts');
foreach ($manifest['records'] as $record) {
    $sourceKey = $record['source_key'];
    $inventory['records'][] = ['source_key' => $sourceKey, 'source_hash' => $record['content_sha256'], 'source_fingerprint' => $manifest['source_fingerprint'], 'dataset' => EXTERIOR_EXONE_IMPORT_DATASET, 'import_state' => 'complete', 'kind' => $record['kind'], 'id' => $ids[$sourceKey]['id'], 'term_taxonomy_id' => $ids[$sourceKey]['term_taxonomy_id'] ?? null, 'form_role' => $record['kind'] === 'wpcf7_contact_form' ? 'personal' : ''];
}
check(exterior_exone_import_plan($manifest, $inventory)['reuse_count'] === 8, 'same identities plan only reuse');
exterior_exone_import_dry_run_plan(exterior_exone_import_plan($manifest, ['schema_version' => 1, 'complete' => true, 'records' => []]));
check(true, 'all-new dry-run remains permitted');
exterior_exone_import_dry_run_plan(exterior_exone_import_plan($manifest, $inventory));
check(true, 'all-reuse dry-run remains permitted and requires full verification');
$partial = $inventory;
array_pop($partial['records']);
$mixed = exterior_exone_import_plan($manifest, $partial);
check($mixed['insert_count'] === 1 && $mixed['reuse_count'] === 7, 'partial inventory produces a mixed reference graph');
rejected(static function () use ($mixed) { exterior_exone_import_dry_run_plan($mixed); }, 'mixed dry-run refuses unverified reuse even if identity hashes match');
foreach (['duplicate-key', 'source-hash', 'wrong-dataset', 'wrong-role-key', 'incomplete', 'missing-inventory'] as $case) {
    $bad = $inventory;
    if ($case === 'duplicate-key') { $bad['records'][] = $bad['records'][0]; }
    if ($case === 'source-hash') { $bad['records'][0]['source_hash'] = str_repeat('0', 64); }
    if ($case === 'wrong-dataset') { $bad['records'][0]['dataset'] = 'legacy'; }
    if ($case === 'wrong-role-key') { foreach ($bad['records'] as &$row) { if ($row['kind'] === 'wpcf7_contact_form') { $row['source_key'] .= '9'; } } unset($row); }
    if ($case === 'incomplete') { $bad['records'][0]['import_state'] = 'pending'; }
    if ($case === 'missing-inventory') { $bad['complete'] = false; }
    rejected(static function () use ($manifest, $bad) { exterior_exone_import_plan($manifest, $bad); }, $case);
}
$uploads = exterior_exone_import_private_directory($root . '/uploads');
exterior_exone_import_write($uploads . '/legacy.jpg', 'keep-old-file');
$newPlan = exterior_exone_import_plan($manifest, ['schema_version' => 1, 'complete' => true, 'records' => []]);
$media = new ExteriorExoneImportMedia($root . '/valid', $uploads, $context, $newPlan, $root . '/media-first.jsonl');
rejected(static function () use ($media) { $media->copy(static function (): void { throw new RuntimeException('Synthetic failure after first media file'); }); }, 'media failure injection');
$media->rollback();
check(file_get_contents($uploads . '/legacy.jpg') === 'keep-old-file' && !file_exists($uploads . '/exone-renewal'), 'rollback removes only current run files');
$media = new ExteriorExoneImportMedia($root . '/valid', $uploads, $context, $newPlan, $root . '/media-retry.jsonl');
$media->copy(static function (): void {});
check(file_get_contents($uploads . '/legacy.jpg') === 'keep-old-file', 'retry preserves legacy file');
$collision = new ExteriorExoneImportMedia($root . '/valid', $uploads, $context, $newPlan, $root . '/media-collision.jsonl');
rejected(static function () use ($collision) { $collision->preflight(); }, 'unowned existing media is not overwritten');
$reuseMedia = new ExteriorExoneImportMedia($root . '/valid', $uploads, $context, exterior_exone_import_plan($manifest, $inventory), $root . '/media-reuse.jsonl');
$reuseMedia->copy(static function (): void { throw new RuntimeException('Reuse must not create files'); });
check(true, 'same package media reuse makes no additions');
$media->rollback();
check(count(scandir($uploads)) === 3, 'rollback leaves only legacy media');
$environment = exterior_exone_import_contract();
exterior_exone_import_check_environment($environment);
check(true, 'pinned isolated environment contract');
foreach (['root', 'wp_root', 'socket', 'database', 'prefix', 'home', 'siteurl', 'environment_id', 'token', 'schema_version'] as $field) {
    $bad = $environment;
    $bad[$field] = $field === 'schema_version' ? 2 : 'foreign-value';
    rejected(static function () use ($bad) { exterior_exone_import_check_environment($bad); }, 'foreign isolated environment ' . $field);
}
$missing = $environment;
unset($missing['token']);
rejected(static function () use ($missing) { exterior_exone_import_check_environment($missing); }, 'missing environment sentinel identity');
rejected(static function () { exterior_exone_import_options(['package' => 'x', 'output' => 'y', 'host' => 'exterior-exone.com'], ['package', 'output']); }, 'host override unavailable');
exterior_exone_import_write($root . '/result.json', exterior_exone_import_json(['schema_version' => 1, 'php' => PHP_VERSION, 'passed' => $passed, 'database_connections' => 0, 'http_requests' => 0, 'source_mutations' => 0]));
echo 'PASS ' . $passed . ' synthetic checks; database/network/source writes 0.' . PHP_EOL;
