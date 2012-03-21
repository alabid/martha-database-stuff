<html>
<title>Carleton College Energy Data</title>
<script type="text/javascript" src="jquery-ui-1.8.18/jquery-1.7.1.js"></script>
<link href="css/main.css" rel="stylesheet" type="text/css">
<linl href="css/demos.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="jquery-ui-1.8.18/themes/base/jquery.ui.all.css">
<script src="jquery-ui-1.8.18/ui/jquery.ui.core.js"></script>
<script src="jquery-ui-1.8.18/ui/jquery.ui.widget.js"></script>
<script src="jquery-ui-1.8.18/ui/jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
<link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">

<body>

<script language="javascript">


var energyName = {};
energyName["wind1"] ="Wind Turbine #1";
energyName["log"] = "Steam Plant Production Logs";
energyName["elecDemand"] = "Electric Demand";
energyName["weeklyElecConsump"] = "Weekly Electric Consumption";
energyName["campusWater"] = "(Siemens System) Campus Water Consumption";
energyName["campusSteam"] = "(Siemens System) Campus Steam Consumption";
energyName["campusElec"] = "(Siemens System) Campus Electric Consumption";
energyName["bill"] = "Bills";

	$(function() {
		$( "#accordion" ).accordion();
	});
/*$(function() {
		$( "#datepicker" ).datepicker();
	});*/

  function changeYear(choices){
    var div = document.getElementById("checkDiv");
    div.innerHTML = "<input id='toCheck' type='checkbox' value='Yes' /> I understand all the procedures and "+
     "I'm sure "+'"'+energyName[choices]+"-"+document.getElementById("year").value+
      '"'+" is the one I am uploading.";
 }
  function uploadHandler(){
     var choices = document.getElementById("choices");
     var div = document.getElementById("uploadInterface");
     div.innerHTML ="You have chosen "+energyName[choices.value]+".";
     var additive = "";
     if (choices.value=="log"){
         additive +="Select the year of the data: <select id='year' onchange='changeYear(&quot;"+choices.value+"&quot;)'>";
	 for (var i=2012;i>1990;i--){
	     additive += "<option value='"+i+"'>"+i+"</option>";
	 }
	 additive +="</select><br>";
	       

     }
     div.innerHTML += "<form class='classic1' enctype='multipart/form-data' " +
     "action='upload.php?type="+choices.value+"'"+
     " method='POST' onsubmit='return check()'>" +
     "<input type='hidden' name='MAX_FILE_SIZE' value='900000000' />"+
     "Choose a file to upload: <input id='uploadedfile' name='uploadedfile' type='file' /><br />"+
      additive + 
     "<div id='checkDiv'><input id='toCheck' type='checkbox' value='Yes' /> I understand all the procedures and "+
     "I'm sure "+'"'+energyName[choices.value]+'"'+" is the one I am uploading.</div>" +
     "<input id='formbutton' class='formbutton' type='submit' value='Upload File' />"+
     "<input type='submit' value='Restart' onclick='reload()'/>" +
     "</form>";

     }
     function check(){
          if (!document.getElementById("toCheck").checked){
           alert("Must check first");
           return false;
       };
         return true;
     }
     function reload(){
    
     var div = document.getElementById("uploadInterface");
     var optionMenu = "";
     for (var abbr in energyName){
        optionMenu +="<option value='"+abbr+"'>"+energyName[abbr]+"</option>";
     }

     div.innerHTML = 'Choose what you will upload:<select id="choices">'+
              optionMenu+
	      '<input id="button" class="button" type="submit" value="Start" onclick="uploadHandler()"/>';
	     
	     }
	     </script>
