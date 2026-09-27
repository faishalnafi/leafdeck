<?php

namespace App\Models;

use CodeIgniter\Model;

class DeckModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'decks';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nano_id',
        'user_id',
        'title',
        'description',
        'file_path',
        'thumbnail',
        'is_public',
        'view_count',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'nano_id'   => 'required|max_length[21]|is_unique[decks.nano_id,id,{id}]',
        'user_id'   => 'required|is_natural_no_zero',
        'title'     => 'required|min_length[3]|max_length[200]',
        'file_path' => 'required',
    ];

    /**
     * Cari deck berdasarkan NanoID (Google-style URL identifier)
     */
    public function findByNanoId(string $nanoId): ?object
    {
        return $this->where('nano_id', $nanoId)->first();
    }

    /**
     * Ambil deck milik user tertentu (ordered by terbaru)
     */
    public function getDecksByUser(int $userId, int $limit = 20, int $offset = 0): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll($limit, $offset);
    }

    /**
     * Increment view count
     */
    public function incrementViews(string $nanoId): bool
    {
        return (bool) $this->where('nano_id', $nanoId)
            ->set('view_count', 'view_count + 1', false)
            ->update();
    }

    /**
     * Ambil deck yang sudah di-soft-delete milik user tertentu
     */
    public function getTrashByUser(int $userId, int $limit = 50, int $offset = 0): array
    {
        return $this->onlyDeleted()
            ->where('user_id', $userId)
            ->orderBy('deleted_at', 'DESC')
            ->findAll($limit, $offset);
    }

    /**
     * Cari deck yang sudah di-soft-delete berdasarkan NanoID
     */
    public function findDeletedByNanoId(string $nanoId): ?object
    {
        return $this->onlyDeleted()
            ->where('nano_id', $nanoId)
            ->first();
    }

    /**
     * Pulihkan deck yang di-soft-delete
     */
    public function restoreDeck(int|string $id): bool
    {
        return (bool) $this->builder()
            ->where('id', $id)
            ->update(['deleted_at' => null]);
    }

    /**
     * Ambil seluruh deck aktif beserta data penulisnya untuk keperluan Admin
     */
    public function getAllDecksWithAuthor(int $limit = 100, int $offset = 0): array
    {
        return $this->builder()
            ->select('decks.*, users.nama as author_name, users.email as author_email, users.nomor_induk as author_nip, users.role as author_role')
            ->join('users', 'users.id = decks.user_id', 'left')
            ->where('decks.deleted_at', null)
            ->orderBy('decks.created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->getResult();
    }

    /**
     * Hitung total tayangan seluruh deck
     */
    public function getTotalViews(): int
    {
        $row = $this->builder()
            ->selectSum('view_count', 'total_views')
            ->where('deleted_at', null)
            ->get()
            ->getFirstRow();

        return (int) ($row->total_views ?? 0);
    }
}


