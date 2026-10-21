<?php
/** 専用uploads領域への排他的追加。既存ファイルを上書き・削除しない。 */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/transform.php';

final class ExteriorExoneImportMedia {
    private $package;
    private $root;
    private $context;
    private $plan;
    private $created = [];
    private $directories = [];
    private $journal;
    private $journalInode;

    public function __construct(string $package, string $uploadsRoot, array $context, array $plan, string $journal) {
        foreach ([$package, $uploadsRoot, dirname($journal)] as $path) {
            exterior_exone_import_no_symlinks($path);
            exterior_exone_import_assert(is_dir($path) && realpath($path) === $path && exterior_exone_import_under($path, EXTERIOR_EXONE_IMPORT_PRIVATE_ROOT), 'Media paths must be real directories inside the fixed private root.');
        }
        exterior_exone_import_assert($package !== $uploadsRoot && !exterior_exone_import_under($uploadsRoot, $package), 'Media destination overlaps the source package.');
        $this->package = $package;
        $this->root = $uploadsRoot;
        $this->context = $context;
        $this->plan = $plan;
        $this->journal = $journal;
        exterior_exone_import_write($journal, json_encode(['schema_version' => 1, 'event' => 'begin-media', 'source_fingerprint' => $context['manifest']['source_fingerprint']], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n");
        $this->journalInode = fileinode($journal);
    }

    private function event(array $event): void {
        clearstatcache(true, $this->journal);
        exterior_exone_import_assert(!is_link($this->journal) && is_file($this->journal) && fileinode($this->journal) === $this->journalInode && (fileperms($this->journal) & 0777) === 0600, 'Private media journal changed.');
        $bytes = json_encode($event, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
        exterior_exone_import_assert(file_put_contents($this->journal, $bytes, FILE_APPEND | LOCK_EX) === strlen($bytes), 'Could not persist media journal.');
    }

    private function destination(array $record, array $file): string {
        $relative = $this->context['files'][$record['source_key']][$file['source_relative_path']];
        exterior_exone_import_relative($relative);
        exterior_exone_import_assert(strpos($relative, 'exone-renewal/' . $this->context['manifest']['source_fingerprint'] . '/') === 0, 'Media target is outside the package namespace.');
        $path = $this->root . '/' . $relative;
        exterior_exone_import_no_symlinks($path);
        return $path;
    }

    private function matches(string $path, array $file): bool {
        clearstatcache(true, $path);
        return !is_link($path) && is_file($path) && filesize($path) === $file['bytes'] && hash_file('sha256', $path) === $file['sha256'];
    }

    public function preflight(): void {
        foreach ($this->context['manifest']['records'] as $record) {
            foreach ($record['files'] as $file) {
                $destination = $this->destination($record, $file);
                $reuse = $this->plan['actions'][$record['source_key']]['action'] === 'reuse';
                exterior_exone_import_assert($reuse ? $this->matches($destination, $file) : !file_exists($destination), $reuse ? 'Reused media is missing or changed.' : 'New media path already exists; no overwrite or blind retry permitted.');
            }
        }
    }

    private function directory(string $path): void {
        if (is_dir($path)) { exterior_exone_import_no_symlinks($path); return; }
        exterior_exone_import_assert(exterior_exone_import_under($path, $this->root . '/exone-renewal'), 'Refusing to create a directory outside the media namespace.');
        $this->directory(dirname($path));
        $this->event(['event' => 'create-directory', 'path' => $path]);
        exterior_exone_import_assert(mkdir($path, 0700), 'Could not create dedicated media directory.');
        $this->directories[] = $path;
        $this->event(['event' => 'created-directory', 'path' => $path, 'inode' => fileinode($path)]);
    }

    public function copy(callable $checkpoint): void {
        $this->preflight();
        foreach ($this->context['manifest']['records'] as $record) {
            foreach ($record['files'] as $file) {
                $destination = $this->destination($record, $file);
                if ($this->plan['actions'][$record['source_key']]['action'] === 'reuse') { continue; }
                $source = $this->package . '/' . $file['package_path'];
                exterior_exone_import_no_symlinks($source);
                exterior_exone_import_assert($this->matches($source, $file), 'Package media changed before copying.');
                $before = lstat($source);
                $this->directory(dirname($destination));
                $this->event(['event' => 'create-file', 'path' => $destination, 'bytes' => $file['bytes'], 'sha256' => $file['sha256']]);
                $input = fopen($source, 'rb');
                $output = fopen($destination, 'xb');
                if ($output === false) { if ($input !== false) { fclose($input); } throw new ExteriorExoneImportException('Exclusive media creation failed.'); }
                chmod($destination, 0600);
                $this->created[$destination] = ['inode' => fileinode($destination), 'bytes' => $file['bytes'], 'sha256' => $file['sha256']];
                $this->event(['event' => 'created-file', 'path' => $destination, 'inode' => $this->created[$destination]['inode'], 'bytes' => $file['bytes'], 'sha256' => $file['sha256']]);
                try {
                    exterior_exone_import_assert($input !== false, 'Package media open failed.');
                    $hash = hash_init('sha256');
                    $count = 0;
                    while (!feof($input)) {
                        $chunk = fread($input, 1024 * 1024);
                        exterior_exone_import_assert($chunk !== false, 'Package media read failed.');
                        if ($chunk === '') { continue; }
                        hash_update($hash, $chunk);
                        $count += strlen($chunk);
                        $offset = 0;
                        while ($offset < strlen($chunk)) {
                            $written = fwrite($output, substr($chunk, $offset));
                            exterior_exone_import_assert($written !== false && $written > 0, 'Media write failed.');
                            $offset += $written;
                        }
                    }
                    exterior_exone_import_assert(fflush($output) && $count === $file['bytes'] && hash_final($hash) === $file['sha256'], 'Copied media bytes differ from the manifest.');
                } finally {
                    if ($input !== false) { fclose($input); }
                    fclose($output);
                }
                clearstatcache(true, $source);
                $after = lstat($source);
                foreach (['dev', 'ino', 'mode', 'size', 'mtime', 'ctime'] as $part) { exterior_exone_import_assert($before[$part] === $after[$part], 'Package media changed during copying.'); }
                exterior_exone_import_assert($this->matches($destination, $file), 'Destination media verification failed.');
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
                exterior_exone_import_assert($this->matches($destination, $file), 'Final media verification failed.');
                $allowed[$destination] = true;
            }
        }
        $namespace = $this->root . '/exone-renewal/' . $this->context['manifest']['source_fingerprint'];
        if ($allowed) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($namespace, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
            foreach ($iterator as $entry) {
                exterior_exone_import_assert(!$entry->isLink(), 'Symlink in the dedicated media namespace.');
                if (!$entry->isDir()) { exterior_exone_import_assert($entry->isFile() && isset($allowed[$entry->getPathname()]), 'Unlisted file in the dedicated media namespace.'); }
            }
        }
    }

    public function rollback(): void {
        foreach (array_reverse(array_keys($this->created)) as $path) {
            exterior_exone_import_no_symlinks($path);
            clearstatcache(true, $path);
            exterior_exone_import_assert(is_file($path) && fileinode($path) === $this->created[$path]['inode'], 'Created media changed ownership; cleanup stopped for inspection.');
            exterior_exone_import_assert(unlink($path), 'Could not remove the current run media file.');
            unset($this->created[$path]);
        }
        foreach (array_reverse($this->directories) as $path) {
            exterior_exone_import_no_symlinks($path);
            exterior_exone_import_assert(rmdir($path), 'Created directory is not empty; cleanup stopped.');
        }
        $this->directories = [];
        $this->event(['event' => 'rollback-created-media-complete']);
    }
}
