/*
  Generate the URL based on the columns selected. 
  Since the php code uses GET method to get data from the client (through JavaScript) 
  to the server (in php), a URL containing all desired information for the server is needed.
*/
function selectColumns(){
    var url ="";
    var columns = parseInt(document.getElementById("columns").value);
    if (columns==0){
	return false;
    }
    for (var i=0;i<columns;i++){
	var temp =  document.getElementById('colsSel'+i).value;
	if (temp=="null"){
	    return false;
	}
	url += temp +"=yes&";
	
    }
    url = url.substring(0,url.length-1);
    return url;
}
/*
  Similar to selectColumns(). This one generates the whole URL while selectColumns() just generates
  partial URL relating to the columns selected. This function also calls selectConstraints().

*/
function dataHandler(){
    var url = "";
    var cols = selectColumns();
    if (cols==false){
	alert("Please choose at least one column and choose a value for each column selection.");
	return;
    }
    url += cols;
    
    var cons = selectConstraints();
    
    if (cons!=false || parseInt(document.getElementById("constraints").value)==0){
	url +="&"+cons;
	//alert("getDataOut.php?"+url);
	//alert(url);
	window.location.href="getDataOut.php?"+url;
	// Equivalence: "Enter" the URL to the address field of the brower.
    }
}

/*
  Similar to selectColumns(). This one selects the constraints specified by the user.
  It generates a part of URL for dataHandler().

*/
function selectConstraints(){
    var url = "";
    // booleans to check starting date is earlier than (or the same as) the ending date. 
    var startYear = false;
    var endYear = false;
    var startMonth = false;
    var endMonth = false;
    var startDay = false;
    var endDay = false;
    var constraints = parseInt(document.getElementById("constraints").value);
 
    for (var i=0;i<constraints;i++){
	var temp =  document.getElementById('consSel'+i).value;

	
	if (temp.toString()!="startDate" && temp.toString()!="endDate"){
	    url += temp+"=";
	    url += document.getElementById(temp).value;
	    if (temp.toString()=="fiscalYear"){
		// checking
		if (startYear && parseInt(document.getElementById(temp).value)<startYear){
		    window.alert("Fiscal year cannot be earlier than the beginning year.");
		    return false;
		}else if (endYear && parseInt(document.getElementById(temp).value)>endYear+1){
		    window.alert("Fiscal year cannot be more than one year later than the end year.");
		    return false; 
		}
	    }
	}else{
	    var curDate = new Date();
	    if (temp.toString()=="startDate"){
		var curDate = $( "#startdatepicker" ).datepicker("getDate");
	    }
	    else {
		var curDate = $( "#enddatepicker" ).datepicker("getDate");
	    }
	    if (!curDate){
		alert("Please choose a date.");
		return false;
	    }
	    var day = curDate.getDate();
	    
	    var month = curDate.getMonth() + 1;
	    var year = curDate.getFullYear();
	    //alert("Year: "+year+", Month: "+month+", day: "+day);
	    
	    url += temp+"Year="+year+"&"+temp+"Month="+month+"&"+temp+"Day="+day;
	    
	    if (temp.toString()=="startDate"){
		
		startYear = parseInt(year);
		startMonth = parseInt(month);
		startDay = parseInt(day);
		if (endYear){
		    if (startYear>endYear){
			window.alert("Beginning date cannot be later than end date.");
			return false;
		    }else if (endMonth  && startYear == endYear){
			if (startMonth>endMonth){
			    window.alert("Beginning date cannot be later than end date.");
			    return false;
			}else if (endDay  && startMonth == endMonth){
			    if (startDay>endDay){
				window.alert("Beginning date cannot be later than end date.");
				return false;
			    } 				    
			} //endif endDay
		    } // endif endMonth
		} // endif endYear
	    }else if (temp.toString()=="endDate"){
		endYear = parseInt(year);
		endMonth = parseInt(month);
		endDay = parseInt(day);
		if (startYear){
		    if (startYear>endYear){
			window.alert("Beginning date cannot be later than end date.");
			return false;
		    }else if (startMonth && startYear == endYear){
			if (startMonth>endMonth){
			    window.alert("Beginning date cannot be later than end date.");
			    return false;
			}else if (startDay  && startMonth == endMonth){
			    if (startDay>endDay){
				window.alert("Beginning date cannot be later than end date.");
				return false;
			    } // endif
			} // endif startDay
		    } // endif startMonth	    
		} // endif startYear
	    } // endif endDate	    
	} // end condition
	url += "&";
    } // end for loop
    
    url = url.substring(0,url.length-1);
    return url;
}


