<?php
/** In-memory SQL double: never connects. Production has no test factory. */
declare(strict_types=1);
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
    public $triggers = [], $foreign = [], $privileges = ['SELECT','INSERT','UPDATE','TRIGGER'];
    public $tables = [], $sql = [], $inventoryPosts = [], $insert_id = 0, $engine = 'InnoDB', $lock = 1, $begins = 0, $commits = 0, $rollbacks = 0, $closed = 0, $failExecute = false, $commitWorks = true;
    public const IDS = ['posts' => 'ID', 'postmeta' => 'meta_id', 'terms' => 'term_id', 'termmeta' => 'meta_id', 'term_taxonomy' => 'term_taxonomy_id', 'options' => 'option_id', 'users' => 'ID', 'usermeta' => 'umeta_id', 'comments' => 'comment_ID', 'commentmeta' => 'meta_id'];
    private $snapshot;
    public function __construct() {
        foreach (self::IDS as $table => $id) { $this->tables[$table] = [[$id => 1, 'fixture' => 'old-' . $table]]; }
        $this->tables['term_relationships'] = [['object_id' => 1, 'term_taxonomy_id' => 1, 'term_order' => 0]];
    }
    public function prepare(string $sql): ReviewStatement { return new ReviewStatement($this, $sql); }
    public function query(string $sql, $mode = null) {
        if (strpos($sql, 'SET TRANSACTION ') === 0) { $this->sql[] = ['sql' => $sql, 'values' => []]; return true; }
        if (strpos($sql, 'START TRANSACTION ') === 0) { $this->sql[] = ['sql' => $sql, 'values' => []]; return $this->begin_transaction(); }
        return new ReviewResult($this->execute($sql, []));
    }
    public function begin_transaction(): bool { $this->snapshot = $this->tables; $this->begins++; return true; }
    public function commit(): bool { $this->commits++; return $this->commitWorks; }
    public function rollback(): bool { $this->tables = $this->snapshot; $this->rollbacks++; return true; }
    public function close(): void { $this->closed++; }
    public function execute(string $sql, array $values): array {
        $this->sql[] = ['sql' => $sql, 'values' => $values];
        if (strpos($sql, 'information_schema.SCHEMA_PRIVILEGES') !== false) { return array_map(function ($p) { return ['PRIVILEGE_TYPE' => $p]; }, $this->privileges); }
        if (strpos($sql, 'information_schema.TRIGGERS') !== false) { return $this->triggers; }
        if (strpos($sql, 'information_schema.KEY_COLUMN_USAGE') !== false) { return $this->foreign; }
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
function review_store(ReviewDatabase $db, bool $readOnly = false): ExteriorExoneProductionSqlStore {
    $class = new ReflectionClass(ExteriorExoneProductionSqlStore::class);
    $store = $class->newInstanceWithoutConstructor();
    foreach (['db' => $db, 'prefix' => 'wp_', 'readOnly' => $readOnly] as $name => $value) {
        $property = $class->getProperty($name); $property->setAccessible(true); $property->setValue($store, $value);
    }
    return $store;
}
