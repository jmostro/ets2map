<?php
session_start();
require_once("inc/model.php");
require_once("inc/functions.php");
require_once("inc/config.php");
$action = filter_input(INPUT_GET, "a", FILTER_SANITIZE_STRING);

switch ($action) {	
	case 'usersonservers':
		echo json_encode(usuariosOnSever());
		break;
	case 'modoconvoy':
		echo json_encode(array('convoy'=>CONVOY_ON));
		break;
	default:
		break;
}
?>