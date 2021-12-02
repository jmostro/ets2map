<?php
session_start();
require_once(__DIR__.'/../inc/config.php');
require_once(__DIR__.'/../inc/model.php');
require_once(__DIR__.'/../inc/layout.php');
require_once(__DIR__.'/../inc/functions.php');
require_once(__DIR__.'/nav_layout.php');
if (!isRecruiter()){
	gotoUrl("/",MSG_TYPE_ERROR,"Solo para personal autorizado");	
	die;
}
// GET PARAMS
$userid = filter_input(INPUT_GET, 's', FILTER_SANITIZE_NUMBER_INT);

switch (filter_input(INPUT_GET, 'a', FILTER_SANITIZE_STRING)) {
	case 'accept':
		acceptRecruit($userid);
		die;
	case 'decline':
		declineRecruit($userid);
		die;
	case 'get':
		getRecruitsData($userid);
		die;
}

$scripts = array();
$scripts[] = "recruits.js";
layoutHead("Administraci&oacute;n");
layoutInitBody();
layoutInitWrapper();
layoutTopbar("siteadmin");
layoutInitContent();
showRecruits();
layoutEndContent();
layoutEndWrapper();
layoutAddDefaultScript($scripts);
layoutEndBody();

function showRecruits(){
    $gameRecs[1]="Nuevos reclutas";
    $gameRecs[2]="Reclutas rechazados";

	?>
	<div class="control-group col-md-8 col-md-offset-2">
		<?php adminNavigation("recruits"); ?>
		<h3>Lista de reclutas</h3>
		<div class="filter-bar well">
			<select id="filterByRecruits" onchange="updateRecsTable()">
			<?php 
				foreach ($gameRecs as $i => $recs){
					($i == 1)?$selected="selected='selected'":$selected="";
					echo "<option value=$i $selected>$recs</option>";
				}
			?>
			</select>
		</div>
        <center>
            <button id="loading-btn" class="btn btn-sm btn-success"><span class="glyphicon glyphicon-refresh glyphicon-refresh-animate" title="Actualizando..."></span></button>
        </center>
		<div class="table-responsive">
		    <table class="table table-striped table-condensed" id="user-list">
			    <thead>
				    <tr>
					    <th>Usuario</th>
					    <th class="col-md-1.5">Nombre y Apellido</th>
					    <th>Perfil de Steam</th>
					    <th>Facebook</th>
					    <th>Edad</th>
					    <th>Pa&iacute;s</th>
					    <th>Otra VTC</th>
					    <th class="col-md-4">Por qu&eacute; LATAM</th>
					    <th>Aceptarlo</th>
				    </tr>
			    </thead>
		    </table>
    	</div>
    </div>
<?php
}

function acceptRecruit($id){
	if (!isAdmin()){ gotoUrl("/",MSG_TYPE_ERROR,"Solo para personal autorizado"); return null; }
	$returnUrl = "/admin/recruits/";
	$squery = "UPDATE drivers, recruits SET drivers.rank= ".USER_RANK_DRIVER.", recruits.isDriver='1' WHERE drivers.id='$id' AND recruits.uid='$id';";
	if(dbUpdate($squery)){
		$squery = "UPDATE driversnumbers SET uid = $id WHERE uid = 0 LIMIT 1";
		if(dbUpdate($squery)) {
			logEvent(LOG_TYPE_ADMIN,"Aprobó al recluta ".$id,$squery);
			gotoUrl($returnUrl,MSG_TYPE_SUCCESS,"Recluta aprobado.");
			return null;
		}else{
			gotoUrl($returnurl,MSG_TYPE_ERROR,"Error al conectar a la base de datos.");
			return null;
		}
	}else{
		gotoUrl($returnurl,MSG_TYPE_ERROR,"Error al conectar a la base de datos.");
		return null;
	}
}
function declineRecruit($id){
	if (!isAdmin()){ gotoUrl("/",MSG_TYPE_ERROR,"Solo para personal autorizado"); return null; }
	$returnUrl = "/admin/recruits/";
	$squery = "UPDATE drivers, recruits SET drivers.rank= ".USER_RANK_DISABLED.", recruits.isDriver='0' WHERE drivers.id='$id' AND recruits.uid='$id';";
	if(dbUpdate($squery)){
		logEvent(LOG_TYPE_ADMIN,"Rechazó al recluta ".$id,$squery);
		gotoUrl($returnUrl,MSG_TYPE_SUCCESS,"Recluta rechazado.");
		return null;	
	}else{
		gotoUrl($returnurl,MSG_TYPE_ERROR,"Error al conectar a la base de datos.");
		return null;
	}
}
function getRecruitsData($option){
    $auxArray = array();
    $recsData = getRecruits($option);
    while ($row = mysqli_fetch_assoc($recsData)){
        $auxArray[]=$row;
    }
    echo json_encode($auxArray);
}
?>