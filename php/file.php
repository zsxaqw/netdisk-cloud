<?php
include ("auth.php");

$encoded_path = $_GET['path'];
$decoded_path = base64_decode($_GET['path']);
$path_param=urldecode($decoded_path);
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

if ( !is_dir($dir) ) { 

/* get the file to a web accessible location for display 

 I think it should make a user name folder in the symlink directory.
 Create the symlink.
 Forward the browser to the link.
 If the NDAS Device is disconnected, all the files must be unlinked.
 Also, unlink all the files when a user logs out.
 Possibly, unlink all files everytime, or delete the folder recursively
 each time a new directory view is loaded.
  
*/

$target = $decoded_path;
$link = "$encoded_path". 
	substr($decoded_path, strrpos($decoded_path, '.'), strlen($decoded_path));
	
$files_link_directory = $WEB_ROOT . $INSTALL_DIR .'/files';

//Encrypted name file link 
//$file_http_link = $HTTP_SITE . $INSTALL_DIR .'/files/' .$link;

/* Maybe use the original file name as the link? 
 * It looks better for downloads 
 */
$real_name_link = substr($decoded_path, (strrpos($decoded_path, '/') + 1), strlen($decoded_path));
$file_http_link = $HTTP_SITE . $INSTALL_DIR .'/files/' .$real_name_link;

chdir($files_link_directory);

if ($handle = opendir('.')) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
			if (is_link($entry)) 
				unlink($entry);        
		}
    }
    closedir($handle);
}

symlink($target, $real_name_link); // Encrypted link version symlink($target, $link);
header("location: $file_http_link");


/* Check if there is a directory for this user. Make one if not.
	mkdir -p files_link_directory/username
	chdir($files_link_directory/username);
	
	Delete all existing links:
	rm -f *
	
	make the new link
	
	Forward the browser to the new page. 
*/


/*   
$file_info = "encoded_path: $encoded_path<br>
decoded_path: $decoded_path<br>
dir: $dir<br>
path_param: $path_param<br>link: $link<br>";

echo "Some Attempt<br>";
echo "chdir('$files_link_directory')<br>";
//echo "symlink($target, $link)<br>";
echo "symlink($target, $real_name_link)<br>";

echo "header('location: $file_http_link')<br><br>";
die($file_info);
*/
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

	/* get the file to a web accessible location for display */

	$temp = tmpfile();
	fwrite($temp, file_get_contents($path_param));
	fseek($temp, 0);
	echo fread($temp,  filesize($temp));
	fclose($temp); // this removes the file
	
    die("File: ".$path_param);
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

	echo "<h3>Index of $visible_directory - Access $ndasmode</h3>
	<div class='file-access-notice'>";
	if($ndasmode == 'RO')
		echo "Current user has no permission to write in this folder.";
	else
		echo "Todo: Delete should ask for confirmation, then delete and reload.<br>
		&nbsp;";
	echo "</div>";	
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
	$count = 0;
	while ( $entry = readdir($dh) )
	{
		$count ++;
		
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
			if($entry != ".." && $entry != "." && $entry != 'lost+found' &&  ndasIsMountedVolumeWritable($entry) == 'RW'){
			?>
				<form method=GET action=delete.php onSubmit="if(confirm('Are you sure? Click OK to delete. Click Cancel if this is a mistake.')) return true; return false;">
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
	/*<tr><td><a href="<?php echo str_replace($WEB_ROOT,'',$e_file_param); ?>">
	  */
	?>
	  <tr><td><a href="./file.php?path=<?php echo base64_encode($e_file_param); ?>">
	                <?php echo $entry; ?></a></td>
	      <td><?php echo filesize($e_file); ?></td>
	      <td><?php echo filetype($e_file); ?></td>
	      <td><form method=GET action=delete.php  onSubmit="if(confirm('Are you sure? Click OK to delete. Click Cancel if this is a mistake.')) return true; return false;">
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