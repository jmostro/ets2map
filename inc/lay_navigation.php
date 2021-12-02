<?php

/**
* Menu de navegacion de la seccion Empresa
*/
function companyNavigation($option){
	?>
    <ul class="nav nav-tabs">
        <li role="presentation"><a href="<?php echo SITE_URL; ?>/"><span class='glyphicon glyphicon-chevron-left' aria-hidden='true'>&nbsp;</span>Mapa</a></li>
        <li role="presentation" <?php if ($option == "rank") echo 'class="active"';?>><a href="<?php echo SITE_URL; ?>/ranking/view">Ranking</a></li>
        <li role="presentation" <?php if ($option ==1) echo 'class="active"';?>><a href="<?php echo SITE_URL; ?>/company/stats">Estad&iacute;sticas</a></li>
        <li role="presentation" <?php if ($option ==2) echo 'class="active"';?>><a href="<?php echo SITE_URL; ?>/company/users">Conductores</a></li>
        <li role="presentation" <?php if ($option ==3) echo 'class="active"';?>><a href="<?php echo SITE_URL; ?>/company/trips">Viajes</a></li>
        <li role="presentation" <?php if ($option ==4) echo 'class="active"';?>><a href="<?php echo SITE_URL; ?>/company/tripping">Viajes en curso</a></li>
    </ul>
<?php   
}
?>