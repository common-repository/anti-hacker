<?php

/** 
LOG_EMERG: Mensagem de emergência - sistema inutilizável.
LOG_ALERT: Alerta crítico - ação imediata necessária.
LOG_CRIT: Condição crítica - erros críticos não imediatos.
LOG_ERR: Erro - erros não críticos.
LOG_WARNING: Aviso - situações potencialmente problemáticas.
LOG_NOTICE: Aviso normal - eventos significativos, mas não erros.
LOG_INFO: Informação - mensagens informativas.
LOG_DEBUG: Depuração - mensagens de depuração. 
*/

function wpah_create_syslog_msg($severity, $facility, $message) {
    // Mapeamento das strings de severidade e facilidade para seus valores numéricos correspondentes
    $severityMap = array(
        'LOG_EMERG' => 0,
        'LOG_ALERT' => 1,
        'LOG_CRIT' => 2,
        'LOG_ERR' => 3,
        'LOG_WARNING' => 4,
        'LOG_NOTICE' => 5,
        'LOG_INFO' => 6,
        'LOG_DEBUG' => 7
    );

    $facilityMap = array(
        'LOG_LOCAL0' => 16,
        'LOG_LOCAL1' => 17,
        'LOG_LOCAL2' => 18,
        'LOG_LOCAL3' => 19,
        'LOG_LOCAL4' => 20,
        'LOG_LOCAL5' => 21,
        'LOG_LOCAL6' => 22,
        'LOG_LOCAL7' => 23
    );

    if (!array_key_exists($severity, $severityMap) || !array_key_exists($facility, $facilityMap)) {
        return false;
    }

    $currentDate = date('M d H:i:s');

    $syslogMessage = "<" . ($severityMap[$severity] + $facilityMap[$facility]) . ">" . $currentDate . " WP Anti-Hacker: " . $message;

    return $syslogMessage;
}

function wpah_send_syslog($wazuh_ip_port, $message){
    if (function_exists('socket_create')) {
        $message = wpah_create_syslog_msg('LOG_WARNING', 'LOG_LOCAL0', $message);

        $parts = explode(":", $wazuh_ip_port);

        if (count($parts) === 2) {
            $wazuhPort = $parts[1];
            $wazuhIP = $parts[0];

            $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
            socket_sendto($socket, $message, strlen($message), 0, $wazuhIP, $wazuhPort);
            socket_close($socket);
        }
    }
}

?>
