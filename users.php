<?php
session_start();
require_once('inc/model.php');
require_once('inc/layout.php');
require_once('inc/functions.php');
require_once('inc/config.php');
if (SITE_DEBUG_ON) {
    ini_set('display_errors',1);
    ini_set('display_startup_errors',1);
    error_reporting(-1);
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
        die();
    }
}

if ($_SESSION['rank']<USER_RANK_DRIVER) {
    gotoUrl("/",MSG_TYPE_ERROR,"Solo para conductores");    
    die;
}

$driverid = filter_input(INPUT_GET,'id', FILTER_SANITIZE_NUMBER_INT);

if (isLoggedIn()){
    switch (filter_input(INPUT_GET, 'a', FILTER_SANITIZE_STRING)) {
        case 'updateprofile':
            saveProfileChanges($driverid);
            break;
        case 'saveadminprofile':
            saveAdminChanges($driverid);
            break;
        case 'saveoptions':
            saveUserOptions($driverid);
            break;
    	case 'view':
            $scripts = array(); // JAVASCRIPT PARA SER ENVIADO A LAYOUT
            $css = array();
            $css[] = "lightbox.min.css";
            layoutHead("Usuarios",$css);
            layoutInitBody();
            layoutInitWrapper();

            $section = filter_input(INPUT_GET, 's', FILTER_SANITIZE_STRING);
            ($driverid == $_SESSION['driverid'])?$scn = "user" : $scn = "company";
            layoutTopbar($scn);                                     
            layoutInitContent(); 		
    		if (isLoggedIn()) {				
    			switch ($section) {
    				case 'profile':
    					layoutUserProfile($driverid);
                        $scripts[] = "validator.min.js";
                        $scripts[] = "users.js";
    					break;
    				case 'info':
    					layoutUserInfo($driverid);
                        $scripts[] = "lightbox.min.js";
                        $scripts[] = "chart.min.js";
                        $scripts[] = "2YScales.js";
    					break;
    				case 'options':
    					layoutUserOptions($driverid);
                        $scripts[] = "validator.min.js";
                        $scripts[] = "users.js";
    					break;
                    case 'telemetry':
                        if (canEdit($driverid))
                            layoutTelemetryOptions($driverid);
                            $scripts[] = "lightbox.min.js";
                        break;
                    case 'admin':
                        layoutUserAdmin($driverid);
                        $scripts[] = "validator.min.js";
                        $scripts[] = "users.js";
                        break;
    				default:					
    					break;
    			}			
    		}
            layoutEndContent();
            layoutEndWrapper();
            layoutAddDefaultScript($scripts);
            layoutEndBody();
		break;
    }

} else {
    gotoUrl("index.php", MSG_TYPE_WARNING, "Solo usuarios registrados");
}

/**
    GUARDAR CAMBIOS EN EL PERFIL
**/
function saveProfileChanges($uid){
    /* CONTROL DE ACCESO */
    $editPW = canEdit($uid);
    if (!$editPW) { echo '<span>No posee permisos para realizar la acci&oacute;n solicitada.</span>'; return null;}	
    $displayname = filter_input(INPUT_POST,'displayname', FILTER_SANITIZE_STRING);
	$fullname = filter_input(INPUT_POST,'fullname',FILTER_SANITIZE_STRING);
	$email = filter_input(INPUT_POST,'email',FILTER_SANITIZE_EMAIL);
    $facebook = filter_input(INPUT_POST,'facebook',FILTER_SANITIZE_URL);
    $imgtruck = filter_input(INPUT_POST,'imgtruck',FILTER_SANITIZE_URL);
    $wotlink = filter_input(INPUT_POST,'wotlink',FILTER_SANITIZE_URL);
	$oldpass = filter_input(INPUT_POST,'actualpassword', FILTER_SANITIZE_STRING);
	$newpass = filter_input(INPUT_POST,'newpassword', FILTER_SANITIZE_STRING);
	$cnfpass = filter_input(INPUT_POST,'confirmpassword', FILTER_SANITIZE_STRING);	
	$upass = getDriverField($uid,'password');
    $res = array();
    $res['error'] = array();
    $res['success'] = true;

    // Valido
    if(!empty($facebook))
        if (filter_var($facebook, FILTER_VALIDATE_URL) === false) {
            $res['success'] = false;
            array_push($res['error'], "La url de facebook no es correcta.");
        }
    if(!empty($imgtruck))
        if (filter_var($imgtruck, FILTER_VALIDATE_URL) === false) {
            $res['success'] = false;
            array_push($res['error'], "La url para la imágen de perfil no es correcta.");
        }
    if(!empty($email))        
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $res['success'] = false;
            array_push($res['error'], "El email no es correcto.");
        }
    if(!empty($wotlink))
        if (filter_var($wotlink, FILTER_VALIDATE_URL) === false) {
            $res['success'] = false;
            array_push($res['error'], "La url del perfil de World of Trucks no es correcta.");
        }

	if ($oldpass != ""){	
		if (md5($oldpass) == $upass) {
			if ($newpass == $cnfpass) {
				$upass = md5($newpass);
			} else {
                $res['success'] = false;
                array_push($res['error'], "Las contraseñas no coinciden.");
			}
		} else {
            $res['success'] = false;
            array_push($res['error'], "La contraseña no es válida.");
		}
	}

    if($res['success'] == false){
        echo json_encode($res);
        die();
    }

    $squery = "UPDATE drivers SET fullname='$fullname', displayname='$displayname', email='$email', password='$upass', img_truck='$imgtruck', wot_profile='$wotlink' WHERE id=$uid LIMIT 1;";
    if (dbUpdate($squery)){
        $rquery = "UPDATE recruits SET facebook='$facebook' WHERE uid=$uid LIMIT 1;";
        if(dbUpdate($rquery)){
            if ($editPW == 1)
                logEvent(LOG_TYPE_EVENT,"Modificó su perfil",$squery."\n".$rquery);
            else
                logEvent(LOG_TYPE_ADMIN,"Modificó el perfil del usuario ID ".$uid,$squery."\n".$rquery);

            $res['success'] = true;
            array_push($res['error'], "Perfil actualizado.");
            echo json_encode($res);
            die();
        }else{
            $res['success'] = false;
            array_push($res['error'], "Error al conectar a la base de datos.");
            echo json_encode($res);
            die();
        }
    }
    $res['success'] = false;
    array_push($res['error'], "Error al conectar a la base de datos.");
    echo json_encode($res);
}

