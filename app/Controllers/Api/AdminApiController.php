<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Helpers\ApiResponse;
use App\Models\DeckModel;
use App\Models\UserModel;
use App\Services\DeckService;
use Throwable;

class AdminApiController extends BaseController
{
    protected UserModel $userModel;
    protected DeckModel $deckModel;
    protected DeckService $deckService;

    public function __construct()
    {
        $this->userModel   = new UserModel();
        $this->deckModel   = new DeckModel();
        $this->deckService = new DeckService();
    }

    /**
     * Pastikan request dilakukan oleh admin / superadmin
     */
    protected function ensureAdmin(): ?object
    {
        $user = $this->request->user ?? null;
        if (!$user) {
            $userId = session()->get('user_id');
            if ($userId) {
                $user = $this->userModel->find($userId);
            }
        }

        // Development fallback
        if (!$user && ENVIRONMENT === 'development') {
            $user = $this->userModel->where('role', 'superadmin')->first();
        }

        if (!$user || !in_array($user->role, ['superadmin', 'admin'], true)) {
            return null;
        }

        return $user;
    }

    /**
     * POST /api/v1/admin/users/(:num)/role
     */
    public function updateRole($targetUserId = null)
    {
        $admin = $this->ensureAdmin();
        if (!$admin) {
            return ApiResponse::forbidden('Akses ditolak: Memerlukan hak akses administrator');
        }

        $targetUserId = (int) $targetUserId;
        $targetUser   = $this->userModel->find($targetUserId);
        if (!$targetUser) {
            return ApiResponse::notFound('Pengguna tidak ditemukan');
        }

        // Jangan izinkan mengubah role superadmin pertama sembarangan
        if ((int) $targetUser->id === 1 && (int) $admin->id !== 1) {
            return ApiResponse::error('Tidak dapat mengubah role Superadmin utama', 403);
        }

        $rawBody = $this->request->getBody();
        $json = json_decode(preg_replace('/^\xEF\xBB\xBF/', '', trim($rawBody)), true) ?? $this->request->getJSON(true) ?? $this->request->getPost();
        $role = $json['role'] ?? null;

        if (!in_array($role, ['superadmin', 'admin', 'pengguna'], true)) {
            return ApiResponse::error('Role tidak valid. Pilihan: superadmin, admin, pengguna', 400);
        }

        try {
            $this->userModel->updateRole($targetUserId, $role);
            return ApiResponse::success([
                'user_id' => $targetUserId,
                'role'    => $role,
            ], "Role pengguna {$targetUser->nama} berhasil diubah menjadi {$role}");
        } catch (Throwable $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * POST /api/v1/admin/users/(:num)/status
     */
    public function toggleStatus($targetUserId = null)
    {
        $admin = $this->ensureAdmin();
        if (!$admin) {
            return ApiResponse::forbidden('Akses ditolak: Memerlukan hak akses administrator');
        }

        $targetUserId = (int) $targetUserId;
        $targetUser   = $this->userModel->find($targetUserId);
        if (!$targetUser) {
            return ApiResponse::notFound('Pengguna tidak ditemukan');
        }

        if ((int) $targetUser->id === 1) {
            return ApiResponse::error('Tidak dapat menonaktifkan Superadmin utama', 403);
        }

        try {
            $this->userModel->toggleStatus($targetUserId);
            $updated = $this->userModel->find($targetUserId);
            $statusText = ((int) $updated->is_active === 1) ? 'diaktifkan' : 'dinonaktifkan';

            return ApiResponse::success([
                'user_id'   => $targetUserId,
                'is_active' => (int) $updated->is_active,
            ], "Akun pengguna {$targetUser->nama} berhasil {$statusText}");
        } catch (Throwable $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * DELETE /api/v1/admin/decks/(:segment) (Force delete by admin)
     */
    public function forceDeleteDeck($nanoId = null)
    {
        $admin = $this->ensureAdmin();
        if (!$admin) {
            return ApiResponse::forbidden('Akses ditolak: Memerlukan hak akses administrator');
        }

        $deck = $this->deckModel->withDeleted()->where('nano_id', (string) $nanoId)->first();
        if (!$deck) {
            return ApiResponse::notFound('Presentasi tidak ditemukan');
        }

        try {
            // Admin can force delete any deck
            $fileService = new \App\Services\FileService();
            if (!empty($deck->file_path)) {
                $fileService->delete($deck->file_path);
            }
            $this->deckModel->delete($deck->id, true);

            return ApiResponse::success(null, "Presentasi \"{$deck->title}\" berhasil dihapus permanen oleh admin");
        } catch (Throwable $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }
}
