<?php
session_start();
require_once(__DIR__.'/../inc/config.php');
require_once(__DIR__.'/../inc/model.php');
require_once(__DIR__.'/../inc/layout.php');
require_once(__DIR__.'/../inc/functions.php');
if (!isDeveloper()){gotoUrl("/",MSG_TYPE_ERROR,"Solo para personal autorizado");die;}

switch (filter_input(INPUT_GET, 'a', FILTER_SANITIZE_STRING)) {
	case 'clear':
		clearSiteLog();
		die;
	case 'data':
		pullLogs();
		die;
	default:
		die;
}

/** 
	BORRAR LOG DEL SITIO
**/
function clearSiteLog (){
	$res = array();
	if (clearLog()) {
		logEvent(LOG_TYPE_ADMIN,"CLEAR LOG!");
		$res['successful'] = true;
		$res['message'] = "Registros eliminados";
	} else {
		$res['successful'] = false;
		$res['message'] = "Ocurrió un error al vaciar el log, intente nuevamente.";
	}
	echo json_encode($res);
}

/**
	Obtengo los datos de la tabla
**/
function pullLogs(){
	global $log_type_name;
	$jsondataList = array();
	$myquery = "SELECT 
				log.id as id,
				log.uid as uid,
				log.query as query,
				log.type as type,
				log.timestamp as tiempo,
				log.text as texto,
				drivers.realusername as user
				FROM log
				INNER JOIN drivers ON log.uid = drivers.id
				ORDER BY tiempo DESC";
	$resultado = dbSelect($myquery);
	while($fila = mysqli_fetch_assoc($resultado))
	{
		$jsondataperson = array(
			$fila["id"],
			"<a class='table-a' href='".SITE_URL."/users/view/info/".$fila["uid"]."' target='_blank'>".$fila["user"]."</a>",
			$log_type_name[$fila["type"]],
			date("H:i:s d/m/Y", strtotime($fila["tiempo"])),
			$fila["texto"],
			(!empty($fila["query"])?"<td><span class='glyphicon glyphicon-info-sign' aria-hidden='true' title='".$fila["query"]."'></span></td></tr>":"<td></td></tr>"));
		$jsondataList[]=$jsondataperson;
	}
	$jsondata["data"] = array_values($jsondataList);
	header("Content-type:application/json; charset = utf-8");
	echo json_encode($jsondata);
}
?>