/**
GUARDAR CAMBIOS ADMINISTRATIVOS EN PERFIL
**/
function saveAdminChanges($uid){
    /* CONTROL DE ACCESO */
    if (!canAdmin($uid)) { echo '<span>No posee permisos para realizar la acci&oacute;n solicitada.</span>'; return null;}
    $username = filter_input(INPUT_POST, 'drivername', FILTER_SANITIZE_STRING);
    $newrank = filter_input(INPUT_POST,'rank',FILTER_SANITIZE_NUMBER_INT);
    $newpos = filter_input(INPUT_POST,'number',FILTER_SANITIZE_NUMBER_INT);
    $newpass = filter_input(INPUT_POST, 'newpassword',FILTER_SANITIZE_STRING);
    $cpass = filter_input(INPUT_POST,'confirmpassword', FILTER_SANITIZE_STRING);
    $mpid = filter_input(INPUT_POST,'mpid', FILTER_SANITIZE_STRING);
    $steamid = filter_input(INPUT_POST,'steamid', FILTER_SANITIZE_STRING);

    $res = array();
    $res['error'] = array();
    $res['success'] = true;
    if ($newpass != "") {
        if ($newpass != $cpass) {
            $res['success'] = false;
            array_push($res['error'], "Las contraseñas no coinciden");
        }
        $newpass = MD5($cpass);
    }

    if ($newrank >= $_SESSION['rank']){
        $res['success'] = false;
        array_push($res['error'], "No puede asignar ese puesto");
    }

    if($res['success'] == false){
        echo json_encode($res);
        die();
    }
    
    $squery = "UPDATE drivers SET ";
    if ($newpass != "")
        $squery .="password='$newpass', ";

    // Si el usuario era recluta lo activo/desactivo
    $rquery = "SELECT * FROM drivers WHERE id='$uid'";
    if ($result = dbSelect($rquery)) {
        if ($row = mysqli_fetch_array($result)){
            if($row['rank'] == USER_RANK_RECRUIT && $newrank > USER_RANK_RECRUIT){
                $rquery = "UPDATE recruits SET isDriver=1 WHERE uid='$uid';";
                dbUpdate($rquery);
                $rquery = "UPDATE driversnumbers SET uid = $uid WHERE uid = 0 LIMIT 1;";
                dbUpdate($rquery);
            }else if($row['rank'] == USER_RANK_RECRUIT && $newrank < USER_RANK_RECRUIT){
                $rquery = "UPDATE recruits SET isDriver=0 WHERE uid='$uid';";
                dbUpdate($rquery);
                $rquery = "UPDATE driversnumbers SET uid = $uid WHERE uid = 0 LIMIT 1;";
                dbUpdate($rquery);
            }
        }
    }

    // Guardo el nuevo puesto
    if(isset($newpos)){
        $rquery = "UPDATE driversnumbers set uid = 0 WHERE uid = $uid;";
        dbUpdate($rquery);

        $rquery = "UPDATE driversnumbers SET uid = $uid WHERE id = $newpos;";
        dbUpdate($rquery);
    }

    // Si el nuevo puesto es deshabilitado, libero el número de conductor
    if($newrank == USER_RANK_DISABLED) {
        $rquery = "UPDATE driversnumbers set uid = 0 WHERE uid = '$uid';";
        dbUpdate($rquery);
    }

    // Si el nuevo puesto es recluta, libero el número y actualizo la tabla
    if($newrank == USER_RANK_RECRUIT){
        $rquery = "UPDATE driversnumbers set uid = 0 WHERE uid = '$uid';";
        dbUpdate($rquery);
        $rquery = "UPDATE recruits SET isDriver=0 WHERE uid='$uid';";
        dbUpdate($rquery);   
    }

    // Si estaba deshabilitado y pasa a ser conductor, le otorgo un número libre
    $rquery = "SELECT * FROM drivers WHERE id='$uid'";
    if ($result = dbSelect($rquery)) {
        if ($row = mysqli_fetch_array($result)){
            if($row['rank'] == USER_RANK_DISABLED && $newrank >= USER_RANK_DRIVER){
                $rquery = "UPDATE driversnumbers SET uid = $uid WHERE uid = 0 LIMIT 1;";
                dbUpdate($rquery);
            }
        }
    }

    $squery .="rank=$newrank, mp_id='$mpid', steam_id='$steamid' WHERE id=$uid LIMIT 1;";
    if (dbUpdate($squery)){
        logEvent(LOG_TYPE_ADMIN,"Administró el usuario con id $uid",$squery);
        $res['success'] = true;
        array_push($res['error'], "Cambios guardados.");
        echo json_encode($res);
        die();
    }

    $res['success'] = false;
    array_push($res['error'], "Hubo un problema al guardar las opciones.");
    echo json_encode($res);
}