<h1 xmlns="http://www.w3.org/1999/xhtml">Carleton College Energy Data</h1>
<marquee xmlns="http://www.w3.org/1999/xhtml" class="lead" behavior=alternate>A user interface
for data management. </marquee>
<br>
<div class="container-fluid">
<div class="row-fluid">
  <div class="span3">
   <div class="well sidebar-nav">
   <ul class="nav nav-list">

              <li class="nav-header">Welcome!</li>
	       <?php
	      if ((!$_GET["option"]) || ($_GET["option"] && $_GET["option"]=="instructions")){
              echo "<li class='active'>";
	      }else{
	      echo "<li>";
	      }
	      ?><a href="index.php?option=instructions">Instructions</a></li>
	       <?php
	      if ($_GET["option"] && $_GET["option"]=="tutorial"){
              echo "<li class='active'>";
	      }else{
	      echo "<li>";
	      }
	      ?><a href="index.php?option=tutorial">Tutorial</a></li>
               <?php
	      if ($_GET["option"] && $_GET["option"]=="format"){
              echo "<li class='active'>";
	      }else{
	      echo "<li>";
	      }
	      ?><a href="index.php?option=format">Download Data Formats</a></li>
               <?php
	      if ($_GET["option"] && $_GET["option"]=="contact"){
              echo "<li class='active'>";
	      }else{
	      echo "<li>";
	      }
	      ?><a href="index.php?option=contact">Contact Us</a></li>
               <?php
	      if ($_GET["option"] && $_GET["option"]=="upload"){
              echo "<li class='active'>";
	      }else{
	      echo "<li>";
	      }
	      ?><a href="index.php?option=upload">Ready to Upload</a></li>
             
            </ul>
          </div><!--/.well -->
        </div><!--/span-->
	<div class="span7">
 <div class="well sidebar-nav">
  <?php
   if ((!$_GET["option"]) || ($_GET["option"] && $_GET["option"]=="instructions")){
    $file = fopen("guide.txt","r") or die("Can't open file");
     $data =fread($file, filesize("guide.txt"));
 
    $content = explode("\n",$data);
    if (sizeof($content) == 1){
      $content = explode("\r",$data);
    }

   
   echo "<span class='label label-success'>Instructions</span>";
    for ($i = 0;$i< sizeof($content);$i++){
      echo "<p>".$content[$i]."</p>";
     }
   }else if ($_GET["option"] && $_GET["option"]=="format"){
       $energyName = array("wind1.csv" => "Wind Turbine #1",
            "log.csv" => "Steam Plant Production Logs",
            "elecDemand.csv" => "Electric Demand",
             "weeklyElecConsump.csv" => "Weekly Electric Consumption",
            "campusWater.csv" => "(Siemens System) Campus Water Consumption",
            "campusSteam.csv" => "(Siemens System) Campus Steam Consumption",
            "campusElec.csv" => "(Siemens System) Campus Electric Consumption",
            "bill.csv" => "Bills");
 
      foreach ($energyName as $key => $value){

          echo "<a href='template/".$key."'>".$value."</a><br>";
          unset($value);
      }
  

   }else if ($_GET["option"] && $_GET["option"]=="contact"){
   echo <<<UPLS
   <div id="accordion">
	<h3><a href="#">Energy Data Management Project</a></h3>
	<div>
		<p>
		Some stuff here, probably an overview
		</p>
	</div>
	<h3><a href="#">Collaborators</a></h3>
	<div>
		<p> (Alphabetical order by last name)
	  <li>Daniel Alabi</li>
	  <li>Paula Lackie</li>
	  <li>Martha Larson</li>
	  <li>Jie Lin</li>
	  <li>More</li>
		</p>
	</div>
	<h3><a href="#">More information</a></h3>
	<div>
		<p>
	Contact <strong>Martha Larson</strong> for more information and arrangment for tours.
	  
		</p>
		<ul><li>Manager of Campus Energy and Sustainability</li>
		  	<li>Office:Facilities Building 302</li>
			<li>Email:mlarson@carleton.edu</li>
			<li>Phone:x7893</li>
		
		</ul>
	</div>
</div>
UPLS;
   }else if ($_GET["option"] && $_GET["option"]=="upload"){
   echo <<<UPLS
  <div class="alert">
    <a class="close" data-dismiss="alert">x</a>
    <strong>Warning!</strong> Must read instructions first.</div>
 <div id='uploadInterface' >
   <script>reload();</script>
</div>
UPLS;
   }
   
  ?>
 </div><!--/.well -->
</div>


  


</div>
</div>
  

</body>
</html>