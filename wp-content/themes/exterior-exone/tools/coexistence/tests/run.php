<?php
/** 合成データだけで実行する。DB・WordPress・ネットワークを使わない。 */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once dirname(__DIR__) . '/lib.php';

$passed = 0;
$failed = 0;
$root = EXTERIOR_EXONE_COEX_PRIVATE_ROOT . '/synthetic-tests-' . bin2hex(random_bytes(6));
exterior_exone_coex_private_directory($root);
mkdir($root . '/uploads', 0700);
mkdir($root . '/uploads/2026', 0700);
foreach (['image.jpg', 'small.jpg', 'original.jpg', 'backup.jpg', 'column.jpg', 'orphan.jpg'] as $name) {
    exterior_exone_coex_write($root . '/uploads/2026/' . $name, "synthetic binary fixture: $name\n");
}

function exterior_exone_coex_test(string $label, callable $body): void {
    global $passed, $failed;
    try {
        $body();
        $passed++;
        echo "PASS $label\n";
    } catch (Throwable $error) {
        $failed++;
        echo "FAIL $label (", get_class($error), ")\n";
    }
}
function exterior_exone_coex_expect_fail(callable $body): void {
    $thrown = false;
    try { $body(); } catch (Throwable $error) { $thrown = true; }
    exterior_exone_coex_assert($thrown, 'Expected failure did not occur.');
}
function exterior_exone_coex_fixture_post(int $id, string $type, string $slug, int $parent = 0, string $state = 'publish', string $content = ''): array {
    return ['ID' => $id, 'post_type' => $type, 'post_name' => $slug, 'post_title' => '合成 "タイトル"', 'post_status' => $state, 'post_parent' => $parent, 'source_parent_type' => $parent ? 'page' : '', 'post_content' => $content, 'post_excerpt' => "改行\n引用'", 'post_date' => '2026-10-15 12:00:00', 'post_date_gmt' => '2026-10-15 03:00:00', 'post_modified' => '2026-10-15 12:00:00', 'post_modified_gmt' => '2026-10-15 03:00:00', 'comment_status' => 'open', 'ping_status' => 'open', 'post_author' => 998, 'post_password' => 'DO_NOT_EXPORT'];
}
function exterior_exone_coex_fixture_meta(int $id, string $key, mixed $value): array {
    return ['post_id' => $id, 'meta_key' => $key, 'meta_value' => is_array($value) ? serialize($value) : (string) $value];
}
function exterior_exone_coex_fixture(): array {
    $posts = [
        exterior_exone_coex_fixture_post(1, 'works', 'proposal', 0, 'publish', '<!-- wp:image {"id":10} --><img class="wp-image-10" src="http://exterior-exone-renewal.local/wp-content/uploads/2026/image.jpg" srcset="http://exterior-exone-renewal.local/wp-content/uploads/2026/small.jpg 100w, http://exterior-exone-renewal.local/wp-content/uploads/2026/image.jpg 500w"><!-- /wp:image -->'),
        exterior_exone_coex_fixture_post(2, 'news', 'announcement'),
        exterior_exone_coex_fixture_post(3, 'page', 'contact', 0, 'publish', '[contact-form-7 id="20"]'),
        exterior_exone_coex_fixture_post(4, 'page', 'recruit'),
        exterior_exone_coex_fixture_post(5, 'page', 'thanks', 3, 'draft'),
        exterior_exone_coex_fixture_post(6, 'page', 'thanks', 4, 'future'),
        exterior_exone_coex_fixture_post(10, 'attachment', 'image', 1, 'inherit'),
        exterior_exone_coex_fixture_post(11, 'attachment', 'column-image', 90, 'inherit'),
        exterior_exone_coex_fixture_post(12, 'attachment', 'orphan-image', 0, 'inherit'),
        exterior_exone_coex_fixture_post(20, 'wpcf7_contact_form', 'personal'),
        exterior_exone_coex_fixture_post(21, 'wpcf7_contact_form', 'corporate'),
        exterior_exone_coex_fixture_post(22, 'wpcf7_contact_form', 'entry'),
        exterior_exone_coex_fixture_post(90, 'column', 'not-exported', 0, 'publish', 'SECRET_COLUMN_BODY'),
    ];
    $posts[6]['source_parent_type'] = 'works';
    $posts[7]['source_parent_type'] = 'column';
    $meta = [
        exterior_exone_coex_fixture_meta(1, '_exterior_exone_works_image_main', 10),
        exterior_exone_coex_fixture_meta(1, '_exterior_exone_works_image_exterior', 11),
        exterior_exone_coex_fixture_meta(1, '_exterior_exone_works_image_perspective', 10),
        exterior_exone_coex_fixture_meta(1, '_exterior_exone_works_image_plan', 11),
        exterior_exone_coex_fixture_meta(1, '_exterior_exone_works_gallery', '10,11'),
        exterior_exone_coex_fixture_meta(1, '_exterior_exone_works_on_top', '1'),
        exterior_exone_coex_fixture_meta(2, '_thumbnail_id', 10),
        exterior_exone_coex_fixture_meta(5, '_wp_page_template', 'default'),
        exterior_exone_coex_fixture_meta(10, '_wp_attached_file', '2026/image.jpg'),
        exterior_exone_coex_fixture_meta(10, '_wp_attachment_metadata', ['file' => '2026/image.jpg', 'width' => 500, 'height' => 300, 'original_image' => 'original.jpg', 'sizes' => ['small' => ['file' => 'small.jpg', 'width' => 100]]]),
        exterior_exone_coex_fixture_meta(10, '_wp_attachment_backup_sizes', ['full-orig' => ['file' => 'backup.jpg']]),
        exterior_exone_coex_fixture_meta(11, '_wp_attached_file', '2026/column.jpg'),
        exterior_exone_coex_fixture_meta(12, '_wp_attached_file', '2026/orphan.jpg'),
        exterior_exone_coex_fixture_meta(20, '_exterior_exone_contact_form', 'personal'),
        exterior_exone_coex_fixture_meta(21, '_exterior_exone_contact_form', 'corporate'),
        exterior_exone_coex_fixture_meta(22, '_exterior_exone_recruit_form', 'entry'),
        exterior_exone_coex_fixture_meta(20, '_mail', ['recipient' => 'fixture@example.invalid', 'body' => "合成メール\n[your-name]"]),
        exterior_exone_coex_fixture_meta(20, '_hash', 'DO_NOT_EXPORT_HASH'),
        exterior_exone_coex_fixture_meta(1, '_edit_lock', 'DO_NOT_EXPORT_LOCK'),
        exterior_exone_coex_fixture_meta(1, '_unknown_fixture_key', 'DO_NOT_EXPORT_UNKNOWN_VALUE'),
    ];
    return ['posts' => $posts, 'meta' => $meta, 'terms' => [['term_id' => 1, 'term_taxonomy_id' => 50, 'taxonomy' => 'works_package', 'name' => '合成', 'slug' => 'synthetic', 'description' => '分類', 'parent' => 0]], 'relationships' => [['object_id' => 1, 'term_taxonomy_id' => 50, 'term_order' => 0]], 'excluded_column_count' => 1];
}
function exterior_exone_coex_test_inventory(): array {
    return ['schema_version' => 1, 'inventory_type' => 'exone-coexistence-destination', 'environment' => 'synthetic', 'completeness' => 'complete', 'namespaces' => [], 'records' => [['destination_id' => 20, 'kind' => 'wpcf7_contact_form', 'ownership' => 'protected', 'title' => '合成 "タイトル"', 'slug' => 'personal']], 'files' => []];
}

