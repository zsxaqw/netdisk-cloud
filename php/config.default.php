<?php
/* Change the static variables below as needed */
$WEB_ROOT = "WEB_ROOT_V";
$HTTP_SITE = "HTTP_SITE_V";
$INSTALL_DIR = "INSTALL_DIR_V";
$WAN_IP_DETECTION_URL = "WAN_IP_DETECTION_URL_V";

$ENC_KEY = "ENC_KEY_V";

/* Directory where NDAS devices may be mounted.
 * And how many levels below you would like to search for 
 * empty folders that can become mount points for the NetDISKs
 */
$TOP_MOUNTABLE_DIRECTORY = "TOP_MOUNTABLE_DIRECTORY_V";
$SUB_DIR_LEVELS = SUB_DIR_LEVELS_V;

 /* This is a default. Some pages will change it based on their operation 
  * (Mounting, Enabling and the like). 
  */
$PAGE_TITLE = "PAGE_TITLE_V";

/* These go up to 5 and displays messages <= log_level. The local log file
 * is set in each logging function. php_log, file_access_log, etc, based 
 * on the operations. If set to php's system log level, the local log will
 * be ignored. */	
$LOCAL_LOG_FILE = "LOCAL_LOG_FILE_V";	
$ADMIN_LOG_LEVEL = ADMIN_LOG_LEVEL_V; 
$USER_LOG_LEVEL = USER_LOG_LEVEL_V;

/* Default TimeZone.
 * Must use a php identifier. See the list a the following url. 
 * http://php.net/manual/en/timezones.php
 */
$LOCAL_TIMEZONE = "LOCAL_TIMEZONE_V";

$script_tz = ini_get('date.timezone'); // Set for the scripts if needed. 
if (strcmp($script_tz, $LOCAL_TIMEZONE)){
    date_default_timezone_set($LOCAL_TIMEZONE);
}

/* Use the local folder for session data. Assign strict permissions to this
 * folder to prevent external access. 
 * ex: chmod 600 /the/full/path/
 */
$LOCAL_SESSION_PATH = "LOCAL_SESSION_PATH_V";

if (!empty($LOCAL_SESSION_PATH)) //setting the path if needed.
	session_save_path($LOCAL_SESSION_PATH);
?>