<?php
/*
id	bigint(20)			No	Ninguna	AUTO_INCREMENT	Cambiar Cambiar	Eliminar Eliminar	
uid	bigint(20)			No	Ninguna		Cambiar Cambiar	Eliminar Eliminar	
brand	varchar(50)	latin1_swedish_ci		No	Ninguna		Cambiar Cambiar	Eliminar Eliminar	
model	varchar(50)	latin1_swedish_ci		No	Ninguna		Cambiar Cambiar	Eliminar Eliminar	
org_city	varchar(50)	latin1_swedish_ci		No	Ninguna		Cambiar Cambiar	Eliminar Eliminar	
des_city	varchar(50)	latin1_swedish_ci		No	Ninguna		Cambiar Cambiar	Eliminar Eliminar	
org_cmpy	varchar(50)	latin1_swedish_ci		No	Ninguna		Cambiar Cambiar	Eliminar Eliminar	
des_cmpy	varchar(50)	latin1_swedish_ci		No	Ninguna		Cambiar Cambiar	Eliminar Eliminar	
trailer	varchar(100)	latin1_swedish_ci		No	Ninguna		Cambiar Cambiar	Eliminar Eliminar	
mass	float			No	Ninguna		Cambiar Cambiar	Eliminar Eliminar	
income	float			No	Ninguna		Cambiar Cambiar	Eliminar Eliminar	
distance	float			No	Ninguna		Cambiar Cambiar	Eliminar Eliminar	
truck_dmg	float			No	Ninguna		Cambiar Cambiar	Eliminar Eliminar	
trailer_dmg	float			No	Ninguna		Cambiar Cambiar	Eliminar Eliminar	
start	timestamp		on update CURRENT_TIMESTAMP	No	CURRENT_TIMESTAMP	ON UPDATE CURRENT_TIMESTAMP	Cambiar Cambiar	Eliminar Eliminar	
finish	timestamp			No	0000-00-00 00:00:00		Cambiar Cambiar	Eliminar Eliminar	
delivered	tinyint(4)
*/

/**
* Busca un viaje sin finalizar para el conductor indicado
*
* @param integer $uid ID del usuario
* @return integer ID del viaje o 0 si no hay
*/
function findOpenTrip ($uid){
	$OpenTripData = null;
	$squery = "SELECT id, serverstart FROM trips WHERE uid = $uid AND delivered = 0 AND deleted = 0 LIMIT 1;";
	if ($result = dbSelect($squery)){ 
		while ($row = mysqli_fetch_assoc($result)){
			$OpenTripData = $row;
        }        
    }
	return $OpenTripData;
}

/**
* Comienza un viaje en la DB para el conductor indicado
* 
* @param integer $uid ID del usuario
* @return integer ID del viaje comenzado
*/
function initializeTrip ($uid, $gamedata) {
    $game = $gamedata->Game;
    $trailer = $gamedata->Trailer;
    $truck = $gamedata->Truck;
    $job = $gamedata->Job;
    $nav = $gamedata->Navigation;
    $road = $gamedata->Roadtrip;

	$squery = "INSERT INTO trips 
	(uid,brand,model,org_city,des_city,org_cmpy,des_cmpy,trailer,mass,income,distance,serverstart,game)
	 VALUES 
	 	(".$uid.",
		'".addslashes($truck->Make)."',
		'".addslashes($truck->Model)."',
		'".addslashes($job->SourceCity)."',
		'".addslashes($job->DestinationCity)."',
		'".addslashes($job->SourceCompany)."',
		'".addslashes($job->DestinationCompany)."',
		'".addslashes($trailer->Name)."',
		".$trailer->Mass.",
		".$job->Income.",
		".$nav->EstimatedDistance.",
		'".$road->OnlineStatus."',
		'".$game->gameName."');";
	if ($tripId = dbInsert($squery))
		return $tripId;
}

