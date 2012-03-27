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
<script type="text/javascript">
   $(document).ready(function(){
       $(".collapse-in").css("display", "none");
       $(".click-collapse-in").click(function() {
	   var col = $(this).parent().next();
           if (col.css("display") === "none") {
	     col.slideDown();
	   } else {
	     col.slideUp();
	   }
	   return false;
	 });
    });
</script>
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
$(".collapse").collapse();


  function changeYear(choice){
    var div = document.getElementById("checkDiv");
    div.innerHTML = "<input id='toCheck' type='checkbox' value='Yes' /> I understand all the procedures and "+
     "I'm sure "+'"'+energyName[choice]+"-"+document.getElementById("year").value+
      '"'+" is the one I am uploading.";
   document.getElementById("browse").action = "upload.php?type="+choice+"&year="+document.getElementById("year").value;
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
     div.innerHTML += "<form id='browse' class='classic1' enctype='multipart/form-data' " +
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
     if (choices.value=="log"){
       changeYear(choices.value);
     }
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
<div width=10px;><marquee xmlns="http://www.w3.org/1999/xhtml" class="lead" behavior="alternate">A user interface
for data management. </marquee></div>
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
	<h3><a href="#" class="click-collapse-in">Energy Data Management Project</a></h3>
	<div id="project" class="collapse-in">  
               <br/>
	       <strong>This user interface is for the Carleton Energy Sustainability Project.</strong>
               <br/><br/>
	</div>
	<h3><a href="#" class="click-collapse-in">Collaborators</a></h3>
	<div id="collaborator" class="collapse-in">
		<p> (Alphabetical order by last name)
          <li><a href="mailto:alabid@carleton.edu">Daniel Alabi   (alabid@carleton.edu)</a></li>
          <li><a href="mailto:plackie@carleton.edu">Paula Lackie   (plackie@carleton.edu)</a></li>
          <li><a href="mailto:mlarson@carleton.edu">Martha Larson   (mlarson@carleton.edu)</a></li>
          <li><a href="mailto:linji@carleton.edu">Jie Lin  (linji@carleton.edu)</a></li>
		</p>
	</div>
	<h3><a href="#" class="click-collapse-in">More information</a></h3>
	<div id="info" class="collapse-in">
		<p>
	Contact <strong>Martha Larson</strong> for more information and arrangment for tours.
	  
		</p>
		<ul><ol>Manager of Campus Energy and Sustainability</ol>
		  	<ol>Office:Facilities Building 302</ol>
			<ol>Email:mlarson@carleton.edu</ol>
			<ol>Phone:x7893</ol>
		
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
   } else if ($_GET["option"] && $_GET["option"] == "tutorial") {
     echo <<<UPLS
       <div class="alert">
        <strong>Please</strong> Please read the whole tutorial if possible before you start uploading
       </div>
	<div class="upload-tutorial">
	   <h2>HOW TO IMPORT DATA USING THIS INTERFACE</h2>			   
            <ul>
	     <li>
              Click on the "Ready to Upload" button on the left pane of the import facility page import.php.
	     </li>
             <p>
    							     You should see something like this:</p>
             <a href="img/ready_upload.jpg"><img src="img/ready_upload.jpg" width="300px" height="300px"/></a>
              <br/>
	      <li>Choose the type of file you want to upload. Right now the system supports only these types of files:
             </li>  
            </ul>
           <ul>
            <ol>Wind Turbine #1 data files</ol>
            <ol>Steam Plant Production Logs</ol>
            <ol>Electric Demand</ol>
            <ol>Weekly Electric Consumption</ol>
	    <ol>(Siemens System) Campus Water Consumption</ol>
            <ol>(Siemens System) Campus Steam Consumption</ol>
            <ol>(Siemens System) Campus Electric Consumption</ol>
          </ul> 
          <h3>Wind Turbine #1</h3>
            <p>=> Only Hourly Wind Turbine Data is currently supported. </p>
            <p>An example is the 2011 Hourly Wind Turbine Data file from Wind Turbine #1 shown below:</p>
            <a href="img/wind_turbine1.jpg"><img src="img/wind_turbine1.jpg" width="300px" height="300px"/></a><br/><br/>
	  <h3>Steam Plant Production Logs</h3>
           <p>=> The steam Plant production logs contains information about steam, water, and 
             gas consumption by the Carleton College Facilities Steam Plant.
           </p>
           <p>An example is the Steam Plant Production Log 2009 shown below:</p>
           <p><strong>Filename: "Steam Plant Production Log 2009.xls"</strong></p>
           <a href="img/raw_steam.jpg"><img src="img/raw_steam.jpg" width="300px" height="300px"/></a>
	   <p> 
           Then convert each worksheet in this ".xls" file to a tab-delimited ".txt" by
           using the "Save As" option in excel.<br/>
           Also collapse the spaces in the filename, so for example, 
           "Steam Plant Production Log 2009.xls" will become "SteamPlantProductionLog2009.txt".<br/>
           This is how the file to be uploaded ("SteamPlantProductionLog2009.txt") should look like:
           </p>
           <p><strong>(in Excel)</strong></p>
           <a href="img/converted_steam_excel.jpg"><img src="img/converted_steam_excel.jpg" width="300px" height="300px"/></a>  <br/>
            <p><strong>(in a text editor)</strong></p>
           <a href="img/converted_steam_tedit.jpg"><img src="img/converted_steam_tedit.jpg" width="300px" height="300px"/>                  </a>
           </p><br/><br/>

           <h3>Electric Demand</h3>
	    <p>File to be uploaded should look like this: </p>
             <a href="img/elec_demand.jpg"><img src="img/elec_demand.jpg" width="300px" height="300px"/></a><br/><br/>				     <h3>Weekly Electric Consumption</h3>
            <p>File to be uploaded should look like this:</p> 
              <a href="img/weekly_electric.jpg"><img src="img/weekly_electric.jpg" width="300px" height="300px"/></a><br/><br/>
	   <h3>(Siemens System) Campus Water Consumption</h3>
            <p>File to be uploaded should look like this: </p>
	    <p><strong>Filename: "CAMPUS WATER.csv"</strong></p>
            <a href="img/campus_water.jpg"><img src="img/campus_water.jpg" width="300px" height="300px"/></a><br/><br/>
            <h3>(Siemens System) Campus Steam consumption</h3>
            <p>File to be uploaded should look like this: </p>
	    <p><strong>Filename: "CAMPUS STEAM.csv"</strong></p>
            <a href="img/campus_steam.jpg"><img src="img/campus_steam.jpg" width="300px" height="300px"/></a><br/><br/>
	    <h3>(Siemens System) Campus Electric Consumption</h3>
	    <p>File to be uploaded should look like this: </p>
	    <p><strong>Filename: "CAMPUS ELECTRIC.csv"</strong></p>
             <a href="img/campus_electric.jpg"><img src="img/campus_electric.jpg" width="300px" height="300px"/></a><br/><br/>
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