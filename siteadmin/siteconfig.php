<?php
session_start();
require_once(__DIR__.'/../inc/config.php');
require_once(__DIR__.'/../inc/model.php');
require_once(__DIR__.'/../inc/layout.php');
require_once(__DIR__.'/../inc/functions.php');
require_once(__DIR__.'/nav_layout.php');

if (!isDeveloper()){
	gotoUrl("/",MSG_TYPE_ERROR,"Solo para personal autorizado");	
	die;
}
// GET PARAMS
$p = filter_input(INPUT_GET,'p',FILTER_SANITIZE_NUMBER_INT);
$action = filter_input(INPUT_GET, 'a', FILTER_SANITIZE_STRING);
$scripts = array();

switch ($action) {
	case 'save':
		saveSiteConfig();
		die;
	case 'delete':
		deleteUsers();
		die;
	case 'updateservers':
		updateServers();
		die;		
}

layoutHead("Administraci&oacute;n");
layoutInitBody();
layoutInitWrapper();
layoutTopbar("siteadmin");                                     
layoutInitContent();    

switch ($action) {	
	case 'edit':
		layoutSiteConfig();
		break;
}

layoutEndContent();
layoutEndWrapper();         
layoutAddDefaultScript($scripts);
layoutEndBody();


function printChecked($value){
	if ($value){
		echo 'checked="checked"';
	}
}

/**
 	PANEL DE CONFIGURACION DEL SITIO
 **/ 
