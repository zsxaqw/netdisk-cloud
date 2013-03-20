<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
    <meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8">
      <title><?php echo $PAGE_TITLE ?></title>
      <link rel="stylesheet" href="../skins/default/styles.css" type="text/css" />
      <link rel="stylesheet" href="../skins/default/main-nav.css" type="text/css" />
<?php
if (userIsAdmin()){
?>
		<script src="../js/admin.js" type="text/javascript" ></script>
<?php	
}     
?> 
</head>
<body>
<div id="headerTopDiv" style="">&nbsp;</div>
<div id="main-nav">
	<ul id="nav">
		<li><a href="logout.php">Logout</a></li>
<?php
if (userIsAdmin())	{
?>
		<li><a href="user_manage.php">Accounts</a></li>
		<li><a href="folder_manage.php">Folders</a></li>
		<li><a href="logviewer.php">Logs</a></li>
		<li><a href="configuration.php">Configuration</a></li>
<?php
} else {
?>
   <li><a href="user_manage.php">Account</a></li>
<?php
}
?>
		<li><a href="list.php">Home</a></li>
	</ul>
</div>

<div id="main-div">