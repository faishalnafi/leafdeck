<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Helpers\ApiResponse;
use App\Services\DeckService;
use Throwable;

class DeckController extends BaseController
{
    protected DeckService $deckService;

    public function __construct()
    {
        $this->deckService = new DeckService();
    }

    /**
     * GET /api/v1/decks
     */
    public function index()
    {
        $userId = (int) ($this->request->user->id ?? session()->get('user_id') ?? 0);
        if ($userId === 0) {
            return ApiResponse::unauthorized();
        }

        $decks = $this->deckService->getAllByUser($userId);

        return ApiResponse::success([
            'decks' => $decks,
            'total' => count($decks),
        ]);
    }

    /**
     * POST /api/v1/decks (Upload Deck)
     */
    public function create()
    {
        $userId = (int) ($this->request->user->id ?? session()->get('user_id') ?? 0);
        if ($userId === 0) {
            return ApiResponse::unauthorized();
        }

        $title = $this->request->getPost('title');
        $description = $this->request->getPost('description');
        $isPublic = (int) $this->request->getPost('is_public');

        $file = $this->request->getFile('file');
        if (!$file || !$file->isValid()) {
            $msg = $file ? $file->getErrorString() : 'Berkas materi presentasi wajib diunggah';
            return ApiResponse::error($msg, 400);
        }

        // Batasan ukuran berkas: 250 MB (262.144.000 bytes)
        $maxSizeBytes = 250 * 1024 * 1024;
        if ($file->getSize() > $maxSizeBytes) {
            return ApiResponse::error('Ukuran berkas melebihi batas maksimal 250 MB.', 400);
        }

        // Whitelist ekstensi terkunci pada 4 format: HTML, ZIP, PDF, PPTX
        $ext = strtolower($file->getClientExtension());
        $allowedExtensions = ['html', 'htm', 'zip', 'pdf', 'pptx'];
        if (!in_array($ext, $allowedExtensions, true)) {
            return ApiResponse::error('Format berkas tidak didukung. Unggah berkas HTML (.html/.htm), paket ZIP (.zip), dokumen PDF (.pdf), atau PowerPoint (.pptx).', 400);
        }

        // Jika judul dikosongkan, gunakan nama file tanpa ekstensi
        if (empty($title)) {
            $title = pathinfo($file->getClientName(), PATHINFO_FILENAME);
        }

        try {
            $deck = $this->deckService->create($userId, [
                'title'       => $title,
                'description' => $description,
                'is_public'   => $isPublic,
            ], $file);

            return ApiResponse::created([
                'id'      => (int) $deck->id,
                'nano_id' => $deck->nano_id,
                'title'   => $deck->title,
                'url'     => site_url("presentation/u/0/d/{$deck->nano_id}/view"),
            ], 'Presentasi berhasil diunggah');
        } catch (Throwable $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * GET /api/v1/decks/(:segment)
     */
    public function show($nanoId = null)
    {
        $deck = $this->deckService->findByNanoId((string) $nanoId);
        if (!$deck) {
            return ApiResponse::notFound('Presentasi tidak ditemukan');
        }

        return ApiResponse::success($deck);
    }

    /**
     * PUT /api/v1/decks/(:segment)
     */
    public function update($nanoId = null)
    {
        $userId = (int) ($this->request->user->id ?? session()->get('user_id') ?? 0);
        if ($userId === 0 && ENVIRONMENT === 'development') {
            $userModel = new \App\Models\UserModel();
            $admin = $userModel->where('role', 'superadmin')->first();
            if ($admin) {
                $userId = (int) $admin->id;
            }
        }

        $data = [];
        $rawBody = $this->request->getBody();
        if (!empty($rawBody)) {
            $cleanBody = preg_replace('/^\xEF\xBB\xBF/', '', trim($rawBody));
            $json = json_decode($cleanBody, true);
            if (is_array($json)) {
                $data = $json;
            }
        }

        if (empty($data)) {
            try {
                $json = $this->request->getJSON(true);
                if (is_array($json)) {
                    $data = $json;
                }
            } catch (\Throwable $e) {
                $data = [];
            }
        }

        if (empty($data)) {
            $raw = $this->request->getRawInput();
            if (is_array($raw)) {
                $data = $raw;
            }
        }
        if (empty($data)) {
            $data = $this->request->getVar() ?? [];
        }

        try {
            $deck = $this->deckService->update((string) $nanoId, $userId, $data);
            return ApiResponse::success($deck, 'Presentasi berhasil diperbarui');
        } catch (Throwable $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * GET /api/v1/decks/trash
     */
    public function trash()
    {
        $userId = (int) ($this->request->user->id ?? session()->get('user_id') ?? 0);
        if ($userId === 0 && ENVIRONMENT === 'development') {
            $userModel = new \App\Models\UserModel();
            $admin = $userModel->where('role', 'superadmin')->first();
            if ($admin) {
                $userId = (int) $admin->id;
            }
        }

        if ($userId === 0) {
            return ApiResponse::unauthorized();
        }

        $decks = $this->deckService->getTrashByUser($userId);

        return ApiResponse::success([
            'decks' => $decks,
            'total' => count($decks),
        ]);
    }

    /**
     * DELETE /api/v1/decks/(:segment) (Soft Delete)
     */
    public function delete($nanoId = null)
    {
        $userId = (int) ($this->request->user->id ?? session()->get('user_id') ?? 0);
        if ($userId === 0 && ENVIRONMENT === 'development') {
            $userModel = new \App\Models\UserModel();
            $admin = $userModel->where('role', 'superadmin')->first();
            if ($admin) {
                $userId = (int) $admin->id;
            }
        }

        try {
            $this->deckService->delete((string) $nanoId, $userId);
            return ApiResponse::success(null, 'Presentasi berhasil dipindahkan ke tong sampah');
        } catch (Throwable $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * POST /api/v1/decks/(:segment)/restore (Restore from trash)
     */
    public function restore($nanoId = null)
    {
        $userId = (int) ($this->request->user->id ?? session()->get('user_id') ?? 0);
        if ($userId === 0 && ENVIRONMENT === 'development') {
            $userModel = new \App\Models\UserModel();
            $admin = $userModel->where('role', 'superadmin')->first();
            if ($admin) {
                $userId = (int) $admin->id;
            }
        }

        if ($userId === 0) {
            return ApiResponse::unauthorized();
        }

        try {
            $this->deckService->restore((string) $nanoId, $userId);
            return ApiResponse::success(null, 'Presentasi berhasil dipulihkan dari tong sampah');
        } catch (Throwable $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * DELETE /api/v1/decks/(:segment)/force (Permanent delete)
     */
    public function forceDelete($nanoId = null)
    {
        $userId = (int) ($this->request->user->id ?? session()->get('user_id') ?? 0);
        if ($userId === 0 && ENVIRONMENT === 'development') {
            $userModel = new \App\Models\UserModel();
            $admin = $userModel->where('role', 'superadmin')->first();
            if ($admin) {
                $userId = (int) $admin->id;
            }
        }

        if ($userId === 0) {
            return ApiResponse::unauthorized();
        }

        try {
            $this->deckService->forceDelete((string) $nanoId, $userId);
            return ApiResponse::success(null, 'Presentasi berhasil dihapus secara permanen');
        } catch (Throwable $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }
}

