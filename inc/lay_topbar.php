<?php
require_once(__DIR__."/config.php");
/**
    BARRA DE NAVEGACIÓN SUPERIOR
**/
function layoutTopbar ($section = 0, $mapOptions = false){
    ?>      
    <nav class="navbar navbar-default" id="top-navbar">
        <div class="container-fluid">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                    <span class="sr-only">Mostrar navegaci&oacute;n</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="http://www.logisticalatinoamericana.com" title="Sitio web" target="_blank">Log&iacute;stica Latinoamericana</a>      
            </div>
            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                <!-- Mapa -->
                    <li class="<?php if ($section == "map") echo "active"; ?>">
                        <a href="<?php echo SITE_URL."/"; ?>" title="Mapa"><i class="fa fa-map"></i><span class="visible-xs-inline">&nbsp;Mapa</span></a>
                    </li>
                <?php
                if (isLoggedIn()) {
                ?>   
                    <!-- Compañía -->
                    <li class="dropdown <?php if ($section == "company") echo "active"; ?>">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-tasks fa-fw"></i>&nbsp;Compa&ntilde;&iacute;a&nbsp;<i class="fa fa-caret-down"></i>
                        </a>            
                        <ul class="dropdown-menu">
                            <li><a href="<?php echo SITE_URL; ?>/company/stats">Estad&iacute;sticas</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/ranking/view">Ranking</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/company/users">Conductores</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/company/trips">Viajes</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/company/tripping">Viajes en curso</a></li>
                        </ul>
                    </li>
                    <!-- Viajes del usuario -->
                    <li class="dropdown <?php if ($section == "trips") echo "active";?>" id="usertrip-menu">                
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-truck fa-fw"></i>&nbsp;Viajes&nbsp;<i class="fa fa-caret-down"></i>
                        </a>        
                        <ul class="dropdown-menu">
                            <?php    
                            if ($_SESSION['trip'] = findOpenTrip($_SESSION['driverid'])){           
                            ?>
                            <li><a href="<?php echo SITE_URL; ?>/trips/current/<?php echo $_SESSION['driverid']; ?>">Ver viaje</a></li>
                            <?php } else { ?>
                            <li><a href="<?php echo SITE_URL; ?>/trips/list/<?php echo $_SESSION['driverid']; ?>">Mis viajes</a></li>
                            <?php } ?>
                            <li><a href="<?php echo SITE_URL; ?>/trips/stats/<?php echo $_SESSION['driverid'];?>">Estad&iacute;sticas</a></li>
                        </ul>
                    </li>
                    <!-- Usuario -->
                    <li class="dropdown <?php if ($section == "user") echo "active";?>">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-user fa-fw"></i>&nbsp;Usuario&nbsp;<i class="fa fa-caret-down"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="<?php echo SITE_URL; ?>/users/view/info/<?php echo $_SESSION['driverid']; ?>">Perfil</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/users/view/profile/<?php echo $_SESSION['driverid']; ?>">Editar el perfil</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/users/view/options/<?php echo $_SESSION['driverid']; ?>">Opciones</a></li>
                            <?php if ($_SESSION['rank']>USER_RANK_RECRUIT) { ?>
                            <li><a href="<?php echo SITE_URL; ?>/users/view/telemetry/<?php echo $_SESSION['driverid']; ?>">Telemetry</a></li>
                            <?php } ?>
                            <li role="separator" class="divider"></li>
                            <li><a href="<?php echo SITE_URL; ?>/logout">Cerrar sesi&oacute;n</a></li>
                        </ul>
                    </li>
                    <?php
                        if (isRecruiter()){
                    ?>
                    <!-- Admin -->
                    <li class="dropdown <?php if ($section == "siteadmin") echo "active";?>">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-cogs fa-fw"></i><span class="visible-xs-inline">&nbsp;Admnistraci&oacute;n</span>&nbsp;<i class="fa fa-caret-down"></i></a>
                        <ul class="dropdown-menu">
                            <li><a href="<?php echo SITE_URL; ?>/admin/recruits">Reclutas</a></li>
                            <?php if(isAdmin()){ ?>
                            <li><a id="convoystatus" href="#" data-userid="<?php echo $_SESSION['driverid']; ?>"><?php echo (CONVOY_ON == 1)?"Desactivar modo convoy":"Activar modo convoy"; ?></a></li>
                            <li><a href="https://logisticalatinoamericana.com:2096" target="_blank">Email</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/admin/driversnumbers">N&uacute;meros</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/admin/trips">Viajes</a></li>
                                <?php }
                                    if (isDeveloper()){
                                ?>
                                <li role="separator" class="divider"></li>
                                <li><a href="<?php echo SITE_URL; ?>/admin/config/edit">Configuraci&oacute;n</a></li>
                                <li><a href="<?php echo SITE_URL; ?>/admin/log">Log del sitio</a></li>
                                <li><a href="<?php echo SITE_URL; ?>/admin/chat/view">Log de chat</a></li>
                                <li role="separator" class="divider"></li>
                                <li><a href="https://logisticalatinoamericana.com:2083" target="_blank">CPanel</a></li>
                            <?php
                                    } // isDeveloper
                                } // isAdmin
                            ?>
                        </ul>
                    </li>
                <?php      
                 if (SITE_DEBUG_ON){
                    ?>
                    <li>
                        <a class="small" hreF="<?php printURL("/admin/config/edit");?>">DEBUG ON!</a>
                    </li>
                    <?php
                    }
                } // isLoggedIn
               
                ?>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <li><a href="http://soporte.logisticalatinoamericana.com/" target="_blank"><i class="fa fa-question" aria-hidden="true"></i>&nbsp;Soporte</a></li>
                </ul>
            </div><!-- /.navbar-collapse -->    

        </div><!-- /.container-fluid -->
    </nav>
    <?php
    if ($mapOptions){
    ?>
        <div id='map-options-overlay'>
            <button id='toggle-topbar' type='button' class='btn btn-default btn-sm' title='Ocultar barra superior'>
                <span class='glyphicon glyphicon-fullscreen' aria-hidden='true'></span>
            </button>
            <button id='toggle-online-drivers' type='button' class='pressed btn btn-default btn-sm' title='Mostrar usuarios en l&iacute;nea'>
                <span class='glyphicon glyphicon-list' aria-hidden='true'></span>
            </button>
            <button id='toggle-truck-info' type='button' class='pressed btn btn-default btn-sm' title='Mostrar informaci&oacute;n del cami&oacute;n seleccionado'>
                <span class='glyphicon glyphicon-dashboard' aria-hidden='true'></span>
            </button>
        <!--    <button id='toggle-traffic' type='button' class='btn btn-default btn-sm' title='Mostrar t&aacute;fico'><span class='glyphicon glyphicon-map-marker' aria-hidden='true'></span></button> -->
            <button id='toggle-chatbox' type='button' class='pressed btn btn-default btn-sm' title='Mostrar chat'>
                <span class='glyphicon glyphicon-comment' aria-hidden='true'></span>
            </button>
            <button id='toggle-routetracer' type='button' class='pressed btn btn-default btn-sm' title='Mostrar seguimiento'>
                <span class='glyphicon glyphicon-road' aria-hidden='true'></span>
            </button>
        </div>
    <?php    
    }
}
?>