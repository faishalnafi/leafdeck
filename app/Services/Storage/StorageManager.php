<?php

namespace App\Services\Storage;

use Config\Storage as StorageConfig;
use InvalidArgumentException;

class StorageManager
{
    protected static array $instances = [];

    /**
     * Dapatkan instance driver storage berdasarkan nama
     * Jika $name null, gunakan defaultDriver dari konfigurasi
     */
    public static function disk(?string $name = null): StorageDriverInterface
    {
        $config = config(StorageConfig::class) ?? new StorageConfig();
        $driverName = strtolower($name ?: $config->defaultDriver);

        if (isset(self::$instances[$driverName])) {
            return self::$instances[$driverName];
        }

        $driver = self::createDriver($driverName, $config);
        self::$instances[$driverName] = $driver;

        return $driver;
    }

    /**
     * Dapatkan nama driver penyimpanan default yang sedang aktif
     */
    public static function getDefaultDriver(): string
    {
        $config = config(StorageConfig::class) ?? new StorageConfig();
        return strtolower($config->defaultDriver ?: 'local');
    }

    /**
     * Daftarkan custom instance driver (berguna untuk testing atau ekstensi dinamis)
     */
    public static function setDriver(string $name, StorageDriverInterface $driver): void
    {
        self::$instances[strtolower($name)] = $driver;
    }

    /**
     * Reset instance cache (misal setelah perubahan .env)
     */
    public static function flushInstances(): void
    {
        self::$instances = [];
    }

    /**
     * Membuat instance driver berdasarkan nama dan konfigurasi
     */
    public static function createDriver(string $name, ?StorageConfig $config = null): StorageDriverInterface
    {
        $config = $config ?? (config(StorageConfig::class) ?? new StorageConfig());

        return match ($name) {
            'local' => new LocalDriver($config->local ?? []),
            's3'    => new S3CompatibleDriver($config->s3 ?? [], 's3'),
            'r2'    => new S3CompatibleDriver($config->r2 ?? [], 'r2'),
            'gcs'   => new S3CompatibleDriver($config->gcs ?? [], 'gcs'),
            'custom', 'generic', 's3-compatible' => new S3CompatibleDriver($config->custom ?? [], 'custom'),
            default => throw new InvalidArgumentException("Driver storage '{$name}' tidak didukung."),
        };
    }

    /**
     * Menguji konektivitas driver tertentu secara on-the-fly dengan konfigurasi spesifik
     */
    public static function testDriver(string $driver, array $customConfig = []): array
    {
        $config = config(StorageConfig::class) ?? new StorageConfig();
        $driver = strtolower($driver);

        $mergedConfig = match ($driver) {
            's3'    => array_merge($config->s3 ?? [], $customConfig),
            'r2'    => array_merge($config->r2 ?? [], $customConfig),
            'gcs'   => array_merge($config->gcs ?? [], $customConfig),
            'custom', 'generic', 's3-compatible' => array_merge($config->custom ?? [], $customConfig),
            default => array_merge($config->local ?? [], $customConfig),
        };

        if ($driver === 'local') {
            $instance = new LocalDriver($mergedConfig);
        } else {
            $instance = new S3CompatibleDriver($mergedConfig, $driver);
        }

        return $instance->testConnection();
    }
}
