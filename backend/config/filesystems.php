<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => false,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        'supabase' => [
            'driver' => 's3',
            // Supabase Storage exposes an S3-compatible endpoint. Keep this hostname
            // browser-reachable because signed upload/download URLs are opened directly by clients.
            'key' => env('SUPABASE_STORAGE_ACCESS_KEY_ID'),
            'secret' => env('SUPABASE_STORAGE_SECRET_ACCESS_KEY'),
            'region' => env('SUPABASE_STORAGE_REGION', 'ap-southeast-1'),
            'bucket' => env('SUPABASE_STORAGE_BUCKET', 'lab-results'),
            'url' => env('SUPABASE_STORAGE_URL'),
            'endpoint' => env('SUPABASE_STORAGE_ENDPOINT'),
            'use_path_style_endpoint' => env('SUPABASE_STORAGE_PATH_STYLE', true),
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
