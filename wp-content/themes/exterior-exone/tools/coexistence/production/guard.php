<?php
/** 本番専用CLI契約。WPを実行せず、認証値を表示しない。接続先上書き不可。 */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/reader.php';
const EXTERIOR_EXONE_PRODUCTION_WP_ROOT = '/home/xs357102/exterior-exone.com/public_html/retest';
const EXTERIOR_EXONE_PRODUCTION_FINGERPRINT = '9ebda8d9fb3a5479ddf173d30bb3e1852dae77bcf632e81a8e11c5df60e0c791';
// 親担当の読取preflight実測後にだけ固定する。未確定なら接続前に停止。
const EXTERIOR_EXONE_PRODUCTION_DB_HOST = 'localhost';
const EXTERIOR_EXONE_PRODUCTION_DB_SOCKET = '/var/lib/mysql/mysql.sock';
const EXTERIOR_EXONE_PRODUCTION_UPLOADS_ROOT = '/home/xs357102/exterior-exone.com/public_html/retest/wp-content/uploads';
const EXTERIOR_EXONE_PRODUCTION_UPLOADS_URL = 'https://exterior-exone.com/retest/wp-content/uploads';

function exterior_exone_production_contract(): array {
    return ['schema_version' => 1, 'environment_id' => 'exone-production-coexistence-20261016', 'root' => EXTERIOR_EXONE_PRODUCTION_PRIVATE_ROOT, 'wp_root' => EXTERIOR_EXONE_PRODUCTION_WP_ROOT, 'database' => 'xs357102_w8mov', 'prefix' => 'wp_', 'home' => 'https://exterior-exone.com', 'siteurl' => 'https://exterior-exone.com/retest', 'php' => '7.4.33', 'db_host' => EXTERIOR_EXONE_PRODUCTION_DB_HOST, 'db_socket' => EXTERIOR_EXONE_PRODUCTION_DB_SOCKET, 'uploads_root' => EXTERIOR_EXONE_PRODUCTION_UPLOADS_ROOT, 'uploads_url' => EXTERIOR_EXONE_PRODUCTION_UPLOADS_URL];
}

function exterior_exone_production_check_contract(array $value): void {
    exterior_exone_production_fields($value, array_keys(exterior_exone_production_contract()), 'production environment');
    exterior_exone_production_assert(exterior_exone_production_hash($value) === exterior_exone_production_hash(exterior_exone_production_contract()), 'Production environment identity differs.');
    exterior_exone_production_assert(EXTERIOR_EXONE_PRODUCTION_DB_HOST !== '' && EXTERIOR_EXONE_PRODUCTION_DB_SOCKET !== '', 'Server identity has not been pinned; no connection is allowed.');
}

function exterior_exone_production_environment(): array {
    exterior_exone_production_assert(PHP_VERSION === '7.4.33' && PHP_SAPI === 'cli', 'Use the verified PHP 7.4.33 CLI.');
    $root = EXTERIOR_EXONE_PRODUCTION_PRIVATE_ROOT;
    foreach ([$root, EXTERIOR_EXONE_PRODUCTION_WP_ROOT, EXTERIOR_EXONE_PRODUCTION_UPLOADS_ROOT, __DIR__] as $path) {
        exterior_exone_production_no_symlinks($path);
        exterior_exone_production_assert(is_dir($path) && realpath($path) === $path, 'A fixed server directory is missing or aliased.');
    }
    exterior_exone_production_assert(__DIR__ === $root . '/runner' && (fileperms($root) & 0777) === 0700 && (fileperms(__DIR__) & 0777) === 0700, 'Runner must be deployed only inside the fixed private root.');
    exterior_exone_production_assert(function_exists('posix_geteuid') && fileowner($root) === posix_geteuid() && fileowner(EXTERIOR_EXONE_PRODUCTION_WP_ROOT) === posix_geteuid(), 'Server ownership differs.');
    $file = $root . '/environment.json'; exterior_exone_production_no_symlinks($file);
    exterior_exone_production_assert(is_file($file) && (fileperms($file) & 0777) === 0600, 'Private environment sentinel missing.');
    $environment = exterior_exone_production_read_json($file); exterior_exone_production_check_contract($environment); return $environment;
}

