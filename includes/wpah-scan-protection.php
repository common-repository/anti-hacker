<?php
require_once "wpah-messages.php";
require_once "wpah-logs.php";
require_once "wpah-siem-connector.php";

function wpah_transform_404_to_200() {
    global $wp_query;

    if ($wp_query->is_404) {
        $ip = wpah_get_ip_address();

        if ($ip != '127.0.0.1' && $ip !== '::1') {    
            $user_agent = sanitize_text_field($_SERVER['HTTP_USER_AGENT']);
            if (strpos($user_agent, 'WordPress/') !== false) {
                status_header(200);
                $wp_query->is_404 = false;
            }
        }
    }
}

function wpah_is_browser_user_agent() {
    $user_agent = sanitize_text_field($_SERVER['HTTP_USER_AGENT']);
    $browser_names = array(
        'Chrome', 
        'Firefox', 
        'Mozilla',
        'Safari', 
        'Opera', 
        'Edge', 
        'Internet Explorer', 
        'Brave', 
        'Vivaldi', 
        'Maxthon', 
        'SeaMonkey', 
        'Pale Moon', 
        'Konqueror', 
        'Midori', 
        'QupZilla', 
        'Waterfox', 
        'Yandex',
        'facebookexternalhit',
        'Twitterbot',
        'LinkedInBot',
        'Googlebot',
        'Bingbot',
        'DuckDuckBot',
        'Slurp',
        'Baiduspider',
        'YandexBot',
        'Pinterest',
        'GooglePlus',
        'WhatsApp',
        'Bingbot',
        'BingPreview',
        'SkypeUriPreview',
        'Discordbot',
        'Slackbot',
        'TelegramBot',
        'Instagram',
        'Applebot',
        'Google',
        'Iframely',
        'SerendeputyBot',
        'DuckDuckGo',
        'com.apple.WebKit.Networking',
        'Mediatoolkitbot',
        'Dalvik/',
        'msnbot',
        'WordPress/',
    );

    foreach ($browser_names as $browser_name) {
        if (strpos($user_agent, $browser_name) !== false) {
            return true;
        }
    }

    return false;
}

// Função para verificar se o acesso é do WPScan
function wpah_is_wpscan_client() {
    $cookie = '';
    if (isset($_SERVER['HTTP_COOKIE'])) {
        $cookie = sanitize_text_field($_SERVER['HTTP_COOKIE']);
    }

    if ($cookie == 'wordpress_test_cookie=WP%20Cookie%20check') {
        return true;
    }

    return false;
}

function wpah_url_file_exists($url){
    // Extrai o caminho do arquivo a partir da URL
    $path = parse_url($url, PHP_URL_PATH);

    // Remove a parte da URL que não faz parte da raiz do WordPress
    //$path = preg_replace('#^/wp-content/themes/twentytwentyone/#', '', $path);
    $path = preg_replace('#^/' . basename(WP_CONTENT_DIR) . '/themes/twentytwentyone/#', '', $path);

    // Adiciona a raiz do WordPress ao caminho do arquivo
    $path = dirname(__FILE__) . '/' . $path;

    // Verifica se o arquivo existe no sistema de arquivos
    if (file_exists($path)) {
        return true;
    } 
    return false;
}

function wpah_is_nuclei_client() {
    $uri_list = file_get_contents(plugin_dir_path(__FILE__) . '../data/nuclei.txt');

    // Converte a lista em um array de uri$uri_list
    $uri_list = explode("\n", $uri_list);

    $url_without_params = preg_replace('/(\?.*)|(#.*)/', '', sanitize_url($_SERVER['REQUEST_URI']));

    // Loop através das uri$uri_list da lista
    foreach ($uri_list as $uri) {
        $uri = str_replace(array("\r", "\n"), '', $uri);

        if (strlen($uri) < strlen($url_without_params)) {
            $uri_last_part = substr($url_without_params, -strlen($uri));

            if (strcmp($uri_last_part, $uri) === 0) {
                if (url_file_exists(sanitize_url($_SERVER['REQUEST_URI'])) == false) {
                    return true;
                }
            }
        }
    }
    return false;
}

