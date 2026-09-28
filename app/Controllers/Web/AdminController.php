<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\DeckModel;
use App\Models\UserModel;
use App\Services\FileService;

class AdminController extends BaseController
{
    protected UserModel $userModel;
    protected DeckModel $deckModel;
    protected FileService $fileService;

    public function __construct()
    {
        $this->userModel   = new UserModel();
        $this->deckModel   = new DeckModel();
        $this->fileService = new FileService();
    }

    /**
     * Verifikasi otorisasi admin
     */
    protected function checkAdminAuth()
    {
        $userId = session()->get('user_id');
        $role   = session()->get('role');

        // Development fallback: inisialisasi superadmin jika belum login
        if (empty($userId) && ENVIRONMENT === 'development') {
            $admin = $this->userModel->where('role', 'superadmin')->first();
            if ($admin) {
                $userId = (int) $admin->id;
                $role   = $admin->role;
                session()->set([
                    'user_id' => $admin->id,
                    'role'    => $admin->role,
                    'nama'    => $admin->nama,
                ]);
            }
        }

        if (empty($userId) || !in_array($role, ['superadmin', 'admin'], true)) {
            return false;
        }

        return true;
    }

    /**
     * Dashboard Utama Admin: /admin atau /admin/dashboard
     */
    public function index()
    {
        if (!$this->checkAdminAuth()) {
            return redirect()->to('/presentation/u/0/')->with('error', 'Akses ditolak: Halaman ini khusus Administrator.');
        }

        $totalUsers   = $this->userModel->countAllResults();
        $activeUsers  = $this->userModel->countActiveUsers();
        $totalDecks   = $this->deckModel->where('deleted_at', null)->countAllResults();
        $trashDecks   = $this->deckModel->onlyDeleted()->countAllResults();
        $totalViews   = $this->deckModel->getTotalViews();
        $storageBytes = $this->fileService->getTotalStorageBytes();

        $stats = [
            'total_users'       => $totalUsers,
            'active_users'      => $activeUsers,
            'total_decks'       => $totalDecks,
            'trash_decks'       => $trashDecks,
            'total_views'       => $totalViews,
            'storage_bytes'     => $storageBytes,
            'storage_formatted' => FileService::formatBytes($storageBytes),
        ];

        $users = $this->userModel->getAllUsers(50);
        $decks = $this->deckModel->getAllDecksWithAuthor(50);

        $ssoConfig = [
            'sso_base_url'         => env('SSO_BASE_URL') ?? 'http://localhost:8000',
            'sso_client_id'        => env('SSO_CLIENT_ID') ?? '',
            'sso_jwt_secret'       => env('SSO_JWT_SECRET') ?? '',
            'sso_api_key'          => env('SSO_API_KEY') ?? '',
            'sso_callback_url'     => env('SSO_CALLBACK_URL') ?? site_url('sso/callback'),
            'google_client_id'     => env('GOOGLE_CLIENT_ID') ?? '',
            'google_client_secret' => env('GOOGLE_CLIENT_SECRET') ?? '',
            'google_login_enabled' => filter_var(env('GOOGLE_LOGIN_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
        ];

        $storageConfig = [
            'driver'                => env('STORAGE_DRIVER') ?: 'local',

            // AWS S3
            's3_key'                => env('S3_KEY') ?? env('AWS_ACCESS_KEY_ID') ?? '',
            's3_secret'             => env('S3_SECRET') ?? env('AWS_SECRET_ACCESS_KEY') ?? '',
            's3_region'             => env('S3_REGION') ?? env('AWS_DEFAULT_REGION') ?? 'ap-southeast-3',
            's3_bucket'             => env('S3_BUCKET') ?? '',
            's3_endpoint'           => env('S3_ENDPOINT') ?? '',
            's3_public_url'         => env('S3_PUBLIC_URL') ?? '',
            's3_use_path_style'     => filter_var(env('S3_USE_PATH_STYLE_ENDPOINT', false), FILTER_VALIDATE_BOOLEAN),

            // Cloudflare R2
            'r2_account_id'         => env('R2_ACCOUNT_ID') ?? '',
            'r2_access_key_id'      => env('R2_ACCESS_KEY_ID') ?? '',
            'r2_secret_access_key'  => env('R2_SECRET_ACCESS_KEY') ?? '',
            'r2_bucket'             => env('R2_BUCKET') ?? '',
            'r2_public_url'         => env('R2_PUBLIC_URL') ?? '',

            // Google Cloud Storage
            'gcs_project_id'        => env('GCS_PROJECT_ID') ?? '',
            'gcs_access_key_id'     => env('GCS_ACCESS_KEY_ID') ?? '',
            'gcs_secret_access_key' => env('GCS_SECRET_ACCESS_KEY') ?? '',
            'gcs_bucket'            => env('GCS_BUCKET') ?? '',
            'gcs_endpoint'          => env('GCS_ENDPOINT') ?? 'https://storage.googleapis.com',
            'gcs_public_url'        => env('GCS_PUBLIC_URL') ?? '',

            // Generic / Custom S3-Compatible
            'storage_endpoint'      => env('STORAGE_ENDPOINT') ?? '',
            'storage_access_key'    => env('STORAGE_ACCESS_KEY') ?? '',
            'storage_secret_key'    => env('STORAGE_SECRET_KEY') ?? '',
            'storage_region'        => env('STORAGE_REGION') ?? 'us-east-1',
            'storage_bucket'        => env('STORAGE_BUCKET') ?? '',
            'storage_public_url'    => env('STORAGE_PUBLIC_URL') ?? '',
            'storage_use_path_style'=> filter_var(env('STORAGE_USE_PATH_STYLE', true), FILTER_VALIDATE_BOOLEAN),
        ];

        return view('pages/admin/dashboard', [
            'title'         => 'Panel Administrasi — LeafDeck',
            'protected'     => 'true',
            'stats'         => $stats,
            'users'         => $users,
            'decks'         => $decks,
            'ssoConfig'     => $ssoConfig,
            'storageConfig' => $storageConfig,
            'currentUser'   => [
                'nama' => session()->get('nama') ?? 'Administrator',
                'role' => session()->get('role') ?? 'superadmin',
            ],
        ]);
    }

    /**
     * Simpan Perubahan Konfigurasi SSO & Google OAuth (Khusus Superadmin)
     */
    public function saveSsoSettings()
    {
        $role = session()->get('role');
        if ($role !== 'superadmin') {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Akses ditolak: Hanya Super Administrator yang berhak mengubah konfigurasi SSO.'
            ])->setStatusCode(403);
        }

        $raw = (string) $this->request->getBody();
        $input = !empty($raw) ? (json_decode($raw, true) ?? []) : [];
        if (empty($input)) {
            $input = $this->request->getPost();
        }

        $fields = [
            'SSO_BASE_URL'         => trim($input['sso_base_url'] ?? ''),
            'SSO_CLIENT_ID'        => trim($input['sso_client_id'] ?? ''),
            'SSO_JWT_SECRET'       => trim($input['sso_jwt_secret'] ?? ''),
            'SSO_API_KEY'          => trim($input['sso_api_key'] ?? ''),
            'SSO_CALLBACK_URL'     => trim($input['sso_callback_url'] ?? ''),
            'GOOGLE_CLIENT_ID'     => trim($input['google_client_id'] ?? ''),
            'GOOGLE_CLIENT_SECRET' => trim($input['google_client_secret'] ?? ''),
            'GOOGLE_LOGIN_ENABLED' => (!empty($input['google_login_enabled']) && in_array($input['google_login_enabled'], ['1', 'true', true], true)) ? 'true' : 'false',
        ];

        $success = $this->updateEnvFile($fields);

        if ($success) {
            return $this->response->setJSON([
                'status'  => true,
                'message' => 'Konfigurasi integrasi SSO & Google OAuth berhasil disimpan!'
            ]);
        }

        return $this->response->setJSON([
            'status'  => false,
            'message' => 'Gagal memperbarui berkas .env sistem.'
        ])->setStatusCode(500);
    }

