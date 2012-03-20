<?php
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
if ($_GET["building"]){
  echo "Select a building:  ";
  $query = "SELECT BuildingName FROM building ORDER BY BuildingName";
  $res = mysql_query($query);
  
  echo "<select name='building' id='building'>";
  echo "<OPTION value='all'>All buildings</OPTION>";
  while ($row = mysql_fetch_array($res)){
    
    echo "<OPTION value='".$row["BuildingName"]."'>". $row["BuildingName"]."</OPTION>";
    
  }
  
  echo "</select>";
}else if ($_GET["fuelType"]){
  echo "Select a type of fuel to analyze: ";   

  $query = "SELECT FuelType FROM FuelType ORDER BY FuelType";
  $res = mysql_query($query);
  echo "<select name='fuelType' id='fuelType'>";
  echo "<OPTION value='all'>All</OPTION>";
  while ($row = mysql_fetch_array($res)){
    echo "<OPTION value='".$row["FuelType"]."'>". $row["FuelType"]."</OPTION>";
  }
  echo "</select>";
}

?>
