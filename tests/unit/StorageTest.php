<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Storage as StorageConfig;
use App\Services\Storage\StorageManager;
use App\Services\Storage\LocalDriver;
use App\Services\Storage\S3CompatibleDriver;

/**
 * @internal
 */
final class StorageTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        StorageManager::flushInstances();
    }

    public function testStorageConfigDefaults(): void
    {
        $config = new StorageConfig();
        $this->assertSame('local', $config->defaultDriver);
        $this->assertArrayHasKey('root', $config->local);
        $this->assertArrayHasKey('bucket', $config->s3);
        $this->assertArrayHasKey('bucket', $config->r2);
        $this->assertArrayHasKey('bucket', $config->gcs);
        $this->assertArrayHasKey('bucket', $config->custom);
    }

    public function testStorageManagerReturnsLocalDriver(): void
    {
        $driver = StorageManager::disk('local');
        $this->assertInstanceOf(LocalDriver::class, $driver);

        $testPath = 'test_sample_' . bin2hex(random_bytes(4)) . '.html';
        $content = '<h1>LeafDeck Object Storage Test</h1>';

        // Put
        $this->assertTrue($driver->put($testPath, $content));

        // Has
        $this->assertTrue($driver->has($testPath));

        // Get
        $this->assertSame($content, $driver->get($testPath));

        // URL
        $url = $driver->url($testPath);
        $this->assertStringContainsString($testPath, $url);

        // Delete
        $this->assertTrue($driver->delete($testPath));
        $this->assertFalse($driver->has($testPath));
    }

    public function testLocalDriverConnectionTest(): void
    {
        $res = StorageManager::testDriver('local');
        $this->assertTrue($res['status']);
        $this->assertArrayHasKey('latency_ms', $res);
        $this->assertStringContainsString('dapat diakses', $res['message']);
    }

    public function testS3DriverInstantiationAndUrl(): void
    {
        $config = [
            'key'        => 'TESTKEY123',
            'secret'     => 'TESTSECRET456',
            'bucket'     => 'leafdeck-s3-test',
            'region'     => 'ap-southeast-3',
            'public_url' => 'https://cdn.example.sch.id',
        ];

        $driver = new S3CompatibleDriver($config, 's3');
        $this->assertInstanceOf(S3CompatibleDriver::class, $driver);

        // Public CDN URL
        $this->assertSame('https://cdn.example.sch.id/uploads/decks/abc123/index.html', $driver->url('uploads/decks/abc123/index.html'));

        // Direct S3 URL without public CDN
        $driverNoCdn = new S3CompatibleDriver([
            'key'            => 'TESTKEY123',
            'secret'         => 'TESTSECRET456',
            'bucket'         => 'leafdeck-s3-test',
            'region'         => 'ap-southeast-3',
            'use_path_style' => false,
        ], 's3');
        $this->assertSame('https://leafdeck-s3-test.s3.ap-southeast-3.amazonaws.com/test.html', $driverNoCdn->url('test.html'));
    }

    public function testR2DriverConfiguration(): void
    {
        $config = [
            'account_id'        => 'cf_account_12345',
            'access_key_id'     => 'r2_key_abc',
            'secret_access_key' => 'r2_secret_xyz',
            'bucket'            => 'leafdeck-r2',
            'public_url'        => 'https://pub-abc.r2.dev',
        ];

        $driver = new S3CompatibleDriver($config, 'r2');
        $this->assertInstanceOf(S3CompatibleDriver::class, $driver);
        $this->assertSame('https://pub-abc.r2.dev/decks/test.html', $driver->url('decks/test.html'));
    }

    public function testGcsDriverConfiguration(): void
    {
        $config = [
            'project_id'        => 'leafdeck-gcp',
            'access_key_id'     => 'GOOG1ETEST',
            'secret_access_key' => 'GOOGSECRET',
            'bucket'            => 'leafdeck-gcs-bucket',
            'public_url'        => 'https://storage.googleapis.com/leafdeck-gcs-bucket',
        ];

        $driver = new S3CompatibleDriver($config, 'gcs');
        $this->assertInstanceOf(S3CompatibleDriver::class, $driver);
        $this->assertSame('https://storage.googleapis.com/leafdeck-gcs-bucket/index.html', $driver->url('index.html'));
    }

    public function testCustomGenericS3Configuration(): void
    {
        $config = [
            'endpoint'          => 'https://s3.idcloudhost.com',
            'access_key_id'     => 'idcloud_key',
            'secret_access_key' => 'idcloud_secret',
            'bucket'            => 'sekolah-decks',
            'use_path_style'    => true,
        ];

        $driver = new S3CompatibleDriver($config, 'custom');
        $this->assertInstanceOf(S3CompatibleDriver::class, $driver);
        $this->assertSame('https://s3.idcloudhost.com/sekolah-decks/presentation.html', $driver->url('presentation.html'));
    }

    public function testIncompleteRemoteStorageConnectionTest(): void
    {
        $res = StorageManager::testDriver('s3', [
            'key'    => '',
            'secret' => '',
            'bucket' => '',
        ]);
        $this->assertFalse($res['status']);
        $this->assertStringContainsString('belum lengkap', $res['message']);
    }
}