function wpah_is_nikto_client() {
    $regex1 = '#[A-Za-z0-9]{8}\.#';
    $regex2 = '#[A-Z].*\.#';

    $ip = sanitize_text_field( $_SERVER['REMOTE_ADDR'] );
    $file_name = basename(sanitize_url($_SERVER['REQUEST_URI']));

    $check_timeout = 5;

    /*
    /j4b9yV3t.chl+
    /p4Crxn6c.access
    // padrao 8 caracteres aleatorios sempre com pelo menos 1 maiusculo 
    // se o padrao fez 3 requests em sequencia bloqueia
    */

    if (strpos($file_name, 'nikto-test') !== false) {
        return true;
    }
    $transient_name = 'urls_nikto_' . $ip;
    if ( preg_match( $regex1, $file_name ) && preg_match( $regex2, $file_name ) ) {
        $last_matche = get_transient( $transient_name );
        if ($last_matche) {
            $last_matche++;
            if ($last_matche == 3) {
                return true;
            }
            set_transient($transient_name, $last_matche, $check_timeout);
        } else {
            set_transient($transient_name, 1, $check_timeout);
        }
    } else {
        delete_transient( $transient_name );
    }
    return false;
}

function wpah_is_owasp_zap_client() {
    // Obtém o endereço IP do usuário
    $ip = sanitize_text_field( $_SERVER['REMOTE_ADDR'] );
    
    // Obtém o parâmetro REQUEST_URI atual
    $request_uri = urldecode(sanitize_url(( $_SERVER['REQUEST_URI'] )));
    
    // Define o tempo limite em segundos
    $check_timeout = 5;
    // owasp zap sempre manda 2 requests no padrao:
    // /5386012307819140034.php
    // ou /5386012307819140034
    // 19 numeros aleatorios
    $transient_name = 'urls_zap_' . $ip;
    if ( preg_match( '#\/\d{19}(\.php)?$#', $request_uri ) ) {
        $last_matche = get_transient( $transient_name );
        if ($last_matche) {
            $last_matche++;
            if ($last_matche == 2) {
                return true;
            }
        } else {
            set_transient($transient_name, 1, $check_timeout);
        }
    } else {
        delete_transient( $transient_name );
    }
    // se bateu o terceiro request do owasp zap ja bloqueia
    if ( strpos($request_uri, '?-d allow_url_include1 -d auto_prepend_filephp://input') !== false ) {
        return true;
    }
    return false;
}


// Verificar se o IP está bloqueado
function wpah_ip_blocked($ip_address) {
    // Definir a chave do transient com o endereço IP do visitante
    $transient_key = 'blocked_ip_' . $ip_address;

    // Verificar se o transient existe para o endereço IP do visitante
    if (get_transient($transient_key) == true) {
        if (get_option( "_transient_timeout_$transient_key") == 0) {
            // por algum motivo o transiente esta sem tempo, nesse caso vamos eliminar
            delete_transient($transient_key);
            //error_log("$ip_address transient infinito sendo deletado: $transient_key blocked: " . var_export(get_transient($transient_key), true) . "\n", 3, 'wp_anti_hacker_log');
        } else {
            //error_log("$ip_address transient: $transient_key blocked: " . var_export(get_transient($transient_key), true) . "\n", 3, 'wp_anti_hacker_log');
            return true;
        }
    }
    return false;
}

function wpah_add_ip_to_blocked_list($ip_address) {
    $transient_key = 'blocked_ip_' . $ip_address;
    set_transient($transient_key, true, 30);
}

