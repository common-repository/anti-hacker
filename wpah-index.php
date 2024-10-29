<?php
/*
    Copyright (c) 2016 - 2022, WPPlugins.
    The copyrights to the software code in this file are licensed under the (revised) BSD open source license.

    Plugin Name: Anti-Hacker, hide admin and WAF
    Plugin URI: http://wordpress.org/plugins/wp-anti-hacker/
    Description: This plugin protects your Wordpress against hackers attacks, hiding sensitive information that would be used to exploit your site.  
    Tags: hacker,security,firewall,hide,antivirus,wp-login,wp-admin,hide wordpress,hide wp,security plugin,vulnerability,scanner,brute-force
    Version: 0.6.3
    Author: AHT Security
    Author URI: https://ahtsecurity.com
    License: GPLv2 or later
    License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
    Text Domain: hide-my-wp
    Domain Path: /languages
    Network: true
    Requires at least: 4.3
    Tested up to: 6.4.2
    Requires PHP: 5.6
*/

require_once plugin_dir_path(__FILE__) . "includes/wpah-logs.php";
require_once plugin_dir_path(__FILE__) . "includes/wpah-vulnerabilities.php";
require_once plugin_dir_path(__FILE__) . 'view/wpah-settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/wpah-brute-force.php';
require_once plugin_dir_path(__FILE__) . 'includes/wpah-header-protection.php';
require_once plugin_dir_path(__FILE__) . 'includes/wpah-wordpress-protection.php';
require_once plugin_dir_path(__FILE__) . 'includes/wpah-htaccess.php';
require_once plugin_dir_path(__FILE__) . 'includes/wpah-ip.php';
require_once plugin_dir_path(__FILE__) . 'includes/wpah-injection-protection.php';
require_once plugin_dir_path(__FILE__) . 'includes/wpah-scan-protection.php';
require_once plugin_dir_path(__FILE__) . 'includes/wpah-hide-login.php';

/*
List of current features:
* hide version and HTTP headers used to identify wordpress version	
* hide Wordpress default dir names used by wpscan, cmseek and wapanalyzer to detect wordpress	
* Disable XML-RPC	
* Add HTTP Header level security layer	
* disable directory listing	
* Convert 404 status code to 200(avoid scanner enumerate files)	
* Block access by anonymous proxy	
* Block access by TOR network	
* Detect and block XSS,Sql Injection,Cmd and transversal directory
* Block vulnerability scanner based in behavior and unknown user agent	
* Block IP when brute force login failures more then X
* Option to hide wp-admin changing its name to new one
* Detection of sensitive files exposed
*/


add_action( 'init', 'wpah_load_config_options' );

function wpah_load_config_options() {
  $config_file = plugin_dir_path( __FILE__ ) . 'config.json';
  if ( file_exists( $config_file ) ) {
    $config_data = json_decode( file_get_contents( $config_file ), true );

    // carrega as opções do arquivo config.json
    if ( isset( $config_data['wpah_config_block_version'] ) ) {
      update_option( 'wpah_config_block_version', $config_data['wpah_config_block_version'] );
    }
    if ( isset( $config_data['wpah_config_block_fingerprint'] ) ) {
      update_option( 'wpah_config_block_fingerprint', $config_data['wpah_config_block_fingerprint'] );
    }
    if ( isset( $config_data['wpah_config_block_rpc'] ) ) {
      update_option( 'wpah_config_block_rpc', $config_data['wpah_config_block_rpc'] );
    }
    if ( isset( $config_data['wpah_config_add_security_header'] ) ) {
      update_option( 'wpah_config_add_security_header', $config_data['wpah_config_add_security_header'] );
    }
    if ( isset( $config_data['wpah_config_block_directory_listing'] ) ) {
      update_option( 'wpah_config_block_directory_listing', $config_data['wpah_config_block_directory_listing'] );
    }
    if ( isset( $config_data['wpah_config_convert_404_to_200'] ) ) {
      update_option( 'wpah_config_convert_404_to_200', $config_data['wpah_config_convert_404_to_200'] );
    }
    if ( isset( $config_data['wpah_config_block_proxy'] ) ) {
      update_option( 'wpah_config_block_proxy', $config_data['wpah_config_block_proxy'] );
    }
    if ( isset( $config_data['wpah_config_block_tor'] ) ) {
      update_option( 'wpah_config_block_tor', $config_data['wpah_config_block_tor'] );
    }
    if ( isset( $config_data['wpah_config_block_injection'] ) ) {
      update_option( 'wpah_config_block_injection', $config_data['wpah_config_block_injection'] );
    }
    if ( isset( $config_data['wpah_config_block_scanner'] ) ) {
      update_option( 'wpah_config_block_scanner', $config_data['wpah_config_block_scanner'] );
    }
    if ( isset( $config_data['wpah_config_block_brute_force'] ) ) {
      update_option( 'wpah_config_block_brute_force', $config_data['wpah_config_block_brute_force'] );
    }
    if ( isset( $config_data['wpah_config_apikey'] ) ) {
      update_option( 'wpah_config_apikey', $config_data['wpah_config_apikey'] );
    }
    if ( isset( $config_data['wpah_config_add_footer'] ) ) {
      update_option( 'wpah_config_add_footer', $config_data['wpah_config_add_footer'] );
    }
  }
}

