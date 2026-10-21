<?php
/** 隔離storeが取得した識別情報と、取り込むパッケージを照合する。 */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/transform.php';

/** A dry run cannot assign the real future IDs needed to verify a partial reuse graph. */
function exterior_exone_import_dry_run_plan(array $plan): void {
    exterior_exone_import_assert($plan['insert_count'] === 0 || $plan['reuse_count'] === 0, 'A mixed insert/reuse dry-run cannot verify every existing reference; refusing an incomplete validation.');
}

function exterior_exone_import_plan(array $manifest, array $inventory): array {
    exterior_exone_import_fields($inventory, ['schema_version', 'complete', 'records'], 'destination inventory');
    exterior_exone_import_assert($inventory['schema_version'] === 1 && $inventory['complete'] === true, 'Incomplete destination inventory.');
    exterior_exone_import_list($inventory['records'], 'destination records');
    $byKey = [];
    $roles = [];
    foreach ($inventory['records'] as $record) {
        exterior_exone_import_fields($record, ['source_key', 'source_hash', 'source_fingerprint', 'dataset', 'import_state', 'kind', 'id', 'term_taxonomy_id', 'form_role'], 'destination identity');
        exterior_exone_import_assert(is_string($record['source_key']) && $record['source_key'] !== '' && !isset($byKey[$record['source_key']]), 'Missing or duplicate destination source key.');
        exterior_exone_import_assert(is_int($record['id']) && $record['id'] > 0 && is_string($record['kind']) && is_string($record['form_role']), 'Invalid destination identity values.');
        exterior_exone_import_assert($record['dataset'] === EXTERIOR_EXONE_IMPORT_DATASET && $record['import_state'] === 'complete', 'Foreign dataset or incomplete destination record.');
        exterior_exone_import_digest($record['source_hash']);
        exterior_exone_import_digest($record['source_fingerprint']);
        if ($record['kind'] === 'term') {
            exterior_exone_import_assert(is_int($record['term_taxonomy_id']) && $record['term_taxonomy_id'] > 0, 'Missing destination term taxonomy ID.');
        } else {
            exterior_exone_import_assert($record['term_taxonomy_id'] === null, 'Unexpected taxonomy identity on a post.');
        }
        if ($record['form_role'] !== '') {
            exterior_exone_import_assert($record['kind'] === 'wpcf7_contact_form' && in_array($record['form_role'], ['personal', 'corporate', 'entry'], true) && !isset($roles[$record['form_role']]), 'Conflicting destination form role.');
            $roles[$record['form_role']] = $record['source_key'];
        }
        $byKey[$record['source_key']] = $record;
    }
    $actions = [];
    $known = [];
    foreach ($manifest['records'] as $source) {
        $key = $source['source_key'];
        $known[$key] = true;
        $role = $source['kind'] === 'wpcf7_contact_form' ? array_search($key, $manifest['analysis']['form_roles'], true) : '';
        exterior_exone_import_assert($role !== false, 'Missing source form role.');
        exterior_exone_import_assert($role === '' || !isset($roles[$role]) || $roles[$role] === $key, 'Another source key owns the required form role.');
        if (isset($byKey[$key])) {
            $target = $byKey[$key];
            exterior_exone_import_assert($target['kind'] === $source['kind'] && $target['source_hash'] === $source['content_sha256'] && $target['source_fingerprint'] === $manifest['source_fingerprint'] && $target['form_role'] === $role, 'Source identity has changed content, type, package or role; no overwrite permitted.');
            $map = ['id' => $target['id']];
            if ($source['kind'] === 'term') { $map['term_taxonomy_id'] = $target['term_taxonomy_id']; }
            $actions[$key] = ['action' => 'reuse', 'mapping' => $map];
        } else {
            $actions[$key] = ['action' => 'insert', 'mapping' => null];
        }
    }
    // 不明な同じデータ群を消したり、現在のpackageへ取り込んだ扱いにしない。
    exterior_exone_import_assert(!array_diff(array_keys($byKey), array_keys($known)), 'Unexpected existing renewal identities.');
    return ['schema_version' => 1, 'source_fingerprint' => $manifest['source_fingerprint'], 'actions' => $actions, 'insert_count' => count(array_filter($actions, static function (array $a): bool { return $a['action'] === 'insert'; })), 'reuse_count' => count(array_filter($actions, static function (array $a): bool { return $a['action'] === 'reuse'; })), 'production_ready' => false];
}
