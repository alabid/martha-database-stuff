
function dataHandler(){
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
		    return;
		}else if (endYear && parseInt(document.getElementById(temp).value)>endYear+1){
		    window.alert("Fiscal year cannot be more than one year later than the end year.");
		    return;
		}
	    }
	}else{
	    url += temp+"Year=";
	    var year = document.getElementById(temp+"Year").value;
	    if (temp.toString()=="startDate"){
		startYear = parseInt(year);
		if (endYear && startYear>endYear){
		    window.alert("Beginning year cannot be later than end year.");
		    return;
		}
	    }else if  (temp.toString()=="endDate"){
		endYear = parseInt(year);
		if (startYear && startYear>endYear){
		    window.alert("Beginning year cannot be later than end year.");
		    return;
		}
	    }
	    url += year;
	
	}
	url += "&";
    }
    
    url = url.substring(0,url.length-1);
    window.location.href="getDataOut.php?"+url;
}
function addConstraints(selection){
    curCons = new Array();
    var constraintsDiv = document.getElementById("forConstraints");
    constraintsDiv.innerHTML="";
    var consNum = parseInt(selection.value);
    for (var i=0;i<consNum;i++){
	constraintsDiv.innerHTML+="<div id='consDiv"+i+
	    "'>"+"Constraint "+(i+1)+" :<select id='consSel"+i+
	    "' onchange='optionHandler(this,"+i+")'><option VALUE='null'> --- </option></select></div>";
    }
    addOneConstraint(0);
}
function addColumns(selection){
    return;
}
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
	if (jQuery.inArray("duration",curCons)==-1 || jQuery.inArray("duration",curCons)>=id){
	    newString+="<OPTION VALUE='duration'>Duration</OPTION>";
	}
	if (jQuery.inArray("building",curCons)==-1 || jQuery.inArray("building",curCons)>=id){
	    newString+="<OPTION VALUE='building'>Building</OPTION>";
	}
	if (jQuery.inArray("fuelType",curCons)==-1 || jQuery.inArray("fuelType",curCons)>=id){
	    newString+="<OPTION VALUE='fuelType'>Fuel Type</OPTION>";
	}	
	newString+="</select><div id='moreOptionDiv"+id+"'></div>"
	//alert(newString);
	device.innerHTML = newString;
    }
}
function optionHandler(selection,id){
    var curDiv = document.getElementById("moreOptionDiv"+id);
    curCons[id] = selection.value;
    addOneConstraint(id+1);
    curDiv.innerHTML="<div id='moreOption"+id+"'>";
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
    }
    else if (selection.value.toString() == "fiscalYear"){
	option = "Year: <select id='fiscalYear'>";
	for (var i=2012;i>=1990;i--){
	    option+="<OPTION value='"+i+"'>"+i+"</OPTION>";
	}
	option+="</select>";
    }
    else if (selection.value.toString() == "duration"){
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
		curDiv.innerHTML="<div id='moreOption"+id+"'>"+option+"</div>";
	
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
		curDiv.innerHTML="<div id='moreOption"+id+"'>"+option+"</div>";
	
		//option +="</select><br/>";
	    }
	}
	xmlhttp.send(null);
    }

    curDiv.innerHTML += option+"</div>";
    
    //alert(curDiv.innerHTML);
}

var curCons;
window.onload = function(){
    
    addConstraints(document.getElementById("constraints"));
   
}