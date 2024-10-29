<?php
require_once plugin_dir_path(__FILE__) . '/wpah-server.php';

function wpah_create_backup_file($file_path) {
    $pathinfo = pathinfo($file_path);
    $file_name = $pathinfo['basename'];
    $dir_name = $pathinfo['dirname'];
    $dest_file_path = $dir_name . DIRECTORY_SEPARATOR . $file_name . '.bak';
    $i = 1;
    while (file_exists($dest_file_path) && filemtime($dest_file_path) >= filemtime($file_path)) {
        $dest_file_path = $dir_name . DIRECTORY_SEPARATOR . $file_name . '-copy' . $i . '.bak';
        $i++;
    }
    if (!file_exists($dest_file_path) || filemtime($dest_file_path) < filemtime($file_path)) {
        if (copy($file_path, $dest_file_path)) {
            return true;
        } else {
            return false;
        }
    } else {
        return true;
    }
}

function wpah_disable_directory_listing() {
    if (wpah_is_apache()) {
        $htaccess_path = ABSPATH . '.htaccess';

        if (file_exists($htaccess_path)) {
            $htaccess_content = file_get_contents($htaccess_path);

            if (strpos($htaccess_content, 'Options +Indexes') !== false) {
                $htaccess_content = str_replace('Options +Indexes', '', $htaccess_content);
                file_put_contents($htaccess_path, $htaccess_content);
            }

            if (strpos($htaccess_content, 'Options -Indexes') === false) {
                $htaccess_content .= "\n# WP Anti-Hacker rules\nOptions -Indexes\n";
                file_put_contents($htaccess_path, $htaccess_content);
            }
        }
    }
}
