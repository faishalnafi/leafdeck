<?php

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\UserModel;
use App\Services\FileService;
use App\Services\DeckService;
use App\Services\AuthService;
use App\Services\SsoService;
use Firebase\JWT\JWT;

/**
 * @internal
 */
final class ServicesTest extends CIUnitTestCase
{
    public function testFileServiceLifecycle(): void
    {
        $fileService = new FileService();
        $sampleNanoId = 'test_file_' . substr(bin2hex(random_bytes(5)), 0, 8);
        $sampleHtml = '<!DOCTYPE html><html><body><h1>Slide Test</h1></body></html>';

        $relPath = $fileService->saveHtml($sampleHtml, $sampleNanoId);
        $this->assertSame('uploads/decks/' . $sampleNanoId . '.html', $relPath);

        $readContent = $fileService->readHtml($relPath);
        $this->assertSame($sampleHtml, $readContent);

        $deleted = $fileService->delete($relPath);
        $this->assertTrue($deleted);
        $this->assertNull($fileService->readHtml($relPath));
    }

    public function testDeckServiceCreateAndLifecycle(): void
    {
        $userModel = new UserModel();
        $admin = $userModel->where('role', 'superadmin')->first();

        $deckService = new DeckService();
        $sampleHtml = '<html><body><h2>Presentasi Interaktif</h2></body></html>';

        $deck = $deckService->create(
            (int) $admin->id,
            [
                'title'       => 'Materi Kimia Molekul',
                'description' => 'Bab ikatan kimia molekul',
                'is_public'   => 1,
            ],
            $sampleHtml
        );

        $this->assertNotNull($deck);
        $this->assertSame(21, strlen($deck->nano_id));
        $this->assertSame('Materi Kimia Molekul', $deck->title);

        $found = $deckService->findByNanoId($deck->nano_id);
        $this->assertNotNull($found);
        $this->assertSame($deck->id, $found->id);

        // Test update
        $updated = $deckService->update($deck->nano_id, (int) $admin->id, [
            'title' => 'Materi Kimia Molekul Terupdate',
        ]);
        $this->assertSame('Materi Kimia Molekul Terupdate', $updated->title);

        // Test increment views
        $deckService->incrementViews($deck->nano_id);
        $viewed = $deckService->findByNanoId($deck->nano_id);
        $this->assertEquals(1, $viewed->view_count);

        // Test soft delete
        $deleted = $deckService->delete($deck->nano_id, (int) $admin->id);
        $this->assertTrue($deleted);
        $this->assertNull($deckService->findByNanoId($deck->nano_id));

        // Test appears in trash
        $trash = $deckService->getTrashByUser((int) $admin->id);
        $trashNanoIds = array_map(fn($d) => $d->nano_id, $trash);
        $this->assertContains($deck->nano_id, $trashNanoIds);

        // Test restore
        $restored = $deckService->restore($deck->nano_id, (int) $admin->id);
        $this->assertTrue($restored);
        $restoredDeck = $deckService->findByNanoId($deck->nano_id);
        $this->assertNotNull($restoredDeck);
        $this->assertSame($deck->nano_id, $restoredDeck->nano_id);

        // Test force delete (permanent deletion)
        $deckService->delete($deck->nano_id, (int) $admin->id);
        $forceDeleted = $deckService->forceDelete($deck->nano_id, (int) $admin->id);
        $this->assertTrue($forceDeleted);
        $this->assertNull($deckService->findByNanoId($deck->nano_id));
        $trashAfter = $deckService->getTrashByUser((int) $admin->id);
        $trashNanoIdsAfter = array_map(fn($d) => $d->nano_id, $trashAfter);
        $this->assertNotContains($deck->nano_id, $trashNanoIdsAfter);
    }

    public function testAuthServiceTokenLifecycle(): void
    {
        $userModel = new UserModel();
        $admin = $userModel->where('role', 'superadmin')->first();

        $authService = new AuthService();
        $package = $authService->createLoginPackage($admin, 3600);

        $this->assertArrayHasKey('token', $package);
        $this->assertArrayHasKey('user', $package);
        $this->assertSame(64, strlen($package['token']));
        $this->assertSame($admin->nama, $package['user']['nama']);

        $validated = $authService->validateToken($package['token']);
        $this->assertNotNull($validated);
        $this->assertSame($admin->id, $validated->user_id);

        $authService->revokeToken($package['token']);
        $this->assertNull($authService->validateToken($package['token']));
    }

