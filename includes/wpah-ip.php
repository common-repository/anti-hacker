<?php

require_once "wpah-messages.php";
require_once "wpah-logs.php";
require_once "wpah-siem-connector.php";

function wpah_find_ip($ip, $file_name) {
    // Lê o conteúdo do arquivo em uma string
    $file_contents = file_get_contents(plugin_dir_path( __FILE__ ) . '../data/' . $file_name);

    // Verifica se o endereço IP está presente no arquivo de texto
    return strpos($file_contents, $ip);
}

function wpah_is_proxy_ip($ip) {
    return wpah_find_ip($ip, "proxy.txt");
}

function wpah_is_tor_ip($ip) {
    return wpah_find_ip($ip, "tor.txt");
}

function wpah_get_ip_address() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = filter_var( $_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP );
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = filter_var( $_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP );
    } else {
        $ip = filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP );
    }
    return $ip;
}

function wpah_blacklist_ip_proxy() {
    if ( wpah_is_proxy_ip(wpah_get_ip_address()) ) {
        $proxy_msg = "Was detected and blocked an access by IP " . get_ip_address() . " in url " . sanitize_url($_SERVER['REQUEST_URI']);
        if (get_option('wpah_config_syslog', 999)) {
            wpah_send_syslog(get_option('wpah_config_syslog', 999), $proxy_msg);
        }        

        wpah_add_log("Anonymous proxy blocked", $proxy_msg);

        header( 'HTTP/1.1 403 Forbidden' ); // retorna o status de erro 403
        wp_die( WPAH_PROXY_MSG ); // exibe a mensagem de bloqueio
    }
}

function wpah_blacklist_ip_tor() {
    if ( wpah_is_tor_ip(wpah_get_ip_address()) ) {
        $tor_msg = "Was detected and blocked an access by TOR Network IP " . get_ip_address() . " in url " . sanitize_url($_SERVER['REQUEST_URI']);
        wpah_add_log("TOR blocked", $tor_msg);
        if (get_option('wpah_config_syslog', 999)) {
                wpah_send_syslog(get_option('wpah_config_syslog', 999), $tor_msg);
        }
        header( 'HTTP/1.1 403 Forbidden' ); // retorna o status de erro 403
        wp_die( WPAH_TOR_MSG ); // exibe a mensagem de bloqueio
    }
}
