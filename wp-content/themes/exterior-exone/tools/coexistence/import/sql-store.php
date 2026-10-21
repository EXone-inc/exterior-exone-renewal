<?php
/** WordPressを起動しない隔離DB専用store。接続はguard.phpの固定先だけ。 */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/plan.php';

final class ExteriorExoneImportSqlStore {
    private $db;
    private $prefix;
    private $createdPosts = [];
    private $createdTerms = [];
    private $createdTaxonomies = [];
    private $inTransaction = false;
    private $writes = 0;
    private const POST_FIELDS = ['post_author', 'post_date', 'post_date_gmt', 'post_content', 'post_title', 'post_excerpt', 'post_status', 'comment_status', 'ping_status', 'post_password', 'post_name', 'to_ping', 'pinged', 'post_modified', 'post_modified_gmt', 'post_content_filtered', 'post_parent', 'guid', 'menu_order', 'post_type', 'post_mime_type', 'comment_count'];
    private const TABLES = ['posts', 'postmeta', 'terms', 'termmeta', 'term_taxonomy', 'term_relationships', 'options', 'users', 'usermeta', 'comments', 'commentmeta'];

    private function __construct($db, string $prefix) {
        $this->db = $db;
        $this->prefix = $prefix;
    }

    public static function open(): self {
        require_once __DIR__ . '/guard.php';
        $environment = exterior_exone_import_environment();
        $db = exterior_exone_import_connect($environment);
        return new self($db, $environment['prefix']);
    }

    private function table(string $name): string {
        exterior_exone_import_assert(in_array($name, self::TABLES, true), 'Table is outside the import contract.');
        return '`' . $this->prefix . $name . '`';
    }

    private function statement(string $sql, array $values = []) {
        $statement = $this->db->prepare($sql);
        exterior_exone_import_assert($statement !== false, 'Isolated SQL preparation failed.');
        if ($values) {
            $types = '';
            $arguments = [];
            foreach ($values as &$value) {
                exterior_exone_import_assert(is_int($value) || is_string($value), 'Invalid bound SQL value.');
                $types .= is_int($value) ? 'i' : 's';
                $arguments[] =& $value;
            }
            unset($value);
            $statement->bind_param($types, ...$arguments);
        }
        exterior_exone_import_assert($statement->execute(), 'Isolated SQL statement failed.');
        return $statement;
    }

    private function select(string $sql, array $values = []): array {
        exterior_exone_import_assert(strpos($sql, 'SELECT ') === 0, 'Read query must be SELECT.');
        $statement = $this->statement($sql, $values);
        $result = $statement->get_result();
        exterior_exone_import_assert($result !== false, 'mysqlnd result support is required.');
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        $statement->close();
        return $rows;
    }

    private function insert(string $table, array $data): int {
        exterior_exone_import_assert($this->inTransaction && in_array($table, ['posts', 'postmeta', 'terms', 'termmeta', 'term_taxonomy', 'term_relationships'], true), 'Insert outside the allowed transaction.');
        foreach (array_keys($data) as $field) { exterior_exone_import_assert(preg_match('/\A[a-z_]+\z/', $field) === 1, 'Invalid column name.'); }
        $sql = 'INSERT INTO ' . $this->table($table) . ' (`' . implode('`,`', array_keys($data)) . '`) VALUES (' . implode(',', array_fill(0, count($data), '?')) . ')';
        $statement = $this->statement($sql, array_values($data));
        $id = (int) $this->db->insert_id;
        $statement->close();
        $this->writes++;
        return $id;
    }

    private function updateCreated(string $table, string $idField, int $id, array $data): void {
        $allowed = $table === 'posts' ? $this->createdPosts : ($table === 'terms' ? $this->createdTerms : ($table === 'term_taxonomy' ? $this->createdTaxonomies : []));
        exterior_exone_import_assert($this->inTransaction && isset($allowed[$id]), 'Refusing to update a pre-existing record.');
        foreach (array_keys($data) as $field) { exterior_exone_import_assert(preg_match('/\A[a-z_]+\z/', $field) === 1, 'Invalid update column.'); }
        $sets = array_map(static function (string $field): string { return '`' . $field . '`=?'; }, array_keys($data));
        $statement = $this->statement('UPDATE ' . $this->table($table) . ' SET ' . implode(',', $sets) . ' WHERE `' . $idField . '`=?', array_merge(array_values($data), [$id]));
        $statement->close();
        $this->writes++;
    }

