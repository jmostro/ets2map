<?php
if (SITE_DEBUG_ON==0) {
	require_once("inc/model.php");
	require_once("inc/functions.php");
	$result = dbSelect("SELECT id FROM drivers WHERE steam_id='".filter_input(INPUT_GET, 'id', FILTER_SANITIZE_STRING)."' LIMIT 1;"); // Obtengo el ID a partir del steam ID
 	if ($row = mysqli_fetch_array($result))
		$driverId = $row['id'];
	$json = file_get_contents('php://input');
	$gameData = json_decode($json);
	switch (filter_input(INPUT_GET,'a', FILTER_SANITIZE_STRING)) {
		case 'updateTruck':
			updateTruck($driverId, filter_input(INPUT_GET, 'sec', FILTER_SANITIZE_STRING), $gameData);
			break;
		default:
			break;
	}
}
?>
