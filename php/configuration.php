<?php
include('auth.php');
include('netdisk.functions.php');

$PAGE_TITLE = "NETDISK CLOUD ADMIN";
include('header.php');

$admin = false;
if($_SESSION['USERGROUP'] == 'admin') {
echo "<h1>NDAS Administration Configuration</h1>";

	/* Test for missing programs and user sudo abilities */
	testCurrentUserSudoAbilities();

}
else
{
 echo "Admin token expired or does not exist.";
}

include('footer.php');
?>