<?php
session_start();
require_once(__DIR__.'/../inc/config.php');
require_once(__DIR__.'/../inc/model.php');
require_once(__DIR__.'/../inc/functions.php');

if (!isLoggedIn()){
	die;
}

if (!isAdmin()){
	gotoUrl("/",MSG_TYPE_ERROR,"Solo para personal autorizado");	
	die;
}

$id = filter_input(INPUT_GET,'id',FILTER_SANITIZE_NUMBER_INT);

$date = new DateTime(null, new DateTimeZone('America/Argentina/Buenos_Aires'));

$response = array();
// Leo el archivo
$file = file_get_contents("../inc/settings.php");
// Cambio el valor
if (CONVOY_ON == 1){
	$text = addslashes("Se desactiv&oacute; el modo convoy (".$date->format('H:i d-m-Y')." GMT-3)");
	$file = str_replace("const CONVOY_ON = 1;", "const CONVOY_ON = 0;", $file); // Cancelo el modo convoy
	$squery = "INSERT INTO chatbox (uid, text) VALUES ($id, '$text');";
	$response['status'] = 0;
}
else{
	$text = addslashes("Se activ&oacute; el modo convoy (".$date->format('H:i d-m-Y')." GMT-3)");
	$file = str_replace("const CONVOY_ON = 0;", "const CONVOY_ON = 1;", $file); // Activo el modo convoy
	$squery = "INSERT INTO chatbox (uid, text) VALUES ($id, '$text');";
	$response['status'] = 1;
}
if(dbInsert($squery)){
	// Grabo
	file_put_contents("../inc/settings.php", $file);
	logEvent(LOG_TYPE_ADMIN,"Se actualizó el modo convoy");
	$response['error'] = false;
}else{
	$response['error'] = true;
}
echo json_encode($response);

?>