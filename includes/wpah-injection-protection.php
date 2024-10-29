<?php

require_once "wpah-messages.php";
require_once "wpah-logs.php";
require_once "wpah-siem-connector.php";

function wpah_pre_process_input($input) {
    $input = str_replace('https://', '', $input);
    $input = str_replace('http://', '', $input);
    $input = str_replace(' ', '', $input);
    $input = str_replace('\'+\'', '', $input);
    $input = str_replace('"+"', '', $input);
    $input = str_replace('\\', '/', $input);
    $input = urldecode($input);
    return $input;
}

function wpah_text_from_blocked($xss, $sqli, $cmd, $dir) {
    if($xss) {
        return 'XSS';
    }
    if($sqli) {
        return 'SQL Injection';
    }
    if($cmd) {
        return 'CMD Injection';
    }
    if($dir) {
        return 'Directory Transversal';
    }
    return 'unknown';
}

function wpah_block_injection() {
    $url = $_SERVER['REQUEST_URI']; 
    $xss = wpah_detect_xss( $url ); 
    $sqli = wpah_detect_sql_injection_url( $url );
    $cmd = wpah_detect_cmd_injection( $url );
    $dir = wpah_detect_transversal_directory( $url );

    $url = sanitize_url($url);

    if ( $xss || $sqli || $cmd || $dir ) {
        $injection = wpah_text_from_blocked($xss, $sqli, $cmd, $dir);
        $injection_msg = "Was detected and blocked an attempt to use $injection in url " . $url;
        if (get_option('wpah_config_syslog', 999)) {
            wpah_send_syslog(get_option('wpah_config_syslog', 999), $injection_msg);
        }

        wpah_add_log("$injection blocked", $injection_msg);
    
        wpah_block_access();
    }

    if ( isset( $_GET ) ) {
        foreach ( $_GET as $key => $value ) {
            $xss = wpah_detect_xss( $value ); 
            $sqli = wpah_detect_sql_injection( $value );
            $cmd = wpah_detect_cmd_injection( $value );
            $dir = wpah_detect_transversal_directory( $value );
        
            if ( $xss || $sqli || $cmd || $dir ) {
                $injection = wpah_text_from_blocked($xss, $sqli, $cmd, $dir);
                $injection_msg = "Was detected and blocked an attempt to use $injection in url " . $url . " GET parameter " . $key . " valure " . $value;
                if (get_option('wpah_config_syslog', 999)) {
                    wpah_send_syslog(get_option('wpah_config_syslog', 999), $injection_msg);
                }        
                wpah_add_log("$injection blocked", $injection_msg);
        
                wpah_block_access();
            }
        }
    }
    
    if ( isset( $_POST ) ) {
        foreach ( $_POST as $key => $value ) {
            $xss = wpah_detect_xss( $value ); 
            $sqli = wpah_detect_sql_injection( $value );
            $cmd = wpah_detect_cmd_injection( $value );
            $dir = wpah_detect_transversal_directory( $value );
        
            if ( $xss || $sqli || $cmd || $dir ) {
                $injection = wpah_text_from_blocked($xss, $sqli, $cmd, $dir);
                $injection_msg = "Was detected and blocked an attempt to use $injection in url " . $url . " POST parameter " . $key . " valure " . $value;
                if (get_option('wpah_config_syslog', 999)) {
                    wpah_send_syslog(get_option('wpah_config_syslog', 999), $injection_msg);
                }        
                wpah_add_log("$injection blocked", $injection_msg);
        
                wpah_block_access();
            }
        }
    }
}

