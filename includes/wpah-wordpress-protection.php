<?php
require_once 'wpah-htaccess.php';
require_once "wpah-messages.php";

$use_redirect_folder = FALSE;

function wpah_is_user_logged_in() {
    try {
        return is_user_logged_in();
    } catch (Exception $e) {
    }
    return True;
}

function  wpah_is_default_wp_content() {
    return basename(WP_CONTENT_DIR) == 'wp-content';
}

function wpah_remover_header_and_version_callback() {
    if (!is_admin()) {
        remove_action('wp_head', 'wp_generator');
        // remove meta generator from html
        add_filter('the_generator', '__return_false', PHP_INT_MAX, 1);

        remove_action('wp_head', 'wlwmanifest_link');
        
        // remove link in http header
        remove_action('template_redirect', 'rest_output_link_header', 11);

        remove_action('wp_head', 'rsd_link');
        remove_action('xmlrpc_rsd_apis', 'rest_output_rsd');


        remove_action('wp_head', 'feed_links', 2);
        remove_action('wp_head', 'index_rel_link');
        remove_action('wp_head', 'feed_links_extra', 3);
        remove_action('wp_head', 'start_post_rel_link', 10, 0);
        remove_action('wp_head', 'parent_post_rel_link', 10, 0);
        remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0);
        remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
        remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
        remove_action('wp_head', 'feed_links', 2);

    }
}

function wpah_remover_header_and_version() {
    add_action('init', 'wpah_remover_header_and_version_callback');
}


function wpah_replace_wp_content_strings_callback( $content ) {
    global $use_redirect_folder;
    if (!wpah_is_user_logged_in()) {
        if (is_string($content)) {
            if ($use_redirect_folder == FALSE) {
                if (wpah_is_default_wp_content()) {
                    $content = str_replace( 'wp-content', '%77%70%2d%63%6f%6e%74%65%6e%74', $content );
                }
                $content = str_replace( 'wp-includes', '%77%70%2d%69%6e%63%6c%75%64%65%73', $content );
                $content = str_replace( 'wp-json', '%77%70%2d%6a%73%6f%6e', $content );
                $content = str_replace( 'wp-admin', '%77%70%2D%61%64%6D%69%6E', $content );
            } 
            if(strpos($content, 'ver=' . get_bloginfo('version'))) {
                $content = remove_query_arg('ver', $content);
            }
        }
    }
    return $content;
}

function wpah_replace_uoload_dir_names_callback($dir) {
    if (!wpah_is_user_logged_in()) {
        $backtrace = debug_backtrace();
        $i = 0;
        foreach ($backtrace as $trace) {
            if (isset($trace['file'])) {
                if (strpos($trace['file'], 'wp-file-manager') !== false || 
                strpos($trace['file'], 'elementor\core\files\base.php') !== false ||
                strpos($trace['file'], 'sitemaps') !== false ||
                strpos($trace['file'], 'wp-includes\\theme.php') !== false ||
                strpos($trace['file'], 'https-detection.php') !== false ||
                //se nao blouear retorna para protecao, responsavel pelo diretorio 
                // strpos($trace['file'], 'class-cookie-admin.php') !== false ||
                strpos($trace['file'], 'google-site-kit') !== false) {
                    return $dir;
                }
                //error_log('log: ' . $trace['file'] . "\n", 3, 'wp_anti_hacker_log');
            }
            $i = $i + 1;
        }
    }
    return wpah_replace_replace_dir_names_callback($dir);
}

