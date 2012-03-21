/*
S  Generate the URL based on the columns selected. 
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
	    isStartYear = document.getElementById("startDateYear") != undefined;
	    isEndYear = document.getElementById("endDateYear") != undefined;
	    var year = document.getElementById(temp+"Year").value;
	    var month = document.getElementById(temp+"Month").value;
	    var day = document.getElementById(temp+"Day").value;
	    url += temp+"Year="+year+"&"+temp+"Month="+month+"&"+temp+"Day="+day;
	    
	    
	    if (isStartYear && !isEndYear){		
		startYear = parseInt(year);
		startMonth = parseInt(month);
		startDay = parseInt(day);
		// redundant!
		if (endYear){
		    if (startYear>endYear){
			window.alert("Beginning date cannot be later than end date.");
			return false;
		    }else if (startYear == endYear && endMonth){
			if (startMonth>endMonth){
			    window.alert("Beginning date cannot be later than end date.");
			    return false;
			}else if (startMonth == endMonth && endDay){
			    if (startDay>endDay){
				window.alert("Beginning date cannot be later than end date.");
				return false;
			    } 				    
			} //endif endDay
		    } // endif endMonth
		} // endif endYear
	    }else if (!isStartYear && isEndYear){
		endYear = parseInt(year);
		endMonth = parseInt(month);
		endDay = parseInt(day);
		// redundant!
		if (startYear){
		    if (startYear>endYear){
			window.alert("Beginning date cannot be later than end date.");
			return false;
		    }else if (startMonth){
			if (startMonth>endMonth){
			    window.alert("Beginning date cannot be later than end date.");
			    return false;
			}else if (startDay){
			    if (startDay>endDay){
				window.alert("Beginning date cannot be later than end date.");
				return false;
			    } // endif
			} // endif startDay
		    } // endif startMonth	    
		} // endif startYear
	    } else if (isStartYear && isEndYear) {
		startYear = document.getElementById("startDateYear").options[document.getElementById("startDateYear").selectedIndex].value;
		startMonth = document.getElementById("startDateMonth").options[document.getElementById("startDateMonth").selectedIndex].value;
		startDay = document.getElementById("startDateDay").options[document.getElementById("startDateDay").selectedIndex].value;
		endYear = document.getElementById("endDateYear").options[document.getElementById("endDateYear").selectedIndex].value;
		endMonth = document.getElementById("endDateMonth").options[document.getElementById("endDateMonth").selectedIndex].value;
		endDay = document.getElementById("endDateDay").options[document.getElementById("endDateDay").selectedIndex].value;

		if (startYear > endYear ||
		   (startYear == endYear && startMonth > endMonth) ||
		   (startYear == endYear && startMonth == endMonth && startDay > endDay)) {
		    window.alert("Beginning Date Cannot be later than end Date.");
		    return false;
		}  
	    }
	    // endif endDate 	    
	} // end condition
	url += "&";
    } // end for loop
    
    url = url.substring(0,url.length-1);
    return url;
}

/*
  Dynamically generate drop-down menu according to the number of constraints selected.

*/
function addConstraints(selection){
    curCons = new Array();
    var constraintsDiv = document.getElementById("forConstraints");
    constraintsDiv.innerHTML="<div class='conditions' id='consDiv'>";
    var consNum = parseInt(selection.value);
    for (var i=0;i<consNum;i++){
	constraintsDiv.innerHTML+="<div>"+"Constraint "+(i+1)+" :<select id='consSel"+i+
	    "' onchange='optionHandler(this,"+i+")'><option VALUE='null'> --- </option></select></div>";
    }
    addOneConstraint(0);
}
/*
  Dynamically generate drop-down menu according to the number of columns selected.
*/

