<?php

namespace App\Services;

use CodeIgniter\HTTP\Files\UploadedFile;
use App\Services\Storage\StorageManager;
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
        $relPath  = 'uploads/decks/' . $fileName;

        $content = '';
        if ($file instanceof UploadedFile) {
            if (!$file->isValid()) {
                throw new RuntimeException('File upload tidak valid: ' . $file->getErrorString());
            }

            $ext = strtolower($file->getClientExtension());
            if (!in_array($ext, ['html', 'htm'])) {
                throw new RuntimeException('Hanya file berekstensi .html atau .htm yang diizinkan');
            }

            $content = file_get_contents($file->getTempName());
            if ($content === false) {
                throw new RuntimeException('Gagal membaca konten file unggahan.');
            }
        } elseif (is_string($file)) {
            $content = $file;
        } else {
            throw new RuntimeException('Tipe konten file tidak didukung');
        }

        // Jika object storage aktif (s3, r2, gcs, custom), langsung lempar ke remote storage tanpa simpan di lokal
        if (StorageManager::getDefaultDriver() !== 'local') {
            $uploaded = StorageManager::disk()->put($relPath, $content, 'text/html; charset=UTF-8');
            if (!$uploaded) {
                throw new RuntimeException('Gagal mengunggah file ke Object Storage.');
            }
            return $relPath;
        }

        // Driver lokal: simpan ke direktori lokal public/uploads/decks/
        $destination = $this->uploadPath . $fileName;
        file_put_contents($destination, $content);

        return $relPath;
    }

    /**
     * Menyimpan dan mengekstrak paket ZIP presentasi (Flipbook, E-Book multi-aset)
     *
     * @param UploadedFile|string $file
     * @param string $nanoId
     * @return array [file_path, title, thumbnail]
     */
    public function saveZipPackage($file, string $nanoId): array
    {
        if (!class_exists('\ZipArchive')) {
            throw new RuntimeException('Ekstensi PHP ZipArchive tidak aktif pada server.');
        }

        $isRemote = (StorageManager::getDefaultDriver() !== 'local');

        // Jika remote, gunakan direktori temporer terisolasi di writable/temp/
        $tempBase = WRITEPATH . 'temp' . DIRECTORY_SEPARATOR;
        if (!is_dir($tempBase)) {
            mkdir($tempBase, 0755, true);
        }

        $targetDir = $isRemote
            ? ($tempBase . 'extract_' . $nanoId . DIRECTORY_SEPARATOR)
            : ($this->uploadPath . $nanoId . DIRECTORY_SEPARATOR);

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $zipPath = ($file instanceof UploadedFile) ? $file->getTempName() : (string) $file;
        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new RuntimeException('Gagal membaca paket berkas ZIP presentasi.');
        }

        // Ekstrak dengan normalisasi path (mendukung berkas dari Windows & Linux)
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = $zip->getNameIndex($i);
            $normalizedName = str_replace('\\', '/', $entryName);
            // Cegah directory traversal
            if (str_contains($normalizedName, '../') || str_starts_with($normalizedName, '/')) {
                continue;
            }

            $targetFile = $targetDir . str_replace('/', DIRECTORY_SEPARATOR, $normalizedName);

            if (str_ends_with($normalizedName, '/')) {
                if (!is_dir($targetFile)) {
                    mkdir($targetFile, 0755, true);
                }
                continue;
            }

            $dir = dirname($targetFile);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $content = $zip->getFromIndex($i);
            file_put_contents($targetFile, $content);
        }
        $zip->close();

        // Cari berkas entry HTML utama
        $entrySubPath = null;
        if (file_exists($targetDir . 'index.html')) {
            $entrySubPath = 'index.html';
        } elseif (file_exists($targetDir . 'index.htm')) {
            $entrySubPath = 'index.htm';
        } else {
            // Cari di subfolder pertama jika ZIP membungkus seluruh isi dalam satu folder induk
            $htmlFiles = glob($targetDir . '*' . DIRECTORY_SEPARATOR . 'index.html');
            if (!empty($htmlFiles)) {
                $subFolder = basename(dirname($htmlFiles[0]));
                $entrySubPath = $subFolder . '/index.html';
            } else {
                $anyHtml = glob($targetDir . '*.html');
                if (!empty($anyHtml)) {
                    $entrySubPath = basename($anyHtml[0]);
                }
            }
        }

        if (!$entrySubPath) {
            $this->deleteDirectoryRecursive($targetDir);
            throw new RuntimeException('Berkas HTML utama (index.html) tidak ditemukan di dalam paket ZIP.');
        }

        $entryRelPath = 'uploads/decks/' . $nanoId . '/' . $entrySubPath;

        // Deteksi judul dari tag <title>
        $fullHtmlPath = $targetDir . str_replace('/', DIRECTORY_SEPARATOR, $entrySubPath);
        $detectedTitle = null;
        if (file_exists($fullHtmlPath)) {
            $htmlHead = file_get_contents($fullHtmlPath, false, null, 0, 8192);
            if (preg_match('/<title[^>]*>(.*?)<\/title>/si', $htmlHead, $matches)) {
                $detectedTitle = trim(html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            }
        }

        // Deteksi cover/thumbnail otomatis (kompatibel FlipBuilder, Flip PDF, iSpring, dll)
        $detectedThumb = null;
        $entryDir = dirname($fullHtmlPath);
        $thumbCandidates = [
            'files/thumb/1.jpg',
            'files/mobile/1.jpg',
            'shot.png',
            'cover.jpg',
            'cover.png',
            'thumb.jpg',
            'thumb.png',
            'assets/cover.jpg',
        ];

        foreach ($thumbCandidates as $cand) {
            $candPath = $entryDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $cand);
            if (file_exists($candPath)) {
                if ($isRemote) {
                    $detectedThumb = StorageManager::disk()->url('uploads/decks/' . $nanoId . '/' . $cand);
                } else {
                    $relCand = str_replace(rtrim(FCPATH, '/\\'), '', $candPath);
                    $detectedThumb = '/' . str_replace('\\', '/', ltrim($relCand, '/\\'));
                }
                break;
            }
        }

        // Jika object storage aktif (s3, r2, gcs, custom), unggah seluruh berkas bundle ke remote storage
        if ($isRemote) {
            $this->syncDirectoryToStorage($targetDir, 'uploads/decks/' . $nanoId);
            // Hapus total direktori temporer lokal: TIDAK MENYISAKAN BERKAS DI LOKAL!
            $this->deleteDirectoryRecursive($targetDir);
        }

        return [
            'file_path' => $entryRelPath,
            'title'     => $detectedTitle,
            'thumbnail' => $detectedThumb,
        ];
    }

    /**
     * Membaca isi file HTML berdasarkan relative path
     */
    public function readHtml(string $filePath): ?string
    {
        // Ambil dari object storage jika driver aktif bukan local
        if (StorageManager::getDefaultDriver() !== 'local') {
            $remote = StorageManager::disk()->get($filePath);
            if ($remote !== null) {
                return $remote;
            }
        }

        $fullPath = $this->getAbsolutePath($filePath);
        if (file_exists($fullPath)) {
            return file_get_contents($fullPath);
        }

        return null;
    }

    /**
     * Menghapus file fisik atau direktori bundle dari storage
     */
    public function delete(string $filePath): bool
    {
        $fullPath = $this->getAbsolutePath($filePath);
        $deleted = false;
        if (file_exists($fullPath)) {
            if (is_file($fullPath)) {
                $deleted = unlink($fullPath);
                $dir = dirname($fullPath);
                // Jika file berada di subdirektori uploads/decks/{nanoId}/..., bersihkan foldernya
                $decksDir = rtrim($this->uploadPath, DIRECTORY_SEPARATOR);
                if (dirname($dir) === $decksDir && is_dir($dir)) {
                    $this->deleteDirectoryRecursive($dir);
                }
            } elseif (is_dir($fullPath)) {
                $deleted = $this->deleteDirectoryRecursive($fullPath);
            }
        }

        // Hapus juga dari object storage jika bukan driver local
        if (StorageManager::getDefaultDriver() !== 'local') {
            $storage = StorageManager::disk();
            $storage->delete($filePath);
            if (preg_match('#uploads/decks/([^/]+)/#', $filePath, $m)) {
                $storage->deleteDirectory('uploads/decks/' . $m[1]);
            }
            return true;
        }

        return $deleted;
    }

    /**
     * Mendapatkan URL publik berkas (baik lokal maupun CDN / Object Storage)
     */
    public function getUrl(string $filePath): string
    {
        return StorageManager::disk()->url($filePath);
    }

    /**
     * Sinkronisasi seluruh isi direktori lokal ke object storage
     */
    protected function syncDirectoryToStorage(string $dir, string $storagePrefix): void
    {
        $storage = StorageManager::disk();
        if (!is_dir($dir)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($items as $item) {
            if ($item->isFile()) {
                $subPath = substr($item->getPathname(), strlen($dir));
                $subPath = str_replace('\\', '/', ltrim($subPath, '\\/'));
                $targetKey = rtrim($storagePrefix, '/') . '/' . $subPath;
                $content = file_get_contents($item->getPathname());
                if ($content !== false) {
                    $mime = $this->detectMimeType($item->getPathname());
                    $storage->put($targetKey, $content, $mime);
                }
            }
        }
    }

    /**
     * Deteksi MIME Type berkas berdasarkan ekstensi
     */
    protected function detectMimeType(string $path): string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return match ($ext) {
            'html', 'htm' => 'text/html; charset=UTF-8',
            'css'         => 'text/css; charset=UTF-8',
            'js', 'mjs'   => 'application/javascript; charset=UTF-8',
            'json'        => 'application/json; charset=UTF-8',
            'png'         => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif'         => 'image/gif',
            'webp'        => 'image/webp',
            'svg'         => 'image/svg+xml',
            'ico'         => 'image/x-icon',
            'woff'        => 'font/woff',
            'woff2'       => 'font/woff2',
            'ttf'         => 'font/ttf',
            'eot'         => 'application/vnd.ms-fontobject',
            'mp3'         => 'audio/mpeg',
            'mp4'         => 'video/mp4',
            'pdf'         => 'application/pdf',
            default       => 'application/octet-stream',
        };
    }

    /**
     * Hapus direktori beserta seluruh isinya secara rekursif
     */
    public function deleteDirectoryRecursive(string $dir): bool
    {
        if (!is_dir($dir)) {
            return false;
        }

        $items = array_diff(scandir($dir), ['.', '..']);
        foreach ($items as $item) {
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            is_dir($path) ? $this->deleteDirectoryRecursive($path) : unlink($path);
        }

        return rmdir($dir);
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

