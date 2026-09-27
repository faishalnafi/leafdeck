<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Services\DeckService;
use CodeIgniter\Exceptions\PageNotFoundException;

class ViewerController extends BaseController
{
    protected DeckService $deckService;

    public function __construct()
    {
        $this->deckService = new DeckService();
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

        return view('pages/viewer', [
            'title'   => $deck->title . ' — LeafDeck Viewer',
            'deck'    => $deck,
            'rawUrl'  => site_url("raw-deck/{$deck->nano_id}"),
            'isOwner' => $isOwner,
        ]);
    }

    /**
     * Mengalirkan isi file HTML murni ke iframe viewer
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

        $html = $this->deckService->getHtmlContent($deck);
        if ($html === null) {
            throw PageNotFoundException::forPageNotFound('File materi di storage tidak ditemukan');
        }

        return $this->response
            ->setHeader('Content-Type', 'text/html; charset=UTF-8')
            ->setBody($html);
    }
}
