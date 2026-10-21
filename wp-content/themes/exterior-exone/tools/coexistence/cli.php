<?php
/** 本番に接続しない、併存移行の準備用 CLI。 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/lib.php';

try {
    $command = $argv[1] ?? 'help';
    $arguments = exterior_exone_coex_arguments(array_slice($argv, 2));
    switch ($command) {
        case 'export-local':
            exterior_exone_coex_options($arguments, ['output']);
            exterior_exone_coex_output_guard($arguments['output']);
            $dependencies = exterior_exone_coex_theme_dependencies();
            $snapshot = exterior_exone_coex_read_local();
            $snapshot['theme_dependencies'] = $dependencies;
            $manifest = exterior_exone_coex_package($snapshot, EXTERIOR_EXONE_COEX_SOURCE_ROOT . '/wp-content/uploads', $arguments['output']);
            echo json_encode(['status' => 'prepared', 'schema_version' => 1, 'counts' => $manifest['counts'], 'source_fingerprint' => $manifest['source_fingerprint'], 'production_ready' => false], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), "\n";
            break;
        case 'validate-package':
            exterior_exone_coex_options($arguments, ['package']);
            $manifest = exterior_exone_coex_validate($arguments['package']);
            echo json_encode(['status' => 'valid', 'counts' => $manifest['counts'], 'source_fingerprint' => $manifest['source_fingerprint'], 'production_ready' => false], JSON_THROW_ON_ERROR), "\n";
            break;
        case 'plan-offline':
            exterior_exone_coex_options($arguments, isset($arguments['inventory']) ? ['package', 'inventory', 'output'] : ['package', 'output']);
            $manifest = exterior_exone_coex_validate($arguments['package']);
            $inventory = isset($arguments['inventory']) ? exterior_exone_coex_read_json($arguments['inventory']) : [];
            $plan = exterior_exone_coex_plan($manifest, $inventory);
            exterior_exone_coex_write_report($arguments['output'], $plan);
            echo json_encode(['status' => $plan['status'], 'counts' => $plan['counts'], 'production_ready' => false, 'database_writes' => 0], JSON_THROW_ON_ERROR), "\n";
            if ($plan['counts']['conflicts'] > 0) {
                exit(2);
            }
            break;
        case 'help':
            echo "Preparation only; no importer or production connection.\n",
                "  php cli.php export-local --output PRIVATE_ROOT/package-NEW\n",
                "  php cli.php validate-package --package PRIVATE_ROOT/package-NEW\n",
                "  php cli.php plan-offline --package PATH [--inventory PATH] --output PRIVATE_ROOT/plan-NEW\n",
                "PRIVATE_ROOT: ", EXTERIOR_EXONE_COEX_PRIVATE_ROOT, "\n",
                "Outputs must be new private directories. See README.md.\n";
            break;
        default:
            throw new RuntimeException('Unknown command; use help.');
    }
} catch (Throwable $error) {
    // DB の例外・本文・フォーム宛先などは標準出力/ログへ転記しない。
    fwrite(STDERR, 'Preparation failed: ' . ($error instanceof ExteriorExoneCoexException ? $error->getMessage() : 'operation failed; inspect local input and private files') . "\n");
    exit(1);
}
