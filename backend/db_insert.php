<?php

// Since this runs from cron, we want it to log everything
error_reporting(E_ALL);

// Require and instantiate my Indego class to get all stations
require_once('Indego.class.php');
$indego = new Indego;
$all_stations = $indego->getStations();

// Connect to MySQL
require_once('sql.php');
try {
	$dbh = new PDO("mysql:host=$sql_hostname;dbname=$sql_database", $sql_username, $sql_password);
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
	die($e->getMessage());
}

// Loop through each station
foreach ($all_stations as $station) {

	// Print/log the station ID and name
	echo $station->kioskId . ' / ' . $station->name . "\n";

	// Insert the stations basic information to the station table and update it if necessary
	try {
		$insert_station = $dbh->prepare("INSERT INTO `stations` (`kioskId`, `name`, `addressStreet`, `addressCity`, `addressState`, `addressZipCode`, `added`)
		VALUES (:kioskId, :name, :addressStreet, :addressCity, :addressState, :addressZipCode, NOW())
		ON DUPLICATE KEY UPDATE `name` = :name, `addressStreet` = :addressStreet, `addressCity` = :addressCity, `addressState` = :addressState, `addressZipCode` = :addressZipCode");

		$insert_station->bindValue(':kioskId', $station->kioskId, PDO::PARAM_INT);
		$insert_station->bindValue(':name', trim($station->name), PDO::PARAM_STR);
		$insert_station->bindValue(':addressStreet', trim($station->addressStreet), PDO::PARAM_STR);
		$insert_station->bindValue(':addressCity', trim($station->addressCity), PDO::PARAM_STR);
		$insert_station->bindValue(':addressState', trim($station->addressState), PDO::PARAM_STR);
		$insert_station->bindValue(':addressZipCode', trim($station->addressZipCode), PDO::PARAM_STR);

		echo 'Station: ';
		if ($insert_station->execute()) {
			echo 'OK';
		} else {
			echo 'ERROR!';
		}
		echo "\n";

	// Bail on MySQL errors
	} catch (PDOException $e) {
		die($e->getMessage());
	}

	// Insert the stations bike availability information
	try {

		$insert_data = $dbh->prepare("INSERT INTO `data` (`kioskId`, `kioskPublicStatus`, `bikesAvailable`, `docksAvailable`, `totalDocks`, `added`)
		VALUES (:kioskId, :kioskPublicStatus, :bikesAvailable, :docksAvailable, :totalDocks, NOW())");

		$insert_data->bindValue(':kioskId', $station->kioskId, PDO::PARAM_INT);
		$insert_data->bindValue(':kioskPublicStatus', $station->kioskPublicStatus, PDO::PARAM_STR);
		$insert_data->bindValue(':bikesAvailable', $station->bikesAvailable, PDO::PARAM_INT);
		$insert_data->bindValue(':docksAvailable', $station->docksAvailable, PDO::PARAM_INT);
		$insert_data->bindValue(':totalDocks', $station->totalDocks, PDO::PARAM_INT);

		echo 'Data: ';
		if ($insert_data->execute()) {
			echo 'OK';
		} else {
			echo 'ERROR!';
		}
		echo "\n";

	} catch (PDOException $e) {
		die($e->getMessage());
	}
}

// Disconnect from MySQL
$dbh = null;
