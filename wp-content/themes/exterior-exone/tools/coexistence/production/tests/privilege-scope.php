<?php
/** 固定DBの権限表記差だけを検証するSQL double。DB・ネットワーク接続なし。 */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/sql-store.php';
require_once __DIR__ . '/fake-db.php';

final class ScopeDatabase {
    public $inner, $rows, $observed = [];
    public function __construct(array $rows) { $this->inner = new ReviewDatabase(); $this->rows = $rows; }
    public function prepare(string $sql) {
        if (strpos($sql, 'information_schema.SCHEMA_PRIVILEGES') !== false) { return new ScopeStatement($this, $sql); }
        return $this->inner->prepare($sql);
    }
    public function close(): void { $this->inner->close(); }
}
final class ScopeStatement {
    private $db, $sql, $values = [], $rows = [];
    public function __construct(ScopeDatabase $db, string $sql) { $this->db = $db; $this->sql = $sql; }
    public function bind_param($types, &...$values): bool {
        if ($types !== 'ss') { throw new RuntimeException('Expected two bound schema names.'); }
        $this->values = array_values($values); return true;
    }
    public function execute(): bool {
        $grantee = "CONCAT(QUOTE(SUBSTRING_INDEX(CURRENT_USER(),'@',1)),'@',QUOTE(SUBSTRING_INDEX(CURRENT_USER(),'@',-1)))";
        $expected = 'SELECT PRIVILEGE_TYPE FROM information_schema.SCHEMA_PRIVILEGES WHERE (TABLE_SCHEMA=? OR TABLE_SCHEMA=?) AND GRANTEE=' . $grantee . ' UNION SELECT PRIVILEGE_TYPE FROM information_schema.USER_PRIVILEGES WHERE GRANTEE=' . $grantee;
        if ($this->sql !== $expected || $this->values !== ['xs357102_w8mov', 'xs357102\\_w8mov']) { throw new RuntimeException('Privilege query scope widened or was not bound.'); }
        $this->db->observed[] = ['sql' => $this->sql, 'values' => $this->values];
        foreach ($this->db->rows as $row) {
            if (in_array($row['TABLE_SCHEMA'], $this->values, true) && $row['GRANTEE'] === "'fixture_user'@'localhost'") {
                $this->rows[] = ['PRIVILEGE_TYPE' => $row['PRIVILEGE_TYPE']];
            }
        }
        return true;
    }
    public function get_result(): ReviewResult { return new ReviewResult($this->rows); }
    public function close(): void {}
}

function scope_rows(string $schema, string $grantee = "'fixture_user'@'localhost'", array $privileges = ['SELECT', 'INSERT', 'UPDATE', 'TRIGGER']): array {
    return array_map(static function (string $name) use ($schema, $grantee): array { return ['TABLE_SCHEMA' => $schema, 'GRANTEE' => $grantee, 'PRIVILEGE_TYPE' => $name]; }, $privileges);
}
function scope_test(array $rows, bool $accept): void {
    $db = new ScopeDatabase($rows);
    $reflection = new ReflectionClass(ExteriorExoneProductionSqlStore::class);
    $store = $reflection->newInstanceWithoutConstructor();
    foreach (['db' => $db, 'prefix' => 'wp_', 'readOnly' => true] as $name => $value) { $property = $reflection->getProperty($name); $property->setAccessible(true); $property->setValue($store, $value); }
    $accepted = false;
    try { $store->preflight(); $accepted = true; } catch (ExteriorExoneProductionException $error) {}
    if ($accepted !== $accept || count($db->observed) !== 1 || $db->inner->begins !== 0) { throw new RuntimeException('Privilege scope fixture failed.'); }
}
if (PHP_VERSION !== '7.4.33') { throw new RuntimeException('PHP 7.4.33 required.'); }
$checks = 0;
foreach (['xs357102_w8mov', 'xs357102\\_w8mov'] as $scope) { scope_test(scope_rows($scope), true); $checks++; }
foreach (['other_database', 'xs357102_w8mov_other', 'xs357102Xw8mov', 'xs357102%', 'xs357102\\\\_w8mov', ''] as $scope) { scope_test(scope_rows($scope), false); $checks++; }
scope_test(scope_rows('xs357102\\_w8mov', "'other_user'@'localhost'"), false); $checks++;
scope_test(scope_rows('xs357102_w8mov', "'fixture_user'@'other_host'"), false); $checks++;
scope_test(scope_rows('xs357102\\_w8mov', "'fixture_user'@'localhost'", ['SELECT', 'INSERT', 'UPDATE']), false); $checks++;
scope_test(array_merge(scope_rows('xs357102\\_w8mov', "'fixture_user'@'localhost'", ['SELECT', 'INSERT', 'UPDATE']), scope_rows('other_database', "'fixture_user'@'localhost'", ['TRIGGER'])), false); $checks++;
echo 'Production privilege scope fixtures passed: ' . $checks . PHP_EOL;
