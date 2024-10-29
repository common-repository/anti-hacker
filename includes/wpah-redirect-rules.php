<?php

function wpah_get_wordpress_root_dir_name() {
    $wp_root_dir_url = trailingslashit( parse_url( site_url(), PHP_URL_PATH ) );
    $wp_root_dir_url = trim( $wp_root_dir_url, '/' );
    if (strlen($wp_root_dir_url) > 0) {
        $wp_root_dir_url = '/' . $wp_root_dir_url;
    }
    return $wp_root_dir_url;
}

function wpah_add_redirect_folder_rules() {
    $htaccess_file = ABSPATH . '.htaccess'; // Caminho para o arquivo .htaccess
    $entry_point = wpah_get_wordpress_root_dir_name();
    //error_log(' entry point '. $entry_point . "\n", 3, 'wp_anti_hacker_log');
    
    $regras_htaccess = "# WP Anti-Hacker rules\nRewriteRule ^([_0-9a-zA-Z-]+/)?\\-content/(.*) $entry_point/wp-content/$2 [QSA,L]\nRewriteRule ^([_0-9a-zA-Z-]+/)?\\-includes/(.*) $entry_point/wp-includes/$2 [QSA,L]\nRewriteRule ^([_0-9a-zA-Z-]+/)?\\-json/(.*) $entry_point/wp-json/$2 [QSA,L]\nRewriteRule ^([_0-9a-zA-Z-]+/)?\\-admin/(.*) $entry_point/wp-admin/$2 [QSA,L]\n";

    if (file_exists($htaccess_file)) {
        $htaccess_contents = file_get_contents($htaccess_file);

        if (strpos($htaccess_contents, $regras_htaccess) == false) {
            // Verifica se a linha RewriteBase /wordpress/ existe
            if (strpos($htaccess_contents, 'RewriteEngine On') !== false) {
                $htaccess_contents = str_replace('RewriteEngine On', 'RewriteEngine On' . "\n" . $regras_htaccess, $htaccess_contents);
            } else {
                $htaccess_contents = "<IfModule mod_rewrite.c>\nRewriteEngine On\n" . $regras_htaccess . "</IfModule>\n" . $htaccess_contents;
            }

            // Grava as novas regras no arquivo .htaccess
            wpah_create_backup_file($htaccess_file);
            file_put_contents($htaccess_file, $htaccess_contents);
        }
    } else {
        wpah_create_backup_file($htaccess_file);
        $htaccess_contents = "<IfModule mod_rewrite.c>\nRewriteEngine On\n" . $regras_htaccess . "</IfModule>\n" . $htaccess_contents;
        file_put_contents($htaccess_file, $htaccess_contents);
    }
}
