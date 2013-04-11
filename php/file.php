<?php
include ("auth.php");

$path_param=urldecode(base64_decode($_GET['path']));
if ( !isset($_GET['path'])) {
    $path_param=urldecode($_POST['path']);
}
if ( strpos($path_param, '/') != 0 ) {
    $path_param = '/' . $path_param;
}
if ( strlen($path_param) > 1 && strpos($path_param, '/', strlen($path_param) - 1) == strlen($path_param) - 1 ) {
    $path_param = substr($path_param, 0, strlen($path_param) - 1);
}
$dir=$path_param;
if ( $dir == $WEB_ROOT || $dir == "$WEB_ROOT/" ){
    die('Error: No permission to view. $dir');
}
if ( $dir == "." ){ 
    die('Error: System subfolder.');
}

$visible_directory=str_replace($TOP_MOUNTABLE_DIRECTORY,'',$dir);

$PAGE_TITLE = "Index of $visible_directory";

include_once("header.php");
?>
<script language="javascript">
function createDir()
{
	//window.open("create_folder.php","_cdir","width=300,height=300");
	window.open("","_cdir","width=350,height=200");
}
function upload()
{
	var rnum = Math.floor(Math.random() * 10000);
	window.open("choose_file.php","up"+rnum,"width=500,height=250");
	document.fup.target="up"+rnum;
	document.fup.submit();
}
</script>
<?php
if ( !is_dir($dir) ) { 
    die("No such directory - ".$path_param);
}

/* Get the username based on the session id and test if the user has 
 * permission to see this folder or file.*/
$allowAccess = false;
$currentUserName = "";
 
/* 1) Allow if directory is public */
if ( substr_count($dir,"$WEB_ROOT/public") > 0) { 
	echo "public";
	$allowAccess = true; 
} else {
	/* 2) Allow if directory contains the user name in home */ 
	$currentUserName = getUserFromSavedSessionId();
	if	(substr_count($dir,"$WEB_ROOT/$currentUserName") > 0){
		echo "homedir";
		$allowAccess = true;
	}
	
	/* 3) Check for "allowed" in netdisk.dir */
	
	/* 4) Admin has unlimited access */
	if ($_SESSION['USERGROUP'] === 'admin')
		$allowAccess = true;
	 
}
 
if ($allowAccess == true) {

	include('netdisk.functions.php');
	$ndasmode = ndasIsMountedVolumeWritable($dir);

	echo "<h3>Index of $visible_directory  (NDAS Mode $ndasmode)</h3>";
	
	?>
		
	<div class=list>
	<table cellpadding=0 cellspacing=0>
	<thead>
	  <tr><b>
	    <th>Name</th>
	    <th>Size</th>
	    <th>Type</th>
	    <th>Actions</th>
	  </tr>
	</thead>
	<?php
	$dh = opendir($dir);
	if ( !$dh ) {
	    die("cannot open $dir");
	}
	while ( $entry = readdir($dh) )
	{
	    if( $entry == '.' )
			continue;
	    if( $entry == '..' )
		{
	        $e_file = dirname($dir);
	        $e_file_param = dirname($path_param);
	    }
		else
		{
	    	$e_file = $dir.'/'.$entry;
	        $e_file_param = $path_param.'/'.$entry;
	    }
	        
	    if ( is_dir($e_file) ) { 
			if( $e_file == $WEB_ROOT )
				continue;
		if(!$_SESSION['SHOW_HIDDEN'] && strpos($entry,'.') === 0 && $entry !== '..' ) {
			continue;
		}
	
	?>
	  <tr><td>
	    <?php
	        echo "<a href=\"checkDir.php?path=";
	        echo base64_encode($e_file_param);
			echo "\" target=\"check\">";
	    	if ( $entry == '..' )
				echo 'Parent directory';
	        else 
	            echo $entry; 
	        echo "</a>";
	    ?>
	      </td>
	      <td>-</td>
	      <td>Directory</td>
	      <td>
			<?php
			if($entry != ".." && $entry != "." && $entry != 'lost+found'){
			?>
				<form method=GET action=delete.php>
	            <input type=hidden name=file value="<?php echo urlencode($e_file_param); ?>">
	            <input type=submit value=Delete class=btn></form>
			<?php } else {
				echo "&nbsp;";
			} ?>
	      </td>
	  </tr>
	<?php
	    } else {
		if(	!$_SESSION['SHOW_HIDDEN']  && strpos($entry,".") === 0 ) {
			continue;
		}
	
	?>
	  <tr><td><a href="<?php echo str_replace($WEB_ROOT,'',$e_file_param); ?>">
	                <?php echo $entry; ?></a></td>
	      <td><?php echo filesize($e_file); ?></td>
	      <td><?php echo filetype($e_file); ?></td>
	      <td><form method=GET action=delete.php>
	            <input type=hidden name=file value="<?php echo urlencode($e_file_param); ?>">
	            <input type=submit value=Delete class=btn></form>
	      </td>
	  </tr>
	<?php
	   } 
	}
	?>
		</table>
	</div>
	<br>
	<table>
		<tr>


		<?php 
		if($ndasmode === 'RW') :
		?>
			<td>
			<form name=fup method=POST action=choose_file.php>
			<input type=hidden name=path value=<?php echo base64_encode($path_param); ?>>
			<input type=button value="Upload" onclick="javascript:upload()">
			</form></td>
			<td>
			<form method=POST target=_cdir action=create_dir.php onSubmit="javascript:createDir()">
			<input type=hidden name=path value=<?php echo base64_encode($path_param); ?>>
			<input type=submit value="Create Directory">
			</form></td>
		<?php 
		endif; 
		?>
		<td><input type=button onclick="javascript:location.reload()" value=Refresh></td>
<?php 
} else {
	echo "Access Denied.
		<br>
	<table>
		<tr>";

} 
?>
			<td><form method=GET action=list.php>
			<input value="Go Back" type=submit>
			</form></td>
		</tr>
	</table>
<br>			
<?php 
include('footer.php');
?>