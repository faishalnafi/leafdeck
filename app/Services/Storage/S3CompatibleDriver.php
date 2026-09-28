<?php

namespace App\Services\Storage;

class S3CompatibleDriver implements StorageDriverInterface
{
    protected string $accessKey;
    protected string $secretKey;
    protected string $bucket;
    protected string $region;
    protected string $endpoint;
    protected string $publicUrl;
    protected bool $usePathStyle;
    protected string $providerName;

    public function __construct(array $config = [], string $providerName = 's3')
    {
        $this->providerName = $providerName;
        $this->accessKey    = trim($config['key'] ?? $config['access_key_id'] ?? '');
        $this->secretKey    = trim($config['secret'] ?? $config['secret_access_key'] ?? '');
        $this->bucket       = trim($config['bucket'] ?? '');
        $this->region       = trim($config['region'] ?? 'us-east-1') ?: 'us-east-1';
        $this->publicUrl    = trim($config['public_url'] ?? '');
        $this->usePathStyle = !empty($config['use_path_style']);

        // Default endpoint per provider
        $rawEndpoint = trim($config['endpoint'] ?? '');
        if (empty($rawEndpoint)) {
            if ($providerName === 'r2' && !empty($config['account_id'])) {
                $rawEndpoint = "https://{$config['account_id']}.r2.cloudflarestorage.com";
            } elseif ($providerName === 'gcs') {
                $rawEndpoint = 'https://storage.googleapis.com';
            } else {
                $rawEndpoint = "https://s3.{$this->region}.amazonaws.com";
            }
        }

        $this->endpoint = rtrim($rawEndpoint, '/');

        // Untuk R2, GCS, MinIO default path style adalah true
        if (in_array($providerName, ['r2', 'gcs', 'custom'], true) && !isset($config['use_path_style'])) {
            $this->usePathStyle = true;
        }
    }

    public function put(string $path, string $content, string $mimeType = 'text/html'): bool
    {
        $url = $this->buildObjectUrl($path);
        $res = $this->request('PUT', $url, [
            'Content-Type' => $mimeType,
            'x-amz-acl'    => 'public-read',
        ], $content);

        return in_array($res['http_code'], [200, 201, 204], true);
    }

    public function get(string $path): ?string
    {
        $url = $this->buildObjectUrl($path);
        $res = $this->request('GET', $url);

        return ($res['http_code'] === 200) ? $res['body'] : null;
    }

    public function has(string $path): bool
    {
        $url = $this->buildObjectUrl($path);
        $res = $this->request('HEAD', $url);

        return ($res['http_code'] === 200);
    }

    public function delete(string $path): bool
    {
        $url = $this->buildObjectUrl($path);
        $res = $this->request('DELETE', $url);

        return in_array($res['http_code'], [200, 204], true);
    }

    public function deleteDirectory(string $prefix): bool
    {
        $prefix = trim(str_replace('\\', '/', $prefix), '/');
        if (empty($prefix)) {
            return false;
        }

        // List objek dengan prefix
        $listUrl = $this->buildBucketUrl() . '?prefix=' . urlencode($prefix . '/');
        $res = $this->request('GET', $listUrl);

        if ($res['http_code'] !== 200 || empty($res['body'])) {
            return false;
        }

        // Parse XML keys
        preg_match_all('/<Key>(.*?)<\/Key>/s', $res['body'], $matches);
        if (empty($matches[1])) {
            return true;
        }

        $allOk = true;
        foreach ($matches[1] as $key) {
            $key = html_entity_decode(trim($key), ENT_QUOTES | ENT_XML1, 'UTF-8');
            $delUrl = $this->buildObjectUrl($key);
            $delRes = $this->request('DELETE', $delUrl);
            if (!in_array($delRes['http_code'], [200, 204], true)) {
                $allOk = false;
            }
        }

        return $allOk;
    }

    public function url(string $path): string
    {
        $clean = ltrim(str_replace('\\', '/', $path), '/');

        if (!empty($this->publicUrl)) {
            return rtrim($this->publicUrl, '/') . '/' . $clean;
        }

        return $this->buildObjectUrl($clean);
    }

    public function testConnection(): array
    {
        if (empty($this->accessKey) || empty($this->secretKey) || empty($this->bucket)) {
            return [
                'status'     => false,
                'latency_ms' => 0,
                'message'    => 'Konfigurasi belum lengkap: Pastikan Access Key, Secret Key, dan Nama Bucket telah diisi.',
            ];
        }

        $startTime = microtime(true);
        // Uji dengan list objek max-keys=1
        $testUrl = $this->buildBucketUrl() . '?max-keys=1';
        $res = $this->request('GET', $testUrl);
        $latency = round((microtime(true) - $startTime) * 1000, 2);

        if ($res['http_code'] === 200) {
            $providerLabel = match ($this->providerName) {
                's3'     => 'Amazon S3',
                'r2'     => 'Cloudflare R2',
                'gcs'    => 'Google Cloud Storage (GCS)',
                default  => 'Object Storage S3-Compatible',
            };

            return [
                'status'     => true,
                'http_code'  => 200,
                'latency_ms' => $latency,
                'message'    => "Koneksi berhasil ke {$providerLabel} (Bucket: '{$this->bucket}').",
            ];
        }

        if ($res['http_code'] === 403) {
            return [
                'status'     => false,
                'http_code'  => 403,
                'latency_ms' => $latency,
                'message'    => "Akses ditolak (403 Forbidden): Kredensial tidak valid atau tidak memiliki izin akses ke bucket '{$this->bucket}'.",
            ];
        }

        if ($res['http_code'] === 404) {
            return [
                'status'     => false,
                'http_code'  => 404,
                'latency_ms' => $latency,
                'message'    => "Bucket tidak ditemukan (404 Not Found): Periksa kembali nama bucket '{$this->bucket}'.",
            ];
        }

        return [
            'status'     => false,
            'http_code'  => $res['http_code'],
            'latency_ms' => $latency,
            'message'    => 'Gagal terhubung ke storage: ' . ($res['error'] ?: 'HTTP ' . $res['http_code']),
        ];
    }

