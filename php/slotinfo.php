<?php 

include ('netdisk.functions.php');
$showslot = isset($_GET['slot'])? $_GET['slot'] : die("Invalid Slot");

?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<title>NDAS Drive Details</title>
	<link rel="stylesheet" href="../css/styles.css" type="text/css" />
      
</head>
<body>
<center>
<?php


echo ndasShowDiskInformation($showslot);


?>
<br>
<a href='javascript:self.close()'>Close window</a>
</center>
</body>
</html>


