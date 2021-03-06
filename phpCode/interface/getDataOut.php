<?php
/*
 $tblName -- the name of the table that contains the desired data.
 $colName -- the name of the column (attribute) that is used to select data (in WHERE clause)
 $colVal  -- the value of the attribute above (in WHERE clause)
 $colQuery -- the desired field/column (attribute)
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

function genericQuery($select, $from, $where, $order,$metadata){
  $query = "SELECT DISTINCT ";
  
  foreach ($select as $column){
    $query = $query."".$column.",";
  }
  $query = substr($query, 0 ,strlen($query)-1)." FROM ";
  foreach ($from as $table){
    $query = $query."".$table." NATURAL JOIN ";
  }
  $query  = substr($query, 0, strrpos($query,"NATURAL JOIN"));
  if (sizeof($where)!=0){
    $query = $query." WHERE ";
  
    foreach (array_keys($where) as $condition){
      $query = $query."".$condition."'{$where[$condition]}' AND ";
    }
    $query  = substr($query, 0, strrpos($query,"AND"));
  }
  $query = $query." ORDER BY '{$order}'";
  $res = mysql_query($query);
  //echo "the query is ".$query;
  $data = array();
  while ($row = mysql_fetch_array($res)){
    $temp = array();
    foreach ($select as $column){
      $temp[$column]  = $row[$column];
    }
    foreach (array_keys($metadata) as $meta){
      $temp[$meta] = $metadata[$meta];
    }
    array_push($data,$temp);
  
  }
  //var_dump($data);
  //echo "<br/>";
  return $data;
}
function test(){
  $from=array("EnergyData","DateObj");
  $select= array("Date","MeasuredValue","Unit");
  $year1= 2010;
  $year2= 2011;
  $where = array("Date>=" => $year1,
		 "Date<="=> $year2,
	
		 "FiscalYear="=>$year2
		 );
  $order= "Date";
  $metadata= array("BuildingName"=>"Burton Hall");
  writeIntoCSV(genericQuery($select,$from,$where,$order, $metadata));
}
function getConditions($attr,$op,$val){
  if (sizeof($attr)==sizeof($op) && sizeof($op)==sizeof($val)){
    $result = array();
    for ($i = 0;$i< sizeof($attr);$i++){
      $result[$attr." ".$op] = $val;
    }
    return $result;
  
  }
  return null;
}

function selectEnergy($columns,$constraints){
  
  $buildingName = meterToBuilding($meterID);
  $fuelType = meterToFuelType($meterID);
  $select = $columns; 
    //array("Date","FiscalYear","BuildingName","FuelType","MeasuredValue","Unit","BTUConversion");
  $from = array("EnergyData","DateObj","FuelType","MeterInfo","building");
  $where = $constraints;
  $order = "Date, MeterID";
  $metadata = array();
  writeIntoCSV(genericQuery($select,$from,$where,$order,$metadata));

  
}


function writeIntoCSV($data){
  if (sizeof($data)<1){
    return;
  }
 
  header("Content-Type:text/csv");
  header("Content-Disposition:attachment;filename=energyData.csv");
  $fp = fopen("php://output","w");
  
  fputcsv($fp,array_keys($data[0]));
  
  for ($i=0;$i< sizeof($data); $i++){
    fputcsv($fp,$data[$i]);
    
  }
  fclose($fp);
  
}

function getConstraints(){
  $field = array();
  
  if ($_GET["building"]){
    if (strcmp($_GET["building"],"all")!==0){
      $field["BuildingName="] = $_GET["building"];
    }
  }
  if ($_GET["startDateYear"]){ 
    if (strcmp($_GET["startDateYear"],"null")!==0){
      if ($_GET["startDateMonth"] && (strcmp($_GET["startDateMonth"],"null")!==0)){
	if ($_GET["startDateDay"] && (strcmp($_GET["startDateDay"],"null")!==0)){
	  $field["Date>="] =  date('Y-m-d H:i:s',mktime(0,0,0,(int)$_GET["startDateMonth"],(int)$_GET["startDateDay"],(int)$_GET["startDateYear"]));
	}else{
	  $field["Date>="] =  date('Y-m-d H:i:s',mktime(0,0,0,(int)$_GET["startDateMonth"],1,(int)$_GET["startDateYear"]));
	}
      }else{
	$field["Date>="] =  date('Y-m-d H:i:s',mktime(0,0,0,1,1,(int)$_GET["startDateYear"]));
      }
    }
  }  

   if ($_GET["endDateYear"]){ 
    if (strcmp($_GET["endDateYear"],"null")!==0){
      if ($_GET["endDateMonth"] && (strcmp($_GET["endDateMonth"],"null")!==0)){
	if ($_GET["endDateDay"] && (strcmp($_GET["endDateDay"],"null")!==0)){
	  $field["Date<"] =  date('Y-m-d H:i:s',mktime(0,0,0,(int)$_GET["endDateMonth"],(int)$_GET["endDateDay"]+1,(int)$_GET["endDateYear"]));
	}else{
	  $field["Date<"] =  date('Y-m-d H:i:s',mktime(0,0,0,(int)$_GET["endDateMonth"]+1,1,(int)$_GET["endDateYear"]));
	}
      }else{
	$field["Date<"] =  date('Y-m-d H:i:s',mktime(0,0,0,1,1,(int)$_GET["endDateYear"])+1);
      }
    }
   }  
  if ($_GET["fuelType"]){
    if (strcmp($_GET["fuelType"],"all")!==0){
      $field["FuelType="] = $_GET["fuelType"];
    }
  }
  if ($_GET["fiscalYear"]){
    $field["FiscalYear="] = $_GET["fiscalYear"];
  }
   if ($_GET["weekday"]){
    $field["Weekday="] = $_GET["weekday"];
   }
  //var_dump($field);
  return $field;
}
function getColumns(){
  $field = array();
  foreach (array_keys($_GET) as $column){
    if (strcmp($_GET[$column],"yes")==0){
      array_push($field,$column);
    }
  }
  return $field;
}

selectEnergy(getColumns(),getConstraints());
//test();
/*
$building = $_GET["building"];

$query = "SELECT * FROM building WHERE buildingNAME = '{$building}'";
$res = mysql_query($query);
if ($row = mysql_fetch_array($res)){
  $tbl = mysql_query("SHOW COLUMNS FROM building");
  
  while ($rowTbl = mysql_fetch_assoc($tbl)){
    echo $row[$rowTbl['Field']]."\t";
    
  }
}
*/
?>
