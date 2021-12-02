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
}

$action = filter_input(INPUT_GET, 'a', FILTER_SANITIZE_STRING);

switch ($action) {
	case 'dologin':
		$driver = filter_input(INPUT_POST, 'drivername', FILTER_SANITIZE_STRING);
        $driver = str_replace(' ', '', $driver); // Evito los espacios
		$pass = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
        $remember = filter_input(INPUT_POST, 'rememberme',FILTER_SANITIZE_NUMBER_INT); 
		tryLogin(strtolower($driver),$pass,$remember);	
		break;
    case 'doregister':
        $driver = filter_input(INPUT_POST, 'drivername', FILTER_SANITIZE_STRING);
        $driver = str_replace(' ', '', $driver); // Evito los espacios
        $fullname = filter_input(INPUT_POST,'driverfullname',FILTER_SANITIZE_STRING);
        $fblink = filter_input(INPUT_POST,'driverfb',FILTER_SANITIZE_URL);
        $pass = filter_input(INPUT_POST, 'inputPassword', FILTER_SANITIZE_STRING);
        $repass = filter_input(INPUT_POST, 'inputPasswordConfirm', FILTER_SANITIZE_STRING);
        date_default_timezone_set("America/Argentina/Buenos_Aires");
        $birth = filter_input(INPUT_POST, 'driverage');
        $birthdate = DateTime::createFromFormat('d/m/Y', $birth);
        $birthdatesql = date_format ( $birthdate, 'Y-m-d' );
        $email = filter_input(INPUT_POST,'driveremail',FILTER_SANITIZE_EMAIL);
        $pais = filter_input(INPUT_POST, 'drivercountry', FILTER_SANITIZE_STRING);
        $otravtc = filter_input(INPUT_POST, 'drivervtc', FILTER_SANITIZE_STRING);
        $whytext = filter_input(INPUT_POST, 'driverwhy', FILTER_SANITIZE_STRING);
        $steamid = filter_input(INPUT_POST, 'driversteamid', FILTER_SANITIZE_NUMBER_INT);
        $res = array();
        $res['error'] = array();
        $res['success'] = true;
        
        if(strlen($driver)<MIN_USERNAME_LENGTH){
            $res['success'] = false;
            array_push($res['error'], "El nombre de usuario debe tener al menos ".MIN_USERNAME_LENGTH." caracteres.");
        }// Compruebo que no exista un usuario con el mismo username
        if ($userData = getLoginInfo($driver)){
            $res['success'] = false;
            array_push($res['error'], "El nombre de usuario elegido ya existe!");
        }
        if($pass!=$repass){
            $res['success'] = false;
            array_push($res['error'], "Las contraseñas no son iguales!");
        }else if ($pass == $repass && strlen($pass)<MIN_PASSWORD_LENGTH){
            $res['success'] = false;
            array_push($res['error'], "La contraseña debe tener un mínimo de ".MIN_PASSWORD_LENGTH." caracteres.");
        }
        if($res['success'] == false){
            echo json_encode($res);
            die();
        }
        $pass = md5($pass);
        if ($conn = dbOpen()){
            // Me fijo si existe algún usuario en la tabla, sino existe lo creo como el desarrollador del sitio.
            $squery = "SELECT COUNT(1) FROM drivers";
            $result = mysqli_query($conn,$squery);
            $row = mysqli_fetch_array($result);
            if($row[0]==0){
                $squery = "INSERT INTO drivers (username,realusername,password,displayname,fullname,registered,rank,email,steam_id) VALUES ('".strtolower($driver)."','$driver','$pass','$driver','$fullname',CURRENT_TIMESTAMP(),'".USER_RANK_DEVELOPER."','$email','$steamid');";
                if ($newId = dbInsert($squery)){
                    $squery = "INSERT INTO trucks (owner) VALUES ($newId)";
                    if (dbInsert($squery)){
                        $squery = "INSERT INTO user_options (uid) VALUES ($newId)";
                        if (dbInsert($squery)){
                            $squery = "INSERT INTO recruits (uid, facebook, birthdate, country, othervtc, whylatam, isDriver) VALUES ('$newId', '$fblink', '$birthdatesql', '$pais', '$otravtc', '$whytext','1');";
                            if(dbInsert($squery)){
                                $secretid=generateNewSecret($newId);
                                generateCookie($driver,$secretid);                        
                                generateUserSession($newId);
                                $res['success'] = true;
                            }else{
                                $res['success'] = false;
                                array_push($res['error'], "No se pudo crear el usuario.");
                            }
                        }else{
                                $res['success'] = false;
                                array_push($res['error'], "No se pudo crear el usuario.");
                        }
                    } else {
                            $res['success'] = false;
                            array_push($res['error'], "No se pudo crear el usuario.");
                    }
                }
            }else{
                // Ya existen usuarios, registro a un recluta
                $squery = "INSERT INTO drivers (username,realusername,password,displayname,fullname,registered,rank,email,steam_id) VALUES ('".strtolower($driver)."','$driver','$pass','$driver','$fullname',CURRENT_TIMESTAMP(),'".USER_RANK_RECRUIT."','$email','$steamid');";
                if ($newId = dbInsert($squery)){
                    $squery = "INSERT INTO trucks (owner) VALUES ($newId)";
                    if (dbInsert($squery)){
                        $squery = "INSERT INTO user_options (uid) VALUES ($newId)";
                        if (dbInsert($squery)){
                            $squery = "INSERT INTO recruits (uid, facebook, birthdate, country, othervtc, whylatam, isDriver) VALUES ('$newId', '$fblink', '$birthdatesql', '$pais', '$otravtc', '$whytext','0');";
                            if(dbInsert($squery)){
                                $secretid=generateNewSecret($newId);
                                generateCookie($driver,$secretid);                        
                                generateUserSession($newId);
                                $res['success'] = true;
                                $res['showagreed'] = true;
                            }else{
                                $res['success'] = false;
                                array_push($res['error'], "No se pudo crear el usuario.");
                            }
                        } else {
                            $res['success'] = false;
                            array_push($res['error'], "No se pudo crear el usuario.");
                        }
                    } else {
                        $res['success'] = false;
                        array_push($res['error'], "No se pudo crear el usuario.");
                    }
                } else {
                    $res['success'] = false;
                    array_push($res['error'], "No se pudo crear el usuario.");
                }
            }
        }
        echo json_encode($res);
        break;
    case 'agree':
        $resultado = agreeTerms($_SESSION['driverid']);
        echo json_encode(array("status" => $resultado));
        break;
    case 'agreesteam':
        agreeTerms($_SESSION['driverid']);
        gotoUrl("/");
        break;        
	case 'logout':
		session_destroy();            
        setcookie(KEEPALIVE_COOKIE_NAME, "", time() - 3600);        
		gotoUrl("/login",MSG_TYPE_SUCCESS,"Sesi&oacute;n finalizada, vuelve pronto!");
		break;	
	default:
        require 'inc/openid.php';
        $_STEAMAPI = "E37988170699985E2F5CABE1982F000E";
        try 
        {
            $openid = new LightOpenID(SITE_URL.'/login');
            if(!$openid->mode) 
            {
                if(isset($_GET['steamLogin'])) 
                {
                    $openid->identity = 'http://steamcommunity.com/openid/?l=spanish';
                    header('Location: ' . $openid->authUrl());
                }
                $scripts = array();
                $scripts[] = "jquery.backstretch.min.js";
                $scripts[] = "validator.min.js";
                $scripts[] = "loginregister.js";
                layoutHead("Ingresar");
                layoutInitBody();
                layoutInitWrapper();
                layoutTopbar("company");
                layoutInitContent();
                layoutLoginForm();
                layoutEndContent();
                layoutEndWrapper();
                layoutAddDefaultScript($scripts);
                layoutEndBody();
            } 
            elseif($openid->mode == 'cancel') 
            {
                gotoUrl("/login",MSG_TYPE_ERROR,"El usuario cancel&oacute; el inicio de sesi&oacute;n.");
            } 
            else 
            {
                if($openid->validate()) 
                {
                    $id = $openid->identity;
                    $ptn = "/^http:\/\/steamcommunity\.com\/openid\/id\/(7[0-9]{15,25}+)$/";
                    preg_match($ptn, $id, $matches);
                    // Obtengo datos del jugador a partir del Steam ID
                    $url = "http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=$_STEAMAPI&steamids=$matches[1]";
                    $json_object= file_get_contents($url);
                    $json_decoded = json_decode($json_object);

                    foreach ($json_decoded->response->players as $player)
                    {
                        $fallbackURL = "/login";
                        $squery = "SELECT id, username, secret, rank FROM drivers WHERE steam_id=$player->steamid LIMIT 1";
                        if ($result = dbSelect($squery)) {
                            if ($row = mysqli_fetch_array($result)){
                                // Existe en la DB, ingreso
                                if ($row['rank'] > USER_RANK_DISABLED) {    
                                    generateCookie($row['username'],$row['secret']);
                                    generateUserSession($row['id']);                     
                                    if (!$_SESSION['agreed']){
                                        showWarnMsg();
                                    }else {
                                        gotoUrl("/");   
                                    }                
                                } else {
                                    gotoUrl($fallbackURL,MSG_TYPE_ERROR,"La cuenta de usuario esta deshabilitada, contacte al administrador.");                    
                                }
                            }else{
                                // No existe en la DB, le presento formulario para postularse como recluta/primer registro
                                $scripts = array();
                                $scripts[] = "bootstrap-datepicker.min.js";
                                $scripts[] = "bootstrap-datepicker.es.js";
                                $scripts[] = "datepicker.js";
                                $scripts[] = "jquery.backstretch.min.js";
                                $scripts[] = "validator.min.js";
                                $scripts[] = "loginregister.js";
                                $css = array();
                                $css[] = "bootstrap-datepicker.min.css";
                                $css[] = "bootstrap-datepicker3.min.css";

                                layoutHead("Registrarse como recluta",$css);
                                layoutInitBody();
                                layoutInitWrapper();
                                layoutTopbar("company");
                                layoutInitContent();
                                layoutRecruitForm($player->steamid);
                                layoutEndContent();
                                layoutEndWrapper();
                                layoutAddDefaultScript($scripts);
                                layoutEndBody();
                            }
                        }
                    }
                } 
                else 
                {
                    gotoUrl("/login",MSG_TYPE_ERROR,"El usuario no se encuentra conectado.");
                }
            }
        }
        catch(ErrorException $e)
        {
            echo $e->getMessage();
        }
		break;
}

