<?php

/**
 * Terjemahan otomatis (B5). Penyedia dipilih lewat .env; ganti tanpa mengubah kode.
 *
 *   TRANSLATE_DRIVER=deepl       (deepl | google | anthropic | none)
 *   DEEPL_API_KEY=...            (kunci gratis berakhiran ":fx")
 *   GOOGLE_TRANSLATE_KEY=...
 *   ANTHROPIC_API_KEY=...
 */
return [

    'driver' => env('TRANSLATE_DRIVER', 'none'),

    'timeout' => (int) env('TRANSLATE_TIMEOUT', 60),

    'deepl' => [
        'key'      => env('DEEPL_API_KEY'),
        // Kunci gratis (berakhiran :fx) otomatis memakai api-free.deepl.com.
        'endpoint' => env('DEEPL_ENDPOINT'),
    ],

    'google' => [
        'key' => env('GOOGLE_TRANSLATE_KEY'),
    ],

    'anthropic' => [
        'key'        => env('ANTHROPIC_API_KEY'),
        'model'      => env('TRANSLATE_CLAUDE_MODEL', 'claude-sonnet-5-5'),
        'max_tokens' => (int) env('TRANSLATE_CLAUDE_MAX_TOKENS', 16000),
    ],

    /*
    | Glosarium proteksi: istilah ini tidak diterjemahkan. Nama brand dari tabel
    | brands dan pilar bisnis ikut ditambahkan otomatis oleh Translator.
    */
    'protected' => [
        'DECAA.ID', 'DECAA', 'Komunitas Peduli Digital', 'Satu AI', 'Pasar Online',
        'MBG Food Hub', 'Desa Digital', 'PaperKing', 'Dapur Kirana',
    ],
];