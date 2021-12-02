<?php
session_start();
require_once('inc/model.php');
require_once('inc/layout.php');
require_once('inc/functions.php');
require_once('inc/config.php');

if (isset($_SERVER['HTTP_REFERER'])) {
    $fallbackURL = $_SERVER['HTTP_REFERER'];    
} else {
    $fallbackURL = "";
}

if (SITE_DEBUG_ON) {
    ini_set('display_errors',1);
    ini_set('display_startup_errors',1);
    error_reporting(-1);
    if($_SESSION['rank']<USER_RANK_DEVELOPER)
        exit;
}   

$section = filter_input(INPUT_GET, 's', FILTER_SANITIZE_STRING);
$action = filter_input(INPUT_GET, 'a', FILTER_SANITIZE_STRING);
$option = filter_input(INPUT_GET,'o', FILTER_SANITIZE_STRING);

switch ($section) {
    case 'get':
        $year = filter_input(INPUT_GET, "y", FILTER_SANITIZE_NUMBER_INT);
        $month = filter_input(INPUT_GET, "m", FILTER_SANITIZE_NUMBER_INT);
        $game = filter_input(INPUT_GET, "g", FILTER_SANITIZE_NUMBER_INT);
        if (!$year) { $year = date("y"); }
        if (!$month) { $month = date("m"); }
        if(!$game) {$game = -1;}
        getRankingJSON($action,$year,$month,$game);
        break;
    case 'view':
        iniLayout();
        showRanking();
        $scripts = array();        
        $scripts[] = "chart.min.js";
        $scripts[] = "ranks.js";        
        endLayout($scripts);
        break;
    default:

        break;
}

function iniLayout(){
    layoutHead("Empresa");
    layoutInitBody();
    layoutInitWrapper();
    layoutTopbar("company");                                     
    layoutInitContent();
}

function endLayout($scripts = array()){
    layoutEndContent();
    layoutEndWrapper();      
    layoutAddDefaultScript($scripts);
    layoutEndBody();
}

function getRankingJSON($field, $year, $month, $game){
    $field = strtolower($field);    
    $auxArray = array();
    if ($field == "trips"){
        $count = true;
        $field = "*";
    } else {
        $count = false;
    }
    $rankData = getTopDrivers($field,10,$count,$year,$month,$game);
    while ($row = mysqli_fetch_assoc($rankData)){
        $auxArray[]=$row;
    }
    echo json_encode($auxArray);
}

function showRanking(){
    $monthsArray[-1]="Todos";
    $monthsArray[1]="Enero";
    $monthsArray[2]="Febrero";
    $monthsArray[3]="Marzo";
    $monthsArray[4]="Abril";
    $monthsArray[5]="Mayo";
    $monthsArray[6]="Junio";
    $monthsArray[7]="Julio";
    $monthsArray[8]="Agosto";
    $monthsArray[9]="Septiembre";
    $monthsArray[10]="Octubre";
    $monthsArray[11]="Noviembre";
    $monthsArray[12]="Diciembre";
    $monthNumber = date('m');
    $initialYear = 2015;
    $endYear = 2020;
    $yearNumber  = date('Y');
    $gameArray[-1]="Todos";
    $gameArray[1]="American Truck Simulator";
    $gameArray[2]="Euro Truck Simulator 2";

    ?>
    <div class="control-group col-md-8 col-md-offset-2">
    <?php companyNavigation("rank"); ?>  
        <h3>Ranking mensual de conductores</h3>
        
        <div class="filter-bar well">            
            <div class="btn-group" >                  
                <select id="filterByMonth" onchange="javascript:updateRankTables()">
                <?php 
                foreach ($monthsArray as $i => $month) {
                    ($i == $monthNumber)?$selected="selected='selected'":$selected="";
                    echo "<option value=$i $selected> $month</option>\n";
                }
                ?>
                </select>                   
                <select id="filterByYear" onchange="javascript:updateRankTables()">
                <option value=-1> Todos</option>
                <?php
                    for($i = $initialYear; $i <= $endYear; $i++){
                        ($i == $yearNumber)?$selected="selected='selected'":$selected="";
                        echo "<option value=$i $selected> $i</option>\n";
                    }
                ?>                                    
                </select>
                <select id="filterByGame" onchange="javascript:updateRankTables()">
                <?php 
                foreach ($gameArray as $i => $game) {
                    ($i == -1)?$selected="selected='selected'":$selected="";
                    echo "<option value=$i $selected> $game</option>\n";
                }
                ?>
                </select>    
                <!--
                <a href="javascript:updateRankTables('week')">
                <button id="btn-filter-week" class="btn btn-md btn-info">
                    Semana
                </button>                
                <a href="javascript:updateRankTables('month')">
                <button id="btn-filter-month" class="btn btn-md btn-info btn-filter active">
                    Mes
                </button>
                </a>                
                <a href="javascript:updateRankTables('year')">
                <button id="btn-filter-year" class="btn btn-md btn-info btn-filter">
                    A&ntilde;o
                </button>                    
                <a href="javascript:updateRankTables('all')">
                <button id="btn-filter-all" class="btn btn-md btn-info btn-filter">
                    Todos
                </button>
                </a>          
                -->
            </div>          
        </div>
        <center>
            <button id="loading-btn" class="btn btn-sm btn-success"><span class="glyphicon glyphicon-refresh glyphicon-refresh-animate" title="Actualizando..."></span></button>
        </center>
        <legend>Kil&oacute;metros recorridos</legend>
        <div class="container-fluid">
            <table class="table table-striped table-condensed" id="data-table-driven" >
                <thead>
                    <tr> 
                        <th>Puesto</th>           
                        <th>Nombre</th>
                        <th>Km</th>
                    </tr>
                </thead>        
            </table>
            <div>
                <canvas id="data-chart-driven" class="rank-chart"></canvas>
            </div>
        </div>
        <legend>Ingresos netos</legend>
        <div class="container-fluid">
            <table class="table table-striped table-condensed" id="data-table-income" >
                <thead>
                    <tr>            
                        <th>Puesto</th>
                        <th>Nombre</th>
                        <th>Ingresos</th>
                    </tr>
                </thead>        
            </table>
            <div>
                <canvas id="data-chart-income" class="rank-chart"></canvas>
            </div>
        </div>
        <legend>Viajes entregados</legend>
        <div class="container-fluid">
            <table class="table table-striped table-condensed" id="data-table-trips" >
                <thead>
                    <tr>            
                        <th>Puesto</th>
                        <th>Nombre</th>
                        <th>Viajes</th>
                    </tr>
                </thead>        
            </table>
            <div>
                <canvas id="data-chart-trips" class="rank-chart"></canvas>
            </div>
        </div>
        <legend>Toneladas transportadas</legend>
        <div class="container-fluid">
            <table class="table table-striped table-condensed" id="data-table-mass" >
                <thead>
                    <tr>            
                        <th>Puesto</th>
                        <th>Nombre</th>
                        <th>Toneladas</th>
                    </tr>
                </thead>        
            </table>
            <div>
                <canvas id="data-chart-mass" class="rank-chart"></canvas>
            </div>
        </div>
    </div>
<?php
}
?>
