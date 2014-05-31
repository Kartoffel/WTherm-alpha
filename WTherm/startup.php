<?php
include('db.php');
$sql = "UPDATE status SET OVERRIDE=0"; // Disable the override after a reboot
$stmt = $db->prepare($sql);
$stmt->execute();
?>