<?php
session_start();
require_once(__DIR__.'/../inc/config.php');
require_once(__DIR__.'/../inc/model.php');
require_once(__DIR__.'/../inc/layout.php');
require_once(__DIR__.'/../inc/functions.php');
if (!isDeveloper()){gotoUrl("/",MSG_TYPE_ERROR,"Solo para personal autorizado");die;}

switch (filter_input(INPUT_GET, 'a', FILTER_SANITIZE_STRING)) {
	case 'clear':
		clearSiteChats();
		die;
	case 'cleardeleted':
		clearSiteChatsDeleted();
		die;
	case 'data':
		pullChats();
		die;
	default:
		die;
}

function clearSiteChats(){
	$res = array();
	if ($numRows = flushChat()) {
		logEvent(LOG_TYPE_ADMIN,"Flush chat ($numRows mensajes)");
		$res['successful'] = true;
		$res['message'] = $numRows." mensajes eliminados";
	} else {
		$res['successful'] = false;
		$res['message'] = "No hay mensajes para eliminar.";
	}
	echo json_encode($res);
}

function clearSiteChatsDeleted(){
	$res = array();
	if ($numRows = flushDeletedChat()) {
		logEvent(LOG_TYPE_ADMIN,"Flush chat ($numRows mensajes)");
		$res['successful'] = true;
		$res['message'] = $numRows." mensajes eliminados";
	} else {
		$res['successful'] = false;
		$res['message'] = "No hay mensajes para eliminar.";
	}
	echo json_encode($res);
}

function pullChats(){
	global $user_rank_name;
	$jsondataList = array();
	$myquery = "SELECT 
	            chatbox.id AS id, 
	            chatbox.uid AS uid, 
	            chatbox.text AS mensaje, 
	            chatbox.date AS fecha, 
	            chatbox.deleted AS deleted, 
	            drivers.displayname AS name, 
	            drivers.fullname AS fullname, 
	            drivers.rank AS rank 
	            FROM chatbox 
	            INNER JOIN drivers ON drivers.id = chatbox.uid
	            ORDER BY fecha;";
	$resultado = dbSelect($myquery);
	while($fila = mysqli_fetch_assoc($resultado))
	{
		$jsondataperson = array(
			$fila["id"],
			"<a href='".SITE_URL."/users/view/profile/".$fila['uid']."' title='Ver perfil del usuario'>".$fila["name"]."</a>",
			$user_rank_name[$fila["rank"]],
			date("H:i:s d/m/Y", strtotime($fila["fecha"])),
			$fila["mensaje"],
			($fila['deleted'])?"<span class='glyphicon glyphicon-remove' aria-hidden='true' title='Borrado'></span>":"");
		$jsondataList[]=$jsondataperson;
	}
	$jsondata["data"] = array_values($jsondataList);
	header("Content-type:application/json; charset = utf-8");
	echo json_encode($jsondata);
}
?>