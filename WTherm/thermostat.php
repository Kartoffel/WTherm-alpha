<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

include('httpful.phar');
include('config.php');
include('db.php');

$heating;

exec("gpio mode ".$CONFIG['sense_pin']." in"); //set pin as input
exec("gpio mode ".$CONFIG['heating_pin']." out"); //set pin as output

exec("gpio read ".$CONFIG['sense_pin'], $output); //read state of other thermostat
$sense = $output[0]; // 1 if the other thermostat wants to switch on the heater

$sql = "SELECT * FROM status"; // Fetch the settings
$stmt = $db->prepare($sql);
$stmt->execute();
$status = $stmt->fetch();

$targettemp = $status['TARGET_TEMP'];
$override = $status['OVERRIDE'];
$last_update = $status['LAST_UPDATE'];

list($temp, $humidity) = HW_sense(); //Fetch the temperature and humidity from the HomeWizard

if(strtotime($status['LAST_UPDATE']) < strtotime("-".$CONFIG['time_unreliable']." minutes") && $temp == "fail"){ //Check if the temperature is unreliable
	$override = 0; //Disable the override
	$sql = "UPDATE status SET OVERRIDE=0;";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	push_notification($CONFIG['errormessage'], 1); //Send out a push notification
	error($CONFIG['errormessage']); //Log an error
}

if($override){
	if($targettemp - $temp >= $CONFIG['temp_offset']) heat(true); //switch on heater
	else heat(false); //switch off heater
}else{
	heat($sense); //control heater
}

if($temp != "fail" && !isset($argv[1])){ // If the WTherm was able to fetch the temperature and the script hasn't been passed any variables, log the values
	$sql = "INSERT INTO log (temp, target_temp, humidity, heating, override) VALUES (:temp, :targettemp, :humidity, :heating, :override)";
	$stmt = $db->prepare($sql);
	$stmt->execute(array(
		":temp" => $temp,
		":targettemp" => $targettemp,
		":humidity" => $humidity,
		":heating" => ($heating ? 1 : 0),
		":override" => ($override ? 1: 0),
	));
	if (!$stmt) {
		error("\nPDO::errorInfo():\n");
		error($db->errorInfo());
	}
}else if($temp == "fail"){
	error("WTherm was unable to fetch the temperature!");
}

$sql = "UPDATE status SET TEMP=:temp, TARGET_TEMP=:targettemp, HUMIDITY=:humidity, HEATING=:heating, OVERRIDE=:override, LAST_UPDATE=:date;"; // Update the settings
$stmt = $db->prepare($sql);
$stmt->execute(array(
	":temp" => $temp,
	":targettemp" => $targettemp,
	":humidity" => $humidity,
	":heating" => ($heating ? 1 : 0),
	":override" => ($override ? 1: 0),
	":date" => date('Y-m-d H:i:s'),
));
if (!$stmt) {
	error("\nPDO::errorInfo():\n");
    error($db->errorInfo());
}


function heat($onoff){
	global $heating, $CONFIG;
	
	if($onoff){
		exec("gpio write ".$CONFIG['heating_pin']." 0"); //heater on
		$heating = true;
	}else{
		exec("gpio write ".$CONFIG['heating_pin']." 1"); //heater off
		$heating = false;
	}
}

function push_notification($message, $priority){ //priority: send as -2 to generate no notification/alert, -1 to always send as a quiet notification, 1 to display as high-priority and bypass the user's quiet hours, or 2 to also require confirmation from the user
	global $CONFIG;
	if(!is_null($CONFIG['pushover_APItoken']) && !is_null($CONFIG['pushover_userkey'])){
		curl_setopt_array($ch = curl_init(), array(
		CURLOPT_URL => "https://api.pushover.net/1/messages.json",
		CURLOPT_POSTFIELDS => array(
			"token" => $CONFIG['pushover_APItoken'],
			"user" => $CONFIG['pushover_userkey'],
			"message" => $message,
			"priority" => $priority,
		)));
		curl_exec($ch);
		curl_close($ch);
	}
}

function HW_sense(){ //HomeWizard sensor values
	global $CONFIG;
	
	$uri = $CONFIG['hw_ip']."/".$CONFIG['hw_pw']."/telist";
	try {
		$response = \Httpful\Request::get($uri)
			->timeout(5)
			->send();
	}catch (Exception $e) {
		//echo 'Caught exception: '.$e->getMessage();
		return array("fail", "fail");
	}
	if($response->code == 200){
		$body = $response->raw_body;
		$data = json_decode($body, true);
		$temp = $data['response'][$CONFIG['hw_sid']]['te'];
		$humidity = $data['response'][$CONFIG['hw_sid']]['hu'];
		if(!($temp = floatval($temp))) $temp = "fail"; //check if the temperature is valid, not 'null' or something else
		if(!($humidity = intval($humidity))) $humidity = "fail";
		return array($temp, $humidity);
	}else{
		return array("fail", "fail");
	}
}

function error($errormsg){ // Output an error message with timestamp
	if(!is_string($errormsg)) $errormsg = serialize($errormsg);
	echo "[".date("Y-m-d H:i:s")."] ".$errormsg."\r\n";
}

$db = null; // Disconnect the database
?>
