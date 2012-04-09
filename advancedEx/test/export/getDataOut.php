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

/*
  Generic query to access one field in one table at one time.
  Too old, too simple and too inconvenient.
  Just keep here for now before we make sure deleting this one will not create new bugs.
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
  The following several functions are self-explanatory by their names.
  They convert something to something else.
  I don't think we need these functions, but we can keep them for now.
  To delete this one, please check doing so doesn't break any codes, especially some codes 
  dependent on this file.
*/
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


/*
  This function generates a generic query. It can query multiple tables and multiple fields
  by multiple conditions and order these data in the desired data. Then it can append any metadata
  into the array of data, which can be later written into a file. 
  
  NOTICE: Proper design/naming of the database schema is a must for this function.
  This function uses NATURAL JOIN when constructing SQL.
  Anything that is not compatiable with NATURAL JOIN will cause a problem here.
*/
function genericQuery($select, $from, $where, $group, $order,$metadata){
  $query = "SELECT DISTINCT ";
  
  foreach ($select as $column){
    $query = $query."".$column.",";
  }
  $query = substr($query, 0 ,strlen($query)-1)." FROM ";
  // select all related tables.
  foreach ($from as $table){
    $query = $query."".$table." NATURAL JOIN ";
  }
  $query  = substr($query, 0, strrpos($query,"NATURAL JOIN"));
  if (sizeof($where)!=0){
    $query = $query." WHERE ";
    // construct the conditions.
    foreach (array_keys($where) as $condition){
      $query = $query."".$condition."'{$where[$condition]}' AND ";
    }
    $query  = substr($query, 0, strrpos($query,"AND"));
  }
  // Set the ordering conditions.
  $query = $query." GROUP BY {$group}";
  $query = $query." ORDER BY {$order}";
  $res = mysql_query($query);
  //echo "the query is ".$query;
  $data = array();

  // fetching the result of this query.
  while ($row = mysql_fetch_array($res)){
    $temp = array();
    foreach ($select as $column){
      $temp[$column]  = $row[$column];
    }
    foreach (array_keys($metadata) as $meta){
      $temp[$meta] = $metadata[$meta];
    }
    array_push($data,$temp);
    //construct the big array of data, called $data
  }
  //var_dump($data);
  //echo "<br/>";
  return $data;
}

/*
  TODO: calculate data.
*/
function accumulate($select,$duration,$where){
  if ($duration==null){
    return $select;
  }

}
/*
  This is not a function, just a brunch of queries.
  It is mainly for test purpose. It is too specific.
*/
function trial(){
  "select Month(Date),sum(BTUConversion) from EnergyData where Unit='klb.' and Year(Date)=2011 group by Month(Date)";
  "select Hour(Date),sum(BTUConversion) from EnergyData NATURAL JOIN MeterInfo NATURAL JOIN FuelType where FuelType='wind' and Year(Date)=2011 group by Hour(Date) order by Date";
}

function genericQueryWrapper($select, $from, $where, $order,$metadata){
  $duration = checkDuration($from,$where);
  $select = accumulate($select, $duration, $where);
  // We might need some calculations to get suitable duration.
  
  $group="";
  genericQuery($select, $from, $where, $group, $order, $metadata);
}

/*
  This function checks whether some data exist in database by $where constraints.
  

*/
function checkExistence($from, $where){
  $query = "SELECT EXISTS (SELECT 1 FROM ";
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
  $query = $query.") AS DECISION";
  //echo "what is the query: ".$query;
  $res = mysql_query($query);
  $row = mysql_fetch_array($res);
  if ($row["DECISION"] == 0){
    return false;
  }else{
    return true;
  }
}


/*
  Usually, $data is $_GET. This function is likely to be called from other php files, such as dropdown.php.
  Thus, $data is not necessary $_GET from this php URL.
  $type is the type we want to check. That is, we want to know whether there are data for $type = $value
  $value is associated with $type.
  An example of $type and $value is $type = "BuildingName" and $value = "Memorial Hall". 
  Probably there are other constraints in $data. What this function does is that
  it checks the database whether there are data for Memorial Hall based on those constraints in $data.  
*/

function preliminaryCheck($data, $type, $value){
  $where = parseConstraints($data);
  if ($value){
    $where[$type."="]=$value;
  }
  $from = array("EnergyData","DateObj","FuelType","MeterInfo","building");
  // $from now is hardcoded!! This is bad. Remember to modify here so that this function can work for all tables.
  if (checkExistence($from, $where)){
    return true;
  }else{
    return false;
  }
}

/*
  This function is not a good choice. Probably I will use a hash table instead. Still thinking.

*/
function getSmallerDuration($curDuration){
  
  if ($curDuration=="monthly"){
    return "daily";
  }
  if ($curDuration=="daily"){
    return "hourly";
  }

  if ($curDuration=="hourly"){
    return "10minutes";
  }

  if ($curDuration=="10minutes"){
    return "5minutes";
  }
  if ($curDuration=="5minutes"){
    return null;
  }

 
}

