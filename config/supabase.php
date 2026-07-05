<?php

return [
    'url' => env('SUPABASE_URL'),
    'key' => env('SUPABASE_KEY'),
    'headers' => [
        'apikey' => env('SUPABASE_KEY'),
        'Authorization' => 'Bearer ' . env('SUPABASE_KEY'),
        'Content-Type' => 'application/json',
    ],
];