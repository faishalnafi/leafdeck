<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Storage extends BaseConfig
{
    /**
     * Driver penyimpanan aktif: 'local', 's3', 'r2', 'gcs', 'custom'
     */
    public string $defaultDriver = 'local';

    /**
     * Konfigurasi Local Disk
     */
    public array $local = [
        'root' => FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'decks' . DIRECTORY_SEPARATOR,
        'url'  => 'uploads/decks',
    ];

    /**
     * Konfigurasi AWS S3 (Amazon Web Services)
     */
    public array $s3 = [
        'key'            => '',
        'secret'         => '',
        'region'         => 'us-east-1',
        'bucket'         => '',
        'endpoint'       => '',
        'public_url'     => '',
        'use_path_style' => false,
    ];

    /**
     * Konfigurasi Cloudflare R2 (S3-Compatible Object Storage)
     */
    public array $r2 = [
        'account_id'        => '',
        'access_key_id'     => '',
        'secret_access_key' => '',
        'bucket'            => '',
        'endpoint'          => '',
        'public_url'        => '',
    ];

    /**
     * Konfigurasi Google Cloud Storage (GCS)
     * Menggunakan HMAC Interoperability API yang 100% kompatibel S3 SigV4
     */
    public array $gcs = [
        'project_id'        => '',
        'access_key_id'     => '',
        'secret_access_key' => '',
        'bucket'            => '',
        'endpoint'          => 'https://storage.googleapis.com',
        'public_url'        => '',
    ];

    /**
     * Konfigurasi Object Storage Umum / Custom S3-Compatible
     * (MinIO, DigitalOcean Spaces, Wasabi, IDCloudHost, Alibaba OSS, dll)
     */
    public array $custom = [
        'endpoint'          => '',
        'access_key_id'     => '',
        'secret_access_key' => '',
        'region'            => 'us-east-1',
        'bucket'            => '',
        'public_url'        => '',
        'use_path_style'    => true,
    ];

    public function __construct()
    {
        parent::__construct();

        // Ambil dari environment variables (.env)
        $this->defaultDriver = env('STORAGE_DRIVER', $this->defaultDriver);

        // Local Storage Path (Tersentralisasi di uploads/decks)
        $localRelPath = (string) (env('LOCAL_STORAGE_PATH') ?? 'uploads/decks');
        $localRelPath = trim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $localRelPath), DIRECTORY_SEPARATOR);
        $this->local['root'] = FCPATH . $localRelPath . DIRECTORY_SEPARATOR;
        $this->local['url']  = str_replace(DIRECTORY_SEPARATOR, '/', $localRelPath);

        // AWS S3
        $this->s3['key']            = (string) (env('S3_KEY') ?? env('AWS_ACCESS_KEY_ID') ?? $this->s3['key']);
        $this->s3['secret']         = (string) (env('S3_SECRET') ?? env('AWS_SECRET_ACCESS_KEY') ?? $this->s3['secret']);
        $this->s3['region']         = (string) (env('S3_REGION') ?? env('AWS_DEFAULT_REGION') ?? $this->s3['region']);
        $this->s3['bucket']         = (string) (env('S3_BUCKET') ?? $this->s3['bucket']);
        $this->s3['endpoint']       = (string) (env('S3_ENDPOINT') ?? $this->s3['endpoint']);
        $this->s3['public_url']     = (string) (env('S3_PUBLIC_URL') ?? $this->s3['public_url']);
        $this->s3['use_path_style'] = filter_var(env('S3_USE_PATH_STYLE_ENDPOINT', $this->s3['use_path_style']), FILTER_VALIDATE_BOOLEAN);

        // Cloudflare R2
        $this->r2['account_id']        = (string) (env('R2_ACCOUNT_ID') ?? $this->r2['account_id']);
        $this->r2['access_key_id']     = (string) (env('R2_ACCESS_KEY_ID') ?? $this->r2['access_key_id']);
        $this->r2['secret_access_key'] = (string) (env('R2_SECRET_ACCESS_KEY') ?? $this->r2['secret_access_key']);
        $this->r2['bucket']            = (string) (env('R2_BUCKET') ?? $this->r2['bucket']);
        $this->r2['public_url']        = (string) (env('R2_PUBLIC_URL') ?? $this->r2['public_url']);
        if (!empty($this->r2['account_id'])) {
            $this->r2['endpoint'] = "https://{$this->r2['account_id']}.r2.cloudflarestorage.com";
        }

        // Google Cloud Storage (GCS)
        $this->gcs['project_id']        = (string) (env('GCS_PROJECT_ID') ?? $this->gcs['project_id']);
        $this->gcs['access_key_id']     = (string) (env('GCS_ACCESS_KEY_ID') ?? $this->gcs['access_key_id']);
        $this->gcs['secret_access_key'] = (string) (env('GCS_SECRET_ACCESS_KEY') ?? $this->gcs['secret_access_key']);
        $this->gcs['bucket']            = (string) (env('GCS_BUCKET') ?? $this->gcs['bucket']);
        $this->gcs['endpoint']          = (string) (env('GCS_ENDPOINT') ?? $this->gcs['endpoint']);
        $this->gcs['public_url']        = (string) (env('GCS_PUBLIC_URL') ?? $this->gcs['public_url']);

        // Custom / Generic S3-Compatible
        $this->custom['endpoint']          = (string) (env('STORAGE_ENDPOINT') ?? $this->custom['endpoint']);
        $this->custom['access_key_id']     = (string) (env('STORAGE_ACCESS_KEY') ?? $this->custom['access_key_id']);
        $this->custom['secret_access_key'] = (string) (env('STORAGE_SECRET_KEY') ?? $this->custom['secret_access_key']);
        $this->custom['region']            = (string) (env('STORAGE_REGION') ?? $this->custom['region']);
        $this->custom['bucket']            = (string) (env('STORAGE_BUCKET') ?? $this->custom['bucket']);
        $this->custom['public_url']        = (string) (env('STORAGE_PUBLIC_URL') ?? $this->custom['public_url']);
        $this->custom['use_path_style']    = filter_var(env('STORAGE_USE_PATH_STYLE', $this->custom['use_path_style']), FILTER_VALIDATE_BOOLEAN);
    }
}
