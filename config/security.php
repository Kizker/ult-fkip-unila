<?php

return [
    // Security headers are applied via middleware globally.
    'headers' => [
        'x_content_type_options' => 'nosniff',
        'referrer_policy' => 'strict-origin-when-cross-origin',
        'x_frame_options' => 'DENY',
        'permissions_policy' => "geolocation=(), microphone=(), camera=()",
        // CSP: minimal; tune as needed for analytics/CDN
        'content_security_policy' => implode('; ', [
            "default-src 'self'",
            "img-src 'self' data: blob: https://img.youtube.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com" . (env('APP_ENV') === 'local' ? " http://localhost:5173 http://127.0.0.1:5173" : ""),
            "font-src 'self' https://fonts.gstatic.com data:",
            // Alpine.js CSP-friendly build avoids unsafe-eval
            "script-src 'self' 'unsafe-inline'" . (env('APP_ENV') === 'local' ? " http://localhost:5173 http://127.0.0.1:5173" : ""),
            "connect-src 'self'" . (env('APP_ENV') === 'local' ? " http://localhost:5173 http://127.0.0.1:5173 ws://localhost:5173 ws://127.0.0.1:5173" : ""),
            // Needed for embedded video (e.g., YouTube) inside rich text editor.
            "frame-src 'self' https://www.youtube.com https://www.youtube-nocookie.com",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ]),
        // HSTS enabled in production only
        'hsts' => [
            'enabled' => env('APP_ENV') === 'production',
            'max_age' => 31536000,
            'include_subdomains' => true,
            'preload' => false,
        ],
    ],
];
