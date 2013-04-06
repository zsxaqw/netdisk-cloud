<?php
include('auth.php');
include('netdisk.functions.php');

$PAGE_TITLE = "NETDISK CLOUD ADMIN";
include('header.php');

$admin = false;
if($_SESSION['USERGROUP'] == 'admin') {

	$log_message = '&nbsp;';
	
	if(isset($_POST['submit'])){
		
		switch($_POST['submit']) {
			case 'Clear PHP Logs':
				if (file_put_contents($LOCAL_LOG_FILE, '') === FALSE) 
					$log_message = "Failed to clear PHP Logs";
				else 
					$log_message = "Cleared PHP Logs";
				break;
		}	
	}
	$log1 = file_get_contents($LOCAL_LOG_FILE);
	echo '<h1>NDAS PHP Log</h1>
	<div class="admin-flash-message">'.$log_message.'</div>
	<textarea style="width:97%;height:160px;">'.$log1.'</textarea>
	<h2>Clear the PHP logs</h2>
	<br>
	<form method="post" action="./logviewer.php">
	<input type="submit" name="submit" value="Clear PHP Logs">
	</form>';

}
else
{
 echo "Admin token expired or does not exist.";
}

include('footer.php');
?>