function layoutSiteConfig(){
	global  $user_rank_name;
	$clientlifetime = CLIENT_SESSION_LIFETIME / 3600;
	$SQLPHPTime = SQL_PHP_TIME / 3600;
	?>
    <div class="control-group col-md-8 col-md-offset-2">
	<?php adminNavigation("config"); ?>
	    <legend>Configuraci&oacute;n del sitio</legend>
	   	<form id="user-form" method="POST" action="<?php printURL("/admin/config/save");?>">
		   	<div class="row text-center">
			   	<div class="col-md-4">
					<label class="checkbox-inline"><input type="checkbox" name="debugOn" <?php printChecked(SITE_DEBUG_ON);?> value=1>Modo debug</label>
				</div>
				<div class="col-md-3">					
					<label class="checkbox-inline"><input type="checkbox" name="localServer" <?php printChecked(IS_IN_LOCAL_SERVER);?> value=1>Servidor local</label>
				</div>
				<div class="col-md-5">
					<label class="checkbox-inline"><input type="checkbox" name="findETS2MAP" <?php printChecked(FIND_DRIVERS_ETSMAP);?> value=1>Buscar conductores en ETS2MAP</label>
				</div>
			</div>
			<br>
		    <div class="input-group">
		    	<span class="input-group-addon">Heartbeat cliente telemetry (segs)</span>
		    	<input type="number" name="clientUpdateInterval" placeholder="Valor en segundos" class="form-control" value="<?php echo CLIENT_UPDATE_INTERVAL; ?>">
		    </div>
		    <br>    
		    <div class="input-group">
				<span class="input-group-addon">Cami&oacute;n en el mapa TTL (segs)</span>
				<input type="number" name="truckOnMapLifetime" placeholder="Valor en segundos" class="form-control" value="<?php echo TRUCK_ON_MAP_LIFETIME; ?>">
		    </div>
		    <br>
		    <div class="input-group">
				<span class="input-group-addon">Telemetry TTL (segs)</span>
				<input type="number" name="truckTelemetryLifetime" placeholder="Valor en segundos" class="form-control" value="<?php echo TRUCK_TELEMETRY_LIFETIME; ?>">
		    </div>
		    <br>
		    <div class="input-group">
				<span class="input-group-addon">URL del sitio</span>
				<input type="text" name="siteURL" placeholder="Ingrese URL completa" class="form-control" value="<?php echo SITE_URL;?>">
		    </div>
		    <br>
		    <div class="input-group">
				<span class="input-group-addon">Sesion del cliente TTL (hs)</span>
				<input type="text" name="clientSessionLifetime" placeholder="Valor en horas" class="form-control" value="<?php echo $clientlifetime;?>">
			</div>	
			<br>
		    <div class="input-group">
		    	<span class="input-group-addon">Diferencia entre PHP Y Mysql (hs)</span>
				<input type="text" name="SQLPHPTime" placeholder="Valor en horas" class="form-control" value="<?php echo $SQLPHPTime; ?>">
		    </div>
			<br>
		    <div class="input-group">
		    	<span class="input-group-addon">Longitud m&iacute;nima para el nombre de usuario</span>
				<input type="number" name="userLengthMin" placeholder="Valor entero" class="form-control" value="<?php echo MIN_USERNAME_LENGTH; ?>">
		    </div>
			<br>
		    <div class="input-group">
		    	<span class="input-group-addon">Longitud m&iacute;nima para la contrase&ntilde;a</span>
				<input type="number" name="passLengthMin" placeholder="Valor entero" class="form-control" value="<?php echo MIN_PASSWORD_LENGTH; ?>">
		    </div>
		    <br>
		    <div class="input-group">
		    	<span class="input-group-addon">Cantidad de números para otorgar a conductores:</span>
				<input type="number" name="numberDrivers" placeholder="Valor entero" class="form-control" value="<?php echo NUMBER_DRIVERS; ?>">
		    </div>
		    <br>
		    <div class="input-group">
		    	<span class="input-group-addon">Kil&oacute;metros m&iacute;nimos para registrar un viaje:</span>
				<input type="number" name="minkm" placeholder="Valor entero" class="form-control" value="<?php echo MIN_KILOMETERS; ?>">
		    </div>
		    <br>
		    <div class="input-group">
		    	<span class="input-group-addon">Servidores en la base de datos:</span>
				<select class="form-control">
					<?php
						if($serversdb = getServersDB()){
							foreach ($serversdb as $sdb){
								echo "<option>".$sdb['name']." (".$sdb['game'].")";
								if ($sdb['limiter']) echo " (limitador)";
								echo "</option>";
							}
						}else{
							echo "<option>No hay servidores</option>";
						}
					?>
				</select>
		    </div>
		    <br>
		    <div class="alert alert-warning"><center>Modificar los siguientes par&aacute;metros puede causar que el sitio deje de funcionar</center></div>
		    <div class="row">
		    	<div class="form-group col-md-3">
				    <div class="input-group">
				    	<span class="input-group-addon">Host de la DB</span>
				    	<input type="text" name="DBHostname" placeholder="Host de la base de datos" class="form-control" value="<?php echo DB_HOSTNAME; ?>">
				    </div>
			    </div>
			    <div class="form-group col-md-3">
				    <div class="input-group">
				    	<span class="input-group-addon">Nombre de la DB</span>
				    	<input type="text" name="DBName" placeholder="Nombre de la base de datos" class="form-control" value="<?php echo DB_NAME; ?>">
				    </div>
			    </div>
			    <div class="form-group col-md-3">
				    <div class="input-group">
				    	<span class="input-group-addon">Usuario de la DB</span>
				    	<input type="text" name="DBUserName" placeholder="Usuario de la base de datos" class="form-control" value="<?php echo DB_USERNAME; ?>">
				    </div>
				</div>
				<div class="form-group col-md-3">
				    <div class="input-group">
				    	<span class="input-group-addon">Contrase&ntilde;a de la DB</span>
				    	<input type="password" name="DBPassword" placeholder="Contrase&ntilde;a de la base de datos" class="form-control" value="<?php echo DB_PASSWORD; ?>">
				    </div>
			    </div>
		    </div>
		    <div class="row">
		    	<div class="col-md-2">
					<button type="submit" id="savebutton" name="savebutton" class="btn btn-md btn-success">Guardar</button>
				</div>
				<div class="col-md-10">
				    <a href="<?php printURL("/admin/config/delete"); ?>">
						<button type="button" id="flushbutton" name="flushbutton" class="btn btn-md btn-danger pull-right">Eliminar conductores deshabilitados</button>
					</a>
					<a href="<?php printURL("/admin/config/updateservers"); ?>">
						<button type="button" id="updateServers" name="updateServers" class="btn btn-md btn-info pull-right">Actualizar servidores</button>
					</a>
				</div>
			</div>
    	</form>
    	<br>
    </div>
  	<?php
}

