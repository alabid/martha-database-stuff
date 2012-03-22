<?php
/*
  This php file is made specifically for inserting tuples of data
  in the spreadsheets in the "Campus Main_Steam" into the database.
  It walks the directory and then calls the readAndPutInArray
  functions which puts the data in the spreadsheet in an appropriate
  format in a php array (which works like a dictionary with keys and 
  corresponding values).
  The imported function "addEnergy" in the "senddata.php" folder
  is then called which inserts the tuples of data into the
  database for future use.
 */
include_once("senddata.php");


$allowed = array("2009", "2010", "2011");
/*
  checks if the $file corresponds to a year after
  year 2000.
 */
function after2000($file) {
  global $allowed;
  
  foreach($allowed as $each_allowed) {
    if (strstr($file, $each_allowed)) {
      return true;
    }
  }
  return false;
}

function add_all_r($result) {
  foreach($result as $array) {
    foreach($array as $obj) {
      addEnergy($obj);
    }
  }
}
function print_all_r($result) {
  foreach($result as $array) {
    foreach ($array as $obj) {
      print_r($obj);
      echo "<br/>";
    }
  }
}

/*
The function that does most if not all of the work->
readAndPutInArray($contents)
 */
function readAndPutInArray($contents, $valueIndex, $resource, $btuconv, $unit, $supplierName, $buildingName) {
  $header = "";
  $headers = array();
  $start = false;
  $formerline = "";
  $month = "";
  

  $entry = array("Day" => null,
		 "Month" => null,
		 "Year" => null,
		 "Hour" => 0,
		 "Minute" => 0,
		 "Second" => 0,
		 "MeasuredValue" => null,
		 "BTUConversion" => $btuconv,
		 "SupplierName" => $supplierName,
		 "BuildingName" => $buildingName,
		 "Unit" => $unit,
		 "Type" => null
		 );

  $months = array("January", "February", "March", "April", "May", "June",
		"July", "August", "September", "October", "November",
		"December");

  $entries = array(); // will store an array of entries
  $num = 0;
  $year = 0;
 

  foreach($contents as $line) {
    if (preg_match("/^(\r?\n?)+$/", $line) ||
	strstr($line, "total") ||
	strstr($line, "plant production")) {
      continue;
    }
    if ((strstr($line, "date") 
	|| strstr($line, "steam"))
	&& !strstr($line, "plant production")) {
      $num++;
      continue;
    }
    if (lineInArray($months, $line) === false){
      if (strlen($line) < 30) continue;
      else {
	resetEntry($entry, $btuconv, $supplierName, $buildingName, $unit);
	// so that you can fill it again with new values this time
	if ($months == null) continue;
	$entry["Month"] = array_search($month, $months) + 1;
	$linevals = explode("\t", $line); // fill this as soon as possible
	$entry["Day"] = $linevals[0];
	$entry["Year"] = $year;
	$entry["Type"] = $resource;


	if (preg_match("/^(\"|\')\d+.*\d+(\"|\')$/", $linevals[$valueIndex])) {
	  $entry["MeasuredValue"] = 
	    preg_replace("/^(\d*)(,)(\d*)$/", 
			 "$1$3", 
			 substr($linevals[$valueIndex], 
				1, strlen($linevals[$valueIndex])-2));
	} else {
	  $entry["MeasuredValue"] = $linevals[$valueIndex];
	}
	var_dump($entry["MeasuredValue"]);
	$entry["BTUConversion"] = (double)$btuconv *  (double) $entry["MeasuredValue"];

	$entries[] = $entry;
	
      }
    } else {
      $month = lineInArray($months, $line);
      $year_str = explode("-", $line);
      $year = $year_str[1];
    }
  }
  echo "Found " . $num . " headers" . "<br/><br/>";
  return $entries;
}

function resetEntry($entry, $btuconv, $supplierName, $buildingName, $unit) {
  $entry = array("Day" => null,
		 "Month" => null,
		 "Year" => null,
		 "Hour" => 0,
		 "Minute" => 0,
		 "Second" => 0,
		 "MeasuredValue" => null,
		 "BTUConversion" => $btuconv,
		 "SupplierName" => $supplierName,
		 "BuildingName" => $buildingName,
		 "Unit" => $unit,
		 "Type" => null
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

function put_in_db($contents, $year) {
  /**
     $type could be oil, gas, or steam
   **/
  if (intval($year) < 2009) {
    echo "Only years after 2009 are allowed!<br/>";
    return;
  }
  
  // make a connection here
  $connection = mysql_connect("localhost","root","");
  if (!$connection) {
    die("Database connection failed:". mysql_error());
  }
  
  $db_select = mysql_select_db("EnergyData",$connection);
  if (!$db_select) {
    die("Database select failed:".mysql_error());
  }
  
  // the years that files you are allowed to traverse in
  // the dictionary correspond to.
  
  // $result stores the result of collating the data
  // from each file

  $columns = array(
		   "oil" => 5,
		   "gas" => 4,
		   "steam" => 1
		   );

  $units = array(
		 "oil" => "gal",
		 "gas" => "mcf",
		 "steam" => "klb."
		 );

  $btus = array(
		"oil" => 0.142e6,
		"gas" => 1.010e6,
		"steam" => 1.0e3
		);
  $types = array("oil", "gas", "steam");

  $result = array();		 
  for ($i = 0; $i < sizeof($types); $i++) {
    $result[] = readAndPutInArray($contents,
				  $columns[$types[$i]],
				  $types[$i],
				  $btus[$types[$i]],
				  $units[$types[$i]],
				  "Xcel",
				  "Facilities Building/ Steam Plant");
  }
  
  print_all_r($result);
  add_all_r($result);
  // close the connection. Save handles. Save resources. Save the world.
  mysql_close($connection);
}

function test_put_in_db() {
  $file = "SteamPlantProductionLog2010.txt";
  $fhandle = fopen($file, "rb");
  $contents = preg_split("/\r/",
			 strtolower(fread($fhandle,
					  filesize($file))));
  $year = "2010";
  put_in_db($contents, $year);
}

test_put_in_db();
?>