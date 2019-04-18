<?php

// Create a function to make pretty dock/bike graphs with or without emojis
function make_graph($bikes, $docks, $emoji = false) {

	// Make a pretty graph for bikes at the current station
	$graph = '<span class="bikes">';
	for ($bike = 0; $bike < $bikes; $bike++) {

		// If hitting the bike emoji URL, use bike emojis to represent bikes
		if ($emoji) {
			$graph .= '🚲 ';

		// Otherwise, use stylized blocks for bikes normally
		} else {
			$graph .= '█';
		}
	}
	$graph .= '</span>';

	// And another pretty graph for empty docks at the current station
	$graph .= '<span class="docks">';
	for ($dock = 0; $dock < $docks; $dock++) {

		// If hitting the bike emoji URL, use hyphens to represent empty docks
		if ($emoji) {
			$graph .= '-';

		// Otherwise, use stylized blocks for empty docks normally
		} else {
			$graph .= '█';
		}
	}
	$graph .= '</span>';

	// Return the graph
	return $graph;
}

?>
<!DOCTYPE html>
<html lang="en">
	<head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta name="author" content="Eric O'Callaghan">
        <meta name="description" content="Philadelphia Indego Bikes">
        <meta name="keywords" content="ericoc, indego, philadelphia, philly, bikes, bikeshare, bicycles">
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="Philadelphia Indego Bikes">
        <meta property="og:title" content="Indego Bicycle Stations">
        <meta property="og:description" content="A quick and easy way to see available bicycles and docks throughout all Philadlephia Indego bicycle-share stations.">
        <meta property="og:url" content="https://indego.ericoc.com/">
        <meta property="og:image" content="https://indego.ericoc.com/icon.png">
        <link href="/indego.css" rel="stylesheet" type="text/css">
        <link rel="shortcut icon" href="/icon.png">
        <link rel="apple-touch-icon" href="/icon.png">
        <title>Indego Bikes!</title>
	</head>
	<body>
		<h1><a href="https://indego.ericoc.com/">Indego Bikes</a> <a href="https://xn--h78h.ericoc.com/">🚲</a></h1><br>

		<form method="get">
			<input type="text" name="search"> <i>(i.e. "<a href="/?search=fairmount">fairmount</a>" or "<a href="/?search=19107">19107</a>")</i>
			<input type="submit" value="Search!">
		</form><br>

		<table>
			<tr class='header'>
				<td>Kiosk ID</td>
				<td>Name</td>
				<td>Bikes</td>
				<td></td>
				<td>Docks</td>
				<td>History</td>
			</tr>
<?php

// Use search term if one was given, otherwise blank returns all stations
if (isset($_GET['search'])) {
	$search = $_GET['search'];
} else {
	$search = '';
}

// Require and instantiate the PHP Indego class and get stations
require_once('backend/Indego.class.php');
$indego = new Indego;
$stations = $indego->getStations($search);

// Totals start at zero
$totalbikes = $totaldocks = $totalstations = 0;

// Determine whether the bike emoji URL is being hit with a case-insensitive match
// Use this later to decide whether to display bike/dock graphs with emojis or not
if ($_SERVER['HTTP_HOST'] == 'xn--h78h.ericoc.com') {
	$emoji = true;
} else {
	$emoji = false;
}

// Loop through each bike-share station
foreach ($stations as $station) {

	// Skip the station if its kiosk is not active?
	if ($station->kioskPublicStatus !== 'Active') {
		continue;
	}

	// Get the current stations address with zip code for hover-text
	$address = $station->addressStreet . ' (' . $station->addressZipCode . ')';

	// List the current stations information in a unique table row
	echo "			<tr id='$station->kioskId'>\n";
	echo "				<td><a href='#$station->kioskId'>$station->kioskId</a></td>\n";					// Anchor link to the station/kiosk IDs
	echo "				<td><span title='$address'>$station->name</span></td>\n";					// Hover text on the name shows address+zip code, but doesn't work on mobile :/
	echo "				<td>$station->bikesAvailable</td>\n";								// Number of bikes available at the station
	echo "				<td>" . make_graph($station->bikesAvailable, $station->docksAvailable, $emoji) . "</td>\n";	// Generate and show pretty graph of bikes vs. docks at the station with or without emojis
	echo "				<td>$station->docksAvailable</td>\n";								// Number of docks available at the station
	echo "				<td class='graphcol'><a href='#" . $station->kioskId . "' target='popup' onclick=\"window.open('/chart.php?kioskId=" . $station->kioskId . "','popup','width=800,height=500'); return false;\">📊</a></td>\n";	// Link to popup graph of bike availability for the station for the past 7 days
	echo "			</tr>\n";

	// Add the current stations counts to the totals
	$totalbikes += $station->bikesAvailable;
	$totaldocks += $station->docksAvailable;
	$totalstations++;
}

// Show a nice message if no active stations, instead of leaving empty table
if ($totalstations === 0) {
	echo "			<tr>\n";
	echo "				<td align='center' colspan='6'><i>No active stations found!</i><td>\n";
	echo "			</tr>\n";
}

// Show the total counts at the bottom of our table
echo "			<tr class='header'>\n";
echo "				<td>Totals</td>\n";
echo "				<td>$totalstations stations</td>\n";
echo "				<td>$totalbikes</td>\n";
echo "				<td></td>\n";
echo "				<td>$totaldocks</td>\n";
echo "				<td></td>\n";
echo "			</tr>\n";

// Yay - links below!
?>
		</table>
		<br>
		<pre>courtesy of <a href='https://www.rideindego.com/' target='_blank'>Indego</a></pre><br>
		<pre><a href='https://github.com/ericoc/indego.ericoc.com/' target='_blank'>view source @ github</a> | <a href='https://github.com/ericoc/indego-php-lib/' target='_blank'>my Indego PHP library</a></pre>
	</body>
</html>
