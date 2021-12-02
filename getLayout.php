<?php
session_start();
require_once("inc/model.php");
require_once("inc/functions.php");
require_once("inc/lay_menu.php");
$action = filter_input(INPUT_GET, "a",FILTER_SANITIZE_STRING);
if (!isLoggedIn()){
	die;
}

switch ($action) {
	case 'tripmenu':	
		displayUserTripMenu();		
		break;
	default:
		break;
}

?>
