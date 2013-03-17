<?php
/* Change the static variables below as needed */
$WEB_ROOT="/var/www";
$HTTP_SITE="http://mint64.home";
$INSTALL_DIR="/web-ui";
$WAN_IP_DETECTION_URL="http://www.iocellnetworks.com/visitorip.php";

$ENC_KEY="cidkfe783o0a*(fjkvc";
$ENC_METHOD='aes128';
$ENC_16_CHAR_VECTOR="m7s329x.r92/f71*";  

$TOP_MOUNTABLE_DIRECTORY="/var/www";

 /* This is a default. Some pages will change it based on their operation 
  * (Mounting, Enabling and the like). */
$PAGE_TITLE = "NETDISK CLOUD";

/* These go up to 5 and displays messages <= log_level. The local log file
 * is set in each logging function. php_log, file_access_log, etc, based 
 * on the operations. If set to php's system log level, the local log will
 * be ignored. */		
$ADMIN_LOG_LEVEL = 5; 
$USER_LOG_LEVEL = 0;
$PHP_LOG_TYPE = 3; 
?>