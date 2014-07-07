<?php
/** config.php
 * WTherm web-connected thermostat https://github.com/NiekProductions/WTherm/
 * Author: Niek Blankers <niek@niekproductions.com>
 *
 * This file contains all of the settings.
 */

$CONFIG = array(
	"hw_ip" => "x.x.x.x", //Homewizard IP address
	"hw_pw" => "password", //Homewizard password
	"hw_sid" => 1, //Homewizard temperature sensor ID
	"outside_temp" => false, //Enable or disable the outside temperature sensor
	"hw_s2id" => 2, //Homewizard outside temperature sensor ID
	"db_server" => "localhost", // MySQL server
	"db_name" => "WTherm", // Database name
	"db_user" => "username", // Database username
	"db_pass" => "password", // Database password
	"temp_offset" => 0.5, // Stop heating when the temperature is within x degrees of the target
	"sense_pin" => 15, // GPIO pin for sensing other thermostat
	"heating_pin" => 5, // GPIO pin for relay
	"min_temp" => 10.0, // Minimum allowed target temperature
	"max_temp" => 35.0, // Maximum allowed target temperature
	"time_unreliable" => 30, // Number of minutes after which the temperature is considered unreliable
	"pushover_email" => "email@example.com",
	"pushover_userkey" => "paste user key", //PushOver.net user key, leave empty to disable push notifications
	"pushover_APItoken" => "paste API token", //PushOver.net API token, leave empty to disable push notifications
	"errormessage" => "WTherm was unable to receive the temperature, disabling override.", //Push notification error message
);

$CONFIG["errormessage"] = "WTherm was unable to receive the temperature for ".$CONFIG['time_unreliable']." minutes, disabling override."; //Push notification error message
?>