    public function begin(): void {
        exterior_exone_import_assert(!$this->inTransaction, 'Transaction already started.');
        $engines = $this->select('SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE()');
        $found = [];
        foreach ($engines as $table) { $found[$table['TABLE_NAME']] = $table['ENGINE']; }
        foreach (self::TABLES as $table) { exterior_exone_import_assert(($found[$this->prefix . $table] ?? null) === 'InnoDB', 'Every protected core table must use InnoDB.'); }
        $lock = $this->select('SELECT GET_LOCK(?, 0) AS acquired', ['exone-isolated-import:' . $this->prefix]);
        exterior_exone_import_assert((int) $lock[0]['acquired'] === 1, 'Another isolated import is running.');
        exterior_exone_import_assert($this->db->begin_transaction(), 'Could not start isolated transaction.');
        $this->inTransaction = true;
    }

    public function commit(): void {
        exterior_exone_import_assert($this->inTransaction && $this->db->commit(), 'Isolated transaction commit failed.');
        $this->inTransaction = false;
    }

    public function rollback(): void {
        if ($this->inTransaction) {
            $success = $this->db->rollback();
            $this->inTransaction = false;
            exterior_exone_import_assert($success, 'Database rollback was not confirmed.');
        }
    }

    public function __destruct() {
        try { $this->rollback(); } catch (Throwable $error) {}
        if ($this->db) { $this->db->close(); }
    }

    public function writes(): int { return $this->writes; }

    private function metadata(string $table, string $owner, int $id): array {
        $rows = $this->select('SELECT meta_key,meta_value FROM ' . $this->table($table) . ' WHERE `' . $owner . '`=? ORDER BY meta_id', [$id]);
        $meta = [];
        foreach ($rows as $row) { $meta[$row['meta_key']][] = $row['meta_value']; }
        return $meta;
    }

    private function identity(array $meta, string $name, bool $optional = false): string {
        if ($optional && !isset($meta[$name])) { return ''; }
        exterior_exone_import_assert(isset($meta[$name]) && count($meta[$name]) === 1, 'Missing or duplicate renewal identity metadata.');
        return $meta[$name][0];
    }

    public function inventory(): array {
        $records = [];
        $posts = $this->select('SELECT ID,post_type,post_status FROM ' . $this->table('posts') . " WHERE post_type IN ('exr_page','exr_works','exr_news','exr_form') OR post_status='exr_stored' OR ID IN (SELECT post_id FROM " . $this->table('postmeta') . " WHERE meta_key IN ('_exr_dataset','_exr_source_key','_exr_form_role')) ORDER BY ID");
        foreach ($posts as $post) {
            $id = (int) $post['ID'];
            $meta = $this->metadata('postmeta', 'post_id', $id);
            $type = $post['post_type'];
            $kind = $type === 'exr_form' ? 'wpcf7_contact_form' : (strpos($type, 'exr_') === 0 ? substr($type, 4) : $type);
            exterior_exone_import_assert($kind !== 'wpcf7_contact_form' || $type === 'exr_form', 'New forms must use their dedicated storage type.');
            exterior_exone_import_assert(in_array($kind, EXTERIOR_EXONE_IMPORT_TYPES, true) && $post['post_status'] === exterior_exone_import_storage_status($kind), 'Unexpected record in the renewal namespace.');
            $records[] = ['kind' => $kind, 'id' => $id, 'term_taxonomy_id' => null, 'source_key' => $this->identity($meta, '_exr_source_key'), 'source_hash' => $this->identity($meta, '_exr_source_hash'), 'source_fingerprint' => $this->identity($meta, '_exr_source_fingerprint'), 'dataset' => $this->identity($meta, '_exr_dataset'), 'import_state' => $this->identity($meta, '_exr_import_state'), 'form_role' => $this->identity($meta, '_exr_form_role', true)];
        }
        $terms = $this->select('SELECT term_id,term_taxonomy_id,taxonomy FROM ' . $this->table('term_taxonomy') . " WHERE taxonomy IN ('exr_works_category','exr_works_package','exr_works_tag') OR term_id IN (SELECT term_id FROM " . $this->table('termmeta') . " WHERE meta_key IN ('_exr_dataset','_exr_source_key')) ORDER BY term_taxonomy_id");
        foreach ($terms as $term) {
            exterior_exone_import_assert(in_array($term['taxonomy'], ['exr_works_category', 'exr_works_package', 'exr_works_tag'], true), 'Unexpected destination taxonomy.');
            $id = (int) $term['term_id'];
            $meta = $this->metadata('termmeta', 'term_id', $id);
            $records[] = ['kind' => 'term', 'id' => $id, 'term_taxonomy_id' => (int) $term['term_taxonomy_id'], 'source_key' => $this->identity($meta, '_exr_source_key'), 'source_hash' => $this->identity($meta, '_exr_source_hash'), 'source_fingerprint' => $this->identity($meta, '_exr_source_fingerprint'), 'dataset' => $this->identity($meta, '_exr_dataset'), 'import_state' => $this->identity($meta, '_exr_import_state'), 'form_role' => ''];
        }
        return ['schema_version' => 1, 'complete' => true, 'records' => $records];
    }

