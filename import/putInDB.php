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

$dir = "./"; // current directory

// include the crucial "senddata.php" file
include_once("senddata.php");

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
$allowed = array("2009", "2010", "2011");

// $result stores the result of collating the data
// from each file
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
	// we use mac carriage returns here (\r)
	$result[] = readAndPutInArray($contents, // $contents -- split lines from opened file
				      3,  // $valueIndex -- the column number of the column that we want to get data from
				      // this should correspond to the column occupied by the $resource (see below).
				      "oil", // $resource -- the name of the resource of energy type that we're interested in (e.g. gas or oil)
				      0.142e6, // $btuconv -- one unit of $resource == amount in $btuconv (in BTU's)
				      "gal",  // $unit -- unit of $resource
				      "Xcel", // $supplierName -- supplierName of resource
				      "Facilities Building/Steam Plant"); // $buildingName -- building Name where $resource is produced
	                                                                  // e.g. facilities buidling/steam plant for production of steam
      }
    }
  closedir($dh);
  }   
}

print_all_r($result);exit();

for ($i = 0; $i < count($result); $i++) {
  for ($j = 0; $j < count($result[$i]); $j++) {
    print_r($result[$i][$j]);
    // addEnergy($result[$i][$j]);
  }
}

// close the connection. Save handles. Save resources. Save the world.
mysql_close($connection);

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

/*
  print_all_r --
  $result is an array of array
  prints recursively (using the built-in print_r)
  everything in $result
 */
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
	resetEntry($entry, $supplierName, $buildingName); 
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

function resetEntry($entry, $supplierName, $buildingName) {
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
		 "Unit" => $units["oil"]
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