function wpah_replace_replace_dir_names_callback( $dir ) {
    global $use_redirect_folder;
    if (!wpah_is_user_logged_in()) {
        $backtrace = debug_backtrace();
        $i = 0;
        foreach ($backtrace as $trace) {
            if (isset($trace['file'])) {
                if (strpos($trace['file'], 'wp-file-manager') !== false || 
                   strpos($trace['file'], 'elementor\core\files\base.php') !== false ||
                   strpos($trace['file'], 'sitemaps') !== false ||
                   strpos($trace['file'], 'wp-includes\\theme.php') !== false ||
                   strpos($trace['file'], 'https-detection.php') !== false ||
                   //se nao blouear retorna para protecao, responsavel pelo diretorio 
                   // strpos($trace['file'], 'class-cookie-admin.php') !== false ||
                   strpos($trace['file'], 'google-site-kit') !== false) {
                    return $dir;
                }
            }
            $i = $i + 1;
        }

        if( is_array( $dir ) && array_key_exists( 'url', $dir ) ) {
            if ($use_redirect_folder == FALSE) {
                if (wpah_is_default_wp_content()) {
                    $dir['url'] = str_replace( 'wp-content', '%77%70%2d%63%6f%6e%74%65%6e%74', $dir['url'] );
                }
                $dir['url'] = str_replace( 'wp-includes', '%77%70%2d%69%6e%63%6c%75%64%65%73', $dir['url'] );
                $dir['url'] = str_replace( 'wp-json', '%77%70%2d%6a%73%6f%6e', $dir['url'] );
                $dir['url'] = str_replace( 'wp-admin', '%77%70%2D%61%64%6D%69%6E', $dir['url'] );
            } 
        }
        if( is_array( $dir ) && array_key_exists( 'path', $dir ) ) {
            if ($use_redirect_folder == FALSE) {
                if (wpah_is_default_wp_content()) {
                    $dir['path'] = str_replace( 'wp-content', '%77%70%2d%63%6f%6e%74%65%6e%74', $dir['path'] );
                }
                $dir['path'] = str_replace( 'wp-includes', '%77%70%2d%69%6e%63%6c%75%64%65%73', $dir['path'] );
                $dir['path'] = str_replace( 'wp-json', '%77%70%2d%6a%73%6f%6e', $dir['path'] );
                $dir['path'] = str_replace( 'wp-admin', '%77%70%2D%61%64%6D%69%6E', $dir['path'] );
            } 
        }
        if( is_array( $dir ) && array_key_exists( 'basedir', $dir ) ) {
            if ($use_redirect_folder == FALSE) {
                if (wpah_is_default_wp_content()) {
                    $dir['basedir'] = str_replace( 'wp-content', '%77%70%2d%63%6f%6e%74%65%6e%74', $dir['basedir'] );
                }
                $dir['basedir'] = str_replace( 'wp-includes', '%77%70%2d%69%6e%63%6c%75%64%65%73', $dir['basedir'] );
                $dir['basedir'] = str_replace( 'wp-json', '%77%70%2d%6a%73%6f%6e', $dir['basedir'] );
                $dir['basedir'] = str_replace( 'wp-admin', '%77%70%2D%61%64%6D%69%6E', $dir['basedir'] );
            } 
        }
        if( is_array( $dir ) && array_key_exists( 'baseurl', $dir ) ) {
            if ($use_redirect_folder == FALSE) {
                if (wpah_is_default_wp_content()) {
                    $dir['baseurl'] = str_replace( 'wp-content', '%77%70%2d%63%6f%6e%74%65%6e%74', $dir['baseurl'] );
                }
                $dir['baseurl'] = str_replace( 'wp-includes', '%77%70%2d%69%6e%63%6c%75%64%65%73', $dir['baseurl'] );
                $dir['baseurl'] = str_replace( 'wp-json', '%77%70%2d%6a%73%6f%6e', $dir['baseurl'] );
                $dir['baseurl'] = str_replace( 'wp-admin', '%77%70%2D%61%64%6D%69%6E', $dir['baseurl'] );
            } 
        }
        if(is_string( $dir)) {
            if ($use_redirect_folder == FALSE) {
                if (wpah_is_default_wp_content()) {
                    $dir = str_replace( 'wp-content', '%77%70%2d%63%6f%6e%74%65%6e%74', $dir );
                }
                $dir = str_replace( 'wp-includes', '%77%70%2d%69%6e%63%6c%75%64%65%73', $dir );
                $dir = str_replace( 'wp-json', '%77%70%2d%6a%73%6f%6e', $dir );
                $dir = str_replace( 'wp-admin', '%77%70%2D%61%64%6D%69%6E', $dir );
            } 
        }
    }
    return $dir;
}

function wpah_custom_plugins_url($url, $path, $plugin) {
    global $use_redirect_folder;
    if (!is_admin()) {
        if ($use_redirect_folder == FALSE) {
            if (wpah_is_default_wp_content()) {
                $url = str_replace('wp-content', '%77%70%2d%63%6f%6e%74%65%6e%74', $url);
            }
            $url = str_replace( 'wp-includes', '%77%70%2d%69%6e%63%6c%75%64%65%73', $url );
            $url = str_replace( 'wp-json', '%77%70%2d%6a%73%6f%6e', $url );
            $url = str_replace( 'wp-admin', '%77%70%2D%61%64%6D%69%6E', $url );
        } 
    }

    return $url;
}


