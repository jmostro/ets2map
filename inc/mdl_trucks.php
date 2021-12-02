<?php
function getAliveTrucks ($tSeconds = TRUCK_ON_MAP_LIFETIME){    
    $trucks = array();
    $squery = "SELECT 
        trucks.id AS id, 
        trucks.posx, 
        trucks.posy, 
        trucks.posz, 
        trucks.owner AS driverid, 
        trucks.telemetry AS telemetry, 
        trucks.speed, 
        trucks.fuel_capacity, 
        trucks.fuel_load, 
        trucks.brand, 
        trucks.model, 
        trucks.trailer_on, 
        trucks.trailer_name, 
        trucks.trailer_mass, 
        trucks.job_income, 
        trucks.job_source, 
        trucks.job_destination, 
        trucks.nav_distance, 
        trucks.servername, 
        trucks.game, 
        drivers.fullname AS fullname, 
        drivers.displayname AS drivername, 
        drivers.mp_id AS ets2mp_id";
   /*     ."companystart.posx AS posxinitcomp, companystart.posy AS posyinitcomp, "
        ."companyend.posx AS posxendcomp, companyend.posy AS posyendcomp "*/
    $squery .= " FROM trucks INNER JOIN drivers ON drivers.id = trucks.owner ";
      /*  ."INNER JOIN cities AS citystart ON citystart.cityname = trucks.job_source "
        ."INNER JOIN cities AS cityend ON cityend.cityname = trucks.job_destination "
        ."INNER JOIN companies as companystart ON companystart.companyname = trucks.job_src_cmpy "
        ."INNER JOIN companies as companyend ON companyend.companyname = trucks.job_des_cmpy "
        ."WHERE citystart.id = companystart.cityid AND cityend.id = companyend.cityid ";*/
    if ($tSeconds > -1) {
        $squery.= "WHERE trucks.last_seen + ".$tSeconds." > NOW()";
    } else{
        $squery.= "WHERE trucks.last_seen > 0";
    }
    $result = dbSelect($squery);
    while ($row = mysqli_fetch_assoc($result)){
        // Fix para que las coordenadas coincidan en el mapa.
        $pos = translateTruckCoordinates(array('x' => $row['posx'], 'z' => $row['posz']));
        $row['posx'] = $pos['x'];
        $row['posz'] = $pos['z'];
        $trucks[] = $row;
        /*
        if (IS_IN_LOCAL_SERVER){
            $trucks[] = array_map('utf8_encode', $row);
        } else {
            $trucks[] = $row;
        }*/
    }
    return $trucks;
}

