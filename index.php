<?php
session_start();
require_once('inc/layout.php');
require_once('inc/model.php');
require_once ('inc/functions.php');
require_once('inc/config.php');
if (SITE_DEBUG_ON) {
	ini_set('display_errors',1);
	ini_set('display_startup_errors',1);
	error_reporting(-1);
	if(!isLoggedIn()){
		gotoUrl("/login",MSG_TYPE_INFO,"Por favor, inicia sesi&oacute;n");
	}
	if($_SESSION['rank']<USER_RANK_DEVELOPER){
		layoutHead("Mantenimiento");
        layoutInitBody();
        layoutInitWrapper();
        layoutInitContent();
        echo '<div style="text-align:center">';
        echo '<img src="img/mantenimiento.png" align="middle"><br />';
		echo ("El mapa no se encuentra activo actualmente. Se están realizando tareas de mantenimiento.");
		echo '</div>';		
        layoutEndContent();
        layoutEndWrapper();
        layoutAddDefaultScript();
        layoutEndBody();
	}else{
		$scripts = array();
		layoutHead("Mapa");
		layoutInitBody();
		layoutInitWrapper();	
		layoutTopbar("map",true);
		layoutInitContent();
		layoutDrawMap();	
		$scripts[] = "chatbox.js";
		$scripts[] = "favico.min.js";
		$scripts[] = "leaflet.js";	
		$scripts[] = "map.js";
		layoutEndContent();
		layoutEndWrapper();
		layoutAddDefaultScript($scripts);
		layoutEndBody();
	}
}else if (isLoggedIn()){
	$scripts = array();
	layoutHead("Mapa");
	layoutInitBody();
	layoutInitWrapper();	
	layoutTopbar("map",true);
	layoutInitContent();
	layoutDrawMap();	
	$scripts[] = "chatbox.js";
	$scripts[] = "favico.min.js";
	$scripts[] = "leaflet.js";
	$scripts[] = "map.js";
	layoutEndContent();
	layoutEndWrapper();
	layoutAddDefaultScript($scripts);
	layoutEndBody();
} else {	
	gotoUrl("/login",MSG_TYPE_INFO,"Por favor, inicia sesi&oacute;n");
}
?>