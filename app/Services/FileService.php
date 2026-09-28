<?php

namespace App\Services;

use CodeIgniter\HTTP\Files\UploadedFile;
use App\Services\Storage\StorageManager;
use RuntimeException;

class FileService
{
    /**
     * Format ekstensi yang diizinkan (Terkunci strictly pada 4 format: HTML, ZIP, PDF, PPTX)
     */
    public const ALLOWED_EXTENSIONS = ['html', 'htm', 'zip', 'pdf', 'pptx'];

    /**
     * Batasan ukuran maksimal berkas: 250 MB (262,144,000 bytes)
     */
    public const MAX_FILE_BYTES = 262144000;

    /**
     * Daftar ekstensi berbahaya / executable / webshell yang dilarang keras
     */
    public const DANGEROUS_EXTENSIONS = [
        'php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'php8', 'phps', 'phar',
        'exe', 'dll', 'so', 'dylib', 'bin', 'com', 'msi', 'cmd', 'bat', 'vbs', 'vbe', 'ps1', 'psm1',
        'sh', 'bash', 'zsh', 'cgi', 'pl', 'py', 'rb', 'jsp', 'asp', 'aspx', 'hta', 'scr',
        'htaccess', 'htpasswd', 'env', 'git', 'conf', 'ini'
    ];

    /**
     * Path sentralisasi penyimpanan lokal
     */
    protected string $uploadPath;

    public function __construct()
    {
        $config = config('Storage');
        $this->uploadPath = $config->local['root'] ?? (FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'decks' . DIRECTORY_SEPARATOR);

        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }

    /**
     * Mendapatkan path penyimpanan lokal tersentralisasi
     */
    public function getUploadPath(): string
    {
        return $this->uploadPath;
    }

    /**
     * Verifikasi keamanan berlapis terhadap berkas yang diunggah (Anti-Injection & Cyber-Attack)
     *
     * @param UploadedFile|string $file
     * @param array $allowedExtensions
     * @return array [temp_path, original_name, ext, size]
     * @throws RuntimeException Jika ada indikasi serangan siber atau format tidak sesuai
     */
    public function validateUploadedFileSecurely($file, array $allowedExtensions = self::ALLOWED_EXTENSIONS): array
    {
        $tempPath     = '';
        $originalName = '';
        $size         = 0;

        if ($file instanceof UploadedFile) {
            if (!$file->isValid()) {
                throw new RuntimeException('File upload tidak valid: ' . $file->getErrorString());
            }
            $tempPath     = $file->getTempName();
            $originalName = $file->getClientName();
            $size         = $file->getSize();
        } elseif (is_string($file) && file_exists($file)) {
            $tempPath     = $file;
            $originalName = basename($file);
            $size         = filesize($file);
        } elseif (is_string($file)) {
            // Konten mentah string (misalnya HTML dari test/editor)
            $size = strlen($file);
            $originalName = 'content.html';
        } else {
            throw new RuntimeException('Tipe berkas tidak dikenali oleh sistem.');
        }

        // 1. Validasi Batasan Ukuran Maksimal (250 MB)
        if ($size > self::MAX_FILE_BYTES) {
            throw new RuntimeException('Ukuran berkas (' . self::formatBytes($size) . ') melebihi batas maksimal yang diizinkan (250 MB).');
        }

        // 2. Cegah Serangan Null-Byte Injection & Directory Traversal pada nama berkas
        if (str_contains($originalName, "\0") || str_contains($originalName, "\\0") || str_contains($originalName, "%00")) {
            throw new RuntimeException('Terdeteksi serangan Null-Byte Injection pada nama berkas!');
        }
        if (str_contains($originalName, '..') || str_contains($originalName, '/') || str_contains($originalName, '\\')) {
            $originalName = basename(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $originalName));
        }

        // 3. Cegah Serangan Double Extension / Ekstensi Terselubung (Contoh: payload.php.pdf, webshell.phtml.zip)
        $nameParts = explode('.', $originalName);
        if (count($nameParts) > 2) {
            $intermediateParts = array_slice($nameParts, 1, -1);
            foreach ($intermediateParts as $part) {
                if (in_array(strtolower($part), self::DANGEROUS_EXTENSIONS, true)) {
                    throw new RuntimeException('Serangan ekstensi ganda terdeteksi (' . esc($originalName) . '). Berkas mengandung ekstensi script berbahaya yang disamarkan.');
                }
            }
        }

