<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Helpers\ApiResponse;
use App\Models\UserModel;
use Throwable;

class UserController extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * GET /api/v1/users/profile
     */
    public function profile()
    {
        $user = $this->request->user ?? null;
        if (!$user) {
            $userId = session()->get('user_id');
            if ($userId) {
                $user = $this->userModel->find($userId);
            }
        }

        if (!$user) {
            return ApiResponse::unauthorized('Sesi tidak ditemukan');
        }

        return ApiResponse::success([
            'id'          => (int) $user->id,
            'sso_user_id' => $user->sso_user_id,
            'nomor_induk' => $user->nomor_induk,
            'nama'        => $user->nama,
            'email'       => $user->email,
            'role'        => $user->role,
            'avatar'      => $user->avatar,
            'created_at'  => $user->created_at,
        ]);
    }

    /**
     * PUT /api/v1/users/profile
     */
    public function updateProfile()
    {
        $user = $this->request->user ?? null;
        $userId = $user ? (int) $user->id : (int) session()->get('user_id');

        if (!$userId) {
            return ApiResponse::unauthorized();
        }

        $json = $this->request->getJSON(true) ?? $this->request->getRawInput();
        $allowedFields = ['nama', 'email', 'avatar'];
        $updateData = array_intersect_key($json, array_flip($allowedFields));

        if (empty($updateData)) {
            return ApiResponse::validation(['error' => 'Tidak ada data pembaruan yang dikirim']);
        }

        try {
            $this->userModel->update($userId, $updateData);
            $updated = $this->userModel->find($userId);

            return ApiResponse::success([
                'id'          => (int) $updated->id,
                'nama'        => $updated->nama,
                'email'       => $updated->email,
                'role'        => $updated->role,
                'avatar'      => $updated->avatar,
            ], 'Profil berhasil diperbarui');
        } catch (Throwable $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }
}
