<?php
session_start();
require_once(__DIR__.'/../inc/config.php');
require_once(__DIR__.'/../inc/model.php');
require_once(__DIR__.'/../inc/layout.php');
require_once(__DIR__.'/../inc/functions.php');
require_once(__DIR__.'/nav_layout.php');
if ($_SESSION['rank']<USER_RANK_MANAGER){
	gotoUrl("/",MSG_TYPE_ERROR,"Solo para personal autorizado");	
	die;
}

// GET PARAMS
$action = filter_input(INPUT_GET, 'a', FILTER_SANITIZE_STRING);
switch ($action) {
	case 'get':
		getDriversNumbers();
		die;
	case 'edit':
		editDriversNumbers();
		die;
}

$scripts = array();
$scripts[] = "driversnumbersupdate.js";

layoutHead("Administraci&oacute;n");
layoutInitBody();
layoutInitWrapper();
layoutTopbar("siteadmin");
layoutInitContent();
showNumbers();
layoutEndContent();
layoutEndWrapper();
layoutAddDefaultScript($scripts);
layoutEndBody();

function showNumbers(){
	?>
	<div class="control-group col-md-8 col-md-offset-2">
		<?php adminNavigation("driversnumbers"); ?>
		<h3>N&uacute;meros de los conductores<small class="pull-right"><a href="<?php echo SITE_URL?>/printusers" target="_blank" class="table-a" style="color: #c8c8c8;">Versi&oacute;n lista</a></small></h3>
		<div class="table-responsive">
		    <table class="table table-striped" id="user-list">
			    <thead>
				    <tr>
					    <th class="col-md-2 text-center">N&uacute;mero de conductor</th>
					    <th class="text-center">Usuario</th>
					    <th class="text-center">Nombre y Apellido</th>
					    <th class="text-center">Perfil de Steam</th>
				    </tr>
			    </thead>
			    <?php
			    	$squery = "SELECT id FROM driversnumbers WHERE uid = 0;";
			    	if($result=dbSelect($squery)) {
			    		while($row = mysqli_fetch_assoc($result))
			    			$freepos[] = $row;

		    		    $squery = "SELECT driversnumbers.id as numero,
		    		   						drivers.id as uid,
					    					drivers.realusername,
					    					drivers.fullname,
					    					drivers.steam_id 
					    					FROM driversnumbers 
					    					INNER JOIN drivers ON drivers.id = driversnumbers.uid 
					    					WHERE rank>1;";
					    if ($result = dbSelect($squery)) {
						    while ($row = mysqli_fetch_assoc($result)){
						    	?>
						    	<tr>
						    		<td>
		    							<select class="form-control input-sm" onchange="updateDriversNumbersTable(this,<?php echo $row['uid']; ?>)">
		    								<option value="<?php echo $row['numero']; ?>" selected><?php echo $row['numero']; ?></option>
											<?php
												foreach ($freepos as $free)
													echo "<option value=".$free['id'].">".$free['id']."</option>";
											?>
										</select>
									</td>
						    		<td class="text-center" style="vertical-align: middle;"><a href="<?php printURL("/users/view/info/".$row['uid']); ?>" target="_blank" class="table-a"><?php echo $row['realusername']; ?></a></td>
									<td class="text-center" style="vertical-align: middle;"><?php echo $row['fullname'] ?></td>
									<td class="text-center" style="vertical-align: middle;"><a href="http://steamcommunity.com/profiles/<?php echo $row['steam_id']; ?>" target="_blank" class="table-a"><?php echo $row['steam_id']; ?></a></td>
								</tr>
								<?php
					    	}
					    }
					}
			    ?>
		    </table>
	    </div>
    </div>
	<?php
}

function getDriversNumbers(){
	if ($_SESSION['rank']<USER_RANK_MANAGER) {echo "No tiene permisos para acceder."; return null;}
    $auxArray = array();
    $squery = "SELECT driversnumbers.id as numero,
    					drivers.realusername,
    					drivers.fullname,
    					drivers.steam_id 
    					FROM driversnumbers 
    					INNER JOIN drivers ON drivers.id = driversnumbers.uid 
    					WHERE rank>1;";
    if ($result = dbSelect($squery)) {
	    while ($row = mysqli_fetch_assoc($result)){
        	$auxArray[]=$row;
    	}
    	echo json_encode($auxArray);
    }    
}

function editDriversNumbers(){
	$uid = ($_REQUEST["user_id"] <> "") ? trim($_REQUEST["user_id"]) : "";
	$number = ($_REQUEST["number"] <> "") ? trim($_REQUEST["number"]) : "";

	$squery = "UPDATE driversnumbers set uid = 0 WHERE uid = $uid;";
	dbUpdate($squery);

	$squery = "UPDATE driversnumbers SET uid = $uid WHERE id = $number;";
	dbUpdate($squery);

	$squery = "SELECT username FROM drivers WHERE id = $uid;";
	$result = dbSelect($squery);
    	if($row = mysqli_fetch_array($result))
			logEvent(LOG_TYPE_ADMIN,"Actualizó el número del conductor ".$row['username']." a ".$number);
}

?>