<?php
include('db.php');

if(!isset($argv[1]) || !isset($argv[2])){
	exit("usage: adduser.php user pass \n");
}

$sql = "SELECT * FROM users WHERE username=:username";
$stmt = $db->prepare($sql);
$stmt->execute(array(
	":username" => strtolower($argv[1])
));
$user = $stmt->fetch();
if($user){
	echo "User already exists!\n";
	$db = null;
	exit;
}

$sql = "INSERT INTO users (`username`, `password`) VALUES (:username, :password)";
$stmt = $db->prepare($sql);
$stmt->execute(array(
	":username" => strtolower($argv[1]),
    ":password" => generateHash($argv[2])
));
if (!$stmt) {
    echo "\nPDO::errorInfo():\n";
    print_r($dbh->errorInfo());
	echo "\n";
}else{
	echo "User added succesfully!\n";
}

function verify($password, $hashedPassword) {
    return crypt($password, $hashedPassword) == $hashedPassword;
}

function generateHash($password) {
    if (defined("CRYPT_BLOWFISH") && CRYPT_BLOWFISH) {
        $salt = '$2y$11$' . substr(md5(uniqid(rand(), true)), 0, 22);
        return crypt($password, $salt);
    }else{
		exit("CRYPT_BLOWFISH unsupported!");
	}
}
$db = null;
?>
