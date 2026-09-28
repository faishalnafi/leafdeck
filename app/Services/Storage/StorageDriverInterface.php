<?php

namespace App\Services\Storage;

interface StorageDriverInterface
{
    /**
     * Menyimpan berkas ke storage
     */
    public function put(string $path, string $content, string $mimeType = 'text/html'): bool;

    /**
     * Mengambil isi konten berkas dari storage
     */
    public function get(string $path): ?string;

    /**
     * Memeriksa apakah berkas ada di storage
     */
    public function has(string $path): bool;

    /**
     * Menghapus sebuah berkas dari storage
     */
    public function delete(string $path): bool;

    /**
     * Menghapus seluruh berkas dalam direktori / prefix
     */
    public function deleteDirectory(string $prefix): bool;

    /**
     * Mendapatkan URL publik berkas
     */
    public function url(string $path): string;

    /**
     * Menguji konektivitas & kredensial ke storage
     *
     * @return array [status: bool, message: string, latency_ms: float|int, http_code?: int]
     */
    public function testConnection(): array;
}
