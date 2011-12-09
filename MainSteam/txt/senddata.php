<?php 
     function getDataFromDB($tblName,$colName,$colVal,$colQuery){
       /*
	 Retrieve the column colQuery in the table tblName where the value of colName is colVal
       */
       $query = "SELECT {$colQuery} FROM {$tblName} WHERE {$colName}='{$colVal}'";
       $res = mysql_query($query);
       if ($row = mysql_fetch_array($res)){
	 return $row[$colQuery];
       }
       else {
	 return "";
       }
     }
	


function updateDataInDB($tblName,$colName,$colVal,$toSet,$newValue){
  $query = "UPDATE {$tblName} SET {$toSet} = '{$newValue}' WHERE {$colName}='{$colVal}'";
  mysql_query($query);
}
	
function addNewBuilding($data){
  /*
    Check whether a given building exists. If so, return BuildingID. If not, create a new building record and return BuildingID.
  */
		
  $buildingName = $data["BuildingName"];
  $country = $data["Country"];
  $state = $data["State"];
  $city = $data["City"];
  $zip = $data["ZIP"];
  $year = $data["YearBuilt"];
  $streetAddress = $data["StreetAddress"];
  $buildingID = getDataFromDB("building","BuildingName",$buildingName,"BuildingID");
  if ($buildingID!="")
    return $buildingID;
  $query = "SELECT cityID FROM City WHERE CityName='{$city}' AND State='{$state}' AND Country='{$country}'";
  $res = mysql_query($query);
  if ($row = mysql_fetch_array($res)){
	 $cityID =  $row["cityID"];
  }
  else {
    $query = "INSERT INTO City (CityName,State,Country) VALUES('{$city}','{$state}','{$country}')";
    mysql_query($query);
    $cityID = mysql_insert_id();
    
  }
  
  $query = "SELECT AddressID FROM Address WHERE StreetAddress='{$streetAddress}' AND CityID={$cityID} AND ZipCode='{$zip}'";
  $res = mysql_query($query);
  if ($row = mysql_fetch_array($res)){
    $addressID =  $row["AddressID"];
  }
  else {
    $query = "INSERT INTO Address (StreetAddress,CityID,ZipCode) VALUES('{$streetAddress}',{$cityID},'{$zip}')";
    mysql_query($query);
    $addressID = mysql_insert_id();
    
  }
  
  $query = "INSERT INTO building (BuildingName, BuildingAddressID, YearBuilt) VALUES('{$buildingName}',{$addressID},{$year})";
  mysql_query($query);
  return mysql_insert_id();
}
function addBuildingHistory($data){
  $buildingName = $data["BuildingName"];
  $year = $data["YearChanged"];
  $sf = $data["BuildingSF"];
  $buildingID = getDataFromDB("building","BuildingName",$buildingName,"BuildingID");
  if ($buildingID == "")
    return "";
  $query = "SELECT HistoryID FROM BuildingHistory WHERE BuildingID={$buildingID} AND SquareFeet= {$sf} AND YearChanged = {$year}";
  $res = mysql_query($query);
  if ($row = mysql_fetch_array($res)){
	 $historyID =  $row["HistoryID"];
  }
  else {
    $query = "INSERT INTO BuildingHistory (BuildingID,SquareFeet,YearChanged) VALUES ({$buildingID},{$sf},{$year})";
    mysql_query($query);
    $historyID = mysql_insert_id();
    $query = "UPDATE building SET LatestHistory = {$historyID} WHERE BuildingID = {$buildingID}";
    mysql_query($query);
    
  } 
  return $historyID;
}
function addBuildingType($data){
  $buildingName = $data["BuildingName"];
  $type = $data["Type"];
  $buildingID = getDataFromDB("building","BuildingName",$buildingName,"BuildingID");
  if ($buildingID == "")
    return "";
  $typeID = getDataFromDB("BuildingType","Type",$type,"BuildingTypeID");
  if ($typeID == ""){
    $query = "INSERT INTO BuildingType (Type) VALUES('{$type}')";
    mysql_query($query);
    $typeID= mysql_insert_id();
  }
  $query = "SELECT HistoryID FROM BuildingHistory WHERE BuildingID={$buildingID}";
  $res = mysql_query($query);
  while ($row = mysql_fetch_array($res)){
	 $historyID =  $row["HistoryID"];
	 $query = "INSERT INTO History_BuildingType (HistoryID,BuildingTypeID) VALUES ({$historyID},{$typeID})";
	 mysql_query($query);
  }
  
}
function updateYearBought(){
  $query = "SELECT BuildingID FROM building";
  $res = mysql_query($query);
  while ($row = mysql_fetch_array($res)){
	 $buildingID =  $row["BuildingID"];
	 $query = "SELECT YearBuilt FROM building WHERE BuildingID={$buildingID}";
	 $yearData = mysql_query($query);
	 if ($current = mysql_fetch_array($yearData)){
	   $year =  $current["YearBuilt"];
	   $query = "UPDATE building SET YearBought={$year} WHERE BuildingID={$buildingID}";
	   mysql_query($query);     
	 }else{continue;}
	 
  }
  

}