/**
* Finaliza el viaje que tenga abierto el conductor indicado
*
* @param integer $uid id del conductor
* @return integer id del viaje finalizado, false si hubo error
* @todo Implementar trip_details, con los detalles de rotura y costos
*/
function endTrip($tripData, $gamedata) {
    $game = $gamedata->Game;
    $trailer = $gamedata->Trailer;
    $truck = $gamedata->Truck;
    $job = $gamedata->Job;
    $nav = $gamedata->Navigation;
    $road = $gamedata->Roadtrip;

	$truck_dmg = $truck->WearEngine + $truck->WearTransmission + $truck->WearWheels + $truck->WearCabin + $truck->WearChassis;
	$truck_dmg /= 5;
/*	$expenses = 0;
	$expenses += $truck['trailer_dmg'] * $truck['job_income'];*/
	if(!$road->isLate)
		$profit = $job->Income;
	else
		$profit = 0;
	
	$squery = "UPDATE trips SET 
			delivered = 1,
			trailer_dmg = ".$trailer->Wear.",
			truck_dmg = ".$truck_dmg.",
			expenses = 0,
			profit = ".$profit.",
			driven = ".$road->DistanceDriven.",
			finish = NOW(),
			serverend = '".addslashes($road->OnlineStatus)."'";
	if($road->isLate == true)
		$squery .= ",late=1";
	else
		$squery .= ",late=0";

	// Si el viaje es offline, no lo muestro
	if($tripData['serverstart']=="Offline" and $road->OnlineStatus == "Offline"){
		$squery.=",isshowed = 0";
	}else{
		// Si el viaje es en un servidor con limitador, lo muestro, sino no
		$resultado = getServersDB($road->OnlineStatus,$game->gameName);
		if($resultado[0]['limiter'] && CONVOY_ON == 0)
			$squery.=",isshowed = 1";
		else
			$squery.=",isshowed = 0";
	}
	$squery.=" WHERE id=".$tripData['id'].";";
	if (dbUpdate($squery)) return $tripData['id'];
}

/**
* Elimina un viaje
*
*@param integer $id id del viaje
*@return boolean true o false en caso de error
*/
function deleteTrip($tripData){
	$squery = "UPDATE trips SET deleted = 1, isshowed = 0 WHERE id = $tripData;";
	if (dbUpdate($squery)){
		return true;
	}
	return false;
}

function recoverTrip($tripData){
	$squery = "UPDATE trips SET deleted = 0 WHERE id = $tripData;";
	if (dbUpdate($squery)){
		return true;
	}
	return false;
}

function modifyRanking($tripData, $status){
	$squery = "UPDATE trips SET isshowed = $status WHERE id = $tripData;";
	if (dbUpdate($squery)){
		return true;
	}
	return false;
}

/**
* Obtiene la informacion del viaje indicado
*
* @param integer $id id del viaje 
* @return object informacion del viaje o null en caso de no encontrarse
*/
function getTripinfo($id){
	$squery = "SELECT 
		id
		,uid
		,brand
		,model
		,CONCAT (brand,' ',model) as truck_name
		,org_city
		,des_city
		,org_cmpy
		,des_cmpy
		,trailer
		,mass
		,income
		,expenses
		,profit
		,late
		,distance
		,driven
		,truck_dmg
		,trailer_dmg
		,start
		,finish
		,delivered
		,deleted
		,serverstart
		,serverend
		,game
		,isshowed 
		FROM trips 
		WHERE id=".$id." LIMIT 1;";
	if($result = dbSelect($squery))
		if ($row = mysqli_fetch_array($result))
			return $row;
}

/**
* Obtiene listado de viajes
*
* @param integer [$uid] id del conductor
* @return array viajes o null en caso de no encontrarse
*/
function getUserTrips($uid = 0, $first = 1, $cant = NUM_RECORDS_PER_PAGE, $delivered  = 1, $deleted = 0){
	$squery = "SELECT "
		."trips.id AS id,"
		."trips.uid AS uid,"
		//."trips.brand AS brand,"
		//."trips.model AS model,"
		."trips.org_city AS org_city,"
		."trips.des_city AS des_city,"
		//."trips.org_cmpy as org_cmpy,"
		//."trips.des_cmpy as des_cmpy,"
		."trips.trailer AS trailer,"
		."trips.mass AS mass,"
		."trips.income as income,"
		."trips.distance AS distance,"
		."trips.driven AS driven, "
		."trips.start AS start,"
		."trips.finish AS finish,"
		."trips.delivered AS delivered,"
		."trips.deleted as deleted, "
		."trips.game as game, "
		."drivers.displayname AS drivername"
		." FROM trips INNER JOIN drivers ON trips.uid = drivers.id"
		." WHERE trips.delivered = ".$delivered
		." AND trips.deleted = ".$deleted;
	if ($uid) $squery .=" AND trips.uid=$uid";
	$squery .=" ORDER BY trips.finish DESC LIMIT $first,$cant;";
	if ($result = dbSelect($squery))
		return $result;
	
	return null;
}

/**
* Obtiene la cantidad de viajes
*
* @param integer [$uid] id del conductor
* @return integer cantidad de registros, 0 si no hay
*/
function countUserTrips($uid = 0, $delivered = 1, $deleted = 0){
	$squery = "SELECT COUNT(*) FROM trips WHERE deleted = ".$deleted." AND delivered = ".$delivered;
	if ($uid) $squery .=" AND uid=$uid";
	if ($result = dbSelect($squery)){
		if ($row = mysqli_fetch_array($result)){
			return $row[0];
		}
	}	
	return 0;
}

