<?php
require_once "wpah-messages.php";
require_once "wpah-logs.php";


function wpah_scan_sensitive_files() {
    $base_url = home_url('/');
    return <<<JSCODE
        <div id="scan_result"> Checking...
    </div>

    <script type="text/javascript">
    let file_checker_count = 0;

    function scanSensitiveFiles() {
        const sensitiveFiles = {
            '.env': 'Environment files may expose sensitive information like credentials and api-keys. Is recommended to deny access to this file by http server access, for example, using .htaccess.',
            '.env.old' : 'Environment files backup may expose sensitive information like credentials and api-keys. Is recommended to remove this files or deny access to this file by http server access, for example, using .htaccess.',
            '.bash_history' : 'Bash history file may expose sensitive information like credentials, sensitive folders, etc. Is recommended to remove this files or deny access to this file by http server access, for example, using .htaccess.',.
            '.ssh/id_rsa' : '',
            '.git/config' : 'Git directory accessible using HTTP may expose all your source code and sensible information. Is recommended to remove .git folder or deny access to this folder by http server access, for example, using .htaccess.',
            'wp-config.php.save' : 'Wordpress backup file may expose sensitive information like credentials and api-keys. Is recommended to remove this files or deny access to this file by http server access, for example, using .htaccess.',
            'wp-config.php.old' : 'Wordpress backup file may expose sensitive information like credentials and api-keys. Is recommended to remove this files or deny access to this file by http server access, for example, using .htaccess.',
            'wp-config.php.bak' : 'Wordpress backup file may expose sensitive information like credentials and api-keys. Is recommended to remove this files or deny access to this file by http server access, for example, using .htaccess.',
            'wp-config.old' : 'Wordpress backup file may expose sensitive information like credentials and api-keys. Is recommended to remove this files or deny access to this file by http server access, for example, using .htaccess.',
            '1' : 'Wordpress backup file may expose sensitive information like credentials and api-keys. Is recommended to remove this files or deny access to this file by http server access, for example, using .htaccess.',
            'backup.old' : 'Backup file may expose sensitive information like credentials and api-keys. Is recommended to remove this files or deny access to this file by http server access, for example, using .htaccess.',
            'backup.sql' : 'Database backup file may expose sensitive information like credentials and api-keys. Is recommended to remove this files or deny access to this file by http server access, for example, using .htaccess.',
            'backup.sql.old' : 'Database backup file may expose sensitive information like credentials and api-keys. Is recommended to remove this files or deny access to this file by http server access, for example, using .htaccess.',
            'database.sql' : 'Database backup file may expose sensitive information like credentials and api-keys. Is recommended to remove this files or deny access to this file by http server access, for example, using .htaccess.',
            'database.old' : 'Database backup file may expose sensitive information like credentials and api-keys. Is recommended to remove this files or deny access to this file by http server access, for example, using .htaccess.',
            'database.sql.old' : 'Database backup file may expose sensitive information like credentials and api-keys. Is recommended to remove this files or deny access to this file by http server access, for example, using .htaccess.',
            'readme.html' : 'Default file created in wordpress installation with with Wordpress version information. Is recommended to remove this files.',
            'license.txt' : 'Default file created in wordpress installation with with Wordpress version information. Is recommended to remove this files.',
        };

        const sensitiveFilesIgnoreHtml = ['readme.html'];
        const removeSensitiveFiles = [
            '.env.old',
            'wp-config.php.save',
            'wp-config.php.old',
            'wp-config.php.bak',
            'wp-config.old',
            '1',
            'backup.old',
            'backup.sql',
            'backup.sql.old',
            'database.sql',
            'database.old',
            'database.sql.old',
            'readme.html',
            'license.txt',
            ];

        let html = '<table style="border-collapse: collapse; border: 1px solid black; width: 100%;">';
        html += '<thead><tr><th style="padding: 10px; border: 1px solid black; width: 15%;">File</th><th style="padding: 10px; border: 1px solid black; width: 15%;">Fix</th><th style="padding: 10px; border: 1px solid black; width: 70%;">Description</th></thead>';
        html += '<tbody>';

        let detected = false;

        for (const key in sensitiveFiles) {
            if (sensitiveFiles.hasOwnProperty(key)) {
                const url = '$base_url' + key;
                const xhr = new XMLHttpRequest();
                xhr.open('GET', url, true);

                xhr.onreadystatechange = function () {
                    file_checker_count++;
                    if (xhr.readyState === 4) {
                        if (xhr.status === 200) {
                            const body = xhr.responseText;
                            if (body.indexOf('<html') === -1 || sensitiveFilesIgnoreHtml.includes(key)) {
                                detected = true;
                                if (removeSensitiveFiles.includes(key)) {
                                    html += '<tr>';
                                    html += '<td style="padding: 10px; border: 1px solid black;">' + url + '</td>';
                                    html += '<td style="padding: 10px; border: 1px solid black;"> Delete the file </td>';
                                    html += '<td style="padding: 10px; border: 1px solid black;">' + sensitiveFiles[key] + '</td>';
                                    html += '</tr>';
                                } else {
                                    html += '<tr>';
                                    html += '<td style="padding: 10px; border: 1px solid black;">' + url + '</td>';
                                    html += '<td style="padding: 10px; border: 1px solid black;"> Delete or block external access </td>';
                                    html += '<td style="padding: 10px; border: 1px solid black;">' + sensitiveFiles[key] + '</td>';
                                    html += '</tr>';
                                }
                            }
                        }
                    }

                    if (file_checker_count == Object.keys(sensitiveFiles).length) {
                        html += '</tbody></table>';
                        if (!detected) {
                            html += '<br> * No sensitive file exposed detected.';
                            const element = document.getElementById('scan_result');
                            if (element) {
                                element.innerHTML = html; 
                            }
                        } else {
                            const element = document.getElementById('scan_result');
                            if (element) {
                                element.innerHTML = html; 
                            }
                        }
                    }
                };

                xhr.send();
            }
        }

    }
    scanSensitiveFiles();
    </script>
    JSCODE;
}
