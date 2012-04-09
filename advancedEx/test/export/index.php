<html>
<head>
<title>Energy Sustainability Project Download Page</title>
<script language='JavaScript' src='jquery-ui-1.8.18/jquery-1.7.1.js'></script>
<script language='JavaScript' src='option.js'></script>
<script language="javascript" src="show_instructions_examples.js"></script>
<script language='JavaScript' src='bootstrap/js/bootstrap.min.js'></script>
<script src="jquery-ui-1.8.18/ui/jquery.ui.core.js"></script>
<script src="jquery-ui-1.8.18/ui/jquery.ui.widget.js"></script>
<script src="jquery-ui-1.8.18/ui/jquery.ui.datepicker.js"></script>
<link rel="stylesheet" href="jquery-ui-1.8.18/themes/base/jquery.ui.all.css">
<link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css"/>
<link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap-responsive.min.css"/>
<link rel="stylesheet" type="text/css" href="simpleInterface.css" />
</head>
<body>

   <div id="wrapper">
     <h1 style="color:blue;text-align:center;">Energy Sustainability Project Download Page</h1>
     <div id="instructions-examples">
       <ul class="nav nav-pills">

        <li class="instr"><a href="#">Instructions</a></li>
        <li class="resul"><a href="#">Example Results</a></li>
        <li><a href="#"><span class="caret"></span></a></li>
      </ul>
     
     <div id="instructions">
       <ul>
	 <li>Procedures: Select columns => Select constraints =>
     Download the data as csv</li>
        <li>To choose the number of columns you want to include in
     your csv download, you can either choose the number of columns you want in
     the beginning or click on "Add One More Column" button to
     add one column each time. (Similar for selecting # of constraints.) </li>
        <li>Then, you need to specify what each column in the compiled
     csv file is.</li>
        <li>Since the database contains a huge amount of data, you may
     only want to download a small portion of the data by some
     conditions (constraints). (Also, you may not be able to download
     the whole database as csv at once by the same reason.)</li>

     <li>Note: You can either use date picker to pick a date or type
        in the date you want in the format shown if you choose either start
        date or end date as one of your constraints. </li>
        
       </ul>
     </div>
     <div id="examples" display="none">
       <h3>Examples</h3>
       <a href="img/example_res.jpg" target="_blank"><img src="img/example_res.jpg" width="100px" height="100px"/> </a>
       <a href="img/example_res2.jpg" target="_blank"><img src="img/example_res2.jpg" width="100px" height="100px"/></a>
     </div>
     </div>
     <div class="container-fluid">
     <div class="row-fluid">
     <div id="select-data" class="span5">
             <div class="well">
       <span>Select # of columns: &nbsp;&nbsp;&nbsp;&nbsp;</span>
       <select name='columns' id='columns' onchange ='addColumns(this)' style='width:50px;'>
	 <?php
	 for ($i=0;$i<12;$i++){
	 echo "<option value='".$i."'>". $i."</option>";
	   }
	   ?>
       </select><br/>
     <div id="forColumns"></div>
     <button onclick="addColumn()" id="add-column" class="btn">Add One More Column</button>
     </div>
     </div>
     <div id="select-constraints" class="span6">
       <div class="well">
       <span>Select # of constraints: </span>   
       <select name name='constraints' id='constraints' onchange ='addConstraints(this)' style='width:50px;'>
	 <?php
	 for ($i=0;$i<8;$i++){
	 echo "<OPTION value='".$i."'>". $i."</OPTION>";
	   }
	   ?>
       </select><br/>
        <div id="forConstraints"></div>
	<button onclick="addConstraint()" id="add-cons" class="btn">Add One More Constraint</button>
	</div>
	</div>
	</div>
	</div>
	</div>
	</div>
	<br>
	<div class="container-fluid">
	<div class="row-fluid">
	<div class="span5"></div>
	<div class="span5" align="center"><button onclick='dataHandler()' class="btn">Download Data as CSV</button></div>
	</div>
	</div>

	
</html>

