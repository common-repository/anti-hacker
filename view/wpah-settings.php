<?php

function wpah_should_active_feature($feature_config_group) {
	if (strlen(get_option( 'wpah_config_apikey' )) > 1) {
		return true;
	}
	$advanced_features = array('wpah_config_block_brute_force' => true,
							   'wpah_config_block_version' => false,
							   'wpah_config_block_fingerprint' => false,
							   'wpah_config_block_rpc' => false,
							   'wpah_config_add_security_header' => false,
							   'wpah_config_block_directory_listing' => false,
							   'wpah_config_convert_404_to_200' => false,
							   'wpah_config_block_proxy' => true,
							   'wpah_config_block_tor' => true,
							   'wpah_config_block_injection' => true,
							   'wpah_config_block_scanner' => true,
							   'wpah_config_detect_malware' => true,
							   'wpah_config_detect_vulnerability_component' => true,
							   'wpah_config_add_footer' => false,
							   'wpah_config_wp_admin_new_url' => false,
							);
						
	if ($advanced_features[$feature_config_group]) {
		delete_option($feature_config_group);
	}
	return $advanced_features[$feature_config_group] == false;
}

function wpah_boolean_protection_config($config_name, $config_title) {
    register_setting( 'wpah_config_group', $config_name, '' );
    add_settings_field( $config_name, $config_title,
        function( $args )  use ($config_name, $config_title) {
            $value = get_option( $config_name );
            //echo '<input type="checkbox" id="' . $args['label_for'] . '" name="' . $config_name . '" value="1" ' . checked( 1, $value, false ) . ' />';
			$should_disable = wpah_should_active_feature($config_name) == false ? 'disabled' : '';

            echo '<label class="switch"> 
                <input ' . $should_disable . ' type="checkbox"  id="' . $args['label_for'] . '" name="' . $config_name . '" value="1" ' . checked( 1, $value, false ) . ' />
                <span class="slider round"></span>
            </label>';  
        },
        'wpah_config_group', 'minha_secao', 
        ['label_for' => $config_name, 'class' => 'check-box-class'] 
    );
}

// forbidden_slugs
function wpah_forbidden_new_urls() {
    $wp = new WP;
    return array_merge( $wp->public_query_vars, $wp->private_query_vars );
}

function wpah_validate_new_url($value) {
	$forbidden_urls = wpah_forbidden_new_urls();
    return !in_array($value, $forbidden_urls) && wpah_has_incompatible_plugin() == false;
}

function wpah_validate_apikey($apikey) {
	//$api_url = 'http://127.0.0.1/site/api/anti-hacker.php';
	$api_url = 'https://ahtsecurity.com/api/anti-hacker.php';
	$url = get_site_url();

	// Define os parâmetros para enviar via POST
	$params = array(
		'apikey' => $apikey,
		'url' => $url
	);

	// Realiza a consulta via POST
	$response = wp_remote_post( $api_url, array(
		'body' => $params,
		'timeout' => 30
	) );

	$body = wp_remote_retrieve_body( $response );

	// Verifica se a consulta foi bem-sucedida
	if ( is_wp_error( $response ) ) {
		return false;
	}

	// Decodifica a resposta JSON
	$result = json_decode( $response['body'], true );

	// Verifica o status da resposta
	if ( $result['status'] == 1 ) {
		return true;
	}

	return false;
}

function wpah_validate_syslog() {
	return true;
}

function wpah_sensitive_files_callback() {
	echo '<div class="wrap"><div id="icon-tools" class="icon32"></div>';
		echo '<h2>Detect sensitive files exposed</h2>';
		$result_table = wpah_scan_sensitive_files();
		echo "<div> $result_table </div>";
	echo '</div>';
}

function wpah_logs_page_callback() {
	echo '<div class="wrap"><div id="icon-tools" class="icon32"></div>';
		echo '<h2>Blocked Events</h2>';
		$logs = wpah_get_logs();
		$logs_table = wpah_create_html_table($logs);
		echo "<div> $logs_table </div>";
	echo '</div>';
}

