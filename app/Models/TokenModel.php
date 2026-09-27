<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\I18n\Time;

class TokenModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'user_tokens';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'token',
        'expires_at',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Mengambil data token valid beserta user yang aktif.
     * Digunakan langsung oleh ApiAuthFilter.
     */
    public function getValidToken(string $token): ?object
    {
        $now = Time::now()->toDateTimeString();

        $tokenRow = $this->where('token', $token)
            ->where('expires_at >', $now)
            ->first();

        if (!$tokenRow) {
            return null;
        }

        $userModel = new UserModel();
        $user = $userModel->find($tokenRow->user_id);

        if (!$user || (int) $user->is_active !== 1) {
            return null;
        }

        $tokenRow->user = $user;
        return $tokenRow;
    }

    /**
     * Hapus token spesifik (logout dari perangkat ini)
     */
    public function revokeToken(string $token): bool
    {
        return (bool) $this->where('token', $token)->delete();
    }

    /**
     * Hapus seluruh token milik user (logout dari semua perangkat)
     */
    public function revokeUserTokens(int $userId): bool
    {
        return (bool) $this->where('user_id', $userId)->delete();
    }
}
