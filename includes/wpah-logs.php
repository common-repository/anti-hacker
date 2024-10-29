<?php

require_once "wpah-ip.php";

function wpah_add_log($event, $description) {
    global $wpdb;
    $wpdb->show_errors();

    $ip = wpah_get_ip_address();
    $table_name = $wpdb->prefix . 'wpah_logs';

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id int(11) NOT NULL AUTO_INCREMENT,
        ip text NOT NULL,
        event text NOT NULL,
        description text NOT NULL,        
        created_at datetime NOT NULL,
        PRIMARY KEY  (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $wpdb->query($sql);

    // Cria um array de dados a serem inseridos na tabela.
    $data = array(
        'ip' => $ip,
        'event' => $event,
        'description' => $description,
        'created_at' => current_time('mysql'), // Salva a data e hora em que o registro foi criado.
    );
    $format = array('%s', '%s', '%s', '%s');

    // Insere os dados na tabela.
    $wpdb->insert($table_name, $data, $format);

    // Verifica se a tabela tem mais de 50 linhas.
    $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    if ($count > 50) {
        // Se houver mais de 50 linhas, exclui as mais antigas até que haja no máximo 50 linhas.
        $num_to_delete = $count - 50;
        $wpdb->query("DELETE FROM $table_name ORDER BY created_at ASC LIMIT $num_to_delete");
    }
}

function wpah_get_logs() {
    global $wpdb;

    // Define o nome da tabela que você deseja usar. Substitua "minha_tabela" pelo nome da sua tabela.
    $table_name = $wpdb->prefix . 'wpah_logs';

    // Consulta os registros na tabela e retorna um array de resultados.
    $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");

    // Retorna o array de resultados.
    return $results;
}

function wpah_create_html_table($data) {
    $html = '<table style="border-collapse: collapse; border: 1px solid black;">';
    $html .= '<thead><tr><th style="padding: 10px; border: 1px solid black; width: 15%;">Date</th><th style="padding: 10px; border: 1px solid black; width: 15%;">IP</th><th style="padding: 10px; border: 1px solid black; width: 30%;">Event</th><th style="padding: 10px; border: 1px solid black; width: 40%;">Description</th></tr></thead>';
    $html .= '<tbody>';
    foreach ($data as $row) {
        $html .= '<tr>';
        $html .= '<td style="padding: 10px; border: 1px solid black;">' . $row->created_at . '</td>';
        $html .= '<td style="padding: 10px; border: 1px solid black;">' . $row->ip . '</td>';
        $html .= '<td style="padding: 10px; border: 1px solid black;">' . $row->event . '</td>';
        $html .= '<td style="padding: 10px; border: 1px solid black;">' . sanitize_text_field($row->description) . '</td>';
        $html .= '</tr>';
    }
    $html .= '</tbody></table>';

    return $html;
}