    /**
     * Membangun base URL bucket
     */
    protected function buildBucketUrl(): string
    {
        if ($this->usePathStyle) {
            return $this->endpoint . '/' . $this->bucket;
        }

        $parsed = parse_url($this->endpoint);
        $scheme = $parsed['scheme'] ?? 'https';
        $host   = $parsed['host'] ?? '';
        $port   = isset($parsed['port']) ? ':' . $parsed['port'] : '';

        return "{$scheme}://{$this->bucket}.{$host}{$port}";
    }

    /**
     * Membangun full URL untuk object key
     */
    protected function buildObjectUrl(string $key): string
    {
        $cleanKey = ltrim(str_replace('\\', '/', $key), '/');
        return $this->buildBucketUrl() . '/' . $cleanKey;
    }

    /**
     * Eksekusi HTTP Request dengan otentikasi AWS Signature Version 4 (SigV4)
     */
    protected function request(string $method, string $url, array $headers = [], string $body = ''): array
    {
        $parsed = parse_url($url);
        $host   = $parsed['host'] ?? '';
        if (isset($parsed['port'])) {
            $host .= ':' . $parsed['port'];
        }
        $path  = $parsed['path'] ?? '/';
        $query = $parsed['query'] ?? '';

        $now       = time();
        $amzDate   = gmdate('Ymd\THis\Z', $now);
        $dateStamp = gmdate('Ymd', $now);

        $payloadHash = hash('sha256', $body);

        $signedHeadersMap = [
            'host'                 => $host,
            'x-amz-date'           => $amzDate,
            'x-amz-content-sha256' => $payloadHash,
        ];

        foreach ($headers as $k => $v) {
            $signedHeadersMap[strtolower(trim($k))] = trim($v);
        }

        ksort($signedHeadersMap);

        // Canonical Headers & SignedHeaders string
        $canonicalHeaders = '';
        $signedHeaderNames = [];
        foreach ($signedHeadersMap as $k => $v) {
            $canonicalHeaders .= "{$k}:{$v}\n";
            $signedHeaderNames[] = $k;
        }
        $signedHeadersStr = implode(';', $signedHeaderNames);

        // Canonical Query String
        $canonicalQuery = '';
        if (!empty($query)) {
            parse_str($query, $queryParams);
            ksort($queryParams);
            $pairs = [];
            foreach ($queryParams as $k => $v) {
                $pairs[] = rawurlencode((string)$k) . '=' . rawurlencode((string)$v);
            }
            $canonicalQuery = implode('&', $pairs);
        }

        // Canonical URI
        $canonicalUri = implode('/', array_map('rawurlencode', explode('/', $path)));
        $canonicalUri = preg_replace('#/+#', '/', $canonicalUri);

        // Canonical Request
        $canonicalRequest = "{$method}\n{$canonicalUri}\n{$canonicalQuery}\n{$canonicalHeaders}\n{$signedHeadersStr}\n{$payloadHash}";

        // String to Sign
        $scope = "{$dateStamp}/{$this->region}/s3/aws4_request";
        $stringToSign = "AWS4-HMAC-SHA256\n{$amzDate}\n{$scope}\n" . hash('sha256', $canonicalRequest);

        // Signing Key calculation
        $kDate    = hash_hmac('sha256', $dateStamp, 'AWS4' . $this->secretKey, true);
        $kRegion  = hash_hmac('sha256', $this->region, $kDate, true);
        $kService = hash_hmac('sha256', 's3', $kRegion, true);
        $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);

        $signature = hash_hmac('sha256', $stringToSign, $kSigning);

        $authHeader = "AWS4-HMAC-SHA256 Credential={$this->accessKey}/{$scope}, SignedHeaders={$signedHeadersStr}, Signature={$signature}";

        // Prepare cURL
        $httpHeaders = [
            "Authorization: {$authHeader}",
            "Host: {$host}",
            "x-amz-date: {$amzDate}",
            "x-amz-content-sha256: {$payloadHash}",
        ];
        foreach ($headers as $k => $v) {
            $httpHeaders[] = "{$k}: {$v}";
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeaders);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        if (in_array($method, ['PUT', 'POST'], true)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        } elseif ($method === 'HEAD') {
            curl_setopt($ch, CURLOPT_NOBODY, true);
        }

        $responseBody = curl_exec($ch);
        $httpCode     = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError    = curl_error($ch);
        curl_close($ch);

        return [
            'http_code' => $httpCode,
            'body'      => ($responseBody !== false) ? (string)$responseBody : '',
            'error'     => $curlError,
        ];
    }
}
