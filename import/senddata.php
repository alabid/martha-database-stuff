<?php 

 /*
    PHP page to receive and save data into MySQL database.
    This php script is based on the schema we designed during winter break, 2011.
    It is also based on the txt file formats (columns) inside the same directory.
  */

// gloal connection to MySQL databse.
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
 $tblName -- the name of the table that contains the desired data.
 $colName -- the name of the column (attribute) that is used to select data (in WHERE clause)
 $colVal  -- the value of the attribute above (in WHERE clause)
 $colQuery -- the desired field/column (attribute)
 */
function getDataFromDB($tblName,$colName,$colVal,$colQuery){
  /*
    Retrieve the column colQuery in the table tblName where the value of colName is colVal
  */
  $query = "SELECT {$colQuery} FROM {$tblName} WHERE {$colName}='{$colVal}'";
  // echo "this query is ".$query."<br/>";
  $res = mysql_query($query);
  if ($row = mysql_fetch_array($res)){
    
    return $row[$colQuery];
  }
  else {
    
    return "";
  }
}
/*
  Simple update function, like getDataFromDB.
*/
function updateDataInDB($tblName,$colName,$colVal,$toSet,$newValue){
  $query = "UPDATE {$tblName} SET {$toSet} = '{$newValue}' WHERE {$colName}='{$colVal}'";
  mysql_query($query);
}

/*
  Add a new address into the database if the address does not exist yet.
  Returns the AddressID.
*/
function addAddress($data){
  $addressID = $data["AddressID"];
  if ($addressID !=null && $addressID !=""){
    return $addressID;
  }
  $cityID = $data["CityID"];
  if ($cityID==null || $cityID =="" ){
    $country = $data["Country"];
    $state = $data["State"];
    $city = $data["City"];
    if (!(is_string($country) && is_string($state) && is_string($city))){
      return;
    }
    $zip = (is_string($data["ZipCode"]) || is_numeric($data["ZipCode"])) ? $data["ZipCode"]:
      ((is_string($data["ZIP"]) || is_numeric($data["ZIP"])) ? $data["ZIP"]:"");
    $streetAddress = $data["StreetAddress"];
    $query = "SELECT cityID FROM City WHERE CityName='{$city}' AND State='{$state}' AND Country='{$country}'";
    $res = mysql_query($query);
    if ($row = mysql_fetch_array($res)){
      $cityID =  $row["cityID"];
    }
    else {
      $query = "INSERT INTO City (CityName,State,Country) VALUES('{$city}','{$state}','{$country}')";
      mysql_query($query);
      $cityID = mysql_insert_id();
      // if the city doesn't exist in the database, insert the city record into the database.
    }
  }
  
  $query = "SELECT AddressID FROM Address WHERE StreetAddress='{$streetAddress}' AND CityID={$cityID}";
  $res = mysql_query($query);
  if ($row = mysql_fetch_array($res)){
    $addressID =  $row["AddressID"];
  }
  else {
    if ($zip!="" && $zip!=null){
      $query = "INSERT INTO Address (StreetAddress,CityID,ZipCode) VALUES('{$streetAddress}','{$cityID}','{$zip}')";
    }else{
      $query = "INSERT INTO Address (StreetAddress,CityID) VALUES('{$streetAddress}','{$cityID}')";
    }
    mysql_query($query);
    $addressID = mysql_insert_id();
    
  }
  return $addressID;
}