    /**
     * Uji Koneksi Live ke Gateway SSO Sekolah (Khusus Superadmin)
     */
    public function testSsoConnection()
    {
        $role = session()->get('role');
        if ($role !== 'superadmin') {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Akses ditolak: Hanya Super Administrator yang berhak menguji koneksi SSO.'
            ])->setStatusCode(403);
        }

        $raw = (string) $this->request->getBody();
        $input = !empty($raw) ? (json_decode($raw, true) ?? []) : [];
        if (empty($input)) {
            $input = $this->request->getPost();
        }

        $baseUrl = rtrim($input['sso_base_url'] ?? env('SSO_BASE_URL') ?? 'http://localhost:8000', '/');
        $apiKey  = trim($input['sso_api_key'] ?? env('SSO_API_KEY') ?? '');

        $testUrl = $baseUrl . '/api/v1/test';

        $startTime = microtime(true);
        $ch = curl_init($testUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-API-Key: ' . $apiKey,
            'Accept: application/json',
        ]);
        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        $latencyMs = round((microtime(true) - $startTime) * 1000);

        if ($httpCode >= 200 && $httpCode < 300) {
            $parsed = json_decode($response, true);
            return $this->response->setJSON([
                'status'     => true,
                'http_code'  => $httpCode,
                'latency_ms' => $latencyMs,
                'message'    => 'Terhubung sukses ke SSO Kredensia SMAN 3 MJK (' . $latencyMs . ' ms)',
                'data'       => $parsed
            ]);
        }

        if ($httpCode === 0) {
            return $this->response->setJSON([
                'status'     => false,
                'http_code'  => 0,
                'latency_ms' => $latencyMs,
                'message'    => 'Tidak dapat terhubung ke ' . $baseUrl . ' (' . ($curlError ?: 'Connection refused') . '). Pastikan server SSO di port 8000 sedang aktif.'
            ]);
        }

        return $this->response->setJSON([
            'status'     => false,
            'http_code'  => $httpCode,
            'latency_ms' => $latencyMs,
            'message'    => 'Server SSO merespons kode HTTP ' . $httpCode . ' (Periksa kembali Kunci API Anda).'
        ]);
    }

    /**
     * Simpan Perubahan Konfigurasi Object Storage (Khusus Superadmin)
     */
    public function saveStorageSettings()
    {
        $role = session()->get('role');
        if ($role !== 'superadmin') {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Akses ditolak: Hanya Super Administrator yang berhak mengubah konfigurasi storage.'
            ])->setStatusCode(403);
        }

        $raw = (string) $this->request->getBody();
        $input = !empty($raw) ? (json_decode($raw, true) ?? []) : [];
        if (empty($input)) {
            $input = $this->request->getPost();
        }

        $fields = [
            'STORAGE_DRIVER'             => trim($input['storage_driver'] ?? 'local'),

            // AWS S3
            'S3_KEY'                     => trim($input['s3_key'] ?? ''),
            'S3_SECRET'                  => trim($input['s3_secret'] ?? ''),
            'S3_REGION'                  => trim($input['s3_region'] ?? 'ap-southeast-3'),
            'S3_BUCKET'                  => trim($input['s3_bucket'] ?? ''),
            'S3_ENDPOINT'                => trim($input['s3_endpoint'] ?? ''),
            'S3_PUBLIC_URL'              => trim($input['s3_public_url'] ?? ''),
            'S3_USE_PATH_STYLE_ENDPOINT' => (!empty($input['s3_use_path_style']) && in_array($input['s3_use_path_style'], ['1', 'true', true], true)) ? 'true' : 'false',

            // Cloudflare R2
            'R2_ACCOUNT_ID'              => trim($input['r2_account_id'] ?? ''),
            'R2_ACCESS_KEY_ID'           => trim($input['r2_access_key_id'] ?? ''),
            'R2_SECRET_ACCESS_KEY'       => trim($input['r2_secret_access_key'] ?? ''),
            'R2_BUCKET'                  => trim($input['r2_bucket'] ?? ''),
            'R2_PUBLIC_URL'              => trim($input['r2_public_url'] ?? ''),

            // Google Cloud Storage
            'GCS_PROJECT_ID'             => trim($input['gcs_project_id'] ?? ''),
            'GCS_ACCESS_KEY_ID'          => trim($input['gcs_access_key_id'] ?? ''),
            'GCS_SECRET_ACCESS_KEY'      => trim($input['gcs_secret_access_key'] ?? ''),
            'GCS_BUCKET'                 => trim($input['gcs_bucket'] ?? ''),
            'GCS_ENDPOINT'               => trim($input['gcs_endpoint'] ?? 'https://storage.googleapis.com'),
            'GCS_PUBLIC_URL'             => trim($input['gcs_public_url'] ?? ''),

            // Generic / Custom S3-Compatible
            'STORAGE_ENDPOINT'           => trim($input['storage_endpoint'] ?? ''),
            'STORAGE_ACCESS_KEY'         => trim($input['storage_access_key'] ?? ''),
            'STORAGE_SECRET_KEY'         => trim($input['storage_secret_key'] ?? ''),
            'STORAGE_REGION'             => trim($input['storage_region'] ?? 'us-east-1'),
            'STORAGE_BUCKET'             => trim($input['storage_bucket'] ?? ''),
            'STORAGE_PUBLIC_URL'         => trim($input['storage_public_url'] ?? ''),
            'STORAGE_USE_PATH_STYLE'     => (!empty($input['storage_use_path_style']) && in_array($input['storage_use_path_style'], ['1', 'true', true], true)) ? 'true' : 'false',
        ];

        $success = $this->updateEnvFile($fields);
        \App\Services\Storage\StorageManager::flushInstances();

        if ($success) {
            return $this->response->setJSON([
                'status'  => true,
                'message' => 'Konfigurasi Object Storage berhasil disimpan ke sistem!'
            ]);
        }

        return $this->response->setJSON([
            'status'  => false,
            'message' => 'Gagal memperbarui berkas .env sistem.'
        ])->setStatusCode(500);
    }

    /**
     * Uji Koneksi Live ke Object Storage (Khusus Superadmin)
     */
    public function testStorageConnection()
    {
        $role = session()->get('role');
        if ($role !== 'superadmin') {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Akses ditolak: Hanya Super Administrator yang berhak menguji koneksi storage.'
            ])->setStatusCode(403);
        }

        $raw = (string) $this->request->getBody();
        $input = !empty($raw) ? (json_decode($raw, true) ?? []) : [];
        if (empty($input)) {
            $input = $this->request->getPost();
        }

        $driver = strtolower(trim($input['storage_driver'] ?? 'local'));
        $testResult = \App\Services\Storage\StorageManager::testDriver($driver, $input);

        return $this->response->setJSON($testResult);
    }

    /**
     * Memperbarui entri pada berkas .env secara aman
     */
    protected function updateEnvFile(array $data): bool
    {
        $envPath = ROOTPATH . '.env';
        if (!file_exists($envPath)) {
            return false;
        }

        $envContent = file_get_contents($envPath);
        foreach ($data as $key => $value) {
            $key   = trim($key);
            $value = trim((string)$value);
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;

            // Cari jika sudah ada entri KEY = VALUE atau KEY=VALUE
            if (preg_match("/^{$key}\s*=.*/m", $envContent)) {
                $envContent = preg_replace("/^{$key}\s*=.*/m", "{$key} = {$value}", $envContent);
            } else {
                $envContent .= "\n{$key} = {$value}";
            }
        }

        return file_put_contents($envPath, $envContent) !== false;
    }
}
