<?php
/** 明示的に隔離環境だけで実行するCLI。WP bootstrap・本番の接続口なし。 */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/guard.php';
require_once __DIR__ . '/sql-store.php';
require_once __DIR__ . '/media.php';

set_error_handler(static function (): void { throw new ExteriorExoneImportException('A local runtime operation failed.'); });
umask(0077);
$stage = 'arguments';
$store = null;
$media = null;
$output = null;
$before = null;
$committed = false;
$commitAttempted = false;
try {
    $command = $argv[1] ?? '';
    exterior_exone_import_assert(in_array($command, ['dry-run', 'apply'], true), 'Use dry-run or apply.');
    $options = exterior_exone_import_arguments(array_slice($argv, 2));
    $required = ['package', 'output'];
    if ($command === 'apply') { $required[] = 'confirm'; }
    if (isset($options['fail-at'])) { $required[] = 'fail-at'; }
    exterior_exone_import_options($options, $required);
    exterior_exone_import_assert($command !== 'apply' || $options['confirm'] === 'isolated-lab-only', 'Explicit isolated execution confirmation is required.');
    $failAt = $options['fail-at'] ?? '';
    exterior_exone_import_assert(in_array($failAt, ['', 'after-terms', 'after-posts', 'after-media', 'after-references', 'before-commit'], true) && ($failAt === '' || $command === 'apply'), 'Unsupported failure checkpoint.');
    $checkpoint = static function (string $name) use ($failAt): void {
        if ($failAt !== '' && $name === $failAt) { throw new ExteriorExoneImportException('Requested synthetic failure checkpoint.'); }
    };
    $stage = 'environment';
    $environment = exterior_exone_import_environment();
    $stage = 'package';
    $manifest = exterior_exone_import_validate($options['package']);
    $stage = 'private-output';
    exterior_exone_import_assert(exterior_exone_import_under($options['output'], EXTERIOR_EXONE_IMPORT_LAB_ROOT . '/import-runs'), 'Import output must be inside the isolated lab import-runs directory.');
    $output = exterior_exone_import_private_directory($options['output']);
    $stage = 'connect';
    $store = ExteriorExoneImportSqlStore::open();
    $store->begin();
    $stage = 'protected-baseline';
    $before = $store->protectedSnapshot();
    exterior_exone_import_write($output . '/protected-before.json', exterior_exone_import_json($before));
    $stage = 'plan';
    $plan = exterior_exone_import_plan($manifest, $store->inventory());
    exterior_exone_import_write($output . '/plan.json', exterior_exone_import_json($plan));
    if ($command === 'dry-run') {
        $stage = 'dry-run-plan';
        exterior_exone_import_dry_run_plan($plan);
        $ids = [];
        $postId = $before['bounds']['posts'];
        $termId = $before['bounds']['terms'];
        $taxonomyId = $before['bounds']['term_taxonomy'];
        foreach ($manifest['records'] as $record) {
            $action = $plan['actions'][$record['source_key']];
            $ids[$record['source_key']] = $action['action'] === 'reuse' ? $action['mapping'] : ($record['kind'] === 'term' ? ['id' => ++$termId, 'term_taxonomy_id' => ++$taxonomyId] : ['id' => ++$postId]);
        }
        $context = exterior_exone_import_context($manifest, $ids, $environment['home'], $environment['siteurl'], 1);
        foreach ($manifest['records'] as $record) {
            $expected = exterior_exone_import_transform($record, $context);
            if ($plan['insert_count'] === 0) { $store->verify($expected, $ids[$record['source_key']]); }
        }
        $media = new ExteriorExoneImportMedia($options['package'], $environment['wp_root'] . '/wp-content/uploads', $context, $plan, $output . '/media-journal.jsonl');
        $media->preflight();
        $after = $store->protectedSnapshot($before['bounds']);
        exterior_exone_import_assert($after === $before && $store->writes() === 0, 'Dry-run changed protected data.');
        $store->rollback();
        exterior_exone_import_write($output . '/review-ledger.json', exterior_exone_import_json(exterior_exone_import_ledger($manifest, $context)));
        exterior_exone_import_write($output . '/result.json', exterior_exone_import_json(['schema_version' => 1, 'status' => 'dry-run-complete', 'database_row_writes' => 0, 'planned_insert_count' => $plan['insert_count'], 'reuse_count' => $plan['reuse_count'], 'new_ids_are_simulated' => $plan['insert_count'] > 0, 'protected_rows_unchanged' => true, 'production_ready' => false]));
        echo 'Isolated dry-run complete: ' . $plan['insert_count'] . ' inserts planned, ' . $plan['reuse_count'] . ' reusable; database row writes 0.' . PHP_EOL;
        exit(0);
    }
    $ids = [];
    foreach ($plan['actions'] as $key => $action) { if ($action['action'] === 'reuse') { $ids[$key] = $action['mapping']; } }
    foreach (['term', 'post'] as $phase) {
        $stage = 'allocate-' . $phase;
        foreach ($manifest['records'] as $record) {
            if (($record['kind'] === 'term') !== ($phase === 'term') || isset($ids[$record['source_key']])) { continue; }
            $ids[$record['source_key']] = $store->allocate($record, 1);
        }
        $checkpoint($phase === 'term' ? 'after-terms' : 'after-posts');
    }
    $stage = 'reference-transform';
    $context = exterior_exone_import_context($manifest, $ids, $environment['home'], $environment['siteurl'], 1);
    $rows = [];
    foreach ($manifest['records'] as $record) {
        $key = $record['source_key'];
        $rows[$key] = exterior_exone_import_transform($record, $context);
        if ($plan['actions'][$key]['action'] === 'reuse') { $store->verify($rows[$key], $ids[$key]); }
    }
    $stage = 'media';
    $media = new ExteriorExoneImportMedia($options['package'], $environment['wp_root'] . '/wp-content/uploads', $context, $plan, $output . '/media-journal.jsonl');
    $media->copy($checkpoint);
    $checkpoint('after-media');
    $stage = 'save-references';
    foreach ($rows as $key => $row) { if ($plan['actions'][$key]['action'] === 'insert') { $store->saveCreated($row, $ids[$key]); } }
    $checkpoint('after-references');
    $stage = 'verify';
    foreach ($rows as $key => $row) { $store->verify($row, $ids[$key]); }
    $media->verify();
    $after = $store->protectedSnapshot($before['bounds']);
    exterior_exone_import_assert($after === $before, 'Protected pre-existing rows changed; transaction will be rolled back.');
    exterior_exone_import_write($output . '/protected-before-commit.json', exterior_exone_import_json($after));
    exterior_exone_import_write($output . '/mapping.json', exterior_exone_import_json(['schema_version' => 1, 'source_fingerprint' => $manifest['source_fingerprint'], 'ids' => $ids]));
    exterior_exone_import_write($output . '/review-ledger.json', exterior_exone_import_json(exterior_exone_import_ledger($manifest, $context)));
    $checkpoint('before-commit');
    $stage = 'commit';
    $commitAttempted = true;
    $store->commit();
    $committed = true;
    $stage = 'after-commit-proof';
    $after = $store->protectedSnapshot($before['bounds']);
    exterior_exone_import_assert($after === $before, 'Protected row differences observed after commit; inspection required.');
    exterior_exone_import_write($output . '/protected-after.json', exterior_exone_import_json($after));
    exterior_exone_import_write($output . '/result.json', exterior_exone_import_json(['schema_version' => 1, 'status' => 'isolated-import-complete', 'source_fingerprint' => $manifest['source_fingerprint'], 'records' => count($manifest['records']), 'inserted_records' => $plan['insert_count'], 'reused_records' => $plan['reuse_count'], 'database_statements_writing_new_rows' => $store->writes(), 'protected_rows_unchanged' => true, 'review_items' => count($manifest['analysis']['review']), 'unknown_meta_groups' => count($manifest['unknown_meta']), 'production_ready' => false]));
    exterior_exone_import_write($output . '/COMPLETE', $manifest['source_fingerprint'] . "\n");
    echo 'Isolated import complete: ' . $plan['insert_count'] . ' inserted, ' . $plan['reuse_count'] . ' reused; protected original rows unchanged. Production release remains unverified.' . PHP_EOL;
} catch (Throwable $error) {
    $rollbackVerified = false;
    if (!$committed && !$commitAttempted) {
        try {
            if ($store !== null) { $store->rollback(); }
            if ($media !== null) { $media->rollback(); }
            if ($store !== null && $before !== null) { $rollbackVerified = $store->protectedSnapshot($before['bounds']) === $before; }
        } catch (Throwable $cleanupError) { $rollbackVerified = false; }
    }
    if ($output !== null && is_dir($output)) {
        try { exterior_exone_import_write($output . '/FAILED.json', exterior_exone_import_json(['schema_version' => 1, 'failed_stage' => $stage, 'database_committed' => $committed, 'commit_outcome' => $committed ? 'confirmed' : ($commitAttempted ? 'unknown-do-not-remove-media' : 'not-attempted'), 'rollback_verified' => $rollbackVerified, 'inspection_required' => $commitAttempted || !$rollbackVerified, 'production_ready' => false])); } catch (Throwable $reportError) {}
    }
    fwrite(STDERR, 'Isolated import stopped at ' . $stage . ($commitAttempted ? '; commit was attempted; preserve media and inspect the private result.' : '; no success marker written.') . PHP_EOL);
    exit(1);
}
