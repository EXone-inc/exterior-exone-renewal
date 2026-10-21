<?php
/** Fixed production CLI. Deployment/execution belongs to the parent after explicit authorization. */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/guard.php';
require_once __DIR__ . '/sql-store.php';
require_once __DIR__ . '/media.php';
set_error_handler(static function (): void { throw new ExteriorExoneProductionException('A production operation failed; sensitive diagnostics suppressed.'); });
umask(0077);
$stage = 'arguments'; $store = null; $observer = null; $media = null; $before = null; $output = null; $commitAttempted = false; $committed = false;
try {
    $command = $argv[1] ?? '';
    exterior_exone_production_assert(in_array($command, ['dry-run','apply'], true), 'Expected dry-run or apply.');
    $options = exterior_exone_production_arguments(array_slice($argv, 2));
    exterior_exone_production_options($options, $command === 'apply' ? ['confirm','reviewed-plan'] : []);
    if ($command === 'apply') { exterior_exone_production_assert($options['confirm'] === 'approved-production-append' && $options['reviewed-plan'] === 'offline-plan.json', 'Explicit reviewed production append confirmation is required.'); }
    $stage = 'fixed-environment'; $environment = exterior_exone_production_environment();
    $stage = 'package'; $manifest = exterior_exone_production_validate();
    $sourceManifestHash = hash_file('sha256', EXTERIOR_EXONE_PRODUCTION_PRIVATE_ROOT . '/package/manifest.json');
    $stage = 'private-output';
    $run = $command . '-' . gmdate('Ymd-His') . '-' . bin2hex(random_bytes(4));
    $output = exterior_exone_production_private_directory(EXTERIOR_EXONE_PRODUCTION_PRIVATE_ROOT . '/runs/' . $run);
    exterior_exone_production_assert(disk_free_space(EXTERIOR_EXONE_PRODUCTION_UPLOADS_ROOT) >= 80 * 1024 * 1024 && disk_free_space(EXTERIOR_EXONE_PRODUCTION_PRIVATE_ROOT) >= 32 * 1024 * 1024, 'Insufficient verified free disk space.');
    $offlinePlanSha = '';
    if ($command === 'apply') {
        $stage = 'authorization';
        $path = EXTERIOR_EXONE_PRODUCTION_PRIVATE_ROOT . '/offline-plan.json';
        exterior_exone_production_no_symlinks($path);
        exterior_exone_production_assert(is_file($path) && (fileperms($path) & 0777) === 0600, 'Reviewed result must remain private.');
        $offlinePlanSha = hash_file('sha256', $path);
        $reviewed = exterior_exone_production_read_json($path);
        exterior_exone_production_reviewed_offline_plan($reviewed, $sourceManifestHash);
        exterior_exone_production_assert(hash_file('sha256', $path) === $offlinePlanSha, 'Reviewed report changed while reading.');
        exterior_exone_production_authorization($offlinePlanSha);
        exterior_exone_production_assert(!file_exists(EXTERIOR_EXONE_PRODUCTION_PRIVATE_ROOT . '/APPLY-STARTED.json'), 'Previous apply needs inspection; blind retries are prohibited.');
    }
    $stage = 'connect';
    $observer = ExteriorExoneProductionSqlStore::openReadOnly();
    $store = $command === 'apply' ? ExteriorExoneProductionSqlStore::openForApply($offlinePlanSha) : ExteriorExoneProductionSqlStore::openReadOnly();
    $before = $observer->freshProtectedSnapshot();
    // Take the baseline before opening the reader's repeatable-read snapshot.
    // Otherwise a change in that gap could be invisible to the planner and both observations.
    $store->begin();
    exterior_exone_production_write($output . '/protected-before.json', exterior_exone_production_json($before));
    $stage = 'plan'; $plan = exterior_exone_production_plan($manifest, $store->inventory());
    exterior_exone_production_dry_run_plan($plan);
    exterior_exone_production_assert($command !== 'apply' || $plan['insert_count'] === 120, 'Apply only appends a fresh namespace; use dry-run to verify completed reuse.');
    exterior_exone_production_write($output . '/plan.json', exterior_exone_production_json($plan));
    $bounds = $store->bounds(); $ids = []; $postId = $bounds['posts']; $termId = $bounds['terms']; $taxonomyId = $bounds['term_taxonomy'];
    foreach ($manifest['records'] as $record) {
        $action = $plan['actions'][$record['source_key']];
        $ids[$record['source_key']] = $action['action'] === 'reuse' ? $action['mapping'] : ($record['kind'] === 'term' ? ['id' => ++$termId, 'term_taxonomy_id' => ++$taxonomyId] : ['id' => ++$postId]);
    }
    $context = exterior_exone_production_context($manifest, $ids, $environment['home'], $environment['siteurl'], 1);
    $plannedRows = $plan['insert_count'] > 0 ? exterior_exone_production_planned_rows($manifest, $context) : ['posts'=>0,'postmeta'=>0,'terms'=>0,'termmeta'=>0,'term_taxonomy'=>0,'term_relationships'=>0];
    if ($command === 'apply') { exterior_exone_production_assert(exterior_exone_production_hash($plannedRows) === exterior_exone_production_hash($reviewed['planned_sql_rows']), 'Actual planned rows differ from the approved offline plan.'); }
    $media = new ExteriorExoneProductionMedia(EXTERIOR_EXONE_PRODUCTION_PRIVATE_ROOT . '/package', EXTERIOR_EXONE_PRODUCTION_UPLOADS_ROOT, $context, $plan, $output . '/media-journal.jsonl');
    $media->preflight();
    if ($command === 'dry-run') {
        $stage = 'dry-run-verify';
        if ($plan['insert_count'] === 0) { foreach ($manifest['records'] as $record) { $store->verify(exterior_exone_production_transform($record, $context), $ids[$record['source_key']]); } }
        if ($plan['insert_count'] === 0) { $media->verify(); }
        $store->rollback(); $after = $observer->freshProtectedSnapshot();
        exterior_exone_production_assert($after === $before && $store->writes() === 0, 'Concurrent changes detected during dry-run; do not use this plan as verified.');
        exterior_exone_production_validate(); exterior_exone_production_assert(hash_file('sha256', EXTERIOR_EXONE_PRODUCTION_PRIVATE_ROOT . '/package/manifest.json') === $sourceManifestHash, 'Package changed during dry-run.');
        exterior_exone_production_write($output . '/review-ledger.json', exterior_exone_production_json(exterior_exone_production_ledger($manifest, $context)));
        exterior_exone_production_write($output . '/result.json', exterior_exone_production_json(['schema_version' => 1, 'status' => 'production-dry-run-complete', 'checked_at' => gmdate('c'), 'source_fingerprint' => EXTERIOR_EXONE_PRODUCTION_FINGERPRINT, 'database_row_writes' => 0, 'public_files_written' => 0, 'planned_insert_count' => $plan['insert_count'], 'planned_sql_rows' => $plannedRows, 'reuse_count' => $plan['reuse_count'], 'simulated_new_ids' => $plan['insert_count'] > 0, 'concurrent_changes_detected' => false, 'protected_snapshot_sha256' => exterior_exone_production_hash($before), 'theme_activation_permitted' => false, 'production_ready' => false]));
        echo 'Production read-only dry-run complete: ' . $plan['insert_count'] . ' records planned, ' . $plan['reuse_count'] . ' reusable; public/DB writes 0. Run: ' . $run . PHP_EOL; exit(0);
    }
    $stage = 'apply-marker';
    exterior_exone_production_authorization($offlinePlanSha);
    exterior_exone_production_write(EXTERIOR_EXONE_PRODUCTION_PRIVATE_ROOT . '/APPLY-STARTED.json', exterior_exone_production_json(['schema_version' => 1, 'run' => $run, 'source_fingerprint' => EXTERIOR_EXONE_PRODUCTION_FINGERPRINT, 'approved_offline_plan_sha256' => $offlinePlanSha, 'production_dry_run_skipped_by_user' => true, 'started_at' => gmdate('c'), 'automatic_retry_allowed' => false]));
    $ids = [];
    foreach (['term','post'] as $phase) {
        $stage = 'allocate-' . $phase;
        foreach ($manifest['records'] as $record) { if (($record['kind'] === 'term') === ($phase === 'term')) { $ids[$record['source_key']] = $store->allocate($record, 1); } }
        exterior_exone_production_write($output . '/allocated-' . $phase . '.json', exterior_exone_production_json(['ids' => $ids, 'owned_rows' => $store->ownership()]));
    }
    $context = exterior_exone_production_context($manifest, $ids, $environment['home'], $environment['siteurl'], 1); $rows = [];
    // Media relative paths depend on source IDs/fingerprint, never on simulated target IDs.
    $stage = 'media-copy'; $media->copy(static function (): void {});
    $stage = 'save-references';
    foreach ($manifest['records'] as $record) { $key = $record['source_key']; $rows[$key] = exterior_exone_production_transform($record, $context); $store->saveCreated($rows[$key], $ids[$key]); }
    exterior_exone_production_write($output . '/mapping.json', exterior_exone_production_json(['schema_version' => 1, 'source_fingerprint' => EXTERIOR_EXONE_PRODUCTION_FINGERPRINT, 'ids' => $ids, 'owned_rows' => $store->ownership()]));
    $stage = 'verify-before-commit';
    foreach ($rows as $key => $row) { $store->verify($row, $ids[$key]); } $media->verify();
    exterior_exone_production_validate(); exterior_exone_production_assert(hash_file('sha256', EXTERIOR_EXONE_PRODUCTION_PRIVATE_ROOT . '/package/manifest.json') === $sourceManifestHash, 'Package changed during apply.');
    $observed = $observer->freshProtectedSnapshot(); exterior_exone_production_assert($observed === $before, 'Concurrent updates observed before commit; stopping without claiming unchanged data.');
    exterior_exone_production_write($output . '/verified-before-commit.json', exterior_exone_production_json(['protected' => $observed, 'owned_rows' => $store->ownership(), 'destination_hashes' => array_map(static function (array $row): string { return $row['destination_sha256']; }, $rows), 'commit_outcome' => 'not-attempted']));
    $stage = 'commit'; $commitAttempted = true; $store->commit(); $committed = true;
    $stage = 'observe-after-commit';
    foreach ($rows as $key => $row) { $store->verify($row, $ids[$key]); } $media->verify();
    $observed = $observer->freshProtectedSnapshot($store->ownership());
    exterior_exone_production_assert($observed === $before, 'Post-commit change detected; keep new data/media and inspect.');
    exterior_exone_production_write($output . '/protected-after.json', exterior_exone_production_json($observed));
    exterior_exone_production_write($output . '/result.json', exterior_exone_production_json(['schema_version' => 1, 'status' => 'production-append-complete-inactive', 'checked_at' => gmdate('c'), 'source_fingerprint' => EXTERIOR_EXONE_PRODUCTION_FINGERPRINT, 'inserted_records' => 120, 'pre_apply_production_dry_run_performed' => false, 'approved_offline_plan_sha256' => $offlinePlanSha, 'reused_records' => 0, 'owned_rows' => array_map('count', $store->ownership()), 'sql_write_statements' => $store->writes(), 'concurrent_changes_detected_at_observations' => false, 'global_write_lock_used' => false, 'post_commit_race_window_remains' => true, 'theme_activated' => false, 'production_ready' => false]));
    exterior_exone_production_write($output . '/COMPLETE', EXTERIOR_EXONE_PRODUCTION_FINGERPRINT . "\n");
    echo 'Production append verified, old theme remains selected. Run: ' . $run . PHP_EOL;
} catch (Throwable $error) {
    $rollbackConfirmed = false; $cleanupConfirmed = false; $oldSnapshotEqual = null;
    if (!$commitAttempted) {
        try {
            if ($store !== null) { $store->rollback(); $rollbackConfirmed = true; }
            if ($observer !== null && $before !== null) { $oldSnapshotEqual = $observer->freshProtectedSnapshot() === $before; }
            // A concurrent edit could have introduced a reference to a new image.
            // Retain media unless rollback and the protected rows are both confirmed.
            if ($media !== null && $rollbackConfirmed && $oldSnapshotEqual === true) {
                $media->rollback(); $cleanupConfirmed = true;
                $oldSnapshotEqual = $observer->freshProtectedSnapshot() === $before;
            }
        } catch (Throwable $ignored) {}
    }
    if ($output !== null) { try { exterior_exone_production_write($output . '/FAILED.json', exterior_exone_production_json(['schema_version' => 1, 'failed_stage' => $stage, 'committed' => $committed, 'commit_outcome' => $committed ? 'confirmed' : ($commitAttempted ? 'unknown-preserve-media' : 'not-attempted'), 'transaction_rollback_confirmed' => $rollbackConfirmed, 'owned_media_cleanup_confirmed' => $cleanupConfirmed, 'protected_snapshot_equal' => $oldSnapshotEqual, 'inspection_required' => true, 'automatic_retry_allowed' => false, 'production_ready' => false])); } catch (Throwable $ignored) {} }
    fwrite(STDERR, 'Production runner stopped at ' . $stage . '; inspect the private run evidence. No success marker issued.' . PHP_EOL); exit(1);
}