/*
  Function for "Add one more constraint" button.

*/
function addConstraint(){
    var selection = document.getElementById("constraints");
 
    if (parseInt(selection.value)==7){ 
	// modify here if it is possible to set more constraints. 
	return false;
    }

    $("#forConstraints").append("<div class='conditions' id='consDiv"+selection.value+
	"'>"+"Constraint "+(parseInt(selection.value)+1)+" :<select id='consSel"+selection.value+
				"' onchange='optionHandler(this,"+parseInt(selection.value)+")'><option VALUE='null'> --- </option></select></div>");
    selection.value=parseInt(selection.value)+1;
    if (selection.value-1==0){
	addOneConstraint(0);
    }else if (curCons[selection.value-2]!=null){
	addOneConstraint(selection.value-1);
    } 
    
    
}
/*
  Dynamically generate drop-down menu according to the number of constraints selected.

*/

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


function addColumn(){
    var selection = document.getElementById("columns");
 
    if (parseInt(selection.value)==11){ 
	// modify here if it is possible to set more constraints. 
	return false;
    }

    $("#forColumns").append("<div>Column "+(parseInt(selection.value)+1)+" :<select id='colsSel"+selection.value+
	    "' onchange='columnHandler(this,"+selection.value+")'><option VALUE='null'> --- </option></select></div>");
    selection.value=parseInt(selection.value)+1;
    if (selection.value-1==0){
	addOneColumn(0);
    }else if (curCols[selection.value-2]!=null){
	addOneColumn(selection.value-1);
    } 
    
    
}
/*
  Dynamically generate drop-down menu according to the number of columns selected.
*/
function addColumns(selection){
    curCols = new Array();
    var columnsDiv = document.getElementById("forColumns");
    var colsNum = parseInt(selection.value);
    columnsDiv.innerHTML = "<div class='attributes' id='colsDiv'>";
    for (var i = 0;i<colsNum;i++){
	
	columnsDiv.innerHTML+="<div>Column "+(i+1)+" :<select id='colsSel"+i+
	    "' onchange='columnHandler(this,"+i+")'><option VALUE='null'> --- </option></select></div>";
	if (i%3==2){
	    columnsDiv.innerHTML +="<br/>";
	}
    }
    columnsDiv.innerHTML+="</div>";
    addOneColumn(0);
}
/*
  Handle column selection menus.
*/
function columnHandler(selection,id){
    curCols[id] = selection.value;
    addOneColumn(id+1);
}
/*
  add one more drop-down menu for selection.
*/
function addOneColumn(id){
 
    if (id < parseInt(document.getElementById("columns").value)){
	var device = document.getElementById("colsSel"+id);

	
	newString ="<option VALUE='null'> --- </option>";
	// The following are the options we currently can support.
	// In order to avoid selecting the same attribute more than once at the same query, 
	// it checks whether the attribute has been selected.
	if (jQuery.inArray("Date(Date)",curCols)==-1 || jQuery.inArray("Date(Date)",curCols)>=id){
	    newString+="<OPTION VALUE='Date(Date)'>Date</OPTION>";
	}
	if (jQuery.inArray("Time(Date)",curCols)==-1 || jQuery.inArray("Time(Date)",curCols)>=id){
	    newString+="<OPTION VALUE='Time(Date)'>Time</OPTION>";
	}
	if (jQuery.inArray("Date",curCols)==-1 || jQuery.inArray("Date",curCols)>=id){
	    newString+="<OPTION VALUE='Date'>Date+Time</OPTION>";
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
	    "' onchange='optionHandler(this,"+id+")'>";
	
	newString +="<option VALUE='null'> --- </option>";
	
	// The following are the options we currently can support.
	// In order to avoid selecting the same attribute more than once at the same query, 
	// it checks whether the attribute has been selected.
	
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
/*
  A simple function that calculate how many days a give month has.
  Output depends on the month and if the month is Feb, it also depends on the year.
  Not needed for now.
*/
/*
function calculateDay(selection){
    var year = document.getElementById(selection+"Year").value;
    var month = document.getElementById(selection+"Month").value;
    var daySel = document.getElementById(selection+"Day");
   
    if (year.toString() == "null" || month.toString() == "null"){
	return;
    }
    year = parseInt(year);
    month = parseInt(month);
    
    if (month!=2 && daySel.value.toString()!="null"){
	return;
    }
    daySel.innerHTML = "<OPTION value='null'>All days</OPTION>";
   
    var day = 0;
    switch (month)
    {
    case 1:
    case 3:
    case 5:
    case 7:
    case 8:
    case 10:
    case 12:
	day = 31;
	break;
    case 4:
    case 6:
    case 9:
    case 11:
	day = 30;
	break;
    case 2:
	if (year%100==0){
	    if (year%400==0){
		day = 29; // leap year
	    }else{
		day = 28;
	    }
	}else{
	    if (year%4==0){
		day = 29; // leap year
	    }else{
		day = 28;
	    }
	}
	break;
    default:
	window.alert("Internal error.");
	// should not go here.
	return ;
    }
   
   
    for (var i = 1;i<=day;i++){
	daySel.innerHTML += "<OPTION value='"+i+"'>"+i+"</OPTION>";
	// write output out.
    }
  
}
*/
/*


*/
function optionHandler(selection,id){
    var curDiv = document.getElementById("moreOptionDiv"+id);
    curCons[id] = selection.value;
    addOneConstraint(id+1);
    curDiv.innerHTML="<div class='options' id='moreOption"+id+"'>";
    var option="";
    if (selection.value.toString() == "startDate"){
	option = "<p>Date: <input id='startdatepicker' type='text' style='height:25px;'> (mm/dd/yyyy)</p>";
    }else if (selection.value.toString() == "endDate"){
	option = "<p>Date: <input id='enddatepicker' type='text' style='height:25px;'> (mm/dd/yyyy)</p>";
	
    }else if (selection.value.toString() == "fiscalYear"){
	option = "Year: <select id='fiscalYear'>";
	for (var i=2012;i>=1990;i--){
	    option+="<OPTION value='"+i+"'>"+i+"</OPTION>";
	}
	option+="</select>";
    }else if (selection.value.toString() == "weekday"){
	option = "Weekday: <select id='weekday'>";
	// generate weekday options.
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
	//option+="<OPTION value='Annual'>Annual</OPTION>";
	option+="</select>";
    }
    else if (selection.value.toString() == "building"){
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.open("GET","dropdown.php?building=true",true);
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
	xmlhttp.open("GET","dropdown.php?fuelType=true",true);
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
    if (selection.value.toString() == "startDate"){
	$(function() {
	$("#startdatepicker").datepicker();
	});
    }
    else if (selection.value.toString() == "endDate"){
	$(function() {
	    $("#enddatepicker").datepicker();
	});
    }
    
    //alert(curDiv.innerHTML);
}

var curCons, curCols;

// When the page is loaded, initialize values for drop-down menus.

window.onload = function(){
    //alert(document.getElementById("columns"));
    addColumns(document.getElementById("columns"));
    addConstraints(document.getElementById("constraints"));
   
}