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

$scripts = array();
$scripts[] = "jquery.dataTables.min.js";
$scripts[] = "dataTables.bootstrap.min.js";
$scripts[] = "dataTables.responsive.min.js";
$scripts[] = "tripadmin.js";

$css = array();
$css[] = "dataTables.bootstrap.min.css";
$css[] = "responsive.bootstrap.min.css";

layoutHead("Administraci&oacute;n", $css);
layoutInitBody();
layoutInitWrapper();
layoutTopbar("siteadmin");                                     
layoutInitContent();    
showTrips();
layoutEndContent();
layoutEndWrapper();         
layoutAddDefaultScript($scripts);
layoutEndBody();

function showTrips(){	
?>
	<div class="control-group col-md-8 col-md-offset-2">
		<?php adminNavigation("trips"); ?>
		<legend>Registro de viajes</legend>
        <div class="filter-bar well">
            <form role="form">
			   	<div class="row text-center col-md-offset-1">
				   	<div class="col-md-4">
	                    <label>Simulador:&nbsp;</label>
		                    <select id="simuladores">
		                        <option value="" selected>Todos</option>
		                        <option value="American Truck Simulator">American Truck Simulator</option>
		                        <option value="Euro Truck Simulator 2">Euro Truck Simulator 2</option>
	                    </select>
					</div>
					<div class="col-md-2 checkbox checkbox-circle checkbox-inline checkbox-info">	
						<input type="checkbox" id="filter_delivered"><label>Entregado</label>
					</div>
					<div class="col-md-2 checkbox checkbox-circle checkbox-inline checkbox-info">
				   		<input type="checkbox" id="filter_deleted"><label>Eliminado</label>
					</div>
					<div class="col-md-2">
						<input type="text" id="user-input" placeholder="Conductor">
					</div>
				</div>
            </form>
        </div>

		<table class="table table-striped table-condensed" id="tripsadmin" cellspacing="0" width="100%">
			<thead>
			 	<tr>
				    <th>Conductor</th>
				    <th>Itinerario</th>
				    <th>Distancia</th>
				    <th>Inicio</th>
				    <th>Fin</th>
				    <th>Borrado</th>
				    <th>Estado</th>
				    <th>Simulador</th>
			  	</tr>
			</thead>
		</table>
	</div>
<?php } ?>