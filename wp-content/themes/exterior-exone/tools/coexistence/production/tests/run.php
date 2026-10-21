<?php
/** PHP 7.4.33; synthetic files/SQL double only. No WordPress, DB, or network. */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/guard.php';
require_once dirname(__DIR__) . '/media.php';
require_once dirname(__DIR__) . '/sql-store.php';
require_once __DIR__ . '/fixtures.php';
require_once __DIR__ . '/fake-db.php';
umask(0077);
$checks = 0;
function check(bool $value, string $label): void { global $checks; if (!$value) { throw new RuntimeException('Fixture failed: ' . $label); } $checks++; }
function reject(callable $callback, string $label): void { try { $callback(); } catch (ExteriorExoneProductionException $e) { check(true, $label); return; } throw new RuntimeException('Missing rejection: ' . $label); }
function invoke($object, string $name, array $args = []) { $method = new ReflectionMethod($object, $name); $method->setAccessible(true); return $method->invokeArgs($object, $args); }
function set_property($object, string $name, $value): void { $property = new ReflectionProperty($object, $name); $property->setAccessible(true); $property->setValue($object, $value); }
check(PHP_VERSION === '7.4.33', 'Actual PHP 7.4.33');
exterior_exone_production_check_contract(exterior_exone_production_contract()); check(true, 'Exact environment contract');
foreach (['root','wp_root','database','prefix','home','siteurl','db_host','db_socket','uploads_root','uploads_url','php','environment_id'] as $field) {
    $bad = exterior_exone_production_contract(); $bad[$field] .= '-wrong';
    reject(function () use ($bad) { exterior_exone_production_check_contract($bad); }, 'Wrong contract field ' . $field);
}
$bad = exterior_exone_production_contract(); $bad['extra'] = true;
reject(function () use ($bad) { exterior_exone_production_check_contract($bad); }, 'Unknown environment key');
reject(function () { exterior_exone_production_environment(); }, 'Local runner cannot open production connection');
reject(function () { exterior_exone_production_options(['socket' => '/tmp/test'], []); }, 'CLI cannot override host or socket');
$config = <<<'CONFIG'
<?php
define('DB_NAME', 'xs357102_w8mov');
define('DB_USER', 'fixture_user');
define('DB_PASSWORD', 'fixture\'quoted\\secret');
define('DB_HOST', 'localhost');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');
$table_prefix = 'wp_';
require_once ABSPATH . 'wp-settings.php';
CONFIG;
$parsed = exterior_exone_production_parse_config($config);
check($parsed['DB_PASSWORD'] === "fixture'quoted\\secret", 'Literal decode without executing config'); unset($parsed);
check(exterior_exone_production_literal('"a\\n\\x41\\101\\$b"') === "a\nAA\$b", 'Double quoted literal escapes');
foreach ([
    str_replace("'localhost'", "'127.0.0.1'", $config),
    str_replace("'xs357102_w8mov'", "'local'", $config),
    str_replace("'wp_'", "'other_'", $config),
    str_replace("'fixture_user'", "getenv('DB_USER')", $config),
    str_replace("'localhost'", "'local' . 'host'", $config),
    $config . "\ndefine('DB_HOST','localhost');",
    $config . "\n\$table_prefix = 'wp_';",
    $config . "\ndefine('UPLOADS','uploads');",
] as $bad) { reject(function () use ($bad) { exterior_exone_production_parse_config($bad); }, 'Unsafe/computed config'); }
exterior_exone_production_recent(gmdate('c')); check(true, 'Fresh evidence accepted');
foreach ([gmdate('c', time() + 1000), gmdate('c', time() - 86401), '2026-02-30T00:00:00+00:00', '2026-10-16T01:00:00Z', 123] as $bad) {
    reject(function () use ($bad) { exterior_exone_production_recent($bad); }, 'Stale/invalid evidence');
}
$backup=['schema_version'=>1,'database'=>'xs357102_w8mov','captured_at'=>gmdate('c'),'sha256'=>str_repeat('a',64),'bytes'=>100,'gzip_integrity_verified'=>true,'required_tables_verified'=>true,'restore_available'=>true,'server_web_recovery_available'=>true];
$approval=['schema_version'=>1,'operation'=>'append-renewal-content-only','approved_at'=>gmdate('c'),'source_fingerprint'=>EXTERIOR_EXONE_PRODUCTION_FINGERPRINT,'offline_plan_sha256'=>str_repeat('b',64),'database_inserts_approved'=>true,'media_append_approved'=>true,'theme_activation_approved'=>false,'production_dry_run_skipped_by_user'=>true,'backup_evidence_sha256'=>str_repeat('c',64)];
exterior_exone_production_authorization_values($approval,$backup,str_repeat('b',64),str_repeat('c',64)); check(true,'Exact approval/backup values');
foreach (['schema_version'=>'1','operation'=>'activate','source_fingerprint'=>str_repeat('0',64),'offline_plan_sha256'=>str_repeat('0',64),'database_inserts_approved'=>1,'media_append_approved'=>false,'theme_activation_approved'=>true,'production_dry_run_skipped_by_user'=>false,'backup_evidence_sha256'=>str_repeat('0',64),'approved_at'=>gmdate('c',time()-86401),'extra'=>true] as $field=>$value) {
    $bad=$approval; $bad[$field]=$value;
    reject(function () use ($bad,$backup) { exterior_exone_production_authorization_values($bad,$backup,str_repeat('b',64),str_repeat('c',64)); },'Approval gate strict value/type');
}
foreach (['schema_version'=>'1','database'=>'local','captured_at'=>gmdate('c',time()+1000),'sha256'=>'bad','bytes'=>'100','gzip_integrity_verified'=>1,'required_tables_verified'=>false,'restore_available'=>false,'server_web_recovery_available'=>false,'extra'=>true] as $field=>$value) {
    $bad=$backup; $bad[$field]=$value;
    reject(function () use ($approval,$bad) { exterior_exone_production_authorization_values($approval,$bad,str_repeat('b',64),str_repeat('c',64)); },'Backup gate strict value/type');
}
$reviewed=['schema_version'=>1,'document_type'=>'production-offline-review-plan','created_at'=>gmdate('c'),'source_fingerprint'=>EXTERIOR_EXONE_PRODUCTION_FINGERPRINT,'source_manifest_sha256'=>str_repeat('d',64),'source_counts'=>['works'=>12,'news'=>5,'page'=>20,'attachment'=>38,'wpcf7_contact_form'=>3,'term'=>42],'source_record_count'=>120,'source_media_files'=>195,'source_media_bytes'=>35343166,'planned_sql_rows'=>['posts'=>78,'postmeta'=>806,'terms'=>42,'termmeta'=>252,'term_taxonomy'=>42,'term_relationships'=>51],'production_dry_run_performed'=>false,'simulated_new_ids'=>true,'theme_activation_permitted'=>false,'production_ready'=>false];
exterior_exone_production_reviewed_offline_plan($reviewed,str_repeat('d',64)); check(true,'Exact offline package-only review plan');
foreach (['schema_version'=>'1','document_type'=>'production-dry-run-complete','source_manifest_sha256'=>str_repeat('0',64),'source_record_count'=>119,'source_media_files'=>194,'source_media_bytes'=>'35343166','source_counts'=>[],'production_dry_run_performed'=>true,'simulated_new_ids'=>false,'theme_activation_permitted'=>true,'production_ready'=>true,'planned_sql_rows'=>[],'extra'=>true] as $field=>$value) {
    $bad=$reviewed;$bad[$field]=$value;reject(function () use ($bad) { exterior_exone_production_reviewed_offline_plan($bad,str_repeat('d',64)); },'Nonconforming offline plan cannot authorize apply');
}
foreach (['posts'=>79,'postmeta'=>-1,'terms'=>'42','term_taxonomy'=>0,'termmeta'=>0,'term_relationships'=>1.5] as $field=>$value) {
    $bad=$reviewed;$bad['planned_sql_rows'][$field]=$value;reject(function () use ($bad) { exterior_exone_production_reviewed_offline_plan($bad,str_repeat('d',64)); },'Invalid planned SQL scope');
}
reject(function () { exterior_exone_production_decode('O:8:"stdClass":0:{}'); }, 'Serialized object blocked');
reject(function () { exterior_exone_production_decode('a:0:{}trailing'); }, 'Serialized trailing data blocked');
reject(function () { exterior_exone_production_unique_json_keys('{"a":1,"a":2}'); }, 'Duplicate JSON keys blocked');
$private = '/Users/exone-se/.codex/visualizations/2026/10/15/01a13e27-de2a-7213-8884-dcd614a97848/migration-private';
$root = $private . '/production-fixtures-' . bin2hex(random_bytes(6)); mkdir($root, 0700);
$manifest = fixture_manifest(); write_fixture($manifest, $root . '/package');
$checked = exterior_exone_production_validate_files($root . '/package'); check(count($checked['records']) === 8, 'Strict package reader');
$ids = []; $next = 10000;
foreach ($manifest['records'] as $record) { $ids[$record['source_key']] = $record['kind'] === 'term' ? ['id' => ++$next, 'term_taxonomy_id' => $next + 10000] : ['id' => ++$next]; }
$context = exterior_exone_production_context($manifest, $ids, 'https://exterior-exone.com', 'https://exterior-exone.com/retest', 1);
reject(function () use ($manifest, $ids) { exterior_exone_production_context($manifest, $ids, 'http://127.0.0.1', 'http://127.0.0.1/retest', 1); }, 'Production transformer rejects lab URLs');
$rows = []; foreach ($manifest['records'] as $record) { $rows[$record['source_key']] = exterior_exone_production_transform($record, $context); }
$key = static function (string $kind, int $id): string { return exterior_exone_production_key('post', $id, $kind); };
$page = $rows[$key('page', 2)]; $mediaRow = $rows[$key('attachment', 4)]; $form = $rows[$key('wpcf7_contact_form', 6)];
check($page['data']['post_parent'] === $ids[$key('page',1)]['id'] && $page['data']['post_status'] === 'exr_stored', 'Private new page with remapped hierarchy');
check($mediaRow['data']['post_status'] === 'private' && $mediaRow['data']['post_parent'] === 0, 'Core-private attachment detaches excluded column parent');
check($form['data']['post_type'] === 'exr_form' && $form['meta']['_exr_form_role'] === ['personal'], 'New CF7 type and source role isolation');
check(strpos($page['data']['post_content'], 'https://exterior-exone.com/retest/wp-content/uploads/exone-renewal/') !== false && strpos($page['data']['post_content'], 'exterior-exone-renewal.local') === false, 'Mapped references use fixed production URL');
foreach ($rows as $row) { if ($row['kind'] !== 'term') { check($row['data']['comment_status'] === 'closed' && $row['data']['ping_status'] === 'closed' && $row['data']['comment_count'] === 0, 'No imported comments'); } }
$plan = exterior_exone_production_plan($manifest, ['schema_version' => 1,'complete' => true,'records' => []]); check($plan['insert_count'] === 8, 'Empty namespace plans inserts');
$planned=exterior_exone_production_planned_rows($manifest,$context);check($planned['posts']===6&&$planned['terms']===2&&$planned['term_taxonomy']===2&&$planned['term_relationships']===1,'Planned SQL rows rebuilt from verified records');
reject(function () { exterior_exone_production_dry_run_plan(['insert_count'=>1,'reuse_count'=>1]); }, 'Partial namespace never silently repairs');