function wpah_alter_esc_url( $good_protocol_url, $original_url, $context ) {
    global $use_redirect_folder;
    if (!wpah_is_user_logged_in()) {
        if ($use_redirect_folder == FALSE) {
            if (wpah_is_default_wp_content()) {
                $good_protocol_url = str_replace('wp-content', '%77%70%2d%63%6f%6e%74%65%6e%74', $good_protocol_url );
            }
            $good_protocol_url = str_replace( 'wp-includes', '%77%70%2d%69%6e%63%6c%75%64%65%73', $good_protocol_url );
            $good_protocol_url = str_replace( 'wp-json', '%77%70%2d%6a%73%6f%6e', $good_protocol_url );
            $good_protocol_url = str_replace( 'wp-admin', '%77%70%2d%61%64%6d%69%6e', $good_protocol_url );
        } 
    }
    return $good_protocol_url;
}


function wpah_remove_x_powered_by_header() {
    header_remove( 'X-Powered-By' );
}

function wpah_remove_php_fingerprints() {
    add_action( 'wp_loaded', 'wpah_remove_x_powered_by_header', 999 );
}

function wpah_modify_og_image_meta($image) {
    global $use_redirect_folder;
    if ($use_redirect_folder == FALSE) {
        if (wpah_is_default_wp_content()) {
            $image = str_replace( 'wp-content', '%77%70%2d%63%6f%6e%74%65%6e%74', $image );
        }
        $image = str_replace( 'wp-includes', '%77%70%2d%69%6e%63%6c%75%64%65%73', $image );
        $image = str_replace( 'wp-json', '%77%70%2d%6a%73%6f%6e', $image );
        $image = str_replace( 'wp-admin', '%77%70%2D%61%64%6D%69%6E', $image );
    } 
    return $image;
}

function wpah_replace_yoast($graph) {
    global $use_redirect_folder;
    foreach ($graph as $index => $node) {
        if (isset($node['@type'])) {
            if ($node['@type'] === 'Organization') {
                if ($use_redirect_folder == FALSE) {
                    if (wpah_is_default_wp_content()) {
                        $graph[$index]['logo']['url'] = str_replace('wp-content', '%77%70%2d%63%6f%6e%74%65%6e%74', $graph[$index]['logo']['url']);
                        $graph[$index]['logo']['contentUrl'] = str_replace('wp-content', '%77%70%2d%63%6f%6e%74%65%6e%74', $graph[$index]['logo']['contentUrl']);
                    }
                    $graph[$index]['logo']['url'] = str_replace( 'wp-includes', '%77%70%2d%69%6e%63%6c%75%64%65%73', $graph[$index]['logo']['url'] );
                    $graph[$index]['logo']['url'] = str_replace( 'wp-json', '%77%70%2d%6a%73%6f%6e', $graph[$index]['logo']['url'] );
                    $graph[$index]['logo']['contentUrl'] = str_replace( 'wp-includes', '%77%70%2d%69%6e%63%6c%75%64%65%73', $graph[$index]['logo']['contentUrl'] );
                    $graph[$index]['logo']['contentUrl'] = str_replace( 'wp-json', '%77%70%2d%6a%73%6f%6e', $graph[$index]['logo']['contentUrl'] );
                } 
            } elseif ($node['@type'] === 'WebPage' && array_key_exists( 'thumbnailUrl', $graph[$index] )) {                    
                if ($use_redirect_folder == FALSE) {
                    if (wpah_is_default_wp_content()) {
                        $graph[$index]['thumbnailUrl'] = str_replace('wp-content', '%77%70%2d%63%6f%6e%74%65%6e%74', $graph[$index]['thumbnailUrl']);
                    }
                    $graph[$index]['thumbnailUrl'] = str_replace( 'wp-includes', '%77%70%2d%69%6e%63%6c%75%64%65%73', $graph[$index]['thumbnailUrl'] );
                    $graph[$index]['thumbnailUrl'] = str_replace( 'wp-json', '%77%70%2d%6a%73%6f%6e', $graph[$index]['thumbnailUrl'] );
                } 
            } elseif ($node['@type'] === 'Article' && array_key_exists( 'thumbnailUrl', $graph[$index] )) {                    
                if ($use_redirect_folder == FALSE) {
                    if (wpah_is_default_wp_content()) {
                        $graph[$index]['thumbnailUrl'] = str_replace('wp-content', '%77%70%2d%63%6f%6e%74%65%6e%74', $graph[$index]['thumbnailUrl']);
                    }
                    $graph[$index]['thumbnailUrl'] = str_replace( 'wp-includes', '%77%70%2d%69%6e%63%6c%75%64%65%73', $graph[$index]['thumbnailUrl'] );
                    $graph[$index]['thumbnailUrl'] = str_replace( 'wp-json', '%77%70%2d%6a%73%6f%6e', $graph[$index]['thumbnailUrl'] );
                } 
            } elseif ($node['@type'] === 'ImageObject') {                    
                if ($use_redirect_folder == FALSE) {
                    if (wpah_is_default_wp_content()) {
                        $graph[$index]['url'] = str_replace('wp-content', '%77%70%2d%63%6f%6e%74%65%6e%74', $graph[$index]['url']);
                        $graph[$index]['contentUrl'] = str_replace('wp-content', '%77%70%2d%63%6f%6e%74%65%6e%74', $graph[$index]['contentUrl']);
                    }
                    $graph[$index]['url'] = str_replace( 'wp-includes', '%77%70%2d%69%6e%63%6c%75%64%65%73', $graph[$index]['url'] );
                    $graph[$index]['url'] = str_replace( 'wp-json', '%77%70%2d%6a%73%6f%6e', $graph[$index]['url'] );

                    $graph[$index]['contentUrl'] = str_replace( 'wp-includes', '%77%70%2d%69%6e%63%6c%75%64%65%73', $graph[$index]['contentUrl'] );
                    $graph[$index]['contentUrl'] = str_replace( 'wp-json', '%77%70%2d%6a%73%6f%6e', $graph[$index]['contentUrl'] );
                } 
            }
        }
    }
    return $graph;
}


