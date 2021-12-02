<?php
session_start();
require_once("inc/model.php");
require_once("inc/functions.php");
require_once("inc/config.php");
$action = filter_input(INPUT_GET, "a", FILTER_SANITIZE_STRING);
if (!isLoggedIn()){
	die;
}
switch ($action) {
	case 'getpos':
		if ($truckId = filter_input (INPUT_GET,"id",FILTER_SANITIZE_NUMBER_INT))
			echo json_encode(getTruckPos($truckId));
		break;
	case 'getalive':
		/*If (!IS_IN_LOCAL_SERVER && FIND_DRIVERS_ETS2MAP)
			mainServerUpdate();*/
		echo json_encode(getAliveTrucks());
		//echo json_encode($trucks,JSON_UNESCAPED_UNICODE); // PHP 5.4+
		break;
	case 'listdrivers':
		echo json_encode(listDrivers());
		break;
	case 'translate':
		$x = filter_input(INPUT_GET, 'x', FILTER_SANITIZE_NUMBER_INT);
		$y = filter_input(INPUT_GET, 'y', FILTER_SANITIZE_NUMBER_INT);
		$pos['x'] = $x;
		$pos['z'] = $y;
		echo json_encode(translateTruckCoordinates($pos));
		break;
	case 'driveroptions':
		echo json_encode(getDriverOptions($_SESSION['driverid']));
		break;
	case 'lasttentrips':
		if ($truckId = filter_input (INPUT_GET,"id",FILTER_SANITIZE_NUMBER_INT)){
			$lasttentrips = getUserTrips($truckId,0,10);
		    while ($row = mysqli_fetch_assoc($lasttentrips)){
		        $data[] = $row;
		    }
		    if(!empty($data)){
			    foreach($data as $valor){
			    	$arrLabels[] = date("d/m/y", strtotime($valor['finish']));
			    	$arrDistance[] = $valor['distance']/1000;
			    	$arrIncome[] = $valor['income'];
			    }
			    // Invierto el orden
			    $arrLabels = array_reverse($arrLabels);
			    $arrDistance = array_reverse($arrDistance);
			    $arrIncome = array_reverse($arrIncome);
			    // Preparo la salida
			    $arrDistance = array('label' => "Kilómetros recorridos (km)",'fillColor' => "rgba(251,120,14,0.2)", 'strokeColor' => "rgba(251,120,14,0)", 'pointColor' => "rgba(251,120,14,1)", 'pointStrokeColor' => "#fff", 'pointHighlightFill' => "#fff", 'pointHighlightStroke' => "rgba(220,220,220,1)", 'data' => $arrDistance);
			    $arrIncome = array('label' => "Ganancias obtenidas ($)",'fillColor' => "rgba(49,216,32,0.2)", 'strokeColor' => "rgba(49,216,32,0)", 'pointColor' => "rgba(49,216,32,1)", 'pointStrokeColor' => "#fff", 'pointHighlightFill' => "#fff", 'pointHighlightStroke' => "rgba(151,187,205,1)", 'data' => $arrIncome, 'y2axis' => true);
			    $data = array($arrDistance,$arrIncome);
		    	// Salida
		    	echo json_encode(array(array('labels' => $arrLabels, 'datasets' => $data)));
		    }
			}
		break;
	default:
		break;
}
?>