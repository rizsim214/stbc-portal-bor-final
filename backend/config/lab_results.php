<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Lab Result File Storage Disk
    |--------------------------------------------------------------------------
    |
    | Set this to the filesystem disk used for lab result files.
    | For AWS S3, keep this as "s3" and configure the AWS_* variables in .env.
    |
    */
    'storage_disk' => env('LAB_RESULTS_STORAGE_DISK', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Signed URL Expiry (minutes)
    |--------------------------------------------------------------------------
    |
    | UPLOAD: lifetime for pre-signed PUT URLs used by the frontend uploader.
    | DOWNLOAD: lifetime for pre-signed GET URLs for secure file viewing.
    |
    */
    'upload_url_ttl_minutes' => (int) env('LAB_RESULTS_UPLOAD_URL_TTL_MINUTES', 10),
    'download_url_ttl_minutes' => (int) env('LAB_RESULTS_DOWNLOAD_URL_TTL_MINUTES', 5),

    /*
    |--------------------------------------------------------------------------
    | Upload Validation
    |--------------------------------------------------------------------------
    |
    | Max file size accepted by the API (in bytes) and allowed MIME types.
    | Keep this aligned with frontend validations and your S3 upload policy.
    |
    */
    'max_file_size_bytes' => (int) env('LAB_RESULTS_MAX_FILE_SIZE_BYTES', 10485760), // 10 MB
    'allowed_content_types' => [
        'application/pdf',
        'image/jpeg',
        'image/png',
    ],
];
