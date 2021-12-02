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

if (isset($_SERVER['HTTP_REFERER'])) {
    $fallbackURL = $_SERVER['HTTP_REFERER'];
} else {
    $fallbackURL = "";
}

$id = filter_input(INPUT_GET,'id',FILTER_SANITIZE_NUMBER_INT);
if (!$id) $id = $_SESSION['driverid'];
if (isLoggedIn()){
    $scripts = array(); // JAVASCRIPT PARA SER ENVIADO A LAYOUT
    $css = array();
    $css[] = "dataTables.bootstrap.min.css";
    $css[] = "responsive.bootstrap.min.css";
    $css[] = "animate.css";
    layoutHead("Viajes",$css);
    layoutInitBody();
    layoutInitWrapper();
    layoutTopbar("trips");                                     
    layoutInitContent();
	switch (filter_input(INPUT_GET, 'a', FILTER_SANITIZE_STRING)) {
        case 'view':						
			showTrip($id);
            $scripts[] = "chart.min.js";
            $scripts[] = "viewtrips.js";
			break;
		case 'list':					
	        listUserTrips($id);
            $scripts[] = "jquery.dataTables.min.js";
            $scripts[] = "dataTables.bootstrap.min.js";
            $scripts[] = "dataTables.responsive.min.js";
            $scripts[] = "usertrips.js";
	        break;
	    case 'stats':
	     	showUserStats($id);
	     	break;
		case 'current':
	        showCurrentTrip($id);
            $scripts[] = "chart.min.js";
			break;
	}
    layoutEndContent();
    layoutEndWrapper();      
    layoutAddDefaultScript($scripts);
    layoutEndBody();
} else {
	gotoUrl($fallbackURL,MSG_TYPE_WARNING,"Solo usuarios registrados");
}

/**
* Menu de navegacion
*
*
*/
function userTripsNavigation($option = 1, $uid){	
?>
<ul class="nav nav-tabs">
<li role="button"><a href="<?php echo SITE_URL; ?>/users/view/info/<?php echo $uid; ?>"><span class='glyphicon glyphicon-chevron-left' aria-hidden='true'>&nbsp;</span><b>Informaci&oacute;n</b></a></li>
<li role="presentation" <?php if ($option ==1) echo 'class="active"';?>><a href="<?php echo SITE_URL; ?>/trips/current/<?php echo $uid; ?>">Viaje en curso</a></li>
<li role="presentation" <?php if ($option ==2) echo 'class="active"';?>><a href="<?php echo SITE_URL; ?>/trips/list/<?php echo $uid; ?>">Viajes</a></li>
<li role="presentation" <?php if ($option ==3) echo 'class="active"';?>><a href="<?php echo SITE_URL; ?>/trips/stats/<?php echo $uid; ?>">Estad&iacute;sticas</a></li>
</ul>
<?php   
}

/**
* Muestra informacion de un viaje
* @param $id id del viaje
*/
function showTrip($id,$uid = 0){
	$trip = getTripInfo($id);  
    if (!$uid) $uid = $trip['uid'];
    ?>
	<div class="control-group col-md-8 col-md-offset-2">
	<?php userTripsNavigation(2,$uid); ?>
	<legend>Informaci&oacute;n del viaje</legend>
	<?php
	if ($trip){
        printTripInfo($trip);
    } else { 
		echo "Viaje no encontrado";
    }
    echo "</div>";
}

/**
* Muestra informacion del viaje actual + panel de control
*/
function showCurrentTrip($uid){
	?>
	<div class="control-group col-md-8 col-md-offset-2">
	<?php
    userTripsNavigation(1,$uid);
    if($driver = getDriverInfo($uid, true)){
        if ($driver['rank'] == USER_RANK_DISABLED){
            echo "Usuario deshabilitado";
            return null;
        }
    }else{
        echo "No existe el usuario.";
        return null;
    }
    ?>
	<legend><?php echo $driver['displayname']; ?>: Viaje en curso</legend>	
	<?php
	$tripId = findOpenTrip($uid);
	if ($tripId){
        $trip = getTripInfo($tripId['id']);
		printTripInfo($trip,1);
	?>	
	<?php
	} else {
		?>
		<b>No existe un viaje en curso</b><br>
		<?php
	}
	?>
	</div>
	<?php
}

