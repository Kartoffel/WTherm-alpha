<?php
/** db.php
 * WTherm web-connected thermostat https://github.com/NiekProductions/WTherm/
 * Author: Niek Blankers <niek@niekproductions.com>
 *
 * Connect to the MySQL database
 */
 
include('config.php');

try {
    $db = new PDO('mysql:host='.$CONFIG['db_server'].';dbname='.$CONFIG['db_name'].'', $CONFIG['db_user'], $CONFIG['db_pass']);
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
}
?>