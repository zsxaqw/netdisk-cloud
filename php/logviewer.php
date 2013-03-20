<?php
include('auth.php');
include('netdisk.functions.php');

$PAGE_TITLE = "NETDISK CLOUD ADMIN";
include('header.php');

$admin = false;
if($_SESSION['USERGROUP'] == 'admin') {
$log1 = file_get_contents('netdisk.php.error.log');
echo "<h1>NDAS PHP Log</h1><br><textarea cols=120 rows=20>$log1</textarea>";
}
else
{
 echo "Admin token expired or does not exist.";
}

include('footer.php');
?>