/*
  Check whether there are data for a specific duration and constraints.
  If not, we can call other functions to calculate data as needed if possible. 
  (This part is not handled by this function, but it is in general how this function is involved.)
*/
function checkDuration($from,$where){
  if (!$_GET["duration"]){
    return null;
  }else{ 
    if (checkExistence($from,$where)){
      return null;
      
    }else{
      $duration = getSmallerDuration($_GET["duration"]);
      $where["Duration="]=$duration;
      while ($duration && !checkExistence($from,$where)){
	$duration = getSmallerDuration($duration);
	$where["Duration="]=$duration;
      }
      if (!$duration || (!checkExistence($from,$where))){
	return false;
      }else{
	return $duration;
      }
    }
  }
}




/*
  Not used probably.
*/
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

/*
  This function is called by other pages outside this file.
  The essence of this file is this function.
  It acts like a main function in python, although it is actually not.
  
*/
function selectEnergy($columns,$constraints){
  
  $buildingName = meterToBuilding($meterID);
  $fuelType = meterToFuelType($meterID);
  $select = $columns; 
    //array("Date","FiscalYear","BuildingName","FuelType","MeasuredValue","Unit","BTUConversion");
  $from = array("EnergyData","DateObj","FuelType","MeterInfo","building");
  // this is bad. We should not hardcode $from. Need an alternative way.

  $where = $constraints;
  $order = "MeterID, FuelType, DATE(Date), TIME(Date)";
  $metadata = array();// can write metadata for each row.

  writeIntoCSV(genericQueryWrapper($select,$from,$where,$order,$metadata));
  
  
}
/*
  Output an array of data into csv format
  This part sets the header of the web page so that it can only download csv.
  To make this download work, please do not echo/print_r/var_dump anything inside this web page.
  Otherwise, this function will fail to set the header.
*/

function writeIntoCSV($data){
  if (sizeof($data)<1){
    echo "No data satisfy all constraints.<br>".'<a href="javascript:history.go(-1);">'.
	  '&lt;&lt Sorry, Go Back.</a>';
    return;
  }
 
  header("Content-Type:text/csv");
  $date = new DateTime("now");
  header("Content-Disposition:attachment;filename=energyData-" . $date->format("Ymd-H:i:s") . ".csv");
  $fp = fopen("php://output","w");

  fputcsv($fp,array_keys($data[0]));

  for ($i=0;$i< sizeof($data); $i++){
    fputcsv($fp,$data[$i]);
 
  }

  fclose($fp);
  
}

function getConstraints(){
  return parseConstraints($_GET);
  
}

/*
  This code is used to parse selected constraints from GET method/ protocol..
  This function is called by getConstraints() and preliminaryCheck().
  It returns an array of constraints that are ready to be included as parts of SQL.
  
*/
function parseConstraints($data){
  $field = array();
  
  if ($data["building"]){
    if (strcmp($data["building"],"all")!==0){
      $field["BuildingName="] = $data["building"];
    }
  }
  // construct a start date by parsing year, month and day.
  if ($data["startDateYear"]){ 
    if (strcmp($data["startDateYear"],"null")!==0){
      if ($data["startDateMonth"] && (strcmp($data["startDateMonth"],"null")!==0)){
	if ($data["startDateDay"] && (strcmp($data["startDateDay"],"null")!==0)){
	  $field["Date>="] =  date('Y-m-d H:i:s',mktime(0,0,0,(int)$data["startDateMonth"],(int)$data["startDateDay"],(int)$data["startDateYear"]));
	}else{
	  $field["Date>="] =  date('Y-m-d H:i:s',mktime(0,0,0,(int)$data["startDateMonth"],1,(int)$data["startDateYear"]));
	}
      }else{
	$field["Date>="] =  date('Y-m-d H:i:s',mktime(0,0,0,1,1,(int)$data["startDateYear"]));
      }
    }
  }  
  // similarly, this part constructs an end date.
   if ($data["endDateYear"]){ 
    if (strcmp($data["endDateYear"],"null")!==0){
      if ($data["endDateMonth"] && (strcmp($data["endDateMonth"],"null")!==0)){
	if ($data["endDateDay"] && (strcmp($data["endDateDay"],"null")!==0)){
	  $field["Date<"] =  date('Y-m-d H:i:s',mktime(0,0,0,(int)$data["endDateMonth"],(int)$data["endDateDay"]+1,(int)$data["endDateYear"]));
	}else{
	  $field["Date<"] =  date('Y-m-d H:i:s',mktime(0,0,0,(int)$data["endDateMonth"]+1,1,(int)$data["endDateYear"]));
	}
      }else{
	$field["Date<"] =  date('Y-m-d H:i:s',mktime(0,0,0,1,1,(int)$data["endDateYear"])+1);
      }
    }
   }  
   
  if ($data["fuelType"]){
    if (strcmp($data["fuelType"],"all")!==0){
      $field["FuelType="] = $data["fuelType"];
    }
  }
  if ($data["fiscalYear"]){
    $field["FiscalYear="] = $data["fiscalYear"];
  }
  if ($data["weekday"]){
    $field["Weekday="] = $data["weekday"];
  }
  if ($data["duration"]){
    $field["Duration="] = $data["duration"];
  }
  
  //var_dump($field);
  return $field;
}

/*
  process the URL from GET protocol
*/
function getColumns(){
  $field = array();
  foreach (array_keys($_GET) as $column){
    if (strcmp($_GET[$column],"yes")==0){
      array_push($field,$column);
    }
  }
  return $field;
}

//selectEnergy(getColumns(),getConstraints());

?>
