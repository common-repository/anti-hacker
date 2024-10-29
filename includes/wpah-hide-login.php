<?php
require_once "wpah-messages.php";
require_once "wpah-logs.php";

$wp_login_php = false;

function wpah_admin_notices_plugin_conflict() {
    echo '<div class="error notice is-dismissible"><p>' . __( 'WP Anti-Hacker Hide Login feature could not be activated because you already have Rename wp-login.php or wps Hide Login active. Please deactivate those plugins to use WP Anti-Hacker Hide Login feature', 'wp-anti-hacker' ) . '</p></div>';
}


function wpah_has_incompatible_plugin() {
    if ( is_multisite() && ! function_exists( 'is_plugin_active_for_network' ) || ! function_exists( 'is_plugin_active' ) ) {
        require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

    }
    $incompatible_plugins = array(
        'rename-wp-admin-login/rename-wp-admin-login.php',
        'wps-hide-login/wps-hide-login.php'
    );

    foreach ($incompatible_plugins as $plugin_path) {
        if (is_plugin_active($plugin_path)) {
            //error_log("is_plugin_active: $plugin_path\n", 3, 'debug.log');

            if (is_multisite()) {
                //deactivate_plugins(plugin_basename(__FILE__), true);
                add_action('network_admin_notices', 'wpah_admin_notices_plugin_conflict');
            } else {
                //deactivate_plugins(plugin_basename(__FILE__));
                add_action('admin_notices', 'wpah_admin_notices_plugin_conflict');
            }

            if (isset($_GET['activate'])) {
                unset($_GET['activate']);
            }

            // nos testes nao apresentou problema, por enquanto vou deixar rodando junto com outros plugins de alteracao de wp-admin
            return false;
        }
    }

    return false;
}

// verifica se a configuração permalink_structure esta utlizando / no final
function wpah_use_trailing_slashes() {
    return '/' === substr( get_option( 'permalink_structure' ), -1, 1 );
}

// se use_trailing_slashes retorna true adiciona / no final da string, se nao remove
function wpah_user_trailingslashit( $string ) {
    return wpah_use_trailing_slashes() ? trailingslashit( $string ) : untrailingslashit( $string );
}

function wpah_init_hide_login() {
    if (wpah_has_incompatible_plugin() == false) {
        add_action( 'plugins_loaded', 'wpah_plugins_loaded', 1 );
        add_action( 'wp_loaded', 'wpah_wp_loaded' );
        add_filter( 'site_url', 'wpah_site_url', 10, 4 );
        add_filter( 'network_site_url', 'wpah_network_site_url', 10, 3 );
        add_filter( 'wp_redirect', 'wpah_wp_redirect', 10, 2 );
    }
}

function wpah_wp_template_loader() {
    global $current_page;

    $current_page = 'index.php';

    if ( ! defined( 'WP_USE_THEMES' ) ) {
        define( 'WP_USE_THEMES', true );
    }

    wp();

    if ( $_SERVER['REQUEST_URI'] === wpah_user_trailingslashit( str_repeat( '-/', 10 ) ) ) {
        $_SERVER['REQUEST_URI'] = wpah_user_trailingslashit( '/wp-login-php/' );
    }

    require_once( ABSPATH . WPINC . '/template-loader.php' );

    die;
}

function wpah_plugins_loaded() {
    global $current_page, $wp_login_php;

    if ( ! is_multisite()
         && ( strpos( rawurldecode( $_SERVER['REQUEST_URI'] ), 'wp-signup' ) !== false
              || strpos( rawurldecode( $_SERVER['REQUEST_URI'] ), 'wp-activate' ) !== false ) ) {

        wp_die( __( 'This feature is not enabled.', 'wp-anti-hacker' ) );

    }

    $request = parse_url( rawurldecode( $_SERVER['REQUEST_URI'] ) );

    if ( ( strpos( rawurldecode( $_SERVER['REQUEST_URI'] ), 'wp-login.php' ) !== false
           || ( isset( $request['path'] ) && untrailingslashit( $request['path'] ) === site_url( 'wp-login', 'relative' ) ) )
         && ! is_admin() ) {

        $wp_login_php = true;

        $_SERVER['REQUEST_URI'] = wpah_user_trailingslashit( '/' . str_repeat( '-/', 10 ) );

        $current_page = 'index.php';

    } elseif ( ( isset( $request['path'] ) && untrailingslashit( $request['path'] ) === home_url( wpah_new_login_slug(), 'relative' ) )
               || ( ! get_option( 'permalink_structure' )
                    && isset( $_GET[ wpah_new_login_slug() ] )
                    && empty( $_GET[ wpah_new_login_slug() ] ) ) ) {

        $current_page = 'wp-login.php';

    } elseif ( ( strpos( rawurldecode( $_SERVER['REQUEST_URI'] ), 'wp-register.php' ) !== false
                 || ( isset( $request['path'] ) && untrailingslashit( $request['path'] ) === site_url( 'wp-register', 'relative' ) ) )
               && ! is_admin() ) {

        $wp_login_php = true;

        $_SERVER['REQUEST_URI'] = user_trailingslashit( '/' . str_repeat( '-/', 10 ) );

        $current_page = 'index.php';
    }

}