/** PHP文字列literalのみを復号する。eval/include/WordPress起動は行わない。 */
function exterior_exone_production_literal(string $token): string {
    $quote = $token[0] ?? ''; exterior_exone_production_assert(($quote === "'" || $quote === '"') && substr($token, -1) === $quote, 'A string literal is required.');
    $body = substr($token, 1, -1); $out = '';
    for ($i = 0; $i < strlen($body); $i++) {
        $char = $body[$i];
        if ($char !== '\\') { $out .= $char; continue; }
        exterior_exone_production_assert(isset($body[$i + 1]), 'Unterminated literal escape.'); $next = $body[++$i];
        if ($quote === "'") { $out .= ($next === '\\' || $next === "'") ? $next : '\\' . $next; continue; }
        $known = ['n' => "\n", 'r' => "\r", 't' => "\t", 'v' => "\v", 'e' => chr(27), 'f' => "\f", '\\' => '\\', '$' => '$', '"' => '"'];
        if (isset($known[$next])) { $out .= $known[$next]; }
        elseif ($next === 'x' && isset($body[$i + 1]) && ctype_xdigit($body[$i + 1])) { $hex = $body[++$i]; if (isset($body[$i + 1]) && ctype_xdigit($body[$i + 1])) { $hex .= $body[++$i]; } $out .= chr(hexdec($hex)); }
        elseif ($next >= '0' && $next <= '7') { $octal = $next; for ($j = 0; $j < 2 && isset($body[$i + 1]) && $body[$i + 1] >= '0' && $body[$i + 1] <= '7'; $j++) { $octal .= $body[++$i]; } $out .= chr(octdec($octal) & 255); }
        else { $out .= '\\' . $next; }
    }
    return $out;
}

function exterior_exone_production_parse_config(string $php): array {
    exterior_exone_production_assert(function_exists('token_get_all') && strlen($php) < 1024 * 1024, 'Config tokenization is unavailable.');
    $tokens = array_values(array_filter(token_get_all($php), static function ($token): bool { return !is_array($token) || !in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT, T_OPEN_TAG, T_CLOSE_TAG], true); }));
    $literal = static function ($token): string { exterior_exone_production_assert(is_array($token) && $token[0] === T_CONSTANT_ENCAPSED_STRING, 'Config values must be literal strings.'); return exterior_exone_production_literal($token[1]); };
    $result = [];
    for ($i = 0; $i < count($tokens); $i++) {
        $token = $tokens[$i]; if (!is_array($token)) { continue; }
        if ($token[0] === T_STRING && strtolower($token[1]) === 'define' && ($tokens[$i + 1] ?? null) === '(' && is_array($tokens[$i + 2] ?? null) && $tokens[$i + 2][0] === T_CONSTANT_ENCAPSED_STRING) {
            $name = $literal($tokens[$i + 2]);
            if (!in_array($name, ['DB_NAME','DB_USER','DB_PASSWORD','DB_HOST','DB_CHARSET','DB_COLLATE','WP_CONTENT_DIR','WP_CONTENT_URL','UPLOADS'], true)) { continue; }
            exterior_exone_production_assert(!isset($result[$name]) && ($tokens[$i + 3] ?? null) === ',' && ($tokens[$i + 5] ?? null) === ')', 'Ambiguous or computed config definition.');
            $result[$name] = $literal($tokens[$i + 4] ?? null);
        }
        if ($token[0] === T_VARIABLE && $token[1] === '$table_prefix') {
            exterior_exone_production_assert(!isset($result['table_prefix']) && ($tokens[$i + 1] ?? null) === '=' && ($tokens[$i + 3] ?? null) === ';', 'Ambiguous table prefix.');
            $result['table_prefix'] = $literal($tokens[$i + 2] ?? null);
        }
    }
    foreach (['DB_NAME','DB_USER','DB_PASSWORD','DB_HOST','table_prefix'] as $name) { exterior_exone_production_assert(isset($result[$name]), 'Required literal config value missing.'); }
    exterior_exone_production_assert($result['DB_NAME'] === 'xs357102_w8mov' && $result['table_prefix'] === 'wp_' && $result['DB_HOST'] === EXTERIOR_EXONE_PRODUCTION_DB_HOST && $result['DB_USER'] !== '', 'Config does not identify the pinned production database.');
    exterior_exone_production_assert(!isset($result['WP_CONTENT_DIR']) && !isset($result['WP_CONTENT_URL']) && !isset($result['UPLOADS']), 'Custom content/upload constants need explicit preflight support.');
    return $result;
}

