<?php
require_once('inc/model.php');
require_once('inc/functions.php');
require_once('inc/config.php');
$uid = filter_input(INPUT_GET,'uid',FILTER_SANITIZE_NUMBER_INT);
global $user_rank_name;
if (($user = getDriverInfo($uid)) && ($stats = getUserStats($uid))){
    if ($user['rank'] == 0) die;   
    $im = imagecreatefrompng('img/signature.png') or die; 
    $chigh = imagecolorallocate($im, 255, 100, 0);
    $ctext = imagecolorallocate($im, 220, 220, 220); 
    $ctrans = imagecolorallocatealpha($im, 50, 50, 50, 30);
    $font = 'fonts/Roboto-Regular.ttf'; 

    imagefilledrectangle($im,360,5,595,190,$ctrans);
    imagettftext($im, 18, 0, 370, 36, $chigh, $font, $user['displayname']);  
    imagettftext($im, 12, 0, 370, 55, $ctext, $font, "(".$user_rank_name[$user['rank']].")");
    imagettftext($im, 14, 0, 370, 85, $ctext, $font, "Nro. empresa: ".getDriverNumber($uid));
    imagettftext($im, 14, 0, 370, 110, $ctext, $font, "Viajes realizados: ".$stats['trips']);
    imagettftext($im, 14, 0, 370, 135, $ctext, $font, "Recorridos: ".formatNumber($stats['driven'],0)." Km");
    imagettftext($im, 14, 0, 370, 160, $ctext, $font, "Ganancias: \$ ".formatNumber($stats['income']-$stats['expenses'],0));
    header('Content-Type: image/png;'); 
    imagepng($im); 
    imagedestroy($im);  
}
?>