    public function allocate(array $record, int $author): array {
        if ($record['kind'] === 'term') {
            $taxonomy = 'exr_' . $record['taxonomy'];
            $conflict = $this->select('SELECT t.term_id FROM ' . $this->table('terms') . ' t JOIN ' . $this->table('term_taxonomy') . ' tt ON tt.term_id=t.term_id WHERE tt.taxonomy=? AND t.slug=?', [$taxonomy, $record['data']['slug']]);
            exterior_exone_import_assert($conflict === [], 'Dedicated taxonomy slug already exists without a reusable identity.');
            $id = $this->insert('terms', ['name' => $record['data']['name'], 'slug' => $record['data']['slug'], 'term_group' => 0]);
            $this->createdTerms[$id] = true;
            $taxonomyId = $this->insert('term_taxonomy', ['term_id' => $id, 'taxonomy' => $taxonomy, 'description' => '', 'parent' => 0, 'count' => 0]);
            $this->createdTaxonomies[$taxonomyId] = true;
            return ['id' => $id, 'term_taxonomy_id' => $taxonomyId];
        }
        $data = $record['data'];
        $data['post_author'] = $author;
        $data['post_type'] = exterior_exone_import_storage_type($record['kind']);
        $data['post_status'] = exterior_exone_import_storage_status($record['kind']);
        $data['post_content'] = '';
        $data['post_excerpt'] = '';
        $data['post_parent'] = 0;
        $data['comment_status'] = 'closed';
        $data['ping_status'] = 'closed';
        $data['comment_count'] = 0;
        foreach (['post_password', 'to_ping', 'pinged', 'post_content_filtered', 'guid'] as $name) { $data[$name] = ''; }
        exterior_exone_import_fields($data, self::POST_FIELDS, 'insert post');
        $id = $this->insert('posts', $data);
        $this->createdPosts[$id] = true;
        return ['id' => $id];
    }

    public function saveCreated(array $row, array $mapping): void {
        $id = $mapping['id'];
        $data = $row['data'];
        if ($row['kind'] === 'term') {
            $this->updateCreated('terms', 'term_id', $id, ['name' => $data['name'], 'slug' => $data['slug'], 'term_group' => $data['term_group']]);
            $this->updateCreated('term_taxonomy', 'term_taxonomy_id', $mapping['term_taxonomy_id'], ['description' => $data['description'], 'parent' => $data['parent'], 'count' => $data['count']]);
            foreach ($row['meta'] as $key => $values) { foreach ($values as $value) { $this->insert('termmeta', ['term_id' => $id, 'meta_key' => $key, 'meta_value' => $value]); } }
        } else {
            exterior_exone_import_fields($data, self::POST_FIELDS, 'saved post');
            $this->updateCreated('posts', 'ID', $id, $data);
            foreach ($row['meta'] as $key => $values) { foreach ($values as $value) { $this->insert('postmeta', ['post_id' => $id, 'meta_key' => $key, 'meta_value' => $value]); } }
            foreach ($row['relationships'] as $relationship) { $this->insert('term_relationships', ['object_id' => $id] + $relationship); }
        }
    }

