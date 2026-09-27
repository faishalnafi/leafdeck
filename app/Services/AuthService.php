<?php

namespace App\Services;

use App\Models\TokenModel;
use App\Models\UserModel;
use CodeIgniter\I18n\Time;
use RuntimeException;

class AuthService
{
    protected TokenModel $tokenModel;
    protected UserModel $userModel;

    public function __construct(?TokenModel $tokenModel = null, ?UserModel $userModel = null)
    {
        $this->tokenModel = $tokenModel ?? new TokenModel();
        $this->userModel  = $userModel ?? new UserModel();
    }

    /**
     * Membuat token Bearer baru untuk user
     *
     * @param int $userId ID User
     * @param int $ttlSeconds Masa berlaku token dalam detik (default: 86400 / 24 jam)
     * @return object Row token yang berhasil dibuat
     */
    public function generateToken(int $userId, int $ttlSeconds = 86400): object
    {
        $user = $this->userModel->find($userId);
        if (!$user || (int) $user->is_active !== 1) {
            throw new RuntimeException('Akun pengguna tidak ditemukan atau dinonaktifkan');
        }

        $tokenStr = bin2hex(random_bytes(32)); // 64 karakter hex acak
        $expiresAt = Time::now()->addSeconds($ttlSeconds)->toDateTimeString();

        $tokenId = $this->tokenModel->insert([
            'user_id'    => $userId,
            'token'      => $tokenStr,
            'expires_at' => $expiresAt,
        ]);

        if (!$tokenId) {
            throw new RuntimeException('Gagal menerbitkan token otentikasi');
        }

        return $this->tokenModel->find($tokenId);
    }

    /**
     * Memvalidasi token Bearer dan mengambil data pengguna terkait
     */
    public function validateToken(string $token): ?object
    {
        return $this->tokenModel->getValidToken($token);
    }

    /**
     * Menghapus token (Logout dari perangkat ini)
     */
    public function revokeToken(string $token): bool
    {
        return $this->tokenModel->revokeToken($token);
    }

    /**
     * Menghapus seluruh token milik user (Logout dari semua perangkat)
     */
    public function revokeAllUserTokens(int $userId): bool
    {
        return $this->tokenModel->revokeUserTokens($userId);
    }

    /**
     * Helper untuk membuat paket login lengkap (token string + user data)
     */
    public function createLoginPackage(object $user, int $ttlSeconds = 86400): array
    {
        $tokenRow = $this->generateToken($user->id, $ttlSeconds);

        return [
            'token'      => $tokenRow->token,
            'expires_at' => $tokenRow->expires_at,
            'user'       => [
                'id'          => (int) $user->id,
                'sso_user_id' => $user->sso_user_id,
                'nomor_induk' => $user->nomor_induk,
                'nama'        => $user->nama,
                'email'       => $user->email,
                'role'        => $user->role,
                'avatar'      => $user->avatar,
            ],
        ];
    }
}
