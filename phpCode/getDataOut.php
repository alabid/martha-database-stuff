<?php
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

function buildingIDtoBuilding($buildingID){
  if ($buildingID !==""){
    return getDataFromDB("building","BuildingID",$buildingID,"BuildingName");
  }else{
    return "";
  }
}
function meterToBuilding($meterID){
  if ($meterID !==""){
    return buildingIDtoBuilding(meterToBuildingID($meterID));
  }else{
    return "";
  }
}
function meterToBuildingID($meterID){
  if ($meterID!==""){
    return getDataFromDB("MeterInfo","MeterID",$meterID,"BuildingID");
  }else{
    return "";
  }
}
function meterToFuelTypeID($meterID){
   if ($meterID!==""){
    return getDataFromDB("MeterInfo","MeterID",$meterID,"FuelTypeID");
  }else{
    return "";
  }
}

function meterToFuelType($meterID){
  if ($meterID !==""){
    return fuelTypeIDToFuelType(meterToFuelTypeID($meterID));
  }else{
    return "";
  }
}
function fuelTypeIDToFuelType($fuelTypeID){
  if ($fuelTypeID!==""){
    return getDataFromDB("FuelType","FuelTypeID",$fuelTypeID,"Type");
  }else{
    return "";
  }
}

function selectDateRange($startDate, $endDate){
  

}
function energy(){}

?>