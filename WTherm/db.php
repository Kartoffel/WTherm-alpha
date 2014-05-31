<?php
include('config.php');

try {
    $db = new PDO('mysql:host='.$CONFIG['db_server'].';dbname='.$CONFIG['db_name'].'', $CONFIG['db_user'], $CONFIG['db_pass']);
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
}
?>