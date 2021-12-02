<?php
session_start();
require_once(__DIR__.'/../inc/config.php');
require_once(__DIR__.'/../inc/model.php');
require_once(__DIR__.'/../inc/layout.php');
require_once(__DIR__.'/../inc/functions.php');
require_once(__DIR__.'/nav_layout.php');

if (SITE_DEBUG_ON) {
    ini_set('display_errors',1);
    ini_set('display_startup_errors',1);
    error_reporting(-1);
}

if (!isDeveloper()){
	gotoUrl("/",MSG_TYPE_ERROR,"Solo para personal autorizado");	
	die;
}

$scripts = array();
$scripts[] = "jquery.dataTables.min.js";
$scripts[] = "dataTables.bootstrap.min.js";
$scripts[] = "dataTables.responsive.min.js";
$scripts[] = "chatadmin.js";

$css = array();
$css[] = "dataTables.bootstrap.min.css";
$css[] = "responsive.bootstrap.min.css";

layoutHead("Administraci&oacute;n",$css);
layoutInitBody();
layoutInitWrapper();
layoutTopbar("siteadmin");                                     
layoutInitContent();    
switch (filter_input(INPUT_GET, 'a', FILTER_SANITIZE_STRING)) {	
	case 'view':
		listChatMsgs();
}
layoutEndContent();
layoutEndWrapper();         
layoutAddDefaultScript($scripts);
layoutEndBody();

/**
	MOSTRAR HISTORICO DE CHAT
**/
function listChatMsgs (){
	?>
	<div class="control-group col-md-8 col-md-offset-2">
		<?php adminNavigation("chat"); ?>
		<legend>Historial de chat</legend>
		<div style="display:none;" id="table-content">
			<table class="table table-striped table-condensed" id="chats" cellspacing="0" width="100%">
				<thead>
				 	<tr>
						<th>ID</th>
						<th>Usuario</th>
						<th>Rango</th>
						<th>Fecha</th>
						<th>Texto</th>
						<th>Borrado</th>
				  	</tr>
				</thead>
			</table>
			<div class="container-fluid text-center">
					<a id="clearchatdeleted" href="javascript:void(0)">
						<button id="flushbutton" class="btn btn-md btn-danger">Eliminar chats borrados</button>
					</a>
					<a id="clearchat" href="javascript:void(0)">
						<button id="flushbutton" class="btn btn-md btn-danger">Eliminar chats</button>
					</a>
				</div>
			</div>
		</div>
	</div>
<?php
}
?>