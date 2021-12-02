<?php
require_once(__DIR__.'/../inc/config.php');
require_once(__DIR__.'/../inc/functions.php');
define('FPDF_FONTPATH',__DIR__.'/../fonts');
require_once(__DIR__.'/../inc/fpdf.php');
class PDF extends FPDF
{
	function Header()
	{
	    $this->Image(__DIR__.'/../img/pagelogo.png',55,0,90);
	    $this->Ln(22);
	    $this->SetFont('Arial','B',12);
    	$this->Cell(17);
		$this->Cell(20,6,utf8_decode("#"),B,0,C);
		$this->Cell(45,6,"Usuario",B,0,C);
		$this->Cell(90,6,"Nombre",B,0,C);
		$this->Ln();
	}
}

$squery = "SELECT driversnumbers.id as numero,
					drivers.id as uid,
					drivers.realusername,
					drivers.fullname,
					drivers.steam_id 
					FROM driversnumbers 
					INNER JOIN drivers ON drivers.id = driversnumbers.uid 
					WHERE rank>1;";

$pdf = new PDF();
$pdf->AddPage();
if ($result = dbSelect($squery)) {
	$pdf->SetFont('Arial','',12);
	while($row = mysqli_fetch_assoc($result)){
		$pdf->Cell(17);
		$pdf->Cell(20,7,$row['numero'],B,0,C);
		$pdf->Cell(45,7,utf8_decode($row['realusername']),B,0,C);
		$pdf->Cell(90,7,utf8_decode($row['fullname']),B,0,C);
		$pdf->Ln();
	}
}
$pdf->Output();
?>