function exterior_exone_production_connect() {
    exterior_exone_production_environment();
    $file = EXTERIOR_EXONE_PRODUCTION_WP_ROOT . '/wp-config.php'; exterior_exone_production_no_symlinks($file);
    exterior_exone_production_assert(is_file($file) && filesize($file) < 1024 * 1024, 'Fixed config is unavailable.');
    $before = hash_file('sha256', $file); $config = exterior_exone_production_parse_config((string) file_get_contents($file));
    exterior_exone_production_assert(hash_file('sha256', $file) === $before, 'Config changed while reading.');
    exterior_exone_production_assert(extension_loaded('mysqli') && function_exists('mysqli_stmt_get_result'), 'mysqli/mysqlnd is required.');
    mysqli_report(MYSQLI_REPORT_OFF); $db = mysqli_init(); $db->options(MYSQLI_OPT_LOCAL_INFILE, false); $db->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
    try {
        exterior_exone_production_assert(@$db->real_connect(EXTERIOR_EXONE_PRODUCTION_DB_HOST, $config['DB_USER'], $config['DB_PASSWORD'], 'xs357102_w8mov', 0, EXTERIOR_EXONE_PRODUCTION_DB_SOCKET), 'Production DB connection failed.');
        unset($config);
        exterior_exone_production_assert($db->set_charset('utf8mb4') && $db->query('SET SESSION TRANSACTION READ ONLY'), 'Could not set read-only session.');
        $result = $db->query('SELECT DATABASE() AS db,@@hostname AS host,@@socket AS socket,VERSION() AS version'); exterior_exone_production_assert($result !== false, 'DB identity read failed.'); $server = $result->fetch_assoc(); $result->free();
        exterior_exone_production_assert($server['db'] === 'xs357102_w8mov' && $server['socket'] === EXTERIOR_EXONE_PRODUCTION_DB_SOCKET && preg_match('/\A10\.5\.[0-9]+-MariaDB/', $server['version']) === 1, 'Production DB server identity differs.');
        $result = $db->query("SELECT option_name,option_value FROM wp_options WHERE option_name IN ('home','siteurl','template','stylesheet','upload_path','upload_url_path')"); exterior_exone_production_assert($result !== false, 'Production URL options unavailable.');
        $values = []; while ($row = $result->fetch_assoc()) { exterior_exone_production_assert(!isset($values[$row['option_name']]), 'Duplicate production option.'); $values[$row['option_name']] = $row['option_value']; } $result->free();
        exterior_exone_production_assert(($values['home'] ?? '') === 'https://exterior-exone.com' && ($values['siteurl'] ?? '') === 'https://exterior-exone.com/retest' && ($values['template'] ?? '') === 'affinger' && ($values['stylesheet'] ?? '') === 'affinger-child', 'Production URL/theme guard differs; theme must remain old.');
        exterior_exone_production_assert(in_array($values['upload_path'] ?? '', ['', 'wp-content/uploads', EXTERIOR_EXONE_PRODUCTION_UPLOADS_ROOT], true) && in_array($values['upload_url_path'] ?? '', ['', EXTERIOR_EXONE_PRODUCTION_UPLOADS_URL], true), 'Production uploads settings differ from the pinned paths.');
        $result = $db->query('SELECT ID FROM wp_users WHERE ID=1'); exterior_exone_production_assert($result !== false && $result->num_rows === 1, 'Pinned author is unavailable.'); $result->free(); return $db;
    } catch (Throwable $error) { unset($config); @$db->close(); throw new ExteriorExoneProductionException('Production identity/connect preflight failed; credentials are never logged.'); }
}

function exterior_exone_production_validate(): array {
    $directory = EXTERIOR_EXONE_PRODUCTION_PRIVATE_ROOT . '/package';
    $manifest = exterior_exone_production_validate_files($directory);
    $count = 0; $bytes = 0; foreach ($manifest['records'] as $record) { foreach ($record['files'] as $file) { $count++; $bytes += $file['bytes']; } }
    exterior_exone_production_assert($manifest['source_fingerprint'] === EXTERIOR_EXONE_PRODUCTION_FINGERPRINT && count($manifest['records']) === 120 && $count === 195 && $bytes === 35343166, 'Only the pinned r3 package is accepted.');
    return $manifest;
}

function exterior_exone_production_recent($value): void {
    exterior_exone_production_assert(is_string($value) && preg_match('/\A\d{4}-\d\d-\d\dT\d\d:\d\d:\d\d\+00:00\z/', $value) === 1, 'Expected UTC evidence time.');
    $time = strtotime($value); exterior_exone_production_assert($time !== false && gmdate('c', $time) === $value && $time <= time() && time() - $time <= 86400, 'Evidence is expired or in the future.');
}