/** 
    GUARDAR OPCIONES DEL USUARIO
**/
function saveUserOptions($uid){    
    /* CONTROL DE ACCESO */
    $editPW = canEdit($uid);
    if (!$editPW) { echo '<span>No posee permisos para realizar la acci&oacute;n solicitada.</span>'; return null;}    
    $returnurl = "users/view/options/".$uid;
    (filter_input(INPUT_POST,'trafficOn',FILTER_SANITIZE_NUMBER_INT))?$trafficOn = 1:$trafficOn = 0;
    (filter_input(INPUT_POST,'tracerOn',FILTER_SANITIZE_NUMBER_INT))?$tracerOn = 1:$tracerOn = 0;
    (filter_input(INPUT_POST,'topbarOn',FILTER_SANITIZE_NUMBER_INT))?$topbarOn = 1:$topbarOn = 0;
    (filter_input(INPUT_POST,'driverlistOn',FILTER_SANITIZE_NUMBER_INT))?$driverlistOn = 1:$driverlistOn = 0;
    (filter_input(INPUT_POST,'truckinfoOn',FILTER_SANITIZE_NUMBER_INT))?$truckinfoOn = 1:$truckinfoOn = 0;
    (filter_input(INPUT_POST,'chatOn',FILTER_SANITIZE_NUMBER_INT))?$chatOn = 1:$chatOn = 0;

    $res = array();
    $res['error'] = array();
    $res['success'] = true;

    $theme = filter_input(INPUT_POST,'theme',FILTER_SANITIZE_NUMBER_INT);
    $map_color = filter_input(INPUT_POST,'map_color',FILTER_SANITIZE_NUMBER_INT);
    $zoomLevel = filter_input(INPUT_POST, 'zoomlevel',FILTER_SANITIZE_NUMBER_INT);
    $followTruck = filter_input(INPUT_POST,'followtruck', FILTER_SANITIZE_NUMBER_INT);
    $squery = "UPDATE user_options SET "
            ."tracer_on=$tracerOn, " // ."traffic_on=$trafficOn, "
            ."topbar_on=$topbarOn, "
            ."driverlist_on=$driverlistOn, "
            ."truckinfo_on=$truckinfoOn, "
            ."zoom_level=$zoomLevel, "
            ."chat_on=$chatOn, "
            ."map_color=$map_color,"
            ."theme=$theme, "
            ."follow_truck=$followTruck "
            ."WHERE uid=$uid "
            ."LIMIT 1;";

    if (dbUpdate($squery)){
        if ($editPW == 1) {
            generateUserSession($_SESSION['driverid']);
            logEvent(LOG_TYPE_EVENT,"Modificó sus opciones",$squery);    
        } else {
            logEvent(LOG_TYPE_ADMIN,"Modificó las opciones del usuario ID ".$uid,$squery);
        }
        $res['success'] = true;
        array_push($res['error'], "Opciones actualizadas.");
        echo json_encode($res);
        die();
    }
    $res['success'] = false;
    array_push($res['error'], "Hubo un problema al guardar las opciones.");
    echo json_encode($res);
}

