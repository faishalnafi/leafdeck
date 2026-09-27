<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'sso_user_id',
        'nomor_induk',
        'nama',
        'email',
        'role',
        'avatar',
        'is_active',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'nama' => 'required|min_length[2]|max_length[150]',
        'role' => 'in_list[superadmin,admin,pengguna]',
    ];

    /**
     * Cari user berdasarkan UUID SSO
     */
    public function findBySsoId(string $ssoUserId): ?object
    {
        return $this->where('sso_user_id', $ssoUserId)->first();
    }

    /**
     * Cari user berdasarkan Nomor Induk (NIP/NIS/NIK)
     */
    public function findByNomorInduk(string $nomorInduk): ?object
    {
        return $this->where('nomor_induk', $nomorInduk)->first();
    }

    /**
     * Ambil semua pengguna terurut berdasarkan waktu dibuat
     */
    public function getAllUsers(int $limit = 100, int $offset = 0): array
    {
        return $this->orderBy('created_at', 'DESC')->findAll($limit, $offset);
    }

    /**
     * Hitung pengguna aktif
     */
    public function countActiveUsers(): int
    {
        return $this->where('is_active', 1)->countAllResults();
    }

    /**
     * Perbarui role pengguna (superadmin, admin, pengguna)
     */
    public function updateRole(int $userId, string $newRole): bool
    {
        if (!in_array($newRole, ['superadmin', 'admin', 'pengguna'], true)) {
            return false;
        }

        return (bool) $this->update($userId, ['role' => $newRole]);
    }

    /**
     * Toggle status aktif/nonaktif akun
     */
    public function toggleStatus(int $userId): bool
    {
        $user = $this->find($userId);
        if (!$user) {
            return false;
        }

        $newStatus = ((int) $user->is_active === 1) ? 0 : 1;
        return (bool) $this->update($userId, ['is_active' => $newStatus]);
    }
}

