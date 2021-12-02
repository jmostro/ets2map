<?php
function dbOpen(){    
    $dbhandle = mysqli_connect(DB_HOSTNAME,DB_USERNAME,DB_PASSWORD,DB_NAME);
    if ($dbhandle) {
        return $dbhandle;    
    } else { 
        die("No se pudo conectar a la base de datos.");
    }
}

function dbClose($dbhandle){
    mysqli_close($dbhandle);
}

function dbInsert($query){
    if($conn = dbOpen()){
        mysqli_query($conn, "SET NAMES 'utf8'");
        $result = mysqli_query($conn,$query);
        $insertID = mysqli_insert_id($conn);
        if ($insertID < 1) {
            logEvent(LOG_TYPE_DBERROR,mysqli_error($conn),$query);
        }        
        dbClose($conn);
        return $insertID;
    }
}

function dbDelete ($query){
    if ($conn = dbOpen()){
        $result = mysqli_query($conn,$query);
        if (!$result) {
            logEvent(LOG_TYPE_DBERROR,mysqli_error($conn),$query);   
        } else { 
            $result = mysqli_affected_rows($conn);
        }
        dbClose($conn);
        return $result;
    }
}

function dbTruncate($query){
    if ($conn = dbOpen()){
        $result = mysqli_query($conn,$query);
        if (!$result) {
            logEvent(LOG_TYPE_DBERROR,mysqli_error($conn),$query);   
        }
        dbClose($conn);
        return $result;
    }
}

function dbUpdate($query){
    if ($conn = dbOpen()){
        mysqli_query($conn, "SET NAMES 'utf8'");
        $result = mysqli_query($conn,$query);        
        if (!$result) {
            logEvent(LOG_TYPE_DBERROR,mysqli_error($conn),$query);   
        }
        dbClose($conn);
        return $result;
    }
}

function dbSelect($query){
    if ($conn = dbOpen()){
        mysqli_query($conn, "SET NAMES 'utf8'");
        $result = mysqli_query($conn,$query);        
        if (!$result) {
            logEvent(LOG_TYPE_DBERROR,mysqli_error($conn),$query);
        }
        dbClose($conn);
        return $result;
    }
}
?>