/** 
	GUARDAR CONFIGURACION DEL SITIO
**/
function saveSiteConfig(){
	$returnUrl = "admin/config/edit";
	if (!isDeveloper()) {echo "No esta autorizado a realizar la acci&oacute;n solicitada.";return null;}
	(filter_input(INPUT_POST,'debugOn',FILTER_SANITIZE_NUMBER_INT))?$debugOn = 1 : $debugOn = 0;
	(filter_input(INPUT_POST,'localServer',FILTER_SANITIZE_NUMBER_INT))?$localServer = 1 : $localServer = 0;
	(filter_input(INPUT_POST,'findETS2MAP',FILTER_SANITIZE_NUMBER_INT))?$findETS2MAP = 1 : $findETS2MAP = 0;
	$clientUpdateInterval = filter_input(INPUT_POST,'clientUpdateInterval',FILTER_SANITIZE_NUMBER_INT);
	$truckOnMapLifetime = filter_input(INPUT_POST,'truckOnMapLifetime',FILTER_SANITIZE_NUMBER_INT);
	$truckTelemetryLifetime = filter_input(INPUT_POST,'truckTelemetryLifetime',FILTER_SANITIZE_NUMBER_INT);
	$siteURL  = filter_input(INPUT_POST,'siteURL', FILTER_SANITIZE_STRING);
	$clientSessionLifetime  = filter_input(INPUT_POST,'clientSessionLifetime',FILTER_SANITIZE_NUMBER_INT) * 3600;
	$SQLPHPTime = filter_input(INPUT_POST,'SQLPHPTime', FILTER_SANITIZE_NUMBER_INT) * 3600;
	$userLengthMin = filter_input(INPUT_POST,'userLengthMin',FILTER_SANITIZE_NUMBER_INT);
	$passLengthMin = filter_input(INPUT_POST,'passLengthMin',FILTER_SANITIZE_NUMBER_INT);
	$numberDrivers = filter_input(INPUT_POST,'numberDrivers',FILTER_SANITIZE_NUMBER_INT);
	$minkm = filter_input(INPUT_POST,'minkm',FILTER_SANITIZE_NUMBER_INT);
	$dbHostName = filter_input(INPUT_POST,'DBHostname', FILTER_SANITIZE_STRING);
	$dbName = filter_input(INPUT_POST,'DBName',FILTER_SANITIZE_STRING);
	$dbUserName = filter_input(INPUT_POST,'DBUserName',FILTER_SANITIZE_STRING);
	$dbPassword = filter_input(INPUT_POST,'DBPassword',FILTER_SANITIZE_STRING);
	$varExport = "<?php \n"
		."const DB_USERNAME = '".$dbUserName."';\n"
		."const DB_PASSWORD = '".$dbPassword."';\n"
		."const DB_HOSTNAME = '".$dbHostName."';\n"
		."const DB_NAME = '".$dbName."';\n"
		."const SITE_DEBUG_ON = ".$debugOn.";\n"
		."const IS_IN_LOCAL_SERVER = ".$localServer.";\n"
		."const FIND_DRIVERS_ETSMAP = ".$findETS2MAP.";\n"
		."const CLIENT_UPDATE_INTERVAL = ".$clientUpdateInterval.";\n"
		."const TRUCK_ON_MAP_LIFETIME = ".$truckOnMapLifetime.";\n"
		."const TRUCK_TELEMETRY_LIFETIME = ".$truckTelemetryLifetime.";\n"
		."const CLIENT_SESSION_LIFETIME = ".$clientSessionLifetime.";\n"
		."const SITE_URL = '".$siteURL."';\n"
		."const SQL_PHP_TIME = ".$SQLPHPTime.";\n"
		."const MIN_USERNAME_LENGTH = ".$userLengthMin.";\n"
		."const MIN_PASSWORD_LENGTH = ".$passLengthMin.";\n"
		."const NUMBER_DRIVERS = ".$numberDrivers.";\n"
		."const MIN_KILOMETERS = ".$minkm.";\n"
		."const CONVOY_ON = ".CONVOY_ON.";\n"
		."?>";

	// Verifico la cantidad de conductores
	$squery = "SELECT COUNT(id) as total FROM drivers WHERE rank>0;";
	if($result = dbSelect($squery)) {
		if($row = mysqli_fetch_assoc($result)) {
			if($numberDrivers < $row['total']) {
				// Tengo mas conductores que valores que puedo asignar -> error
				gotoUrl($returnUrl,MSG_TYPE_ERROR,"El número de conductores es menor a la cantidad de conductores actualmente en la empresa.");
			}else{
				// Verifico la cantidad de números actualmente
				$squery = "SELECT COUNT(id) as total FROM driversnumbers;";
				if($result = dbSelect($squery)) {
					if($row = mysqli_fetch_assoc($result)) {
						// Si es menor que el número actual, agrego valores
						if($row['total'] < $numberDrivers) {
							$squery = "INSERT INTO driversnumbers (uid) VALUES (0)";
							$i = 2;
							while ($i<=($numberDrivers-$row['total'])) {
								$squery .= ",(0)";
								$i++;
							}
			                if (dbInsert($squery)) {
            					if (file_put_contents("../inc/settings.php", $varExport)) {
									logEvent(LOG_TYPE_ADMIN,"Actualizó la configuración del sitio");
									gotoUrl($returnUrl,MSG_TYPE_SUCCESS,"Configuarión actualizada");
								} else {
									gotoUrl($returnUrl,MSG_TYPE_WARNING,"Error al guardar la configuración");
								}
			                }else{
			                	gotoUrl($returnUrl,MSG_TYPE_WARNING,"Error al guardar la configuración");
			                }
						}
						// Si es mayor que el número actual, elimino valores y restablezco el id
						if($row['total']>$numberDrivers){
							$squery = "DELETE FROM driversnumbers ORDER BY id DESC LIMIT ".($row['total']-$numberDrivers).";";
							if(dbDelete($squery)){
								if(dbTruncate("ALTER TABLE driversnumbers AUTO_INCREMENT = $numberDrivers;")){
									if (file_put_contents("../inc/settings.php", $varExport)) {
										logEvent(LOG_TYPE_ADMIN,"Actualizó la configuración del sitio");
										gotoUrl($returnUrl,MSG_TYPE_SUCCESS,"Configuarión actualizada");
									} else {
										gotoUrl($returnUrl,MSG_TYPE_WARNING,"Error al guardar la configuración");
									}
								}else{
									gotoUrl($returnUrl,MSG_TYPE_WARNING,"Error al guardar la configuración");	
								}
							}else{
								gotoUrl($returnUrl,MSG_TYPE_WARNING,"Error al guardar la configuración");
							}
						}
						// Si es igual, solo guardo la configuración
						if($row['total'] == $numberDrivers){
    						if (file_put_contents("../inc/settings.php", $varExport)) {
								logEvent(LOG_TYPE_ADMIN,"Actualizó la configuración del sitio");
								gotoUrl($returnUrl,MSG_TYPE_SUCCESS,"Configuarión actualizada");
							} else {
								gotoUrl($returnUrl,MSG_TYPE_WARNING,"Error al guardar la configuración");
							}	
						}
					}else{
						gotoUrl($returnUrl,MSG_TYPE_WARNING,"Error al guardar la configuración");
					}
				}else{
					gotoUrl($returnUrl,MSG_TYPE_WARNING,"Error al guardar la configuración");
				}
			}
		}else{
			gotoUrl($returnUrl,MSG_TYPE_WARNING,"Error al guardar la configuración");
		}
	}else{
		gotoUrl($returnUrl,MSG_TYPE_WARNING,"Error al guardar la configuración");
	}
}

