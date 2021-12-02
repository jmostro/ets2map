<?php
session_start();
require_once('inc/model.php');
require_once('inc/layout.php');
require_once('inc/functions.php');
require_once('inc/config.php');

layoutHead("No encontrado");
layoutInitBody();
layoutInitWrapper();
layoutTopbar("");
layoutInitContent();

?>
<div class='well'>
	<legend>404 - No encontrado</legend>
		<center>
			<img class="img-fullwidth img-rounded img-centered" src="<?php echo SITE_URL; ?>/img/notfound.png">
			<br>
			<h4> La ruta que especific&oacute; no pudo ser encontrada en nuestro servidor</h4>
			<a href="<?php echo SITE_URL; ?>/">
				<button name="back" class="btn btn-md btn-info">Ir al inicio</button>
			</a>
		</center>
</div>		
<?php

layoutEndContent();
layoutEndWrapper();
layoutAddDefaultScript();
layoutEndBody();
?>