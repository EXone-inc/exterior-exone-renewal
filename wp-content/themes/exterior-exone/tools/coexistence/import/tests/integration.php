<?php
/** 親担当が隔離DB復元後に明示実行するS2試験。元DBや本番には接続しない。 */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/guard.php';

try {
    $options = exterior_exone_import_arguments(array_slice($argv, 1));
    exterior_exone_import_options($options, ['package', 'confirm']);
    exterior_exone_import_assert($options['confirm'] === 'isolated-lab-only', 'Explicit isolated integration confirmation is required.');
    exterior_exone_import_environment();
    $manifest = exterior_exone_import_validate($options['package']);
    exterior_exone_import_assert(extension_loaded('mysqli'), 'The integration runtime requires mysqli.');
    $directory = EXTERIOR_EXONE_IMPORT_LAB_ROOT . '/import-runs/integration-' . gmdate('Ymd-His') . '-' . bin2hex(random_bytes(4));
    exterior_exone_import_private_directory($directory);
    $run = static function (string $name, string $command, string $failure = '') use ($options, $directory): array {
        $output = $directory . '/' . $name;
        $arguments = [PHP_BINARY, '-n', dirname(__DIR__) . '/cli.php', $command, '--package', $options['package'], '--output', $output];
        if ($command === 'apply') { $arguments = array_merge($arguments, ['--confirm', 'isolated-lab-only']); }
        if ($failure !== '') { $arguments = array_merge($arguments, ['--fail-at', $failure]); }
        $process = proc_open($arguments, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        exterior_exone_import_assert(is_resource($process), 'Could not run guarded isolated subprocess.');
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $status = proc_close($process);
        // 子CLIの出力もprivateにのみ保存。テスト結果には本文等を含めない。
        exterior_exone_import_write($directory . '/' . $name . '-execution.json', exterior_exone_import_json(['exit_code' => $status, 'stdout' => $stdout, 'stderr' => $stderr]));
        exterior_exone_import_assert($status === ($failure === '' ? 0 : 1), 'Unexpected isolated subprocess exit status.');
        $report = exterior_exone_import_read_json($output . ($failure === '' ? '/result.json' : '/FAILED.json'));
        if ($failure !== '') {
            exterior_exone_import_assert($report['database_committed'] === false && $report['rollback_verified'] === true && $report['inspection_required'] === false, 'Failure rollback did not verify the original rows and owned media.');
        }
        return $report;
    };
    $initial = $run('initial-dry-run', 'dry-run');
    exterior_exone_import_assert($initial['planned_insert_count'] === count($manifest['records']) && $initial['reuse_count'] === 0, 'Failure integration requires a pristine renewal namespace; no existing imports are removed automatically.');
    $cases = [];
    foreach (['after-terms', 'after-posts', 'after-media', 'after-references', 'before-commit'] as $failure) {
        $run('fail-' . $failure, 'apply', $failure);
        $after = $run('verify-' . $failure, 'dry-run');
        exterior_exone_import_assert($after['planned_insert_count'] === count($manifest['records']) && $after['reuse_count'] === 0 && $after['protected_rows_unchanged'] === true, 'Failure left partial renewal records or files.');
        $cases[] = $failure;
    }
    $first = $run('apply', 'apply');
    exterior_exone_import_assert($first['inserted_records'] === count($manifest['records']) && $first['reused_records'] === 0 && $first['protected_rows_unchanged'] === true, 'Initial import count or protection check failed.');
    $second = $run('reuse', 'apply');
    exterior_exone_import_assert($second['inserted_records'] === 0 && $second['reused_records'] === count($manifest['records']) && $second['database_statements_writing_new_rows'] === 0 && $second['protected_rows_unchanged'] === true, 'Re-execution added or changed records.');
    $firstMap = exterior_exone_import_read_json($directory . '/apply/mapping.json');
    $secondMap = exterior_exone_import_read_json($directory . '/reuse/mapping.json');
    exterior_exone_import_assert(exterior_exone_import_hash($firstMap) === exterior_exone_import_hash($secondMap), 'Re-execution changed destination mappings.');
    exterior_exone_import_write($directory . '/integration-result.json', exterior_exone_import_json(['schema_version' => 1, 'php' => PHP_VERSION, 'status' => 'passed', 'failure_checkpoints' => $cases, 'records' => count($manifest['records']), 'source_fingerprint' => $manifest['source_fingerprint'], 'initial_insert_records' => $first['inserted_records'], 'second_insert_records' => $second['inserted_records'], 'protected_original_rows_unchanged' => true, 'wordpress_bootstraps' => 0, 'production_ready' => false]));
    echo 'PASS isolated SQL integration: five rollback/retry stages, full import and unchanged reuse. Theme/runtime release checks remain separate.' . PHP_EOL;
} catch (Throwable $error) {
    fwrite(STDERR, "Isolated integration did not pass; inspect the new private import-runs evidence.\n");
    exit(1);
}