// check for activated protections
if (!is_admin()) {  

    if (get_option('wpah_config_block_version')) {
        wpah_remover_header_and_version();
    }

    if (get_option('wpah_config_block_fingerprint')) {
        wpah_rename_fingerprints();
        wpah_remove_php_fingerprints();
    }

    if (get_option( 'wpah_config_block_rpc' )) {
        wpah_disable_xmlrpc();
    }

    if (get_option('wpah_config_add_security_header')) {
        wpah_add_security_headers();
    }

    if (get_option('wpah_config_block_directory_listing')) {
        wpah_disable_directory_listing();
    }

    if (get_option('wpah_config_convert_404_to_200')) {
        add_action('template_redirect', 'wpah_transform_404_to_200');
    }

    if (get_option('wpah_config_block_proxy')) {
        add_action( 'wp', 'wpah_blacklist_ip_proxy' ); 
    }

    if (get_option('wpah_config_block_tor')) {
        add_action( 'wp', 'wpah_blacklist_ip_tor' ); 
    }

    if (get_option('wpah_config_block_scanner')) {
        add_action( 'wp', 'wpah_block_scanners' );
    }
}

if (get_option('wpah_config_block_injection')) {  
    add_action( 'init', 'wpah_block_injection' );
    add_action('admin_init', 'wpah_block_injection');
}

if (get_option('wpah_config_wp_admin_new_url')) {  
    wpah_init_hide_login();
}

WpahLoginAttemptsLimiter::init(get_option('wpah_config_block_brute_force', 999));
if (get_option('wpah_config_block_brute_force', 0) > 0) {
    WpahLoginAttemptsLimiter::enable();
} else {
    WpahLoginAttemptsLimiter::disable();
}


//** Adiciona selo de protecao WP Anti Hacker no foot de todas as paginas */
function wpah_custom_footer_text() {
    echo '<div class="plugin-logo" style="display: flex; align-items: center; width: 100%; justify-content: center;">
    <p style="line-height: 300px; text-align: center; font-family: sans-serif; font-size: 8pt;">
      <a href="http://www.ahtsecurity.com" target="_blank">
      <img width="32" height="32" src="' . plugin_dir_url(__FILE__) . 'view/img/icon_80x80.jpg" alt="Protected by WP Anti-Hacker">
      Protected by WP Anti-Hacker </a>
    </p>
    </div>';
}
if (get_option('wpah_config_add_footer')) {  
  add_action( 'wp_footer', 'wpah_custom_footer_text' );
}

/**
 * adiciona menu settings na listagem de plugins
 */
function wpah_plugin_list_settings( $links ) {
	$settings_link = '<a href="options-general.php?page=wpah-settings.php">Settings</a>';
	array_unshift( $links, $settings_link );
	return $links;
}
$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_$plugin", 'wpah_plugin_list_settings' );



add_action( 'admin_init', 'wpah_settings_html_options' );

/**
 * admin menu functions
 */
function wpah_settings_html() {
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form action="options.php" method="post">
			<?php
			settings_fields( 'wpah_config_group' );
			do_settings_sections( 'wpah_config_group' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

function wpah_add_plugin_link()
{
      add_menu_page(
        'Settings', // page_title
        'Anti-Hacker', // menu_title
        'manage_options', // capability
        'wpah-settings.php', // menu_slug
        'wpah_settings_html' // callback 
    );

    add_submenu_page(
		'wpah-settings.php', // parent_slug
		'Settings', // page_title
		'Settings', // menu_title
		'manage_options', // capability
		'wpah-settings.php', // menu_slug
		'wpah_settings_html' // callback  
    );

    add_submenu_page(
		'wpah-settings.php',
		'Logs',
		'Logs',
		'manage_options',
		'wpah-logs.php',
		'wpah_logs_page_callback' );

    add_submenu_page(
      'wpah-settings.php',
      'Sensitive files',
      'Sensitive files',
      'manage_options',
      'wpah-sensitive_files.php',
      'wpah_sensitive_files_callback' );
  
}

add_action('admin_menu', 'wpah_add_plugin_link');

function wpah_admin_style() {
    wp_enqueue_style('admin-styles', plugin_dir_url( __FILE__ ) .'view/assets/style.css');
}
add_action('admin_enqueue_scripts', 'wpah_admin_style');

