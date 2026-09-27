<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\DeckModel;
use App\Models\UserModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class DashboardController extends BaseController
{
    /**
     * Dashboard utama: /presentation/u/0/
     */
    public function index()
    {
        $userId = session()->get('user_id');

        // Development fallback: otomatis inisialisasi session jika belum login
        if (empty($userId) && ENVIRONMENT === 'development') {
            $userModel = new UserModel();
            $admin = $userModel->where('role', 'superadmin')->first();
            if ($admin) {
                $userId = (int) $admin->id;
                session()->set([
                    'user_id' => $admin->id,
                    'role'    => $admin->role,
                    'nama'    => $admin->nama,
                ]);
            }
        }

        $deckModel = new DeckModel();
        $decks = $userId ? $deckModel->getDecksByUser($userId) : $deckModel->where('is_public', 1)->orderBy('created_at', 'DESC')->findAll();

        return view('pages/dashboard', [
            'title'     => 'Google Slides Style Dashboard — LeafDeck',
            'protected' => 'true',
            'decks'     => $decks,
            'user'      => [
                'nama' => session()->get('nama') ?? 'Pengguna',
                'role' => session()->get('role') ?? 'pengguna',
            ],
        ]);
    }

    /**
     * Editor metadata: /presentation/u/0/d/{nanoId}/edit
     */
    public function editor($arg1 = null, $arg2 = null)
    {
        $nanoId = ($arg2 !== null) ? $arg2 : $arg1;
        $deckModel = new DeckModel();
        $deck = $deckModel->findByNanoId((string) $nanoId);

        if (!$deck) {
            throw PageNotFoundException::forPageNotFound('Materi presentasi tidak ditemukan: ' . esc($nanoId));
        }

        return view('pages/editor', [
            'title'     => 'Edit ' . $deck->title . ' — LeafDeck',
            'protected' => 'true',
            'deck'      => $deck,
        ]);
    }

    /**
     * Tong Sampah: /presentation/u/0/trash
     */
    public function trash($arg1 = null)
    {
        $userId = session()->get('user_id');

        // Development fallback: otomatis inisialisasi session jika belum login
        if (empty($userId) && ENVIRONMENT === 'development') {
            $userModel = new UserModel();
            $admin = $userModel->where('role', 'superadmin')->first();
            if ($admin) {
                $userId = (int) $admin->id;
                session()->set([
                    'user_id' => $admin->id,
                    'role'    => $admin->role,
                    'nama'    => $admin->nama,
                ]);
            }
        }

        $deckModel = new DeckModel();
        $trashDecks = $userId ? $deckModel->getTrashByUser($userId) : [];

        return view('pages/trash', [
            'title'     => 'Tong Sampah — LeafDeck',
            'protected' => 'true',
            'decks'     => $trashDecks,
            'user'      => [
                'nama' => session()->get('nama') ?? 'Pengguna',
                'role' => session()->get('role') ?? 'pengguna',
            ],
        ]);
    }
}

