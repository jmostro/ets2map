<?php 
function logEvent($log_type, $text, $query = ""){
    IF ($log_type == LOG_TYPE_DEBUG && !SITE_DEBUG_ON){
        return null;
    }
    if ($conn = dbOpen()){    
        $file = "";
        $func = "";
        if ($log_type >= LOG_TYPE_WARNING) {
            $backtrace = debug_backtrace();
            if (isset($backtrace[1]['function']) && (isset($backtrace[1]['file']))){
                $file = $backtrace[1]['file'];
                $func = $backtrace[1]['function'];
                $text .= "\n".$file." => ".$func;
            }    
        }
        if (isset($_SESSION['driverid'])) {
            $uid = $_SESSION['driverid'];
        } else {
            $uid = 0;
        }
        $text = mysqli_escape_string($conn,htmlents($text));
        $query = mysqli_escape_string($conn,$query);
        $log_type= intval($log_type);
        $squery = "INSERT INTO log (uid,type,timestamp,text,query) VALUES ($uid,$log_type,CURRENT_TIMESTAMP(),'$text','$query');";        
        mysqli_query($conn,$squery);
        dbClose($conn);
    }
}

function clearLog(){
    if (!isDeveloper()) return null;
    $squery = "TRUNCATE TABLE log;";
    $status  = dbTruncate($squery);
    return $status;
}

function countLogRecords($log_type = null){
    $where = "";
     if ($log_type){
        $where = "WHERE log.type=$log_type";
    }
    $squery = "SELECT COUNT(*) FROM log ".$where;
    $result = dbSelect($squery);
    if ($row = mysqli_fetch_array($result)){
        return $row[0];
    } 
    return 0;    
}

function getLogRecords($log_type = 0, $start = 0, $numrecs = RECORDS_PER_PAGE){
    $result = null;
    $where = "";   
    $limit = "LIMIT $start,$numrecs";        
    if ($log_type){
        $where = "WHERE log.type=$log_type";
    }
    $squery = "SELECT "
            ."log.id as id, "
            ."log.uid as uid, "
            ."log.type as type, "
            ."log.query as query, "
            ."log.timestamp as timestamp, "
            ."log.text as text, "
            ."drivers.username as username "
            ."FROM log INNER JOIN drivers ON drivers.id = log.uid"
            ." $where $limit";
    $result = dbSelect($squery);   
    return $result;
}
?>