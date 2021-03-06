<?php 
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
  

*/
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

  
/*
  Read data from a file and dump it into MySQL database by the specified function.
  Default line separator is \r since on MAC, when saving Excel to txt, \r is used.
  Can pass a separator to the function.

*/
function dumpDataToDB($filename,$functionName,$separator="\r"){
  
  $f = fopen($filename,"r") or die("Can't open file");
  $content = fread($f, filesize($filename));
  $row = split($separator,$content);
  fclose($f);
  $header = explode("\t",$row[0]);
  echo sizeof($header);
  var_dump($header);
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
    var_dump($newArray);
    echo "<br/>".$functionName."<br/>";
    $functionName($newArray);
  }
}

/*
  create a date in database. If a date only exists in the database, then just return it.
*/
function createDate($data){
  

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

  // figure out the calendar type.
  // need to be changed.
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
  if ($date =="" || $date==null){
    echo "Error in date.<br/>";
    return;
  }
  $measuredValue  = $data["MeasuredValue"];
  if ($measuredValue =="" || $measuredValue == null || (!is_numeric($measuredValue))){
   
    return;
  }
  $conversion = $data["BTUConversion"];
  $unit = $data["Unit"];
  $type = $data["Type"];
  $buildingName = $data["BuildingName"];
  $buildingID = getDataFromDB("building","BuildingName",$buildingName,"BuildingID");
  if ($buildingID ==""){
    echo "Error in finding building ID <br/>";
    return;
  }
  $meterID = addMeter($data);
  if ($meterID=="" || $meterID==null){
    echo "error in finding meter ID<br/>.";
    return;
  }
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
  
  $buildingID = $data["BuildingID"];
  if ($buildingID=="" || $buildingID ==null){
    $buildingName = $data["BuildingName"];
    $buildingID = getDataFromDB("building","BuildingName",$buildingName,"BuildingID");
    if ($buildingID ==""){
      echo "<br><br>Error in finding building!</br></br><br/>";
      return;
    }
  }

  echo "in addMeter, building ID :".$buildingID."<br/>";
  $meterManufID = ($data["MeterManufID"]!=null &&  $data["MeterManufID"]!="") ? $data["MeterManufID"] : (($data["SupplierID"]!=null && $data["SupplierID"]!="") ? $data["SupplierID"]: "");
  if ($meterManufID=="" || $meterManufID==null){
    //get meter supplier's name.
    $supplier = ($data["MeterManufName"]!="" && ($data["MeterManufName"]!=null ) ?  
		 $data["MeterManufName"]: (($data["Supplier"]!="" && $data["Supplier"]!=null) ? 
					   $data["Supplier"] : (($data["SupplierName"]!="" && $data["SupplierName"]!=null) ? 
								$data["SupplierName"] : "")));
    echo "Supplier name is ".$supplier."<br/>";
    $data["Supplier"]=$supplier;
    $meterManufID = addSupplier($data);
    if ($meterManufID =="" || $meterManufID == null){
      return "";
    }
  }
  echo "in addMeter, manufacture ID :".$meterManufID."<br/>";
  $fuelTypeID = addFuelType($data);
  if ($fuelTypeID =="" || $fuelTypeID ==null){
    return "";
  }
  echo "in addMeter, fuel type ID :".$fuelTypeID."<br/>";
  $meterNum = $data["MeterNum"];
  $query = "SELECT MeterID, MeterNum FROM MeterInfo WHERE BuildingID='{$buildingID}' AND MeterManufID='{$meterManufID}' AND FuelTypeID ='{$fuelTypeID}'";
  echo "<br/>see the query: ".$query."<br/>";
  $res = mysql_query($query);
  if ($row = mysql_fetch_array($res)){
    if (strcmp($meterNum,$row["MeterNum"])==0){
      return $row["MeterID"];
    }
  }
  
  $meterDescr = $data["MeterDescr"];
  echo "Meter Type is ".$data["MeterType"]."!<br/>";
  
  $meterType = strcmp(strtolower($data["MeterType"]),"digital")==0 ? "Digital":(strcmp(strtolower($data["MeterType"]),"analog")==0 ? "Analog":"");
  echo "Meter Type is ".$meterType."<br/>";
  $meterModel = $data["MeterModel"];

  // get siemen point information.
  
  $siemensPt = ($data["SiemensPt"]!=null && $data["SiemensPt"]!="") ? 
    $data["SiemensPt"]: (($data["Point"]!=null && $data["Point"]!="") ? 
			$data["Point"]:"");
  $query = "INSERT INTO MeterInfo (BuildingID, MeterManufID,FuelTypeID,";
  $value = "VALUES ('{$buildingID}','{$meterManufID}','{$fuelTypeID}',";

  // construct a query.
  if ($meterDescr!="" && $meterDescr !=null){
    $query = $query."MeterDescr,";
    $value = $value."'{$meterDescr}',";
  }
  if ($meterNum!="" && $meterNum !=null){
    $query = $query."MeterNum,";
    $value = $value."'{$meterNum}',";
  }
  if ($meterType!="" && $meterType !=null){
    $query = $query."MeterType,";
    $value = $value."'{$meterType}',";
  }
  if ($meterModel!="" && $meterModel !=null){
    $query = $query."MeterModel,";
    $value = $value."'{$meterModel}',";
  }
  if ($siemensPt!="" && $siemensPt !=null){
    $query = $query."SiemensPt,";
    $value = $value."'{$siemensPt}',";
  }

  // remove the last comma.
  $value = substr($value, 0, strlen($value)-1).")";
  $query = substr($query, 0 ,strlen($query)-1).") ".$value;
  echo "<br/>meter query is ".$query."!<br>";
  mysql_query($query);
  return mysql_insert_id();  
  
}

