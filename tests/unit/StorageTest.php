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

    public function testHtmlUploadToRemoteStorageDoesNotStoreLocally(): void
    {
        putenv('STORAGE_DRIVER=r2');
        $_ENV['STORAGE_DRIVER'] = 'r2';

        $mock = new InMemoryStorageDriver();
        StorageManager::setDriver('r2', $mock);

        $fileService = new \App\Services\FileService();
        $nanoId = 'remote_test_' . bin2hex(random_bytes(4));
        $content = '<html><body><h1>Materi Hanya di R2 Cloud</h1></body></html>';

        $relPath = $fileService->saveHtml($content, $nanoId);

        // 1. Berkas harus tersimpan di Object Storage
        $this->assertTrue($mock->has($relPath));
        $this->assertSame($content, $mock->get($relPath));

        // 2. Berkas TIDAK BOLEH tersimpan di lokal (FCPATH/uploads/decks/...)!
        $localPath = FCPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relPath);
        $this->assertFileDoesNotExist($localPath);

        // 3. readHtml berhasil membaca dari Object Storage
        $this->assertSame($content, $fileService->readHtml($relPath));

        // 4. delete menghapus dari Object Storage
        $fileService->delete($relPath);
        $this->assertFalse($mock->has($relPath));

        putenv('STORAGE_DRIVER=local');
        $_ENV['STORAGE_DRIVER'] = 'local';
    }

    public function testZipUploadToRemoteStorageDoesNotStoreLocally(): void
    {
        putenv('STORAGE_DRIVER=r2');
        $_ENV['STORAGE_DRIVER'] = 'r2';

        $mock = new InMemoryStorageDriver();
        StorageManager::setDriver('r2', $mock);

        $nanoId = 'zip_remote_' . bin2hex(random_bytes(4));
        $tempZip = WRITEPATH . 'test_pkg_' . $nanoId . '.zip';

        $zip = new \ZipArchive();
        $zip->open($tempZip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFromString('index.html', '<!DOCTYPE html><html><head><title>E-Book Kimia Organik</title></head><body><h1>Halaman 1</h1></body></html>');
        $zip->addFromString('files/thumb/1.jpg', 'fake-jpg-content');
        $zip->close();

        $fileService = new \App\Services\FileService();
        $res = $fileService->saveZipPackage($tempZip, $nanoId);

        // 1. Berkas entry terunggah ke Object Storage
        $this->assertSame('uploads/decks/' . $nanoId . '/index.html', $res['file_path']);
        $this->assertSame('E-Book Kimia Organik', $res['title']);
        $this->assertTrue($mock->has('uploads/decks/' . $nanoId . '/index.html'));
        $this->assertTrue($mock->has('uploads/decks/' . $nanoId . '/files/thumb/1.jpg'));

        // 2. Folder lokal di public/uploads/decks TIDAK BOLEH dibuat atau disimpan!
        $localDeckDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'decks' . DIRECTORY_SEPARATOR . $nanoId;
        $this->assertDirectoryDoesNotExist($localDeckDir);

        // 3. Folder temp ekstraksi di writable/temp/extract_... TIDAK BOLEH tersisa
        $tempExtractDir = WRITEPATH . 'temp' . DIRECTORY_SEPARATOR . 'extract_' . $nanoId;
        $this->assertDirectoryDoesNotExist($tempExtractDir);

        // 4. Thumbnail mengarah ke URL Cloudflare R2
        $this->assertStringContainsString('https://pub-mock.r2.dev/uploads/decks/' . $nanoId, $res['thumbnail']);

        // 5. Delete menghapus seluruh folder bundle dari remote Object Storage
        $fileService->delete($res['file_path']);
        $this->assertFalse($mock->has('uploads/decks/' . $nanoId . '/index.html'));
        $this->assertFalse($mock->has('uploads/decks/' . $nanoId . '/files/thumb/1.jpg'));

        if (file_exists($tempZip)) {
            unlink($tempZip);
        }

        putenv('STORAGE_DRIVER=local');
        $_ENV['STORAGE_DRIVER'] = 'local';
    }
}

/**
 * Mock Driver In-Memory untuk menguji perilaku transmisi langsung ke cloud
 */
class InMemoryStorageDriver implements \App\Services\Storage\StorageDriverInterface
{
    public array $files = [];

    public function put(string $path, string $content, string $mimeType = 'text/html'): bool
    {
        $this->files[$path] = $content;
        return true;
    }

    public function get(string $path): ?string
    {
        return $this->files[$path] ?? null;
    }

    public function has(string $path): bool
    {
        return isset($this->files[$path]);
    }

    public function delete(string $path): bool
    {
        unset($this->files[$path]);
        return true;
    }

    public function deleteDirectory(string $prefix): bool
    {
        $prefix = trim($prefix, '/') . '/';
        foreach (array_keys($this->files) as $key) {
            if (str_starts_with($key, $prefix)) {
                unset($this->files[$key]);
            }
        }
        return true;
    }

    public function url(string $path): string
    {
        return 'https://pub-mock.r2.dev/' . ltrim($path, '/');
    }

    public function testConnection(): array
    {
        return ['status' => true, 'latency_ms' => 1.5, 'message' => 'OK'];
    }
}
