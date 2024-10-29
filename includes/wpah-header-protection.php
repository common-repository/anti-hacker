<?php

function wpah_add_security_headers_callback() {
    header("X-XSS-Protection: 1; mode=block");
    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: SAMEORIGIN");
    //header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self'");
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");

    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Authorization, Content-Type");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Expose-Headers: Content-Length, X-JSON");
    header("Access-Control-Max-Age: 600");
    header("Clear-Site-Data: 'cookies', 'storage'");
    //header("Cross-Origin-Embedder-Policy: require-corp");
    // bloqueia js de ads
    //header("Cross-Origin-Opener-Policy: same-origin");
    //header("Cross-Origin-Resource-Policy: same-site");
    header("Permissions-Policy: geolocation=(), microphone=(), camera=(), interest-cohort=()");
    header("Referrer-Policy: same-origin");
    header("X-Permitted-Cross-Domain-Policies: none");
}

function wpah_add_security_headers() {
    add_action('send_headers', 'wpah_add_security_headers_callback');
}
