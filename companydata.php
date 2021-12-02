<?php
session_start();
require_once('inc/model.php');
require_once('inc/functions.php');
require_once('inc/config.php');
if (!isLoggedIn()){gotoUrl("/",MSG_TYPE_ERROR,"Solo para conductores");die;}

switch (filter_input(INPUT_GET, 'a', FILTER_SANITIZE_STRING)) {
	case 'drivers':
		getDriversList();
		break;
	case 'trips':
		getTripsData();
		break;
	default:
		break;
}

function getDriversList(){
	$jsondataList = array();
	global $user_rank_name;
	$myquery =  "SELECT 
	            drivers.id AS id, 
	            drivers.mp_id AS mp_id, 
	            drivers.username AS username, 
	            drivers.displayname AS name, 
	            drivers.fullname AS fullname, 
	            drivers.rank AS rank, 
	            drivers.last_seen AS last_seen, 
	            trucks.last_seen AS last_onroad 
	            FROM drivers 
	            INNER JOIN trucks ON drivers.id = trucks.owner ";
	if (!isAdmin())
       	$myquery .="WHERE rank > ".USER_RANK_RECRUIT;

	$resultado = dbSelect($myquery);
	while($fila = mysqli_fetch_assoc($resultado))
	{
		$jsondataperson = array(
			"<a class='table-a' href='".SITE_URL."/users/view/info/".$fila["id"]."' target='_blank'>".$fila["name"]."</a>",
			$fila["fullname"],
			$user_rank_name[$fila['rank']],
			((strtotime($fila['last_seen']) + TRUCK_ON_MAP_LIFETIME + SQL_PHP_TIME)>time()?"<i class='fa fa-laptop fa-fw' title='El usuario se encuentra en línea en el sitio'></i>":"")." ".((strtotime($fila['last_onroad']) + TRUCK_ON_MAP_LIFETIME + SQL_PHP_TIME)>time()?"<a href='".SITE_URL."/follow/".$fila['id']."'><i class='fa fa-road fa-fw' title='El conductor se encuentra en viaje en este momento'></i></a>":""),
			((strtotime($fila['last_seen']) + TRUCK_ON_MAP_LIFETIME + SQL_PHP_TIME)>time()?"En el sitio":"")." ".((strtotime($fila['last_onroad']) + TRUCK_ON_MAP_LIFETIME + SQL_PHP_TIME)>time()?"En ruta":""));
		$jsondataList[]=$jsondataperson;
	}
	$jsondata["data"] = array_values($jsondataList);
	header("Content-type:application/json; charset = utf-8");
	echo json_encode($jsondata);
}

function getTripsData(){
	$jsondataList = array();
	$myquery = "SELECT 
				trips.id AS id,
				trips.org_city AS org_city,
				trips.des_city AS des_city,
				trips.trailer AS trailer,
				trips.mass AS mass,
				trips.distance AS distance,
				trips.driven AS driven,
				trips.start AS start,
				trips.finish AS finish,
				trips.game as game, 
				drivers.id as uid,
				drivers.displayname AS drivername 
				FROM trips INNER JOIN drivers ON trips.uid = drivers.id 
				WHERE trips.delivered = 1 AND trips.deleted = 0 
				ORDER BY trips.finish DESC";
	$resultado = dbSelect($myquery);
	while($fila = mysqli_fetch_assoc($resultado))
	{
		$jsondataperson = array(
			"<a class='table-a' href='".SITE_URL."/trips/list/".$fila["uid"]."' target='_blank'>".$fila["drivername"]."</a>",
			($fila['game']=="ats"?"American Truck Simulator":"Euro Truck Simulator 2"),
			"<a class='table-a' href='".SITE_URL."/trips/view/".$fila["id"]."' target='_blank'>".$fila["org_city"]." a ". $fila["des_city"]."</a>",
			date("H:i:s d/m/Y", strtotime($fila["finish"])),
			$fila["trailer"],
			($fila["mass"]/1000)." ton",
			$fila["driven"]." km",
			gmdate("H\h i\m\i\\n",strToTime($fila['finish'])-strToTime($fila['start'])));
		$jsondataList[]=$jsondataperson;
	}
	$jsondata["data"] = array_values($jsondataList);
	header("Content-type:application/json; charset = utf-8");
	echo json_encode($jsondata);
}
?>