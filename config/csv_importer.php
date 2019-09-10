<?php
declare(strict_types=1);

return [
    'version'      => '0.1',
    'access_token' => env('FIREFLY_III_ACCESS_TOKEN'),
    'uri'          => env('FIREFLY_III_URI'),
    'upload_path'  => storage_path('uploads'),
];
