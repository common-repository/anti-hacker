<?php
 
require_once "wpah-messages.php";
require_once "wpah-logs.php";
require_once "wpah-ip.php";

 class WpahLoginAttemptsLimiter {
   const LOGIN_ATTEMPTS_TIMER = 10;
   private static $enabled = true;
   private static $attemps_limit = 5;
 
   public static function init($max_attemps_limit) {
     add_action('wp_login_failed', [__CLASS__, 'failed_login']);
     $attemps_limit = $max_attemps_limit;
   }
 
   public static function failed_login() {
     if (!self::$enabled) {
       return;
     }
 
     $ip = wpah_get_ip_address();//self::get_user_ip();
     $transient_name = 'failed_login_' . $ip;
     $failed_login_attempts = get_transient($transient_name);
 
     if ($failed_login_attempts === false) {
       set_transient($transient_name, 1, self::LOGIN_ATTEMPTS_TIMER);
     } else {
       $failed_login_attempts++;
       if ($failed_login_attempts >= self::$attemps_limit) {
        wpah_add_log("Brute force blocked", "Was detected and blocked an attemp of brute force by IP " . wpah_get_ip_address() );

         wp_die(WPAH_BRUTE_FORCE_MSG);
       } else {
         set_transient($transient_name, $failed_login_attempts, 30);
       }
     }
   }
 
   public static function enable() {
     self::$enabled = true;
   }
 
   public static function disable() {
     self::$enabled = false;
   }
/* 
   private static function get_user_ip() {
     return filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP );
   }
   */
 }
  