/**
    MENU DE NAVEGACION DE PERFIL DEL USUARIO
**/
function userProfileNavigation($uid, $option = 1){
?>
    <ul class="nav nav-tabs">
    <li role=""><a href="/company/users"><span class='glyphicon glyphicon-chevron-left' aria-hidden='true'></span><b>Conductores</b></a></li>
    <?php
        ($option==2)?$active='class="active"':$active="";
        echo '<li role="presentation" '.$active.'><a href="'.SITE_URL.'/users/view/info/'.$uid.'">Ver usuario</a></li>';            
    if (canEdit($uid)){
        ($option==1)?$active='class="active"':$active="";
        echo '<li role="presentation" '.$active.'><a href="'.SITE_URL.'/users/view/profile/'.$uid.'">Editar perfil</a></li>';
        ($option==3)?$active='class="active"':$active="";
        echo '<li role="presentation" '.$active.'><a href="'.SITE_URL.'/users/view/options/'.$uid.'">Opciones</a></li>';
        ($option==5)?$active='class="active"':$active="";
        echo '<li role="presentation" '.$active.'><a href="'.SITE_URL.'/users/view/telemetry/'.$uid.'">Telemetry</a></li>';
    }
    if (canAdmin($uid)){
        ($option==4)?$active='class="active"':$active="";
        echo '<li role="presentation" '.$active.'><a href="'.SITE_URL.'/users/view/admin/'.$uid.'">Administrar</a></li>';
    }
    ?>
     <li role="button"><a href="<?php echo SITE_URL; ?>/trips/stats/<?php echo $uid;?>"><b>Estad&iacute;sticas</b><span class='glyphicon glyphicon-chevron-right' aria-hidden='true'></span></a></li>
    <?php
    echo '</ul>';
}

/**
PERFIL DEL USUARIO
**/
function layoutUserProfile($uid){
    echo '<div class="control-group col-md-8 col-md-offset-2">';
    userProfileNavigation($uid,1);
    if(!($user = getDriverInfo($uid, true))){
        echo "No existe el usuario.";
        return null;
    }

    global $user_rank_name;
    if (!canEdit($uid)) {echo '<span>No posee permisos para realizar la acci&oacute;n solicitada.</span>';return null;}
    ?>
    <legend>Perfil de <?php echo $user['displayname']; ?></legend>
    <form id="user-form-profile" data-action="<?php echo SITE_URL; ?>/users/updateprofile/<?php echo $uid; ?>" role="form">
        <!-- NOMBRE DE USUARIO - no editable -->
        <div class="form-group">
            <div class="input-group">
                <span class="input-group-addon">Nombre de usuario</span>
                <input type="text" name="drivername" placeholder="Nombre de usuario" class="form-control" required type="text" disabled="disabled" value="<?php echo $user['username']; ?>">
            </div>
        </div>
        <!-- PUESTO - no editable -->
        <div class="form-group">
            <div class="input-group">
                <span class="input-group-addon">Puesto</span>
                <input type="text" name="userrank" class="form-control" required type="text" disabled="disabled" value="<?php echo $user_rank_name[$user['rank']]; ?>">
            </div>
        </div>
        <!-- NOMBRE PARA MOSTRAR -->
        <div class="form-group">
            <div class="input-group">
                <span class="input-group-addon">Nombre para mostrar</span>
                <input name="displayname" placeholder="Nombre o Nick" class="form-control" required type="text" value="<?php echo $user['displayname']; ?>">
            </div>
        </div>
        <!-- NOMBRE COMPLETO -->
        <div class="form-group">
            <div class="input-group">
                <span class="input-group-addon">Nombre completo</span>
                <input name="fullname" placeholder="Nombre completo" class="form-control" required type="text" value="<?php echo $user['fullname']; ?>">
            </div>
        </div>
        <!-- CORREO -->
        <div class="form-group">
            <div class="input-group">
                <span class="input-group-addon">Correo</span>
                <input name="email" placeholder="usuario@ejemplo.com" class="form-control" type="email" value="<?php echo $user['email']; ?>" data-error="Ese email es incorrecto.">
            </div>
            <div class="help-block with-errors"></div>
        </div>
        <!-- FACEBOOK -->
        <div class="form-group">
            <div class="input-group">
                <span class="input-group-addon">Perfil de Facebook</span>
                <input name="facebook" placeholder="http://www.facebook.com/ejemplo" class="form-control" type="url" value="<?php echo $user['facebook']; ?>">
            </div>
        </div>
        <!-- IMAGE -->
        <div class="form-group">
            <div class="input-group">
                <span class="input-group-addon">Im&aacute;gen de perfil</span>
                <input name="imgtruck" placeholder="http://i.imgur.com/miimagen.jpg" class="form-control" type="url" value="<?php echo $user['img_truck']; ?>">
            </div>
        </div>
        <!-- World of Trucks -->
        <div class="form-group">
            <div class="input-group">
                <span class="input-group-addon">Perfil de World of Trucks</span>
                <input name="wotlink" placeholder="https://www.worldoftrucks.com/en/online_profile.php?id=11111" class="form-control" type="url" value="<?php echo $user['wot_profile']; ?>">
            </div>
        </div>
        <div class="alert alert-info"><center>Lo siguiente modif&iacute;quelo si desea cambiar la contrase&ntilde;a</center></div>
        <!-- CONTRASEÑA -->
        <div class="form-group">
            <div class="input-group">
            <span class="input-group-addon">Contrase&ntilde;a actual</span>
            <input name="actualpassword" class="form-control" type="password">
            </div>
        </div>
        <!-- NUEVA CONTRASEÑA -->
        <div class="form-group">
            <div class="input-group">
            <span class="input-group-addon">Nueva contrase&ntilde;a</span>
            <input id="newpassword" name="newpassword" class="form-control" type="password" maxlength="32">
            </div>
        </div>
        <!-- CONFIRMAR CONTRASEÑA -->
        <div class="form-group">
            <div class="input-group">
                <span class="input-group-addon">Repetir contrase&ntilde;a</span>
                <input name="confirmpassword" class="form-control" type="password" maxlength="32" data-match="#newpassword" data-match-error="Las contraseñas no coinciden!">
            </div>
            <div class="help-block with-errors"></div>
        </div>
        <!-- GUARDAR -->
        <div class="form-group">
            <div class="input-group">
                <button id="savebutton" type="submit" name="savebutton" class="btn btn-md btn-success">Guardar</button>
            </div>
        </div>
    </form>
    <?php
}