        // 4. Validasi Whitelist Ekstensi Terkunci (Hanya HTML, ZIP, PDF, PPTX)
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExtensions, true)) {
            throw new RuntimeException('Format berkas tidak diizinkan. Sistem hanya mengizinkan 4 format: HTML (.html/.htm), paket Flipbook (.zip), dokumen PDF (.pdf), atau presentasi PowerPoint (.pptx).');
        }

        // 5. Deep Inspection: Magic Bytes & Binary Signatures
        if (!empty($tempPath) && file_exists($tempPath)) {
            $handle = fopen($tempPath, 'rb');
            if ($handle === false) {
                throw new RuntimeException('Gagal membaca signature berkas untuk verifikasi keamanan.');
            }
            $header = fread($handle, 1024);
            fclose($handle);

            // A. Verifikasi Format PDF
            if ($ext === 'pdf') {
                if (strncmp($header, '%PDF-', 5) !== 0) {
                    throw new RuntimeException('Verifikasi keamanan gagal: Berkas berekstensi .pdf tetapi magic bytes tidak valid (%PDF-). Berkas mungkin telah dimodifikasi atau disusupi.');
                }
            }

            // B. Verifikasi Format PPTX (Microsoft PowerPoint Open XML)
            if ($ext === 'pptx') {
                // Berkas PPTX adalah arsip ZIP yang wajib memiliki signature PK\x03\x04
                if (strncmp($header, "PK\x03\x04", 4) !== 0 && strncmp($header, "PK\x05\x06", 4) !== 0) {
                    throw new RuntimeException('Verifikasi keamanan gagal: Berkas .pptx tidak memiliki signature ZIP OpenXML yang valid.');
                }

                // Buka arsip dan pastikan struktur internal presentasi PowerPoint valid & bebas macro VBA
                if (class_exists('\ZipArchive')) {
                    $zip = new \ZipArchive();
                    if ($zip->open($tempPath) !== true) {
                        throw new RuntimeException('Berkas PPTX rusak atau tidak dapat diuraikan.');
                    }

                    $hasContentTypes = false;
                    $hasPptDir = false;

                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $entryName = $zip->getNameIndex($i);
                        $entryNormalized = strtolower(str_replace('\\', '/', $entryName));

                        if ($entryNormalized === '[content_types].xml') {
                            $hasContentTypes = true;
                        }
                        if (str_starts_with($entryNormalized, 'ppt/presentation.xml') || str_starts_with($entryNormalized, 'ppt/')) {
                            $hasPptDir = true;
                        }

                        // Tolak jika mengandung macro VBA biner (.bin) atau executable
                        if (str_contains($entryNormalized, 'vbaproject.bin') || str_ends_with($entryNormalized, '.exe') || str_ends_with($entryNormalized, '.dll')) {
                            $zip->close();
                            throw new RuntimeException('Berkas PPTX ditolak karena terdeteksi mengandung script Macro VBA atau berkas biner executable.');
                        }
                    }
                    $zip->close();

                    if (!$hasContentTypes || !$hasPptDir) {
                        throw new RuntimeException('Berkas PPTX tidak valid: Struktur internal bukan dokumen presentasi PowerPoint resmi.');
                    }
                }
            }

            // C. Verifikasi Format ZIP (Interactive HTML5 / Flipbook)
            if ($ext === 'zip') {
                if (strncmp($header, "PK\x03\x04", 4) !== 0 && strncmp($header, "PK\x05\x06", 4) !== 0) {
                    throw new RuntimeException('Verifikasi keamanan gagal: Berkas bukan arsip ZIP yang valid.');
                }

                if (class_exists('\ZipArchive')) {
                    $zip = new \ZipArchive();
                    if ($zip->open($tempPath) !== true) {
                        throw new RuntimeException('Gagal membuka paket berkas ZIP untuk pemeriksaan keamanan.');
                    }

                    // Anti Zip-Bomb: Batasi maksimal file dan ukuran total ekstraksi (600 MB)
                    if ($zip->numFiles > 5000) {
                        $zip->close();
                        throw new RuntimeException('Paket ZIP ditolak: Jumlah berkas di dalam arsip melebihi batas wajar (maks. 5.000 berkas).');
                    }

                    $totalUncompressedBytes = 0;
                    $hasHtmlEntry = false;

                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $stat = $zip->statIndex($i);
                        $entryName = $zip->getNameIndex($i);
                        $normalizedName = str_replace('\\', '/', $entryName);

                        // Anti Zip-Slip: Cegah directory traversal
                        if (str_contains($normalizedName, '../') || str_contains($normalizedName, '..\\') || str_starts_with($normalizedName, '/') || str_starts_with($normalizedName, '\\') || preg_match('/^[a-zA-Z]:/', $normalizedName)) {
                            $zip->close();
                            throw new RuntimeException('Serangan Zip-Slip / Path Traversal terdeteksi di dalam berkas ZIP (' . esc($entryName) . '). Unggahan dibatalkan.');
                        }

                        // Anti Webshell & Executable di dalam ZIP
                        $entryExt = strtolower(pathinfo($normalizedName, PATHINFO_EXTENSION));
                        if (in_array($entryExt, self::DANGEROUS_EXTENSIONS, true)) {
                            $zip->close();
                            throw new RuntimeException('Paket ZIP mengandung berkas skrip atau program berbahaya yang dilarang (' . esc($entryName) . '). Unggahan ditolak.');
                        }

                        if (in_array($entryExt, ['html', 'htm'], true)) {
                            $hasHtmlEntry = true;
                        }

                        $totalUncompressedBytes += ($stat['size'] ?? 0);
                        if ($totalUncompressedBytes > 629145600) { // 600 MB
                            $zip->close();
                            throw new RuntimeException('Paket ZIP ditolak: Total ukuran dekompresi melebihi batas aman 600 MB (potensi Zip-Bomb).');
                        }
                    }
                    $zip->close();

                    if (!$hasHtmlEntry) {
                        throw new RuntimeException('Paket ZIP harus menyertakan minimal 1 berkas presentasi HTML (index.html).');
                    }
                }
            }

            // D. Verifikasi Format HTML
            if ($ext === 'html' || $ext === 'htm') {
                // Cegah executable terselubung (.exe atau .elf dengan ekstensi .html)
                if (strncmp($header, "MZ", 2) === 0 || strncmp($header, "\x7fELF", 4) === 0) {
                    throw new RuntimeException('Berkas terdeteksi sebagai binary executable (.exe/.elf), bukan berkas HTML!');
                }

                // Scan konten untuk mencegah PHP webshell injection & SSI injection
                $contentSample = file_get_contents($tempPath, false, null, 0, 524288); // 512KB first chunk
                if ($contentSample !== false) {
                    if (preg_match('/<\?(?:php|=|\s)/i', $contentSample) || preg_match('/<script\s+language\s*=\s*["\']?php/i', $contentSample)) {
                        throw new RuntimeException('Berkas HTML ditolak karena terdeteksi tag skrip PHP server-side yang berbahaya.');
                    }
                    if (preg_match('/<!--#(?:exec|include|config)/i', $contentSample)) {
                        throw new RuntimeException('Berkas HTML ditolak karena terdeteksi arahan Server-Side Includes (SSI).');
                    }
                }
            }
        } elseif (is_string($file) && ($ext === 'html' || $ext === 'htm')) {
            // Konten mentah string HTML
            if (strncmp($file, "MZ", 2) === 0 || strncmp($file, "\x7fELF", 4) === 0) {
                throw new RuntimeException('Konten terdeteksi sebagai binary executable!');
            }
            if (preg_match('/<\?(?:php|=|\s)/i', $file) || preg_match('/<script\s+language\s*=\s*["\']?php/i', $file)) {
                throw new RuntimeException('Konten HTML ditolak karena mengandung tag skrip PHP server-side.');
            }
            if (preg_match('/<!--#(?:exec|include|config)/i', $file)) {
                throw new RuntimeException('Konten HTML ditolak karena mengandung arahan Server-Side Includes (SSI).');
            }
        }

        return [
            'temp_path'     => $tempPath,
            'original_name' => $originalName,
            'ext'           => $ext,
            'size'          => $size,
        ];
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
        $meta = $this->validateUploadedFileSecurely($file, ['html', 'htm']);
        $fileName = $nanoId . '.html';
        $relPath  = 'uploads/decks/' . $fileName;

        $content = '';
        if ($file instanceof UploadedFile || (is_string($file) && file_exists($file))) {
            $content = file_get_contents($meta['temp_path']);
            if ($content === false) {
                throw new RuntimeException('Gagal membaca konten berkas HTML unggahan.');
            }
        } elseif (is_string($file)) {
            $content = $file;
        } else {
            throw new RuntimeException('Tipe konten berkas tidak didukung.');
        }

        // Jika object storage aktif (s3, r2, gcs, custom), langsung simpan ke remote storage
        if (StorageManager::getDefaultDriver() !== 'local') {
            $uploaded = StorageManager::disk()->put($relPath, $content, 'text/html; charset=UTF-8');
            if (!$uploaded) {
                throw new RuntimeException('Gagal mengunggah berkas HTML ke Object Storage.');
            }
            return $relPath;
        }

        // Driver lokal: simpan ke direktori tersentralisasi public/uploads/decks/
        $destination = $this->uploadPath . $fileName;
        file_put_contents($destination, $content);

        return $relPath;
    }

    /**
     * Menyimpan berkas dokumen presentasi PDF
     *
     * @param UploadedFile|string $file
     * @param string $nanoId
     * @return string Relatif path berkas (contoh: uploads/decks/{nanoId}.pdf)
     */
    public function savePdf($file, string $nanoId): string
    {
        $meta = $this->validateUploadedFileSecurely($file, ['pdf']);
        $fileName = $nanoId . '.pdf';
        $relPath  = 'uploads/decks/' . $fileName;

        $content = file_get_contents($meta['temp_path']);
        if ($content === false) {
            throw new RuntimeException('Gagal membaca konten berkas PDF unggahan.');
        }

        if (StorageManager::getDefaultDriver() !== 'local') {
            $uploaded = StorageManager::disk()->put($relPath, $content, 'application/pdf');
            if (!$uploaded) {
                throw new RuntimeException('Gagal mengunggah berkas PDF ke Object Storage.');
            }
            return $relPath;
        }

        $destination = $this->uploadPath . $fileName;
        file_put_contents($destination, $content);

        return $relPath;
    }

    /**
     * Menyimpan berkas presentasi Microsoft PowerPoint (PPTX)
     *
     * @param UploadedFile|string $file
     * @param string $nanoId
     * @return string Relatif path berkas (contoh: uploads/decks/{nanoId}.pptx)
     */
    public function savePptx($file, string $nanoId): string
    {
        $meta = $this->validateUploadedFileSecurely($file, ['pptx']);
        $fileName = $nanoId . '.pptx';
        $relPath  = 'uploads/decks/' . $fileName;

        $content = file_get_contents($meta['temp_path']);
        if ($content === false) {
            throw new RuntimeException('Gagal membaca konten berkas PPTX unggahan.');
        }

        $mime = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
        if (StorageManager::getDefaultDriver() !== 'local') {
            $uploaded = StorageManager::disk()->put($relPath, $content, $mime);
            if (!$uploaded) {
                throw new RuntimeException('Gagal mengunggah berkas PPTX ke Object Storage.');
            }
            return $relPath;
        }

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

        $meta = $this->validateUploadedFileSecurely($file, ['zip']);

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

        $zipPath = $meta['temp_path'];
        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new RuntimeException('Gagal membaca paket berkas ZIP presentasi.');
        }

        // Ekstrak dengan normalisasi path dan anti-traversal
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = $zip->getNameIndex($i);
            $normalizedName = str_replace('\\', '/', $entryName);

            // Cegah directory traversal
            if (str_contains($normalizedName, '../') || str_contains($normalizedName, '..\\') || str_starts_with($normalizedName, '/') || str_starts_with($normalizedName, '\\')) {
                continue;
            }

            // Cegah ekstraksi file berbahaya
            $entryExt = strtolower(pathinfo($normalizedName, PATHINFO_EXTENSION));
            if (in_array($entryExt, self::DANGEROUS_EXTENSIONS, true)) {
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
     * Membaca isi file berdasarkan relative path
     */
    public function readHtml(string $filePath): ?string
    {
        return $this->readFileContent($filePath);
    }

    /**
     * Membaca isi file mentah (HTML, PDF, atau dokumen lain)
     */
    public function readFileContent(string $filePath): ?string
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
    public function detectMimeType(string $path): string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return match ($ext) {
            'html', 'htm' => 'text/html; charset=UTF-8',
            'pdf'         => 'application/pdf',
            'pptx'        => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'zip'         => 'application/zip',
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
     * Menghitung total ukuran file di storage decks (dalam bytes)
     */
    public function getTotalStorageBytes(): int
    {
        $bytes = 0;
        if (is_dir($this->uploadPath)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->uploadPath, \RecursiveDirectoryIterator::SKIP_DOTS)
            );
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $bytes += $file->getSize();
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
