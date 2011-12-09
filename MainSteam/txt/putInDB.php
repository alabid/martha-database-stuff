<?php
/*
  This php file will go through a steam plant production log and 
  normalize the data in the sheet into the database.
 */
$dir = "./";


/*
$filename = "SteamPlantProductionLog2011.txt";
$fhandle = fopen($filename, "rb");
$contents = preg_split("/\r/", strtolower(fread($fhandle, filesize($filename))));
$buffer = "";
*/
include_once("senddata.php");
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
  

$allowed = array("2009", "2010", "2011");

//  2008-2011 only
$result;
$fhandle = null;
if (is_dir($dir)) {
  if ($dh = opendir($dir)) {
    while (($file = readdir($dh)) !== false) {
      if (strcmp(filetype($file), "dir") != 0 
	  && strcmp($file, "putInDB.php")
	  && after2000($file)) {
	$fhandle = fopen($file, "rb");
	$contents = preg_split("/\r/", strtolower(fread($fhandle, 
							filesize($file))));
	$result[] = readAndPutInArray($contents);
	// print_all_r($result);
      }
    }// ready in $result array
  closedir($dh);
  }   
}

// echo "<br/><br/>";
for ($i = 0; $i < count($result); $i++) {
  for ($j = 0; $j < count($result[$i]); $j++) {
    //print_r($result[$i][$j]);
    //echo "<br/><br/>";
    addEnergy($result[$i][$j]);
  }
}


mysql_close($connection);
function after2000($file) {
  global $allowed;

  foreach($allowed as $each_allowed) {
    if (strstr($file, $each_allowed)) {
      return true;
    }
  }
  return false;
}

function print_all_r($result) {
  foreach($result as $array) {
    print_r($array);
  }
}

// we don't need this right now
// */ 
//16330162 water
// all in MMBTU's




function readAndPutInArray($contents) {
  $header = "";
  $headers = array();
  $start = false;
  $formerline = "";
  $month = "";
  $valueIndex = 1; // for steam
  
  $BTUConv = array(
		 "steam" => 1000, /* 1 MB/HR => 1.000MBTU/Unit */
		 "oil" => 0.142  // in MMBTU's
		 );

  $units = array(
	       "steam" => "K LB/HR" ,
	       "oil" => "GAL"
		 );



  $req_headers = array("steam");
  $entry = array("Day" => null,
		 "Month" => null,
		 "Year" => null,
		 "Hour" => 0,
		 "Minute" => 0,
		 "Second" => 0,
		 "MeasuredValue" => null,
		 "BTUConversion" => null,
		 "SupplierName" => "Xcel",
		 "BuildingName" => "Facilities Building/ Steam Plant",
		 "Unit" => "Klb.",
		 "Type" => null
		 );

  $months = array("January", "February", "March", "April", "May", "June",
		"July", "August", "September", "October", "November",
		"December");

  $entries = array(); // will store an array of entries
  $num = 0;
  $year = 0;
  // global $months;
  // global $req_headers;
  // print_r($req_headers);
  // print_r($months);
  foreach($contents as $line) {
    // echo $line . "<br/>";
    if (preg_match("/^(\r?\n?)+$/", $line) ||
	strstr($line, "total") ||
	strstr($line, "plant production")) {
      continue;
    }
    if ((strstr($line, "date") 
	|| strstr($line, "steam"))
	&& !strstr($line, "plant production")) {
      $num++;
      // $headers = get_req_headers(preg_split("/\t/", $line), $req_headers);
      continue;
    }
    if (lineInArray($months, $line) === false){
      if (strlen($line) < 30) continue;
      else {
	resetEntry($entry);
	//	$entry[$headers[
	if ($months == null) continue;
	$entry["Month"] = array_search($month, $months) + 1;
	$linevals = explode("\t", $line); // fill this as soon as possible
	$entry["Day"] = $linevals[0];
	$entry["Year"] = $year;
	$entry["Type"] = $req_headers[0]; // since there is only one thing there for now
	$entry["MeasuredValue"] = $linevals[$valueIndex];
	$entry["BTUConversion"] = (double)$BTUConv[$req_headers[0]] * $linevals[$valueIndex];
	$entries[] = $entry;
	// print_r($entries);
      }
    } else {
      $month = lineInArray($months, $line);
      // $entry
      /*
      echo "<br/>start here" . "<br/>";
      echo "month: $month" . "<br/>";
      */
      $year_str = explode("-", $line);
      $year = $year_str[1];
      // echo $line . "<br/>";
    }
    // continue;
    // so at this point, $headers has stuff,
    // and $month corresponds to the supplied month
    /* echo "month: " .$month;
    print_r($headers);
    echo "line $num<br/><br/>";
    */
    
  }
  echo "Found " . $num . " headers" . "<br/><br/>";
  // print_r($entries);
  return $entries;
}

function resetEntry($entry) {
  $entry = array("Day" => null,
		 "Month" => null,
		 "Year" => null,
		 "Hour" => 0,
		 "Minute" => 0,
		 "Second" => 0,
		 "MeasuredValue" => null,
		 "BTUConversion" => null,
		 "SupplierName" => "Xcel",
		 "BuildingName" => "Facilities Building/ Steam Plant",
		 "Type" => null,
		 "Unit" => "Klb."
		 );
}

function get_req_headers($header_tokens, $req_headers) {
  $headers = array();
  $header_new = "";
  $num = 0;

  foreach ($header_tokens as $header) {
    if (($header_new = lineInArray($req_headers,$header)) !== false &&
	!strstr($header, "boiler")) {
      $headers[$num] = $header_new;
    }
    $num++;
  }
  return $headers;
}

function lineInArray($array, $line) {
  foreach ($array as $each) {
    if (strstr($line, strtolower($each))) {
	return $each;
    }
  }
  return false;
}

?>