$fixture = exterior_exone_coex_fixture();
$manifest = exterior_exone_coex_package($fixture, $root . '/uploads', $root . '/package');
$inventory = exterior_exone_coex_test_inventory();

exterior_exone_coex_test('round-trip; all media and derivatives included', function () use ($root, $manifest): void {
    $validated = exterior_exone_coex_validate($root . '/package');
    exterior_exone_coex_assert($validated['counts']['attachment'] === 3 && count($validated['records']) === 13 && $validated['source_fingerprint'] === $manifest['source_fingerprint'], 'Round-trip mismatch.');
    $files = array_sum(array_map(static fn(array $record): int => count($record['files']), $validated['records']));
    exterior_exone_coex_assert($files === 6, 'Original/derivatives/backups missing.');
});
exterior_exone_coex_test('forbidden content and unknown meta values excluded', function () use ($manifest): void {
    $json = exterior_exone_coex_json($manifest);
    exterior_exone_coex_assert(!str_contains($json, 'DO_NOT_EXPORT') && !str_contains($json, 'SECRET_COLUMN_BODY') && !str_contains($json, 'post_author'), 'Disallowed value leaked.');
    exterior_exone_coex_assert(count($manifest['unknown_meta']) === 1, 'Unknown key was not reported.');
});
exterior_exone_coex_test('deterministic fingerprint and retained states', function () use ($fixture, $root, $manifest): void {
    $fixture['posts'] = array_reverse($fixture['posts']);
    $other = exterior_exone_coex_package($fixture, $root . '/uploads', $root . '/second');
    exterior_exone_coex_assert($other['source_fingerprint'] === $manifest['source_fingerprint'], 'Unstable fingerprint.');
    exterior_exone_coex_assert(isset($other['status_counts']['page:draft'], $other['status_counts']['page:future']), 'State was changed.');
});
exterior_exone_coex_test('same thanks slug under distinct parents', function () use ($manifest): void {
    exterior_exone_coex_assert(isset($manifest['analysis']['page_paths']['/contact/thanks/'], $manifest['analysis']['page_paths']['/recruit/thanks/']), 'Full paths missing.');
});
foreach (['duplicate full path', 'cyclic parent', 'missing media ID', 'duplicate form role', 'invalid taxonomy parent'] as $case) {
    exterior_exone_coex_test($case, function () use ($case, $fixture, $root): void {
        switch ($case) {
            case 'duplicate full path': $fixture['posts'][5]['post_parent'] = 3; break;
            case 'cyclic parent': $fixture['posts'][2]['post_parent'] = 5; break;
            case 'missing media ID': $fixture['meta'][0]['meta_value'] = '999'; break;
            case 'duplicate form role': $fixture['meta'][14]['meta_value'] = 'personal'; break;
            case 'invalid taxonomy parent': $fixture['terms'][0]['parent'] = 999; break;
        }
        exterior_exone_coex_expect_fail(fn() => exterior_exone_coex_package($fixture, $root . '/uploads', $root . '/invalid-' . str_replace(' ', '-', $case)));
    });
}
foreach (['O:8:"stdClass":0:{}', 's:9:"bad";', 'i:1;TRAILING', 'a:1:{i:0;R:1;}'] as $index => $value) {
    exterior_exone_coex_test('invalid serialized input ' . $index, fn() => exterior_exone_coex_expect_fail(fn() => exterior_exone_coex_decode($value)));
}
exterior_exone_coex_test('duplicate JSON keys including escaped spellings', function () use ($root): void {
    exterior_exone_coex_write($root . '/duplicate.json', '{"key":1,"\u006bey":2}');
    exterior_exone_coex_expect_fail(fn() => exterior_exone_coex_read_json($root . '/duplicate.json'));
});
exterior_exone_coex_test('invalid UTF-8 and oversize JSON', function () use ($root): void {
    exterior_exone_coex_write($root . '/bad-utf8.json', "{\"value\":\"\xff\"}");
    exterior_exone_coex_expect_fail(fn() => exterior_exone_coex_read_json($root . '/bad-utf8.json'));
    $handle = fopen($root . '/large.json', 'xb');
    ftruncate($handle, 64 * 1024 * 1024);
    fclose($handle);
    exterior_exone_coex_expect_fail(fn() => exterior_exone_coex_read_json($root . '/large.json'));
});
exterior_exone_coex_test('output root, symlink and overwrite restrictions', function () use ($root): void {
    exterior_exone_coex_expect_fail(fn() => exterior_exone_coex_private_directory(EXTERIOR_EXONE_COEX_SOURCE_ROOT . '/must-not-create'));
    exterior_exone_coex_expect_fail(fn() => exterior_exone_coex_private_directory('/private/tmp/must-not-create-coex'));
    exterior_exone_coex_expect_fail(fn() => exterior_exone_coex_private_directory($root . '/package'));
    symlink($root . '/package', $root . '/linked');
    exterior_exone_coex_expect_fail(fn() => exterior_exone_coex_validate($root . '/linked'));
});
exterior_exone_coex_test('missing, escaping and symbolic source media', function () use ($fixture, $root): void {
    foreach (['2026/missing.jpg', '../outside.jpg', '2026/linked.jpg'] as $i => $path) {
        $fixture['meta'][12]['meta_value'] = $path;
        if ($i === 2) { symlink($root . '/uploads/2026/image.jpg', $root . '/uploads/2026/linked.jpg'); }
        exterior_exone_coex_expect_fail(fn() => exterior_exone_coex_package($fixture, $root . '/uploads', $root . '/bad-media-' . $i));
    }
});
exterior_exone_coex_test('copy independent of later source changes', function () use ($root): void {
    file_put_contents($root . '/uploads/2026/image.jpg', 'changed synthetic source');
    exterior_exone_coex_validate($root . '/package');
});
exterior_exone_coex_test('title/slug/numeric ID do not reuse protected records', function () use ($manifest, $inventory): void {
    $plan = exterior_exone_coex_plan($manifest, $inventory);
    exterior_exone_coex_assert($plan['counts']['create'] === 13 && $plan['counts']['reuse'] === 0 && $plan['counts']['conflicts'] === 0, 'Protected record reused.');
    foreach ($plan['operations'] as $op) {
        exterior_exone_coex_assert($op['destination_comments'] === ['comment_status' => 'closed', 'ping_status' => 'closed', 'comment_count' => 0], 'Comment policy missing.');
    }
});
exterior_exone_coex_test('simulated rerun reuses source identities only', function () use ($manifest, $inventory): void {
    $first = exterior_exone_coex_plan($manifest, $inventory);
    foreach ($first['operations'] as $i => $op) {
        $inventory['records'][] = ['destination_id' => 1000 + $i, 'kind' => $op['kind'], 'ownership' => 'renewal', 'source_key' => $op['source_key'], 'content_sha256' => $op['content_sha256']];
        if (isset($op['form_role'])) {
            $inventory['records'][count($inventory['records']) - 1]['form_role'] = $op['form_role'];
        }
        foreach ($op['media'] as $media) {
            $inventory['files'][] = ['relative_path' => $media['destination_relative_path'], 'sha256' => $media['sha256'], 'ownership' => 'renewal', 'source_key' => $op['source_key']];
        }
    }
    $second = exterior_exone_coex_plan($manifest, $inventory);
    exterior_exone_coex_assert($second['counts'] === ['create' => 0, 'reuse' => 13, 'conflicts' => 0], 'Rerun proposed duplicates.');
    $inventory['records'][] = $inventory['records'][1] + [];
    $inventory['records'][count($inventory['records']) - 1]['destination_id'] = 9999;
    exterior_exone_coex_expect_fail(fn() => exterior_exone_coex_plan($manifest, $inventory));
});
exterior_exone_coex_test('occupied namespace and media destination conflict', function () use ($manifest, $inventory): void {
    $first = exterior_exone_coex_plan($manifest, $inventory);
    foreach ($first['operations'] as $op) {
        if ($op['media']) { $media = $op['media'][0]; break; }
    }
    $inventory['namespaces']['exr_page'] = 'protected';
    $inventory['files'][] = ['relative_path' => $media['destination_relative_path'], 'sha256' => str_repeat('a', 64), 'ownership' => 'protected'];
    $plan = exterior_exone_coex_plan($manifest, $inventory);
    exterior_exone_coex_assert($plan['counts']['conflicts'] >= 5 && !$plan['production_ready'], 'Conflict not detected.');
});
exterior_exone_coex_test('same renewal role on a different source identity conflicts', function () use ($manifest, $inventory): void {
    $inventory['records'][] = ['destination_id' => 500, 'kind' => 'wpcf7_contact_form', 'ownership' => 'renewal', 'source_key' => 'other-origin:v1:form:500', 'content_sha256' => str_repeat('a', 64), 'form_role' => 'personal'];
    $plan = exterior_exone_coex_plan($manifest, $inventory);
    exterior_exone_coex_assert($plan['counts']['conflicts'] === 1, 'Different source with same form role was accepted.');
});
exterior_exone_coex_test('cross-file source change is rejected before completion', function () use ($fixture, $root): void {
    $records = exterior_exone_coex_records($fixture);
    $before = exterior_exone_coex_media_signatures($records, $root . '/uploads');
    mkdir($root . '/copy-race', 0700);
    exterior_exone_coex_copy_media($root . '/uploads', '2026/image.jpg', $root . '/copy-race', 'media/a.jpg');
    file_put_contents($root . '/uploads/2026/image.jpg', 'changed after A copy before B copy');
    exterior_exone_coex_copy_media($root . '/uploads', '2026/column.jpg', $root . '/copy-race', 'media/b.jpg');
    exterior_exone_coex_expect_fail(fn() => exterior_exone_coex_verify_media_snapshot($before, exterior_exone_coex_media_signatures($records, $root . '/uploads')));
});
foreach (['snapshot-users', 'snapshot-options', 'dependency-users', 'unknown-meta-value', 'wrong-snapshot-type', 'top-users', 'edit-lock', 'column-record', 'excluded-status-array', 'excluded-count-array', 'excluded-negative-count', 'excluded-unknown-status', 'excluded-wrong-type', 'excluded-duplicate', 'record-trash-status'] as $case) {
    exterior_exone_coex_test('resigned malformed payload rejected: ' . $case, function () use ($case, $fixture, $root): void {
        $path = $root . '/bad-schema-' . $case;
        $payload = exterior_exone_coex_package($fixture, $root . '/uploads', $path);
        switch ($case) {
            case 'snapshot-users': $payload['snapshot']['users'] = ['private']; break;
            case 'snapshot-options': $payload['snapshot']['options'] = ['private']; break;
            case 'dependency-users': $payload['theme_dependencies']['users'] = ['private']; break;
            case 'unknown-meta-value': $payload['unknown_meta'][0]['value'] = 'private'; break;
            case 'wrong-snapshot-type': $payload['snapshot']['mode'] = ['private']; break;
            case 'top-users': $payload['users'] = ['private']; break;
            case 'edit-lock':
                $payload['records'][0]['meta']['_edit_lock'] = ['private'];
                unset($payload['records'][0]['content_sha256']);
                $payload['records'][0] = exterior_exone_coex_seal($payload['records'][0]);
                break;
            case 'column-record':
                $payload['records'][0]['kind'] = 'column';
                unset($payload['records'][0]['content_sha256']);
                $payload['records'][0] = exterior_exone_coex_seal($payload['records'][0]);
                break;
            case 'excluded-status-array': $payload['excluded_status_counts'] = [['post_type' => 'page', 'post_status' => ['users' => 'private'], 'count' => 1]]; break;
            case 'excluded-count-array': $payload['excluded_status_counts'] = [['post_type' => 'page', 'post_status' => 'trash', 'count' => ['users' => 'private']]]; break;
            case 'excluded-negative-count': $payload['excluded_status_counts'] = [['post_type' => 'page', 'post_status' => 'trash', 'count' => -1]]; break;
            case 'excluded-unknown-status': $payload['excluded_status_counts'] = [['post_type' => 'page', 'post_status' => 'unknown', 'count' => 1]]; break;
            case 'excluded-wrong-type': $payload['excluded_status_counts'] = [['post_type' => 'users', 'post_status' => 'trash', 'count' => 1]]; break;
            case 'excluded-duplicate': $payload['excluded_status_counts'] = array_fill(0, 2, ['post_type' => 'page', 'post_status' => 'trash', 'count' => 1]); break;
            case 'record-trash-status':
                foreach ($payload['records'] as &$record) {
                    if ($record['kind'] === 'page' && $record['data']['post_status'] === 'publish') {
                        $record['data']['post_status'] = 'trash';
                        unset($record['content_sha256']);
                        $record = exterior_exone_coex_seal($record);
                        break;
                    }
                }
                unset($record);
                break;
        }
        $payload['source_fingerprint'] = exterior_exone_coex_hash($payload['records']);
        $bytes = exterior_exone_coex_json($payload);
        file_put_contents($path . '/manifest.json', $bytes);
        file_put_contents($path . '/manifest.sha256', hash('sha256', $bytes) . "  manifest.json\n");
        file_put_contents($path . '/COMPLETE', $payload['source_fingerprint'] . "\n");
        exterior_exone_coex_expect_fail(fn() => exterior_exone_coex_validate($path));
    });
}
exterior_exone_coex_test('external relative srcset and unknown media remain reviewable', function () use ($fixture, $root): void {
    $fixture['posts'][4]['post_content'] = '<img src="https://example.invalid/image.jpg"><img src="../images/relative.jpg"><img srcset="https://example.invalid/a.jpg 1x, https://example.invalid/b.jpg 2x"><picture><source srcset="//cdn.example.invalid/remote.webp 1x"><img data-asset="unknown"></picture><div style="background:url(unknown.jpg)"></div>';
    $package = exterior_exone_coex_package($fixture, $root . '/uploads', $root . '/external-media-review');
    $key = exterior_exone_coex_key('post', 5, 'page');
    $records = array_column($package['records'], null, 'source_key');
    $references = $records[$key]['references'];
    $external = array_column($references, 'external_url');
    exterior_exone_coex_assert(in_array('https://example.invalid/image.jpg', $external, true) && in_array('https://example.invalid/a.jpg', $external, true) && in_array('https://example.invalid/b.jpg', $external, true) && in_array('//cdn.example.invalid/remote.webp', $external, true), 'External media reference omitted.');
    exterior_exone_coex_assert(in_array('../images/relative.jpg', array_column($references, 'source_url'), true), 'Relative media reference omitted.');
    exterior_exone_coex_assert(in_array('media-element-without-supported-source-attribute', array_column($references, 'unresolved_media'), true), 'Unknown media syntax silently treated as no references.');
    $review = array_filter($package['analysis']['review'], static fn(array $item): bool => $item['source_key'] === $key);
    exterior_exone_coex_assert(count($review) > 0, 'Media review warning omitted.');
});
exterior_exone_coex_test('package media corruption and unsafe permissions rejected', function () use ($fixture, $root): void {
    $path = $root . '/corrupt-media';
    $payload = exterior_exone_coex_package($fixture, $root . '/uploads', $path);
    foreach ($payload['records'] as $record) {
        if ($record['files']) { $file = $path . '/' . $record['files'][0]['package_path']; break; }
    }
    $original = file_get_contents($file);
    file_put_contents($file, $original . 'x');
    exterior_exone_coex_expect_fail(fn() => exterior_exone_coex_validate($path));
    file_put_contents($file, $original);
    chmod($file, 0644);
    clearstatcache(true, $file);
    exterior_exone_coex_expect_fail(fn() => exterior_exone_coex_validate($path));
});
exterior_exone_coex_test('partial URL inventory preserves unknown and preview states', function () use ($manifest): void {
    $partial = ['source' => 'synthetic partial', 'pages' => [['id' => 1, 'url' => 'https://example.invalid/column/'], ['id' => 2, 'url' => 'https://example.invalid/store/store-aomori/'], ['id' => 3, 'url' => 'https://example.invalid/?page_id=3&preview=true']], 'columns' => [['id' => 4, 'url' => 'https://example.invalid/column/example/']]];
    $plan = exterior_exone_coex_plan($manifest, $partial);
    exterior_exone_coex_assert($plan['status'] === 'unknown_target' && count($plan['preview_urls_needing_review']) === 1 && count($plan['required_adapters']) === 2 && $plan['operations'] === [], 'Partial target treated as complete.');
    exterior_exone_coex_assert(exterior_exone_coex_plan($manifest, [])['target_completeness'] === 'not-provided', 'Absent inventory accepted as empty.');
});
exterior_exone_coex_test('tampered manifest and completion marker', function () use ($root): void {
    $file = $root . '/second/manifest.json';
    file_put_contents($file, (string) file_get_contents($file) . ' ');
    exterior_exone_coex_expect_fail(fn() => exterior_exone_coex_validate($root . '/second'));
    unlink($root . '/package/COMPLETE');
    exterior_exone_coex_expect_fail(fn() => exterior_exone_coex_validate($root . '/package'));
});
exterior_exone_coex_test('no host, socket or apply override', function (): void {
    exterior_exone_coex_expect_fail(fn() => exterior_exone_coex_options(['output' => 'x', 'host' => 'production.invalid'], ['output']));
    exterior_exone_coex_expect_fail(fn() => exterior_exone_coex_options(['output' => 'x', 'socket' => '/other'], ['output']));
    exterior_exone_coex_expect_fail(fn() => exterior_exone_coex_options(['output' => 'x', 'apply' => 'yes'], ['output']));
});

echo "Synthetic tests: $passed passed, $failed failed; database connections 0; sends 0.\n";
echo "Private synthetic artifacts: $root\n";
exit($failed ? 1 : 0);
