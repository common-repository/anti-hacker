<?php

function wpah_is_apache() {
    return strpos(sanitize_text_field($_SERVER['SERVER_SOFTWARE']), 'Apache') !== false;
}

function wpah_is_nginx() {
    return strpos(sanitize_text_field($_SERVER['SERVER_SOFTWARE']), 'nginx') !== false;
}

function wpah_is_iis() {
    return strpos(sanitize_text_field($_SERVER['SERVER_SOFTWARE']), 'Microsoft-IIS') !== false;
}
