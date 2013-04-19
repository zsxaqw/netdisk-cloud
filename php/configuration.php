<?php
if(is_file('config.php')){
	
include('auth.php');

$PAGE_TITLE = "NETDISK CLOUD ADMIN";
include('header.php');

}

$admin = false;
if($_SESSION['USERGROUP'] == 'admin') {
	include('netdisk.functions.php');
	echo "<h1>NDAS Administration Configuration</h1>";
	
	if (isset($_SESSION['config_message'])){
		echo '<div style="text-align:center; font-color:red">'. $_SESSION['config_message'] .'</div>';
		unset($_SESSION['config_message'] );
	}

	echo "<h2>Todo: </h2> 
		<ul>
			<li>Add config for max file size upload.</li>
			<li>Mabye config for file types</li>
			<li>Use `which COMMAND` to locate all needed commands on the system</li>
			<li>Intial config shows many missing variable errors, mabye ini_set error check none</li>
			<li>Each user needs their own folder for downloads</li>
			<li>Notifiy about the RW NDAS, even if the directory is RO</li>
			<li>Have take ownership of 'file / all files' button if the volume is on NDAS device</li>
			<li>Mabye a chmod section with recursive power</li>
			<li>Put all displayed components into classes for css skining</li>
			<li>Add the dismount button to the mounted directory section</li>
			<li>Assume RO on all files in a RO directory</li>
			
		</ul>
	 <br>";

	/* Test for missing programs and user sudo abilities */
	testCurrentUserSudoAbilities();

	 
	echo "<h2>Change configuration settings</h2>";
?>
<script type="text/javascript" >
function testpassword(){
	var av = document.getElementById('ADMIN_PASS_V');
	var ac = document.getElementById('ADMIN_PASS_C');
	if (av.value != ac.value) { 
		alert("Your password and confirmation password do not match.");
   		ac.focus();
   		return false; 
   	}
}
</script>
<form id="netdisk_cloud_conf_form" method="post" action="./write_configuration.php" onsubmit="return testpassword();">
<table>
<?php if(!is_file('config.php')){ ?>
	<tr>
		<td><label for="ADMIN_PASS_V">ADMIN_PASSWORD</label></td>
		<td><input type="password" name="ADMIN_PASS_V" id="ADMIN_PASS_V"></td>
		<td>Set the password for user admin.</td>
	</tr>
	<tr>
		<td><label for="ADMIN_PASS_C">CONFIRM_PASSWORD</label></td>
		<td><input type="password" name="ADMIN_PASS_C" id="ADMIN_PASS_C"></td>
		<td></td>
	</tr>
<?php } ?>
	<tr>
		<td><label for="WEB_ROOT">WEB_ROOT</label></td>
		<td><input type="text" name="WEB_ROOT_V" id="WEB_ROOT" value="<?php echo $WEB_ROOT; ?>"></td>
		<td>Normally where this script is running. Perhaps /var/www if you run this script from the desktop. </td>
	</tr>
	<tr>
		<td><label for="HTTP_SITE">HTTP_SITE</label></td>
		<td><input type="text" name="HTTP_SITE_V" id="HTTP_SITE" value="<?php echo $HTTP_SITE; ?>"></td>
		<td>The ipaddress of this computer, or domain if it exits. </td>
	</tr>
	<tr>
		<td><label for="INSTALL_DIR">INSTALL_DIR</label></td>
		<td><input type="text" name="INSTALL_DIR_V" id="INSTALL_DIR" value="<?php echo $INSTALL_DIR; ?>"></td>
		<td>This may append to the WEB_ROOT or HTTP_SITE sometimes.  </td>
	</tr>
	<tr>
		<td><label for="WAN_IP_DETECTION_URL">WAN_IP_DETECTION_URL</label></td>
		<td><input type="text" name="WAN_IP_DETECTION_URL_V" id="WAN_IP_DETECTION_URL" value="<?php echo $WAN_IP_DETECTION_URL; ?>"></td>
		<td>URL of your "tell me public IP script on the net."</td>
	</tr>
	<?php if (empty($ENC_KEY )){ ?>
	<tr>
		<td><label for="ENC_KEY">ENC_KEY</label></td>
		<td><input type="text" name="ENC_KEY_V" id="ENC_KEY" value="<?php echo $ENC_KEY; ?>"></td>
		<td>Invent a string of characters here. It will become salt on your passwords.</td>
	</tr>
	<?php } else { ?> 
			<input type="hidden" name="ENC_KEY_V" id="ENC_KEY" value="<?php echo $ENC_KEY; ?>">
	<?php	} ?>
	<tr>
		<td><label for="TOP_MOUNTABLE_DIRECTORY">TOP_MOUNTABLE_DIRECTORY</label></td>
		<td><input type="text" name="TOP_MOUNTABLE_DIRECTORY_V" id="TOP_MOUNTABLE_DIRECTORY" value="<?php echo $TOP_MOUNTABLE_DIRECTORY; ?>"></td>
		<td>Where your web user will mount the NetDISKs.</td>
	</tr>
	<tr>
		<td><label for="SUB_DIR_LEVELS">SUB_DIR_LEVELS</label></td>
		<td><input type="text" name="SUB_DIR_LEVELS_V" id="SUB_DIR_LEVELS" value="<?php echo $SUB_DIR_LEVELS; ?>"></td>
		<td>How many levels to search for empty directories below TOP_MOUNTABLE_DIRECTORY.</td>
	</tr>
	<tr>
		<td><label for="PAGE_TITLE">PAGE_TITLE</label></td>
		<td><input type="text" name="PAGE_TITLE_V" id="PAGE_TITLE" value="<?php echo $PAGE_TITLE; ?>"></td>
		<td>Appears in top of the browser windows and a few places in the Admin Sections.</td>
	</tr>
	<tr>
		<td><label for="LOCAL_LOG_FILE">LOCAL_LOG_FILE</label></td>
		<td><input type="text" name="LOCAL_LOG_FILE_V" id="LOCAL_LOG_FILE" value="<?php echo $LOCAL_LOG_FILE; ?>"></td>
		<td>Make sure this file has strict permissions like 600. </td>
	</tr>
	<tr>
		<td><label for="ADMIN_LOG_LEVEL">ADMIN_LOG_LEVEL</label></td>
		<td><input type="text" name="ADMIN_LOG_LEVEL_V" id="ADMIN_LOG_LEVEL" value="<?php echo $ADMIN_LOG_LEVEL; ?>"></td>
		<td>Messages related to NetDISK operations. From 0 (No messages) to 5 (Too many messages). </td>
	</tr>
	<tr>
		<td><label for="USER_LOG_LEVEL">USER_LOG_LEVEL</label></td>
		<td><input type="text" name="USER_LOG_LEVEL_V" id="USER_LOG_LEVEL" value="<?php echo $USER_LOG_LEVEL; ?>"></td>
		<td>Related to users login, upload, storage capcity and the like. 0 - 3 (None to All)</td>
	</tr>
	<tr>
		<td><label for="LOCAL_TIMEZONE">LOCAL_TIMEZONE</label></td>
		<td><input type="text" name="LOCAL_TIMEZONE_V" id="LOCAL_TIMEZONE" value="<?php echo $LOCAL_TIMEZONE; ?>"></td>
		<td>Must be a <a href="http://php.net/manual/en/timezones.php" target="_blank">PHP timezone</a>.</td>
	</tr>	
	<tr>
		<td><label for="LOCAL_SESSION_PATH">LOCAL_SESSION_PATH</label></td>
		<td><input type="text" name="LOCAL_SESSION_PATH_V" id="LOCAL_SESSION_PATH" value="<?php echo $LOCAL_SESSION_PATH; ?>"></td>
		<td>Permit only admin to access this folder. It will override PHP's sessions, storing the data locally.</td>
	</tr>
	<tr>
		<td colspan="3" align="center"><input type="submit" value="Save Configuration"></td>
	</tr>
</table>
</form>
<?php

/*	$def_config = file_get_contents('./config.php.default');
	echo "<pre>\n";
	echo str_replace("<?php","&lt;?php",$def_config);
	echo "</pre>";		
*/

}
else
{
 echo "Admin token expired or does not exist.";
}

include('footer.php');
?>