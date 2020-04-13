<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

//$startTime = microtime(true);
$dataRange=$_GET["dataRange"];

// Create connection
$conn=mysqli_connect("localhost", "database_reader", "PASSWORD", "weather_records");



// Check connection
if (!$conn) {
die( "Connection failed: " . mysqli_connect_error());
}

$queryFieldsInternal = array("GMT", "decidegreesInternal", "pressureInternal", "humidityInternal", "decidegreesExternal", "humidityExternal");
$queryFieldsExternal = array("GMT", "decidegreesInternal", "pressureInternal", "humidityInternal", "decidegreesExternal", "humidityExternal");
$queryFieldsExtremes = array("sampledDate","decidegreesInternalHigh","decidegreesInternalLow","pressureInternalHigh","pressureInternalLow","humidityInternalHigh","humidityInternalLow", "decidegreesExternalHigh", "decidegreesExternalLow", "humidityExternalHigh", "humidityExternalLow", "voltageExternal1");

// Get most recent data
if ($dataRange === "Current")
{
	$queryFieldsAll = array_merge($queryFieldsInternal,$queryFieldsExternal, $queryFieldsExtremes);
	$sqlQueries = array("SELECT ".implode(',',$queryFieldsInternal)." FROM sensor_data order by ID desc limit 1");
	$sqlQueries[] = "SELECT ".implode(',',$queryFieldsExternal)." FROM sensor_data WHERE GMT > (DATE_SUB(NOW(), INTERVAL 10 MINUTE) AND decidegreesExternal IS NOT NULL) order by ID desc limit 1";
	$sqlQueries[] ="SELECT ".implode(',',$queryFieldsExtremes)."  FROM dailyExtremes WHERE sampledDate = CURDATE()";
}

elseif ($dataRange === "Today")
{
	$queryFieldsAll = array_merge($queryFieldsInternal,$queryFieldsExternal);
	$sqlQueries=array("SELECT ".implode(',',$queryFieldsAll)." FROM sensor_data WHERE GMT > DATE_SUB(NOW(), INTERVAL 1 DAY)");
}

elseif ($dataRange === "Annual")
{
	$queryFieldsAll = $queryFieldsExtremes;
	$sqlQueries = array("SELECT ".implode(',',$queryFieldsAll)."  FROM dailyExtremes WHERE sampledDate > DATE_SUB(NOW(), INTERVAL 1 YEAR)");
}

foreach ($sqlQueries as $query)
{

	$result=mysqli_query($conn, $query);

	if (mysqli_num_rows($result)!=0)
	{

		$tmpArray =mysqli_fetch_all($result,MYSQLI_ASSOC);

		if (!isset($myArray))
		{
			$myArray= array();
		}

		foreach ($queryFieldsAll as $fieldName)
		{
			
			if (!empty(array_column($tmpArray,$fieldName)))
			{
				$myArray[$fieldName] = array_column($tmpArray,$fieldName);
			}
		}

	} else {

		$myArray = "noData";

	}
}

echo json_encode($myArray, JSON_NUMERIC_CHECK);
mysqli_close($conn);

?>