function getTruckInfo ($uid){
    $squery = "SELECT "
            ."trucks.id AS truckid, "
            //."trucks.posx, "
            //."trucks.posy, "
            //."trucks.posz, "
            ."trucks.owner AS driverid, "
            ."trucks.telemetry AS telemetry, "
            ."trucks.last_seen AS last_seen, "
            ."trucks.speed AS speed, "
            ."trucks.fuel_capacity AS fuel_capacity, "
            ."trucks.fuel_load AS fuel_load, "
            ."trucks.brand, "
            ."trucks.model, "
            ."trucks.dmg_engine, "
            ."trucks.dmg_transmission, "
            ."trucks.dmg_wheels, "
            ."trucks.dmg_cabin, "
            ."trucks.dmg_chasis, "
            ."trucks.trailer_on, "
            ."trucks.trailer_dmg, "
            ."trucks.trailer_name, "
            ."trucks.trailer_mass AS trailer_mass, "
            ."trucks.job_income AS job_income, "
            ."trucks.job_source, "
            ."trucks.job_destination, "
            ."trucks.job_src_cmpy, "
            ."trucks.job_des_cmpy, "
            ."ROUND(trucks.odometer,2) AS odometer, "
            ."ROUND(trucks.nav_distance,2) AS nav_distance, "
            ."trucks.servername, "
            ."trucks.game "
            //."drivers.fullname AS fullname, "
            //."drivers.displayname AS drivername, "
            //."drivers.mp_id AS ets2mp_id "
            ."FROM trucks INNER JOIN drivers ON drivers.id = trucks.owner "
            ."WHERE trucks.owner = $uid";
    if ($result = dbSelect($squery)){      
        while ($row = mysqli_fetch_assoc($result)){
        // Fix para que las coordenadas coincidan en el mapa.
        /*
        $pos = translateTruckCoordinates(array('x' => $row['posx'], 'z' => $row['posz']));
        $row['posx'] = $pos['x'];
        $row['posz'] = $pos['z'];        
        
            if (IS_IN_LOCAL_SERVER){
                $truck = array_map('utf8_encode', $row);
            } else {
                $truck = $row;
            }*/
            $truck = $row;
        }
        return $truck;
    }                
}
/*
function mainServerUpdate (){
    // TODO: Agregar resto de los servidores
    $mapTrackerURL = "http://tracker.ets2map.com:8080/request/0/0/200000";
    $runTime = 10; // tiempo en segundos que debe pasar para poder ejecutar la llamada a ets2map.com
    $flagFile = "serverCall.tmp"; // TODO: cambiar el flag segun servidor
    // Controlar si podemos ejecutar la llamada al server de ets2map.com
    if (file_exists($flagFile))
        $lastRun = filemtime($flagFile); 
    else
        $lastRun = 0;

    if ($lastRun + $runTime < time()){
        $fp = fopen($flagFile, 'w');
        fwrite($fp, $lastRun);
        fclose($fp);
        // Obtener usuarios en linea y listado de usuarios de la empresa
        $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $mapTrackerURL,
            CURLOPT_USERAGENT => 'L-LATAM Map Server'
        ));        
        $serverData = json_decode(curl_exec($curl));
        curl_close($curl); 
        $driversToUpdate = array();
        $drivers = listDrivers();
        // Iterar por la información del servidor, actualizando cada vez que se encuentre un usuario de la empresa
        foreach ($serverData->Trucks as $index => $onlineDriver) {
            foreach ($drivers as $index2 => $companyDriver) {                
                if ($companyDriver['mp_id'] == $onlineDriver->ets2mp_id){                    
                    $driversToUpdate[]=array(
                        'id' => $companyDriver['id'],
                        'x' => $onlineDriver->x,
                        'y' => $onlineDriver->y
                    );                    
                }
            }
        }      
        updateTruckPos($driversToUpdate,0);
    } 
}*/

function updateTruckPos($driversToUpdate, $telemetry = 1){    
    if (count($driversToUpdate)){
        foreach ($driversToUpdate as $driver) {
            $squery = "UPDATE trucks SET posx=".$driver['x'].",posz=".$driver['y'].",telemetry=".$telemetry." WHERE owner=".$driver['id'];
            if (!$telemetry)
                $squery.=" AND last_seen + ".TRUCK_TELEMETRY_LIFETIME." < NOW()";

            $squery.=" LIMIT 1;";
            dbUpdate($squery);
        }
    }
}

