<?php
require_once(__DIR__.'/model.php');
require_once(__DIR__.'/config.php');
function htmlents($text) {
    return htmlentities($text,ENT_QUOTES, "UTF-8");
}

function printNumber($number,$decimals = 2){
    echo number_format($number,$decimals,",",".");
}

function formatNumber($number,$decimals = 2){
    return number_format($number,$decimals,",",".");
}

function clearURLSlashes($url){
    if (substr($url, 0,1) == "/")
        $url = substr($url,1, strlen($url)-1);

    if (substr($url,strlen($url)-1,1) == "/")
        $url = substr($url, 0, strlen($url)-1);

    return $url;    
}

function printURL($url){
    $url = clearURLSlashes($url);
    echo SITE_URL."/".$url;
}

function randomString($length = 10)
{
    $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $randstring = '';
    for ($i = 0; $i < $length; $i++) 
        $randstring.= $characters[rand(0, strlen($characters)-1)];

    return $randstring;
}

function generateUserSession($id) {
    $userData = getDriverInfo($id);
    $trip = findOpenTrip($id);
    $options = getDriverOptions($id);
    $_SESSION['driverid'] = $userData['id'];
    $_SESSION['username'] = $userData['username'];
    $_SESSION['name'] = $userData['displayname'];
    $_SESSION['rank'] = $userData['rank'];
    $_SESSION['trip'] = $trip;
    $_SESSION['theme'] = $options['theme'];
    $_SESSION['agreed'] = $userData['agreed'];
    updateLastSeen($userData['id']);
}

function gotoUrl($url, $msgType = 0, $message = "") { 
    $url = clearURLSlashes($url);
    $_SESSION['site_msg'] = $message;
    $_SESSION['site_msg_t'] = $msgType;
    if (strtolower(substr($url, 0,4)) !== "http") 
        $url = SITE_URL.'/'.$url;

    header('Location: '.$url);
}

function isLoggedIn() {
	if ((isset($_SESSION['driverid'])) && ($_SESSION['rank'] > USER_RANK_DISABLED)) {
        updateLastSeen($_SESSION['driverid']);
		return true;
	} 
    if ($uid = tryWithCookie()) {
        generateUserSession($uid);
        return true;
    }
	return false;	
}

function isDriver(){
    if (isset($_SESSION['rank']))
        return ($_SESSION['rank'] >= USER_RANK_DRIVER);

    return false;
}

function isRecruiter(){
    if (isset($_SESSION['rank']))
        return ($_SESSION['rank'] >= USER_RANK_RECRUITER);

    return false;
}

function isAdmin(){
    if (isset($_SESSION['rank']))
        return ($_SESSION['rank'] >= USER_RANK_MANAGER);

    return false;
}

function isDeveloper(){
    if (isSet($_SESSION['rank']))
        return ($_SESSION['rank'] >= USER_RANK_DEVELOPER);

    return false;
}

function canEdit ($uid, $rank = 0) {
    if (!isLoggedIn())
        return 0;

    if ($uid == $_SESSION['driverid'])
        return 1;
    
    if ($_SESSION['rank'] >= USER_RANK_SUPERVISOR) {
        ($rank)?$userRank = $rank:$userRank = getDriverField($uid,'rank');    
        if ($_SESSION['rank'] > $userRank) 
            return 2;
    }
    return 0;
}

function canSupervise ($uid){
      if (!isLoggedIn())
        return false;
    
    if ($_SESSION['rank'] >= USER_RANK_SUPERVISOR) {
        if ($uid == $_SESSION['driverid'])
            return true;

        $userRank = getDriverField($uid,'rank');    
        if ($_SESSION['rank'] > $userRank)
            return true;
    }
}

function canAdmin ($uid){
    if (!isLoggedIn())
        return false;

    if ($_SESSION['rank'] >= USER_RANK_MANAGER) {
        $userRank = getDriverField($uid,'rank');  
        if ($_SESSION['rank'] > $userRank)
            return true;
    }
}

function generateCookie($name, $secret) {
      setcookie (KEEPALIVE_COOKIE_NAME, 'name='.$name.'&s='.$secret, time() + CLIENT_SESSION_LIFETIME);
}

function tryWithCookie(){
    if(isSet($_COOKIE[KEEPALIVE_COOKIE_NAME])){
         parse_str($_COOKIE[KEEPALIVE_COOKIE_NAME]);
         /*
         $uid : user id
         $s : user secret
         */

         $userData = getLoginInfo($name);

         if (($userData['rank'] > USER_RANK_DISABLED) && ($userData['secret'] == $s))
            return $userData['id'];
     }     
}

function logFile ($text){
    $file = 'log.txt';
    
    if (file_exists($file))
        $fd = fopen($file,'a');
    else
        $fd = fopen($file, 'w');

    fwrite($fd,date('Y/m/d h:i:sa'));
    fwrite($fd," -- ");
    fwrite($fd, $text);
    fwrite($fd, PHP_EOL);
    fclose($fd);
}

function paginationData ($numRecords, $page = null, $lastIsDefault = false){            
    $divRecords = $numRecords / RECORDS_PER_PAGE;
    ($numRecords % RECORDS_PER_PAGE)?$extraPage = 1:$extraPage = 0;
    $numPages = intval($numRecords / RECORDS_PER_PAGE) + $extraPage;
    if (!$numPages) $numPages = 1;
    if (!$page) 
     ($lastIsDefault)?$page = $numPages : $page = 1;         

    $correct = 0;
    if ($page - PAGE_OFFSET < 1) 
        $first_page=1;                
    else
        $first_page=$page - PAGE_OFFSET;    

    if ($page + PAGE_OFFSET > $numPages)
        $last_page = $numPages;
    else 
         $last_page = $page + PAGE_OFFSET;
    
    $pagination = array(
        'records' => $numRecords,
        'pages' => $numPages,
        'current_page' => $page,
        'first_rec' => ($page -1) * RECORDS_PER_PAGE,
        'num_rec' => RECORDS_PER_PAGE,
        'first_page' => $first_page,
        'last_page' => $last_page
    );
    return $pagination;    
}

function layoutPageBar($pagData, $url){
    if (substr($url, 0, 10) == "javascript") {
        $isJs = true;
    } else {
        $isJs = false;
        if (substr($url, 0,1) == "/")
            $url = substr($url,1, strlen($url)-1);
        if (substr($url,strlen($url)-1,1) == "/")
            $url = substr($url, 0, strlen($url)-1);
    }
    ?>
    <center>
    <ul class="pagination">
    <?php
    echo "<li>";
    if ($pagData['first_page']>1){
        if ($isJs)
            echo "<a href='".$url."(1)'>";
        else
            echo "<a href='".SITE_URL."/".$url."/page/1'>";

        echo "<";
        echo "</a></li>";        
    }
    for ($i = $pagData['first_page']; $i <= $pagData['last_page']; $i++){
        ($i == $pagData['current_page'])?$active='class="active"':$active='';
        echo "<li ".$active.">";
        if ($isJs)
            echo "<a href='".$url."(".$i.")'>";
        else 
            echo "<a href='".SITE_URL."/".$url."/page/".$i."''>";

        echo $i;
        echo "</a></li>";
    }
    if ($pagData['last_page'] < $pagData['pages']){
        echo "<li>";
        if ($isJs)
            echo "<a href='".$url."(".$pagData['pages'].")'>";
        else
            echo "<a href='".SITE_URL."/".$url."/page/".$pagData['pages']."''>";

        echo ">";
        echo "</a></li>";
    }
    ?>
    </ul>
    </center>
    <?php
}
?>