/**
INFORMACION DEL USUARIO
**/
function layoutUserInfo($uid){        
    global  $user_rank_name;                          
    echo '<div class="control-group col-md-8 col-md-offset-2">';
	userProfileNavigation($uid,2);
    if (!isLoggedIn()){ echo '<span>Solo usuarios registrados pueden ver esta informaci&oacute;n</span>'; return null; }

    if($user = getDriverInfo($uid, true)){
        if ($user['rank'] == USER_RANK_DISABLED){
            echo "Usuario deshabilitado";
            return null;
        }
    }else{
        echo "No existe el usuario.";
        return null;
    }
    ?>
        <legend>Informaci&oacute;n de <?php echo $user['displayname']; ?></legend>
        <div class="row">
            <div class="col-md-4">
                <div class="container-fluid">
                    <figure class="snip1344">
                        <?php
                            if(!empty($user['img_truck'])) {
                        ?>
                        <img src="<?php echo $user['img_truck']; ?>" class="background" height="300"/>
                        <?php }else{ ?>
                        <img src="<?php echo SITE_URL; ?>/avatar/<?php echo $user['country']; ?>/<?php echo getDriverNumber($uid); ?>" class="background"/>
                        <?php } ?>
                        <a href="<?php echo SITE_URL; ?>/avatar/<?php echo $user['country']; ?>/<?php echo getDriverNumber($uid); ?>" data-lightbox="version-1">
                            <img src="<?php echo SITE_URL; ?>/avatar/<?php echo $user['country']; ?>/<?php echo getDriverNumber($uid); ?>" alt="profile-sample1" class="profile"/>
                        </a>                      
                        <figcaption>
                            <h3><?php echo $user['fullname']; ?><span><?php echo $user_rank_name[$user['rank']]; ?></span></h3>
                            <div class="icons">
                                <a href="http://steamcommunity.com/profiles/<?php echo $user['steam_id']; ?>" target="_blank" title="Perfil de Steam"><i class="fa fa-steam"></i></a>
                                <?php
                                if(!empty($user['email']))
                                    echo '<a href="mailto:'.$user['email'].'" title="Enviar email"> <i class="fa fa-envelope-o"></i></a>';
                                ?>
                                <?php
                                if(!empty($user['facebook']))
                                    echo '<a href="'.$user['facebook'].'" target="_blank" title="Perfil de Facebook"> <i class="fa fa-facebook-square"></i></a>';
                                ?>
                                <?php
                                if(!empty($user['wot_profile']))
                                    echo '<a href="'.$user['wot_profile'].'" target="_blank"title="Perfil de World of Trucks"> <i class="fa fa-truck"></i></a>';
                                ?>                                
                            </div>
                        </figcaption>
                    </figure>
                </div>
                <div class="container-fliud">
                    <dl class="dl-horizontal">
                    <?php
                        if (canSupervise($uid)){
                            ?>
                        <dt>Registrado:</dt><dd><?php echo date("H:i:s \d\e\l d/m/Y", strtotime($user['registered'])) ?></dd>
                        <dt>Ultimo incio de sesi&oacute;n:</dt><dd><?php echo date("H:i:s \d\e\l d/m/Y", strtotime($user['last_seen'])); ?></dd>
                        <dt>Ultima vez en viaje:</dt><dd><?php echo date("H:i:s \d\e\l d/m/Y", strtotime($user['last_onroad'])); ?></dd>
                    <?php
                        }
                    ?>
                    </dl>
                </div>
            </div>
            <div class="col-md-8">
                <div class="text-center">
                    <h4>&Uacute;ltimos 10 viajes</h4>
                </div>

                <div class="container-fluid">
                    <canvas id="tenlasttrips" height="120"></canvas>
                </div>
                <script>
                    $( document ).ready(function() {
                        var options = {
                            showScale: true,
                            pointDot : true,
                            scaleBeginAtZero: true,
                            scaleShowGridLines: false,
                            responsive: true
                        };
                        $.ajax({
                            type: "POST",
                            url: "<?php echo SITE_URL; ?>" + "/getinfo/lasttentrips/" + "<?php echo $uid; ?>",
                            dataType: 'json',
                            cache: false,
                            success: function (data) {
                                lineChartData = data[0];
                                var ctx = document.getElementById("tenlasttrips").getContext("2d");
                                window.myLine = new Chart(ctx).Line2Y(lineChartData, options);
                            } 
                        });
                    });
                </script>
            </div>
        </div>
    </div>
    <?php
} 

