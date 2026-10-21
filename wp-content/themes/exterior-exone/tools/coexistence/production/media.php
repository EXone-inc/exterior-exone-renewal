<?php
/** 専用uploads領域への排他的追加。既存ファイルを上書き・削除しない。 */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/transform.php';
require_once __DIR__ . '/guard.php';

final class ExteriorExoneProductionMedia {
    private $package;
    private $root;
    private $context;
    private $plan;
    private $created = [];
    private $directories = [];
    private $journal;
    private $journalInode;
    private $sourceSignatures = [];

    public function __construct(string $package, string $uploadsRoot, array $context, array $plan, string $journal) {
        foreach ([$package, $uploadsRoot, dirname($journal)] as $path) {
            exterior_exone_production_no_symlinks($path);
            exterior_exone_production_assert(is_dir($path) && realpath($path) === $path, 'Media paths must be existing real directories.');
        }
        exterior_exone_production_assert($package === EXTERIOR_EXONE_PRODUCTION_PRIVATE_ROOT . '/package' && $uploadsRoot === EXTERIOR_EXONE_PRODUCTION_UPLOADS_ROOT && exterior_exone_production_under(dirname($journal), EXTERIOR_EXONE_PRODUCTION_PRIVATE_ROOT . '/runs'), 'Media roots differ from the fixed production contract.');
        exterior_exone_production_assert($context['manifest']['source_fingerprint'] === EXTERIOR_EXONE_PRODUCTION_FINGERPRINT, 'Wrong media package.');
        exterior_exone_production_assert($plan['insert_count'] === 0 || $plan['reuse_count'] === 0, 'Partial namespace reuse cannot be repaired automatically.');
        $namespace = $uploadsRoot . '/exone-renewal/' . EXTERIOR_EXONE_PRODUCTION_FINGERPRINT;
        exterior_exone_production_no_symlinks($namespace);
        exterior_exone_production_assert($plan['insert_count'] === 0 ? is_dir($namespace) : !file_exists($namespace), 'Existing media namespace conflicts with this complete plan.');
        $this->package = $package;
        $this->root = $uploadsRoot;
        $this->context = $context;
        $this->plan = $plan;
        $this->journal = $journal;
        exterior_exone_production_write($journal, json_encode(['schema_version' => 1, 'event' => 'begin-media', 'source_fingerprint' => $context['manifest']['source_fingerprint']], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n");
        $this->journalInode = fileinode($journal);
    }

    private function event(array $event): void {
        clearstatcache(true, $this->journal);
        exterior_exone_production_assert(!is_link($this->journal) && is_file($this->journal) && fileinode($this->journal) === $this->journalInode && (fileperms($this->journal) & 0777) === 0600, 'Private media journal changed.');
        $bytes = json_encode($event, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
        exterior_exone_production_assert(file_put_contents($this->journal, $bytes, FILE_APPEND | LOCK_EX) === strlen($bytes), 'Could not persist media journal.');
    }

    private function destination(array $record, array $file): string {
        $relative = $this->context['files'][$record['source_key']][$file['source_relative_path']];
        exterior_exone_production_relative($relative);
        exterior_exone_production_assert(strpos($relative, 'exone-renewal/' . $this->context['manifest']['source_fingerprint'] . '/') === 0, 'Media target is outside the package namespace.');
        $path = $this->root . '/' . $relative;
        exterior_exone_production_no_symlinks($path);
        return $path;
    }

    private function matches(string $path, array $file): bool {
        clearstatcache(true, $path);
        return !is_link($path) && is_file($path) && filesize($path) === $file['bytes'] && hash_file('sha256', $path) === $file['sha256'];
    }

    public function preflight(): void {
        foreach ([$this->root . '/exone-renewal', $this->root . '/exone-renewal/' . $this->context['manifest']['source_fingerprint']] as $directory) {
            exterior_exone_production_no_symlinks($directory); clearstatcache(true, $directory);
            if (file_exists($directory)) { exterior_exone_production_assert(is_dir($directory) && (fileperms($directory) & 0777) === 0755, 'Existing public media ancestor has incompatible permissions; no chmod is allowed.'); }
        }
        foreach ($this->context['manifest']['records'] as $record) {
            foreach ($record['files'] as $file) {
                $source = $this->package . '/' . $file['package_path'];
                exterior_exone_production_no_symlinks($source);
                exterior_exone_production_assert($this->matches($source, $file), 'Source package changed during preflight.');
                $signature = array_intersect_key(lstat($source), array_flip(['dev','ino','mode','size','mtime','ctime']));
                if (isset($this->sourceSignatures[$source])) { exterior_exone_production_assert($this->sourceSignatures[$source] === $signature, 'Source media changed between checks.'); }
                $this->sourceSignatures[$source] = $signature;
                $destination = $this->destination($record, $file);
                $reuse = $this->plan['actions'][$record['source_key']]['action'] === 'reuse';
                exterior_exone_production_assert($reuse ? $this->matches($destination, $file) : !file_exists($destination), $reuse ? 'Reused media is missing or changed.' : 'New media path already exists; no overwrite or blind retry permitted.');
            }
        }
    }

    private function directory(string $path): void {
        if (is_dir($path)) { exterior_exone_production_no_symlinks($path); return; }
        exterior_exone_production_assert(exterior_exone_production_under($path, $this->root . '/exone-renewal'), 'Refusing to create a directory outside the media namespace.');
        $this->directory(dirname($path));
        $this->event(['event' => 'create-directory', 'path' => $path]);
        exterior_exone_production_assert(mkdir($path, 0755), 'Could not create dedicated media directory.');
        $this->directories[$path] = fileinode($path);
        exterior_exone_production_assert(chmod($path, 0755), 'Could not set new public image directory mode.');
        $this->event(['event' => 'created-directory', 'path' => $path, 'inode' => fileinode($path)]);
    }

    public function copy(callable $checkpoint): void {
        $this->preflight();
        foreach ($this->context['manifest']['records'] as $record) {
            foreach ($record['files'] as $file) {
                $destination = $this->destination($record, $file);
                if ($this->plan['actions'][$record['source_key']]['action'] === 'reuse') { continue; }
                $source = $this->package . '/' . $file['package_path'];
                exterior_exone_production_no_symlinks($source);
                exterior_exone_production_assert($this->matches($source, $file), 'Package media changed before copying.');
                $before = lstat($source);
                $this->directory(dirname($destination));
                $this->event(['event' => 'create-file', 'path' => $destination, 'bytes' => $file['bytes'], 'sha256' => $file['sha256']]);
                $input = fopen($source, 'rb');
                $output = fopen($destination, 'xb');
                if ($output === false) { if ($input !== false) { fclose($input); } throw new ExteriorExoneProductionException('Exclusive media creation failed.'); }
                $this->created[$destination] = ['inode' => fstat($output)['ino'], 'bytes' => $file['bytes'], 'sha256' => $file['sha256'], 'written_bytes' => 0, 'written_sha256' => hash('sha256', '')];
                exterior_exone_production_assert(chmod($destination, 0644), 'Could not set new public image file mode.');
                $this->event(['event' => 'created-file', 'path' => $destination, 'inode' => $this->created[$destination]['inode'], 'bytes' => $file['bytes'], 'sha256' => $file['sha256']]);
                $writtenHash = hash_init('sha256'); $writtenBytes = 0;
                try {
                    exterior_exone_production_assert($input !== false, 'Package media open failed.');
                    $hash = hash_init('sha256');
                    $count = 0;
                    while (!feof($input)) {
                        $chunk = fread($input, 1024 * 1024);
                        exterior_exone_production_assert($chunk !== false, 'Package media read failed.');
                        if ($chunk === '') { continue; }
                        hash_update($hash, $chunk);
                        $count += strlen($chunk);
                        $offset = 0;
                        while ($offset < strlen($chunk)) {
                            $written = fwrite($output, substr($chunk, $offset));
                            exterior_exone_production_assert($written !== false && $written > 0, 'Media write failed.');
                            hash_update($writtenHash, substr($chunk, $offset, $written)); $writtenBytes += $written;
                            $offset += $written;
                        }
                    }
                    exterior_exone_production_assert(fflush($output) && $count === $file['bytes'] && hash_final($hash) === $file['sha256'], 'Copied media bytes differ from the manifest.');
                } finally {
                    $this->created[$destination]['written_bytes'] = $writtenBytes;
                    $this->created[$destination]['written_sha256'] = hash_final($writtenHash);
                    if ($input !== false) { fclose($input); }
                    fclose($output);
                }
                clearstatcache(true, $source);
                $after = lstat($source);
                foreach (['dev', 'ino', 'mode', 'size', 'mtime', 'ctime'] as $part) { exterior_exone_production_assert($before[$part] === $after[$part], 'Package media changed during copying.'); }
                exterior_exone_production_assert($this->matches($destination, $file), 'Destination media verification failed.');
                $checkpoint('media-file');
            }
        }
        $this->verify();
    }

    public function verify(): void {
        $allowed = [];
        foreach ($this->context['manifest']['records'] as $record) {
            foreach ($record['files'] as $file) {
                $destination = $this->destination($record, $file);
                exterior_exone_production_assert($this->matches($destination, $file), 'Final media verification failed.');
                exterior_exone_production_assert((fileperms($destination) & 0777) === 0644, 'New image permissions differ from the public media contract.');
                $allowed[$destination] = true;
            }
        }
        foreach ($this->sourceSignatures as $source => $signature) {
            clearstatcache(true, $source); exterior_exone_production_no_symlinks($source);
            $current = lstat($source);
            foreach (['dev','ino','mode','size','mtime','ctime'] as $field) { exterior_exone_production_assert($current[$field] === $signature[$field], 'Source changed before final media verification.'); }
        }
        $namespace = $this->root . '/exone-renewal/' . $this->context['manifest']['source_fingerprint'];
        if ($allowed) {
            foreach ([$this->root . '/exone-renewal', $namespace] as $directory) { clearstatcache(true, $directory); exterior_exone_production_no_symlinks($directory); exterior_exone_production_assert(is_dir($directory) && (fileperms($directory) & 0777) === 0755, 'Public namespace ancestor permissions changed.'); }
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($namespace, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
            foreach ($iterator as $entry) {
                exterior_exone_production_assert(!$entry->isLink(), 'Symlink in the dedicated media namespace.');
                if ($entry->isDir()) { exterior_exone_production_assert(($entry->getPerms() & 0777) === 0755, 'New media directory permissions differ.'); }
                if (!$entry->isDir()) { exterior_exone_production_assert($entry->isFile() && isset($allowed[$entry->getPathname()]), 'Unlisted file in the dedicated media namespace.'); }
            }
        }
    }

    public function rollback(): void {
        foreach (array_reverse(array_keys($this->created)) as $path) {
            exterior_exone_production_no_symlinks($path);
            clearstatcache(true, $path);
            $owned = $this->created[$path];
            exterior_exone_production_assert(is_file($path) && fileinode($path) === $owned['inode'] && isset($owned['written_bytes'],$owned['written_sha256']) && filesize($path) === $owned['written_bytes'] && hash_file('sha256', $path) === $owned['written_sha256'], 'Created media ownership or content changed; retain it for inspection.');
            exterior_exone_production_assert(unlink($path), 'Could not remove the current run media file.');
            unset($this->created[$path]);
        }
        foreach (array_reverse(array_keys($this->directories)) as $path) {
            exterior_exone_production_no_symlinks($path);
            exterior_exone_production_assert(is_dir($path) && fileinode($path) === $this->directories[$path], 'Created directory ownership changed.');
            exterior_exone_production_assert(rmdir($path), 'Created directory is not empty; cleanup stopped.');
        }
        $this->directories = [];
        $this->event(['event' => 'rollback-created-media-complete']);
    }
}
