<?php
// connect to database using php
$con = mysql_connect("localhost", "", "");
if (!$con) die("Could not connect: " . mysql_error());

// do stuff here, for example
/*
  $query_exec = mysql_query($query, $con);
  if (!$query_exec) {
    echo "Error executing the query";
  }
  where $query == "CREATE DATABASE my_db";

  // you could select a database like this:
  mysql_select_db("my_db", $con);
  $sql = "CREATE TABLE persons (
  FirstName varchar(15),
  LastName varchar(15)
  )";
  
  mysql_query($sql, $con);

 */

mysql_close($con);
?>