function wpah_block_scanners() {
    global $wp_query;
    $ip = wpah_get_ip_address();

    $is_nikto = wpah_is_nikto_client();
    $is_wpscan = wpah_is_wpscan_client();
    $is_owasp_zap = wpah_is_owasp_zap_client();
    $is_nuclei = wpah_is_nuclei_client();
    $is_not_browser = !wpah_is_browser_user_agent();

    if ($ip == '127.0.0.1' || $ip == '::1') {
        return;
    }

    $debug = "ip: $ip blocked ip: " . var_export(wpah_ip_blocked($ip), true) . " is_browser_user_agent() = ". var_export(wpah_is_browser_user_agent(), true) . " is_wpscan_client: " . var_export($is_wpscan, true) . " is_owasp_zap_client(): " . var_export($is_owasp_zap, true) . " is_nikto: " . var_export($is_nikto, true) . " is_nuclei: " . var_export($is_nuclei, true). " url: " . sanitize_url($_SERVER['REQUEST_URI']) . " Agent: ". $_SERVER['HTTP_USER_AGENT'] . "\n";
    //error_log("$debug: $$debug \n", 3, 'wp_anti_hacker_log.txt');

    if (wpah_ip_blocked($ip)) {
        nocache_headers();
        $wp_query->set_403();
        header( 'HTTP/1.1 403 Forbidden' ); // retorna o status de erro 403
        wp_die( WPAH_SCANNER_MSG ); 
    }

    if ($is_not_browser || $is_wpscan || $is_owasp_zap || $is_nikto || $is_nuclei) {
        if ($is_not_browser) {
            wpah_add_log("Access by bot or scanner detected", "Was detected an access by unknown browser in url " . sanitize_url($_SERVER['REQUEST_URI']) . " using the User Agent " . sanitize_text_field($_SERVER['HTTP_USER_AGENT']) . ".");
        }
        if ($is_wpscan) {
            $wp_scan_msg = "Was detected an access wp_scan in url " . sanitize_url($_SERVER['REQUEST_URI']) . ". wpscan is a wordpress vulnerability and exploitation tool used by hackers.";
            if (get_option('wpah_config_syslog', 999)) {
                wpah_send_syslog(get_option('wpah_config_syslog', 999), $wp_scan_msg);
            }

            wpah_add_log("Access by wp_scan detected", $wp_scan_msg);
        }
        if ($is_owasp_zap) {
            $owasp_zap_msg = "Was detected an access by Owasp Zap Scanner in url " . sanitize_url($_SERVER['REQUEST_URI']) . ". Owasp Zap is a vulnerability scanner used by hackers.";
            if (get_option('wpah_config_syslog', 999)) {
                wpah_send_syslog(get_option('wpah_config_syslog', 999), $owasp_zap_msg);
            }
            wpah_add_log("Access by Owasp Zap detected", $owasp_zap_msg);
        }
        if ($is_nuclei) {
            $nuclei_msg = "Was detected an access by Nuclei Scanner in url " . sanitize_url($_SERVER['REQUEST_URI']) . ". Nuclei is a vulnerability scanner used by hackers.";
            if (get_option('wpah_config_syslog', 999)) {
                wpah_send_syslog(get_option('wpah_config_syslog', 999), $nuclei_msg);
            }            
            wpah_add_log("Access by Nuclei detected", $nuclei_msg);
        }
        if ($is_nikto) {
            $nikto_msg = "Was detected an access by Nikto Scanner in url " . sanitize_url($_SERVER['REQUEST_URI']) . ". Nikto is a vulnerability scanner used by hackers.";
            if (get_option('wpah_config_syslog', 999)) {
                wpah_send_syslog(get_option('wpah_config_syslog', 999), $nikto_msg);
            }            
            wpah_add_log("Access by Nikto detected", $nikto_msg);
        }

        wpah_add_ip_to_blocked_list($ip);

        $should_ignore = false;
        $ignore_list = array('python-requests', 'NsToolsBot', 'okhttp', 'LWP::Simple', 'Expanse', 'omgili');
        foreach ($ignore_list as $ignore_word) {
            if (strpos(sanitize_text_field($_SERVER['HTTP_USER_AGENT']), $ignore_word) !== false) {
                //$should_ignore = true;
            }
        }
        if (strlen(sanitize_text_field($_SERVER['HTTP_USER_AGENT'])) > 1 && $should_ignore == false) {
            $server_vars = print_r($_SERVER, true);
        }
        nocache_headers();

        $wp_query->set_403();
        header( 'HTTP/1.1 403 Forbidden' ); // retorna o status de erro 403
        wp_die( WPAH_SCANNER_MSG ); 
    }
}
