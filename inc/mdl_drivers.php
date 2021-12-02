<?php
function countDrivers ($onlyOn = true){ 
    $squery = "SELECT COUNT(*) FROM drivers ";
     if ($onlyOn) 
       $squery .="WHERE rank > ".USER_RANK_DISABLED;
    
    $result = dbSelect($squery);
    if ($row = mysqli_fetch_array($result))
        return $row[0];
    
    return 0;
}

function listDrivers($onlyOn = true, $start = 0, $numrecs = RECORDS_PER_PAGE){
    $drivers=array();
    $squery = "SELECT "
            ."drivers.id AS id, "
            ."drivers.mp_id AS mp_id, "
            ."drivers.username AS username, "
            ."drivers.displayname AS name, "
            ."drivers.fullname AS fullname, "
            ."drivers.rank AS rank, "
            ."drivers.last_seen AS last_seen, "
            ."trucks.last_seen AS last_onroad "
            ."FROM drivers "
            ."INNER JOIN trucks ON drivers.id = trucks.owner ";
    if ($onlyOn) 
       $squery .="WHERE rank > ".USER_RANK_RECRUIT;
    
    $squery .=" LIMIT $start,$numrecs;";
    $result = dbSelect($squery);
    while ($row = mysqli_fetch_assoc($result)){    
        $drivers[] = $row;
        /*if (IS_IN_LOCAL_SERVER){
            $drivers[] = array_map('utf8_encode',$row);
        } else {
            $drivers[] = $row;
        }*/
    }
    return $drivers;
}

/**
* Obtiene listado de reclutas
*
* @param integer [$uid] id del conductor
* @return array viajes o null en caso de no encontrarse
*/
function getRecruits($typeofrec = 1){
    $squery = "SELECT "
        ."recruits.id AS id,"
        ."recruits.uid AS uid,"
        ."recruits.facebook AS facebook,"
        ."TIMESTAMPDIFF(YEAR, recruits.birthdate, CURDATE( )) AS age,"
        ."recruits.country AS country,"
        ."recruits.othervtc AS othervtc,"
        ."recruits.whylatam AS whylatam,"
        ."recruits.isDriver AS isDriver, "
        ."drivers.realusername AS realusername,"
        ."drivers.fullname AS fullname,"
        ."drivers.steam_id AS steam_id"
        ." FROM recruits INNER JOIN drivers"
        ." ON recruits.uid = drivers.id";
    if ($typeofrec == 1)
        $squery .= " WHERE recruits.isDriver = 0 AND drivers.rank = 1";
    else
        $squery .= " WHERE recruits.isDriver = 0 AND drivers.rank = 0";
    
    $squery .=" ORDER BY recruits.id DESC;";
    if ($result = dbSelect($squery))
        return $result;
    
    return null;
}

function countFilteredDrivers ($onlyOn = true, $filter_params){    
    $auxTime = TRUCK_ON_MAP_LIFETIME + SQL_PHP_TIME;            
    $filter_array = array();
    $filter_string = "";
    $squery = "SELECT "
            ."COUNT(*)"
            ."FROM drivers "
            ."INNER JOIN trucks "
            ."ON drivers.id = trucks.owner ";
    if ($onlyOn) 
       $filter_array[] ="rank > ".USER_RANK_DISABLED;

    if ($filter_params['name']){
        $filter_array[] = "(drivers.username LIKE '%".$filter_params['name']."%'"
                        ." OR drivers.displayname LIKE '%".$filter_params['name']."%'"
                        ." OR drivers.fullname LIKE '%".$filter_params['name']."%')";
    }
    if ($filter_params['onsite'])
        $filter_array[] = "drivers.last_seen +".$auxTime."> NOW()";
    
    if ($filter_params['onroad'])
        $filter_array[] = "trucks.last_seen +".$auxTime." > NOW()";
    
    foreach ($filter_array as $i => $filt)        
        $filter_string .= $filt." AND ";
    
    if ($filter_string != ""){
        $filter_string = substr($filter_string, 0, strlen($filter_string) - 5);
        $filter_string = " WHERE ".$filter_string;
    }
    $squery .= $filter_string;   
    $result = dbSelect($squery);
    if ($row = mysqli_fetch_array($result))
        return $row[0];

    return 0; 
}