function log(what) {
    console.log(what);
}
function addColumns(colsNum){
    curCols = new Array();
    // var columnsDiv = document.getElementById("forColumns");
    // var colsNum = parseInt(selection.value);
    $("#forColumns").html(""); // clear
    log("came here");
    for (var i = 0; i < colsNum; i++) {
	addOneColumn(i);
    }
    /*columnsDiv.innerHTML = "<div class='attributes' id='colsDiv'>";
    for (var i = 0;i<colsNum;i++){
	
	columnsDiv.innerHTML+="<div>" + "Column "+(i+1)+" :<select id='colsSel"+i+
	    "' onchange='columnHandler(this,"+i+")'><option VALUE='null'> --- </option></select></div>";
    }
    columnsDiv.innerHTML+="</div>";
    addOneColumn(0);
     */
}
/*
  Handle column selection menus.
*/
function columnHandler(selection,id){
    curCols[id] = selection.value;
    // addOneColumn(id+1);
}
/*
  add one more drop-down menu for selection.
*/
function addOneColumn(id){
    var coldiv = $("#forColumns");

    var colsel = $("select").attr("id", "colsSel"+id);
    var colmat = $("div").append(colsel);
   
    log("problem might be here!");
    log(curCols);
    coldiv.append(colmat);    

    colsel.append($("option").attr("value", "null").text(" --- "));
    
    if (jQuery.inArray("Date(Date)",curCols)==-1 || jQuery.inArray("Date(Date)",curCols)>=id){
	colsel.append($("option").attr("value", "Date(Date)").text("Date"));
    }
    if (jQuery.inArray("Time(Date)",curCols)==-1 || jQuery.inArray("Time(Date)",curCols)>=id){
	// newString+="<OPTION VALUE='Time(Date)'>Time</OPTION>";
	colsel.append($("option").attr("value", "Time(Date)").text("Time"));
    }
    if (jQuery.inArray("Date",curCols)==-1 || jQuery.inArray("Date",curCols)>=id){
	// newString+="<OPTION VALUE='Date'>Date+Time</OPTION>";
	colsel.append($("option").attr("value", "Date").text("Date+Time"));
    }
    if (jQuery.inArray("FiscalYear",curCols)==-1 || jQuery.inArray("FiscalYear",curCols)>=id){
	// newString+="<OPTION VALUE='FiscalYear'>Fiscal Year</OPTION>";
	colsel.append($("option").attr("value", "FiscalYear").text("Fiscal Year"));
    }
    if (jQuery.inArray("Weekday",curCols)==-1 || jQuery.inArray("Weekday",curCols)>=id){
	// newString+="<OPTION VALUE='Weekday'>Weekday</OPTION>";
	colsel.append($("option").attr("value", "Weekday").text("Week day"));
    }
    if (jQuery.inArray("FuelType",curCols)==-1 || jQuery.inArray("FuelType",curCols)>=id){
	// newString+="<OPTION VALUE='FuelType'>Fuel Type</OPTION>";
	colsel.append($("option").attr("value", "FuelType").text("Fuel Type"));
    }
    if (jQuery.inArray("Duration",curCols)==-1 || jQuery.inArray("Duration",curCols)>=id){
	// newString+="<OPTION VALUE='Duration'>Duration</OPTION>";
	colsel.append($("option").attr("value", "Duration").text("Duratiion"));
    }
    if (jQuery.inArray("BuildingName",curCols)==-1 || jQuery.inArray("BuildingName",curCols)>=id){
	// newString+="<OPTION VALUE='BuildingName'>Building Name</OPTION>";
	colsel.append($("option").attr("value", "BuildingName").text("Building Name"));
    }
    /*
     if (jQuery.inArray("BuildingType",curCols)==-1 || jQuery.inArray("BuildingType",curCols)>=id){
     newString+="<OPTION VALUE='BuildingType'>Building Type</OPTION>";
     }*/
    if (jQuery.inArray("MeasuredValue",curCols)==-1 || jQuery.inArray("MeasuredValue",curCols)>=id){
	// newString+="<OPTION VALUE='MeasuredValue'>Measured Value</OPTION>";
	colsel.append($("option").attr("value", "MeasuredValue").text("Measured Value"));
    }
    if (jQuery.inArray("Unit",curCols)==-1 || jQuery.inArray("Unit",curCols)>=id){
	// newString+="<OPTION VALUE='Unit'>Unit</OPTION>";
	colsel.append($("option").attr("value", "unit").text("Unit"));
    }
    if (jQuery.inArray("BTUConversion",curCols)==-1 || jQuery.inArray("BTUConversion",curCols)>=id){
	// newString+="<OPTION VALUE='BTUConversion'>BTU Conversion</OPTION>";
	colsel.append($("option").attr("value", "BTUConversion").text("BTU Conversion"));
    }
    

    /*
    if (id < parseInt(document.getElementById("columns").options[document.getElementById("columns").selectedIndex].value)){
	var device = document.getElementById("colsSel"+id);

	
	newString ="<option VALUE='null'> --- </option>";
	// The following are the options we currently can support.
	// In order to avoid selecting the same attribute more than once at the same query, 
	// it checks whether the attribute has been selected.
	}

	device.innerHTML = newString;
    }
     */
}


