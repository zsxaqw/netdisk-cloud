<?php
/* this section grabs the current page and information in case the login 
 * fails. It will allow the user back to the same page if session was 
 * timed out. We just put $pageURL in the authLoginForm below.
 * This code came from http://webcheatsheet.com/php/get_current_page_url.php
 * Posted by byron | 13 Jan 2008 17:03:41
 */
function curPageURL() {
$isHTTPS = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on");
$port = (isset($_SERVER["SERVER_PORT"]) && ((!$isHTTPS && $_SERVER["SERVER_PORT"] != "80") || ($isHTTPS && $_SERVER["SERVER_PORT"] != "443")));
$port = ($port) ? ':'.$_SERVER["SERVER_PORT"] : '';
$url = ($isHTTPS ? 'https://' : 'http://').$_SERVER["SERVER_NAME"].$port.$_SERVER["REQUEST_URI"];
return $url;
}
$pageURL=curPageURL();

if(is_file('config.php')){
	
	include('config.php');

} else {
	session_start();
	$_SESSION['USERGROUP'] = 'admin';
	$_SESSION['first_run'] = 'y';
	include('config.default.guess.php');
	include('header.php');
	include('configuration.php');
	die('NETDISK CLOUD INITIAL CONFIGURATION');
}


/* Test if the user is in the administrator group.
 * returns true or false */
function userIsAdmin(){
	/*  Get the current user ID from Session_id */
	$cur_uid = getUserFromSavedSessionId();
	if (!$cur_uid){ return false; }
	 	
	/* see if they are registered as admin */
	if (getUserGroupFromDatabase($cur_uid) !== 'admin'){ return false;	}
	
	/* make sure their session group is admin */
	if ($_SESSION['USERGROUP'] !== 'admin') { return false; }
	
	return true;
}


/* */
function AuthUser($iuserid,$iuserpw)
{
	$ufile = "netdisk.user";
	if(!file_exists($ufile))
	{
		echo "<h3>user information file does not exist: ".$ufile."</h3>";
		exit;
	}
	$fp = fopen($ufile,"r");
	if(!$fp)
	{
		echo "<h3>Cannot open user information file: ".$ufile."</h3>";
		exit;
	}
	while(!feof($fp))
	{
		if(fscanf($fp,"%s %s %s\n",$userid,$userpw,$usergrp))
		{
			//echo "$userid == $iuserid ?<br>";
			if($userid == $iuserid)
			{
				// check the password the user tried to login with
				require('passhash.php');
				//echo "Check $userpw vs. $iuserpw";
				if (PassHash::check_password($userpw, $iuserpw))  
				//if($userpw == $iuserpw)
				{
					return true;
				}
				break;
			}
		}
	}
	fclose($fp);
	return false;
}


/* set the users group in the session array. */
function setUserGroup($userId){
	$userGroup = exec("grep $userId ./netdisk.user | cut -d' ' -f 3");
	if ($userGroup != 'admin' && $userGroup != 'user'){
		die('User permission level could not be determined.');
	} 
	$_SESSION['USERGROUP']=$userGroup;
}

/* get the users group from the netdisk.user file. */
function getUserGroupFromDatabase($userId){
	$userGroup = exec("grep $userId ./netdisk.user | cut -d' ' -f 3");
	if ( empty($userGroup) ){ return null;	}
	return $userGroup; 
}


function readDirFile()
{
	$conf = "netdisk.dir";
	if(!file_exists($conf))
		return;
	$fp = fopen($conf,"r");
	if(!$fp)
		return;
	$dirkeys = array();
	$dirvals = array();
	for($i=1; !feof($fp); $i++)
	{
	if(fscanf($fp,"%s\t%s\n",$dir,$pass))
	{
	array_push($dirkeys,$dir);
	array_push($dirvals,$pass);
	}
	}
	fclose($fp);
	$_SESSION['DIRNAME'] = urlencode(serialize($dirkeys));
	$_SESSION['DIRPASS'] = urlencode(serialize($dirvals));
}

/* We want to keep the session ID on file, in case a user tries to access 
 * certain files or folders that are restricted. A hacker might be able to 
 * string in a username in some cases by faking a form or url, but if we 
 * require the live session, it will be harder to fake. So throughout the
 * session, we will retrieve the user name based on the session ID, before
 * testing folders and files against any ACL. */
function setSavedSessionId($uname){
	$ufile = "netdisk.sessions";
	$reval = 0;
	if(!file_exists($ufile))
	{
		return 1;
	}
	$fp = fopen($ufile,"r+");
	if(!$fp)
	{
		return 2;
	}
	if(!flock($fp, LOCK_EX))
	{
		return 3;
	}

	$userSessionsArray = Array();
	
	/* Add existing sessions to the array.
	 * Skip addind if it is the current user because it will be changed. */
	while(!feof($fp))
	{
		if (fscanf($fp," %s %s\n",$userid,$usersession) && $userid != $uname);
		$userSessionsArray[$userid] = $usersession;
	}

	flock($fp,LOCK_UN);
	fclose($fp);
	
	/* Add the current user to the array. */
	$userSessionsArray[$uname] = session_id();

	/* Write all the users and sessions back to the file. */
	$fp = fopen($ufile,"w");
	if(flock($fp, LOCK_EX))
	{
		foreach ($userSessionsArray as $k => $v){
			if( !empty($v) && !empty($k) ) {
				$stringAllData = $k . " " . $v . "\n";
				fwrite($fp,$stringAllData);
			}
		}
		flock($fp,LOCK_UN);
		fclose($fp);
		return 0;

	} else {
		/* Return 4 and let them log in again. */
		fclose($fp);
		return 4;
	}
}

