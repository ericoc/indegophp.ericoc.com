<?php

// Use the kioskId provided in the URL to try to find the station
if ( (!isset($_GET['kioskId'])) || (!is_numeric($_GET['kioskId'])) || (strlen($_GET['kioskId']) != 4) ) {
	die('Invalid kioskId!');
} else {
	$kioskId = $_GET['kioskId'];
}

?>
<!DOCTYPE HTML>
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>Indego Bike Share Availability</title>
		<script type="text/javascript" src="/js/jquery.min.js"></script>
		<script type="text/javascript" src="/js/highcharts.js"></script>
		<script type="text/javascript" src="/js/exporting.js"></script>
		<script type="text/javascript" src="/indapi.php?kioskId=<?php echo $kioskId; ?>"></script>
		<link href="/indego.css" rel="stylesheet" type="text/css">
	</head>
	<body>
		<div id="bikesgraph"></div>
	</body>
</html>
