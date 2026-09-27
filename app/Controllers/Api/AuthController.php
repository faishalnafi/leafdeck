<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Helpers\ApiResponse;
use App\Services\AuthService;
use App\Services\SsoService;
use Throwable;

class AuthController extends BaseController
{
    protected SsoService $ssoService;
    protected AuthService $authService;

    public function __construct()
    {
        $this->ssoService  = new SsoService();
        $this->authService = new AuthService();
    }

    /**
     * POST /api/v1/auth/sso
     * Menerima token JWT dari portal SSO dan menukarkannya dengan Bearer token LeafDeck
     */
    public function ssoExchange()
    {
        $json = $this->request->getJSON(true);
        $ssoToken = $json['sso_token'] ?? $this->request->getPost('sso_token') ?? $this->request->getVar('token');

        if (empty($ssoToken)) {
            return ApiResponse::validation(['sso_token' => 'Token SSO wajib disertakan']);
        }

        try {
            $decoded = $this->ssoService->validateJwt($ssoToken);
            $user = $this->ssoService->provisionUser($decoded);

            // Terbitkan Bearer token lokal (TTL default: 24 jam)
            $loginPackage = $this->authService->createLoginPackage($user);

            return ApiResponse::success($loginPackage, 'Otentikasi SSO berhasil');
        } catch (Throwable $e) {
            return ApiResponse::error('Autentikasi SSO gagal: ' . $e->getMessage(), 401);
        }
    }

    /**
     * POST /api/v1/auth/logout
     * Menghapus Bearer token aktif
     */
    public function logout()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        if (str_starts_with($authHeader, 'Bearer ')) {
            $token = substr($authHeader, 7);
            $this->authService->revokeToken($token);
        }

        return ApiResponse::success(null, 'Berhasil keluar (logout)');
    }
}
