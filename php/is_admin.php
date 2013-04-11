<?php
/* to check for administrative permission level use next two lines on the top
 * of your script.
include('auth.php');
include('is_admin.php');  
 */

$sessionGroup = isset($_SESSION['USERGROUP'])?$_SESSION['USERGROUP']:null;
if($sessionGroup != 'admin') {
	die("Administrative permission required.<br><a href=\"javascript:self.close()\">Close</a>");
}

?>