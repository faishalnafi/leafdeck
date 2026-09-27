<?php

namespace App\Services;

use CodeIgniter\HTTP\Files\UploadedFile;
use RuntimeException;

class FileService
{
    protected string $uploadPath;

    public function __construct()
    {
        $this->uploadPath = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'decks' . DIRECTORY_SEPARATOR;
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }

    /**
     * Menyimpan file HTML yang diunggah
     *
     * @param UploadedFile|string $file UploadedFile instance atau string konten HTML mentah
     * @param string $nanoId Identifier unik deck
     * @return string Relatif path file (contoh: uploads/decks/{nanoId}.html)
     */
    public function saveHtml($file, string $nanoId): string
    {
        $fileName = $nanoId . '.html';
        $destination = $this->uploadPath . $fileName;

        if ($file instanceof UploadedFile) {
            if (!$file->isValid()) {
                throw new RuntimeException('File upload tidak valid: ' . $file->getErrorString());
            }

            $ext = strtolower($file->getClientExtension());
            if (!in_array($ext, ['html', 'htm'])) {
                throw new RuntimeException('Hanya file berekstensi .html atau .htm yang diizinkan');
            }

            $file->move($this->uploadPath, $fileName, true);
        } elseif (is_string($file)) {
            file_put_contents($destination, $file);
        } else {
            throw new RuntimeException('Tipe konten file tidak didukung');
        }

        return 'uploads/decks/' . $fileName;
    }

    /**
     * Membaca isi file HTML berdasarkan relative path
     */
    public function readHtml(string $filePath): ?string
    {
        $fullPath = $this->getAbsolutePath($filePath);
        if (!file_exists($fullPath)) {
            return null;
        }

        return file_get_contents($fullPath);
    }

    /**
     * Menghapus file fisik dari storage
     */
    public function delete(string $filePath): bool
    {
        $fullPath = $this->getAbsolutePath($filePath);
        if (file_exists($fullPath) && is_file($fullPath)) {
            return unlink($fullPath);
        }

        return false;
    }

    /**
     * Mendapatkan absolute path dari relative path
     */
    public function getAbsolutePath(string $filePath): string
    {
        return FCPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($filePath, '/\\'));
    }

    /**
     * Menghitung total ukuran file HTML di storage decks (dalam bytes)
     */
    public function getTotalStorageBytes(): int
    {
        $bytes = 0;
        if (is_dir($this->uploadPath)) {
            $files = glob($this->uploadPath . '*');
            if ($files) {
                foreach ($files as $file) {
                    if (is_file($file)) {
                        $bytes += filesize($file);
                    }
                }
            }
        }

        return $bytes;
    }

    /**
     * Format bytes menjadi representasi ramah pengguna (KB, MB, GB)
     */
    public static function formatBytes(int $bytes, int $precision = 1): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $pow = floor(log($bytes, 1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