function wpah_rename_fingerprints() {
    global $use_redirect_folder;

    // remove yoast comments
    add_filter( 'wpseo_debug_markers', '__return_false' );

    add_filter( 'clean_url', 'wpah_alter_esc_url', 10, 3 );
    
    
    // alterou o parametro css_file do javascript do complianz
    // apos comentar parou o problema
    add_filter( 'upload_dir', 'wpah_replace_uoload_dir_names_callback', 1 );

    add_filter( 'content_url', 'wpah_replace_replace_dir_names_callback', 1 );
    add_filter( 'includes_url', 'wpah_replace_replace_dir_names_callback', 1 );
    add_filter( 'plugins_root', 'wpah_replace_replace_dir_names_callback', 1 );
    add_filter( 'plugins_root_uri', 'wpah_replace_replace_dir_names_callback', 1 );
    add_filter( 'home_url', 'wpah_replace_replace_dir_names_callback', 1 );
    add_action( 'the_content_feed', 'wpah_replace_wp_content_strings_callback');
    add_filter( 'the_content', 'wpah_replace_wp_content_strings_callback', 999  );
    
    // aplica alteracao de url nas tags script src
    add_filter( 'script_loader_src', 'wpah_replace_wp_content_strings_callback', 999  );
    // aplica alteracao de url nas tags stylesheet
    add_filter( 'style_loader_src', 'wpah_replace_wp_content_strings_callback', 999  );
    // nao teve reducao2 add_filter( 'template_directory_uri', 'wpah_replace_wp_content_strings_callback', 999  );

    // alterou na imagem que eu printo de dentro do meu plugin
    add_filter('plugins_url', 'wpah_custom_plugins_url', 10, 3);

    add_filter('wpseo_opengraph_image', 'wpah_modify_og_image_meta');

    // aplica alteracao de url no plugin yoast seo
    add_filter( 'wpseo_schema_graph', 'wpah_replace_yoast' );

    // descomentei pr ver se muda
    add_filter( 'media_loader_src', 'wpah_replace_wp_content_strings_callback', 999  );
    add_filter( 'img_size_src_ardi', 'wpah_replace_wp_content_strings_callback', 999  );
    add_filter( 'bloginfo', 'wpah_replace_wp_content_strings_callback', 999 );
    add_filter( 'stylesheet_directory_uri', 'wpah_replace_wp_content_strings_callback', 999 );    
}

 // disable xml-rpc
 function wpah_disable_xmlrpc() {
    add_filter('xmlrpc_enabled', '__return_false');
    add_filter('xmlrpc_methods', function($methods) {
        unset($methods['pingback.ping']);
        unset($methods['pingback.extensions.getPingbacks']);
        return $methods;
    });
    add_action('init', function() {
        if (strpos(sanitize_url($_SERVER['REQUEST_URI']), 'xmlrpc.php') !== false ||
            strpos(sanitize_url($_SERVER['REQUEST_URI']), 'wp-cron.php') !== false ||
            strpos(sanitize_url($_SERVER['REQUEST_URI']), '/wp/v2/users/') !== false) {
            header( 'HTTP/1.0 403 Forbidden' );
            wp_die( WPAH_XMLRPC_MSG );
        }
    });
 }
