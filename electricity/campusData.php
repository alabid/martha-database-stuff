<?php
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
function meterHandler($row){
  $meterType = strcmp(strtolower($row["MeterType"]),"digital")==0 ? "Digital":(strcmp(strtolower($row["MeterType"]),"analog")==0 ? "Analog":"");

  $meterInfo = array(
		     "MeterDescr"=>$row["MeterDescr"],
		     "MeterNum"=>$row["MeterNum"],
		     "BuildingID" => findBuildingID($row),
		     "FuelTypeID" =>addFuelType($row),
		     "MeterType" => $meterType,
		     "MeterManufID" => addSupplier($row),
		     "MeterModel" =>$row["MeterModel"],
		    
		     "SiemensPt" =>$row["SiemensPt"]
		     );
 
  $meterID = addMeter($meterInfo);
  return $meterID;
}

function electricHandler($content){
  $data = array("Year"=>null,
		"Month"=>null,
		"Day"=>null,
		"Hour"=>0,
		"Minute" => 0,
		"Second" => 0,
		"MeasuredValue" => null,
		"BTUConversion" => null,
		"MeterID"=> null,
		"Unit" => "Kwh",
		"FuelType"=>"electricity"
		);
  $meter = array(
		 "MeterDescr"=>"",
		 "MeterNum"=>"",
		 "BuildingName" => "",
		 "MeterType" => "",
		 "MeterManufName" =>"Siemens",
		 "Supplier" => "Xcel",
		 "MeterModel" =>"",
		 "FuelType" =>"electricity",
		 "SiemensPt" =>""
		 );
  $meters = array(); 
  foreach ($content as $line){
    //var_dump($line);

    if (preg_match("/^(\r?\n?)+$/", $line) ||
	strstr($line, "Report") ||
	strstr($line, "Interval") || strstr($line, "Range") || strstr($line, "Key")) {
      var_dump($line);
      echo "<br/>";
      continue;
    }
   
  
    // get the Siemen point and description.
    $field = explode("\t",$line);
    //var_dump($field);
    //echo "<br/>";
    if (strstr($line,"Point") && strstr($line, "ELEC")){
      $meter["SiemensPt"] =  str_replace(":","",$field[0]);
      $meter["MeterDescr"] = $field[1];
      $meter["BuildingName"] = $field[2];
      
      $id = meterHandler($meter);
      echo "return meter id {$id}!<br/>";
      $meters[str_replace(":","",$field[0])] = $id; 
      echo str_replace(":","",$field[0])."=>".$id."<br/>";
    }else if (strstr($line, "<>Date")){
      $header = $field;
      //var_dump($header);
      //echo "Hello world<br/>";
    } else{
      if ($header){
	$date = explode("/",$field[0]);
	
	$data["Year"] = $date[2];
	$data["Month"] = $date[0];
	$data["Day"] = $date[1];
	$time = explode(":",$field[1]);
	$data["Hour"] = $time[0];
	$data["Minute"] = $time[1];
	$data["Second"] = $time[2];
	
	for ($i=2;$i < sizeof($field);$i++){
 
	  if (!is_null($meters)){
	    //echo "hello the world"; 
	    echo "meter is ";
	    var_dump($meters);
	    echo "<br/>";
	    $data["MeterID"] = $meters[$header[$i]];
	    if (is_numeric($field[$i])){
	      $data["MeasuredValue"] = $field[$i];
	      $data["BTUConversion"] = (double)$field[$i]*3412;
	      addEnergy($data);
	      //var_dump($data);
	      echo "<br/>";
	    }
	  }
	}
      }
    }
  }
}

echo "hello";
$filename = "CAMPUS ELECTRIC.txt";
$f = fopen($filename,"r") or die("Can't open file");
$data =(fread($f, filesize($filename)));
//echo $data;
$content = explode("\r",$data);
//var_dump($content);
electricHandler($content);
fclose($f);

  

?>