function wpah_settings_html_options() {    
    add_settings_section(
		'minha_secao',
		'',
		'',
		'wpah_config_group'
	);

    wpah_boolean_protection_config('wpah_config_block_version', 'Protect Wordpress version and HTTP headers');
    wpah_boolean_protection_config('wpah_config_block_fingerprint', 'Protect Wordpress indentification(fingerprint)');

	wpah_boolean_protection_config('wpah_config_block_rpc', 'Disable XML-RPC');
    wpah_boolean_protection_config('wpah_config_add_security_header', 'Add HTTP Header level security layer');

    wpah_boolean_protection_config('wpah_config_block_directory_listing', 'Protect directory listing (apache)');

    wpah_boolean_protection_config('wpah_config_convert_404_to_200', 'Convert 404 status code to 200(avoid scanner enumerate files)');
	
    wpah_boolean_protection_config('wpah_config_block_proxy', 'Block access by anonymous proxy');
    wpah_boolean_protection_config('wpah_config_block_tor', 'Block access by TOR network');

    wpah_boolean_protection_config('wpah_config_block_injection', 'Injection Protection (XSS,Sql Injection,Cmd)');

    wpah_boolean_protection_config('wpah_config_block_scanner', 'Block vulnerability scanner');
	
    wpah_boolean_protection_config('wpah_config_add_footer', 'Add protected by Anti-Hacker footer');
	//boolean_protection_config('wpah_config_block_default_files', 'Protect default files to be accessed');

    register_setting( 'wpah_config_group', 'wpah_config_block_brute_force', '' );
    add_settings_field(
		'wpah_config_block_brute_force', // id unico da configuracao
		'Block IP when brute force login failures more then', // titulo ou rotulo
		function( $args ) {
			$should_disable = wpah_should_active_feature('wpah_config_block_brute_force') == false ? 'disabled' : '';
			$options = get_option('wpah_config_block_brute_force', 5);
			?>
			<input
				type="text"
				id="<?php echo esc_attr( $args['label_for'] ); ?>"
				name="wpah_config_block_brute_force"
				value="<?php echo esc_attr( $options ); ?>" <?php echo $should_disable; ?>>
			<?php
		}, // funcao que sera usada para exibir o camo
		'wpah_config_group', // nome da pagina de opcao
		'minha_secao', // nome da sessao na pagina de configuração onde a opcao vai ser exibida
		[
			'label_for' => 'wpah_config_apikey_html_id',
			'class'     => 'classe-html-tr',
		]
	);// parametro opcional que vai ser passado para funcao de retorno


	register_setting(
		'wpah_config_group', // option group
		'wpah_config_wp_admin_new_url', // option name
		array(
			'sanitize_callback' => function( $value ) {
				// se a apikey nao mudou e ja esta salva nao precisamos verificar novamente
				if ($value != get_option( 'wpah_config_wp_admin_new_url' )) {
					if (wpah_validate_new_url($value)) {
						//$custom_message = '<p> APIKEY registration success!</p>';
						add_settings_error(
							'wpah_config_wp_admin_new_url',
							'wpah_config_wp_admin_new_url_success',
							'New login url defined!'
						);
	
						return $value;
					}
					//$custom_message = '<p> Invalid APIKEY.</p>';
					add_settings_error(
						'wpah_config_wp_admin_new_url',
						'wpah_config_wp_admin_new_url_erro',
						'Invalid new login URL.',
						'error'
					);

					return '';
				}
				return $value;
			},
		)
	);
	add_settings_field(
		'wpah_config_wp_admin_new_url', // id unico da configuracao
		'New URL for wp-admin:', // titulo ou rotulo
		function( $args ) {
			$options = get_option( 'wpah_config_wp_admin_new_url' );
			?>
			<input
				type="text"
				id="<?php echo esc_attr( $args['label_for'] ); ?>"
				name="wpah_config_wp_admin_new_url"
				value="<?php echo esc_attr( $options ); ?>"> 
			<?php
			echo "(empty for default)";
			
			//echo $custom_message;
		}, // funcao que sera usada para exibir o camo
		'wpah_config_group', // nome da pagina de opcao
		'minha_secao', // nome da sessao na pagina de configuração onde a opcao vai ser exibida
		[
			'label_for' => 'wpah_config_wp_admin_new_url_html_id',
			'class'     => 'classe-html-tr',
		]
	);// parametro opcional que vai ser passado para funcao de retorno
	settings_errors('wpah_config_wp_admin_new_url');


	
	register_setting(
		'wpah_config_group', // option group
		'wpah_config_apikey', // option name
		array(
			'sanitize_callback' => function( $value ) {
				// se a apikey nao mudou e ja esta salva nao precisamos verificar novamente
				if ($value != get_option( 'wpah_config_apikey' )) {
					if (wpah_validate_apikey($value)) {
						//$custom_message = '<p> APIKEY registration success!</p>';
						add_settings_error(
							'wpah_config_apikey',
							'wpah_config_apikey_success',
							'APIKEY registration success!'
						);
	
						return $value;
					}
					//$custom_message = '<p> Invalid APIKEY.</p>';
					add_settings_error(
						'wpah_config_apikey',
						'wpah_config_apikey_erro',
						'APIKEY is not valid.',
						'error'
					);

					return '';
				}
				return $value;
			},
		)
	);
	add_settings_field(
		'wpah_config_apikey', // id unico da configuracao
		'API KEY', // titulo ou rotulo
		function( $args ) {
			$options = get_option( 'wpah_config_apikey' );
			?>
			<input
				type="text"
				id="<?php echo esc_attr( $args['label_for'] ); ?>"
				name="wpah_config_apikey"
				value="<?php echo esc_attr( $options ); ?>"> 
			<?php
			if (strlen($options) < 1) {
				echo "To get your APIKEY access <a href='https://ahtsecurity.com'  target='_blank'>AHT Security</a>";
			}
			//echo $custom_message;
		}, // funcao que sera usada para exibir o camo
		'wpah_config_group', // nome da pagina de opcao
		'minha_secao', // nome da sessao na pagina de configuração onde a opcao vai ser exibida
		[
			'label_for' => 'wpah_config_apikey_html_id',
			'class'     => 'classe-html-tr',
		]
	);// parametro opcional que vai ser passado para funcao de retorno
	settings_errors('wpah_config_apikey');


	register_setting(
		'wpah_config_group', // option group
		'wpah_config_syslog', // option name
		array(
			'sanitize_callback' => function( $value ) {
				// se a apikey nao mudou e ja esta salva nao precisamos verificar novamente
				if ($value != get_option( 'wpah_config_syslog' )) {
					if (wpah_validate_syslog($value)) {
						//$custom_message = '<p> APIKEY registration success!</p>';
						add_settings_error(
							'wpah_config_syslog',
							'wpah_config_syslog_success',
							'Siem Syslog registration success!'
						);
	
						return $value;
					}
					//$custom_message = '<p> Invalid APIKEY.</p>';
					add_settings_error(
						'wpah_config_syslog',
						'wpah_config_syslog_erro',
						'SIEM Syslog address is not valid.',
						'error'
					);

					return '';
				}
				return $value;
			},
		)
	);
	add_settings_field(
		'wpah_config_syslog', // id unico da configuracao
		'SIEM Syslog Address', // titulo ou rotulo
		function( $args ) {
			$options = get_option( 'wpah_config_syslog' );
			?>
			<input
				type="text"
				id="<?php echo esc_attr( $args['label_for'] ); ?>"
				name="wpah_config_syslog"
				value="<?php echo esc_attr( $options ); ?>"> 
			<?php
			echo "(ip:port or host:port)";
			
			//echo $custom_message;
		}, // funcao que sera usada para exibir o camo
		'wpah_config_group', // nome da pagina de opcao
		'minha_secao', // nome da sessao na pagina de configuração onde a opcao vai ser exibida
		[
			'label_for' => 'wpah_config_syslog_html_id',
			'class'     => 'classe-html-tr',
		]
	);// parametro opcional que vai ser passado para funcao de retorno
	settings_errors('wpah_config_syslog');

}