/** 
OPCIONES DE USUARIO
**/
function layoutUserOptions($uid){  
    echo '<div class="control-group col-md-8 col-md-offset-2">';
    userProfileNavigation($uid,3);
    if($user = getDriverInfo($uid, true)){
        if ($user['rank'] == USER_RANK_DISABLED){
            echo "Usuario deshabilitado";
            return null;
        }
    }else{
        echo "No existe el usuario.";
        return null;
    }

    global $map_colors;
    global $site_themes;

    $signURL = SITE_URL."/sign/img/".$uid.".png";
    if (!canEdit($uid)) {echo '<span>No posee permisos para realizar la acci&oacute;n solicitada.</span>';return null;}
    echo '<legend>Opciones de '.getDriverName($uid).'</legend>';       
    $options = getDriverOptions ($uid);
    $drivers = listDrivers(true,0,200);        
?>
    <div>
        <form id="user-form-option" data-action="<?php echo SITE_URL;?>/users/saveoptions/<?php echo $uid; ?>">
            <div class="row text-center">
                <!--<div class="col-md-2">
                    <?php // ($options['traffic_on'])?$checked='checked="checked"':$checked=''; ?>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="trafficOn" <?php // echo $checked; ?> value=1>Mostrar tr&aacute;fico
                    </label>
                </div>-->
                <div class="col-md-2">
                    <?php ($options['tracer_on'])?$checked='checked="checked"':$checked=''; ?>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="tracerOn" <?php echo $checked; ?> value=1>Mostrar seguimiento
                    </label>
                </div>
                <div class="col-md-2">
                    <?php ($options['topbar_on'])?$checked='checked="checked"':$checked=''; ?>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="topbarOn" <?php echo $checked; ?> value=1>Mostrar barra superior
                    </label>
                </div>
                <div class="col-md-3">
                    <?php ($options['driverlist_on'])?$checked='checked="checked"':$checked=''; ?>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="driverlistOn" <?php echo $checked; ?> value=1>Mostrar conductores en l&iacute;nea
                    </label>
                </div>
                <div class="col-md-3">
                    <?php ($options['truckinfo_on'])?$checked='checked="checked"':$checked=''; ?>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="truckinfoOn" <?php echo $checked; ?> value=1>Mostrar informaci&oacute;n del cami&oacute;n
                    </label>
                </div>
                <div class="col-md-2">
                    <?php ($options['chat_on'])?$checked='checked="checked"':$checked=''; ?>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="chatOn" <?php echo $checked; ?> value=1>Mostrar chat
                    </label>
                </div>
            </div>        
            <br>
            <div class="row">
                <div class="form-group col-md-2">
                    <div class="input-group">
                        <span class="input-group-addon">Zoom</span>
                        <select class="form-control" name="zoomlevel" required>
                        <?php        
                            for ($i = MIN_ZOOM_LEVEL; $i <= MAX_ZOOM_LEVEL; $i++) {
                                ($i == $options['zoom_level'])?$selected='selected=""':$selected='';
                                    echo '<option value='.$i.' '.$selected.'">'.$i.'</option>';
                            }
                        ?>
                        </select>
                    </div>
                </div>                
                <div class="form-group col-md-4">
                    <div class="input-group">
                        <span class="input-group-addon">Por defecto, seguir a</span>
                        <select class="form-control" name="followtruck" required>
                        <option value=0 selected="">[Nadie]</option>
                        <?php 
                            foreach ($drivers as $drv) {
                                ($drv['id'] == $options['follow_truck'])?$selected='selected=""':$selected='';            
                                echo '<option value='.$drv['id'].' '.$selected.'">'.$drv['fullname'].'</option>';            
                            }
                        ?>
                        </select>
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <div class="input-group">
                        <span class="input-group-addon">Color del mapa</span>
                        <select class="form-control" name="map_color" required>    
                        <?php 
                            foreach ($map_colors as $i => $t) {
                                ($i == $options['map_color'])?$selected='selected=""':$selected='';            
                                echo '<option value='.$i.' '.$selected.'">'.$t.'</option>';            
                            }        
                        ?>
                        </select>
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <div class="input-group">
                        <span class="input-group-addon">Tema del sitio</span>
                        <select class="form-control" name="theme" id="themeselected" required>    
                        <?php
                        foreach ($site_themes as $i => $t) {
                            ($i == $options['theme'])?$selected='selected=""':$selected='';            
                            echo '<option value='.$i.' '.$selected.'">'.$t.'</option>';            
                        }        
                        ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="input-group col-md-12 text-center">
                <button id="savebutton" name="savebutton" type="submit" class="btn btn-md btn-success">Guardar</button>
            </div>
        </form>
    </div>
    <br>
    <span>Puedes utilizar esta imagen como firma en los foros, la misma se actualiza autom&aacute;ticamente con tus estad&iacute;sticas:</span>
    <br><br>
    <div class="input-group">
        <span class="input-group-addon">URL de la firma</span>
        <input name="signatureURL" readonly="" class="form-control" type="text" value="<?php echo $signURL; ?>" onclick="javascript:this.select();">
        <span class="input-group-addon">
            <a href="<?php echo $signURL; ?>" target="_blank">Ver firma</a>
        </span>
    </div>
    <img class="img-signpreview img-centered" src="<?php echo $signURL; ?>">
<?php
}

