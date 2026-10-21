<?php
/** PHP 7.4.33でpackageだけを再検証するCLI。DB・WordPressを起動しない。 */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/reader.php';

try {
    exterior_exone_import_assert(PHP_VERSION === '7.4.33', 'Validation requires the isolated PHP 7.4.33 binary.');
    $options = exterior_exone_import_arguments(array_slice($argv, 1));
    exterior_exone_import_options($options, ['package', 'output']);
    $manifest = exterior_exone_import_validate($options['package']);
    $files = 0;
    $bytes = 0;
    foreach ($manifest['records'] as $record) { foreach ($record['files'] as $file) { $files++; $bytes += $file['bytes']; } }
    $report = ['schema_version' => 1, 'report_type' => 'isolated-import-package-validation', 'checked_at' => gmdate('c'), 'php_version' => PHP_VERSION, 'source_fingerprint' => $manifest['source_fingerprint'], 'records' => count($manifest['records']), 'counts' => $manifest['counts'], 'media_files' => $files, 'media_bytes' => $bytes, 'review_items' => count($manifest['analysis']['review']), 'unknown_meta_groups' => count($manifest['unknown_meta']), 'database_connections' => 0, 'network_requests' => 0, 'production_ready' => false];
    exterior_exone_import_private_directory($options['output']);
    exterior_exone_import_write($options['output'] . '/validation.json', exterior_exone_import_json($report));
    echo 'Validated ' . $report['records'] . ' records and ' . $files . ' media files; database/network access 0; production readiness not evaluated.' . PHP_EOL;
} catch (Throwable $error) {
    // 生のSQL・本文・メール設定・環境資格情報を出さない。
    fwrite(STDERR, "Import package validation failed; no database connection was made.\n");
    exit(1);
}
