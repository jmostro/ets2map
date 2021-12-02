<?php
function printActiveif($option, $check){
	if ($option == $check){
		echo 'class="active"';
	}
}

function adminNavigation($option = 1){
	?>
    <ul class="nav nav-tabs">
    	<li role="presentation" <?php printActiveif($option,"recruits"); ?>><a href="<?php printURL("/admin/recruits"); ?>">Lista de reclutas</a></li>
	<?php if(isAdmin()){ ?>
    	<li role="presentation" <?php printActiveif($option,"driversnumbers"); ?>><a href="<?php printURL("/admin/driversnumbers"); ?>">N&uacute;meros de los conductores</a></li>
		<li role="presentation" <?php printActiveif($option,"trips"); ?>><a href="<?php printURl("/admin/trips");?>">Viajes</a></li>
    <?php } if (isDeveloper()){ ?>
		<li role="presentation" <?php printActiveif($option,"log"); ?>><a href="<?php printURL("/admin/log"); ?>">Log</a></li>
	    <li role="presentation" <?php printActiveif($option,"config"); ?>><a href="<?php printURL("/admin/config/edit");?>">Configuraci&oacute;n</a></li>
	  	<li role="presentation" <?php printActiveif($option,"chat"); ?>><a href="<?php printURL("/admin/chat/view");?>">Chat</a></li>
	<?php } ?>
    </ul>
<?php
}

?>