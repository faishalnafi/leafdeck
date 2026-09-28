<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use Throwable;

class InstallController extends BaseController
{
    /**
     * Tampilan Wizard Instalasi Web (Easy Installer)
     */
    public function index()
    {
        $isInstalled = $this->checkIsInstalled();
        if ($isInstalled && $this->request->getGet('force') !== '1') {
            return redirect()->to('/')->with('info', 'LeafDeck sudah terpasang. Akses halaman login.');
        }

        // Cek persyaratan lingkungan sistem
        $requirements = [
            'php_version' => [
                'label'   => 'PHP Version (>= 8.1)',
                'current' => PHP_VERSION,
                'status'  => version_compare(PHP_VERSION, '8.1.0', '>='),
            ],
            'ext_intl' => [
                'label'   => 'Ekstensi PHP Intl',
                'current' => extension_loaded('intl') ? 'Tersedia' : 'Tidak Ada',
                'status'  => extension_loaded('intl'),
            ],
            'ext_mbstring' => [
                'label'   => 'Ekstensi PHP Mbstring',
                'current' => extension_loaded('mbstring') ? 'Tersedia' : 'Tidak Ada',
                'status'  => extension_loaded('mbstring'),
            ],
            'ext_curl' => [
                'label'   => 'Ekstensi PHP cURL',
                'current' => extension_loaded('curl') ? 'Tersedia' : 'Tidak Ada',
                'status'  => extension_loaded('curl'),
            ],
            'ext_mysqli' => [
                'label'   => 'Ekstensi PHP MySQLi / PDO',
                'current' => extension_loaded('mysqli') ? 'Tersedia' : 'Tidak Ada',
                'status'  => extension_loaded('mysqli') || extension_loaded('pdo_mysql'),
            ],
            'writable_dir' => [
                'label'   => 'Izin Folder writable/',
                'current' => is_writable(WRITEPATH) ? 'Dapat Ditulis (Writable)' : 'Read-only',
                'status'  => is_writable(WRITEPATH),
            ],
        ];

        $allRequirementsMet = true;
        foreach ($requirements as $req) {
            if (!$req['status']) {
                $allRequirementsMet = false;
                break;
            }
        }

        // Cek koneksi basis data default saat ini
        $dbConnected = false;
        $dbError = null;
        try {
            $db = \Config\Database::connect();
            $db->initialize();
            $dbConnected = true;
        } catch (Throwable $e) {
            $dbError = $e->getMessage();
        }

        return view('pages/install/index', [
            'title'              => 'Pemasang Otomatis LeafDeck (Easy Web Installer)',
            'requirements'       => $requirements,
            'allRequirementsMet' => $allRequirementsMet,
            'dbConnected'        => $dbConnected,
            'dbError'            => $dbError,
            'isInstalled'        => $isInstalled,
            'defaultDb'          => [
                'host'     => env('database.default.hostname') ?? 'localhost',
                'port'     => env('database.default.port') ?? '3306',
                'database' => env('database.default.database') ?? 'leafdeck_db',
                'username' => env('database.default.username') ?? 'root',
                'password' => env('database.default.password') ?? '',
            ],
            'defaultSso'         => [
                'base_url'   => env('SSO_BASE_URL') ?? 'http://localhost:8000',
                'client_id'  => env('SSO_CLIENT_ID') ?? '019fdf20-8005-7004-943e-e16ddf742b54',
                'jwt_secret' => env('SSO_JWT_SECRET') ?? 'OxABotTAP36SONTQKfEVkczynnEtSigGHUrCLZRZ2CyIpAXDfIqYe69Z19B88UrT',
            ],
        ]);
    }

    /**
     * Eksekusi Proses Instalasi Otomatis (Migrasi & Seeding)
     */
    public function run()
    {
        $raw = (string) $this->request->getBody();
        $input = !empty($raw) ? (json_decode($raw, true) ?? []) : [];
        if (empty($input)) {
            $input = $this->request->getPost();
        }

        $host     = trim($input['db_host'] ?? 'localhost');
        $port     = trim($input['db_port'] ?? '3306');
        $database = trim($input['db_name'] ?? 'leafdeck_db');
        $username = trim($input['db_user'] ?? 'root');
        $password = (string) ($input['db_pass'] ?? '');

        $schoolName = trim($input['school_name'] ?? 'SMAN 3 MJK');
        $ssoBaseUrl = trim($input['sso_base_url'] ?? 'http://localhost:8000');
        $ssoClientId = trim($input['sso_client_id'] ?? '019fdf20-8005-7004-943e-e16ddf742b54');

        try {
            // 1. Buat database jika belum ada menggunakan PDO murni
            $pdo = new \PDO("mysql:host={$host};port={$port};charset=utf8mb4", $username, $password, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            ]);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            // 2. Tulis konfigurasi baru ke berkas .env
            $this->updateEnvFile([
                'database.default.hostname' => $host,
                'database.default.port'     => $port,
                'database.default.database' => $database,
                'database.default.username' => $username,
                'database.default.password' => $password,
                'leafdeck.schoolName'       => "'{$schoolName}'",
                'SSO_BASE_URL'              => $ssoBaseUrl,
                'SSO_CLIENT_ID'             => $ssoClientId,
            ]);

            // 3. Jalankan migrasi CodeIgniter 4
            $migrate = \Config\Services::migrations();
            $migrate->setNamespace('App');
            $migrate->latest();

            // 4. Jalankan seeder bawaan (Superadmin default & materi contoh)
            $seeder = \Config\Database::seeder();
            $seeder->call('DatabaseSeeder');

            return $this->response->setJSON([
                'status'  => true,
                'message' => 'Instalasi LeafDeck berhasil diselesaikan! Database dan akun awal telah siap.',
            ]);
        } catch (Throwable $e) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Gagal menjalankan instalasi: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    /**
     * Cek apakah sistem sudah terpasang
     */
    protected function checkIsInstalled(): bool
    {
        try {
            $userModel = new UserModel();
            return $userModel->countAllResults() > 0;
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Memperbarui entri pada berkas .env
     */
    protected function updateEnvFile(array $data): bool
    {
        $envPath = ROOTPATH . '.env';
        if (!file_exists($envPath)) {
            $templatePath = ROOTPATH . 'env';
            if (file_exists($templatePath)) {
                copy($templatePath, $envPath);
            } else {
                file_put_contents($envPath, '');
            }
        }

        $envContent = file_get_contents($envPath);
        foreach ($data as $key => $value) {
            $key   = trim($key);
            $value = trim((string)$value);
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;

            if (preg_match("/^{$key}\s*=.*/m", $envContent)) {
                $envContent = preg_replace("/^{$key}\s*=.*/m", "{$key} = {$value}", $envContent);
            } else {
                $envContent .= "\n{$key} = {$value}";
            }
        }

        return file_put_contents($envPath, $envContent) !== false;
    }
}
