function selectColumns(){
    var url ="";
    var columns = parseInt(document.getElementById("columns").value);
    for (var i=0;i<columns;i++){
	var temp =  document.getElementById('colsSel'+i).value;
	url += temp +"=yes&";

    }
    url = url.substring(0,url.length-1);
    return url;
}
function dataHandler(){
    var url = "";
   
    url += selectColumns();
 
    var cons = selectConstraints();
 
    if (cons!=false || parseInt(document.getElementById("constraints").value)==0){
	url +="&"+cons;
	
	alert(url);
	window.location.href="getDataOut.php?"+url;
    }
}
function selectConstraints(){
    var url = "";
    var startYear = false;
    var endYear = false;
    var constraints = parseInt(document.getElementById("constraints").value);

    for (var i=0;i<constraints;i++){
	var temp =  document.getElementById('consSel'+i).value;


	if (temp.toString()!="startDate" && temp.toString()!="endDate"){
	    url += temp+"=";
	    url += document.getElementById(temp).value;
	    if (temp.toString()=="fiscalYear"){
		if (startYear && parseInt(document.getElementById(temp).value)<startYear){
		    window.alert("Fiscal year cannot be earlier than the beginning year.");
		    return false;
		}else if (endYear && parseInt(document.getElementById(temp).value)>endYear+1){
		    window.alert("Fiscal year cannot be more than one year later than the end year.");
		    return false; 
		}
	    }
	}else{
	    url += temp+"Year=";
	    var year = document.getElementById(temp+"Year").value;
	    if (temp.toString()=="startDate"){
		startYear = parseInt(year);
		if (endYear && startYear>endYear){
		    window.alert("Beginning year cannot be later than end year.");
		    return false;
		}
	    }else if  (temp.toString()=="endDate"){
		endYear = parseInt(year);
		if (startYear && startYear>endYear){
		    window.alert("Beginning year cannot be later than end year.");
		    return false;
		}
	    }
	    url += year;
	
	}
	url += "&";
    }
    
    url = url.substring(0,url.length-1);
    return url;
}
function addConstraints(selection){
    curCons = new Array();
    var constraintsDiv = document.getElementById("forConstraints");
    constraintsDiv.innerHTML="";
    var consNum = parseInt(selection.value);
    for (var i=0;i<consNum;i++){
	constraintsDiv.innerHTML+="<div class='conditions' id='consDiv"+i+
	    "'>"+"Constraint "+(i+1)+" :<select id='consSel"+i+
	    "' onchange='optionHandler(this,"+i+")'><option VALUE='null'> --- </option></select></div>";
    }
    addOneConstraint(0);
}
function addColumns(selection){
    curCols = new Array();
    var columnsDiv = document.getElementById("forColumns");
    var colsNum = parseInt(selection.value);
    columnsDiv.innerHTML = "<div class='attributes' id='colsDiv'>";
    for (var i = 0;i<colsNum;i++){

	columnsDiv.innerHTML+="Column "+(i+1)+" :<select id='colsSel"+i+
	    "' onchange='columnHandler(this,"+i+")'><option VALUE='null'> --- </option></select>";
	if (i%3==2){
	    columnsDiv.innerHTML +="<br/>";
	}
    }
    columnsDiv.innerHTML+="</div>";
    addOneColumn(0);
}
function columnHandler(selection,id){
    curCols[id] = selection.value;
    addOneColumn(id+1);
}
function addOneColumn(id){
    //alert("here");
    if (id < parseInt(document.getElementById("columns").value)){
	var device = document.getElementById("colsSel"+id);
	//alert(document.getElementById("colsSel"+id).innerHTML);

	
	newString ="<option VALUE='null'> --- </option>";

	if (jQuery.inArray("Date",curCols)==-1 || jQuery.inArray("Date",curCols)>=id){
	    newString+="<OPTION VALUE='Date'>Date</OPTION>";
	}
	if (jQuery.inArray("FiscalYear",curCols)==-1 || jQuery.inArray("FiscalYear",curCols)>=id){
	    newString+="<OPTION VALUE='FiscalYear'>Fiscal Year</OPTION>";
	}
	if (jQuery.inArray("Weekday",curCols)==-1 || jQuery.inArray("Weekday",curCols)>=id){
	    newString+="<OPTION VALUE='Weekday'>Weekday</OPTION>";
	}
	if (jQuery.inArray("FuelType",curCols)==-1 || jQuery.inArray("FuelType",curCols)>=id){
	    newString+="<OPTION VALUE='FuelType'>Fuel Type</OPTION>";
	}
	if (jQuery.inArray("Duration",curCols)==-1 || jQuery.inArray("Duration",curCols)>=id){
	    newString+="<OPTION VALUE='Duration'>Duration</OPTION>";
	}
	if (jQuery.inArray("BuildingName",curCols)==-1 || jQuery.inArray("BuildingName",curCols)>=id){
	    newString+="<OPTION VALUE='BuildingName'>Building Name</OPTION>";
	}
	/*
	if (jQuery.inArray("BuildingType",curCols)==-1 || jQuery.inArray("BuildingType",curCols)>=id){
	    newString+="<OPTION VALUE='BuildingType'>Building Type</OPTION>";
	}*/
	if (jQuery.inArray("MeasuredValue",curCols)==-1 || jQuery.inArray("MeasuredValue",curCols)>=id){
	    newString+="<OPTION VALUE='MeasuredValue'>Measured Value</OPTION>";
	}
	if (jQuery.inArray("Unit",curCols)==-1 || jQuery.inArray("Unit",curCols)>=id){
	    newString+="<OPTION VALUE='Unit'>Unit</OPTION>";
	}
	if (jQuery.inArray("BTUConversion",curCols)==-1 || jQuery.inArray("BTUConversion",curCols)>=id){
	    newString+="<OPTION VALUE='BTUConversion'>BTU Conversion</OPTION>";
	}

	
	//alert(newString);
	device.innerHTML = newString;
    }
}

    
/*
  Add options for one constraint.
*/
function addOneConstraint(id){
    if (id < parseInt(document.getElementById("constraints").value)){
	var device = document.getElementById("consDiv"+id);
	var newString = "Constraint "+(id+1)+" :<select id='consSel"+id+
	    "' onclick='optionHandler(this,"+id+")'>";
	//alert(newString);
	newString +="<option VALUE='null'> --- </option>";

	if (jQuery.inArray("startDate",curCons)==-1 || jQuery.inArray("startDate",curCons)>=id){
	    newString+="<OPTION VALUE='startDate'>Beginning Date</OPTION>";
	}
	if (jQuery.inArray("endDate",curCons)==-1 || jQuery.inArray("endDate",curCons)>=id){
	    newString+="<OPTION VALUE='endDate'>End Date</OPTION>";
	}
	if (jQuery.inArray("fiscalYear",curCons)==-1 || jQuery.inArray("fiscalYear",curCons)>=id){
	    newString+="<OPTION VALUE='fiscalYear'>Fiscal Year</OPTION>";
	}
	if (jQuery.inArray("weekday",curCons)==-1 || jQuery.inArray("weekday",curCons)>=id){
	    newString+="<OPTION VALUE='weekday'>Weekday</OPTION>";
	}
	if (jQuery.inArray("duration",curCons)==-1 || jQuery.inArray("duration",curCons)>=id){
	    newString+="<OPTION VALUE='duration'>Duration</OPTION>";
	}
	if (jQuery.inArray("building",curCons)==-1 || jQuery.inArray("building",curCons)>=id){
	    newString+="<OPTION VALUE='building'>Building</OPTION>";
	}
	if (jQuery.inArray("fuelType",curCons)==-1 || jQuery.inArray("fuelType",curCons)>=id){
	    newString+="<OPTION VALUE='fuelType'>Fuel Type</OPTION>";
	}	
	newString+="</select><div class='options' id='moreOptionDiv"+id+"'></div>"
	//alert(newString);
	device.innerHTML = newString;
    }
}
function optionHandler(selection,id){
    var curDiv = document.getElementById("moreOptionDiv"+id);
    curCons[id] = selection.value;
    addOneConstraint(id+1);
    curDiv.innerHTML="<div class='options' id='moreOption"+id+"'>";
    var option="";
    if (selection.value.toString() == "startDate"){
	option = "Year: <select id='startDateYear'>";
	for (var i=2011;i>=1990;i--){
	    option+="<OPTION value='"+i+"'>"+i+"</OPTION>";
	}
	option+="</select>";
    }
    else if (selection.value.toString() == "endDate"){
	option = "Year: <select id='endDateYear'>";
	for (var i=2011;i>=1990;i--){
	    option+="<OPTION value='"+i+"'>"+i+"</OPTION>";
	}
	option+="</select>";
    }else if (selection.value.toString() == "fiscalYear"){
	option = "Year: <select id='fiscalYear'>";
	for (var i=2012;i>=1990;i--){
	    option+="<OPTION value='"+i+"'>"+i+"</OPTION>";
	}
	option+="</select>";
    }else if (selection.value.toString() == "weekday"){
	option = "Weekday: <select id='weekday'>";
	
	option+="<OPTION value='Sun'>Sunday</OPTION>";
	option+="<OPTION value='Mon'>Monday</OPTION>";
	option+="<OPTION value='Tue'>Tuesday</OPTION>";
	option+="<OPTION value='Wed'>Wednesday</OPTION>";
	option+="<OPTION value='Thu'>Thursday</OPTION>";
	option+="<OPTION value='Fri'>Friday</OPTION>";
	option+="<OPTION value='Sat'>Saturday</OPTION>";
	option+="</select>";
    }else if (selection.value.toString() == "duration"){
	option = "Interval: <select id='duration'>";
	option+="<OPTION value='5mins'>5 minutes</OPTION>";
	option+="<OPTION value='10mins'>10 minutes</OPTION>";
	option+="<OPTION value='Hourly'>Hourly</OPTION>";
	
	option+="<OPTION value='Daily'>Daily</OPTION>";
	option+="<OPTION value='Monthly'>Monthly</OPTION>";
	option+="<OPTION value='Annual'>Annual</OPTION>";
	option+="</select>";
    }
    else if (selection.value.toString() == "building"){
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.open("GET","building.php?building=true",true);
	xmlhttp.onreadystatechange = function() {
	    if (xmlhttp.readyState==4) {
		option=xmlhttp.responseText;
		curDiv.innerHTML="<div class='options' id='moreOption"+id+"'>"+option+"</div>";
	
		//option +="</select><br/>";
	    }
	}
	xmlhttp.send(null);
    }
    else if (selection.value.toString() == "fuelType"){
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.open("GET","building.php?fuelType=true",true);
	xmlhttp.onreadystatechange = function() {
	    if (xmlhttp.readyState==4) {
		option=xmlhttp.responseText;
		curDiv.innerHTML="<div class='options' id='moreOption"+id+"'>"+option+"</div>";
	
		//option +="</select><br/>";
	    }
	}
	xmlhttp.send(null);
    }

    curDiv.innerHTML += option+"</div>";
    
    //alert(curDiv.innerHTML);
}

var curCons, curCols;
window.onload = function(){
    //alert(document.getElementById("columns"));
    addColumns(document.getElementById("columns"));
    addConstraints(document.getElementById("constraints"));
   
}