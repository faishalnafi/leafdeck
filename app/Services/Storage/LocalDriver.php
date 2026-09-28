<?php

namespace App\Services\Storage;

class LocalDriver implements StorageDriverInterface
{
    protected string $rootPath;
    protected string $urlPrefix;

    public function __construct(array $config = [])
    {
        $this->rootPath = $config['root'] ?? (FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'decks' . DIRECTORY_SEPARATOR);
        $this->urlPrefix = $config['url'] ?? 'uploads/decks';

        if (!is_dir($this->rootPath)) {
            mkdir($this->rootPath, 0755, true);
        }
    }

    public function put(string $path, string $content, string $mimeType = 'text/html'): bool
    {
        $fullPath = $this->resolvePath($path);
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return file_put_contents($fullPath, $content) !== false;
    }

    public function get(string $path): ?string
    {
        $fullPath = $this->resolvePath($path);
        if (!file_exists($fullPath) || !is_file($fullPath)) {
            return null;
        }

        $content = file_get_contents($fullPath);
        return $content !== false ? $content : null;
    }

    public function has(string $path): bool
    {
        $fullPath = $this->resolvePath($path);
        return file_exists($fullPath);
    }

    public function delete(string $path): bool
    {
        $fullPath = $this->resolvePath($path);
        if (file_exists($fullPath)) {
            if (is_file($fullPath)) {
                return unlink($fullPath);
            }
            if (is_dir($fullPath)) {
                return $this->deleteDirectoryRecursive($fullPath);
            }
        }
        return false;
    }

    public function deleteDirectory(string $prefix): bool
    {
        $fullPath = $this->resolvePath($prefix);
        if (is_dir($fullPath)) {
            return $this->deleteDirectoryRecursive($fullPath);
        }
        return false;
    }

    public function url(string $path): string
    {
        $clean = ltrim(str_replace('\\', '/', $path), '/');
        // Jika path sudah mengandung uploads/decks
        if (str_starts_with($clean, 'uploads/decks/')) {
            return base_url($clean);
        }
        return base_url(rtrim($this->urlPrefix, '/') . '/' . $clean);
    }

    public function testConnection(): array
    {
        $startTime = microtime(true);
        $isWritable = is_writable($this->rootPath);
        $latency = round((microtime(true) - $startTime) * 1000, 2);

        if ($isWritable) {
            return [
                'status'     => true,
                'latency_ms' => $latency,
                'message'    => 'Direktori penyimpanan lokal dapat diakses dan memiliki izin tulis (writable).',
            ];
        }

        return [
            'status'     => false,
            'latency_ms' => $latency,
            'message'    => 'Direktori penyimpanan lokal (' . $this->rootPath . ') tidak memiliki izin tulis.',
        ];
    }

    protected function resolvePath(string $path): string
    {
        $clean = ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);
        // Jika path diawali 'uploads\decks\'
        $relativePrefix = 'uploads' . DIRECTORY_SEPARATOR . 'decks' . DIRECTORY_SEPARATOR;
        if (str_starts_with($clean, $relativePrefix)) {
            $clean = substr($clean, strlen($relativePrefix));
        }

        return rtrim($this->rootPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $clean;
    }

    protected function deleteDirectoryRecursive(string $dir): bool
    {
        if (!is_dir($dir)) {
            return false;
        }

        $items = array_diff(scandir($dir), ['.', '..']);
        foreach ($items as $item) {
            $p = $dir . DIRECTORY_SEPARATOR . $item;
            is_dir($p) ? $this->deleteDirectoryRecursive($p) : unlink($p);
        }

        return rmdir($dir);
    }
}
