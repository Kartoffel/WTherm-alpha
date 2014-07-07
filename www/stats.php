<?php
session_start();

if( !isset( $_SESSION['username'] )){ // Is the user logged in?
	header("Location:login.php");
	exit;
}
?>

<!DOCTYPE HTML>
<html lang="en">
<head>
<title>Stats - WTherm</title>
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
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">google.load('visualization', '1.0', {'packages':['corechart']});</script>
<script type="text/javascript" src="js/createchart<?=(isset($_GET['1w'])? "-archive": "")?><?=(isset($_GET['1y'])? "-archive-y": "")?>.js.php"></script>
</head>
<body>
<div class="container">
      <div class="header">
        <ul class="nav nav-pills pull-right">
			<li><a href="index.php">Home</a></li>
			<li class="active"><a href="#">Stats</a></li>
			<li><a href="logout.php">Log out</a></li>
        </ul>
        <h3 class="text-muted">WTherm</h3>
      </div>
	  
      <div class="row control unselectable">
        
			<h2>Graphs
				<div class="btn-group" style="margin: 9px 0;">
					<a href="stats.php" class="btn btn-default <?=((isset($_GET['1w']) || isset($_GET['1y']))? "": "active")?>">Last day</a>
					<a href="stats.php?1w=true" class="btn btn-default <?=(isset($_GET['1w'])? "active": "")?>">Last week</a>
					<a href="stats.php?1y=true" class="btn btn-default <?=(isset($_GET['1y'])? "active": "")?>">Last year</a>
				</div>
			</h2>
			<p>
				<div id="temps" style="width: 100%; height: auto;"></div>
			</p>
			<p>
				<div id="humid" style="width: 100%; height: auto;"></div>
			</p>
      </div>

      <div class="footer">
        <p style="width:100%;"><span class="text-left">&copy; <a href="http://niekproductions.com/">NiekProductions</a> <?php echo date("Y"); ?></span></p>	
      </div>

    </div> <!-- /container -->
</body>
</html>
