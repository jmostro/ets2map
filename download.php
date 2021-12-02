<?php
session_start();
require_once(__DIR__.'/inc/config.php');
require_once(__DIR__.'/inc/model.php');
require_once(__DIR__.'/inc/functions.php');

$action = filter_input(INPUT_GET, "a", FILTER_SANITIZE_STRING);
if (!isLoggedIn()){
  die;
}
$id = filter_input(INPUT_GET,'id',FILTER_SANITIZE_NUMBER_INT);
switch ($action) {
  case 'getuserdata':
    downloadData($id);
    break;
  case 'gettelemetry':
    downloadTelemetry($id);
    break;
}

function downloadData($id) {
  $user = getDriverInfo($id); // Obtengo el Steam ID
  $pass = filter_input(INPUT_POST,'tpassword',FILTER_SANITIZE_STRING);
  $editPW = canEdit($id);


  if ($editPW) {
    $canDownload = (($editPW == 2) || (validatePassword($id,$pass)));
    if (!$canDownload){noDownload("Clave incorrecta.");die;}    
    if ($editPW == 1) {
        logEvent(LOG_TYPE_EVENT,"Genero UserData.dat telemetry");    
    } else {
        logEvent(LOG_TYPE_ADMIN,"Genero UserData.dat telemetry para el id ".$id);
    }
    $secret = generateNewSecret($id);
	  downloadFile($user['steam_id'], SITE_URL, $secret, CLIENT_UPDATE_INTERVAL);			
  }
}

function noDownload($message) {
?>
	<html>
    <head>
      <title>Error</title>
    </head>
  	<body>
    	<script type="text/javascript">
    	   alert("<?php echo $message; ?>");
    	   close();
    	 </script>
	  </body>
  </html>
<?php
}

function downloadFile($id, $url, $secret, $interval) {
	header('Content-type: text/plain');
	header('Content-Disposition: attachment; filename="UserData.dat"');
  echo $url.'/truckset/'.$secret.'/'.$id;
  echo "\n";
  echo $interval;
}

function downloadTelemetry($ver) {
  /* Usando la clase de php...
  $files = array("../download/Telemetry/Beta/Form/autorun.inf", "../download/Telemetry/Beta/Form/LLatamTelemetryForm.application", "../download/Telemetry/Beta/Form/setup.exe");
  $zipname = "telemetry.zip";
  $zip = new ZipArchive;
  $zip->open($zipname, ZipArchive::CREATE);
  foreach ($files as $file) {
    $zip->addFile($file);
  }
  $zip->close();*/

  include ('/home/logisticalatinoa/php/Archive/Zip.php'); // Utilizo la clase de pear
  if($ver==1){
    $zipname = "Telemetry-Ver1.zip";
    $files = array("../download/Telemetry/Beta/WPF/autorun.inf", "../download/Telemetry/Beta/WPF/LLatamTelemetry.application", "../download/Telemetry/Beta/WPF/setup.exe");
  }
  else{
    $zipname = "Telemetry-Ver2.zip";
    $files = array("../download/Telemetry/Beta/Form/autorun.inf", "../download/Telemetry/Beta/Form/LLatamTelemetryForm.application", "../download/Telemetry/Beta/Form/setup.exe");
  }
  $obj = new Archive_Zip($zipname);
  $options = array ('remove_all_path' => 'true');

  if ($obj->create($files,$options)) {
    header('Content-Type: application/zip');
    header('Content-Length: ' . filesize($zipname));
    header('Content-Disposition: attachment; filename='.$zipname);
    readfile($zipname);
    // Lo elimino del servidor
    unlink($zipname); 
  } else {
    noDownload("Hubo un error al intentar descargar el archivo. Intente nuevamente.");
  }
}
?>