/*
    Check whether a given building exists. If so, return BuildingID. If not, create a new building record and return BuildingID.
    1. $data["BuildingName"];
    2. $data["Country"];
    3. $data["State"];
    4. $data["City"];
    5. $data["ZIP"];
    6. $data["YearBuilt"];
    7. $data["StreetAddress"];
*/	
function addNewBuilding($data){
		
  $buildingName = $data["BuildingName"];
  $year = $data["YearBuilt"];
  $buildingID = getDataFromDB("building","BuildingName",$buildingName,"BuildingID");
  if ($buildingID!="")
    return $buildingID;
  $addressID = addAddress($data);
  $query = "INSERT INTO building (BuildingName, BuildingAddressID, YearBuilt) VALUES('{$buildingName}',{$addressID},{$year})";
  mysql_query($query);
  return mysql_insert_id();
}
/*
  add building history data into the databse from the file buildingHistory.txt
  
*/
function addBuildingHistory($data){
  $buildingName = $data["BuildingName"];
  $year = $data["YearChanged"];
  $sf = $data["BuildingSF"];
  $buildingID = getDataFromDB("building","BuildingName",$buildingName,"BuildingID");
  if ($buildingID == "")
    return "";
  $query = "SELECT HistoryID, FROM BuildingHistory WHERE BuildingID={$buildingID} AND SquareFeet= {$sf} AND YearChanged = {$year}";
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

/*
  Add building type data from building_buildingType.txt
*/
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

/*
  Temporary solution:
  Use the year built as the year bought.
 */
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
/*
  A combined function that calls all building-related functions and passes the correct parameters to each function.
*/
function formBuildingTables(){
  $filename = "buildingdata.txt";
  dumpDataToDB($filename,"addNewBuilding");
  $filename = "buildingHistory.txt";
  dumpDataToDB($filename,"addBuildingHistory");
  $filename = "building_buildingType.txt";
  dumpDataToDB($filename,"addBuildingType");
  updateYearBought();
}

  
/*
  Read data from a file and dump it into MySQL database by the specified function.
  Default line separator is \r since on MAC, when saving Excel to txt, \r is used.
  Can pass a separator to the function.

*/
function dumpDataToDB($filename,$functionName,$separator="\r"){
  
  $f = fopen($filename,"r") or die("Can't open file");
  $content = fread($f, filesize($filename));
  $row = explode($separator,$content);
  fclose($f);
  $header = explode("\t",$row[0]);
  echo sizeof($header);
  //var_dump($header);
  for ($i=1;$i< sizeof($row);$i++){
    $temp = explode("\t",$row[$i]);
    //var_dump( $temp);
    if (sizeof($header)!=sizeof($temp)){
      continue;
    }
    $newArray = array();
    for ($j = 0; $j < sizeof($header);$j++){
      $newArray[$header[$j]] = $temp[$j];
    }
    //var_dump($newArray);
    //echo "<br/>".$functionName."<br/>";
    $functionName($newArray);
  }
}

/*
  create a date in database. If a date only exists in the database, then just return it.
  Before calling this function, make sure it has at least the following information:
  $data["Year"]
  $data["Month"]
  $data["Day"]

*/
function createDate($data){
  

  $month = (int)$data["Month"];
  $day = (int)$data["Day"];
  $hour = ($data["Hour"]!=null) ? (int)$data["Hour"] : 0;
  $minute = ($data["Minute"]!=null) ? (int)$data["Minute"] : 0;
  $second = ($data["Second"]!=null) ? (int)$data["Second"] : 0;

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

  // Figure out the calendar type.
  // need to be changed.
  if ($data["CalendarType"]){
    $calendarType = $data["CalenderType"];
  }else{
    if (true){
      $calendarType = "Historical Data";  // temporary solution.
    }else if (isBreak($data)){
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
  // echo "<br/>This is the date string: ".$dateStr."!<br/>";
  if ($dateStr!=""){
    return $dateStr;
  }else{
    if ($month>=7){
      $fiscalYear = $year+1;
    }else{
      $fiscalYear = $year;
    }
    $weekDay = date("D",mktime($hour,$minute,$second,$month,$day,$year));
    // echo "<br/>This is the date: ".$date."!<br/>";
    //echo "<br/>This is the weekday: ".$weekDay."!<br/>";
   
    $query = "INSERT INTO DateObj (Date, Weekday, CalendarTypeID, FiscalYear) VALUES ('{$date}','{$weekDay}',{$calendarTypeID},{$fiscalYear})";
    mysql_query($query);
    return $date;
  }
  
}

// If we want to determine whether a day is a holiday.
function isHoliday($date){
  // TODO: find a way to determine holiday.
  $year = $date["Year"];
  $month = $date["Month"];
  $day = $date["Day"];
  return true;
				  
}

function isBreak($date){
  // TODO: find a way to determine break.
  $month = $date["Month"];
  $day = $date["Day"];
 
  return true;
		

}
/*
  Before calling this function, make sure $data contains the following information.
  $data["MeasuredValue"]
  $data["Year"], $data["Month"], $data["Day"], $data["Hour"], $data["Minute"], $data["Second"]
  $data["Unit"]
  $data["BTUConversion"]
  Building information
  Meter information
  
 */
function addEnergy($data){
  /*
    $data should at least contain Year, Month, Day, Hour, Minute, Second, MeasuredValue, Unit, BTUConversion, Type, BuildingName, SupplierName
   */
  // add into EnergyData table.
  //var_dump($data);
  $date = createDate($data); // get the date.
  //echo "The date is ".$date."!<br/>";
  if ($date ==="" || $date===null){
    echo "Error in date.<br/>";
    return;
  }
  $measuredValue  = $data["MeasuredValue"];
  if ($measuredValue ==="" || $measuredValue === null || (!is_numeric($measuredValue))){
   
    return;
  }
  $conversion = $data["BTUConversion"];
  $unit = $data["Unit"];
  $duration = $data["Duration"];
  $duration = (strcmp(strtolower($duration),"hourly")==0) ? "Hourly" : 
    ((strcmp(strtolower($duration),"daily")==0) ? "Daily": 
     ((strcmp(strtolower($duration),"monthly")==0) ? "Monthly":
      ((strcmp(strtolower($duration),"annual")==0) ? "Annual" : 
       ((strcmp(strtolower($duration),"5 mins")==0 || strcmp(strtolower($duration),"5mins")==0) ? "5mins":
	((strcmp(strtolower($duration),"10 mins")==0 || strcmp(strtolower($duration),"10mins")==0) ? "10mins": null)))));
  /*forget to specify duration?
    Temporary solution: Daily.
   */
  if ($duration===null){
    $duration = "Daily";
  }

  $meterID = addMeter($data);
  if ($meterID==="" || $meterID===null){
    echo "Error in finding meter ID<br/>.";
    return;
  }
  $query = "SELECT EnergyDataID, BTUConversion FROM EnergyData WHERE Date='{$date}' AND Duration='{$duration}' AND MeterID = '{$meterID}' AND MeasuredValue='{$measuredValue}' AND Unit='{$unit}'";
  $res = mysql_query($query);
  if ($row = mysql_fetch_array($res)){
    
    // Update BTUconversion data if the record alreay exists.
    if ($row["BTUConversion"]!=$conversion && $conversion!==null && $conversion!==""){
      $id = $row["EnergyDataID"];
      $query = "UPDATE EnergyData SET BTUConversion={$conversion} WHERE EnergyDataID={$id}";
      mysql_query($query);
    }
    return;

  }

  // If the record does not exist, according to whether it has BTUconversion data, 
  // construct the appropriate query.
  
  if ($conversion!==null && $conversion!==""){
    $query = "INSERT INTO EnergyData (Date, Duration, MeterID, MeasuredValue, Unit, BTUConversion) VALUES ('{$date}', '{$duration}','{$meterID}', '{$measuredValue}', '{$unit}','{$conversion}')";
  }else{
    $query = "INSERT INTO EnergyData (Date, Duration, MeterID, MeasuredValue, Unit) VALUES ('{$date}','{$duration}', '{$meterID}', '{$measuredValue}', '{$unit}')";
  }
  mysql_query($query);
  //echo "Query is".$query."<br/>";
}

/*
  According to the data availability, this function constructs a query
  to insert a new meter record into MeterInfo table.
  If the meter is alreay in the database, (determined by BuildingID, MeterManufID, FuelSourceID and MeterNum),
  the meterID is returned without modifying the database.
  NOTE:BuildingID, MeterManufID and FuelSourceID are required to add a new meter. So,
  if this function fails to find these three attributes, it cannot insert a new record into the MeterInfo table.
  $data contains the following stuff before calling this function.
  1. $data["BuildingID"] or $data["BuildingName"]
  2. $data["MeterManufID"] or $data["SupplierID"] or $data["MeterManufName"] or $data["Supplier"] or $data["SupplierName"]
  3. $data["FuelType"] or $data["FuelTypeID"]
  1-3 are required. At least one entry for each group.
  4. $data["MeterDescr"]
  5. $data["MeterNum"]
  6. $data["MeterType"] -- "digital" or "analog"
  7. $data["MeterModel"]
  8. $data["SiemensPt"]
  4-8 are optional.
*/
function addMeter($data){
  // retrieve necessary data from the array of data in order to add the meter information to MySQL database.
  $meterID = $data["MeterID"];
  if ($meterID!=="" && $meterID!==null){
    return $meterID;
  }
  $buildingID = $data["BuildingID"];
  if ($buildingID==="" || $buildingID ===null){
    $buildingName = $data["BuildingName"];
    $buildingID = getDataFromDB("building","BuildingName",$buildingName,"BuildingID");
    if ($buildingID ==""){
      echo "<br><br>Error in finding building!</br></br><br/>";
      return "";
    }
  }

  //echo "in addMeter, building ID :".$buildingID."<br/>";
  $fuelTypeID = addFuelType($data);
  if ($fuelTypeID ==="" || $fuelTypeID ===null){
    return "";
  }
  //echo "in addMeter, fuel type ID :".$fuelTypeID."<br/>";
  
  $meterManufID = $data["MeterManufID"];
  if ($meterManufID==="" || $meterManufID===null){
    //get meter supplier's name.
    $supplier = ($data["MeterManufName"]!=="" && ($data["MeterManufName"]!==null ) ?  
		 $data["MeterManufName"]: (($data["Supplier"]!=="" && $data["Supplier"]!==null) ? 
					   $data["Supplier"] : (($data["SupplierName"]!=="" && $data["SupplierName"]!==null) ? 
								$data["SupplierName"] : "")));
    //echo "Supplier name is ".$supplier."<br/>";
    $data["MeterManufName"]=$supplier;
    $meterManufID = addSupplier($data,true);
    if ($meterManufID ==="" || $meterManufID === null){
      return "";
    }
  }
  //echo "in addMeter, manufacture ID :".$meterManufID."<br/>";


  $meterNum = $data["MeterNum"];
  $siemensPt = ($data["SiemensPt"]!==null && $data["SiemensPt"]!=="") ? 
    $data["SiemensPt"]: (($data["Point"]!==null && $data["Point"]!=="") ? 
			$data["Point"]:"");
  $query = "SELECT MeterID, MeterNum,SiemensPt FROM MeterInfo WHERE BuildingID='{$buildingID}' AND MeterManufID='{$meterManufID}' AND FuelTypeID ='{$fuelTypeID}'";
  //echo "<br/>see the query: ".$query."<br/>";
  $res = mysql_query($query);
  while ($row = mysql_fetch_array($res)){
    if (strcmp($meterNum,$row["MeterNum"])==0 && strcmp($siemensPt,$row["SiemensPt"])==0){
      return $row["MeterID"];
    }
  }
  
  $meterDescr = $data["MeterDescr"];
  // echo "Meter Type is ".$data["MeterType"]."!<br/>";
  
  $meterType = strcmp(strtolower($data["MeterType"]),"digital")==0 ? "Digital":(strcmp(strtolower($data["MeterType"]),"analog")==0 ? "Analog":"");
  //echo "Meter Type is ".$meterType."<br/>";
  $meterModel = $data["MeterModel"];

  // get siemens point information.
  
  
  $query = "INSERT INTO MeterInfo (BuildingID, MeterManufID,FuelTypeID,";
  $value = "VALUES ('{$buildingID}','{$meterManufID}','{$fuelTypeID}',";

  // construct a query based on what information is available.
  if ($meterDescr!=="" && $meterDescr !==null){
    $query = $query."MeterDescr,";
    $value = $value."'{$meterDescr}',";
  }
  if ($meterNum!=="" && $meterNum !==null){
    $query = $query."MeterNum,";
    $value = $value."'{$meterNum}',";
  }
  if ($meterType!=="" && $meterType !==null){
    $query = $query."MeterType,";
    $value = $value."'{$meterType}',";
  }
  if ($meterModel!=="" && $meterModel !==null){
    $query = $query."MeterModel,";
    $value = $value."'{$meterModel}',";
  }
  if ($siemensPt!=="" && $siemensPt !==null){
    $query = $query."SiemensPt,";
    $value = $value."'{$siemensPt}',";
  }

  // remove the last comma.
  $value = substr($value, 0, strlen($value)-1).")";
  $query = substr($query, 0 ,strlen($query)-1).") ".$value;
  //echo "<br/>meter query is ".$query."!<br>";
  mysql_query($query);
  return mysql_insert_id();  
  
}

/*
  Add a new fuel source into database.
  Fuel source table is removed from database.	
  We no longer need this function. This function is here in case 
  we make changes to the database schema in the future and this function 
  might be relevant again.
*/
/*
function addFuelSource($data){
  $fuelTypeID = addFuelType($data);
  if ($fuelTypeID == "" || $fuelTypeID ==null || (!is_numeric($fuelTypeID))){
    return "";
  }
  $fuelSourceID = getDataFromDB("FuelSource","FuelTypeID",$fuelTypeID,"FuelSourceID");
  echo "fuel source ID should be ".$fuelSourceID."!<br/>";
  if ($fuelSourceID==""){
    
    $query = "INSERT INTO FuelSource (FuelTypeID) VALUES({$fuelTypeID})";
    mysql_query($query);
    $fuelSourceID = mysql_insert_id();
  }
  return $fuelSourceID;
  }
*/


/*
  Adds a new fuel type into database and returns the fuel type ID.
  Notice if the fuel type already exists in the database, this function
  simply returns the ID.
 */  
function addFuelType($data){
  $fuelTypeID =$data["FuelTypeID"];
  if ($fuelTypeID!="" && $fuelTypeID!=null && is_numeric($fuelTypeID)){
    return $fuelTypeID;
  }
  $type = ($data["FuelType"]!=="" && $data["FuelType"]!==null) ? 
    $data["FuelType"]:(($data["Type"]!==null && $data["Type"]!="") ? 
		      $data["Type"] :(($data["type"]!="" && $data["type"]!==null) ? $data["type"] : ""));
  //echo "The type of fuel is ".$type."!<br/>";
  if ($type==="" || $type ===null){
    return "";
  }
  $fuelTypeID = getDataFromDB("FuelType","FuelType",$type,"FuelTypeID");
  if ($fuelTypeID===""){
    $query = "INSERT INTO FuelType (FuelType) VALUES('{$type}')";
    //echo "<br/>Fuel Type query: ".$query."!<br/>";
    mysql_query($query);
    $fuelTypeID = mysql_insert_id();
  }
  return $fuelTypeID;  
}

/*
  Find the supplier ID if the supplier is in the database.
  Otherwise, insert a record of this supplier into database.
  Depending on which data are available.

*/
function addSupplier($data,$isMeter=false){
  if ($isMeter){
    $supplier = $data["MeterManufName"];

  }else{
    $supplier = $data["Supplier"];
  }
  $supplierDescr = $data["SupplierDescr"];
  $supplierID = getDataFromDB("Supplier","SupplierName",$supplier,"SupplierID");
  // check if it is there already.
  if ($supplierID !=""){
    if (!$isMeter){
      $typeID = addFuelType($data);
      relateFuelTypeToSupplier($typeID,$supplierID);
    }
    return $supplierID;
  }
  if (($supplierDescr===null || $supplierDescr==="")  && ($supplier===null || $supplier ==="")){
    return "";
  }
 

  // construct a query string.
 
  $query = "INSERT INTO Supplier (";
  $value = "VALUES (";
  if ($supplier !=="" && $supplier !==null){
    $query = $query." SupplierName,";
    $value = $value."'{$supplier}',";
  }
  if ($supplierDescr !=="" && $supplierDescr !==null){
      $query = $query." SupplierDescr,";
      $value = $value."'{$supplierDescr},'";
  }
  
  $value = substr($value, 0, strlen($value)-1).")";
  $query = substr($query, 0 ,strlen($query)-1).") ".$value;
  //echo "<br/>Supplier query :".$query."<br/>";
  mysql_query($query);
  $supplierID = mysql_insert_id(); 
  // add to relation table:
  if (!$isMeter){
    $typeID = addFuelType($data);
    relateFuelTypeToSupplier($typeID,$supplierID);
  }
  return $supplierID;
}

/*
  Update the FuelType_Supplier Table.
  Relate each fuel supplier with fuel type.

*/
function relateFuelTypeToSupplier($typeID,$supplierID){
  if ($typeID!==null && $typeID!=="" && $supplierID !==null && $supplierID !==""){
    $query = "SELECT * FROM FuelType_Supplier WHERE FuelTypeID='{$typeID}' AND SupplierID ='{$supplierID}'";
    //echo "relate fuel type to supplier query: {$query}!<br/>";
    $res = mysql_query($query);
    if ($row = mysql_fetch_array($res)){
      return;
    }
    $query = "INSERT INTO FuelType_Supplier (FuelTypeID, SupplierID) VALUES ('{$typeID}','{$supplierID}')";
    mysql_query($query);
  } 
}

// Based on the information we have, get the building id.
function findBuildingID($data){
  
  $buildingID = $data["BuildingID"];
  if ($buildingID !==null && $buildingID !==""){
    return $buildingID;
  }
  $buildingName = $data["BuildingName"];
  if ($buildingName !==null && $buildingName !==""){
    
    
    return  getDataFromDB("building","BuildingName",$buildingName,"BuildingID");
   
  
  }
  $buildingCode = $data["BuildingCode"];
  if ($buildingCode !==null && $buildingCode !==""){
    return getDataFromDB("building","BuildingCode",$buildingCode,"BuildingID");
  }

  $buildingAddressID = $data["BuildingAddressID"];
  
  if ($buildingAddressID ===null || $buildingAddressID ===""){
    $buildingAddressID = addAddress($data);
  }
  if ($buildingAddressID !==null && $buildingAddressID !==""){
    return getDataFromDB("building","BuildingAddressID",$buildingAddressID,"BuildingID");
  }
  return "";
}



function main(){
 
  formBuildingTables();
  
  
}

//main();
mysql_close($connection);
?>
