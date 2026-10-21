<?php
/** Read-only local r3 equivalence. Contains no production factory or credentials. */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/guard.php';
require_once dirname(__DIR__) . '/transform.php';
require_once dirname(__DIR__,2) . '/import/transform.php';
$path='/Users/exone-se/.codex/visualizations/2026/10/15/01a13e27-de2a-7213-8884-dcd614a97848/migration-private/content-20261015-r3';
$before=hash_file('sha256',$path.'/manifest.json');
$production=exterior_exone_production_validate_files($path); $lab=exterior_exone_import_validate($path);
exterior_exone_production_assert($production===$lab && $production['source_fingerprint']===EXTERIOR_EXONE_PRODUCTION_FINGERPRINT,'Readers differ');
$ids=[]; $i=90000; $files=0; $bytes=0;
foreach ($production['records'] as $r) {
    $ids[$r['source_key']]=$r['kind']==='term'?['id'=>++$i,'term_taxonomy_id'=>$i+100000]:['id'=>++$i];
    foreach ($r['files'] as $f) { $files++;$bytes+=$f['bytes']; }
}
$pc=exterior_exone_production_context($production,$ids,'https://exterior-exone.com','https://exterior-exone.com/retest',1);
$lc=exterior_exone_import_context($lab,$ids,'http://127.0.0.1:18746','http://127.0.0.1:18746/retest',1);
foreach ($production['records'] as $record) {
    $a=exterior_exone_production_transform($record,$pc);$b=exterior_exone_import_transform($record,$lc);
    unset($a['destination_sha256'],$b['destination_sha256']);
    // Deserialize string values before rewriting so serialized lengths remain correct.
    foreach ($b['meta'] as $key=>&$values) { foreach ($values as &$value) {
        $decoded=exterior_exone_import_decode($value);
        $replaced=exterior_exone_import_walk_strings($decoded,static function (string $s): string { return str_replace('http://127.0.0.1:18746','https://exterior-exone.com',$s); });
        $value=exterior_exone_import_store_value($replaced,$value);
    } unset($value); } unset($values);
    foreach ($b['data'] as &$value) { if (is_string($value)) { $value=str_replace('http://127.0.0.1:18746','https://exterior-exone.com',$value); } } unset($value);
    exterior_exone_production_assert(exterior_exone_production_hash($a)===exterior_exone_production_hash($b),'Production transformation differs from lab contract');
}
exterior_exone_production_assert(count($ids)===120 && $files===195 && $bytes===35343166 && hash_file('sha256',$path.'/manifest.json')===$before,'r3 scope/source changed');
echo "Production/local reader and transform equivalence passed: 120 records, 195 files; DB/network/writes 0.\n";
