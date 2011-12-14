<html>
<script language="JavaScript" src="jquery-1.6.1.min.js"></script>
<?php

echo "<head><title>Energy Data User Interface</title></head>";
echo "<body>";
echo "Select a building: ";

$connection = mysql_connect("localhost","root","carleton2014");
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
  
  echo "<OPTION value='".$row["BuildingName"]."'>". $row["BuildingName"]."</OPTION>";
  
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
?>
<div id="text">stuff</div>
<input type='submit' value='submit' onclick='buildingHandler()'></input>
<script language='javascript'>

  function buildingHandler(){
  var selected = document.getElementById('building').value;
  
  
  var xmlhttp = new XMLHttpRequest();
  var request = "getDataOut.php"//building="+selected;
  document.getElementById("text").innerHTML =encodeURI(request);
  xmlhttp.open("GET",encodeURI(request));
 
  xmlhttp.onstatechange =   function handler()
{
    if (xmlhttp.readyState == 4 /* complete */) {
      if (xmlhttp.status == 200) {
            alert(xmlhttp.responseText);
        }
    }
};
  xmlhttp.send();
}
</script>
<?php


mysql_close($connection);

?>
</html>