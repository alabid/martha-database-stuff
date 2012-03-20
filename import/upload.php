<?php

include_once("campusData.php");
include_once("senddata.php");

if (empty($_FILES) && empty($_POST) && isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) == 'post') {   
    $poidsMax = ini_get('post_max_size');
    $oElement->addError("fileoverload", "Your file exceeds maximum allowed size $poidsMax.");
} 
$filename = $HTTP_POST_FILES['uploadedfile']['name'];
$filename = str_replace("#", "No.", $filename);
$filename = str_replace("$", "Dollar", $filename);
$filename = str_replace("%", "Percent", $filename);
$filename = str_replace("^", "", $filename);
$filename = str_replace("&", "and", $filename);
$filename = str_replace("*", "", $filename);
$filename = str_replace("?", "", $filename); 
$uploaddir = "uploads/";
$path = $uploaddir.$filename; 

$allowedExtensions = array("txt","csv","xml","doc","xls");
foreach ($_FILES as $file) {
  if ($file['tmp_name'] > '') {
    if (!in_array(end(explode(".", strtolower($file['name']))), $allowedExtensions)) {
      die($file['name'].' is an invalid file type!<br/>'.
	  '<a href="javascript:history.go(-1);">'.
	  '&lt;&lt Sorry, Go Back</a>');
    }
  }
} 
if($uploadedfile != none){ //AS LONG AS A FILE WAS SELECTED...
  
  if(copy($HTTP_POST_FILES['uploadedfile']['tmp_name'], $path)){ //IF IT HAS BEEN COPIED...
    
    //GET FILE NAME
    $theFileName = $HTTP_POST_FILES['uploadedfile']['name'];
    
    //GET FILE SIZE
    $theFileSize = $HTTP_POST_FILES['uploadedfile']['size'];
    
    if ($theFileSize>999999){ //IF GREATER THAN 999KB, DISPLAY AS MB
      $theDiv = $theFileSize / 1000000;
      $theFileSize = round($theDiv, 1)." MB"; //round($WhatToRound, $DecimalPlaces)
    } else { //OTHERWISE DISPLAY AS KB
      $theDiv = $theFileSize / 1000;
      $theFileSize = round($theDiv, 1)." KB"; //round($WhatToRound, $DecimalPlaces)
    }
    $connection = mysql_connect("localhost","root","");
   if (!$connection)
     {
       die("Database connection failed:". mysql_error());
     }

   $db_select = mysql_select_db("EnergyData",$connection);
   if (!$db_select)
     {
    die("Database select failed:".mysql_error());
     }

    $f = fopen($path,"r") or die("Can't open file");
    $data =fread($f, filesize($path));
    //echo $data;
    $content = explode("\n",$data);
    if (sizeof($content) == 1){
      $content = explode("\r",$data);
    }
    var_dump($content);
    //$functionName($content);
    fclose($f);



    echo <<<UPLS
      <table cellpadding="5" width="300">
      <tr>
      <td align="Center" colspan="2"><font color="#009900"><b>Upload Successful</b></font></td>
      </tr>
      <tr>
      <td align="right"><b>File Name: </b></td>
      <td align="left">$theFileName</td>
      </tr>
      <tr>
      <td align="right"><b>File Size: </b></td>
      <td align="left">$theFileSize</td>
      </tr>
      <tr>
      <td align="right"><b>Directory: </b></td>
      <td align="left">$uploaddir</td>
      </tr>
      </table>
      
UPLS;
    
  } else {
   

  }
}  


?>