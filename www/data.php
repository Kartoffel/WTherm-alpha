<?php 
/** data.php
 * WTherm web-connected thermostat https://github.com/NiekProductions/WTherm/
 * Author: Niek Blankers <niek@niekproductions.com>
 *
 * This file handles javascript communication to the database.
 * It outputs responses to the queries.
 * If the user is not logged in, output "LOGIN"
 * If something goes wrong, output "FAIL"
 */
 
include('/usr/local/bin/WTherm/db.php'); // Connect to the database
include('/usr/local/bin/WTherm/config.php');

// Prevent browser caching
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

session_start();

if( !isset( $_SESSION['username'] )){ // Is the user logged in?
	exit("LOGIN");
}

if(isset($_GET['func'])){
	$sql = "SELECT * FROM status;";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$status = $stmt->fetch();
	
	switch ($_GET['func']) { // Handle the requests
		case 'UPTIME': // Requests the uptime
			$tmp = explode(' ', file_get_contents('/proc/uptime'));
			exit(secondsToTime(floor($tmp[0])));
			break;
		case 'OVERRIDE-STATUS': // Requests the override state
			exit($status['OVERRIDE']);
			break;
		case 'HEATING-STATUS': // Requests the state of the central heating unit
			exit($status['HEATING']);
			break;
		case 'CURTEMP': // Requests the current temperature
			exit($status['TEMP']);
			break;
		case 'TARGETTEMP': // Requests the target temperature
			exit($status['TARGET_TEMP']);
			break;
		case 'ENA-OVERRIDE': // Enables the override
			$sql = "UPDATE status SET OVERRIDE=1;";
			$stmt = $db->prepare($sql);
			$stmt->execute();
			update();
			exit("OK");
			break;
		case 'DIS-OVERRIDE': // Disables the override
			$sql = "UPDATE status SET OVERRIDE=0;";
			$stmt = $db->prepare($sql);
			$stmt->execute();
			update();
			exit("OK");
			break;
		case 'TEMP': // Sets the target temp
			if(isset($_GET['value']) && floatval($_GET['value'])){
				$temp = floatval($_GET['value']);
				if($temp >= $CONFIG['min_temp'] && $temp <= $CONFIG['max_temp']){
					$sql = "UPDATE status SET TARGET_TEMP=:targettemp;";
					$stmt = $db->prepare($sql);
					$stmt->execute(array(
						":targettemp" => $temp,
					));
					update();
					exit("OK");
				}else{
					exit("FAIL");
				}
			}else{
				exit("FAIL");
			}
			break;
		default:
			exit("FAIL");
			break;
	}

}else{
	exit("FAIL");
}

/**
 * Convert uptime in seconds to a readable string
 *
 * @param  integer  $seconds  Uptime (in seconds)
 * @return String   Readable uptime
 */ 
function secondsToTime($seconds){
    $dtF = new DateTime("@0");
    $dtT = new DateTime("@$seconds");
    return $dtF->diff($dtT)->format('%ad %hh %im %ss');
}

/**
 * Run the thermostat script
 * Passes an $argv variable to prevent logging
 */
function update(){
	exec('sudo php5 /usr/local/bin/WTherm/thermostat.php noupdate'); //execute thermostat script, but don't log the temperature
}

$db = null;
?>
