<html>
<head>
<?php
echo "<title>Energy Data User Interface</title>";
?>
<script language='JavaScript' src='jQuery.js'></script>
<script language='JavaScript' src='option.js'></script>
<link rel="stylesheet" type="text/css" href="simpleInterface.css" />
</head>
<?php
echo "<body>";
echo "<h1>Energy Data Simple User Interface</h1>";

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



echo "Select the number of columns:     ";   


echo "<select name='columns' id='columns' onchange ='addColumns(this)' >";
  for ($i=0;$i<11;$i++){

  echo "<OPTION value='".$i."'>". $i."</OPTION>";

  
  }

echo "</select><br/>";

echo "<div id='forColumns'></div>";


echo "<p>Select the number of constraints:    ";   


echo "<select name='constraints' id='constraints' onchange ='addConstraints(this)' >";
  for ($i=0;$i<8;$i++){

  echo "<OPTION value='".$i."'>". $i."</OPTION>";

  
    }

echo "</select></p>";
echo "<div id='forConstraints'></div>";


?>

<input type='submit' value='Download Data as CSV' onclick='dataHandler()'></input>

<?php

mysql_close($connection);

?>
</html>