function tryLogin($username, $pass, $remember = 0) {    
     $res = array();
     if ($userData = getLoginInfo($username)){        
        if (MD5($pass) == $userData['password']){
            if ($userData['rank'] > USER_RANK_DISABLED) {    
                if ($remember == "1") {
                    generateCookie($username,$userData['secret']);
                }                  
                generateUserSession($userData['id']);                     
                if (!$_SESSION['agreed']){
                    $res['success'] = true;
                    $res['showagreed'] = true;
                }else {
                    $res['success'] = true;
                    $res['user'] = $userData['fullname'];
                }
            } else {
                $res['success'] = false;
                $res['error'] = "La cuenta de usuario esta deshabilitada, contacte al administrador.";
            }
        } else {
            $res['success'] = false;
            $res['error'] = "Contraseña incorrecta.";
        }
    } else {
        $res['success'] = false;
        $res['error'] = "Nombre de usuario incorrecto.";
    }
    echo json_encode($res);
}

function showWarnMsg(){
      layoutHead("Aviso");
        layoutInitBody();
        layoutInitWrapper();
        layoutTopbar("company");
        layoutInitContent();
        ?>
        <div class="control-group col-md-8 col-md-offset-2">
            <legend>Aviso para todos los conductores</legend>
            <span>
                <p>Compa&ntilde;eros, les comunicamos que por decision de la direcci&oacute;n de la empresa y con el fin de evitar competencias malsanas e injustas, asi como tambi&eacute;n en pos de favorecer el compa&ntilde;erismo compartimos desde un principio, hemos decidido que:</p>
                <ul>
                    <li>
                        <p>El ranking de viajes que se encuentra en <a href="http://map.logisticalatinoamericana.com/ranking/view">http://map.logisticalatinoamericana.com/ranking/view</a> solo contendr&aacute; estad&iacute;sticas del mes en curso. <b>Por lo que al principio de cada mes, todos los conductores estaran en cero en los rankings.</b></p>
                    </li>
                    <li>                    
                        <p>A los conductores que pudiendo jugar en compa&ntilde;&iacute;a de otros miembros de la empresa decidan jugar solos para poder mejorar sus estad&iacute;sticas y demuestren ser reincidentes en este comportamiento, <b>se les advierte el staff de la empresa no está a favor de este modo de juego.</b> Es por esto que apelamos a vuestro sentido com&uacute;n para que sigamos siendo una empresa de compa&ntilde;eros y no de competidores. </p>
                    </li>
                    <li>
                        <p>Los viajes que tengan valores inadecuados ya sea por el uso de mods, o por el abuso de las caracter&iacute;sticas del sistema, ser&aacute;n eliminados por el staff de Log&iacute;stica Latinoamericana sin previo aviso</p><p>En caso de creer que un viaje no debe ser borrado, comunicarlo al staff con pruebas fehacientes y n&uacute;mero de ID de viaje (visible en la barra de direcciones)</p>
                    </li>
                    <li>
                        <p>La reiteraci&oacute;n y/o alevos&iacute;a de los comportamientos mencionados arriba puede ser motivo de deshabilitar los registros de viaje del conductor</p>
                    </li>
                </ul>
                <p>Esperamos que comprendan el motivo de esta decisión y nos hagan llegar por v&iacute;a privada cualquier inquietud que tengan al respecto.</p>
            </span>
            <a href="<?php printURL("/agreesteam");?>"><button class="btn btn-success">Acepto</button></a>
            <a href="<?php printURL("/logout");?>"><button class="btn btn-danger">No acepto</button></a>
        </div>
        <?php
        layoutEndContent();
        layoutEndWrapper();
        layoutAddDefaultScript();
        layoutEndBody();
}
?>
