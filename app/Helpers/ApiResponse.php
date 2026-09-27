<?php

namespace App\Helpers;

use CodeIgniter\HTTP\ResponseInterface;

/**
 * ApiResponse
 *
 * Helper standar untuk semua JSON response API LeafDeck.
 * Format: { status, code, message, data }
 */
class ApiResponse
{
    /**
     * Response sukses
     */
    public static function success(
        mixed $data = null,
        string $message = 'Success',
        int $code = 200
    ): ResponseInterface {
        return response()
            ->setStatusCode($code)
            ->setContentType('application/json')
            ->setBody(json_encode([
                'status'  => true,
                'code'    => $code,
                'message' => $message,
                'data'    => $data,
            ]));
    }

    /**
     * Response error / gagal
     */
    public static function error(
        string $message = 'Terjadi kesalahan',
        int $code = 400,
        mixed $data = null
    ): ResponseInterface {
        return response()
            ->setStatusCode($code)
            ->setContentType('application/json')
            ->setBody(json_encode([
                'status'  => false,
                'code'    => $code,
                'message' => $message,
                'data'    => $data,
            ]));
    }

    /**
     * Response validasi gagal (422)
     */
    public static function validationError(array $errors): ResponseInterface
    {
        return self::error('Validasi gagal', 422, $errors);
    }

    /**
     * Response unauthorized (401)
     */
    public static function unauthorized(string $message = 'Tidak terautentikasi'): ResponseInterface
    {
        return self::error($message, 401);
    }

    /**
     * Response forbidden (403)
     */
    public static function forbidden(string $message = 'Akses ditolak'): ResponseInterface
    {
        return self::error($message, 403);
    }

    /**
     * Response not found (404)
     */
    public static function notFound(string $message = 'Data tidak ditemukan'): ResponseInterface
    {
        return self::error($message, 404);
    }

    /**
     * Response created (201)
     */
    public static function created(mixed $data = null, string $message = 'Berhasil dibuat'): ResponseInterface
    {
        return self::success($data, $message, 201);
    }
}
