<?php
include ('auth.php');
include ('is_admin.php');
include('header.php');

?>
<?php
$ufile = "netdisk.user";
if(!file_exists($ufile))
{
	echo "<h3>user information file is not exist: ".$ufile."</h3>";
	exit;
}
$fp = fopen($ufile,"r");
if(!$fp)
{
	echo "<h3>Cannot open user information file: ".$ufile."</h3>";
	exit;
}
echo "<h1>Registerd Users</h3>";
echo "<table border=1>";
echo "<tr><th>ID</th><th>Group</th><th colspan='2'>Action</th><tr>";
while(!feof($fp))
{
	if(fscanf($fp,"%s %s %s\n",$userid,$userpw,$usergrp))
	{
		echo "<tr><td>$userid</td>";
		echo "<td>$usergrp</td>";
		echo "<td><form target='_moduser' method='POST' action='moduser.php' 
				onSubmit='javascript:modUser()'>";
		echo "<input type='hidden' name='moduserid' value='$userid'>";
		echo "<input type='submit' value='Modify' class='btn'></form></td>";
		if($userid != 'admin')
		{
			echo "<td><form target='_deluser' method='POST' action='deluser.php' 
				onSubmit='javascript:delUser()'>";
			echo "<input type='hidden' name='deluserid' value='$userid'>";
			echo "<input type='submit' value='Delete' class='btn'></form></td>";
		}	
		
	}
}
fclose($fp);
echo "</table>";
echo "<br>";
echo "<a href=\"javascript:addUser()\">Add User</a>";
echo "<br>";
echo "<a href=\"list.php\">Home</a>";
?>
</html>