function listFilteredDrivers($onlyOn = true, $filter_params, $start = 0, $numrecs = RECORDS_PER_PAGE){
    $drivers=array();
    $auxTime = TRUCK_ON_MAP_LIFETIME;// + SQL_PHP_TIME;            
    $filter_array = array();
    $filter_string = "";
    $squery = "SELECT "
            ."drivers.id AS id, "
            ."drivers.mp_id AS mp_id, "
            ."drivers.username AS username, "
            ."drivers.displayname AS name, "
            ."drivers.fullname AS fullname, "
            ."drivers.rank AS rank, "
            ."drivers.last_seen AS last_seen, "
            ."trucks.last_seen AS last_onroad "
            ."FROM drivers "
            ."INNER JOIN trucks "
            ."ON drivers.id = trucks.owner ";
    if ($onlyOn) {
       $filter_array[] ="rank > ".USER_RANK_DISABLED;
    }
    if ($filter_params['name']!=""){
        $filter_array[] = "(drivers.username LIKE '%".$filter_params['name']."%'"
                        ." OR drivers.displayname LIKE '%".$filter_params['name']."%'"
                        ." OR drivers.fullname LIKE '%".$filter_params['name']."%')";
    }
    if ($filter_params['onsite']){        
        $filter_array[] = "drivers.last_seen + $auxTime > NOW()";
    }
    if ($filter_params['onroad']){
        $filter_array[] = "trucks.last_seen + $auxTime > NOW()";
    }
    foreach ($filter_array as $filt) {
        $filter_string .= $filt." AND ";
    }
    if ($filter_string != ""){
        $filter_string = substr($filter_string, 0, strlen($filter_string) - 5);
        $filter_string = " WHERE ".$filter_string;
    }
    $squery .= $filter_string;
    $squery .=" LIMIT $start,$numrecs;";
    $result = dbSelect($squery);
    while ($row = mysqli_fetch_assoc($result)){    
        if (IS_IN_LOCAL_SERVER)
            $drivers[] = array_map('utf8_encode',$row);
        else
            $drivers[] = $row;
    }
    return $drivers;
}

function validateDriver($id, $secret){
    $squery = "SELECT secret FROM drivers WHERE id = $id LIMIT 1;";
    $result = dbSelect($squery);        
    if ($row = mysqli_fetch_array($result))
        if ($secret == $row['secret'])
            return true;
    return false;
}

function getUserField($uid, $fieldName){
	logEvent(LOG_TYPE_WARNING,"Llamado a función vieja");
	return getDriverField ($uid,$fieldName);
}

function getDriverField($uid, $fieldName){
    $squery = "SELECT $fieldName FROM drivers WHERE id=$uid LIMIT 1;";
    $result = dbSelect($squery);    
    if ($row = mysqli_fetch_array($result))
        return $row[$fieldName];
}

function agreeTerms($uid){
    if (!$uid) return null;
    $squery = "UPDATE drivers SET agreed=1 where id=$uid LIMIT 1";
    if(dbUpdate($squery))
        return true;
}

function getLoginInfo ($name){
    $squery = "SELECT id, password, fullname, secret, rank FROM drivers WHERE LOWER(username)='$name' LIMIT 1";
    if ($result = dbSelect($squery))
        if ($row = mysqli_fetch_assoc($result))
            return $row;
        else
            return false;
}

function getDriverNumber($uid){
    if(!$uid) return null;
    $squery = "SELECT id FROM driversnumbers WHERE uid = $uid;";
    $result = dbSelect($squery);
    if($row = mysqli_fetch_assoc($result))
        return $row['id'];
    else
        return null;
}

function getDriverCountry($uid){
    if(!$uid) return null;
    $squery = "SELECT country FROM recruits WHERE uid = $uid;";
    $result = dbSelect($squery);    
    if ($row = mysqli_fetch_assoc($result))
        return $row['country'];
}

