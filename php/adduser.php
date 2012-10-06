<?php
include('auth.php');
include('is_admin.php');

$adduserid = isset($_POST['userid'])? $_POST['userid'] : null;
$adduserpw = isset($_POST['userpw'])? $_POST['userpw'] : null;
$addusergrp = isset($_POST['usergrp'])? $_POST['usergrp'] : null;

if(empty($adduserid) || empty($adduserpw) || empty($addusergrp))
{
	echo '<form action="adduser.php" method=POST>';
	echo '<table>
		<td>User ID:</td><td><input type="text" name="userid" value="';
		if ($adduserid !== null) echo $adduserid;
	echo '"></td>
		</tr>
		<tr>
		<td>Password:</td><td><input type="text" name="userpw" value="';
		if ($adduserpw !== null) echo $adduserpw;
	echo '"></td>
		</tr>
		<tr>
		<td>Group:</td><td><select name="usergrp">
			<option value="user" ';
		if ($addusergrp === 'user') echo "selected ";
	echo '>user</option>
			<option value="admin" ';
		if ($addusergrp === 'admin') echo "selected ";
	echo '>admin</option>
			</select>
		</td>
		</tr>
		</table>
		<input type=submit value="Add">';
	if(empty($adduserid) && !empty($adduserpw))
	{
		echo "<p>Please type userid</p>";
	}
	else if(!empty($adduserid) && empty($adduserpw))
	{
		echo "<p>Please type password</p>";
	}
}
else
{
	$ufile = "netdisk.user";
	if(!file_exists($ufile))
	{
		echo "<h3>user information file is not exist: ".$ufile."</h3>";
		exit;
	}
	$fp = fopen($ufile,"r+");
	if(!$fp)
	{
		echo "<h3>Cannot open user information file: ".$ufile."</h3>";
		exit;
	}
	if(!flock($fp, LOCK_EX))
	{
		echo "<h3>Other connection is using the user information file</h3>";
		exit;
	}
	$find = false;
	
	// Encrypting the password
	$enc_pass=openssl_encrypt($adduserpw,$ENC_METHOD,$ENC_KEY,false,$ENC_16_CHAR_VECTOR);
	while(!feof($fp))
	{
		if(fscanf($fp," %s %s %s\n",$userid,$userpw,$usergrp) && $userid == $adduserid)
		{
			//echo "<p>userid: ".$userid."</p>";
			$find = true;
			break;
		}
	}
	if($find == false)	{
		// create a new user
		fseek($fp,0,SEEK_END);
		$newuser = sprintf("%s %s %s\n",$adduserid,$enc_pass,$addusergrp);
		fwrite($fp,$newuser);
	}
	flock($fp,LOCK_UN);
	fclose($fp);
	if($find)
	{
		echo "<h3>User ID '".$adduserid."' is taken.</h3>";
	}
	else
	{
		echo"<script>
			opener.location.href='user_manage.php'
			//self.close();
			</script>";
		echo "<p>User ID '".$adduserid."' is successfully created.</p>";
	}
}
?>