function deleteUsers(){
	$returnUrl = "admin/config/edit";
    $query = "DELETE FROM drivers, trucks, trips, user_options, recruits
			USING drivers
			INNER JOIN trucks
			INNER JOIN trips
			INNER JOIN user_options
			INNER JOIN recruits
			WHERE drivers.rank = '0' AND trucks.owner = drivers.id AND trips.uid = drivers.id AND user_options.uid = drivers.id AND recruits.uid = drivers.id;";
	if($result = dbDelete($query))
        gotoUrl($returnUrl,MSG_TYPE_SUCCESS,"Conductores eliminados.");
    else
    	gotoUrl($returnUrl,MSG_TYPE_WARNING,"No se eliminaron usuarios.");
}

function updateServers(){
	$returnUrl = "admin/config/edit";
	try{
		$url = 'https://api.truckersmp.com/v2/servers';
		$content = file_get_contents($url);
		$json = json_decode($content, true);
	} catch (Exception $e) {
		gotoUrl($returnUrl,MSG_TYPE_ERROR,"No se pudo contactar con el servidor de Truckers MP.");
	} 

    $squery = "SELECT COUNT(1) FROM servers;";
    if($result = dbSelect($squery)){
    	if($row = mysqli_fetch_array($result)) {
    		if($row[0]!=0){
    			dbDelete("DELETE FROM servers;");
				dbTruncate("ALTER TABLE servers AUTO_INCREMENT = 1;");
    		}else{
				if($json['error'] == "false"){
					$squery = "INSERT INTO servers (name, game, limiter) VALUES ";
					foreach($json['response'] as $value) {
						$squery .= "('".$value['name']."', '".strtolower($value['game'])."', ".$value['speedlimiter']."),";
					}
					$squery = rtrim($squery, ',');
					$squery .= ";";

					if(dbInsert($squery))
						gotoUrl($returnUrl,MSG_TYPE_SUCCESS,"Los servidores han sido actualizados");
					else
						gotoUrl($returnUrl,MSG_TYPE_ERROR,"Hubo un error al insertar los servidores.");		
				}
			}
		}
	}
}
?>