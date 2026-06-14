<?php

return [

    // Mirrors llm.demo_mode (same env). Used by views/banner and the demo seeder.
    'demo_mode' => (bool) env('DEMO_MODE', false),

    'default_target_words' => 1000,

    // Demo / first admin account, seeded on boot. CHANGE before deploying.
    'admin' => [
        'name' => env('ADMIN_NAME', 'Demo User'),
        'email' => env('ADMIN_EMAIL', 'demo@example.com'),
        'password' => env('ADMIN_PASSWORD', 'password'),
    ],

];
