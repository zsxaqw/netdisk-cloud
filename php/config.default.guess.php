<?php
	$config_message="Change these values as needed and save. Admin password is mandatory";
	$WEB_ROOT = $_SERVER['DOCUMENT_ROOT'];
	$INSTALL_DIR = str_replace($WEB_ROOT,'',str_replace("/php","",getcwd()));
	$protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,strpos( $_SERVER["SERVER_PROTOCOL"],'/'))).'://';
	$HTTP_SITE =  $protocol . $_SERVER['HTTP_HOST'];
	$TOP_MOUNTABLE_DIRECTORY = $WEB_ROOT;
	$SUB_DIR_LEVELS = 1;
	$PAGE_TITLE = "NETDISK CLOUD";
	$LOCAL_LOG_FILE = realpath('./netdisk.php.error.log');
	$ADMIN_LOG_LEVEL = 0;
	$LOCAL_LOG_LEVEL = 0;
	$USER_LOG_LEVEL = 0;
	$PHP_LOG_TYPE = -1;
	$shortName = exec('date +%Z');
	$LOCAL_TIMEZONE = timezone_name_from_abbr($shortName);
	$LOCAL_SESSION_PATH = $WEB_ROOT . $INSTALL_DIR . '/sessions';

?>