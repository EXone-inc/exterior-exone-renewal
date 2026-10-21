<?php
/** 接続先の上書き引数を持たない、隔離環境の識別。 */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/reader.php';

const EXTERIOR_EXONE_IMPORT_LAB_ROOT = EXTERIOR_EXONE_IMPORT_PRIVATE_ROOT . '/coexistence-lab-20261016';
const EXTERIOR_EXONE_IMPORT_LAB_TOKEN = 'ae42f866563746e8f130183c0a01225f5afbbc4b7b3209bd';
const EXTERIOR_EXONE_IMPORT_LAB_PREFIX = 'wp_';

function exterior_exone_import_contract(): array {
    return ['schema_version' => 1, 'environment_id' => 'exone-coexistence-isolated-20261016', 'root' => EXTERIOR_EXONE_IMPORT_LAB_ROOT, 'wp_root' => EXTERIOR_EXONE_IMPORT_LAB_ROOT . '/site/retest', 'socket' => '/private/tmp/exone-coexistence-20261016.sock', 'database' => 'exone_coexistence_lab_20261016', 'prefix' => EXTERIOR_EXONE_IMPORT_LAB_PREFIX, 'home' => 'http://127.0.0.1:18746', 'siteurl' => 'http://127.0.0.1:18746/retest', 'token' => EXTERIOR_EXONE_IMPORT_LAB_TOKEN];
}

function exterior_exone_import_check_environment(array $environment): void {
    $expected = exterior_exone_import_contract();
    exterior_exone_import_assert($expected['token'] !== '', 'The isolated environment token has not been pinned.');
    exterior_exone_import_fields($environment, array_keys($expected), 'isolated environment');
    exterior_exone_import_assert(exterior_exone_import_hash($environment) === exterior_exone_import_hash($expected), 'The fixed isolated environment identity does not match.');
}

function exterior_exone_import_environment(): array {
    exterior_exone_import_assert(PHP_VERSION === '7.4.33', 'The isolated importer requires PHP 7.4.33.');
    $file = EXTERIOR_EXONE_IMPORT_LAB_ROOT . '/environment.json';
    exterior_exone_import_no_symlinks($file);
    exterior_exone_import_assert(is_dir(EXTERIOR_EXONE_IMPORT_LAB_ROOT) && realpath(EXTERIOR_EXONE_IMPORT_LAB_ROOT) === EXTERIOR_EXONE_IMPORT_LAB_ROOT && (fileperms(EXTERIOR_EXONE_IMPORT_LAB_ROOT) & 0777) === 0700, 'The fixed lab root must be private.');
    exterior_exone_import_assert(is_file($file) && (fileperms($file) & 0777) === 0600, 'The isolated environment sentinel is missing or not private.');
    $environment = exterior_exone_import_read_json($file);
    exterior_exone_import_check_environment($environment);
    foreach ([$environment['wp_root'], $environment['wp_root'] . '/wp-content/uploads'] as $directory) {
        exterior_exone_import_no_symlinks($directory);
        exterior_exone_import_assert(is_dir($directory) && realpath($directory) === $directory, 'The isolated WordPress directories are missing.');
    }
    exterior_exone_import_no_symlinks($environment['socket']);
    exterior_exone_import_assert(file_exists($environment['socket']) && filetype($environment['socket']) === 'socket' && fileowner($environment['socket']) === fileowner(EXTERIOR_EXONE_IMPORT_LAB_ROOT), 'The dedicated local socket is missing or foreign.');
    return $environment;
}

function exterior_exone_import_connect(array $environment) {
    exterior_exone_import_check_environment($environment);
    exterior_exone_import_assert(exterior_exone_import_hash($environment) === exterior_exone_import_hash(exterior_exone_import_environment()), 'The current environment sentinel changed before connection.');
    // 本番・Local の接続文字列やwp-config.phpは読まない。
    exterior_exone_import_assert(extension_loaded('mysqli') && function_exists('mysqli_stmt_get_result'), 'The isolated PHP runtime needs mysqli with mysqlnd.');
    mysqli_report(MYSQLI_REPORT_OFF);
    $db = mysqli_init();
    $db->options(MYSQLI_OPT_LOCAL_INFILE, false);
    $db->options(MYSQLI_OPT_CONNECT_TIMEOUT, 3);
    exterior_exone_import_assert(@$db->real_connect('localhost', 'root', '', $environment['database'], 0, $environment['socket']), 'The dedicated isolated database could not be opened.');
    try {
        exterior_exone_import_assert($db->set_charset('utf8mb4'), 'Could not select isolated charset.');
        $result = $db->query('SELECT DATABASE() AS database_name, @@socket AS socket_path, @@datadir AS data_directory, @@skip_networking AS tcp_disabled');
        exterior_exone_import_assert($result !== false, 'Could not identify the isolated database server.');
        $server = $result->fetch_assoc();
        $result->free();
        exterior_exone_import_assert($server['database_name'] === $environment['database'] && $server['socket_path'] === $environment['socket'] && (int) $server['tcp_disabled'] === 1, 'Database server identity does not match the isolated contract.');
        $dataDirectory = rtrim($server['data_directory'], '/');
        exterior_exone_import_no_symlinks($dataDirectory);
        exterior_exone_import_assert(exterior_exone_import_under($dataDirectory, EXTERIOR_EXONE_IMPORT_LAB_ROOT) && is_dir($dataDirectory), 'The database files are outside the isolated lab.');
        $options = $db->query('SELECT option_name,option_value FROM `' . $environment['prefix'] . "options` WHERE option_name IN ('home','siteurl')");
        exterior_exone_import_assert($options !== false, 'Could not identify the isolated WordPress options.');
        $urls = [];
        while ($row = $options->fetch_assoc()) {
            exterior_exone_import_assert(!isset($urls[$row['option_name']]), 'Duplicate home/siteurl option.');
            $urls[$row['option_name']] = $row['option_value'];
        }
        $options->free();
        exterior_exone_import_assert(count($urls) === 2 && ($urls['home'] ?? null) === $environment['home'] && ($urls['siteurl'] ?? null) === $environment['siteurl'], 'WordPress URLs do not match the isolated loopback contract.');
        $author = $db->query('SELECT ID FROM `' . $environment['prefix'] . 'users` WHERE ID=1');
        exterior_exone_import_assert($author !== false && $author->num_rows === 1, 'The designated destination author ID 1 does not exist.');
        $author->free();
        return $db;
    } catch (Throwable $error) {
        $db->close();
        throw new ExteriorExoneImportException('Isolated database identity verification failed.');
    }
}