    public function verify(array $expected, array $mapping): void {
        $id = $mapping['id'];
        if ($expected['kind'] === 'term') {
            $rows = $this->select('SELECT t.name,t.slug,t.term_group,tt.description,tt.taxonomy,tt.parent,tt.count FROM ' . $this->table('terms') . ' t JOIN ' . $this->table('term_taxonomy') . ' tt ON tt.term_id=t.term_id WHERE t.term_id=? AND tt.term_taxonomy_id=?', [$id, $mapping['term_taxonomy_id']]);
            exterior_exone_import_assert(count($rows) === 1, 'Destination term/taxonomy mapping does not exist.');
            $data = $rows[0];
            foreach (['term_group', 'parent', 'count'] as $name) { $data[$name] = (int) $data[$name]; }
            $meta = $this->metadata('termmeta', 'term_id', $id);
            $relationships = [];
        } else {
            $rows = $this->select('SELECT `' . implode('`,`', self::POST_FIELDS) . '` FROM ' . $this->table('posts') . ' WHERE ID=?', [$id]);
            exterior_exone_import_assert(count($rows) === 1, 'Destination post mapping does not exist.');
            $data = $rows[0];
            foreach (['post_author', 'post_parent', 'menu_order', 'comment_count'] as $name) { $data[$name] = (int) $data[$name]; }
            $meta = $this->metadata('postmeta', 'post_id', $id);
            $relationships = $this->select('SELECT term_taxonomy_id,term_order FROM ' . $this->table('term_relationships') . ' WHERE object_id=? ORDER BY term_taxonomy_id', [$id]);
            foreach ($relationships as &$relationship) { $relationship['term_taxonomy_id'] = (int) $relationship['term_taxonomy_id']; $relationship['term_order'] = (int) $relationship['term_order']; }
            unset($relationship);
        }
        $actual = ['source_key' => $expected['source_key'], 'kind' => $expected['kind'], 'data' => $data, 'meta' => $meta, 'relationships' => $relationships];
        $expectedRelationships = $expected['relationships'];
        usort($expectedRelationships, static function (array $a, array $b): int { return $a['term_taxonomy_id'] <=> $b['term_taxonomy_id']; });
        $comparison = $expected;
        unset($comparison['destination_sha256']);
        $comparison['relationships'] = $expectedRelationships;
        exterior_exone_import_assert(exterior_exone_import_hash($comparison) === exterior_exone_import_hash($actual), 'Destination content differs; refusing reuse or completion.');
    }

    public function protectedSnapshot(?array $bounds = null): array {
        $idColumns = ['posts' => 'ID', 'postmeta' => 'meta_id', 'terms' => 'term_id', 'termmeta' => 'meta_id', 'term_taxonomy' => 'term_taxonomy_id', 'options' => 'option_id', 'users' => 'ID', 'usermeta' => 'umeta_id', 'comments' => 'comment_ID', 'commentmeta' => 'meta_id'];
        if ($bounds === null) {
            $bounds = [];
            foreach ($idColumns as $table => $column) {
                $rows = $this->select('SELECT COALESCE(MAX(`' . $column . '`),0) AS max_id FROM ' . $this->table($table));
                $bounds[$table] = (int) $rows[0]['max_id'];
            }
        }
        exterior_exone_import_fields($bounds, array_keys($idColumns), 'protected bounds');
        $result = [];
        foreach ($idColumns + ['term_relationships' => 'object_id'] as $table => $column) {
            $limit = $table === 'term_relationships' ? $bounds['posts'] : $bounds[$table];
            $all = in_array($table, ['options', 'users', 'usermeta', 'comments', 'commentmeta'], true);
            $sql = 'SELECT * FROM ' . $this->table($table) . ($all ? '' : ' WHERE `' . $column . '`<=' . (int) $limit) . ' ORDER BY `' . $column . '`' . ($table === 'term_relationships' ? ',term_taxonomy_id' : '');
            $rows = $this->db->query($sql, MYSQLI_USE_RESULT);
            exterior_exone_import_assert($rows !== false, 'Protected snapshot read failed.');
            $hash = hash_init('sha256');
            $count = 0;
            while ($row = $rows->fetch_assoc()) { hash_update($hash, exterior_exone_import_json($row)); $count++; }
            $rows->free();
            $result[$table] = ['sha256' => hash_final($hash), 'rows' => $count];
        }
        return ['bounds' => $bounds, 'tables' => $result];
    }
}
