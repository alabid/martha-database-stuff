<?php
// include senddata.php in order to access the functions inside senddata.php
include_once("senddata.php");

//global connection to MySQL EnergyData database.

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

/*
  Form an array of necessary information and call addMeter function to add the meter.
  Return meterID.
*/
function meterHandler($row){
  $meterType = strcmp(strtolower($row["MeterType"]),"digital")==0 ? "Digital":(strcmp(strtolower($row["MeterType"]),"analog")==0 ? "Analog":"");

  $meterInfo = array(
		     "MeterDescr"=> $row["MeterDescr"],
		     "MeterNum"=>$row["MeterNum"],
		     "BuildingID" => findBuildingID($row),
		     "FuelTypeID" =>addFuelType($row),
		     "MeterType" => $meterType,
		     "MeterManufID" => addSupplier($row,true),
		     "MeterModel" =>$row["MeterModel"],
		     "SiemensPt" =>$row["SiemensPt"]
		     );
 
  $meterID = addMeter($meterInfo);
  addSupplier($row);
  return $meterID;
}

/*
  Form the metadata for electricity.
*/
function electricHandler($content){
  $metadata = array("Unit" => "kWh",
		    "FuelType"=>"electricity",
		    "MeterIdentifier"=>"ELEC",
		    "Conversion"=>3412,
		    "Supplier"=>"Xcel");
  siemensEnergyHandler($content,$metadata);
}
/*
  Form the metadata for campus water.
*/
function campusWaterHandler($content){
  $metadata = array("Unit"=>"gal",
		    "FuelType"=>"campus water",
		    "Conversion"=>null,
		    "MeterIdentifier"=>"H2O",
		    "Supplier"=>"Carleton College");
  siemensEnergyHandler($content,$metadata);
}
/*
  Form the metadata for campus steam
*/
function campusSteamHandler($content){
  $metadata = array("Unit"=>"Klb",
		    "FuelType"=>"steam",
		    "Conversion"=>1000,
		    "MeterIdentifier"=>"STEAM",
		    "Supplier"=>"Carleton College");
  siemensEnergyHandler($content,$metadata);
}
/*
  Generic energy handler.
  It is based on the Siemens data format.
*/

function errorMessage($errorType){
   echo "<script>alert('".$errorType.". Please check again.');window.href.location='index.php?option=format';</script>";
}

