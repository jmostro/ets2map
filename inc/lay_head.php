<?php
/**
    HEADER DEL SITIO
**/
function layoutHead ($sectionName  = "", $filesToLoad = array()){
    global $site_themes;
    (isset($_SESSION['theme']))?$theme = $_SESSION['theme']:$theme = 1;
    $bsFile = "/css/bs-".strtolower($site_themes[$theme]).".css";
    ?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Logistica Latinoamericana">
        <meta name="author" content="">
        <title><?php echo $sectionName; ?> - Log&iacute;stica Latinoamericana</title>

        <link rel="icon" type="image/ico" href="<?php echo SITE_URL; ?>/favicon.ico">
        <!-- Bootstrap Theme -->
        <link href="<?php echo SITE_URL.$bsFile; ?>" rel="stylesheet" class="theme">
        <!-- Bootstrap table CSS -->
        <link href="<?php echo SITE_URL; ?>/css/bootstrap-table.min.css" rel="stylesheet">        
        <!-- Checkbox style for bootstrap -->
        <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/awe-checkbox.css" /> 
        <!-- Leaflet map plugin styles -->
        <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/leaflet.css" /> 
        <!-- AWE Fonts -->
        <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/font-awesome.min.css">
        <!-- Custom CSS -->      
        <link href="<?php echo SITE_URL; ?>/css/site.css" rel="stylesheet">
        <link href="<?php echo SITE_URL; ?>/css/animate.css" rel="stylesheet">
        <?php
        if(!empty($filesToLoad))
            foreach ($filesToLoad as $file)
                echo "<link href='".SITE_URL."/css/$file' rel='stylesheet'>\n";
        ?>
        <script>
        document.mybaseurl = "<?php echo SITE_URL; ?>";
        </script>
        <!-- jQuery -->
        <script src="<?php echo SITE_URL; ?>/js/jquery.js"></script>
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
          <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>
<?php
}

/**
    INICIO DEL CUERPO
**/
function layoutInitBody(){
?>
    <body>
<?php
}

/**
    CONTENEDOR PRINCIPAL
**/
function layoutInitWrapper(){
?>
        <div id="wrapper">
<?php
}
?>