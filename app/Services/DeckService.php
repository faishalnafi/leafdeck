<?php

namespace App\Services;

use App\Models\DeckModel;
use Hidehalo\Nanoid\Client as NanoidClient;
use RuntimeException;

class DeckService
{
    protected DeckModel $deckModel;
    protected FileService $fileService;
    protected NanoidClient $nanoid;

    public function __construct(?DeckModel $deckModel = null, ?FileService $fileService = null)
    {
        $this->deckModel   = $deckModel ?? new DeckModel();
        $this->fileService = $fileService ?? new FileService();
        $this->nanoid      = new NanoidClient();
    }

    /**
     * Generate NanoID acak 21 karakter (Google-style unique identifier)
     */
    public function generateNanoId(): string
    {
        // Pastikan tidak tabrakan di database
        do {
            $id = $this->nanoid->generateId(21);
            $exists = $this->deckModel->where('nano_id', $id)->first();
        } while ($exists !== null);

        return $id;
    }

    /**
     * Mengambil semua presentasi milik user tertentu
     */
    public function getAllByUser(int $userId, int $limit = 20, int $offset = 0): array
    {
        return $this->deckModel->getDecksByUser($userId, $limit, $offset);
    }

    /**
     * Mengambil detail deck berdasarkan NanoID
     */
    public function findByNanoId(string $nanoId): ?object
    {
        return $this->deckModel->findByNanoId($nanoId);
    }

    /**
     * Membuat deck baru dari file HTML yang diupload
     *
     * @param int $userId ID pemilik deck
     * @param array $data Data input (title, description, is_public)
     * @param mixed $file UploadedFile instance atau string konten HTML
     * @return object Entitas deck yang berhasil disimpan
     */
    public function create(int $userId, array $data, $file): object
    {
        $nanoId = $this->generateNanoId();

        $isZip = false;
        if ($file instanceof \CodeIgniter\HTTP\Files\UploadedFile) {
            $ext = strtolower($file->getClientExtension());
            if ($ext === 'zip') {
                $isZip = true;
            }
        } elseif (is_string($file) && (str_ends_with(strtolower($file), '.zip') || (file_exists($file) && str_ends_with(strtolower(pathinfo($file, PATHINFO_EXTENSION)), 'zip')))) {
            $isZip = true;
        }

        if ($isZip) {
            $zipResult = $this->fileService->saveZipPackage($file, $nanoId);
            $filePath = $zipResult['file_path'];

            // Gunakan judul dari dokumen jika input judul kosong
            if (empty($data['title']) && !empty($zipResult['title'])) {
                $data['title'] = $zipResult['title'];
            }
            // Gunakan thumbnail sampul otomatis dari isi buku
            if (empty($data['thumbnail']) && !empty($zipResult['thumbnail'])) {
                $data['thumbnail'] = $zipResult['thumbnail'];
            }
        } else {
            // Simpan file HTML via FileService
            $filePath = $this->fileService->saveHtml($file, $nanoId);
        }

        if (empty($data['title'])) {
            throw new RuntimeException('Judul presentasi wajib diisi');
        }

        $payload = [
            'nano_id'     => $nanoId,
            'user_id'     => $userId,
            'title'       => trim($data['title']),
            'description' => $data['description'] ?? null,
            'file_path'   => $filePath,
            'thumbnail'   => $data['thumbnail'] ?? null,
            'is_public'   => !empty($data['is_public']) ? 1 : 0,
            'view_count'  => 0,
        ];

        $deckId = $this->deckModel->insert($payload);

        if (!$deckId) {
            // Rollback file jika gagal simpan ke DB
            $this->fileService->delete($filePath);
            $errors = implode(', ', $this->deckModel->errors());
            throw new RuntimeException('Gagal menyimpan presentasi ke database: ' . $errors);
        }

        return $this->deckModel->find($deckId);
    }

    /**
     * Memperbarui metadata deck (hanya pemilik atau admin)
     */
    public function update(string $nanoId, int $userId, array $data): ?object
    {
        $deck = $this->deckModel->findByNanoId($nanoId);
        if (!$deck) {
            throw new RuntimeException('Presentasi tidak ditemukan');
        }

        if ((int) $deck->user_id !== $userId) {
            throw new RuntimeException('Anda tidak memiliki izin untuk mengubah presentasi ini');
        }

        if (isset($data['is_public'])) {
            $data['is_public'] = (!empty($data['is_public']) && $data['is_public'] !== '0' && $data['is_public'] !== false) ? 1 : 0;
        }

        $allowedFields = ['title', 'description', 'is_public', 'thumbnail'];
        $updateData = array_intersect_key($data, array_flip($allowedFields));

        if (!empty($updateData)) {
            $this->deckModel->update($deck->id, $updateData);
        }

        return $this->deckModel->find($deck->id);
    }

    /**
     * Menghapus deck (Soft delete)
     */
    public function delete(string $nanoId, int $userId): bool
    {
        $deck = $this->deckModel->findByNanoId($nanoId);
        if (!$deck) {
            throw new RuntimeException('Presentasi tidak ditemukan');
        }

        if ((int) $deck->user_id !== $userId) {
            throw new RuntimeException('Anda tidak memiliki izin untuk menghapus presentasi ini');
        }

        return (bool) $this->deckModel->delete($deck->id);
    }

    /**
     * Mengambil daftar presentasi yang berada di tong sampah milik user
     */
    public function getTrashByUser(int $userId, int $limit = 50, int $offset = 0): array
    {
        return $this->deckModel->getTrashByUser($userId, $limit, $offset);
    }

    /**
     * Memulihkan presentasi dari tong sampah (Restore)
     */
    public function restore(string $nanoId, int $userId): bool
    {
        $deck = $this->deckModel->findDeletedByNanoId($nanoId);
        if (!$deck) {
            throw new RuntimeException('Presentasi tidak ditemukan di tong sampah');
        }

        if ((int) $deck->user_id !== $userId) {
            throw new RuntimeException('Anda tidak memiliki izin untuk memulihkan presentasi ini');
        }

        return $this->deckModel->restoreDeck($deck->id);
    }

    /**
     * Menghapus presentasi secara permanen beserta file fisiknya
     */
    public function forceDelete(string $nanoId, int $userId): bool
    {
        $deck = $this->deckModel->findDeletedByNanoId($nanoId);
        if (!$deck) {
            // Cek juga jika masih ada di daftar aktif
            $deck = $this->deckModel->findByNanoId($nanoId);
        }

        if (!$deck) {
            throw new RuntimeException('Presentasi tidak ditemukan');
        }

        if ((int) $deck->user_id !== $userId) {
            throw new RuntimeException('Anda tidak memiliki izin untuk menghapus permanen presentasi ini');
        }

        // Hapus file fisik HTML
        if (!empty($deck->file_path)) {
            $this->fileService->delete($deck->file_path);
        }

        // Hapus permanen dari database (purge = true)
        return (bool) $this->deckModel->delete($deck->id, true);
    }

    /**
     * Menambah hitungan view count
     */
    public function incrementViews(string $nanoId): bool
    {
        return $this->deckModel->incrementViews($nanoId);
    }

    /**
     * Membaca konten HTML mentah dari sebuah deck
     */
    public function getHtmlContent(object $deck): ?string
    {
        return $this->fileService->readHtml($deck->file_path);
    }
}

