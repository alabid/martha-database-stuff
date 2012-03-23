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
	//var_dump($entry["MeasuredValue"]);
	$entry["BTUConversion"] = (double)$btuconv *  (double) $entry["MeasuredValue"];

	$entries[] = $entry;
	
      }
    } else {
      $month = lineInArray($months, $line);
      $year_str = explode("-", $line);
      $year = $year_str[1];
    }
  }
  //echo "Found " . $num . " headers" . "<br/><br/>";
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


function lineInArray($array, $line) {
  foreach ($array as $each) {
    if (strstr($line, strtolower($each))) {
	return $each;
    }
  }
  return false;
}


function othersHandler($contents, $year) {
  /**
     $type could be oil, gas, or steam
   **/
  //echo "the year is ".$year;
  if (intval($year) < 2009) {
    echo "Only years after 2009 are allowed!<br/>";
    return;
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
  
  foreach($result as $array) {
    foreach($array as $obj) {
      addEnergy($obj);
    }
  }
  // close the connection. Save handles. Save resources. Save the world.
}



function waterHandler($contents,$year){
  $results = readResultWater($contents, ((int)$year) > 2008, $year);
  //print_out_result($results);
  foreach($results as $each) {
      addEnergy($each);
  }
    
  echo "Successfully update the database!";
}


function print_out_result($results) {
  foreach($results as $each) {
 
      print_r($each);
      echo "<br/>";
    
  }
}


function readResultWater($contents, $after2008, $year) {
  $months = array("January", "February", "March", "April", "May", "June",
		"July", "August", "September", "October", "November",
		"December");
  $indexAfter2008 = array(
			  "DomWater" => 6,
			  "MakeUpWater" => 3 ,
			   "LoopWater" => 7,
			  "TowerWater" => 8
		          );
  $indexNotAfter2008 = array(
			     "DomWater" => 5,
			     "MakeUpWater" => 4,
			     "LoopWater" => 6,
			     "TowerWater" => 7
			     );
  $entry1 = array("Type" => "DomWater");
  $entry2 = array("Type" => "TowerWater");
  $entry3 = array("Type" => "RemainderWater");
  $entry4 = array("Type" => "MakeUpWater");
  $entry5 = array("Type" => "LoopWater");

  for ($i = 1; $i < 6; $i++) {
    ${entry.$i}["Day"] = null;
    ${entry.$i}["Month"] = null;
    ${entry.$i}["Year"] = null;
    ${entry.$i}["Hour"] = 0;
    ${entry.$i}["Minute"] = 0;
    ${entry.$i}["Second"] = 0;

    ${entry.$i}["MeasuredValue"] = null;
    ${entry.$i}["BTUConversion"] = null;
    ${entry.$i}["SupplierName"] = "Carleton College";
    ${entry.$i}["BuildingName"] = "Facilities Building/ Steam Plant";
    ${entry.$i}["Unit"] = "GAL";
  }
  // echo $contents."<br><br>";
  foreach($contents as $line) {
    // echo $line."<br/>";
    if (preg_match("/^(\r\n?)+$/", $line) 
	|| strstr($line, "total") 
	|| strstr($line, "production")) {
      continue;
    }
    if ((strstr($line, "date")
	 || strstr($line, "steam"))
	&& !strstr($line, "plant production")) {
      continue;
    }
    if (lineInArray($months, $line) === false){
      if (strlen($line) < 20) {     ;continue;}
      else {
	resetWaterEntry($entry1, $entry2, $entry3, $entry4, $entry5);
	// reset an $entry into its initial state
	// so that you can fill it again with new values this time
	// if ($months == null) continue;
	$linevals = explode("\t" , $line);
	if ($linevals[0]==null){
	  continue;
	}
	for ($i = 1; $i < 6; $i++) {
	  ${entry.$i}["Month"] = array_search($month, $months) + 1;
	  ${entry.$i}["Day"] = $linevals[0];
	  ${entry.$i}["Year"] = $year;
	}
	$entry1["MeasuredValue"] = (int)
	  preg_replace("/^\"?(\d*)(,?)(\d*)\"?$/", "$1$3",
		       $linevals[($after2008 ?
			       $indexAfter2008["DomWater"] : 
				  $indexNotAfter2008["DomWater"])]);
	$entry4["MeasuredValue"] = (int)
	  preg_replace("/^\"?(\d*)(,?)(\d*)\"?$/", "$1$3",
		       $linevals[($after2008 ?
				  $indexAfter2008["MakeUpWater"] : 
				  $indexNotAfter2008["MakeUpWater"])]);
	$entry5["MeasuredValue"] = (int)
	  preg_replace("/^\"?(\d*)(,?)(\d*)\"?$/", "$1$3",
		       $linevals[($after2008 ?
				       $indexAfter2008["LoopWater"] : 
				  $indexNotAfter2008["LoopWater"])]);
	$entry2["MeasuredValue"] = (int)
	  preg_replace("/^\"?(\d*)(,?)(\d*)\"?$/", "$1$3",
	            	    $linevals[($after2008 ?
			       $indexAfter2008["TowerWater"] : 
				       $indexNotAfter2008["TowerWater"])]);
	if ($entry1["MeasuredValue"]==0){
	  $entry3["MeasuredValue"] = 0;
	}else{
	  $entry3["MeasuredValue"] = $entry1["MeasuredValue"]
	  - ($entry2["MeasuredValue"] 
	     + $entry4["MeasuredValue"]			
	     + $entry5["MeasuredValue"]);
	}
	
	if (!$entry5["MeasuredValue"]) {
	  $entry5["MeasuredValue"] = 0;
	}
	if (!$entry2["MeasuredValue"]) {
	  $entry2["MeasuredValue"] = 0;
	}

	for ($i = 0; $i < 6; $i++) {
	  if (${entry.$i} != NULL) 
	    $entries[] = ${entry.$i};
	}
	
      }
    } else {
      $month = lineInArray($months, $line);
    }
  }
  return $entries;
}



function resetWaterEntry($entry1, $entry2, $entry3, $entry4, $entry5) {
  for ($i = 1; $i < 6; $i++) {
    ${entry.$i}["Day"] = null;
    ${entry.$i}["Month"] = null;
    ${entry.$i}["Year"] = null;
    ${entry.$i}["Hour"] = 0;
    ${entry.$i}["Minute"] = 0;
    ${entry.$i}["Second"] = 0;

    ${entry.$i}["MeasuredValue"] = null;
    ${entry.$i}["BTUConversion"] = null;
    ${entry.$i}["SupplierName"] = "Carleton College";
    ${entry.$i}["BuildingName"] = "Facilities Building/ Steam Plant";
    ${entry.$i}["Unit"] = "GAL";
  }
}
  
function logHandler($contents, $year){
  waterHandler($contents,$year);
  othersHandler($contents,$year);
}

?>