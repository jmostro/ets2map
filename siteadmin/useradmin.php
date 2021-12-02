<?php
session_start();
require_once(__DIR__.'/../inc/config.php');
require_once(__DIR__.'/../inc/model.php');
require_once(__DIR__.'/../inc/layout.php');
require_once(__DIR__.'/../inc/functions.php');
require_once(__DIR__.'/nav_layout.php');

if (!isAdmin()){
	gotoUrl("/",MSG_TYPE_ERROR,"Solo para personal autorizado");	
	die;
}

// GET PARAMS
$action = filter_input(INPUT_GET, 'a', FILTER_SANITIZE_STRING);

switch ($action) {
	case 'savenew':
		saveNewUser();		
		die;
}

layoutHead("Administraci&oacute;n");
layoutInitBody();
layoutInitWrapper();
layoutTopbar("siteadmin");                                     
layoutInitContent();    

switch ($action) {
	case 'new':
		layoutNewUser();
		break;
}

layoutEndContent();
layoutEndWrapper();         
layoutAddDefaultScript();
layoutEndBody();

/**
	FORMULARIO ALTA NUEVO USUARIO
**/
function layoutNewUser(){
	global  $user_rank_name; 	
    echo '<div class="control-group col-md-8 col-md-offset-2">';
    adminNavigation("users");	
    echo '<legend>Alta de usuario</legend>';         
    echo '<form id="user-form col-md-5" method="POST" action="'.SITE_URL.'/admin/users/savenew">';
    /*
     * NOMBRE DE USUARIO
     */
    echo '<div class="input-group">';
    echo '<span class="input-group-addon">Nombre de usuario</span>';
    echo '<input type="text" name="drivername" placeholder="Nombre de usuario" class="form-control" required="" type="text">';
    echo '</div><br>';  
    /*
     * Puesto
     */
    echo '<div class="input-group">';
    echo '<span class="input-group-addon">Puesto</span>';
    echo '<select class="form-control" name="rank" required="">';
    foreach ($user_rank_name as $idx => $name) {
        if ($idx < $_SESSION['rank']) {
            echo '<option value='.$idx.'">'.$name.'</option>';
        }
    }
    echo '</select>';
    echo '</div><br>';
    /*
     * Nombre completo
     */
    echo '<div class="input-group">';
    echo '<span class="input-group-addon">Nombre completo</span>';    
    echo '<input name="fullname" placeholder="Nombre completo" class="form-control" type="text" required=>';
    echo '</div><br><div class="input-group">';
    /*
     * Correo
     */
    echo '<span class="input-group-addon">Correo</span>';
    echo '<input name="email" placeholder="usuario@ejemplo.com" class="form-control" type="email">';
    echo '</div><br>';	
    /*
    * ID DE ETS2MP
    */
    echo '<div class="input-group">';
    echo '<span class="input-group-addon">ID de ETS2MP</span>';
    echo '<input type="text" id="mpid" name="mpid" placeholder="ETS2MP ID" class="form-control" type="text"">';
    echo '</div><br>';
    /*
    * ID DE STEAM
    */
    echo '<div class="input-group">';
    echo '<span class="input-group-addon">ID de Steam</span>';
    echo '<input type="text" id="steamid" name="mpid" placeholder="STEAM ID (steamID64)" class="form-control" type="text"">';
    echo '</div><br>';
    echo '<div class="input-group">';
    echo '<span class="input-group-addon">Para buscar el ID de Steam: <a href="https://steamid.io/lookup" target="_blank">https://steamid.io/lookup</a></span>';
    echo '</div><br>';
    /*    
    /*
     * CONTRASEÑA
     */
    echo '<div class="input-group">';    
    echo '<span class="input-group-addon">Nueva contrase&ntilde;a</span>';
    echo '<input name="newpassword" placeholder="" class="form-control" type="password">';
    echo '</div><br><div class="input-group">';
    echo '<span class="input-group-addon">Repetir contrase&ntilde;a</span>';
    echo '<input name="confirmpassword" placeholder="" class="form-control" type="password">';
    echo '</div><br><div class="input-group">';

    /*
     *  SUBMIT
     */
    echo '<button id="savebutton" name="savebutton" class="btn btn-md btn-primary">Guardar</button>';
    echo '</div></form>';
    echo '</div>';
}

/**
	GUARDAR NUEVO USUARIO
**/
function saveNewUser(){
	$returnUrl = "admin/users/new";
	$username = filter_input(INPUT_POST,'drivername',FILTER_SANITIZE_STRING);	
	$fullname = filter_input(INPUT_POST,'fullname',FILTER_SANITIZE_STRING);
	$email = filter_input(INPUT_POST,'email',FILTER_SANITIZE_STRING);
	$rank = filter_input(INPUT_POST,'rank', FILTER_SANITIZE_NUMBER_INT);
	$mp_id = filter_input(INPUT_POST,'mpid',FILTER_SANITIZE_STRING);
    $steam_id = filter_input(INPUT_POST,'steamid',FILTER_SANITIZE_STRING);
	$pass = filter_input(INPUT_POST,'newpassword',FILTER_SANITIZE_STRING);
	$confirmpassword = filter_input(INPUT_POST,'confirmpassword',FILTER_SANITIZE_STRING);	
	if (strlen($username) < MIN_USERNAME_LENGTH){			
		gotoUrl($returnUrl,MSG_TYPE_WARNING,"El nombre de usuario debe tener al menos ".MIN_USERNAME_LENGTH." car&aacute;cteres");	
		return null;
	}
	if ($rank >= $_SESSION['rank']) {
		gotoUrl($returnUrl,MSG_TYPE_WARNING,"No puede asignar ese puesto");
		return null;
	}
	if (strlen($pass) < MIN_PASSWORD_LENGTH) {
		gotoUrl($returnUrl,MSG_TYPE_WARNING,"La contrase&ntilde;a debe tener un m&iacute;nimo de ".MIN_PASSWORD_LENGTH." car&aacute;cteres");
		return null;
	}
	if ($pass != $confirmpassword) {
		gotoUrl($returnUrl,MSG_TYPE_WARNING,"Las contrase&ntilde;as no coinciden");	
		return null;
	}
    if ($steam_id ==null) {
        gotoUrl($returnUrl,MSG_TYPE_WARNING,"Debe ingresar un Steam ID");   
        return null;
    }

	$pass = md5($pass);
	$squery = "INSERT INTO drivers (username,password,displayname,fullname,mp_id,registered,rank,email,steam_id) VALUES ('$username','$pass','$fullname','$fullname','$mp_id',CURRENT_TIMESTAMP(),$rank,'$email','$steam_id');";
	if ($newId = dbInsert($squery)){
		$squery = "INSERT INTO trucks (owner) VALUES ($newId)";
		if (dbInsert($squery)){
			$squery = "INSERT INTO user_options (uid) VALUES ($newId)"	;
			if (dbInsert($squery)){
				logEvent(LOG_TYPE_ADMIN,"Creo el usuario con id $newId");
				gotoUrl("users/view/info/".$newId,MSG_TYPE_SUCCESS,"Creado el usuario con ID $newId");
				return null;
			} else {
				gotoUrl($returnUrl,MSG_TYPE_ERROR,"No se pudo generar las opciones de usuario");
				return null;
			}
		} else {
			gotoUrl($returnUrl,MSG_TYPE_ERROR,"No se pudo generar la telemetria de usuario");
			return null;
		}
	} else {
		gotoUrl($returnUrl,MSG_TYPE_ERROR,"No se pudo generar el usuario");
		return null;
	}		
}
?>