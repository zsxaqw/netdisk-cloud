<?php
/* Change the static variables below as needed */
$WEB_ROOT="./";
$HTTP_SITE="http://192.168.1.200";
$INSTALL_DIR="netdisk-cloud";
$WAN_IP_DETECTION_URL="";

$ENC_KEY="cidkfe783o0a*(fjkvc";
$ENC_METHOD='aes128';
$ENC_16_CHAR_VECTOR="m7s329x.r92/f71*";  

/* Directory where NDAS devices may be mounted.
 * And how many levels below you would like to search for 
 * empty folders that can become mount points for the NetDISKs
 */
$TOP_MOUNTABLE_DIRECTORY="/media/";
$SUB_DIR_LEVELS=1;

 /* This is a default. Some pages will change it based on their operation 
  * (Mounting, Enabling and the like). 
  */
$PAGE_TITLE = "NETDISK CLOUD";

/* These go up to 5 and displays messages <= log_level. The local log file
 * is set in each logging function. php_log, file_access_log, etc, based 
 * on the operations. If set to php's system log level, the local log will
 * be ignored. */	
$LOCAL_LOG_FILE = $_SERVER['DOCUMENT_ROOT'] . "/$INSTALL_DIR/php/netdisk.php.error.log";	
$ADMIN_LOG_LEVEL = 5; 
$USER_LOG_LEVEL = 0;
$PHP_LOG_TYPE = 3; 

/* Default TimeZone.
 * Must use a php identifier. See the list a the following url. 
 * http://php.net/manual/en/timezones.php
 */
$LOCAL_TIMEZONE="America/New_York";

/* Use the local folder for session data  */
session_save_path($_SERVER['DOCUMENT_ROOT'] . "/$INSTALL_DIR/sessions");

/* Checking the set timezone against the system. */
$script_tz = ini_get('date.timezone');
if (strcmp($script_tz, $LOCAL_TIMEZONE)){
    date_default_timezone_set($LOCAL_TIMEZONE);
}

?>