function siemensEnergyHandler($content,$metadata){
    $data = array("Year"=>null,
		  "Month"=>null,
		  "Day"=>null,
		  "Hour"=>0,
		  "Minute" => 0,
		  "Second" => 0,
		  "Duration" =>"Daily",
		  "MeasuredValue" => null,
		  "BTUConversion" => null,
		  "MeterID"=> null,
		  "Unit" => $metadata["Unit"],
		  "FuelType"=>$metadata["FuelType"]
		);
  $meter = array(
		 "MeterDescr"=>"",
		 "MeterNum"=>"",
		 "BuildingName" => "",
		 "MeterType" => "",
		 "MeterManufName" =>"Siemens",
		 "Supplier" => $metadata["Supplier"],
		 "MeterModel" =>"",
		 "FuelType" =>$metadata["FuelType"],
		 "SiemensPt" =>""
		 );
  $meters = array(); 
  foreach ($content as $line){
    //var_dump($line);

    /*if (preg_match("/^(\r?\n?)+$/", $line) ||
	strstr($line, "Report") ||
	strstr($line, "Interval") || strstr($line, "Range") || strstr($line, "Key")) {
      var_dump($line);
      echo "<br/>";
      continue;
      }*/
   
  
    // get the Siemens point and description.
    $field = explode("\t",$line);
    //var_dump($field);
    //echo "<br/>";
    if (strstr($line,"Point") && strstr($line, $metadata["MeterIdentifier"])){
      $meter["SiemensPt"] =  str_replace(":","",$field[0]);
      $meter["MeterDescr"] = $field[1];
      $meter["BuildingName"] = $field[2] ? $field[2]: errorMessage("Unknown building");
      
      $id = meterHandler($meter);
      //echo "return meter id {$id}!<br/>";
      $meters[str_replace(":","",$field[0])] = $id; 
      //echo str_replace(":","",$field[0])."=>".$id."<br/>";
    }else if (strstr($line, "<>Date")){
      $header = $field;
      //var_dump($header);
  
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
	  
	    //echo "meter is ";
	    //var_dump($meters);
	    //echo "<br/>";
	    $data["MeterID"] = $meters[$header[$i]];
	    if (is_numeric($field[$i])){
	      $data["MeasuredValue"] = $field[$i];
	      if ($metadata["Conversion"]==null){
		$data["BTUConversion"]=null;
	      }else{
	       
		$data["BTUConversion"] = ((double)$field[$i])*((double)$metadata["Conversion"]);
	      }
	      addEnergy($data);
	      //var_dump($data);
	      echo "successfully update the database!";
	    }
	  }else{
	    errorMessage("Unknown meter");
	  }
	}
      }
    }
  }

}
/*
  Read a file and call the appropriate function to handle the data.
*/
function dumpData($filename,$functionName){
  $f = fopen($filename,"r") or die("Can't open file");
  $data =(fread($f, filesize($filename)));
  //echo $data;
  $content = explode("\n",$data);
  //var_dump($content);
  $functionName($content);
  fclose($f);
}
/*specify the filename and the corresponding energy handler*/
function addElectricity(){
  $filename = "CAMPUS ELECTRIC.txt";
  dumpData($filename,"electricHandler");
}
/*specify the filename and the corresponding energy handler*/
function addCampusWater(){
  $filename = "CAMPUS WATER.txt";
  dumpData($filename,"campusWaterHandler");
} 
/*specify the filename and the corresponding energy handler*/
function addCampusSteam(){
  $filename = "CAMPUS STEAM.txt";
  dumpData($filename,"campusSteamHandler");
}
function addWindTurbine(){
  $filename = "2011HourlyWindTurbineData.txt";
  dumpData($filename,"hourlyWindTurbine");
} 
function addkwDemand(){
  $filename = "2010_1.txt";
  dumpData($filename, "kwDemand");
  //$filename = "2007.txt";
  //dumpData($filename, "kwDemand");
}
function addkwWeekly(){
  $filename = "2010.txt";
  dumpData($filename, "kwWeekly");
  $filename = "2009.txt";
  dumpData($filename, "kwWeekly");
  
}
function hourlyWindTurbine($content){
  $metadata = array("Unit"=>"kWh",
		    "FuelType"=>"wind",
		    "Conversion"=>3412,
		    "Duration" => "Hourly",
		    "Supplier"=>"Carleton College",
		    "MeterManufName" => "Windman",
		    "MeterDescr" =>"Wind Turbine #1");
  
  $meterType = strcmp(strtolower($metadata["MeterType"]),"digital")==0 ? "Digital":(strcmp(strtolower($metadata["MeterType"]),"analog")==0 ? "Analog":"");

  $meterInfo = array(
		     "MeterDescr"=> $metadata["MeterDescr"],
		     "MeterNum"=>$metadata["MeterNum"],
		     "BuildingID" => findBuildingID(array("BuildingName"=>"Wind Turbine 1")),
		     "FuelTypeID" =>addFuelType($metadata),
		     "MeterType" => $meterType,
		     "MeterManufID" => addSupplier($metadata,true),
		     "MeterModel" =>$metadata["MeterModel"]
		   
		     );
 
  $meterID = addMeter($meterInfo);
  addSupplier($metadata);
 
  $data = array("Year"=>null,
		  "Month"=>null,
		  "Day"=>null,
		  "Hour"=>0,
		  "Minute" => 0,
		  "Second" => 0,
		  "Duration" =>$metadata["Duration"],
		  "MeasuredValue" => null,
		  "BTUConversion" => null,
		  "MeterID"=> $meterID,
		  "Unit" => $metadata["Unit"],
		  "FuelType"=>$metadata["FuelType"]
		);
  foreach ($content as $line){
    $field = explode("\t",$line);
    $datetime = explode(" ",$field[0]);
    $date = explode("/",$datetime[0]);
    
    $data["Month"] = $date[0];
    $data["Day"] = $date[1];
    $data["Year"] = $date[2];	
    $time = explode(":",$datetime[1]);
    $data["Hour"] = $time[0];
    $data["Minute"] = $time[1];
    $data["MeasuredValue"] = $field[1];
    $data["BTUConversion"] = ((double)$field[1])*((double)$metadata["Conversion"]);
    //var_dump($data);
    addEnergy($data);
  }
}
function kwWeekly($content){
  $metadata = array("Unit"=>"kWh",
		    "FuelType"=>"electricity consumption",
		    "Conversion"=>3412,
		    "Duration" => "Daily",
		    "Supplier"=>"Carleton College",
		    "MeterManufName" => "Carleton College",
		    "MeterDescr" =>"Campus Electricity Weekly Usage EMS Readings");
  
  /*$meterType = strcmp(strtolower($metadata["MeterType"]),"digital")==0 ? "Digital":(strcmp(strtolower($metadata["MeterType"]),"analog")==0 ? "Analog":"");
 
  $meterInfo = array(
		     "MeterDescr"=> $metadata["MeterDescr"],
		     "MeterNum"=>$metadata["MeterNum"],
		     "BuildingID" => findBuildingID(array("BuildingName"=>"Campus")),
		     "FuelTypeID" =>addFuelType($metadata),
		     "MeterType" => $meterType,
		     "MeterManufID" => addSupplier($metadata,true),
		     "MeterModel" =>$metadata["MeterModel"]
		   
		     );
 
  $meterID = addMeter($meterInfo);
  
  */
  $meterID = 11;
  $data = array("Year"=>null,
		  "Month"=>null,
		  "Day"=>null,
		  "Hour"=>0,
		  "Minute" => 0,
		  "Second" => 0,
		  "Duration" =>$metadata["Duration"],
		  "MeasuredValue" => null,
		  "BTUConversion" => null,
		  "MeterID"=> $meterID,
		  "Unit" => $metadata["Unit"],
		  "FuelType"=>$metadata["FuelType"]
		);
  foreach ($content as $line){
    if (strcmp($line,"\n")==0){
      continue;
    }
    $field = explode("\t",$line);
    $date = explode("-",$field[0]);
    
    $data["Month"] = $date[1];
    $data["Day"] = $date[2];
    $data["Year"] = $date[0];	
    $time = explode(":",$field[1]);
    $data["Hour"] = $time[0];
    $data["Minute"] = $time[1];
    $data["Second"] = $time[2];
    $data["MeasuredValue"] = $field[2];
    $data["BTUConversion"] = ((double)$field[2])*((double)$metadata["Conversion"]);
    // var_dump($data);
    addEnergy($data);
  }

}
function kwDemand($content){
  $metadata = array("Unit"=>"kWh",
		    "FuelType"=>"electricity demand",
		    "Conversion"=>3412,
		    "Duration" => "5mins",
		    "Supplier"=>"Carleton College",
		    "MeterManufName" => "Carleton College",
		    "MeterDescr" =>"Campus Electricity Demand EMS Readings");
  
  $meterType = strcmp(strtolower($metadata["MeterType"]),"digital")==0 ? "Digital":(strcmp(strtolower($metadata["MeterType"]),"analog")==0 ? "Analog":"");
 
  $meterInfo = array(
		     "MeterDescr"=> $metadata["MeterDescr"],
		     "MeterNum"=>$metadata["MeterNum"],
		     "BuildingID" => findBuildingID(array("BuildingName"=>"Campus")),
		     "FuelTypeID" =>addFuelType($metadata),
		     "MeterType" => $meterType,
		     "MeterManufID" => addSupplier($metadata,true),
		     "MeterModel" =>$metadata["MeterModel"]
		   
		     );
 
  $meterID = addMeter($meterInfo);
  addSupplier($metadata);
 
  $data = array("Year"=>null,
		  "Month"=>null,
		  "Day"=>null,
		  "Hour"=>0,
		  "Minute" => 0,
		  "Second" => 0,
		  "Duration" =>$metadata["Duration"],
		  "MeasuredValue" => null,
		  "BTUConversion" => null,
		  "MeterID"=> $meterID,
		  "Unit" => $metadata["Unit"],
		  "FuelType"=>$metadata["FuelType"]
		);
  foreach ($content as $line){
    if (strcmp($line,"\n")==0){
      continue;
    }
    $field = explode("\t",$line);
    $date = explode("-",$field[0]);
    
    $data["Month"] = $date[1];
    $data["Day"] = $date[2];
    $data["Year"] = $date[0];	
    $time = explode(":",$field[1]);
    $data["Hour"] = $time[0];
    $data["Minute"] = $time[1];
    $data["Second"] = $time[2];
    $data["MeasuredValue"] = $field[2];
    $data["BTUConversion"] = ((double)$field[2])*((double)$metadata["Conversion"]);
    //var_dump($data);
    addEnergy($data);
  }
}

mysql_close();

?>