function exterior_exone_production_reviewed_offline_plan(array $report, string $manifestSha): void {
    exterior_exone_production_fields($report, ['schema_version','document_type','created_at','source_fingerprint','source_manifest_sha256','source_counts','source_record_count','source_media_files','source_media_bytes','planned_sql_rows','production_dry_run_performed','simulated_new_ids','theme_activation_permitted','production_ready'], 'reviewed offline plan');
    exterior_exone_production_digest($manifestSha);
    exterior_exone_production_assert($report['schema_version'] === 1 && $report['document_type'] === 'production-offline-review-plan' && $report['source_fingerprint'] === EXTERIOR_EXONE_PRODUCTION_FINGERPRINT && $report['source_manifest_sha256'] === $manifestSha && $report['source_record_count'] === 120 && $report['source_media_files'] === 195 && $report['source_media_bytes'] === 35343166 && $report['production_dry_run_performed'] === false && $report['simulated_new_ids'] === true && $report['theme_activation_permitted'] === false && $report['production_ready'] === false, 'The fixed package-only offline review plan is required.');
    exterior_exone_production_assert(is_array($report['source_counts']) && exterior_exone_production_hash($report['source_counts']) === exterior_exone_production_hash(['works'=>12,'news'=>5,'page'=>20,'attachment'=>38,'wpcf7_contact_form'=>3,'term'=>42]), 'Offline source scope differs.');
    exterior_exone_production_assert(is_array($report['planned_sql_rows']), 'Missing planned SQL rows.');
    exterior_exone_production_fields($report['planned_sql_rows'], ['posts','postmeta','terms','termmeta','term_taxonomy','term_relationships'], 'planned SQL rows');
    foreach ($report['planned_sql_rows'] as $count) { exterior_exone_production_assert(is_int($count) && $count >= 0, 'Invalid planned row count.'); }
    exterior_exone_production_assert($report['planned_sql_rows']['posts'] === 78 && $report['planned_sql_rows']['terms'] === 42 && $report['planned_sql_rows']['term_taxonomy'] === 42 && $report['planned_sql_rows']['postmeta'] > 0 && $report['planned_sql_rows']['termmeta'] > 0, 'Offline planned row scope differs.');
    exterior_exone_production_recent($report['created_at']);
}

function exterior_exone_production_authorization(string $offlinePlanSha): array {
    $root = EXTERIOR_EXONE_PRODUCTION_PRIVATE_ROOT;
    foreach (['apply-authorization.json','backup-evidence.json'] as $name) { exterior_exone_production_no_symlinks($root . '/' . $name); exterior_exone_production_assert(is_file($root . '/' . $name) && (fileperms($root . '/' . $name) & 0777) === 0600, 'Private apply/backup evidence is missing.'); }
    $approval = exterior_exone_production_read_json($root . '/apply-authorization.json');
    $backup = exterior_exone_production_read_json($root . '/backup-evidence.json');
    exterior_exone_production_authorization_values($approval, $backup, $offlinePlanSha, hash_file('sha256', $root . '/backup-evidence.json'));
    $file = $root . '/backup/database.sql.gz'; exterior_exone_production_no_symlinks($file);
    exterior_exone_production_assert(is_file($file) && (fileperms($file) & 0777) === 0600 && filesize($file) === $backup['bytes'] && hash_file('sha256', $file) === $backup['sha256'], 'Fixed private backup differs.');
    return $approval;
}

/** Pure verification for review; the callable factory still re-reads fixed private files. */
function exterior_exone_production_authorization_values(array $approval, array $backup, string $offlinePlanSha, string $backupSha): void {
    exterior_exone_production_digest($offlinePlanSha); exterior_exone_production_digest($backupSha);
    exterior_exone_production_fields($approval, ['schema_version','operation','approved_at','source_fingerprint','offline_plan_sha256','database_inserts_approved','media_append_approved','theme_activation_approved','production_dry_run_skipped_by_user','backup_evidence_sha256'], 'apply authorization');
    exterior_exone_production_assert($approval['schema_version'] === 1 && $approval['operation'] === 'append-renewal-content-only' && $approval['source_fingerprint'] === EXTERIOR_EXONE_PRODUCTION_FINGERPRINT && $approval['offline_plan_sha256'] === $offlinePlanSha && $approval['database_inserts_approved'] === true && $approval['media_append_approved'] === true && $approval['theme_activation_approved'] === false && $approval['production_dry_run_skipped_by_user'] === true && $approval['backup_evidence_sha256'] === $backupSha, 'Apply authorization differs from the reviewed offline plan.');
    exterior_exone_production_recent($approval['approved_at']);
    exterior_exone_production_fields($backup, ['schema_version','database','captured_at','sha256','bytes','gzip_integrity_verified','required_tables_verified','restore_available','server_web_recovery_available'], 'backup evidence');
    exterior_exone_production_assert($backup['schema_version'] === 1 && $backup['database'] === 'xs357102_w8mov' && $backup['gzip_integrity_verified'] === true && $backup['required_tables_verified'] === true && $backup['restore_available'] === true && $backup['server_web_recovery_available'] === true && is_int($backup['bytes']) && $backup['bytes'] > 0, 'Backup verification is incomplete.');
    exterior_exone_production_recent($backup['captured_at']); exterior_exone_production_digest($backup['sha256']);
}