    public function testSsoServiceValidationAndProvisioning(): void
    {
        $ssoService = new SsoService();
        $secret = env('SSO_JWT_SECRET') ?? 'sso_secret_key_default_32_characters';

        $uniqueSsoId = 'sso-' . bin2hex(random_bytes(8));
        $uniqueNip   = 'GURU-' . bin2hex(random_bytes(4));

        $payload = [
            'user_id'     => $uniqueSsoId,
            'nomor_induk' => $uniqueNip,
            'nama'        => 'Ibu Siti Aminah',
            'email'       => 'siti.aminah@sman3mjk.sch.id',
            'roles'       => ['Guru', 'Pengguna'],
            'exp'         => time() + 300,
        ];

        $jwt = JWT::encode($payload, $secret, 'HS256');

        // Test validate
        $decoded = $ssoService->validateJwt($jwt);
        $this->assertSame($uniqueSsoId, $decoded->user_id);

        // Test JIT Provisioning (New User)
        $user = $ssoService->provisionUser($decoded);
        $this->assertNotNull($user);
        $this->assertSame($uniqueSsoId, $user->sso_user_id);
        $this->assertSame('Ibu Siti Aminah', $user->nama);
        $this->assertSame('pengguna', $user->role);

        // Test JIT Provisioning (Update Existing User with new name)
        $decoded->nama = 'Ibu Siti Aminah, M.Pd.';
        // Test redirect URLs
        $this->assertStringContainsString('http://localhost:8000/otentikasi', $ssoService->getRedirectUrl());
        $this->assertStringContainsString('http://localhost:8000/auth/google', $ssoService->getGoogleRedirectUrl());
        $this->assertStringContainsString('http://localhost:8000/otentikasi/keluar', $ssoService->getLogoutUrl());

        // Test Super Admin role mapping
        $this->assertSame('superadmin', $ssoService->mapRole(['Super Admin']));
        $this->assertSame('superadmin', $ssoService->mapRole(['superadmin']));
        $this->assertSame('admin', $ssoService->mapRole(['Admin']));
        $this->assertSame('pengguna', $ssoService->mapRole(['Guru', 'Wali Kelas']));
        $this->assertSame('pengguna', $ssoService->mapRole(['Siswa']));

        // Test Google Login Enabled toggle config
        putenv('GOOGLE_LOGIN_ENABLED=false');
        $_ENV['GOOGLE_LOGIN_ENABLED'] = 'false';
        $this->assertFalse(filter_var(env('GOOGLE_LOGIN_ENABLED', false), FILTER_VALIDATE_BOOLEAN));

        putenv('GOOGLE_LOGIN_ENABLED=true');
        $_ENV['GOOGLE_LOGIN_ENABLED'] = 'true';
        $this->assertTrue(filter_var(env('GOOGLE_LOGIN_ENABLED', false), FILTER_VALIDATE_BOOLEAN));

        putenv('GOOGLE_LOGIN_ENABLED=false');
        $_ENV['GOOGLE_LOGIN_ENABLED'] = 'false';

        // Clean up test user
        $userModel = new UserModel();
        $userModel->delete($user->id);
    }

    public function testZipPackageLifecycle(): void
    {
        $userModel = new UserModel();
        $admin = $userModel->where('role', 'superadmin')->first();

        // Buat file ZIP sementara
        $tempZip = WRITEPATH . 'test_flipbook_' . bin2hex(random_bytes(4)) . '.zip';
        $zip = new \ZipArchive();
        $zip->open($tempZip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFromString('index.html', '<!DOCTYPE html><html><head><title>E-Book Biologi Sel</title></head><body><h1>Halaman Utama</h1></body></html>');
        $zip->addFromString('files/thumb/1.jpg', 'fake-image-bytes');
        $zip->addFromString('mobile/style.css', 'body { margin: 0; }');
        $zip->close();

        $deckService = new DeckService();
        $deck = $deckService->create((int) $admin->id, [
            'is_public' => 1,
        ], $tempZip);

        $this->assertNotNull($deck);
        $this->assertSame('E-Book Biologi Sel', $deck->title);
        $this->assertStringContainsString('uploads/decks/' . $deck->nano_id . '/index.html', $deck->file_path);
        $this->assertNotNull($deck->thumbnail);
        $this->assertStringContainsString('1.jpg', $deck->thumbnail);

        // Pastikan file fisik ter-ekstrak di disk
        $extractedIndex = FCPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $deck->file_path);
        $this->assertFileExists($extractedIndex);

        // Hapus permanen dan pastikan direktori bersih
        $deckService->delete($deck->nano_id, (int) $admin->id);
        $deckService->forceDelete($deck->nano_id, (int) $admin->id);
        $this->assertFileDoesNotExist($extractedIndex);

        if (file_exists($tempZip)) {
            unlink($tempZip);
        }
    }
}
