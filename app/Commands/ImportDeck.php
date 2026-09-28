<?php

namespace App\Commands;

use App\Models\UserModel;
use App\Services\DeckService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Throwable;

class ImportDeck extends BaseCommand
{
    protected $group = 'LeafDeck';
    protected $name = 'deck:import';
    protected $description = 'Import file materi HTML atau paket ZIP FlipBook ke dalam LeafDeck';
    protected $usage = 'deck:import <filePath> [title] [userId]';
    protected $arguments = [
        'filePath' => 'Path absolut atau relatif ke file .html atau .zip',
        'title'    => 'Judul presentasi (opsional, auto-detect dari berkas jika kosong)',
        'userId'   => 'ID Pengguna pemilik deck (opsional, default ke superadmin)',
    ];

    public function run(array $params)
    {
        $filePath = $params[0] ?? CLI::prompt('Masukkan path file presentasi (.html atau .zip)');
        if (!file_exists($filePath)) {
            CLI::error("Berkas tidak ditemukan: {$filePath}");
            return;
        }

        $userModel = new UserModel();
        $userId = (int) ($params[2] ?? 0);
        if ($userId === 0) {
            $user = $userModel->where('role', 'superadmin')->first();
            if (!$user) {
                $user = $userModel->first();
            }
            if (!$user) {
                CLI::error("Tidak ada data pengguna di database. Jalankan 'php spark db:seed DatabaseSeeder' terlebih dahulu.");
                return;
            }
            $userId = (int) $user->id;
        }

        $title = $params[1] ?? '';
        CLI::write("Mengimpor berkas: {$filePath}...", 'yellow');

        try {
            $deckService = new DeckService();
            $deck = $deckService->create($userId, [
                'title'       => $title,
                'description' => 'Materi E-Book Interaktif Kimia Dasar oleh Mustofa, M.Pd. - SMAN 3 Kota Mojokerto',
                'is_public'   => 1,
            ], $filePath);

            CLI::write('Berhasil mengimpor presentasi!', 'green');
            CLI::write('ID: ' . $deck->id, 'white');
            CLI::write('NanoID: ' . $deck->nano_id, 'white');
            CLI::write('Judul: ' . $deck->title, 'cyan');
            CLI::write('File Path: ' . $deck->file_path, 'white');
            CLI::write('Thumbnail: ' . ($deck->thumbnail ?? '-'), 'white');
            CLI::write('Viewer URL: ' . site_url("presentation/u/0/d/{$deck->nano_id}/view"), 'green');
        } catch (Throwable $e) {
            CLI::error('Gagal mengimpor: ' . $e->getMessage());
        }
    }
}