function getUserStats ($uid = 0){
	if ($uid == 0) return null;
	$squery="SELECT "
		."COUNT(*) AS trips"
		.", ROUND(SUM(income),2) AS income"
		.", ROUND(SUM(expenses),2) AS expenses"
		.", ROUND(SUM(profit),2) AS profit"
		.", ROUND(SUM(distance) / 1000,2) AS distance"
		.", SUM(driven) AS driven"
		." FROM trips WHERE deleted = 0 AND delivered = 1 AND isshowed=1";
		if ($uid > 0){
			$squery.=" AND uid=$uid";
		}
		$squery .= ";";		
	if ($result = dbSelect($squery)) {
		if ($row = mysqli_fetch_array($result)){
			return $row;
		}
	}
}

/**
* Devuelve la cantidad de viajes abiertos
* @param $uid id del usuario
* @return numero de viajes, 0 si no hay
*/
function countOpenTrips ($uid = 0){
	$squery = "SELECT COUNT(*) FROM trips WHERE deleted = 0 AND delivered = 0";
	if ($uid) $squery .= " AND uid=$uid";
	if ($result = dbSelect($squery)){
		if ($row = mysqli_fetch_array($result)){
			return $row[0];
		}
	}
	return 0;
}

/**
* Obtiene listado de viajes abiertos
*
* @param integer [$uid] id del conductor
* @return array viajes o null en caso de no encontrarse
*/
function getOpenTrips($uid = 0, $first = 0, $cant = NUM_RECORDS_PER_PAGE){
	$squery = "SELECT 
		trips.id AS id,
		trips.uid AS uid, "
		//."trips.brand AS brand,"
		//."trips.model AS model,"
		."trips.org_city AS org_city,
		trips.des_city AS des_city,"
		//."trips.org_cmpy as org_cmpy,"
		//."trips.des_cmpy as des_cmpy,"
		."trips.trailer AS trailer,
		trips.mass AS mass,"
		//."trips.income as income,"
		."trips.distance AS distance,
		trips.start AS start,
		trips.finish AS finish,
		trips.delivered AS delivered,
		trips.game AS game,
		drivers.displayname as drivername 
		 FROM trips INNER JOIN drivers ON trips.uid = drivers.id 
		 WHERE trips.deleted = 0 AND trips.delivered = 0";

	if ($uid) $squery .=" AND trips.uid=$uid";
	$squery .=" ORDER BY trips.start";
	$squery .=" LIMIT $first,$cant;";	
	if ($result = dbSelect($squery)){
		return $result;
	}	
	return null;
}

function getTopDrivers ($field = distance, $numRecords = 5,$count = false, $year, $month, $game){	
	$squery = "SELECT uid, value, name FROM ("
			." SELECT trips.uid AS uid,";
	if ($count) {
		$squery.=" COUNT(*) AS value,";
	} else {
		$squery.=" ROUND(SUM(trips.$field),2) AS value,";
	}
	if ($year > 0){
		$yearFilter = " AND YEAR(trips.finish) = $year";
	} else {
		$yearFilter = "";
	}
	if ($month > 0){
		$monthFilter = " AND MONTH(trips.finish) = $month";
	} else {
		$monthFilter = "";
	}
	if($game < 1){
		$gameFilter = "";
	}
	if($game == 1){
		$gameFilter = " AND trips.game = 'ats'";
	}if($game == 2){
		$gameFilter = " AND trips.game = 'ets2'";
	}
	$squery.=" drivers.displayname AS name"
			." FROM trips"
			." INNER JOIN drivers"
			." ON trips.uid = drivers.id"
			." WHERE trips.delivered=1 AND trips.deleted=0 AND drivers.rank > 0 AND trips.isshowed = 1"
			.$yearFilter
			.$monthFilter
			.$gameFilter
			
			." GROUP BY uid) ttq"
			." ORDER BY value DESC"
			." LIMIT $numRecords";	
	logEvent(LOG_TYPE_DEBUG,"Debug filter rank by date.",$squery);
	$result = dbSelect($squery);
	return $result;
}

function getServersDB($server = "", $game = "") {
	$squery = "SELECT * FROM servers";
	if(!empty($server) && !empty($game))
		$squery .= " WHERE name = '".$server."' AND game = '".$game."';";
	if ($result = dbSelect($squery)){ 
		while ($row = mysqli_fetch_assoc($result)){
			$servers[] = $row;
        }
        return $servers;
    }
    return null;
}
?>