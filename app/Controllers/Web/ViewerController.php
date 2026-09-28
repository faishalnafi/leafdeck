<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Services\DeckService;
use App\Services\FileService;
use CodeIgniter\Exceptions\PageNotFoundException;

class ViewerController extends BaseController
{
    protected DeckService $deckService;
    protected FileService $fileService;

    public function __construct()
    {
        $this->deckService = new DeckService();
        $this->fileService = new FileService();
    }

    /**
     * Halaman Viewer Presentasi E-Book: /presentation/u/0/d/{nanoId}/view
     */
    public function show($arg1 = null, $arg2 = null)
    {
        $nanoId = ($arg2 !== null) ? $arg2 : $arg1;
        $deck = $this->deckService->findByNanoId((string) $nanoId);
        if (!$deck) {
            throw PageNotFoundException::forPageNotFound('Materi presentasi tidak ditemukan: ' . esc($nanoId));
        }

        // Cek hak akses jika materi privat
        $currentUserId = session()->get('user_id');
        $currentUserRole = session()->get('role');
        $isOwner = $currentUserId && ((int) $deck->user_id === (int) $currentUserId);
        $isAdmin = in_array($currentUserRole, ['superadmin', 'admin'], true);

        if ((int) $deck->is_public !== 1 && !$isOwner && !$isAdmin) {
            return view('errors/html/private_deck', [
                'title' => 'Akses Terbatas — LeafDeck',
                'deck'  => $deck,
            ]);
        }

        // Hitung view jika bukan pemilik yang membuka
        if (!$isOwner) {
            $this->deckService->incrementViews((string) $nanoId);
        }

        // Deteksi tipe berkas
        $ext = strtolower(pathinfo($deck->file_path, PATHINFO_EXTENSION));
        $fileType = match ($ext) {
            'pdf'         => 'pdf',
            'pptx'        => 'pptx',
            'zip'         => 'zip',
            'html', 'htm' => 'html',
            default       => 'html',
        };

        if (str_contains($deck->file_path, "uploads/decks/{$deck->nano_id}/")) {
            $fileType = 'zip';
        }

        // Tentukan URL muatan: jika paket bundle (uploads/decks/{nanoId}/...), arahkan ke URL storage (lokal atau CDN)
        $rawUrl = site_url("raw-deck/{$deck->nano_id}");
        if (str_contains($deck->file_path, "uploads/decks/{$deck->nano_id}/")) {
            $rawUrl = $this->fileService->getUrl($deck->file_path);
        }

        $downloadUrl = site_url("download-deck/{$deck->nano_id}");

        return view('pages/viewer', [
            'title'       => $deck->title . ' — LeafDeck Viewer',
            'deck'        => $deck,
            'rawUrl'      => $rawUrl,
            'downloadUrl' => $downloadUrl,
            'fileType'    => $fileType,
            'isOwner'     => $isOwner,
        ]);
    }

    /**
     * Mengalirkan isi file murni (HTML, PDF, PPTX) ke iframe viewer
     */
    public function rawHtml($nanoId = null)
    {
        $deck = $this->deckService->findByNanoId((string) $nanoId);
        if (!$deck) {
            throw PageNotFoundException::forPageNotFound('File presentasi tidak ditemukan');
        }

        // Cek hak akses
        $currentUserId = session()->get('user_id');
        $currentUserRole = session()->get('role');
        $isOwner = $currentUserId && ((int) $deck->user_id === (int) $currentUserId);
        $isAdmin = in_array($currentUserRole, ['superadmin', 'admin'], true);

        if ((int) $deck->is_public !== 1 && !$isOwner && !$isAdmin) {
            return $this->response->setStatusCode(403)->setBody('Akses ditolak. Materi presentasi ini disetel sebagai privat.');
        }

        if (str_contains($deck->file_path, "uploads/decks/{$deck->nano_id}/")) {
            return redirect()->to($this->fileService->getUrl($deck->file_path));
        }

        $content = $this->deckService->getHtmlContent($deck);
        if ($content === null) {
            throw PageNotFoundException::forPageNotFound('File materi di storage tidak ditemukan');
        }

        $ext = strtolower(pathinfo($deck->file_path, PATHINFO_EXTENSION));
        $cleanTitle = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $deck->title);

        if ($ext === 'pdf') {
            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'inline; filename="' . $cleanTitle . '.pdf"')
                ->setBody($content);
        }

        if ($ext === 'pptx') {
            return $this->response
                ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.presentationml.presentation')
                ->setHeader('Content-Disposition', 'inline; filename="' . $cleanTitle . '.pptx"')
                ->setBody($content);
        }

        return $this->response
            ->setHeader('Content-Type', 'text/html; charset=UTF-8')
            ->setBody($content);
    }

    /**
     * Mengunduh berkas materi presentasi asli
     */
    public function download($nanoId = null)
    {
        $deck = $this->deckService->findByNanoId((string) $nanoId);
        if (!$deck) {
            throw PageNotFoundException::forPageNotFound('File presentasi tidak ditemukan');
        }

        // Cek hak akses
        $currentUserId = session()->get('user_id');
        $currentUserRole = session()->get('role');
        $isOwner = $currentUserId && ((int) $deck->user_id === (int) $currentUserId);
        $isAdmin = in_array($currentUserRole, ['superadmin', 'admin'], true);

        if ((int) $deck->is_public !== 1 && !$isOwner && !$isAdmin) {
            return $this->response->setStatusCode(403)->setBody('Akses ditolak. Materi ini disetel privat.');
        }

        $ext = strtolower(pathinfo($deck->file_path, PATHINFO_EXTENSION));
        if (empty($ext) || str_contains($deck->file_path, "uploads/decks/{$deck->nano_id}/")) {
            $ext = 'zip';
        }

        $cleanTitle = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $deck->title) . '.' . $ext;
        $content = $this->deckService->getHtmlContent($deck);

        if ($content === null) {
            // Jika file fisik ada di storage atau direct URL
            $fullPath = $this->fileService->getAbsolutePath($deck->file_path);
            if (file_exists($fullPath)) {
                return $this->response->download($fullPath, null)->setFileName($cleanTitle);
            }
            throw PageNotFoundException::forPageNotFound('Berkas materi di storage tidak ditemukan.');
        }

        $mime = $this->fileService->detectMimeType($deck->file_path);
        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'attachment; filename="' . $cleanTitle . '"')
            ->setBody($content);
    }
}
