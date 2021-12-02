<?php 
	const USER_RANK_DISABLED = 0;
	const USER_RANK_RECRUIT = 1;
	const USER_RANK_DRIVER = 2;
	const USER_RANK_RECRUITER = 6;
	const USER_RANK_SUPERVISOR = 7;
	const USER_RANK_MANAGER = 8;
	const USER_RANK_CEO = 9;
	const USER_RANK_DEVELOPER = 10;
	$user_rank_name[USER_RANK_DISABLED] = "Deshabilitado";
	$user_rank_name[USER_RANK_RECRUIT] = "Recluta";
	$user_rank_name[USER_RANK_DRIVER] = "Conductor";
	$user_rank_name[USER_RANK_RECRUITER] = "Reclutador";
	$user_rank_name[USER_RANK_SUPERVISOR] = "Supervisor";
	$user_rank_name[USER_RANK_MANAGER] = "Manager";
	$user_rank_name[USER_RANK_CEO] = "CEO";
	$user_rank_name[USER_RANK_DEVELOPER] = "Desarrollador";
	const MSG_TYPE_NOMSG = 0;
	const MSG_TYPE_SUCCESS = 1;
	const MSG_TYPE_INFO = 2;
	const MSG_TYPE_WARNING = 3;
	const MSG_TYPE_ERROR = 4;
	$msg_class[MSG_TYPE_ERROR] = "alert-danger";
	$msg_class[MSG_TYPE_WARNING] = "alert-warning";
	$msg_class[MSG_TYPE_INFO] = "alert-info";
	$msg_class[MSG_TYPE_SUCCESS] = "alert-success";
	const LOG_TYPE_DEBUG = 0;
	const LOG_TYPE_EVENT = 1;
	const LOG_TYPE_ADMIN = 2;
	const LOG_TYPE_WARNING = 3;	
	const LOG_TYPE_ERROR = 5;
	const LOG_TYPE_DBERROR = 6;
	$log_type_name[LOG_TYPE_DEBUG] = "DEBUG";
	$log_type_name[LOG_TYPE_EVENT] = "EVENTO";
	$log_type_name[LOG_TYPE_WARNING] = "WARN";
	$log_type_name[LOG_TYPE_ADMIN] = "ADMIN";
	$log_type_name[LOG_TYPE_ERROR] = "ERROR";
	$log_type_name[LOG_TYPE_DBERROR] = "ERRDB";
	const MIN_ZOOM_LEVEL  = 3;
	const MAX_ZOOM_LEVEL  = 7;
	const RECORDS_PER_PAGE = 25;
	const PAGE_OFFSET = 3;
	const KEEPALIVE_COOKIE_NAME = "loglatmaprv";
	$site_themes[1] = "Sandstone";
	$site_themes[2] = "Cyborg";	
	$site_themes[3] = "Slate";
	$site_themes[4] = "United";	
	$site_themes[5] = "Darkly";
	$site_themes[6] = "Cosmo";
	$map_colors[2] = "Claro";
	$map_colors[3] = "Oscuro";
	require_once(__DIR__."/settings.php");
		if (SITE_DEBUG_ON) {
	    ini_set('display_errors',1);
	    ini_set('display_startup_errors',1);
	    error_reporting(-1);
	}
	?>