// SQL double exercises private methods only through Reflection; production has no bypass factory.
$db = new ReviewDatabase(); $store = review_store($db, true); $store->begin();
reject(function () use ($store) { invoke($store, 'insert', ['posts', ['post_title'=>'blocked']]); }, 'Read-only session rejects writes');
check($store->writes() === 0, 'Read-only write count zero'); $store->rollback();
$db = new ReviewDatabase(); $store = review_store($db); $store->begin();
foreach (['options','users','usermeta','comments','commentmeta'] as $table) { reject(function () use ($store,$table) { invoke($store,'insert',[$table,['fixture'=>'blocked']]); }, 'Shared table insertion rejected'); }
foreach (['posts'=>'ID','terms'=>'term_id','term_taxonomy'=>'term_taxonomy_id'] as $table=>$field) { reject(function () use ($store,$table,$field) { invoke($store,'updateCreated',[$table,$field,1,['fixture'=>'blocked']]); }, 'Existing row update rejected'); }
$map = $store->allocate($manifest['records'][0]['kind'] === 'term' ? $manifest['records'][1] : $manifest['records'][0], 1);
check($map['id'] > 1 && count($store->ownership()['posts']) === 1, 'Allocated rows have explicit ownership');
$store->rollback(); unset($store);
foreach (['engine'=>'MyISAM','lock'=>0,'triggers'=>[['EVENT_OBJECT_TABLE'=>'wp_posts']],'foreign'=>[['TABLE_NAME'=>'other','REFERENCED_TABLE_NAME'=>'wp_posts']],'privileges'=>['SELECT','INSERT','UPDATE']] as $field=>$value) {
    $db = new ReviewDatabase(); $db->$field=$value; $store=review_store($db);
    reject(function () use ($store) { $store->begin(); }, 'Unsafe production precondition rejected'); check($db->begins === 0, 'Precondition fails before transaction'); unset($store);
}
$db = new ReviewDatabase(); $observer=review_store($db,true); $baseline=$observer->freshProtectedSnapshot();
foreach ($db->tables as $table=>$initial) {
    $db->tables[$table][0]['fixture']='concurrent-change';
    check($observer->freshProtectedSnapshot() !== $baseline, 'Fresh observer detects existing row update'); $db->tables[$table]=$initial;
    $new = $initial[0]; if ($table === 'term_relationships') { $new['object_id']=999; $identity='999:1'; } else { $id=ReviewDatabase::IDS[$table]; $new[$id]=999; $identity='999'; }
    $db->tables[$table][]=$new; check($observer->freshProtectedSnapshot() !== $baseline, 'Concurrent inserted row never mistaken for owned');
    if (in_array($table,['posts','postmeta','terms','termmeta','term_taxonomy','term_relationships'],true)) { check($observer->freshProtectedSnapshot([$table=>[$identity=>true]]) === $baseline, 'Only explicit committed ownership may be excluded'); }
    else { reject(function () use ($observer,$table,$identity) { $observer->freshProtectedSnapshot([$table=>[$identity=>true]]); },'Shared tables cannot hide changes under owned exclusions'); }
    $db->tables[$table]=$initial;
    reject(function () use ($observer,$table,$identity) { $observer->freshProtectedSnapshot([$table=>[$identity=>true]]); }, 'Missing expected owned row rejected');
}
check($db->begins > 30 && $db->begins === $db->rollbacks, 'Each observation uses a new read-only snapshot');
check(count(array_filter($db->sql, function ($entry) { return strpos($entry['sql'],'GET_LOCK')!==false; }))===0, 'Read-only observer acquires no advisory write lock');