function wpah_detect_xss( $input ) {
    $input = wpah_pre_process_input($input);

    $pattern = '/<[^\w<>]*(?:[^<>"\'\s]*:)?[^\w<>]*(?:\W*s\W*c\W*r\W*i\W*p\W*t|\W*f\W*o\W*r\W*m|\W*s\W*t\W*y\W*l\W*e|\W*s\W*v\W*g|\W*m\W*a\W*r\W*q\W*u\W*e\W*e|(?:\W*l\W*i\W*n\W*k|\W*o\W*b\W*j\W*e\W*c\W*t|\W*e\W*m\W*b\W*e\W*d|\W*a\W*p\W*p\W*l\W*e\W*t|\W*p\W*a\W*r\W*a\W*m|\W*i?\W*f\W*r\W*a\W*m\W*e|\W*b\W*a\W*s\W*e|\W*b\W*o\W*d\W*y|\W*m\W*e\W*t\W*a|\W*i\W*m\W*a?\W*g\W*e?|\W*v\W*i\W*d\W*e\W*o|\W*a\W*u\W*d\W*i\W*o|\W*b\W*i\W*n\W*d\W*i\W*n\W*g\W*s|\W*s\W*e\W*t|\W*i\W*s\W*i\W*n\W*d\W*e\W*x|\W*a\W*n\W*i\W*m\W*a\W*t\W*e)[^>\w])|(?:<\w[\s\S]*[\s\0\/]|[\'"])(?:formaction|style|background|src|lowsrc|ping|on(?:d(?:e(?:vice(?:(?:orienta|mo)tion|proximity|found|light)|livery(?:success|error)|activate)|r(?:ag(?:e(?:n(?:ter|d)|xit)|(?:gestur|leav)e|start|drop|over)?|op)|i(?:s(?:c(?:hargingtimechange|onnect(?:ing|ed))|abled)|aling)|ata(?:setc(?:omplete|hanged)|(?:availabl|chang)e|error)|urationchange|ownloading|blclick)|Moz(?:M(?:agnifyGesture(?:Update|Start)?|ouse(?:PixelScroll|Hittest))|S(?:wipeGesture(?:Update|Start|End)?|crolledAreaChanged)|(?:(?:Press)?TapGestur|BeforeResiz)e|EdgeUI(?:C(?:omplet|ancel)|Start)ed|RotateGesture(?:Update|Start)?|A(?:udioAvailable|fterPaint))|c(?:o(?:m(?:p(?:osition(?:update|start|end)|lete)|mand(?:update)?)|n(?:t(?:rolselect|extmenu)|nect(?:ing|ed))|py)|a(?:(?:llschang|ch)ed|nplay(?:through)?|rdstatechange)|h(?:(?:arging(?:time)?ch)?ange|ecking)|(?:fstate|ell)change|u(?:echange|t)|l(?:ick|ose))|m(?:o(?:z(?:pointerlock(?:change|error)|(?:orientation|time)change|fullscreen(?:change|error)|network(?:down|up)load)|use(?:(?:lea|mo)ve|o(?:ver|ut)|enter|wheel|down|up)|ve(?:start|end)?)|essage|ark)|s(?:t(?:a(?:t(?:uschanged|echange)|lled|rt)|k(?:sessione|comma)nd|op)|e(?:ek(?:complete|ing|ed)|(?:lec(?:tstar)?)?t|n(?:ding|t))|u(?:ccess|spend|bmit)|peech(?:start|end)|ound(?:start|end)|croll|how)|b(?:e(?:for(?:e(?:(?:scriptexecu|activa)te|u(?:nload|pdate)|p(?:aste|rint)|c(?:opy|ut)|editfocus)|deactivate)|gin(?:Event)?)|oun(?:dary|ce)|l(?:ocked|ur)|roadcast|usy)|a(?:n(?:imation(?:iteration|start|end)|tennastatechange)|fter(?:(?:scriptexecu|upda)te|print)|udio(?:process|start|end)|d(?:apteradded|dtrack)|ctivate|lerting|bort)|DOM(?:Node(?:Inserted(?:IntoDocument)?|Removed(?:FromDocument)?)|(?:CharacterData|Subtree)Modified|A(?:ttrModified|ctivate)|Focus(?:Out|In)|MouseScroll)|r(?:e(?:s(?:u(?:m(?:ing|e)|lt)|ize|et)|adystatechange|pea(?:tEven)?t|movetrack|trieving|ceived)|ow(?:s(?:inserted|delete)|e(?:nter|xit))|atechange)|p(?:op(?:up(?:hid(?:den|ing)|show(?:ing|n))|state)|a(?:ge(?:hide|show)|(?:st|us)e|int)|ro(?:pertychange|gress)|lay(?:ing)?)|t(?:ouch(?:(?:lea|mo)ve|en(?:ter|d)|cancel|start)|ime(?:update|out)|ransitionend|ext)|u(?:s(?:erproximity|sdreceived)|p(?:gradeneeded|dateready)|n(?:derflow|load))|f(?:o(?:rm(?:change|input)|cus(?:out|in)?)|i(?:lterchange|nish)|ailed)|l(?:o(?:ad(?:e(?:d(?:meta)?data|nd)|start)?|secapture)|evelchange|y)|g(?:amepad(?:(?:dis)?connected|button(?:down|up)|axismove)|et)|e(?:n(?:d(?:Event|ed)?|abled|ter)|rror(?:update)?|mptied|xit)|i(?:cc(?:cardlockerror|infochange)|n(?:coming|valid|put))|o(?:(?:(?:ff|n)lin|bsolet)e|verflow(?:changed)?|pen)|SVG(?:(?:Unl|L)oad|Resize|Scroll|Abort|Error|Zoom)|h(?:e(?:adphoneschange|l[dp])|ashchange|olding)|v(?:o(?:lum|ic)e|ersion)change|w(?:a(?:it|rn)ing|heel)|key(?:press|down|up)|(?:AppComman|Loa)d|no(?:update|match)|Request|zoom))[\s\0]*=/i';

    if ( preg_match( $pattern, $input ) ) {
        return true;
    }
    
    return false;
}

