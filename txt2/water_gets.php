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
 */



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
	  && strstr($file, "19")
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
  }
}

$outstr = "Day\tMonth\tYear\tDomWater\tMakeUpWater\tLoopWater\tTowerWater\tRemainder\r";
$dlim = " \t ";
$fout = fopen("out2.txt", "w");
foreach($result as $each) {
  foreach($each as $obj) {
    // print_r($obj);

    $outstr .= $obj["Day"] . $dlim .
               $obj["Month"] . $dlim .
               $obj["Year"] . $dlim .
               $obj["DomWater"] . $dlim .
               $obj["MakeUpWater"] . $dlim . 
               $obj["LoopWater"] . $dlim . 
               $obj["TowerWater"] . $dlim .
               $obj["Remainder"];
     
    $outstr .= "\r";
  }
}
echo nl2br($outstr);
fwrite($fout, $outstr);
exit();


function print_out_result($results) {
  foreach($results as $each) {
    var_dump($each);
    echo "<br/>";
  }
}

function readResultWater($contents, $after2008, $year) {
  $months = array("Jan", "Feb", "Mar", "Apr", "May", "Jun",
		"Jul", "Aug", "Sep", "Oct", "Nov",
		"Dec");
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

    $entry = array("DomWater" => null,
		 "MakeUpWater" => null,
		 "LoopWater" => null,
		 "TowerWater" => null,
		 "Remainder" => null,
		 "Month" => null,
		 "Day" => null,
		 "Year" => null
		 );

  foreach($contents as $line) {
    // echo $line."<br/>";
    if (preg_match("/^(\r\n?)+$/", $line) 
	|| strstr($line, "total") 
	|| strstr($line, "production")) {
      continue;
    }
    if ((strstr($line, "date")
	 || strstr($line, "steam"))
	&& !strstr($line, "production")) {
      continue;
    }
    if (lineInArray($months, $line) === false){
      if (strlen($line) < 20) {     ;continue;}
      else {
	resetEntry($entry); // reset an $entry into its initial state
	// so that you can fill it again with new values this time
	// if ($months == null) continue;
	$entry["Month"] = array_search($month, $months) + 1;
	$linevals = explode("\t", $line); // fill this as soon as possible
	if ($linevals[0]==null){
	  continue;
	}
	$entry["Day"] = $linevals[0];
	$entry["Year"] = $year;

	$entry["DomWater"] = (int)
	  preg_replace("/^\"?(\d*)(,?)(\d*)\"?$/", "$1$3",
		       $linevals[($after2008 ?
			       $indexAfter2008["DomWater"] : 
				  $indexNotAfter2008["DomWater"])]);
	$entry["MakeUpWater"] = (int)
	  preg_replace("/^\"?(\d*)(,?)(\d*)\"?$/", "$1$3",
		       $linevals[($after2008 ?
				  $indexAfter2008["MakeUpWater"] : 
				  $indexNotAfter2008["MakeUpWater"])]);
	$entry["LoopWater"] = (int)
	  preg_replace("/^\"?(\d*)(,?)(\d*)\"?$/", "$1$3",
		       $linevals[($after2008 ?
				       $indexAfter2008["LoopWater"] : 
				  $indexNotAfter2008["LoopWater"])]);
	$entry["TowerWater"] = (int)
	  preg_replace("/^\"?(\d*)(,?)(\d*)\"?$/", "$1$3",
	            	    $linevals[($after2008 ?			       $indexAfter2008["TowerWater"] : 
				       $indexNotAfter2008["TowerWater"])]);
	if ($entry["DomWater"]!=null){
	  $entry["Remainder"] = $entry["DomWater"] - ($entry["MakeUpWater"]
						      + $entry["LoopWater"]
						      + $entry["TowerWater"]);
	}else{
	  $entry["Remainder"] = 0;
	  $entry["DomWater"] = 0;
	}
	if (!$entry["LoopWater"]) {
	  $entry["LoopWater"] = 0;
	}
	if (!$entry["TowerWater"]) {
	  $entry["TowerWater"] = 0;
	}

	$entries[] = $entry;
	// print_r($entry);
      }
    } else {
      $month = lineInArray($months, $line);
    }
  }
  return $entries;
}

function resetEntry($entry) {
  $entry = array("DomWater" => null,
		 "MakeUpWater" => null,
		 "LoopWater" => null,
		 "TowerWater" => null,
		 "Remainder" => null,
		 "Month" => null,
		 "Day" => null,
		 "Year" => null
		 );
}

function lineInArray($array, $line) {
  foreach($array as $each) {
    if (strstr($line, strtolower($each))) {
      return $each;
    }
  }
  return false;
}


?>