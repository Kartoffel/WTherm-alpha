/** control.js
 * WTherm web-connected thermostat https://github.com/NiekProductions/WTherm/
 * Author: Niek Blankers <niek@niekproductions.com>
 *
 * This script is used to communicate with the WTherm database, through data.php
 */

var updateTemp;
var updateInterval = 60; //update interval in seconds

var update=setInterval(function(){updateall()}, updateInterval*1000);
updateall();
document.getElementById('uptime').innerHTML = 'Uptime: ' + HTTPrequest('UPTIME');
document.getElementById('targettemp').innerHTML = HTTPrequest('TARGETTEMP');
if(!isNaN(parseFloat(document.getElementById('targettemp').innerHTML))){document.getElementById('t_temp_li').style.backgroundColor = "white"; }

function updateall(){
	heat_status('heat_status');
	document.getElementById('curtemp').innerHTML = HTTPrequest('CURTEMP');
	override_status('tmpoverride');
}

function incrtemp(id){
	var element = document.getElementById(id);
	var temp = parseFloat(element.innerHTML);
	if(isNaN(temp)){
		document.getElementById('t_temp_li').style.backgroundColor = "lightgray";
	}else{
		document.getElementById('t_temp_li').style.backgroundColor = "white";
		temp += 0.5;
		element.innerHTML = temp;
		clearTimeout(updateTemp);
		updateTemp = setTimeout(function(){ HTTPrequest('TEMP&value=' + temp); updateall()},2000); //set temperature
	}
}

function decrtemp(id){
	var element = document.getElementById(id);
	var temp = parseFloat(element.innerHTML);
	if(isNaN(temp)){
		document.getElementById('t_temp_li').style.backgroundColor = "lightgray";
	}else{
		document.getElementById('t_temp_li').style.backgroundColor = "white";
		temp -= 0.5;
		element.innerHTML = temp;
		clearTimeout(updateTemp);
		updateTemp = setTimeout(function(){ HTTPrequest('TEMP&value=' + temp); updateall()},2000); //set temperature
	}
}

function override_status(id){
	var element = document.getElementById(id);
	var status = HTTPrequest('OVERRIDE-STATUS');
	if(status == '1'){
		element.style.color = "rgb(60, 118, 61)";
		element.innerHTML = "Enabled";
	}else if(status == '0'){
		element.style.color = "rgb(169, 68, 66)";
		element.innerHTML = "Disabled";	
	}
}

function override_toggle(id){
	var element = document.getElementById(id);
	if(element.style.color == "rgb(60, 118, 61)"){
		element.style.color = "rgb(169, 68, 66)";
		HTTPrequest('DIS-OVERRIDE');
	}else if(element.style.color == "rgb(169, 68, 66)"){
		element.style.color = "rgb(60, 118, 61)";
		HTTPrequest('ENA-OVERRIDE');		
	}
	updateall();
}

function heat_status(id){
	var element = document.getElementById(id);
	var status = HTTPrequest('HEATING-STATUS');
	if(status == '1'){
		element.style.color = "#a94442";
		element.innerHTML = "Heating"
	}else if(status == '0'){
		element.style.color = "#31708f";
		element.innerHTML = "Cooling"	
	}
}

function error(msg){
	var errorEl = document.getElementById('error');
	var errormsgEl = document.getElementById('errormsg');
	errormsgEl.innerHTML = msg;
	errorEl.style.display = 'block';
}

function HTTPrequest(func){
	var request = new XMLHttpRequest();  
	request.open('GET', 'data.php?func=' + func, false); 
	request.send(null);  
	
	if (request.status === 200) {  
	  var response = request.responseText;
	  if(response == 'LOGIN'){
		window.location.replace('login.php');
		return false;
	  }else if(response == 'FAIL'){
		error('An error was encountered while processing the request');
		return false;
	  }
	  return response;  
	}else{
	  error('WTherm is unreachable');
	  return false;
	}
}