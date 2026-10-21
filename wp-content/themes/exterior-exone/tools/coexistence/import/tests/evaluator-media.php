<?php
/** Independent file boundary tests; synthetic private files only. No DB or HTTP. */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { exit; }
require_once dirname(__DIR__) . '/media.php';
umask(0077);
function review_media_fixture(string $root): array {
    $fingerprint = str_repeat('a', 64);
    $records = []; $files = []; $actions = [];
    foreach ([1, 2] as $id) {
        $key = 'fixture:' . $id; $relative = $id . '.jpg'; $bytes = 'private-synthetic-image-' . $id;
        $file = ['source_relative_path' => $relative, 'package_path' => 'media/' . $relative, 'bytes' => strlen($bytes), 'sha256' => hash('sha256', $bytes)];
        $records[] = ['source_key' => $key, 'files' => [$file]];
        $files[$key][$relative] = 'exone-renewal/' . $fingerprint . '/' . $id . '/' . $relative;
        $actions[$key] = ['action' => 'insert'];
    }
    return [['manifest' => ['source_fingerprint' => $fingerprint, 'records' => $records], 'files' => $files], ['actions' => $actions]];
}
function review_media_make(string $root, string $journal, array $context, array $plan): ExteriorExoneImportMedia {
    return new ExteriorExoneImportMedia($root . '/package', $root . '/uploads', $context, $plan, $root . '/' . $journal);
}
if (($argv[1] ?? '') === '--abrupt') {
    $root = $argv[2];
    exterior_exone_import_assert(strpos($root, EXTERIOR_EXONE_IMPORT_PRIVATE_ROOT . '/media-independent-') === 0 && realpath($root) === $root, 'Wrong synthetic root');
    [$context, $plan] = review_media_fixture($root);
    $media = review_media_make($root, 'abrupt.jsonl', $context, $plan);
    $media->copy(static function (): void { exit(75); });
    exit(76);
}
$checks = 0;
function media_check(bool $condition, string $label): void {
    global $checks;
    if (!$condition) { throw new RuntimeException('Independent media fixture failed: ' . $label); }
    $checks++;
}
function media_reject(callable $callback, string $label): void {
    try { $callback(); } catch (ExteriorExoneImportException $error) { media_check(true, $label); return; }
    throw new RuntimeException('Expected media rejection: ' . $label);
}
function media_root(): string {
    $root = EXTERIOR_EXONE_IMPORT_PRIVATE_ROOT . '/media-independent-' . bin2hex(random_bytes(6));
    exterior_exone_import_private_directory($root);
    mkdir($root . '/package', 0700); mkdir($root . '/package/media', 0700); mkdir($root . '/uploads', 0700);
    foreach ([1, 2] as $id) { exterior_exone_import_write($root . '/package/media/' . $id . '.jpg', 'private-synthetic-image-' . $id); }
    exterior_exone_import_write($root . '/uploads/legacy.jpg', 'existing-file-must-stay');
    return $root;
}
$root = media_root(); [$context, $plan] = review_media_fixture($root);
$first = $root . '/uploads/' . $context['files']['fixture:1']['1.jpg'];
$second = $root . '/uploads/' . $context['files']['fixture:2']['2.jpg'];
$legacyHash = hash_file('sha256', $root . '/uploads/legacy.jpg');
foreach (['../escape.jpg', '/absolute.jpg', 'exone-renewal/' . str_repeat('b', 64) . '/image.jpg', 'exone-renewal/' . str_repeat('a', 64) . '/../../escape.jpg'] as $i => $path) {
    $bad = $context; $bad['files']['fixture:1']['1.jpg'] = $path;
    $media = review_media_make($root, 'invalid-' . $i . '.jsonl', $bad, $plan);
    media_reject(function () use ($media) { $media->preflight(); }, 'Reject out-of-namespace destination ' . $i);
}
symlink($root . '/package', $root . '/uploads/exone-renewal');
$media = review_media_make($root, 'symlink.jsonl', $context, $plan);
media_reject(function () use ($media) { $media->preflight(); }, 'Reject linked destination ancestor');
unlink($root . '/uploads/exone-renewal');
file_put_contents($root . '/package/media/1.jpg', 'changed-source');
$media = review_media_make($root, 'source-change.jsonl', $context, $plan);
media_reject(function () use ($media) { $media->copy(static function (): void {}); }, 'Reject source changed after manifest');
media_check(!file_exists($first), 'Changed source creates no destination');
file_put_contents($root . '/package/media/1.jpg', 'private-synthetic-image-1');
$media = review_media_make($root, 'partial.jsonl', $context, $plan);
media_reject(function () use ($media, $root) {
    $media->copy(static function () use ($root): void { file_put_contents($root . '/package/media/2.jpg', 'changed-before-second-copy'); });
}, 'Reject mid-run source alteration');
media_check(is_file($first) && !file_exists($second), 'Mid-run failure leaves only owned first file until rollback');
$media->rollback();
media_check(!file_exists($first) && hash_file('sha256', $root . '/uploads/legacy.jpg') === $legacyHash, 'Rollback removes owned files while preserving legacy file');
file_put_contents($root . '/package/media/2.jpg', 'private-synthetic-image-2');
$media = review_media_make($root, 'success.jsonl', $context, $plan); $media->copy(static function (): void {});
media_check(is_file($first) && is_file($second), 'Successful retry copies both files');
$reuse = $plan; foreach ($reuse['actions'] as &$action) { $action['action'] = 'reuse'; } unset($action);
$reused = review_media_make($root, 'reuse.jsonl', $context, $reuse);
$reused->copy(static function (): void { throw new RuntimeException('Reuse unexpectedly copied'); });
$reused->rollback();
media_check(is_file($first) && is_file($second), 'Reuse rollback does not delete prior successful files');
file_put_contents($second, 'tampered-destination');
$tampered = review_media_make($root, 'tampered.jsonl', $context, $reuse);
media_reject(function () use ($tampered) { $tampered->preflight(); }, 'Reuse rejects destination bytes changed');
file_put_contents($second, 'private-synthetic-image-2');
exterior_exone_import_write(dirname($first) . '/unexpected.jpg', 'unlisted');
media_reject(function () use ($reused) { $reused->verify(); }, 'Namespace rejects undeclared extra file');
unlink(dirname($first) . '/unexpected.jpg');
rename($first, $root . '/preserved-owned-file.jpg');
exterior_exone_import_write($first, 'replacement-must-not-be-deleted');
media_reject(function () use ($media) { $media->rollback(); }, 'Rollback refuses replaced inode');
media_check(file_get_contents($first) === 'replacement-must-not-be-deleted', 'Replacement file survives refused rollback');
media_check(hash_file('sha256', $root . '/uploads/legacy.jpg') === $legacyHash, 'All failure paths leave legacy file untouched');
$abruptRoot = media_root(); [$abruptContext, $abruptPlan] = review_media_fixture($abruptRoot);
$process = proc_open([PHP_BINARY, '-n', __FILE__, '--abrupt', $abruptRoot], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
media_check(is_resource($process), 'Synthetic child process started');
fclose($pipes[0]); $stdout = stream_get_contents($pipes[1]); $stderr = stream_get_contents($pipes[2]); fclose($pipes[1]); fclose($pipes[2]);
media_check(proc_close($process) === 75 && $stdout === '' && $stderr === '', 'Abrupt child exits without cleanup or content disclosure');
$afterAbrupt = review_media_make($abruptRoot, 'after-abrupt.jsonl', $abruptContext, $abruptPlan);
media_reject(function () use ($afterAbrupt) { $afterAbrupt->preflight(); }, 'New process refuses orphan collision after abrupt exit');
media_check(is_file($abruptRoot . '/uploads/' . $abruptContext['files']['fixture:1']['1.jpg']), 'Abrupt-exit file preserved for manual inspection, not silently removed');
media_check(hash_file('sha256', $abruptRoot . '/uploads/legacy.jpg') === $legacyHash, 'Abrupt process preserves old media');
exterior_exone_import_write($root . '/independent-result.json', exterior_exone_import_json(['checks' => $checks, 'php' => PHP_VERSION, 'database_connections' => 0, 'abrupt_exit_recovery' => 'fails-closed; manual recovery not implemented or validated', 'production_ready' => false]));
echo 'PASS ' . $checks . " independent media assertions; abrupt termination safely refuses retry; recovery remains unverified.\n";
