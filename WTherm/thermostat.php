<?php
/** thermostat.php
 * WTherm web-connected thermostat https://github.com/NiekProductions/WTherm/
 * Author: Niek Blankers <niek@niekproductions.com>
 *
 * This file runs the thermostat. Modify the HW_sense() function to fetch your room temperature 
 * Modify heat() to suit your central heating unit.
 */

error_reporting(E_ALL);
ini_set("display_errors", 1);

include('httpful.phar'); // This code uses the HTTPful class (http://phphttpclient.com/) to fetch the current temperature and humidity from a HomeWizard (http://www.homewizard.nl/) home-automation box
include('config.php'); // Config.php contains all of the configuration parameters
include('db.php'); // Connect to the MySQL database

/**
 * Some initialization
 */
$heating;
exec("gpio mode ".$CONFIG['sense_pin']." in"); //set pin as input
exec("gpio mode ".$CONFIG['heating_pin']." out"); //set pin as output

exec("gpio read ".$CONFIG['sense_pin'], $output); //read state of other thermostat
$sense = $output[0]; // 1 if the other thermostat wants to switch on the heater


/**
 * Fetch the thermostat settings
 */
$sql = "SELECT * FROM status";
$stmt = $db->prepare($sql);
$stmt->execute();
$status = $stmt->fetch();
$targettemp = $status['TARGET_TEMP'];
$override = $status['OVERRIDE'];
$last_update = $status['LAST_UPDATE'];

list($temp, $humidity) = HW_sense($CONFIG['hw_sid']);
if($CONFIG['outside_temp']) list($outside_temp, $outside_humidity) = HW_sense($CONFIG['hw_s2id']); 

/**
 * Check the reliability of the last temperature reading
 * If the last successful temperature reading is over $CONFIG['time_unreliable'] minutes, disable the override, push and log an error.
 */
if(strtotime($status['LAST_UPDATE']) < strtotime("-".$CONFIG['time_unreliable']." minutes") && $temp == "fail"){
	$override = 0; //Disable the override
	$sql = "UPDATE status SET OVERRIDE=0;";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	push_notification($CONFIG['errormessage'], 1); //Send out a push notification
	error($CONFIG['errormessage']); //Log an error
}

/**
 * The actual thermostat
 */
if($override){ 
	if($targettemp > $CONFIG['max_temp'] || $targettemp < $CONFIG['min_temp']) $targettemp = 15.0;
	
	if($targettemp - $temp >= $CONFIG['temp_offset']) heat(true); //switch on heater
	else heat(false); //switch off heater
}else{
	heat($sense); //control heater
}

if($temp != "fail" && !isset($argv[1])){ // If the WTherm was able to fetch the temperature and the script hasn't been passed any variables, log the values
	$sql = "INSERT INTO log (temp, outside_temp, target_temp, humidity, heating, override) VALUES (:temp, :outside_temp, :targettemp, :humidity, :heating, :override)";
	$stmt = $db->prepare($sql);
	$stmt->execute(array(
		":temp" => $temp,
		":outside_temp" => ($CONFIG['outside_temp'] ? $outside_temp : 0.0),
		":targettemp" => $targettemp,
		":humidity" => $humidity,
		":heating" => ($heating ? 1 : 0),
		":override" => ($override ? 1: 0),
	));
	if (!$stmt) {
		error("PDO::errorInfo():");
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

/**
 * Controls heating
 *
 * @param  boolean  $onoff  true: switch on heating unit, false: switch it off.
 * @return boolean  true
 */ 
function heat($onoff){
	global $heating, $CONFIG;
	
	if($onoff){
		exec("gpio write ".$CONFIG['heating_pin']." 0"); //heater on
		$heating = true;
	}else{
		exec("gpio write ".$CONFIG['heating_pin']." 1"); //heater off
		$heating = false;
	}
	
	return true;
}

/**
 * Sends a push notification through the Pushover(https://pushover.net/) service if something goes wrong. 
 *
 * @param  String   $message  What to send
 * @param  integer  $priority send as -2 to generate no notification/alert, -1 to always send as a quiet notification, 1 to display as high-priority and bypass the user's quiet hours, or 2 to also require confirmation from the user
 * @return boolean  true if push message was sent successfully
 */ 
function push_notification($message, $priority){
	global $CONFIG;
	if(!is_null($CONFIG['pushover_APItoken']) && !is_null($CONFIG['pushover_userkey'])){
		try {
			curl_setopt_array($ch = curl_init(), array(
			CURLOPT_URL => "https://api.pushover.net/1/messages.json",
			CURLOPT_POSTFIELDS => array(
				"token" => $CONFIG['pushover_APItoken'],
				"user" => $CONFIG['pushover_userkey'],
				"message" => $message,
				"priority" => $priority,
			)));
			$result = curl_exec($ch);
			curl_close($ch);
			if (FALSE === $result)
				throw new Exception(curl_error($ch), curl_errno($ch));
		} catch(Exception $e) {
			error(sprintf(
				'Curl failed with error #%d: %s',
				$e->getCode(), $e->getMessage()));
			return false;
		}
		return true;
	}
}

/**
 * Fetches temperature and humidity from a HomeWizard (http://www.homewizard.nl/)
 *
 * @param  int  $sid   HomeWizard sensor ID
 * @return array($temp, $humidity) -- returns array("fail", "fail") if the temperature could not be fetched within the timeout period
 */ 
function HW_sense($sid){ //HomeWizard sensor values
        global $CONFIG;

        $uri = $CONFIG['hw_ip']."/".$CONFIG['hw_pw']."/telist";
        try {
                $response = \Httpful\Request::get($uri)
                        ->timeout(30)
                        ->send();
        }catch (Exception $e) {
                //echo 'Caught exception: '.$e->getMessage();
                return array("fail", "fail");
        }
        if($response->code == 200){
                $body = $response->raw_body;
                $data = json_decode($body, true);
                $temp = isset($data['response'][$sid]['te']) ? $data['response'][$sid]['te'] : "fail";
                $humidity = isset($data['response'][$sid]['hu']) ? $data['response'][$sid]['hu'] : "fail";
                if(!($temp = floatval($temp))) $temp = "fail"; //check if the temperature is valid, not 'null' or something else
                if(!($humidity = intval($humidity))) $humidity = "fail";
                return array($temp, $humidity);
        }else{
                return array("fail", "fail");
        }
}


/**
 * Output an error message with timestamp
 *
 * @param  String $errormsg  Error message to log
 */ 
function error($errormsg){
	if(!is_string($errormsg)) $errormsg = serialize($errormsg);
	echo "[".date("Y-m-d H:i:s")."] ".$errormsg."\r\n";
}

$db = null; // Disconnect the database
?>
