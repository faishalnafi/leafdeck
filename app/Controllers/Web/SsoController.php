<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\TokenModel;
use App\Services\AuthService;
use App\Services\SsoService;
use App\Database\Seeds\DatabaseSeeder;

class SsoController extends BaseController
{
    /**
     * Memulai alur login SSO ke portal terpusat sekolah
     */
    public function login()
    {
        $bypass = $this->request->getGet('bypass');
        $ssoBaseUrl  = env('SSO_BASE_URL');
        $ssoClientId = env('SSO_CLIENT_ID');

        // Jika dipaksa bypass atau belum ada konfigurasi SSO, gunakan bypass login
        if ($bypass === '1' || empty($ssoBaseUrl) || empty($ssoClientId) || $ssoClientId === 'LEAFDECK_CLIENT_UUID') {
            return $this->bypassLogin();
        }

        $ssoService = new SsoService();
        return redirect()->to($ssoService->getRedirectUrl());
    }

    /**
     * Memulai alur login Google terpusat melalui SSO sekolah
     */
    public function googleLogin()
    {
        $bypass = $this->request->getGet('bypass');
        $ssoBaseUrl  = env('SSO_BASE_URL');
        $ssoClientId = env('SSO_CLIENT_ID');

        if ($bypass === '1' || empty($ssoBaseUrl) || empty($ssoClientId) || $ssoClientId === 'LEAFDECK_CLIENT_UUID') {
            return $this->bypassLogin();
        }

        $ssoService = new SsoService();
        return redirect()->to($ssoService->getGoogleRedirectUrl());
    }

    /**
     * Bypass login untuk development (mendukung pilihan role: superadmin, admin, pengguna)
     */
    public function bypassLogin($roleParam = null)
    {
        $role = $this->request->getGet('role') ?? $roleParam ?? 'superadmin';

        $userModel = new UserModel();
        $user = $userModel->where('role', $role)->first();

        // Jika role yang dicari belum ada, buat atau fallback
        if (!$user) {
            if ($role === 'superadmin') {
                $seeder = new DatabaseSeeder(config('Database'));
                $seeder->run();
                $user = $userModel->where('role', 'superadmin')->first();
            } else {
                // Buat user dummy untuk role tersebut jika belum ada
                $newId = $userModel->insert([
                    'sso_user_id' => 'dev-' . $role . '-' . substr(bin2hex(random_bytes(4)), 0, 8),
                    'nomor_induk' => 'DEV-' . strtoupper($role),
                    'nama'        => ($role === 'admin' ? 'Administrator Sekolah' : 'Guru Penguji LeafDeck'),
                    'email'       => $role . '@sman3mjk.sch.id',
                    'role'        => $role,
                    'is_active'   => 1,
                ]);
                $user = $userModel->find($newId);
            }
        }

        $authService = new AuthService();
        $package = $authService->createLoginPackage($user, 86400 * 7);

        session()->set([
            'user_id' => $user->id,
            'role'    => $user->role,
            'nama'    => $user->nama,
            'token'   => $package['token'],
        ]);

        return view('pages/auth_redirect', [
            'token' => $package['token'],
            'user'  => $package['user'],
        ]);
    }


    /**
     * Menerima callback token JWT dari portal SSO
     */
    public function callback()
    {
        $token = $this->request->getGet('token');

        if (empty($token)) {
            return redirect()->to('/?error=' . urlencode('Token SSO tidak ditemukan'));
        }

        try {
            $ssoService = new SsoService();
            $decoded = $ssoService->validateJwt($token);
            $user = $ssoService->provisionUser($decoded);

            $authService = new AuthService();
            $package = $authService->createLoginPackage($user, 86400 * 7);

            session()->set([
                'user_id' => $user->id,
                'role'    => $user->role,
                'nama'    => $user->nama,
                'token'   => $package['token'],
            ]);

            return view('pages/auth_redirect', [
                'token' => $package['token'],
                'user'  => $package['user'],
            ]);
        } catch (\Throwable $e) {
            return redirect()->to('/?error=' . urlencode('Gagal memverifikasi login SSO: ' . $e->getMessage()));
        }
    }

    /**
     * Logout dari LeafDeck dan sesi SSO
     */
    public function logout()
    {
        $tokenStr = session()->get('token');
        if (!empty($tokenStr)) {
            $authService = new AuthService();
            $authService->revokeToken($tokenStr);
        }

        session()->remove(['user_id', 'role', 'nama', 'token']);
        session()->destroy();

        // Jika diminta keluar dari seluruh sistem SSO terpusat
        if ($this->request->getGet('sso') === '1') {
            $ssoService = new SsoService();
            return redirect()->to($ssoService->getLogoutUrl(site_url('/?logged_out=1')));
        }

        return redirect()->to('/?logged_out=1');
    }
}

