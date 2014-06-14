<?php
/** deluser.php
 * WTherm web-connected thermostat https://github.com/NiekProductions/WTherm/
 * Author: Niek Blankers <niek@niekproductions.com>
 *
 * run 'deluser.php [user]' to delete a user from the database
 */
 
include('db.php');

if(!isset($argv[1])){
	exit("usage: deluser.php user\n");
}

$sql = "SELECT * FROM users WHERE username=:username";
$stmt = $db->prepare($sql);
$stmt->execute(array(
	":username" => strtolower($argv[1])
));
$user = $stmt->fetch();
if(!$user){
	echo "No such user!\n";
	$db = null;
	exit;
}

$sql = "DELETE FROM users WHERE username=:username";
$stmt = $db->prepare($sql);
$stmt->execute(array(
	":username" => strtolower($argv[1])
));
if (!$stmt) {
    echo "\nPDO::errorInfo():\n";
    print_r($dbh->errorInfo());
}else{
	echo "User deleted successfully!\n";
}

$db = null;
?>
