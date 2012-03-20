<html>
<title>Carleton College Energy Data</title>
<LINK href="css/main.css" rel="stylesheet" type="text/css">
<body>
<h1>Carleton College Energy Data</h1>


  <?php
    $file = fopen("guide.txt","r") or die("Can't open file");
     $data =fread($file, filesize("guide.txt"));
 
    $content = explode("\n",$data);
    if (sizeof($content) == 1){
      $content = explode("\r",$data);
    }
echo "<frameset cols='25%,50%,25%'>";
echo "<frame/>";
echo "<frame>";
    for ($i = 0;$i< sizeof($content);$i++){
      echo "<p>".$content[$i]."</p>";
     }
     echo "<br/></frame><frame/></frameset>";
  ?>

<div id="uploadInterface">

  
Choose what you will upload:<select id="choices" class="classic1">
 <option value="wind">Wind Turbine</option>
 <option value="log">Steam Plant logs</option>
 <option value="elecconsump">Electric consumption</option>
 <option value="elecdemand">Electric demand</option>
 <option value="bill">Bills</option>
<input id="button" class="button" type="submit" value="Start" onclick="uploadHandler()"/>

</div>
  
<script language="javascript">
  function uploadHandler(){
     var choices = document.getElementById("choices");
     var div = document.getElementById("uploadInterface");

     div.innerHTML = "<form class='classic1' enctype='multipart/form-data'" +
     "action='upload.php?type="+choices.value+"'"+" method='POST'>" +
     "</select><br>" +
     "<input type='hidden' name='MAX_FILE_SIZE' value='100000' />"+
     "Choose a file to upload: <input name='uploadedfile' type='file' /><br />"+
     "<input id='formbutton' class='formbutton' type='submit' value='Upload File' />"+
     "</form>";

     div.innerHTML += "<input type='submit' value='restart' onclick='reload()'/>";
     
     }
     function reload(){
    
     var div = document.getElementById("uploadInterface");
     
     div.innerHTML = 'Choose what you will upload:<select id="choices" class="classic1">'+
       '<option value="wind">Wind Turbine</option>'+
	 '<option value="log">Steam Plant logs</option>'+
	   '<option value="elecconsump">Electric consumption</option>'+
	 '<option value="elecdemand">Electric demand</option>'+
	   '<option value="bill">Bills</option>'+
	     '<input id="button" class="button" type="submit" value="Start" onclick="uploadHandler()"/>';
	     
	     }
	     </script>
</body>
</html>