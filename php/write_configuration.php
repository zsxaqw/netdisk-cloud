<?php

if (is_file('config.php')){
	include('auth.php');
} else {
	session_start();
	$_SESSION['USERGROUP'] = 'admin';

	/* it is an initial configuration, so hash and save the admin 
	 * password*/  
	require('passhash.php');

	$pass_hash = PassHash::hash($_POST['ADMIN_PASS_V']);
	$filename = 'netdisk.user';
	$data = "admin $pass_hash admin\n";
	file_put_contents($filename, $data) or die("Failed to put $filename");

}

include('netdisk.functions.php');

$admin = false;
if($_SESSION['USERGROUP'] == 'admin') {

	$def_config = file_get_contents('./config.default.php');
	
	$int_variables = array('ADMIN_LOG_LEVEL_V','USER_LOG_LEVEL_V','SUB_DIR_LEVELS_V');

	foreach($_POST as $k => $v){
		$search = $k;
		if(in_array($search,$int_variables) && empty($v))
			$v = 0;
		$replace = $v;
		$def_config = str_replace($search, $replace, $def_config);
	}
	
	// write the new configuration file
	file_put_contents('config.php',$def_config) or die("failed to put config.php");
	$_SESSION['config_message'] = "Updated configuration data. (" . date('H:i:s') .")";
/*    echo "<pre>\n";
    echo "Compare below to config.test.php and netdisk.user.test\n$data\n";
	echo str_replace("<?php","&lt;?php",$def_config);
	echo "</pre>";
*/
	header('location: ./configuration.php');		

} else {

	header('Location: ../');

}
?>