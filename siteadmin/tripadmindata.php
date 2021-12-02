<?php
session_start();
require_once(__DIR__.'/../inc/config.php');
require_once(__DIR__.'/../inc/model.php');
require_once(__DIR__.'/../inc/functions.php');
if (!isDeveloper()){gotoUrl("/",MSG_TYPE_ERROR,"Solo para personal autorizado");die;}

$jsondataList = array();
$myquery = "SELECT 
			trips.id AS id,
			trips.uid AS uid,
			trips.org_city AS org_city,
			trips.des_city AS des_city,
			trips.driven AS driven, 
			trips.start AS start,
			trips.finish AS finish,
			trips.delivered AS delivered,
			trips.deleted as deleted, 
			trips.game as game, 
			drivers.displayname AS drivername
			 FROM trips INNER JOIN drivers ON trips.uid = drivers.id
			 ORDER BY trips.finish DESC";
$resultado = dbSelect($myquery);
while($fila = mysqli_fetch_assoc($resultado))
{
	$jsondataperson = array(
		"<a class='table-a' href='".SITE_URL."/users/view/info/".$fila["uid"]."' target='_blank'>".$fila["drivername"]."</a>",
		"<a class='table-a' href='".SITE_URL."/trips/view/".$fila["id"]."' target='_blank'>".$fila["org_city"]." a ". $fila["des_city"]."</a>",
		$fila["driven"]." km",
		date("H:i:s d/m/Y", strtotime($fila["start"])),
		date("H:i:s d/m/Y", strtotime($fila["finish"])),
		($fila["deleted"]?"<i class='fa fa-times' aria-hidden='true'></i>":""),
		($fila["deleted"]?"Eliminado":"Entregado"),
		($fila['game']=="ats"?"American Truck Simulator":"Euro Truck Simulator 2"));
	$jsondataList[]=$jsondataperson;
}
$jsondata["data"] = array_values($jsondataList);
header("Content-type:application/json; charset = utf-8");
echo json_encode($jsondata);
?>