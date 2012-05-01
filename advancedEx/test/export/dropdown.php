<?php
include_once("getDataOut.php");
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
if ($_GET["request"]=="building"){
  
  $query = "SELECT BuildingName FROM building ORDER BY BuildingName";
  $res = mysql_query($query);
  if (preliminaryCheck($_GET,"BuildingName",null)){
    echo "Building Name:  ";
    echo "<select name='building' id='building' onchange='restrictedOptionHandler(this)'>";
    
    echo "<OPTION value='all'>All buildings</OPTION>";
    
    while ($row = mysql_fetch_array($res)){
      //$_GET["building"]=$row["BuildingName"];
      if (preliminaryCheck($_GET,"BuildingName",$row["BuildingName"])){
	echo "<OPTION value='".$row["BuildingName"]."'>". $row["BuildingName"]."</OPTION>";
      }
    }
    echo "</select>";
  }else{
    echo "No data available for given constraints.<br> Revise your constraints.";
  }
  
}else if ($_GET["request"]=="fuelType"){

  $query = "SELECT FuelType FROM FuelType ORDER BY FuelType";
  $res = mysql_query($query);
  if (preliminaryCheck($_GET,"FuelType",null)){
    echo "Fuel Type: ";   
    echo "<select name='fuelType' id='fuelType' onchange='restrictedOptionHandler(this)'>";
    echo "<OPTION value='all'>All</OPTION>";
    while ($row = mysql_fetch_array($res)){
      if (preliminaryCheck($_GET,"FuelType",$row["FuelType"])){
	echo "<OPTION value='".$row["FuelType"]."'>". $row["FuelType"]."</OPTION>";
      }
    }
  echo "</select>";
  }else{
    echo "No data available for given constraints. <br> Revise your constraints.";
  }
}

?>