function getDriverInfo($uid, $recruit = false){
    if (!$uid) return null;
    $squery = "SELECT "
        ."drivers.id AS id, "
        ."drivers.mp_id AS mp_id, "
        ."drivers.username AS username, "
        ."drivers.fullname AS fullname, "
        ."drivers.displayname AS displayname, "
        ."drivers.rank AS rank, "
        ."drivers.last_seen AS last_seen, "
        ."drivers.registered AS registered, "
        ."drivers.email AS email, "
        ."drivers.ip_address AS ip_address, "
        ."drivers.secret AS secret, "
        ."drivers.agreed AS agreed, "
        ."drivers.steam_id AS steam_id, "
        ."drivers.img_truck AS img_truck, "
        ."drivers.wot_profile AS wot_profile, ";
    if ($recruit) $squery .= "recruits.facebook AS facebook,
                              recruits.country as country,
                              TIMESTAMPDIFF(YEAR, recruits.birthdate, CURDATE()) AS age, ";
    $squery .= "trucks.last_seen AS last_onroad "
        ."FROM drivers "
        ."INNER JOIN trucks ON drivers.id = trucks.owner ";
    if ($recruit) $squery .= "INNER JOIN recruits ON drivers.id = recruits.uid ";
        $squery .= "WHERE drivers.id=$uid;";
    $result = dbSelect($squery);
    if ($row = mysqli_fetch_array($result)){
       $row['trip'] = findOpenTrip($uid);
       return $row;        
    }    
}

function getUserInfo($uid){
	logEvent(LOG_TYPE_WARNING,"Llamado a función vieja");
	return getDriverinfo($uid);
}

function getDriverOptions($uid){
    $squery = "SELECT * FROM user_options WHERE uid=$uid LIMIT 1;";
    $result = dbSelect($squery);
    if ($row = mysqli_fetch_assoc($result))
        return $row;
}

function validatePassword($uid, $pass) {
    if ($conn = dbOpen()) {
        $squery = "SELECT password FROM drivers WHERE id=$uid";
        $result = mysqli_query($conn,$squery);
        if ($row = mysqli_fetch_array($result))
            if ($row['password'] == MD5($pass))
                return true;
    }
    return false;
}

function generateNewSecret($uid){
    if ($conn = dbOpen()) {
        $newSecret = randomString(10);
        $squery = "UPDATE drivers SET secret='$newSecret' WHERE id=$uid LIMIT 1;";
        mysqli_query($conn,$squery);
        dbClose($conn);
        return $newSecret;
    } 
}

function updateLastSeen($uid) {
    $ipaddr = $_SERVER['REMOTE_ADDR'];
    if ($conn = dbOpen()){
        $squery = "UPDATE drivers SET last_seen=CURRENT_TIMESTAMP(), ip_address='$ipaddr' WHERE id=$uid LIMIT 1;";        
        mysqli_query($conn, $squery);        
    }    
}

function getDriverName($uid){
   if  ($uid == $_SESSION['driverid'])
        return $_SESSION['name'];
    else
        return getDriverField($uid,"displayname");   
}

// Obtengo los usuarios que estan en los distintos servidores
function usuariosOnSever($tSeconds = TRUCK_ON_MAP_LIFETIME){
$trucks = array();
$i = 0;
    $squery = "SELECT "
        ."trucks.servername, "
        ."trucks.game, "
        ."drivers.displayname "
        ."FROM trucks "
        ."INNER JOIN drivers "
        ."ON drivers.id = trucks.owner";
    if ($tSeconds > -1)
        $squery .= " WHERE trucks.last_seen + ".$tSeconds." > NOW()";

    $squery .= " ORDER BY trucks.game DESC, trucks.servername ASC";
    $result = dbSelect($squery);
    while ($row = mysqli_fetch_assoc($result)){
        if($row['servername']!="Offline")
            $trucks[$row['game']][$row['servername']][] = $row['displayname'];
        $i++;
    }
    return array('cantidad' => $i) + $trucks;
}
?>