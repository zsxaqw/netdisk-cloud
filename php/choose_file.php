<?php 
include('auth.php');
$postpath = isset($_POST['path'])? $_POST['path'] : null;
$path = base64_decode($postpath);

//echo "pp:$postpath<br>";
//echo "dp:$path<br>";

if ($path == null){
	die('Need Destination.');
}
$visible_directory=str_replace($TOP_MOUNTABLE_DIRECTORY,'',$path);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Single</title>

    <script type="text/javascript">
	var refreshOpener = 0;

      function fileSelected() {
        var file = document.getElementById('fileToUpload').files[0];
        if (file) {
          var fileSize = 0;
          if (file.size > 1024 * 1024)
            fileSize = (Math.round(file.size * 100 / (1024 * 1024)) / 100).toString() + 'MB';
          else
            fileSize = (Math.round(file.size * 100 / 1024) / 100).toString() + 'KB';

          document.getElementById('fileName').innerHTML = 'Name: ' + file.name;
          document.getElementById('fileSize').innerHTML = 'Size: ' + fileSize;
          document.getElementById('fileType').innerHTML = 'Type: ' + file.type;
        }
      }

      function uploadFile() {
        var fd = new FormData();
        fd.append("path", "<?php echo $postpath; ?>");
        fd.append("fileToUpload", document.getElementById('fileToUpload').files[0]);
       var xhr = new XMLHttpRequest();
        xhr.upload.addEventListener("progress", uploadProgress, false);
        xhr.addEventListener("load", uploadComplete, false);
        xhr.addEventListener("error", uploadFailed, false);
        xhr.addEventListener("abort", uploadCanceled, false);
        xhr.open("POST", "upload.ht5.php");
        xhr.send(fd);
      }

      function uploadProgress(evt) {
        if (evt.lengthComputable) {
          var percentComplete = Math.round(evt.loaded * 100 / evt.total);
          document.getElementById('progressNumber').innerHTML = percentComplete.toString() + '%';
        }
        else {
          document.getElementById('progressNumber').innerHTML = 'unable to compute';
        }
      }

      function uploadComplete(evt) {
        /* This event is raised when the server send back a response */
        alert(evt.target.responseText);
		opener.location.href='file.php?path=<?php echo $postpath; ?>';
      }

      function uploadFailed(evt) {
        alert("There was an error attempting to upload the file.");
      }

      function uploadCanceled(evt) {
        alert("The upload has been canceled by the user or the browser dropped the connection.");
      }
    </script>
</head>
<body>
  <h2>Upload files</h2>
  	Destination:<br>&nbsp;&nbsp;<?php echo $visible_directory;?><br>
  <form id="form1" enctype="multipart/form-data" method="post" 
	action="upload.ht5.php">
    <div class="row">
      <label for="fileToUpload">Select a File to Upload</label><br />
      <input type="file" name="fileToUpload" id="fileToUpload" 
		onchange="fileSelected();"/>
    </div>
    <div id="fileName"></div>
    <div id="fileSize"></div>
    <div id="fileType"></div>
    <div class="row">
      <input type="button" onclick="uploadFile()" value="Upload" />
		<div style="display:inline" id="progressNumber"></div>
    </div>
    
	<input type="hidden" id="path" name="path" value="<?php echo $_POST['path'];?>" />
  </form>

<input type=button onclick="javascript:self.close()" value=Close>
</body>
</html>