function wpah_detect_sql_injection_url( $input ) {
    $pattern = '/(\'|%27|--|\#|\/\*)[^\n]*((information|into|from|select|union|where|and|or|\|\||&|\&\&)[^\n]*)+/ix';

    $input = wpah_pre_process_input($input);

    if ( preg_match( $pattern, $input ) ) {
        return true;
    }
    
    return false;
}

function wpah_detect_sql_injection( $input ) {
    $pattern = '/(\'|%27|--|\#|\/\*)[^\n]*((information|into|from|select|union|where|and|or|\|\||&|\&\&)[^\n]*)+/ix';
    
    $input = wpah_pre_process_input($input);

    if ( preg_match( $pattern, $input ) ) {
        return true;
    }
    
    return false;
}

function wpah_detect_cmd_injection( $input ) {
    $pattern_linux = '/;\s*(?:\/\*.*?\*\/\s*)?(?:\|\||&&)?\s*[\n\r]*|`.*?`|;?\s*shutdown\s*(?:-r|-h)?\s*(?:now|0)?\b/i';
    $pattern_windows = '/(\b(?:exec|xp_cmdshell|sp_executesql)\b|\|\||&&|\bping\b)|\s*\bnet\b\s*(?:user|group|localgroup)\b/i';

    $input = wpah_pre_process_input($input);

    if ( preg_match( $pattern_linux, $input ) ) {
        return true;
    } elseif ( preg_match( $pattern_windows, $input ) ) {
        return true;
    }
    
    return false;
}

function wpah_detect_transversal_directory($input) {
    $regex = '#\.{2};?\/|\/var\/|\/usr\/|\/etc\/#';
    $input = wpah_pre_process_input($input);

    if ( preg_match( $regex, $input ) ) {
        return true;
    }
    
    return false;
}

function wpah_block_access($text = "") {
    header( 'HTTP/1.0 403 Forbidden' );
    wp_die( WPAH_INJECTION_MSG . $text );
}
