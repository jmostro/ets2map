<?php
Header ("Content-type: image/jpeg");
$driver = imagecreatefromjpeg("img/drivers/driver_".filter_input(INPUT_GET, "n", FILTER_SANITIZE_NUMBER_INT).".jpg");
$country = imagecreatefrompng("img/flags/".filter_input(INPUT_GET, "c", FILTER_SANITIZE_STRING).".png");
imagealphablending($country, true);
imagesavealpha($country, true);
// Copiar y fusionar
imagecopy($driver, $country, 0, 0, 0, 0, 1275, 1276);
imagejpeg($driver);

imagedestroy($driver);
imagedestroy($country);
?>