/**
    OPCIONES DE TELEMETRY PARA EL USUARIO
**/
function layoutTelemetryOptions($uid){
    $editPW = canEdit($uid);
    if (!$editPW) {echo '<span>No posee permisos para realizar la acci&oacute;n solicitada.</span>';return null;}
    ?>
    <div class="control-group col-md-8 col-md-offset-2">
        <?php userProfileNavigation($uid,5); ?>
    <?php
    if($user = getDriverInfo($uid, true)){
        if ($user['rank'] == USER_RANK_DISABLED){
            echo "Usuario deshabilitado";
            return null;
        }
    }else{
        echo "No existe el usuario.";
        return null;
    }
?>
        <legend><?php echo getDriverName($uid); ?>: Telemetry</legend>
        <div class="row">
            <div class="col-md-6">
                <div class="thumbnail">
                    <div class="caption" style="padding-top:0; padding-bottom:0;">
                        <h3>Versi&oacute;n 1</h3>
                    </div>
                    <a href="<?php echo SITE_URL; ?>/img/version1.png" data-lightbox="version-1"><img src="<?php echo SITE_URL; ?>/img/version1.png" class="img-thumbnail"></a>
                    <a href="<?php echo SITE_URL; ?>/download/gettelemetry/1" target="_blank">
                        <button type="button" class="btn btn-md btn-info center-block"><i class="fa fa-download"></i> Descargar Telemetry</button>
                    </a>
                </div>
            </div>
            <div class="col-md-6">
                <div class="thumbnail">
                    <div class="caption" style="padding-top:0; padding-bottom:0;">
                        <h3>Versi&oacute;n 2</h3>
                    </div>
                    <a href="<?php echo SITE_URL; ?>/img/version2.png" data-lightbox="version-2"><img src="<?php echo SITE_URL; ?>/img/version2.png" class="img-thumbnail"></a>
                    <a href="<?php echo SITE_URL; ?>/download/gettelemetry/2" target="_blank">
                        <button type="button" class="btn btn-md btn-info center-block"><i class="fa fa-download"></i> Descargar Telemetry</button>
                    </a>
                </div>
            </div>
        </div>
        <div class="panel panel-info">
          <div class="panel-heading">
            <h3 class="panel-title">Informaci&oacute;n de uso
                <i class="fa fa-info pull-right"></i>
            </h3>
          </div>
          <div class="panel-body">
            <ul class="fa-ul">
                <li><i class="fa-li fa fa-arrow-right"></i>Descomprima el archivo telemetry.zip y ejecute el archivo "setup.exe".</li>
                <li><i class="fa-li fa fa-arrow-right"></i>Para poder utilizar el telemetry se debe descargar el <button type="button" class="btn btn-xs btn-info" data-toggle="modal" data-target="#modal-dialog">UserData.dat</button></li>
                <li><i class="fa-li fa fa-arrow-right"></i>Se puede usar cualquiera de las dos versiones del Telemetry.</li>
                <li><i class="fa-li fa fa-arrow-right"></i>Se debe tener instalado Microsoft .NET Framework 4.5. <a href="https://www.microsoft.com/es-ar/download/details.aspx?id=30653" target="_blank"><button type="button" class="btn btn-xs btn-info">Descargar</button></a></li>
                <li><i class="fa-li fa fa-arrow-right"></i>Ante cualquier inconveniente puede comunicarse mediante el canal <a href="https://l-latam.slack.com/messages/soporte/" target="_blank" class="table-a">#Soporte</a> en Slack.</li>
            </ul>
          </div>
        </div>
    </div>

    <!-- Modal para descargar .dat -->
    <div class="modal fade" tabindex="-1" role="dialog" id="modal-dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Generar configuraci&oacute;n del telemetry</h4>
                </div>
                <div class="modal-body">
                    <span>Para generar el archivo de configuraci&oacute;n del telemetry, ingrese su contraseña y haga click en Generar. Esto invalidar&aacute; cualquier archivo anterior.</span>
                    <br><br>                
                    <form id="user-form" method="POST" action="<?php echo SITE_URL; ?>/download/getuserdata/<?php echo $uid; ?>" target="_blank">
                        <div class="input-group">
                            <span class="input-group-addon">Contrase&ntilde;a</span>
                            <input id="tpassword" name="tpassword" placeholder="Contrase&ntilde;a" class="form-control" type="password">
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary pull-right" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-danger btn-modal pull-right">Generar</a></button>
                </div>
                </form>
            </div>
        </div>
    </div>
<?php
}

