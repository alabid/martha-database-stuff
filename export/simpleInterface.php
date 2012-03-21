<html>
<head>
<title>Energy Sustainability Project Download Page</title>
<script language='JavaScript' src='jQuery.js'></script>
<script language='JavaScript' src='option.js'></script>
<script language="javascript" src="show_instructions_examples.js"></script>
<script language='JavaScript' src='bootstrap/js/bootstrap.min.js'></script>
<link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css"/>
<link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap-responsive.min.css"/>
<link rel="stylesheet" type="text/css" href="simpleInterface.css" />
</head>
<body>
   <div id="wrapper">
     <h1>Energy Sustainability Project Download Page</h1>
     <div id="instructions-examples">
       <ul class="nav nav-pills">
        <li class="instr"><a href="#">Instructions</a></li>
        <li class="resul"><a href="#">Example Results</a></li>
        <li><a href="#"><span class="caret"></span></a></li>
      </ul>
     <div id="instructions">
       <ul>
        <li>Select the number of columns (variables) you want included in the csv download.</li>
        <li>Then choose the names of the columns (variables) you want to be included in the csv download</li>
        <li>Then select the number of constraints you want applied to the CSV. Constraints are the conditions you want to limit the downloaded data to. </li>
        <li>Example constraints are Start Date, End Date, and so on.</li>
       </ul>
     </div>
     <div id="examples" display="none">
      <h3>Examples</h3>
      <a href="img/example_res.jpg"><img src="img/example_res.jpg" width="100px" height="100px"/> </a>
      <a href="img/example_res2.jpg"><img src="img/example_res2.jpg" width="100px" height="100px"/></a>
     </div>
     </div>
     <div id="select-data">
       <div class="select-block">
       <span>Select the number of columns: &nbsp;&nbsp;&nbsp;&nbsp;</span>
       <select name='columns' id='columns' onchange ='addColumns(this)'>
       <?php
          for ($i=0;$i<11;$i++){
            echo "<option value='".$i."'>". $i."</option>";
          }
       ?>
       </select><br/>
       <div id="forColumns"></div>
       <button id="add-column" class="btn">Add Column</button>
     </div>
     <div class="select-block">
       <span>Select the number of constraints: </span>   
       <select name name='constraints' id='constraints' onchange ='addConstraints(this)' >
       <?php
         for ($i=0;$i<8;$i++){
          echo "<OPTION value='".$i."'>". $i."</OPTION>";
         }
        ?>
        </select><br/>
        <div id="forConstraints"></div>
        <button id="add-cons" class="btn">Add Constraint</button>
      </div>
    <button onclick='dataHandler()'>Download Data as CSV</button>
  </div>
 </div>
</html>

