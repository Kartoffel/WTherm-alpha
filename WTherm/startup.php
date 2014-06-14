<?php
/** startup.php
 * WTherm web-connected thermostat https://github.com/NiekProductions/WTherm/
 * Author: Niek Blankers <niek@niekproductions.com>
 *
 * This script should run at startup, to disable the override in case of an unexpected reboot.
 */

include('db.php');
$sql = "UPDATE status SET OVERRIDE=0";
$stmt = $db->prepare($sql);
$stmt->execute();
?>