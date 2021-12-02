<?php
session_start();
require_once('inc/model.php');
require_once('inc/layout.php');
require_once('inc/functions.php');
require_once('inc/config.php');

if (isset($_SERVER['HTTP_REFERER'])) {
    $fallbackURL = $_SERVER['HTTP_REFERER'];    
} else {
    $fallbackURL = "";
}

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

$scripts = array(); // JAVASCRIPT PARA SER ENVIADO A LAYOUT
$section = filter_input(INPUT_GET, 's', FILTER_SANITIZE_STRING);
$id = filter_input(INPUT_GET,'id',FILTER_SANITIZE_NUMBER_INT);
$action = filter_input(INPUT_GET, 'a', FILTER_SANITIZE_STRING);
if ($section == "triptable"){
    openTripsTable();
    die;
}
if (isLoggedIn()){
    $css = array();
    $css[] = "dataTables.bootstrap.min.css";
    $css[] = "responsive.bootstrap.min.css";
	layoutHead("Empresa",$css);
	layoutInitBody();
	layoutInitWrapper();
	layoutTopbar("company");                                     
	layoutInitContent();
	switch ($section) {
		case 'users':
            switch ($action) {
                default:
                    listCompanyUsers();
                    $scripts[] = "jquery.dataTables.min.js";
                    $scripts[] = "dataTables.bootstrap.min.js";
                    $scripts[] = "dataTables.responsive.min.js";
                    $scripts[] = "companyusers.js";
                    break;
            }
			break;
		case 'trips':
			switch ($action) {
				case 'view':
					showTrip($id);
                    $scripts[] = "chart.min.js";
					break;
				default:
					listCompanyTrips();
                    $scripts[] = "jquery.dataTables.min.js";
                    $scripts[] = "dataTables.bootstrap.min.js";
                    $scripts[] = "dataTables.responsive.min.js";
                    $scripts[] = "companytrips.js";
					break;
			}			
			break;
		case 'stats':
			showStats();
			break;        
        case 'tripping':
            listOpenTrips();
            $scripts[]="opentrips.js";
            break;
		default:		
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
* Muestra la lista de usuarios
**/
function listCompanyUsers(){
	?>
    <div class="control-group col-md-8 col-md-offset-2">    
        <?php 
        companyNavigation(2);
        ?>
        <h3>Viajes</h3>
        <div class="control-group filter-bar well">
           <form id="filter-form" role="form">
               <div class="checkbox checkbox-circle checkbox-inline checkbox-info">
                   <input type="checkbox" id="filter-onsite">
                   <label>En el sitio</label>
               </div>
               <div class="checkbox checkbox-circle checkbox-inline checkbox-info">
                   <input type="checkbox" id="filter-onroad">
                   <label>En ruta</label>
               </div>
               <input type="text" id="filter-name" placeholder="Nombre">
           </form>
        </div>
        <table class="table table-striped table-condensed" id="companyusers" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>Nick</th>
                    <th>Nombre</th>
                    <th>Puesto</th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
        </table>
    </div>
	<?php
}

/**
* Muestra todos los viajes de la empresa o de un usuario específico
**/
function listCompanyTrips(){
	?>
	<div class="control-group col-md-8 col-md-offset-2">	
    	<?php 
    	companyNavigation(3);
        ?>
        <h3>Viajes</h3>
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
        <table class="table table-striped table-condensed" id="companytrips" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>Conductor</th>
                    <th>Simulador</th>
                    <th>Itinerario</th>
                    <th>Entregado</th>
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
* Muestra informacion de un viaje
* @param $id id del viaje
*/
function showTrip($id){
    $trip = getTripInfo($id);
	?>
	<div class="control-group col-md-8 col-md-offset-2">
	<?php 
	companyNavigation(3);
	?>
	<h3>Informaci&oacute;n del viaje</h3>
	<?php
	if ($trip){
        printTripInfo($trip);
    } else {
		echo "Viaje no encontrado";
	}
	?>
	</div>
	<?php
}

/**
* Muestra estadisticas de la empresa
**/
function showStats(){
    $stats = getUserStats(-1);    
    $numDrivers = countDrivers();	
    ?>
    <div class="control-group col-md-8 col-md-offset-2">
	<?php companyNavigation(1); ?>	
        <h3>Estad&iacute;sticas de la empresa</h3>
        <div class="row">
             <div class="col-lg-6 col-md-4">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-xs-3">
                                <i class="fa fa-users fa-4x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <div class="huge"><?php echo $numDrivers; ?></div>
                                <div>Conductores</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-4">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-xs-3">
                                <i class="fa fa-exchange fa-4x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <div class="huge"><?php echo $stats['trips'];?></div>
                                <div>Viajes</div>
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
                                <i class="fa fa-map fa-4x"></i>
                            </div>
                            <div class="col-xs-9 text-right">
                                <div class="huge"><?php printNumber($stats['distance']);?> Km</div>
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
                                <div class="huge"><?php printNumber($stats['driven']);?> Km</div>
                                <div>En viaje</div>
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
                                <div>Ganados</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
}

/**
* Muestra todos los viajes en curso de la empresa
**/
function listOpenTrips($uid = 0){
    ?>
    <div class="control-group col-md-8 col-md-offset-2">    
    <?php companyNavigation(4); ?>
    <h3>Viajes en curso</h3>
    <div class="table-responsive" id="opentrips-table">
    </div>
    <center>
        <button id="loading-btn" class="btn btn-sm btn-success"><span class="glyphicon glyphicon-refresh glyphicon-refresh-animate" title="Actualizando..."></span></button>
    </center>
    </div>
    <?php
}

function openTripsTable($uid = 0){
?>
    <table class="table table-striped table-condensed">
    <thead><tr>
    <th>Conductor</th>
    <th>Simulador</th>
    <th>Servidor</th>
    <th>Itinerario</th>
    <th>Carga</th>
    <th>Restante</th>
    <th>Velocidad</th>
    </tr></thead>
    <?php
    $trips = getOpenTrips($uid,0,RECORDS_PER_PAGE);            
    while ($t = mysqli_fetch_array($trips)){    
    $truck = getTruckInfo($t['uid']);
    $auxTime = strtotime($truck['last_seen']) + TRUCK_ON_MAP_LIFETIME + SQL_PHP_TIME;
    if (($auxTime) >= time()) {
        if (($truck['trailer_name']==$t['trailer']) && ($truck['trailer_mass'] == $t['mass'])){        
    ?>
    <tr>
    <td><a class="table-a" href="<?php echo SITE_URL."/company/trips/".$t['uid'];?>"><?php echo $t['drivername']; ?></a></td>
    <td>
    <?php if($t['game']=="ats")
        echo 'American Truck Simulator';
    else
        echo 'Euro Truck Simulator 2';
    ?>
    </td>
    <td><?php echo $truck['servername']; ?></td>
    <td><a class="table-a" href="<?php echo SITE_URL."/company/trips/view/".$t['id']; ?>"><?php echo $t['org_city']." a ".$t['des_city'];?></a></td>
    <td>
    <?php
    echo $t['trailer'];
    echo "&nbsp;[";
    printNumber($t['mass'] / 1000,0);
    ;?>
    Ton]</td>
    <td><?php printNumber($truck['nav_distance'] / 1000,1);?> Km</td>
    <td>
    <a class="table-a" href="<?php echo SITE_URL."/follow/".$t['uid']; ?>" title="Seguir en el mapa"><?php echo $truck['speed'];?>Km/h</a>
    </td>
    </tr>
    <?php
    }}}
    ?>
    </table>
    <?php
}
?>