function formBuildingTables(){
  $filename = "buildingdata.txt";
  dumpDataToDB($filename,"addNewBuilding");
  $filename = "buildingHistory.txt";
  dumpDataToDB($filename,"addBuildingHistory");
  $filename = "building_buildingType.txt";
  dumpDataToDB($filename,"addBuildingType");
  updateYearBought();
}

  

function dumpDataToDB($filename,$functionName,$separator="\r"){
  $f = fopen($filename,"r") or die("Can't open file");
  $content = fread($f, filesize($filename));
  $row = split($separator,$content);
  fclose($f);
  $header = split("\t",$row[0]);
  echo sizeof($header);
  var_dump($header);
  for ($i=1;$i< sizeof($row);$i++){
    $temp = split("\t",$row[$i]);
    //var_dump( $temp);
    if (sizeof($header)!=sizeof($temp)){
      continue;
    }
    $newArray = array();
    for ($j = 0; $j < sizeof($header);$j++){
      $newArray[$header[$j]] = $temp[$j];
    }
    //var_dump($newArray);
    echo "<br/>".$functionName."<br/>";
    $functionName($newArray);
  }
}

function createDate($data){
  // create a date in database. If a date only exists in the database, then just return it as a string.
  

  $month = (int)$data["Month"];
  $day = (int)$data["Day"];
  $hour = (int)$data["Hour"];
  $minute = (int)$data["Minute"];
  $second = (int)$data["Second"];
  // if only two digits, add 19 or 20 before the year.
  // current year is 2011, so 11 is a breaking point.
  if ($data["Year"]!=null){
    $year = (int)$data["Year"];
    if ($year<100 and $year>11){
      $year = 1900 + $year;
    }else if ($year < 100){
      $year = 2000 + $year; 
    }
  }else{
    $year= 0;
  }

  if ($data["CalendarType"]){
    $calendarType = $data["CalenderType"];
  }else{
    if (isBreak($data)){
      $calendarType = "break";
    }else if (isHoliday($data)){
      $calendarType = "holiday";
    }else{
      $calendarType = "term";
      
    }
  }
  $calendarTypeID = getDataFromDB("CalendarType","Type",$calendarType,"CalendarTypeID");
  if ($calendarTypeID==""){
    $query = "INSERT INTO CalendarType (Type) VALUES ('{$calendarType}')";
    mysql_query($query);
    $calendarTypeID = mysql_insert_id();  
  }
  $date = date('Y-m-d H:i:s',mktime($hour,$minute,$second,$month,$day,$year));

  $dateStr = getDataFromDB("DateObj","Date",$date,"Date");
    echo "<br/>This is the date string: ".$dateStr."!<br/>";
  if ($dateStr!=""){
    return $dateStr;
  }else{
    if ($month>=7){
      $fiscalYear = $year+1;
    }else{
      $fiscalYear = $year;
    }
    $weekDay = date("D",mktime($hour,$minute,$second,$month,$day,$year));
    echo "<br/>This is the date: ".$date."!<br/>";
    echo "<br/>This is the weekday: ".$weekDay."!<br/>";
   
    $query = "INSERT INTO DateObj (Date, Weekday, CalendarTypeID, FiscalYear) VALUES ('{$date}','{$weekDay}',{$calendarTypeID},{$fiscalYear})";
    mysql_query($query);
    return $date;
  }
  
}