// Constructor stays fixed to production; synthetic media tests cannot relax runtime guards.
mkdir($root . '/uploads',0700); file_put_contents($root . '/uploads/legacy.jpg','legacy');
function media_double(string $root, array $context, array $plan, string $journal): ExteriorExoneProductionMedia {
    $class=new ReflectionClass(ExteriorExoneProductionMedia::class); $media=$class->newInstanceWithoutConstructor();
    exterior_exone_production_write($journal,"{\"fixture\":true}\n");
    foreach (['package'=>$root.'/package','root'=>$root.'/uploads','context'=>$context,'plan'=>$plan,'journal'=>$journal,'journalInode'=>fileinode($journal)] as $name=>$value) { set_property($media,$name,$value); } return $media;
}
reject(function () use ($root,$context,$plan) { new ExteriorExoneProductionMedia($root.'/package',$root.'/uploads',$context,$plan,$root.'/not-created.jsonl'); }, 'Media constructor refuses local runtime roots');
$media=media_double($root,$context,$plan,$root.'/media.jsonl');
mkdir($root.'/uploads/exone-renewal',0700);
reject(function () use ($media) { $media->preflight(); },'Existing nonpublic ancestor rejected before media copying');
check((fileperms($root.'/uploads/exone-renewal')&0777)===0700,'Existing ancestor permission never changed');
rmdir($root.'/uploads/exone-renewal');
$media->preflight(); $media->copy(static function (): void {}); $media->verify();
$namespace=$root.'/uploads/exone-renewal/'.$context['manifest']['source_fingerprint'];
chmod($namespace,0700); reject(function () use ($media) { $media->verify(); },'Namespace root permissions checked'); chmod($namespace,0755);
chmod($root.'/uploads/exone-renewal',0700); reject(function () use ($media) { $media->verify(); },'Namespace parent permissions checked'); chmod($root.'/uploads/exone-renewal',0755);
$first=$root.'/uploads/'.reset($context['files'][$key('attachment',4)]);
check((fileperms($first)&0777)===0644 && (fileperms(dirname($first))&0777)===0755, 'New public images readable with 0644/0755');
chmod($first,0600); reject(function () use ($media) { $media->verify(); }, 'Permission change cannot pass public image verification'); chmod($first,0644);
$raw=file_get_contents($first); file_put_contents($first,'same-inode-tamper');
reject(function () use ($media) { $media->rollback(); }, 'Rollback preserves same-inode modified content');
check(is_file($first) && file_get_contents($root.'/uploads/legacy.jpg')==='legacy', 'Changed and legacy files retained');
file_put_contents($first,$raw); $media->rollback(); check(!file_exists($first) && is_file($root.'/uploads/legacy.jpg'),'Only own unchanged files removed');
exterior_exone_production_write($root.'/result.json',exterior_exone_production_json(['checks'=>$checks,'php'=>PHP_VERSION,'database_connections'=>0,'network_requests'=>0]));
echo 'Production pure/SQL-double/media fixtures passed: '.$checks.PHP_EOL;
