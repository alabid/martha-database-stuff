<?php

 /*
  water_gets.php --
  get an array of results where 
  result = {
   domWater : ____, // domestic water
   makeUpWater : ____, // make-up water
   loopWater : _____, // loop water
   towerWater : ____,
   remainder : _____
  }
  This file is very similar to water_gets.php except that the
  data generated will go into the database instead
  of an excel sheet.
 */

include("senddata.php");

$con = mysql_connect("localhost" , "root", "");
if (!$con) 
  die("Database connection failed: " . mysql_error());

$db_sel = mysql_select_db("EnergyData", $con);
if (!$db_sel) 
  die("Database select failed: " . mysql_error());
echo "hello";

$handle = null;
$result = array();
$dir = "./";

if (is_dir($dir)) {
  if ($dh = opendir($dir)) {
    while (($file = readdir($dh)) !== false) {
      if (strcmp(filetype($file), "dir") != 0 
	  && strcmp($file, "putInDB.php") != 0
	  && strcmp($file, "senddata.php") != 0
	  && strcmp($file, basename(__FILE__)) != 0
	  && strcmp($file, ".DS_Store") != 0
	  && strstr($file, "20")
	  || strstr($file, "2000")) {
	$year = substr($file, strpos($file, ".") - 4, 4);
	$after2008 = ((int)$year) > 2008;
	$handle = fopen($file, "rb");
	$contents = preg_split("/(\r\n?)+/", 
			       strtolower(fread($handle, 
						filesize($file))));
	$result[] = readResultWater($contents, $after2008, $year);
	// print_out_result($result);
      }
    }
    closedir($dh);
  }
}


print_out_result($result);
exit();


function print_out_result($results) {
  foreach($results as $each) {
    foreach($each as $obj) {
      var_dump($obj);
      //addEnergy($obj);
      echo "<br/>";
    }
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
	resetEntry($entry1, $entry2, $entry3, $entry4, $entry5);
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

function lineInArray($array, $line) {
  foreach($array as $each) {
    if (strstr($line, strtolower($each))) {
      return $each;
    }
  }
  return false;
}

function resetEntry($entry1, $entry2, $entry3, $entry4, $entry5) {
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

?>