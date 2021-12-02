<?php
session_start();
require_once(__DIR__.'/../inc/config.php');
require_once(__DIR__.'/../inc/model.php');
require_once(__DIR__.'/../inc/layout.php');
require_once(__DIR__.'/../inc/functions.php');
require_once(__DIR__.'/nav_layout.php');
if (!isDeveloper()){gotoUrl("/",MSG_TYPE_ERROR,"Solo para personal autorizado");die;}

$scripts = array();
$scripts[] = "jquery.dataTables.min.js";
$scripts[] = "dataTables.bootstrap.min.js";
$scripts[] = "dataTables.responsive.min.js";
$scripts[] = "log.js";

$css = array();
$css[] = "dataTables.bootstrap.min.css";
$css[] = "responsive.bootstrap.min.css";

layoutHead("Administraci&oacute;n", $css);
layoutInitBody();
layoutInitWrapper();
layoutTopbar("siteadmin");                                     
layoutInitContent();    
showLog();
layoutEndContent();
layoutEndWrapper();         
layoutAddDefaultScript($scripts);
layoutEndBody();

/**
	MOSTRAR LOG DEL SITIO
**/
function showLog(){
	?>
	<div class="control-group col-md-8 col-md-offset-2">
		<?php adminNavigation("log"); ?>
		<legend>Log del sitio</legend>
		<div style="display:none;" id="table-content">
			<table class="table table-striped table-condensed" id="log" cellspacing="0" width="100%">
				<thead>
				 	<tr>
						<th>ID</th>
						<th>Usuario</th>
						<th>Tipo</th>
						<th>Fecha</th>
						<th>Descripc&oacute;n</th>
						<th>SQL</th>
				  	</tr>
				</thead>
			</table>
			<div class="container-fluid text-center">
				<a id="clearlog" href="javascript:void(0)">
					<button id="flushbutton" class="btn btn-md btn-danger">Vaciar log</button>
				</a>
			</div>
		</div>
	</div>
	<?php 
}
?>