/*
  Add a new fuel source into database.
  Fuel source table is removed from database.
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
  Add a new fuel type into database.
 */  
function addFuelType($data){
  $fuelTypeID =$data["FuelTypeID"];
  if ($fuelTypeID!="" && $fuelTypeID!=null && is_numeric($fuelTypeID)){
    return $fuelTypeID;
  }
  $type = ($data["FuelType"]!="" && $data["FuelType"]!=null) ? 
    $data["FuelType"]:(($data["Type"]!=null && $data["Type"]!="") ? 
		      $data["Type"] :(($data["type"]!="" && $data["type"]!=null) ? $data["type"] : ""));
  echo "The type of fuel is ".$type."!<br/>";
  if ($type=="" || $type ==null){
    return "";
  }
  $fuelTypeID = getDataFromDB("FuelType","Type",$type,"FuelTypeID");
  if ($fuelTypeID==""){
    $query = "INSERT INTO FuelType (Type) VALUES('{$type}')";
    echo "<br/>Fuel Type query: ".$query."!<br/>";
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
function addSupplier($data){
  
  $supplier = $data["Supplier"];
  $supplierDescr = $data["SupplierDescr"];
  $supplierID = getDataFromDB("Supplier","SupplierName",$supplier,"SupplierID");
  // check if it is there already.
  if ($supplierID !=""){
    $data["SupplierID"] = $supplierID;
    relateFuelTypeToSupplier($data);
    return $supplierID;
  }
  if (($supplierDescr==null || $supplierDescr=="")  && ($supplier==null || $supplier =="")){
    return "";
  }
 

  // construct a query string.
 
  $query = "INSERT INTO Supplier (";
  $value = "VALUES (";
  if ($supplier !="" && $supplier !=null){
    $query = $query." SupplierName,";
    $value = $value."'{$supplier}',";
  }
  if ($supplierDescr !="" && $supplierDescr !=null){
      $query = $query." SupplierDescr,";
      $value = $value."'{$supplierDescr},'";
  }
  
  $value = substr($value, 0, strlen($value)-1).")";
  $query = substr($query, 0 ,strlen($query)-1).") ".$value;
  echo "<br/>Supplier query :".$query."<br/>";
  mysql_query($query);
  $supplierID = mysql_insert_id(); 
  // add to relation table:
  $data["SupplierID"] = $supplierID;
  relateFuelTypeToSupplier($data);
  return $supplierID;
}

function relateFuelTypeToSupplier($data){
  $typeID = addFuelType($data);
  $supplierID = $data["SupplierID"];
  if ($typeID!=null && $typeID!="" && $supplierID !=null && $supplierID !=""){
    $query = "SELECT FROM FuelType_Supplier WHERE FuelTypeID='{$typeID}' AND SupplierID ='{$supplierID}'";
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
  if ($buildingID !=null && $buildingID !=""){
    return $buildingID;
  }
  $buildingName = $data["BuildingName"];
  if ($buildingName !=null && $buildingName !=""){
    return getDataFromDB("building","BuildingName",$buildingName,"BuildingID");
  }
  $buildingCode = $data["BuildingCode"];
  if ($buildingCode !=null && $buildingCode !=""){
    return getDataFromDB("building","BuildingCode",$buildingCode,"BuildingID");
  }

  $buildingAddressID = $data["BuildingAddressID"];
  
  if ($buildingAddressID ==null || $buildingAddressID ==""){
    $buildingAddressID = addAddress($data);
  }
  if ($buildingAddressID !=null && $buildingAddressID !=""){
    return getDataFromDB("building","BuildingAddressID",$buildingAddressID,"BuildingID");
  }
  return "";
}

// debug purpose.
function test(){
  DumpDataToDB("test2.txt","addMeter","\n");
}


function main(){
  /*
    PHP page to receive and save data into MySQL database.
    
  */

  //test();
  
  echo "successful";
  
}
//test();
//main()
mysql_close($connection);
?>
