<?php
/* Change the static variables below as needed */
$page_title = "NETDISK CLOUD";
$WEB_ROOT="/var/www";
$HTTP_SITE="http://mint64.home";
$INSTALL_DIR="/web-ui";
$ENC_KEY="cidkfe783o0a*(fjkvc";
$ENC_METHOD='aes128';
$ENC_16_CHAR_VECTOR="m7s329x.r92/f71*";  
$WAN_IP_DETECTION_URL="http://www.iocellnetworks.com/visitorip.php";

/* Default TimeZone.
 * Must use a php identifier. See the list a the following url. 
 * http://php.net/manual/en/timezones.php
 */
$LOCAL_TIMEZONE="America/New_York";

/* Log Level & log destination.
 * 0: No messages
 * 1: Critical Errors (No Permission, Missing Config Files)
 * 2: Level 1 and many other usage logs. 
 * 3: Full debugging. 1,2 and every function call will be logged.
 */
$LOG_LEVEL=3;
$LOG_FILE="./netdisk.error.log";

/* Directory where NDAS devices may be mounted. */
$TOP_MOUNTABLE_DIRECTORY="/var/www";


/* Editing below this line should not be necessary */

/* Checking the set timezone against the system. */
$GLOBALS['LOG_LEVEL']=$LOG_LEVEL;

$script_tz = ini_get('date.timezone');
if (strcmp($script_tz, $LOCAL_TIMEZONE)){
    date_default_timezone_set($LOCAL_TIMEZONE);
}

/* Use the local folder for session data */
session_save_path($_SERVER['DOCUMENT_ROOT'] . '/sessions');
?>
