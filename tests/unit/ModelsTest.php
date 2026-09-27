<?php

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\UserModel;
use App\Models\TokenModel;
use App\Models\DeckModel;
use CodeIgniter\I18n\Time;

/**
 * @internal
 */
final class ModelsTest extends CIUnitTestCase
{
    public function testUserModelQueries(): void
    {
        $userModel = new UserModel();
        $admin = $userModel->where('role', 'superadmin')->first();

        $this->assertNotNull($admin, 'Superadmin must exist in database');
        $this->assertSame('superadmin', $admin->role);

        $bySso = $userModel->findBySsoId($admin->sso_user_id);
        $this->assertNotNull($bySso);
        $this->assertSame($admin->id, $bySso->id);

        $byNomor = $userModel->findByNomorInduk($admin->nomor_induk);
        $this->assertNotNull($byNomor);
        $this->assertSame($admin->id, $byNomor->id);
    }

    public function testTokenModelLifecycle(): void
    {
        $userModel = new UserModel();
        $admin = $userModel->where('role', 'superadmin')->first();

        $tokenModel = new TokenModel();
        $tokenStr = 'unit_test_token_' . bin2hex(random_bytes(10));

        $tokenModel->insert([
            'user_id'    => $admin->id,
            'token'      => $tokenStr,
            'expires_at' => Time::now()->addHours(1)->toDateTimeString(),
        ]);

        $valid = $tokenModel->getValidToken($tokenStr);
        $this->assertNotNull($valid);
        $this->assertNotNull($valid->user);
        $this->assertSame($admin->nama, $valid->user->nama);

        $tokenModel->revokeToken($tokenStr);
        $this->assertNull($tokenModel->getValidToken($tokenStr));
    }

    public function testDeckModelLifecycle(): void
    {
        $userModel = new UserModel();
        $admin = $userModel->where('role', 'superadmin')->first();

        $deckModel = new DeckModel();
        $nanoId = 'test_nano_' . substr(bin2hex(random_bytes(6)), 0, 10);

        $deckId = $deckModel->insert([
            'nano_id'     => $nanoId,
            'user_id'     => $admin->id,
            'title'       => 'Test Deck Title',
            'description' => 'Test Description',
            'file_path'   => 'uploads/decks/test.html',
            'thumbnail'   => null,
            'is_public'   => 1,
            'view_count'  => 0,
        ]);

        $this->assertIsNumeric($deckId);

        $found = $deckModel->findByNanoId($nanoId);
        $this->assertNotNull($found);
        $this->assertSame('Test Deck Title', $found->title);

        $deckModel->incrementViews($nanoId);
        $found = $deckModel->findByNanoId($nanoId);
        $this->assertEquals(1, $found->view_count);

        $deckModel->delete($deckId);
        $this->assertNull($deckModel->findByNanoId($nanoId));
        $this->assertNotNull($deckModel->withDeleted()->find($deckId));

        $deckModel->purgeDeleted();
    }
}
