<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Lab Result File Storage Disk
    |--------------------------------------------------------------------------
    |
    | Set this to the filesystem disk used for lab result files.
    | Supabase Storage uses the dedicated "supabase" disk in config/filesystems.php.
    |
    */
    'storage_disk' => env('LAB_RESULTS_STORAGE_DISK', 'supabase'),

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
    | Keep this aligned with frontend validations and your Supabase Storage policy.
    |
    */
    'max_file_size_bytes' => (int) env('LAB_RESULTS_MAX_FILE_SIZE_BYTES', 10485760), // 10 MB
    'allowed_content_types' => [
        'application/pdf',
        'image/jpeg',
        'image/png',
    ],
];