function wpah_wp_loaded() {
    global $current_page, $wp_login_php;

    if ( is_admin() && ! is_user_logged_in() && ! defined( 'DOING_AJAX' ) ) {
        if (get_option('wpah_config_convert_404_to_200')) {
            status_header(200);
        } else {
            status_header(404);
        }

        nocache_headers();
        die();
    }

    $request = parse_url( rawurldecode( $_SERVER['REQUEST_URI'] ) );

    if (
        $current_page === 'wp-login.php' &&
        $request['path'] !== wpah_user_trailingslashit( $request['path'] ) &&
        get_option( 'permalink_structure' )
    ) {
        wp_safe_redirect( wpah_user_trailingslashit( wpah_new_login_url() ) . ( ! empty( $_SERVER['QUERY_STRING'] ) ? '?' . $_SERVER['QUERY_STRING'] : '' ) );
        die;
    } elseif ( $wp_login_php ) {
        if (
            ( $referer = wp_get_referer() ) &&
            strpos( $referer, 'wp-activate.php' ) !== false &&
            ( $referer = parse_url( $referer ) ) &&
            ! empty( $referer['query'] )
        ) {
            parse_str( $referer['query'], $referer );

            if (
                ! empty( $referer['key'] ) &&
                ( $result = wpmu_activate_signup( $referer['key'] ) ) &&
                is_wp_error( $result ) && (
                    $result->get_error_code() === 'already_active' ||
                    $result->get_error_code() === 'blog_taken'
            ) ) {
                wp_safe_redirect( wpah_new_login_url() . ( ! empty( $_SERVER['QUERY_STRING'] ) ? '?' . $_SERVER['QUERY_STRING'] : '' ) );
                die;
            }
        }

        wpah_wp_template_loader();
    } elseif ( $current_page === 'wp-login.php' ) {
        global $error, $interim_login, $action, $user_login;

        @require_once ABSPATH . 'wp-login.php';

        die;
    }
}

function wpah_site_url( $url, $path, $scheme, $blog_id ) {
    return wpah_filter_wp_login_php( $url, $scheme );
}

function wpah_network_site_url( $url, $path, $scheme ) {
    return wpah_filter_wp_login_php( $url, $scheme );
}

function wpah_wp_redirect( $location, $status ) {
    return wpah_filter_wp_login_php( $location );
}

function wpah_new_login_slug() {
    if (
        ( $slug = get_option( 'wpah_config_wp_admin_new_url' ) ) || (
            is_multisite() &&
            is_plugin_active_for_network( plugin_basename( __FILE__ ) ) &&
            ( $slug = get_site_option( 'wpah_config_wp_admin_new_url', 'login' ) )
        ) ||
        ( $slug = 'login' )
    ) {
        return $slug;
    }
}

function wpah_new_login_url( $scheme = null ) {
    if ( get_option( 'permalink_structure' ) ) {
        return wpah_user_trailingslashit( home_url( '/', $scheme ) . wpah_new_login_slug() );
    } else {
        return home_url( '/', $scheme ) . '?' . wpah_new_login_slug();
    }
}

// rwal_redirect_field url a ser direcionada quando acessar wp-login
// rwal_redirect_field
// rwal-page-input name input nova url para acessar wp-login
// rwal_page id do input da nova url
function wpah_filter_wp_login_php( $url, $scheme = null ) {
    if ( strpos( $url, 'wp-login.php' ) !== false ) {
        if ( is_ssl() ) {
            $scheme = 'https';
        }

        $args = explode( '?', $url );

        if ( isset( $args[1] ) ) {
            parse_str( $args[1], $args );
            $url = add_query_arg( $args, wpah_new_login_url( $scheme ) );
        } else {
            $url = wpah_new_login_url( $scheme );
        }
    }

    return $url;
}
