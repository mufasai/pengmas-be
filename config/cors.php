<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi ini mengatur header CORS yang dikirim oleh aplikasi.
    | Frontend (PENGMAS-MARGASANA-FIX) yang berjalan di port berbeda
    | perlu diizinkan untuk mengakses API backend ini.
    |
    | Untuk production (Railway), set FRONTEND_URL di environment variables
    | Railway dengan URL frontend kamu, contoh: https://frontend-kamu.railway.app
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_filter([
        // Development local
        'http://127.0.0.1:8001',
        'http://localhost:8001',
        'http://127.0.0.1:8000',
        'http://localhost:8000',
        'http://127.0.0.1:5173',
        'http://localhost:5173',
        // Production (Railway/hosting lain) — set via env FRONTEND_URL
        env('FRONTEND_URL'),
        env('APP_URL'),
    ]),

    'allowed_origins_patterns' => [
        // Izinkan semua subdomain railway.app
        '#^https://.*\.railway\.app$#',
        '#^https://.*\.up\.railway\.app$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
