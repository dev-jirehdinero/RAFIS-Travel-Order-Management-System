<?php

return [
    'client_id' => env('GOOGLE_DRIVE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_DRIVE_CLIENT_SECRET'),
    'refresh_token' => env('GOOGLE_DRIVE_REFRESH_TOKEN'),

    // ID of the shared corporate folder that Make.com used to write into.
    'folder_id' => env('GOOGLE_DRIVE_FOLDER_ID'),

    'connect_timeout' => 10,
    'timeout' => 60,
];
