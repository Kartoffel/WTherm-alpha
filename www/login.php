<?php
session_start();
include('/usr/local/bin/WTherm/config.php');

if( !isset( $_SESSION['username'] )){
	if( isset( $_POST['username'], $_POST['password'] ) ){
		include('/usr/local/bin/WTherm/db.php'); // Connect to the database
		$user = strtolower($_POST['username']);
		$pass = $_POST['password'];
		$sql = "SELECT * FROM users WHERE username=:username";
		$stmt = $db->prepare($sql);
		$stmt->execute(array(
			":username" => $user
		));
		$row = $stmt->fetch();
		if(!$row){
			$error = true; // Incorrect login
		}else{
			if(verify($pass, $row['password'])){	
				$_SESSION['username'] = $user; // Successfully logged in
				header("Location:index.php");
				exit;
			}else{
				$error = true; // Incorrect login
			}
		}
	$db = null;
	}
}else{
	//already logged in
	header("Location:index.php");
	exit;
}

function verify($password, $hashedPassword) {
    return crypt($password, $hashedPassword) == $hashedPassword;
}
?>

<!DOCTYPE HTML>
<html lang="en">
<head>
<title>Login - WTherm</title>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no">
<meta name="description" content="Web controlled thermostat">
<meta name="author" content="NiekProductions">
<link href="css/bootstrap.min.css" rel="stylesheet">
<link href="css/style.css" rel="stylesheet">
<link rel="icon" sizes="196x196" href="WTherm-icon-196.png">
<link rel="apple-touch-icon" sizes="196x196" href="WTherm-icon-196.png">
<link rel="shortcut icon" type="image/x-icon" href="WTherm-icon.ico">
</head>
<body>
<div class="container">
      <div class="header">
        <h3 class="text-muted">WTherm</h3>
      </div>
	  
	  <?php
	  if(isset($error))
	  echo('
	  <div id="error" style="display: block;" class="alert alert-danger">
		  <h3>Incorrect username/password combination!</h3>
		</div>
		');
	  ?>
	  
      <div class="row control">
        <div class="col-lg-12 unselectable">
			<form role="form" name="login-form" action="login.php" method="POST">
				<h2>Login</h2>
				<div class="form-group">
					<input type="text" class="form-control input-lg" name="username" placeholder="Username">
				</div>
				<div class="form-group">
					<div class="input-group input-group-lg">
						<input type="password" class="form-control" name="password" placeholder="Password">
						<span class="input-group-btn">
							<button class="btn btn-default" type="submit">Submit</button>
						</span>
					</div>
				</div>
			</form>
        </div>
      </div>

      <div class="footer">
        <p style="width:100%;"><span class="text-left">&copy; <a href="http://niekproductions.com/">NiekProductions</a> <?php echo date("Y"); ?></span></p>	
      </div>

    </div> <!-- /container -->
</body>
</html>