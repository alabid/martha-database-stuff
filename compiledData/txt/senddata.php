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
	
	/*
	PHP page to receive and save data into MySQL database.
	Return a streamID when receiving information from a new stream.
	Determine whether this is a new or previous user.
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

	
	
function addNewBuilding($data){
  /*
    Check whether a given building exists. If so, return BuildingID. If not, create a new building record and return BuildingID.
  */
		
  $buildingName = $data["BuildingName"];
  $country = $data["Country"];
  $state = $data["State"];
  $city = $data["City"];
  $zip = $data["Zip"];
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
    
  } 
  return $historyID;
}
function addBuildingType($data){
  $buildingName = $data["BuildingName"];
  $type = $data["Type"];
  $buildingID = getDataFromDB("building","BuildingName",$buildingName,"BuildingID");
  if ($buildingID == "")
    return "";
  
}

$f = fopen("buildingdata.txt","w");

$row = fgets($f);
$header = split("\t",$row);
$row = fgets($f);
while ($row){
  $temp = split("\t",$row);
  if (sizeof($header)!=sizeof($temp)){
    continue;
  }
  $newArray = array();
  for ($i = 0; $i < sizeof($header);$i++){
    $newArray[$header[$i]] = $temp[$i];
  }
  addNewBuilding($newArray);

}










	mysql_close($connection);
?>