/* In some cases we will restrict access to files and folders unless 
 * the owner is logged in. This function compares the current user's
 * registered session id (setSessionId()) with the current session.
 * The user name is returned if the session exists. NULL if not. */ 
function getUserFromSavedSessionId(){
	$ufile = "netdisk.sessions";
	if(!file_exists($ufile))
	{
		echo "netdisk_error: gssi 1";
		return null;
	}
	$fp = fopen($ufile,"r");
	if(!$fp)
	{
		echo "netdisk_error: gssi 2";
		return null; 
	}
	if(!flock($fp, LOCK_EX))
	{
		echo "netdisk_error: gssi 3";
		return null;
	}

	$userSessionsArray = Array();
	$currentSession = session_id();
	$userIdFound = null;

	/* Loop through the session file to find see if there is a user with that
	 * id already logged in.  */
	$userid = null;
	$usersession = null;
	fscanf($fp,"%s %s\n",$userid,$usersession);
	while(!feof($fp))
	{
		if ( !empty($userid) && !empty($usersession) && ($usersession == $currentSession) ){
			$userIdFound = $userid;
			fseek($fp,1,SEEK_END);
		}
		fscanf($fp,"%s %s\n",$userid,$usersession);
	}

	flock($fp,LOCK_UN);
	fclose($fp);
	return $userIdFound;
}


/* Delete the saved session data based on the id and/or current session 
 * for the user. It happens on logout, when someone sends a fake a url or 
 * form data as a username or when folder access is requested but the session
 * has expired. */
function deleteSavedSessionId($uname){
	$ufile = "netdisk.sessions";
	$reval = 0;
	if(!file_exists($ufile))
	{
		return "netdisk_error: dssi 1";
	}
	$fp = fopen($ufile,"r");
	if(!$fp)
	{
		return "netdisk_error: dssi 2";
	}
	if(!flock($fp, LOCK_EX))
	{
		return "netdisk_error: dssi 3";
	}

	$userSessionsArray = Array();
	$currentSession = session_id();
	
	/* Read each line of the session file while searching for the existing user
	 * or current session id. Skip blank lines and the user we want to delete
	 * and add all other existing sessions to the array. */
	$userid = null;
	$usersession = null;
	while(!feof($fp))
	{
		fscanf($fp," %s %s\n",$userid,$usersession);
		if ( !empty($userid) && !empty($usersession) 
			&& ($usersession != $currentSession) && ($userid != $uname)){
			$userSessionsArray[$userid] = $usersession;
		}
	}

	flock($fp,LOCK_UN);
	fclose($fp);
	
	/* Write all the users and sessions back to the file. */
	$fp = fopen($ufile,"w");
	if(flock($fp, LOCK_EX))
	{
		foreach ($userSessionsArray as $k => $v){
			if( !empty($v) && !empty($k) ) {
				$stringAllData = $k . " " . $v . "\n";
				fwrite($fp,$stringAllData);
			}
		}
		flock($fp,LOCK_UN);
		fclose($fp);
		return 0;

	} else {
		/* Return 4 and let them log in again. */
		fclose($fp);
		return "netdisk_error: dssi 4";
	}
}


session_start(); 

if (! isset($_SESSION['SHOW_HIDDEN'])){
	$_SESSION['SHOW_HIDDEN'] = null;
}
if (! isset($_SESSION['AUTH'])){
	$_SESSION['AUTH'] = null;
}

if ( $_SESSION['AUTH'] != "TRUE")
{                                
	$userid = isset($_POST['userid'])? $_POST['userid'] : null;
	$tpass = isset($_POST['password'])? $_POST['password'] : null;

	if(!empty($userid) && !empty($tpass))
	{
		if(AuthUser($userid,$tpass))
		{
			$_SESSION['AUTH'] = "TRUE";
			$_SESSION['USERID'] = $userid;
			setUserGroup($userid);
			setSavedSessionId($userid);
			readDirFile();
		}
		else
		{
			echo "<h2>Invalid user id or password</h2><br>";
		}
	}
	
    if( $_SESSION['AUTH'] != "TRUE")
	{     
?>                                                                              
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
	      <meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8">
	      <title><?php echo $PAGE_TITLE ?></title>
	      <link rel="stylesheet" href="../skins/default/styles.css" type="text/css" />
	      <link rel="stylesheet" href="../skins/default/main-nav.css" type="text/css" />
	      
	</head>
	<body>
	<div id="headerTopDiv" style="">&nbsp;</div>
	<div id="main-nav">
	<center>
	<form name="authLoginForm" action="<?php echo $pageURL; ?>" method="POST">                                  
	<h3><?php echo $PAGE_TITLE; ?></h3>
	<table>
		<tr>
			<td>User ID:</td><td><input type="text" name="userid"></td>
		</tr>
		<tr>
			<td>Password:</td><td><input type="password" name="password"></td>                                
		</tr>
	</table>
	<input type=submit value="Login">                                           
<?php
	if ( isset($_POST['path']) ) {
            echo "<input type=hidden name=path value=\"".$_POST['path']."\">";
	}
?>
    </form>
    </center>                                                                         
<?php 
		exit;                                                                   
    }
}                                                                               
?>        