/*
*Comprueba si la telemetría inicio un viaje, en ese caso inicia el viaje con la función initializeTrip
*las comprobaciones ya se realizan en el telemetry (para dar por válido el inicio del viaje)
*/
function updateTruck($driverId, $secret, $data) {
    if ($data->Game->Connected) {
        $game = $data->Game;
        $trailer = $data->Trailer;
        $truck = $data->Truck;
        $job = $data->Job;
        $nav = $data->Navigation;
        $road = $data->Roadtrip;

        if ($trailer->Attached == true){
            $trailer_name = addslashes($trailer->Name);
            $trailer_mass = $trailer->Mass;
            $trailer_dmg  = $trailer->Wear;
            $job_income = $job->Income;
            $job_source = addslashes($job->SourceCity);
            $job_src_cmpy = addslashes($job->SourceCompany);
            $job_destination = addslashes($job->DestinationCity);            
            $job_des_cmpy = addslashes($job->DestinationCompany);
            $nav_distance = $nav->EstimatedDistance;
        } else {
            $trailer_attached = 0;
            $trailer_name = "";
            $trailer_dmg = 0;
            $trailer_mass = 0;
            $job_income = 0;
            $job_source = "";
            $job_src_cmpy = 0;
            $job_destination = "";
            $job_des_cmpy = 0;
            $nav_distance = 0;    
        }        

        $squery = "UPDATE trucks INNER JOIN drivers ON trucks.owner = drivers.id SET 
                    trucks.posx = ".$truck->Placement->X.", 
                    trucks.posy = ".$truck->Placement->Y.",
                    trucks.posz = ".$truck->Placement->Z.", 
                    trucks.telemetry = 1, 
                    trucks.speed = ".$truck->Speed.", 
                    trucks.fuel_capacity = ".$truck->FuelCapacity.", 
                    trucks.fuel_load = ".$truck->Fuel.", 
                    trucks.brand = '".$truck->Make."', 
                    trucks.model = '".$truck->Model."', 
                    trucks.dmg_engine = ".$truck->WearEngine.", 
                    trucks.dmg_transmission = ".$truck->WearTransmission.", 
                    trucks.dmg_wheels = ".$truck->WearWheels.", 
                    trucks.dmg_cabin = ".$truck->WearCabin.", 
                    trucks.dmg_chasis = ".$truck->WearChassis.",";
        if($trailer->Attached)
            $squery .= "trucks.trailer_on = 1,";
        else
            $squery .= "trucks.trailer_on = 0,";
            $squery .= "trucks.trailer_name = '".$trailer_name."', 
                    trucks.trailer_mass = ".$trailer_mass.", 
                    trucks.trailer_dmg = ".$trailer_dmg.", 
                    trucks.job_income = ".$job_income.", 
                    trucks.job_source = '".$job_source."', 
                    trucks.job_destination = '".$job_destination."', 
                    trucks.job_src_cmpy = '".$job_src_cmpy."', 
                    trucks.job_des_cmpy = '".$job_des_cmpy."', 
                    trucks.nav_distance = ".$nav_distance.", 
                    trucks.odometer = ".$truck->Odometer.", 
                    trucks.servername = '".$road->OnlineStatus."', 
                    trucks.game = '".addslashes($game->gameName)."', 
                    trucks.last_seen = CURRENT_TIMESTAMP()
                    WHERE trucks.owner = $driverId AND drivers.rank > 1 AND drivers.secret = '".$secret."';";
        dbUpdate($squery);
        // Viajes
        // Veo si hay un viaje abierto
        $hayViaje = findOpenTrip($driverId);
        if(is_null($hayViaje)){
            // Me fijo si inicio uno el telemetry
            if($road->TelemetryStatus == "iniciado" && CONVOY_ON == 0){
                $tripId = initializeTrip($driverId, $data);      
                if ($tripId)
                    $_SESSION['trip']=$tripId;
            }
        }else{ // Hay un viaje abierto
            // Me fijo si la telemetria lo finalizo
            if($road->TelemetryStatus == "finalizado"){
                if(endTrip($hayViaje, $data)){
                    if ($driverId == $_SESSION['driverid'])
                        $_SESSION['trip'] = null;
                }
            }else if($road->TelemetryStatus == "cancelado"){ // Me fijo si la telemetria lo cancelo (por algún motivo)
                if (deleteTrip($hayViaje['id'])){
                    if ($driverId == $_SESSION['driverid'])
                        $_SESSION['trip'] = null;
                }
            }
        }
    }
}

function translateTruckCoordinates($truckPos){
    if ($truckPos['x'] < -31412 && $truckPos['z'] < -5618) {
        // UK
        $ppp = 9.69522;
        $x0 = 10225;
        $y0 = 23910;
    } else {
        //EUROPA
        $ppp = 7.278;
        $x0 = 11366;
        $y0 = 24046;
    }
    $truckPos['x'] = strval(intval($truckPos['x'] / $ppp + $x0));
    $truckPos['z'] = strval(intval($truckPos['z']/ $ppp + $y0));
    return $truckPos;
}

function getTruckPos($id) {
    $squery = "SELECT posx,posy,posz FROM trucks WHERE id=$id;";   
    $result = dbSelect($squery);
    if ($row = mysqli_fetch_array($result)) {
        $truckPos = array(
            "id" => $id,
             "x" => $row['posx'],
             "y" => $row['posy'],
             "z" => $row['posz']
        );           
        return translateTruckCoordinates($truckPos);
    }
}
?>