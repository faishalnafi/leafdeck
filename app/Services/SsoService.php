<?php

namespace App\Services;

use App\Models\UserModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use RuntimeException;
use Throwable;

class SsoService
{
    protected UserModel $userModel;
    protected string $ssoBaseUrl;
    protected string $ssoClientId;
    protected string $ssoJwtSecret;

    public function __construct(?UserModel $userModel = null)
    {
        $this->userModel    = $userModel ?? new UserModel();
        $this->ssoBaseUrl   = rtrim(env('SSO_BASE_URL') ?? '', '/');
        $this->ssoClientId  = env('SSO_CLIENT_ID') ?? 'LEAFDECK_CLIENT_UUID';
        $this->ssoJwtSecret = env('SSO_JWT_SECRET') ?? 'sso_secret_key_default_32_characters';
    }

    /**
     * Menghasilkan URL pengalihan ke portal SSO sekolah
     */
    public function getRedirectUrl(?string $callbackUrl = null): string
    {
        $callback = $callbackUrl ?? site_url('sso/callback');

        return $this->ssoBaseUrl . '/otentikasi?' . http_build_query([
            'client_id'    => $this->ssoClientId,
            'redirect_uri' => $callback,
        ]);
    }

    /**
     * Menghasilkan URL pengalihan ke login Google terpusat via SSO sekolah
     */
    public function getGoogleRedirectUrl(?string $callbackUrl = null): string
    {
        $callback = $callbackUrl ?? site_url('sso/callback');

        return $this->ssoBaseUrl . '/auth/google?' . http_build_query([
            'client_id'    => $this->ssoClientId,
            'redirect_uri' => $callback,
        ]);
    }

    /**
     * Menghasilkan URL logout terpusat SSO sekolah
     */
    public function getLogoutUrl(?string $redirectUri = null): string
    {
        $redirect = $redirectUri ?? site_url('/');

        return $this->ssoBaseUrl . '/otentikasi/keluar?' . http_build_query([
            'redirect_uri' => $redirect,
        ]);
    }

    /**
     * Memvalidasi dan mendecode token JWT dari SSO sekolah
     *
     * @param string $jwtToken String token JWT
     * @return object Payload JWT terverifikasi
     */
    public function validateJwt(string $jwtToken): object
    {
        try {
            // Algoritma HS256 sesuai spesifikasi SSO sekolah
            $decoded = JWT::decode($jwtToken, new Key($this->ssoJwtSecret, 'HS256'));

            if (empty($decoded->user_id)) {
                throw new RuntimeException('Payload JWT tidak memiliki klaim user_id yang valid');
            }

            return $decoded;
        } catch (Throwable $e) {
            throw new RuntimeException('Validasi token SSO gagal: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Memetakan peran dari SSO ke peran lokal LeafDeck
     * (superadmin, admin, pengguna)
     */
    public function mapRole(array $ssoRoles): string
    {
        $lowerRoles = array_map(function ($r) {
            return strtolower(trim((string)$r));
        }, $ssoRoles);

        if (in_array('super admin', $lowerRoles, true) || in_array('superadmin', $lowerRoles, true)) {
            return 'superadmin';
        }

        if (in_array('admin', $lowerRoles, true) || in_array('operator', $lowerRoles, true)) {
            return 'admin';
        }

        // Guru, Wali Kelas, BK, GDS, Siswa -> pengguna
        return 'pengguna';
    }

    /**
     * JIT (Just-In-Time) User Provisioning
     * Mencari atau membuat akun user baru otomatis dari data JWT SSO
     *
     * @param object $payload Data dari JWT payload
     * @return object Objek UserModel dari user yang berhasil diprovisi
     */
    public function provisionUser(object $payload): object
    {
        $ssoUserId  = (string) ($payload->user_id ?? '');
        $nomorInduk = !empty($payload->nomor_induk) ? (string) $payload->nomor_induk : null;
        $nama       = !empty($payload->nama) ? trim((string) $payload->nama) : 'Pengguna LeafDeck';
        $email      = !empty($payload->email) ? trim((string) $payload->email) : null;
        $ssoRoles   = !empty($payload->roles) && is_array($payload->roles) ? $payload->roles : [];

        $role = $this->mapRole($ssoRoles);
        $avatar = !empty($payload->avatar) ? trim((string) $payload->avatar) : (!empty($payload->google_avatar) ? trim((string) $payload->google_avatar) : null);

        // Cari user yang sudah ada berdasarkan sso_user_id terlebih dahulu
        $existing = null;
        if (!empty($ssoUserId)) {
            $existing = $this->userModel->findBySsoId($ssoUserId);
        }

        // Fallback cari berdasarkan nomor_induk
        if (!$existing && !empty($nomorInduk)) {
            $existing = $this->userModel->findByNomorInduk($nomorInduk);
        }

        if ($existing) {
            // Sinkronisasi data terkini jika ada perubahan
            $updateData = [];
            if ($existing->nama !== $nama) {
                $updateData['nama'] = $nama;
            }
            if ($email && $existing->email !== $email) {
                $updateData['email'] = $email;
            }
            if ($nomorInduk && $existing->nomor_induk !== $nomorInduk) {
                $updateData['nomor_induk'] = $nomorInduk;
            }
            if (empty($existing->sso_user_id) && $ssoUserId) {
                $updateData['sso_user_id'] = $ssoUserId;
            }
            if (!empty($ssoRoles) && $existing->role !== $role) {
                $updateData['role'] = $role;
            }
            if ($avatar && $existing->avatar !== $avatar) {
                $updateData['avatar'] = $avatar;
            }

            if (!empty($updateData)) {
                $this->userModel->update($existing->id, $updateData);
                return $this->userModel->find($existing->id);
            }

            return $existing;
        }

        // Pengguna baru -> Buat akun otomatis
        $newUserId = $this->userModel->insert([
            'sso_user_id' => $ssoUserId ?: null,
            'nomor_induk' => $nomorInduk,
            'nama'        => $nama,
            'email'       => $email,
            'role'        => $role,
            'avatar'      => $avatar,
            'is_active'   => 1,
        ]);

        if (!$newUserId) {
            $errors = implode(', ', $this->userModel->errors());
            throw new RuntimeException('Gagal membuat profil pengguna baru: ' . $errors);
        }

        return $this->userModel->find($newUserId);
    }
}
