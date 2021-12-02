<?php
session_start();
require_once('inc/functions.php');
if (!isLoggedIn()){
    die;
}
 // TODO: Agregar resto de los servidores
    $mapTrackerURL = "http://tracker.ets2map.com:8080/request/0/0/220000";
    $runTime = 5; // tiempo en segundos que debe pasar para poder ejecutar la llamada a ets2map.com
    $flagFile = "serverCall.tmp"; // TODO: cambiar el flag segun servidor
    
    // Obtener usuarios en linea y listado de usuarios de la empresa
    $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $mapTrackerURL,
        CURLOPT_USERAGENT => 'L-LATAM Map Server'
    ));        
    $serverData = json_decode(curl_exec($curl));
    curl_close($curl); 
    
    //$drivers = listDrivers();
    // Iterar por la información del servidor, actualizando cada vez que se encuentre un usuario de la empresa
    foreach ($serverData->Trucks as $index => $onlineDriver) {
        echo "<a href='".$onlineDriver->ets2mp_id."' style='text-decoration:none; color: black;'>";
        echo $onlineDriver->name."  ( ".$onlineDriver->id." )";
        echo "</a><br>";
/*
        foreach ($drivers as $index2 => $companyDriver) {
            //echo $companyDriver['mp_id']." - ".$companyDriver['fullname'];                
            if ($companyDriver['mp_id'] == $onlineDriver->ets2mp_id){                    
                updateTruckPos($companyDriver['id'],$onlineDriver->x,$onlineDriver->y);
            }
        }
        */
    }

?>