function isHoliday($date){
  // to do
  $year = $date["Year"];
  $month = $date["Month"];
  $day = $date["Day"];
  return true;
				  
}
function isBreak($date){
  // to do
  $month = $date["Month"];
  $day = $date["Day"];
 
  return true;
		

}

function addEnergy($data){
  /*
    $data should at least contain Year, Month, Day, Hour, Minute, Second, MeasuredValue, Unit, BTUConversion, Type, BuildingName, SupplierName
   */
  // add into EnergyData table.
  var_dump($data);
  $date = createDate($data); // get the date.
  echo "The date is ".$date."!<br/>";
  $measuredValue  = $data["MeasuredValue"];
  if ($measuredValue =="" || $measuredValue == null || (!is_numeric($measuredValue))){
    return;
  }
  $conversion = $data["BTUConversion"];
  $unit = $data["Unit"];
  $type = $data["Type"];
  $buildingName = $data["BuildingName"];
  $supplierName = $data["SupplierName"];
  $buildingID = getDataFromDB("building","BuildingName",$buildingName,"BuildingID");
  if ($buildingID ==""){
    echo "error in finding building ID <br/>";
    return;
  }
  echo $supplierName."<br/>";
  $supplierID = getDataFromDB("Supplier","SupplierName",$supplierName,"SupplierID");
  if ($supplierID ==""){
    $query = "INSERT INTO Supplier (SupplierName) VALUES('{$supplierName}')";
    mysql_query($query);
    $supplierID = mysql_insert_id();
    
  }
  echo "Type is ".$type."<br/>";
  $fuelTypeID = getDataFromDB("FuelType","Type",$type,"FuelTypeID");
  if ($fuelTypeID==""){
    $query = "INSERT INTO FuelType (Type) VALUES('{$type}')";
    mysql_query($query);
    $fuelTypeID = mysql_insert_id();
  }
  echo "Fuel Type ID is ".$fuelTypeID."<br/>";
  $fuelSourceID = getDataFromDB("FuelSource","FuelTypeID",$fuelTypeID,"FuelSourceID");
  echo "fule source ID should be ".$fuelSourceID."!<br/>";
  if ($fuelSourceID==""){
 
    $query = "INSERT INTO FuelSource (FuelTypeID) VALUES({$fuelTypeID})";
    mysql_query($query);
    $fuelSourceID = mysql_insert_id();
    
  }
  echo "Fuel Source ID is ".$fuelSourceID."<br/>";
  $meterID = getDataFromDB("MeterInfo","FuelSourceID",$fuelSourceID,"MeterID");
  if ($meterID==""){    
    $query = "INSERT INTO MeterInfo (BuildingID, MeterManufID, FuelSourceID) VALUES('{$buildingID}','{$supplierID}','{$fuelSourceID}')";
    mysql_query($query);
    $meterID = mysql_insert_id();
    
  }
  echo "Meter ID is ".$meterID."<br/>";
  $query = "SELECT EnergyDataID, BTUConversion FROM EnergyData WHERE Date='{$date}' AND MeterID = '{$meterID}' AND MeasuredValue='{$measuredValue}' AND Unit='{$unit}'";
  $res = mysql_query($query);
  if ($row = mysql_fetch_array($res)){
    if ($row["BTUConversion"]!=$conversion){
      $id = $row["EnergyDataID"];
      $query = "UPDATE EnergyData SET BTUConversion={$conversion} WHERE EnergyDataID={$id}";
      mysql_query($query);
    }
    return;

  }
  $query = "INSERT INTO EnergyData (Date, MeterID, MeasuredValue, Unit, BTUConversion) VALUES ('{$date}', '{$meterID}', '{$measuredValue}', '{$unit}','{$conversion}')";
  mysql_query($query);
  echo "Query is".$query."<br/>";
}
function energy(){
  DumpDataToDB("test.txt","addEnergy","\n");
}

function main(){
  /*
    PHP page to receive and save data into MySQL database.
    
  */
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
  
  energy();
  
  echo "successful";
  mysql_close($connection);
}

?>
