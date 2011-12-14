<html>

<?php

echo "<head><title>Energy Data User Interface</title></head>";
echo "<body>";
echo "Select a building: <br/>";

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
  
  echo "<OPTION value='".$row["BuildingName"]."'>". $row["BuildingName"]."</OPTION>";
  
}

echo "</select><br/>";

echo "Select a type of fuel to analyze: <br/>";   

$query = "SELECT Type FROM FuelType ORDER BY Type";
$res = mysql_query($query);

echo "<select name='FuelType' id='FuelType'>";
echo "<OPTION value='all'>All</OPTION>";
while ($row = mysql_fetch_array($res)){

  echo "<OPTION value=".$row["Type"].">". $row["Type"]."</OPTION>";

  
}

echo "</select><br/>";

echo "Select a fiscal year to analyze: <br/>";   


echo "<select name='FiscalYear' id='FiscalYear'>";
echo "<OPTION value='any'>--</OPTION>";
  for ($i=1991;$i<2012;$i++){

  echo "<OPTION value='".$i."'>". $i."</OPTION>";
    
  
    }

echo "</select><br/>";

echo "Select the number of constraints: <br/>";   


echo "<select name='constraints' id='constraints' onchange ='addConstraints(this)' >";
  for ($i=0;$i<10;$i++){

  echo "<OPTION value='".$i."'>". $i."</OPTION>";

  
    }

echo "</select><br/>";
echo "<div id='forConstraints'></div>";
echo "Select a type of fuel to analyze: <br/>";   


?>
<div id="text">stuff</div>
<input type='submit' value='submit' onclick='buildingHandler()'></input>
<script language='javascript'>

  function buildingHandler(){
  var selected = document.getElementById('building').value;
  window.location.href="getDataOut.php?building="+selected;
}
function addConstraints(selection){

var constraintsDiv = document.getElementById("forConstraints");
constraintsDiv.innerHTML="";
var consNum = parseInt(selection.value);
for (var i=0;i<consNum;i++){
constraintsDiv.innerHTML+="<div id='consDiv"+i+
  "'>"+"Constraint "+(i+1)+" :<select id='consSel"+i+"' onchange='optionHandler(this,"+i+")'>"+
    "<OPTION VALUE='null'>---</OPTION>"+
    "<OPTION VALUE='Date'>Date</OPTION>"+
      "<OPTION VALUE='FiscalYear'>FiscalYear</OPTION>"+
	"<OPTION VALUE='Location'>Location</OPTION>"+
	  "<OPTION VALUE='Building'>Building</OPTION>"+
	    "<OPTION VALUE='Date'>Date</OPTION>"
    +"</select><div id='moreOptionDiv"+i+"'></div></div>";
}
}
function optionHandler(selection,id){
    var curDiv = document.getElementById("moreOptionDiv"+id);
    curDiv.innerHTML="<div id='moreOption"+id+"'>";
      var option="";
      if (selection.value.toString() == "Date"){
      option = "Year: <select id='consYear"+id+">"
	for (var i=1990;i<2012;i++){
	option+="<OPTION value='"+i+"'>"+i+"</OPTION>";
	  }
	  option+="</select>";
	  }

      curDiv.innerHTML+=option+"</div>";
    
    //alert(curDiv.innerHTML);
    }
</script>
<?php

mysql_close($connection);

?>
</html>