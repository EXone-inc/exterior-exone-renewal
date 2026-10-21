<?php
/** Independent store boundary tests. In-memory SQL double only: never opens a DB. */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { exit; }
require_once dirname(__DIR__) . '/sql-store.php';
if (!defined('MYSQLI_ASSOC')) { define('MYSQLI_ASSOC', 1); }
if (!defined('MYSQLI_USE_RESULT')) { define('MYSQLI_USE_RESULT', 1); }

final class ReviewResult {
    private $rows;
    public function __construct(array $rows) { $this->rows = array_values($rows); }
    public function fetch_all($mode): array { return $this->rows; }
    public function fetch_assoc() { return array_shift($this->rows); }
    public function free(): void {}
}
final class ReviewStatement {
    private $db, $sql, $values = [], $rows = [];
    public function __construct(ReviewDatabase $db, string $sql) { $this->db = $db; $this->sql = $sql; }
    public function bind_param($types, &...$values): bool { $this->values = array_map(function ($value) { return $value; }, $values); return true; }
    public function execute(): bool {
        if ($this->db->failExecute) { return false; }
        $this->rows = $this->db->execute($this->sql, $this->values);
        return true;
    }
    public function get_result(): ReviewResult { return new ReviewResult($this->rows); }
    public function close(): void {}
}
final class ReviewDatabase {
    public $tables = [], $sql = [], $inventoryPosts = [], $insert_id = 0, $engine = 'InnoDB', $lock = 1, $begins = 0, $commits = 0, $rollbacks = 0, $closed = 0, $failExecute = false, $commitWorks = true;
    public const IDS = ['posts' => 'ID', 'postmeta' => 'meta_id', 'terms' => 'term_id', 'termmeta' => 'meta_id', 'term_taxonomy' => 'term_taxonomy_id', 'options' => 'option_id', 'users' => 'ID', 'usermeta' => 'umeta_id', 'comments' => 'comment_ID', 'commentmeta' => 'meta_id'];
    private $snapshot;
    public function __construct() {
        foreach (self::IDS as $table => $id) { $this->tables[$table] = [[$id => 1, 'fixture' => 'old-' . $table]]; }
        $this->tables['term_relationships'] = [['object_id' => 1, 'term_taxonomy_id' => 1, 'term_order' => 0]];
    }
    public function prepare(string $sql): ReviewStatement { return new ReviewStatement($this, $sql); }
    public function query(string $sql, $mode): ReviewResult { return new ReviewResult($this->execute($sql, [])); }
    public function begin_transaction(): bool { $this->snapshot = $this->tables; $this->begins++; return true; }
    public function commit(): bool { $this->commits++; return $this->commitWorks; }
    public function rollback(): bool { $this->tables = $this->snapshot; $this->rollbacks++; return true; }
    public function close(): void { $this->closed++; }
    public function execute(string $sql, array $values): array {
        $this->sql[] = ['sql' => $sql, 'values' => $values];
        if (strpos($sql, 'information_schema.TABLES') !== false) {
            return array_map(function ($name) { return ['TABLE_NAME' => 'wp_' . $name, 'ENGINE' => $this->engine]; }, array_keys($this->tables));
        }
        if (strpos($sql, 'SELECT GET_LOCK') === 0) { return [['acquired' => $this->lock]]; }
        if (strpos($sql, 'SELECT ID,post_type,post_status FROM ') === 0) { return $this->inventoryPosts; }
        if (strpos($sql, 'SELECT term_id,term_taxonomy_id,taxonomy FROM ') === 0) { return []; }
        if (preg_match('/^INSERT INTO `wp_([a-z_]+)` \(([^)]+)\)/', $sql, $match)) {
            $fields = array_map(function ($field) { return trim($field, '`'); }, explode(',', $match[2]));
            $row = array_combine($fields, $values);
            $id = self::IDS[$match[1]] ?? null;
            $this->insert_id = $id ? 1 + max(array_column($this->tables[$match[1]], $id)) : 0;
            if ($id) { $row[$id] = $this->insert_id; }
            $this->tables[$match[1]][] = $row;
            return [];
        }
        if (preg_match('/^UPDATE `wp_([a-z_]+)` SET (.+) WHERE `([^`]+)`=\?$/', $sql, $match)) {
            preg_match_all('/`([^`]+)`=\?/', $match[2], $fields);
            $id = array_pop($values);
            foreach ($this->tables[$match[1]] as &$row) {
                if ($row[$match[3]] === $id) { $row = array_replace($row, array_combine($fields[1], $values)); }
            }
            unset($row);
            return [];
        }
        if (preg_match('/^SELECT COALESCE\(MAX\(`([^`]+)`\),0\) AS max_id FROM `wp_([a-z_]+)`$/', $sql, $match)) {
            return [['max_id' => max(array_merge([0], array_column($this->tables[$match[2]], $match[1])))]];
        }
        if (preg_match('/^SELECT (.+) FROM `wp_([a-z_]+)`(?: WHERE (.+?))?(?: ORDER BY .+)?$/', $sql, $match)) {
            $rows = $this->tables[$match[2]];
            $where = $match[3] ?? '';
            if (preg_match('/`?([a-z_A-Z]+)`?=\?/', $where, $condition)) {
                $rows = array_values(array_filter($rows, function ($row) use ($condition, $values) { return isset($row[$condition[1]]) && (string) $row[$condition[1]] === (string) $values[0]; }));
            } elseif (preg_match('/`([^`]+)`<=(\d+)/', $where, $condition)) {
                $rows = array_values(array_filter($rows, function ($row) use ($condition) { return $row[$condition[1]] <= (int) $condition[2]; }));
            } elseif ($where !== '') { throw new RuntimeException('Unimplemented fake SQL condition'); }
            if ($match[1] !== '*') {
                $fields = array_map(function ($field) { return trim($field, '`'); }, explode(',', $match[1]));
                $rows = array_map(function ($row) use ($fields) { return array_intersect_key($row, array_flip($fields)); }, $rows);
            }
            return $rows;
        }
        throw new RuntimeException('Unimplemented fake SQL statement');
    }
}
function review_store(ReviewDatabase $db): ExteriorExoneImportSqlStore {
    $class = new ReflectionClass(ExteriorExoneImportSqlStore::class);
    $store = $class->newInstanceWithoutConstructor();
    foreach (['db' => $db, 'prefix' => 'wp_'] as $name => $value) {
        $property = $class->getProperty($name); $property->setAccessible(true); $property->setValue($store, $value);
    }
    return $store;
}
$checks = 0;
function review_check(bool $condition, string $label): void {
    global $checks;
    if (!$condition) { throw new RuntimeException('Independent fixture failed: ' . $label); }
    $checks++;
}
function review_reject(callable $callback, string $label): void {
    try { $callback(); } catch (ExteriorExoneImportException $error) { review_check(true, $label); return; }
    throw new RuntimeException('Expected importer rejection: ' . $label);
}
function review_private(ExteriorExoneImportSqlStore $store, string $name, array $args) {
    $method = new ReflectionMethod($store, $name); $method->setAccessible(true); return $method->invokeArgs($store, $args);
}
exterior_exone_import_dry_run_plan(['insert_count' => 2, 'reuse_count' => 0]);
review_check(true, 'All-new dry-run allowed');
exterior_exone_import_dry_run_plan(['insert_count' => 0, 'reuse_count' => 2]);
review_check(true, 'All-reuse dry-run can proceed to actual row verification');
review_reject(function () { exterior_exone_import_dry_run_plan(['insert_count' => 1, 'reuse_count' => 1]); }, 'Partial reuse dry-run cannot claim verified content');
$db = new ReviewDatabase(); $store = review_store($db);
review_reject(function () use ($store) { review_private($store, 'insert', ['posts', ['post_title' => 'blocked']]); }, 'No write outside transaction');
review_check($db->sql === [], 'Transaction gate fails before SQL');
$store->begin(); $before = $store->protectedSnapshot();
foreach (['options', 'users', 'usermeta', 'comments', 'commentmeta'] as $table) {
    $count = count($db->sql);
    review_reject(function () use ($store, $table) { review_private($store, 'insert', [$table, ['fixture' => 'blocked']]); }, 'Forbidden insert table ' . $table);
    review_check(count($db->sql) === $count, 'Forbidden table rejected before SQL ' . $table);
}
foreach (['posts' => 'ID', 'terms' => 'term_id', 'term_taxonomy' => 'term_taxonomy_id'] as $table => $field) {
    $count = count($db->sql);
    review_reject(function () use ($store, $table, $field) { review_private($store, 'updateCreated', [$table, $field, 1, ['fixture' => 'blocked']]); }, 'Existing row update blocked ' . $table);
    review_check(count($db->sql) === $count, 'Old row rejected before SQL ' . $table);
}
review_reject(function () use ($store) { review_private($store, 'insert', ['posts', ['post_title`) VALUES (1)--' => 'blocked']]); }, 'SQL identifier injection blocked');
foreach ($db->tables as $table => $rows) {
    $db->tables[$table][0]['fixture_probe'] = 'mutation';
    review_check($store->protectedSnapshot($before['bounds']) !== $before, 'Snapshot detects changed existing row ' . $table);
    $db->tables[$table] = $rows;
}
foreach (['options', 'users', 'usermeta', 'comments', 'commentmeta'] as $table) {
    $db->tables[$table][] = [ReviewDatabase::IDS[$table] => 999, 'fixture' => 'unexpected insertion'];
    review_check($store->protectedSnapshot($before['bounds']) !== $before, 'Snapshot detects added global row ' . $table);
    array_pop($db->tables[$table]);
}
$fields = ['post_author', 'post_date', 'post_date_gmt', 'post_content', 'post_title', 'post_excerpt', 'post_status', 'comment_status', 'ping_status', 'post_password', 'post_name', 'to_ping', 'pinged', 'post_modified', 'post_modified_gmt', 'post_content_filtered', 'post_parent', 'guid', 'menu_order', 'post_type', 'post_mime_type', 'comment_count'];
$data = array_fill_keys($fields, '');
foreach (['post_author', 'post_parent', 'menu_order', 'comment_count'] as $field) { $data[$field] = 0; }
$data['post_content'] = 'SYNTHETIC-PRIVATE-CONTENT'; $data['post_status'] = 'publish';
$map = $store->allocate(['kind' => 'wpcf7_contact_form', 'data' => $data], 1);
$allocated = $db->tables['posts'][1];
review_check($allocated['post_type'] === 'exr_form' && $allocated['post_status'] === 'exr_stored', 'CF7 stored in private dedicated type');
review_check($allocated['post_content'] === '' && $allocated['comment_status'] === 'closed' && $allocated['comment_count'] === 0, 'Incomplete row has no public body or comments');
unset($allocated['ID']);
$allocated['post_content'] = 'SYNTHETIC-PRIVATE-CONTENT';
$expected = ['source_key' => 'review:form', 'kind' => 'wpcf7_contact_form', 'data' => $allocated, 'meta' => ['_exr_import_state' => ['complete']], 'relationships' => []];
$store->saveCreated($expected, $map);
$store->verify($expected, $map); review_check(true, 'Persisted new record verifies exact fields');
$attachmentMap = $store->allocate(['kind' => 'attachment', 'data' => $data], 1);
$attachmentData = $db->tables['posts'][2]; unset($attachmentData['ID']);
review_check($attachmentData['post_type'] === 'attachment' && $attachmentData['post_status'] === 'private', 'Attachment uses core-supported private state');
$attachmentExpected = ['source_key' => 'review:attachment', 'kind' => 'attachment', 'data' => $attachmentData, 'meta' => ['_exr_import_state' => ['complete']], 'relationships' => []];
$store->saveCreated($attachmentExpected, $attachmentMap);
$store->verify($attachmentExpected, $attachmentMap); review_check(true, 'Private attachment exact content verifies for reuse');
$db->tables['posts'][2]['post_status'] = 'exr_stored';
review_reject(function () use ($store, $attachmentExpected, $attachmentMap) { $store->verify($attachmentExpected, $attachmentMap); }, 'Reuse detects unsafe attachment status changed from private');
$db->tables['posts'][2]['post_status'] = 'private';
review_check($store->protectedSnapshot($before['bounds']) === $before, 'Allowed new content leaves protected old rows byte-equivalent');
$privateBound = true;
foreach ($db->sql as $entry) { $privateBound = $privateBound && strpos($entry['sql'], 'SYNTHETIC-PRIVATE-CONTENT') === false; }
review_check($privateBound, 'Private values stay bound, outside all SQL strings');
$db->tables['posts'][1]['post_content'] = 'tampered';
review_reject(function () use ($store, $expected, $map) { $store->verify($expected, $map); }, 'Identity alone cannot hide changed reused content');
$store->rollback(); review_check($store->protectedSnapshot() === $before, 'Rollback restores snapshot in SQL double');
unset($store); review_check($db->rollbacks === 1 && $db->closed === 1, 'Completed rollback closes without duplicate rollback');
foreach (['engine' => 'MyISAM', 'lock' => 0] as $field => $value) {
    $db = new ReviewDatabase(); $db->$field = $value; $store = review_store($db);
    review_reject(function () use ($store) { $store->begin(); }, 'Reject unsafe transaction prerequisite ' . $field);
    review_check($db->begins === 0, 'Unsafe prerequisite prevents transaction ' . $field); unset($store);
}
$db = new ReviewDatabase(); $store = review_store($db); $store->begin(); unset($store);
review_check($db->rollbacks === 1 && $db->closed === 1, 'Destructor rolls back uncommitted transaction');
$db = new ReviewDatabase(); $store = review_store($db); $store->begin(); $db->commitWorks = false;
review_reject(function () use ($store) { $store->commit(); }, 'Commit failure is not success'); unset($store);
review_check($db->rollbacks === 1, 'Failed commit retains transaction for cleanup');
$db = new ReviewDatabase(); $store = review_store($db); $store->begin(); $store->commit(); unset($store);
review_check($db->commits === 1 && $db->rollbacks === 0, 'Committed transaction is not rolled back');
$db = new ReviewDatabase(); $store = review_store($db);
$db->inventoryPosts = [['ID' => 100, 'post_type' => 'wpcf7_contact_form', 'post_status' => 'exr_stored']];
review_reject(function () use ($store) { $store->inventory(); }, 'Existing native CF7 type cannot masquerade as new form');
$db->inventoryPosts[0]['post_type'] = 'exr_form'; $db->inventoryPosts[0]['post_status'] = 'publish';
review_reject(function () use ($store) { $store->inventory(); }, 'New form with public storage status rejected');
$db->inventoryPosts[0]['post_status'] = 'exr_stored';
$identity = ['_exr_source_key' => 'review:form', '_exr_source_hash' => str_repeat('a', 64), '_exr_source_fingerprint' => str_repeat('b', 64), '_exr_dataset' => EXTERIOR_EXONE_IMPORT_DATASET, '_exr_import_state' => 'complete', '_exr_form_role' => 'personal'];
foreach ($identity as $key => $value) { $db->tables['postmeta'][] = ['meta_id' => count($db->tables['postmeta']) + 1, 'post_id' => 100, 'meta_key' => $key, 'meta_value' => $value]; }
$inventory = $store->inventory();
review_check($inventory['records'][0]['kind'] === 'wpcf7_contact_form' && $inventory['records'][0]['form_role'] === 'personal', 'Dedicated form identity inventories as source kind without native storage');
$db->tables['postmeta'][] = ['meta_id' => 99, 'post_id' => 100, 'meta_key' => '_exr_source_key', 'meta_value' => 'other-source'];
review_reject(function () use ($store) { $store->inventory(); }, 'Duplicate identity metadata rejected');
array_pop($db->tables['postmeta']);
foreach ($db->tables['postmeta'] as &$meta) { if (($meta['meta_key'] ?? '') === '_exr_form_role') { $meta['meta_value'] = ''; } } unset($meta);
$db->inventoryPosts[0]['post_type'] = 'attachment'; $db->inventoryPosts[0]['post_status'] = 'private';
review_check($store->inventory()['records'][0]['kind'] === 'attachment', 'Private attachment identity accepted by inventory');
$db->inventoryPosts[0]['post_status'] = 'exr_stored';
review_reject(function () use ($store) { $store->inventory(); }, 'Legacy unsafe attachment status rejected by inventory');
unset($store);
$secretFixture = 'synthetic-private-mail-body@example.invalid';
$badArguments = [
    ['apply', '--package', $secretFixture, '--output', $secretFixture, '--confirm', 'wrong-confirmation'],
    ['apply', '--package', $secretFixture, '--output', $secretFixture, '--confirm', 'isolated-lab-only', '--db', $secretFixture],
    ['dry-run', '--package', $secretFixture, '--output', $secretFixture, '--package', 'duplicate'],
    ['dry-run', '--package', $secretFixture, '--output', $secretFixture, '--fail-at', 'before-commit'],
    ['unsupported-' . $secretFixture],
];
foreach ($badArguments as $index => $args) {
    $process = proc_open(array_merge([PHP_BINARY, '-n', dirname(__DIR__) . '/cli.php'], $args), [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    if (!is_resource($process)) { throw new RuntimeException('Cannot run CLI rejection fixture'); }
    fclose($pipes[0]); $stdout = stream_get_contents($pipes[1]); $stderr = stream_get_contents($pipes[2]); fclose($pipes[1]); fclose($pipes[2]);
    review_check(proc_close($process) === 1 && $stdout === '' && strpos($stderr, 'stopped at arguments') !== false && strpos($stderr, $secretFixture) === false, 'CLI rejects before environment or DB without echoing input ' . $index);
}
echo 'PASS ' . $checks . " independent store assertions; in-memory SQL double, no DB/bootstrap/network.\n";