/*
  Add options for one constraint.
*/
function addOneConstraint(id){
    if (id < parseInt(document.getElementById("constraints").options[document.getElementById("constraints").selectedIndex].value)){
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
	newString+="</select><div class='options' id='moreOptionDiv"+id+"'></div>";
	//alert(newString);
	device.innerHTML = newString;
    }
}
/*
  A simple function that calculate how many days a give month has.
  Output depends on the month and if the month is Feb, it also depends on the year.
*/
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

/*


*/
function optionHandler(selection,id){
    var curDiv = document.getElementById("moreOptionDiv"+id);
    curCons[id] = selection.value;
    addOneConstraint(id+1);
    var months = {1: "January", 2: "Febraury", 3: "March", 4: "April", 5: "May", 
		 6: "June", 7: "July", 8: "August", 9: "September", 10: "October",
		 11: "November", 12: "December"};
    curDiv.innerHTML="<div class='options' id='moreOption"+id+"'>";
    var option="";
    if (selection.value.toString() == "startDate"){
	option = "Year: <select id='startDateYear' onchange='calculateDay("+'"startDate"'+")'>";
	option +="<OPTION value='null'>All years</OPTION>";
	for (var i=2011;i>=1990;i--){
	    option += "<OPTION value='"+i+"'>"+i+"</OPTION>";
	}
	option += "</select>";
	option += "Month: <select id='startDateMonth' onchange='calculateDay("+'"startDate"'+")'>";
	option += "<OPTION value='null'>All months</OPTION>";
	for (var i=1;i<=12;i++){
	    option += "<OPTION value='"+i+"'>"+months[i]+"</OPTION>";
	}
	option += "</select>";
	option += "Day: <select id='startDateDay'>";
	option += "<OPTION value='null'>All days</OPTION>";
	option += "</select>";
    }else if (selection.value.toString() == "endDate"){
	option = "Year: <select id='endDateYear' onchange='calculateDay("+'"endDate"'+")'>";
	option +="<OPTION value='null'>All years</OPTION>";
	for (var i=2012;i>=1990;i--){
	    option+="<OPTION value='"+i+"'>"+i+"</OPTION>";
	}
	option += "</select>";
	option += "Month: <select id='endDateMonth' onchange='calculateDay("+'"endDate"'+")'>";
	option += "<OPTION value='null'>All months</OPTION>";
	for (var i=1;i<=12;i++){
	    option+="<OPTION value='"+i+"'>"+months[i]+"</OPTION>";
	}
	option += "</select>";
	option += "Day: <select id='endDateDay'>";
	option += "<OPTION value='null'>All days</OPTION>";
	option += "</select>";
	
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
	option+="<OPTION value='Annual'>Annual</OPTION>";
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
    
    //alert(curDiv.innerHTML);
}

var curCons, curCols;

// When the page is loaded, initialize values for drop-down menus.

window.onload = function(){
    //alert(document.getElementById("columns"));
    $("#columns").change(function(e) {
			     $("#forColumns").html("");
			     var numCols = this.options[this.selectedIndex].value;
			     addColumns(numCols);
			     return false;
			 });
    // addColumns(document.getElementById("columns"));
    /* )$("#constraints").change(function(e) {
				 $("#constraints").html("");
				 var numCols = this.options[this.selectedIndex].value;
				 addConstraints(numCols);
				 return false;
			});
     */
    // addConstraints(document.getElementById("constraints"));
   
}