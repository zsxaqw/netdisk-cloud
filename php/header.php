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
		<li><a href="list.php">Home</a></li>
		<li>My Account</li>
		<li><a href="logout.php">Logout</a></li>
<?php
if (userIsAdmin())	{
?>
		<li><a href="user_manage.php">Users</a>
			<ul>
			<li>List of Users</li>
			</ul>
		</li>
		<li><a href="folder_manage.php">Folders</a></li>
<?php
}
?>
	</ul>
</div>

<div id="main-div">