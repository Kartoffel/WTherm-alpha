<?php
session_start();

if( !isset( $_SESSION['username'] )){ // Is the user logged in?
	header("Location:login.php");
	exit;
}
?>
<!--
	This is the dashboard page for the WTherm web-connected thermostat https://github.com/NiekProductions/WTherm/
	Author: Niek Blankers <niek@niekproductions.com>
-->
<!DOCTYPE HTML>
<html lang="en">
<head>
<title>WTherm</title>
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
        <ul class="nav nav-pills pull-right">
			<li class="active"><a href="#">Home</a></li>
			<li><a href="stats.php">Stats</a></li>
			<li><a href="logout.php">Log out</a></li>
        </ul>
        <h3 class="text-muted">WTherm</h3>
      </div>
	  
	  <noscript>Please enable javascript!</noscript>
	  
	  <div id="error" style="display: none;" class="alert alert-danger alert-dismissable">
		  <button type="button" onclick="document.getElementById('error').style.display='none';" class="close" aria-hidden="true">&times;</button>
		  <strong>Error!</strong> <span id="errormsg">Unknown.</span>
		</div>	

      <div class="row control">
        <div class="col-lg-6 col-sm-6 col-md-6 unselectable">
			<h2>Current temperature</h2>
			<ul class="list-group">
				<li class="list-group-item text-center"><h3><span id="curtemp">17.5</span> &deg;C</h3></li>
				<li class="list-group-item text-center"><h3 id="heat_status" style="color: red;">Unknown</h3></li>
				<li class="list-group-item text-center mouseover" onclick="override_toggle('tmpoverride');"><h3 id="tmpoverride" style="color: black;">Override</h3></li>
			</ul>
        </div>
        <div class="col-lg-6 col-sm-6 col-md-6 unselectable">
          <h2>Target temperature</h2>
		<ul class="list-group">
			<form>
			<li class="list-group-item mouseover text-center" onclick="incrtemp('targettemp');"><h3>+</h3></li>
			<li id="t_temp_li" style="background-color: lightgray;" class="list-group-item text-center"><h3><span id="targettemp">UNKNOWN</span> &deg;C</h3></li>
			<li class="list-group-item mouseover text-center" onclick="decrtemp('targettemp');"><h3>-</h3></li>
			</form>
		</ul>
        </div>
      </div>

      <div class="footer">
        <p style="width:100%;"><span class="text-left">&copy; <a href="http://niekproductions.com/">NiekProductions</a> <?php echo date("Y"); ?></span><span class="pull-right" id="uptime">Uptime: Unknown</span></p>	
      </div>

    </div> <!-- /container -->

<script src="js/control.js"></script>
</body>
</html>