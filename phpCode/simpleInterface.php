<?php
echo "<html>"
echo "<head><title>Energy Data User Interface</title></head>";
echo "<body>";
echo "Select a building: ";

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

$query = "SELECT BuildingName FROM building ORDER BY BuildingName";
$res = mysql_query($query);

echo "<select name='building' id='building'>";

while ($row = mysql_fetch_array($res)){
  
  echo "<OPTION value=".$row["BuildingName"].">". $row["BuildingName"]."</OPTION>";
  
}

echo "</select><br/>";

echo "Select a type of fuel to analyze: <br/>";   

$query = "SELECT Type FROM FuelType ORDER BY Type";
$res = mysql_query($query);

echo "<select name='FuelType' id='FuelType'>";

while ($row = mysql_fetch_array($res)){

  echo "<OPTION value=".$row["Type"].">". $row["Type"]."</OPTION>";

  
}
echo "</select><br/>";

echo "<input type='submit' value='submit' onclick='buildingHandler'></input>";
echo "<script language='javascript'>\n";
echo "function buildingHandler(){";
echo "var selected= document."
echo "location.href=forfun.php?building=";
echo "</script>\n";


$building = $_GET["building"];
$query = "SELECT * FROM building WHERE buildingNAME = '{$building}'";
$res = mysql_query($query);
if ($row = mysql_fetch_array($res)){
  $tbl = mysql_query("SHOW COLUMNS FROM building");
  
  while ($rowTbl = mysql_fetch_assoc($tbl)){
    echo $row[$rowTbl['Field']]."\t";
    
  }
}
mysql_close($connection);
echo "</html>";
?>
