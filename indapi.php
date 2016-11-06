<?php

// The purpose of this script is to return JavaScript
header('Content-Type: text/javascript');

// Use the kioskId provided in the URL to find the station
if ( (isset($_GET['kioskId'])) && (is_numeric($_GET['kioskId'])) && (strlen($_GET['kioskId']) == 4) ) {
	require_once('backend/Indego.class.php');
	$indego = new Indego;
	$stations = $indego->getStations($_GET['kioskId']);
	if ( (isset($stations)) && (count($stations) == 1) ) {
		$kioskId = $_GET['kioskId'];
		$station = $stations[$kioskId];
	}
}

// Bail if we did not find the station
if (!isset($station)) {
	die ('var data = ["error","Invalid kioskId!"]');
}

// Include MySQL credentials and connect to the database
require_once('backend/sql.php');
try {
	$dbh = new PDO("mysql:host=$sql_hostname;dbname=$sql_database", $sql_username, $sql_password);
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
	die($e->getMessage());
}

// Get bike availability information for the past month for the station requested
$get_data = $dbh->prepare("SELECT UNIX_TIMESTAMP(`added`)*1000 AS `added`, `bikesAvailable` FROM `data` WHERE `kioskId` = :kioskId AND `added` > NOW() - INTERVAL 1 MONTH ORDER BY `added` ASC;");
$get_data->bindValue(':kioskId', $_GET['kioskId'], PDO::PARAM_INT);
$get_data->execute();
$results = json_encode($get_data->fetchAll(PDO::FETCH_NUM), JSON_NUMERIC_CHECK|JSON_PRETTY_PRINT);

// Close MySQL connection
$dbh = null;

?>
var data = (<?php echo $results; ?>);

$(function () {
	$('#bikesgraph').highcharts({
		chart: { zoomType: 'x' },
		title: { text: 'Bikes available at <?php echo addslashes($station->name); ?>' },
		subtitle: { text: document.ontouchstart === undefined ? 'Click and drag in the plot area to zoom in' : 'Pinch the chart to zoom in' },
		xAxis: { title: { text: 'when' }, type: 'datetime' },
		yAxis: { title: { text: 'bikes' }, max: <?php echo $station->totalDocks; ?> },
		legend: { enabled: true },
		series: [{ type: 'area', name: 'Bikes Available', data: data }]
	});
});

Highcharts.setOptions({
	global: { useUTC: false }
});
