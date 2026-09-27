<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\TokenModel;

/**
 * ApiAuthFilter
 *
 * Memvalidasi Bearer Token pada semua request ke /api/*
 * Letakkan token di header: Authorization: Bearer {token}
 */
class ApiAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
            return $this->unauthorized('Token tidak ditemukan');
        }

        $token = substr($authHeader, 7);

        $tokenModel = new TokenModel();
        $tokenData  = $tokenModel->getValidToken($token);

        if (!$tokenData) {
            return $this->unauthorized('Token tidak valid atau sudah kedaluwarsa');
        }

        // Simpan data user ke request untuk digunakan controller
        $request->user    = $tokenData->user;
        $request->tokenId = $tokenData->id;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // tidak diperlukan
    }

    private function unauthorized(string $message)
    {
        $response = service('response');
        $response->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        $response->setContentType('application/json');
        $response->setBody(json_encode([
            'status'  => false,
            'code'    => 401,
            'message' => $message,
            'data'    => null,
        ]));
        return $response;
    }
}
