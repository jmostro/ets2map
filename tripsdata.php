<?php
session_start();
require_once('inc/model.php');
require_once('inc/layout.php');
require_once('inc/functions.php');
require_once('inc/config.php');

if (!isLoggedIn()){gotoUrl("/",MSG_TYPE_ERROR,"Sólo para conductores");die;}

switch (filter_input(INPUT_POST, 'a', FILTER_SANITIZE_STRING)) {
	case 'view':
		viewtrips(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_STRING));
		break;
	case 'delete':
		deleteUserTrip(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_STRING));
		break;
    case 'deleteadmin':
        deleteUserTripAdmin(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_STRING), filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING));
        break;
    case 'inranking':
        changeInRanking(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_STRING), filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING));
        break;
}

function viewtrips($uid = 0){
	$jsondataList = array();
	$myquery = "SELECT 
			trips.id AS id,
			trips.uid AS uid,
			trips.org_city AS org_city,
			trips.des_city AS des_city,
			trips.trailer AS trailer,
			trips.mass AS mass,
			trips.income as income,
			trips.distance AS distance,
			trips.driven AS driven, 
			trips.start AS start,
			trips.finish AS finish,
			trips.delivered AS delivered,
			trips.deleted as deleted, 
			trips.game as game, 
			drivers.displayname AS drivername
			 FROM trips INNER JOIN drivers ON trips.uid = drivers.id
			 WHERE trips.delivered = 1";
		if ($uid) $myquery .=" AND trips.uid=$uid";
		$myquery .=" ORDER BY trips.finish DESC";
	$resultado = dbSelect($myquery);
	while($fila = mysqli_fetch_assoc($resultado))
	{
		$jsondataperson = array(
			"<a class='table-a' href='".SITE_URL."/users/view/info/".$fila["uid"]."' target='_blank'>".$fila["drivername"]."</a>",
			($fila["game"]=="ats"?"American Truck Simulator":"Euro Truck Simulator 2"),
			"<a class='table-a' href='".SITE_URL."/trips/view/".$fila["id"]."' target='_blank'>".$fila["org_city"]." a ". $fila["des_city"]."</a>",
			date("H:i:s d/m/Y", strtotime($fila["start"])),
			$fila["trailer"],
			($fila["mass"]/1000)." ton",
			$fila["driven"]." km",
			gmdate("H\h i\m\i\\n",strToTime($fila['finish']) - strToTime($fila['start'])));
		$jsondataList[]=$jsondataperson;
	}
	$jsondata["data"] = array_values($jsondataList);
	header("Content-type:application/json; charset = utf-8");
	echo json_encode($jsondata);
}

function deleteUserTrip($id){
	$res = array();
	if ($trip = getTripInfo($id)) {
		if ($pwr = canEdit($trip['uid'])){
			if (deleteTrip($id)) {
				if ($pwr == 1) {
					logEvent(LOG_TYPE_EVENT,"Eliminó el viaje con ID $id");
        		} else {
		      		logEvent(LOG_TYPE_ADMIN,"Eliminó el viaje con ID $id");
	       		}
				$res['successful'] = true;
				$res['message'] = "Viaje eliminado";
			}
		}
	} else {
		$res['successful'] = false;
		$res['message'] = "El viaje no existe";
	}
	echo json_encode($res);
}

function deleteUserTripAdmin($id, $status) {
	$res = array();
	if(!isAdmin()){
		$res['successful'] = false;
		$res['message'] = "No tienes los permisos necesarios";
	}else{
		switch ($status){
			case 0:		// Elimino viaje
				if (getTripInfo($id)) {
					if (deleteTrip($id)) {
			      		logEvent(LOG_TYPE_ADMIN,"Eliminó el viaje con ID $id");
						$res['successful'] = true;
						$res['message'] = "Viaje eliminado";
						$res['deleted'] = 1;
					}
				} else {
					$res['successful'] = false;
					$res['message'] = "El viaje no existe";
					$res['deleted'] = 0;
				}
				break;
			case 1:		// Recupero viaje
				if (getTripInfo($id)) {
					if (recoverTrip($id)) {
			      		logEvent(LOG_TYPE_ADMIN,"Restableció el viaje con ID $id");
						$res['successful'] = true;
						$res['message'] = "Viaje recuperado";
						$res['deleted'] = 2;
					}	
				} else {
					$res['successful'] = false;
					$res['message'] = "El viaje no existe";
					$res['deleted'] = 0;
				}
				break;
			default:
				$res['successful'] = false;
				$res['message'] = "Algo no salió bien";
				$res['deleted'] = 0;
		}
		echo json_encode($res);
	}
}

function changeInRanking($id, $status) {
	$res = array();
	if(!isAdmin()){
		$res['successful'] = false;
		$res['message'] = "No tienes los permisos necesarios";
	}else{
		switch ($status){
			case 0:		// Elimino del ranking
				if (getTripInfo($id)) {
					if (modifyRanking($id, 1)) {
			      		logEvent(LOG_TYPE_ADMIN,"El viaje con ID $id se muestra nuevamente en el ranking");
						$res['successful'] = true;
						$res['message'] = "Se muestra nuevamente el viaje en el ranking";
						$res['ranking'] = 1;
					}	
				} else {
					$res['successful'] = false;
					$res['message'] = "El viaje no existe";
					$res['ranking'] = 0;
				}
				break;
			case 1:		// Recupero al ranking
				if (getTripInfo($id)) {
					if (modifyRanking($id, 0)) {
			      		logEvent(LOG_TYPE_ADMIN,"El viaje con ID $id no se muestra en el ranking");
						$res['successful'] = true;
						$res['message'] = "Se eliminó el viaje del ranking";
						$res['ranking'] = 2;
					}
				} else {
					$res['successful'] = false;
					$res['message'] = "El viaje no existe";
					$res['ranking'] = 0;
				}
				break;
			default:
				$res['successful'] = false;
				$res['message'] = "Algo no salió bien";
				$res['ranking'] = 0;
		}
		echo json_encode($res);
	}
}
?>