/**
ADMININSTRACIÓN DE USUARIO
**/
function layoutUserAdmin($uid) {
    global  $user_rank_name;
    /* CONTROL DE ACCESO */
    if (!canAdmin($uid)){ echo '<span>No posee permisos para realizar la acci&oacute;n solicitada.</span>'; return null; }
    echo '<div class="control-group col-md-8 col-md-offset-2">';
    userProfileNavigation($uid,4);
    if(!($user = getDriverInfo($uid))){
        echo "No existe el usuario.";
        return null;
    }
    ?>
    <legend>Administraci&oacute;n del usuario: <?php echo $user['displayname']; ?></legend>
    <form id="user-form-admin" role="form" data-action="<?php echo SITE_URL; ?>/users/saveadminprofile/<?php echo $uid ?>">
        <!-- NOMBRE DE USUARIO -->
        <div class="form-group">
            <div class="input-group">
                <span class="input-group-addon">Nombre de usuario</span>
                <input type="text" id="drivername" name="drivername" placeholder="Nombre de usuario" class="form-control" required disabled type="text" value="<?php echo $user['username']; ?>">
            </div>
        </div>
        <!-- Puesto -->
        <div class="form-group">
            <div class="input-group">
                <span class="input-group-addon">Puesto</span>
                <select class="form-control" name="rank" required>
                    <?php
                        foreach ($user_rank_name as $idx => $name) {
                            ($idx == $user['rank'])?$selected='selected=""':$selected='';
                            if ($idx < $_SESSION['rank'])
                                echo '<option value='.$idx.' '.$selected.'">'.$name.'</option>';
                        }
                    ?>
                </select>
            </div>
        </div>
        <?php
            if ($user['rank'] >= USER_RANK_DRIVER) {
                // Busco números libres
                $squery = "SELECT id FROM driversnumbers WHERE uid = 0;";
                if($result=dbSelect($squery))
                    while($row = mysqli_fetch_assoc($result))
                        $freepos[] = $row;
        ?>
            <!-- Número de conductor -->
            <div class="form-group">
                <div class="input-group">
                    <span class="input-group-addon">Número en la empresa</span>
                    <select class="form-control" name="number" required>
                    <option selected value="<?php echo getDriverNumber($uid); ?>"><?php echo getDriverNumber($uid); ?></option>
                    <?php
                        foreach ($freepos as $free)
                            echo "<option value=".$free['id'].">".$free['id']."</option>";
                    ?>
                    </select>
                </div>
            </div>
        <?php
            }
        ?>
        <!-- ID DE ETS2MP -->
        <div class="form-group">
            <div class="input-group">
                <span class="input-group-addon">ID de ETS2MP</span>
                <input type="text" id="mpid" name="mpid" placeholder="ETS2MP ID" class="form-control" type="text" value="<?php echo $user['mp_id']; ?>">
            </div>
        </div>
        <!-- ID DE STEAM-->
        <div class="form-group">
            <div class="input-group">
                <span class="input-group-addon">ID de Steam (steamID64)</span>
                <input type="text" id="steamid" name="steamid" placeholder="STEAM ID" class="form-control" required type="text" value="<?php echo $user['steam_id']; ?>">
            </div>
        </div>
        <!-- NUEVA CONTRASEÑA -->
        <div class="form-group">
            <div class="input-group">
                <span class="input-group-addon">Nueva contrase&ntilde;a</span>
                <input id="newpassword" name="newpassword" class="form-control" type="password">
            </div>
        </div>
        <div class="form-group">
            <div class="input-group">
                <span class="input-group-addon">Repetir contrase&ntilde;a</span>
                <input id="confirmpassword" name="confirmpassword" class="form-control" type="password" maxlength="32" data-match="#newpassword" data-match-error="Las contrase&ntildes;as no coinciden!">
            </div>
            <div class="help-block with-errors"></div>
        </div>
        <!-- SUBMIT -->
        <div class="form-group">
            <div class="input-group">
                <button type="submit" id="savebutton" name="savebutton" class="btn btn-md btn-success">Guardar</button>
            </div>
        </div>
    </form>
<?php
}
?>