/**
* Lista los viajes del usuario logueado
*/
function listUserTrips($uid = 0){
	?>
	<div class="control-group col-md-8 col-md-offset-2">
	<?php 
	userTripsNavigation(2,$uid);

    // Analizar este IF para obtener información con reclutas incluido
    if ($driver = getDriverInfo($uid)) {
        if ($driver['rank'] == USER_RANK_DISABLED){
            echo "Usuario deshabilitado.";
            return null;
        }
    } else {
        echo "No existe el usuario.";
        return null;
    }
    ?>
        <legend>Viajes de <?php echo $driver['displayname']; ?></legend>
        <div class="filter-bar well">
            <form role="form">
                <div class="input-group col-md-6 col-md-offset-3">
                    <label>Simulador:&nbsp;</label>
                    <select id="simuladores">
                        <option value="" selected>Todos</option>
                        <option value="American Truck Simulator">American Truck Simulator</option>
                        <option value="Euro Truck Simulator 2">Euro Truck Simulator 2</option>
                    </select>
                </div>
            </form>
        </div>
        <table class="table table-striped table-condensed" id="usertrips" cellspacing="0" width="100%" data-id="<?php echo $uid;?>">
            <thead>
                <tr>
                    <th>Conductor</th>
                    <th>Simulador</th>
                    <th>Itinerario</th>
                    <th>Inicio</th>
                    <th>Carga</th>
                    <th>Peso</th>
                    <th>Distancia</th>    
                    <th>Tiempo</th>
                </tr>
            </thead>
        </table>
    </div>
	<?php
}

/**
* Muestra estadisticas del usuario
*/
function showUserStats($uid){
    ?>
	<div class="control-group col-md-8 col-md-offset-2">
	<?php 
	userTripsNavigation(3,$uid);
    if($driver = getDriverInfo($uid, true)){
        if ($driver['rank'] == USER_RANK_DISABLED){
            echo "Usuario deshabilitado";
            return null;
        }
    }else{
        echo "No existe el usuario.";
        return null;
    }
    $stats = getUserStats($uid);
	?>
	<legend>Estad&iacute;sticas de <?php echo $driver['displayname'] ?></legend>	
	       <div class="row">                    
                <div class="col-lg-6 col-md-4">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-exchange fa-4x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge"><?php echo $stats['trips']; ?></div>
                                    <div>Viajes completados</div>
                                </div>
                            </div>
                        </div>
                      
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6 col-md-4">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-road fa-4x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge"><?php printNumber($stats['distance']); ?> Km</div>
                                    <div>En itinerario</div>
                                </div>
                            </div>
                        </div>                       
                    </div>
                </div>                
                <div class="col-lg-6 col-md-4">
                    <div class="panel panel-warning">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-road fa-4x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge"><?php printNumber($stats['driven']); ?> Km</div>
                                    <div>En viaje</div>
                                </div>
                            </div>
                        </div>                       
                    </div>
                </div>
            </div>            
            <div class="row">
                <div class="col-lg-6 col-md-4">
                    <div class="panel panel-success">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-money fa-4x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge">$ <?php printNumber($stats['income']); ?></div>
                                    <div>Ingresos</div>
                                </div>
                            </div>
                        </div>
                      
                    </div>
                </div>
                <div class="col-lg-6 col-md-4">
                    <div class="panel panel-danger">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-money fa-4x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge">$ <?php printNumber($stats['expenses']); ?></div>
                                    <div>Gastos</div>
                                </div>
                            </div>
                        </div>
                      
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6 col-md-4">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-money fa-4x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge">$ <?php echo printNumber($stats['income'] - $stats['expenses']); ?></div>
                                    <div>Ganancias</div>
                                </div>
                            </div>
                        </div>                     
                    </div>
                </div>        
            </div>
        </div>	
<?php } ?>