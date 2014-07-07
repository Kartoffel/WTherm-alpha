<?php
/** archivelog.php
 * WTherm web-connected thermostat https://github.com/NiekProductions/WTherm/
 * Author: Niek Blankers <niek@niekproductions.com>
 *
 * Used to archive log values that are older than 1 day
 */
 
include('db.php'); // Connect to the database

$sql = "INSERT INTO archive (`time`, `min_temp`, `max_temp`, `outside_min_temp`, `outside_max_temp`, `min_humidity`, `max_humidity`) SELECT `time`, MIN(`temp`) as min_temp, MAX(`temp`) as max_temp, MIN(`outside_temp`) as outside_min_temp, MAX(`outside_temp`) as outside_max_temp, MIN(`humidity`) as min_humidity, MAX(`humidity`) as max_humidity FROM log WHERE `time` < DATE_SUB(NOW(), INTERVAL 1 DAY) GROUP BY DATE(`time`), HOUR(`time`) ORDER BY `time` ASC; DELETE FROM log WHERE `time` < DATE_SUB(NOW(), INTERVAL 1 DAY)";
$stmt = $db->prepare($sql);
$stmt->execute();
if (!$stmt) {
    echo "\nPDO::errorInfo():\n";
    print_r($dbh->errorInfo());
	echo "\n";
}

$db = null;
?>