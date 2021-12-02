<?php
/**
* Imprime informacion del viaje 
* @param integer $id id del viaje
* 
*/
function printTripInfo($trip, $show = 0) {
		$driverName = getDriverField($trip['uid'],'displayname');
		$trip['mass'] /= 1000;
		$trip['distance'] /= 1000;
	?>
		<div class="row">
			<div class="col-sm-3 col-lg-3 col-sm-offset-1 col-lg-offset-1">
		  		<div class="dash-unit">
		      		<dtitle>Conductor</dtitle>
		      		<hr>
		      		<div class="thumbnail">
						<img src="<?php echo SITE_URL; ?>/avatar/<?php echo getDriverCountry($trip['uid']); ?>/<?php echo getDriverNumber($trip['uid']); ?>" class="img-rounded img-responsive" width="100" height="100">
					</div>
					<h1><?php echo $driverName; ?></h1><br>
					<p>Inicializado a las <?php echo date("H:i:s", strtotime($trip['start'])); ?> del <?php echo date("d/m/Y", strtotime($trip['start'])); ?></p>
					<?php if($trip['finish'] == "0000-00-00 00:00:00") { ?>
					<p>En curso</p>
					<?php }else{ ?>
					<p>Finalizado a las <?php echo date("H:i:s", strtotime($trip['finish'])); ?> del <?php echo date("d/m/Y", strtotime($trip['finish'])); ?></p>
					<?php } ?>
					<p>Duraci&oacute;n <?php echo gmdate("H\h i\m\i\\n s\s",strToTime($trip['finish']) - strToTime($trip['start']));?></p>
		  		</div>
	  		</div>
			<div class="col-sm-3 col-lg-3">
		  		<div class="dash-unit">
		      		<dtitle>Servidor</dtitle>
		      		<hr>
		      		<div class="thumbnail">
		      		<?php
			      		if($trip['game']=="ats") 
		      				echo "<img src='".SITE_URL."/img/atslogo.png' class='img-responsive'>";
	      				else
	      					echo "<img src='".SITE_URL."/img/ets2logo.png' class='img-responsive'>";
		      		?>
		      		</div>
					<h1>Servidor inicio: <?php echo $trip['serverstart'];?></h1>
					<h1>Servidor fin: <?php echo $trip['serverend'];?></h1>
		  		</div>
	  		</div>	  		
	  		<div class="col-sm-3 col-lg-3">
		  		<div class="dash-unit">
		      		<dtitle>Camión</dtitle>
		      		<hr>
		      		<div class="thumbnail">
						<img src="<?php echo SITE_URL; ?>/img/trucks/<?php echo strtolower($trip['brand']); ?>.png" class="img-responsive">
					</div>
					<h1><?php echo $trip['truck_name'];?></h1>
					<div class="container-fluid text-center">
						<canvas id="chart-truck" height="200"/></canvas>
					</div>
		  		</div>
	  		</div>
  		</div>
  		<div class="row">
	  		<div class="col-sm-5 col-lg-5 col-sm-offset-3 col-lg-offset-3">
		  		<div class="dash-unit">
		      		<dtitle>Carga</dtitle>
		      		<hr>
		      		<div class="row">
		      			<div class="col-xs-6 col-sm-6 col-lg-6">
		      				<h1>Origen</h1>
		      				<div class="thumbnail">
		      					<img src="<?php echo SITE_URL; ?>/img/companies/<?php echo strtolower($trip['org_cmpy']); ?>.png" class="img-responsive">
		      				</div>
		      				<h1><?php echo $trip['org_city']; ?><h1>
		      			</div>
		      			<div class="col-xs-6 col-sm-6 col-lg-6">
		      				<h1>Destino</h1>
		      				<div class="thumbnail">
		      					<img src="<?php echo SITE_URL; ?>/img/companies/<?php echo strtolower($trip['des_cmpy']); ?>.png" class="img-responsive block-center">
	      					</div>
		      				<h1><?php echo $trip['des_city']; ?></h1>
		      			</div>
		      		</div>
		      		<div class="row">
		      			<div class="col-xs-6 col-sm-6 col-lg-6">
							<p>Carga: <?php echo $trip['trailer']." [".$trip['mass']." Ton]";?></p>
							<p>Ganancias:
								<?php 
									if($trip['late'] == 0)
										echo "<span style='color: #b2c831;'>$ ".number_format($trip['income'],2,",",".")."</span>";
									else
										echo "<span style='color: #fa1d2d;'>$ 0 (tarde)</span>";
								?>
							</p>
							<p>Distancia en itinerario: <?php echo round($trip['distance'],2) ;?> Km</p>
							<p>Distancia recorrida: <?php echo round($trip['driven'],2); ?> Km</p>
						</div>
						<div class="col-xs-6 col-sm-6 col-lg-6">
							<div class="container-fluid">
								<canvas id="chart-cargo" height="180"/></canvas>
							</div>
							<script>
								var options = {
									animationEasing: "easeOutCubic",
									responsive: true
							    };  							
								var truckData = [
										{
											value: "<?php echo round($trip['truck_dmg']*100,2); ?>",
											color:"#F7464A",
											highlight: "#FF5A5E",
											label: "Daño (%)"
										},
										{
											value: 100-"<?php echo round($trip['truck_dmg']*100,2); ?>",
											color: "#45DA28",
											highlight: "#60F458",
											label: "Sin daño (%)"
										}
									];

								var cargoData = [
										{
											value: "<?php echo round($trip['trailer_dmg']*100,2); ?>",
											color:"#F7464A",
											highlight: "#FF5A5E",
											label: "Daño (%)"
										},
										{
											value: 100-"<?php echo round($trip['trailer_dmg']*100,2); ?>",
											color: "#45DA28",
											highlight: "#60F458",
											label: "Sin daño (%)"
										}
									];

								$( document ).ready(function() {
									var ctx = document.getElementById("chart-truck").getContext("2d");
									window.myTruck = new Chart(ctx).Doughnut(truckData,options);
									var cts = document.getElementById("chart-cargo").getContext("2d");
									window.myCargo = new Chart(cts).Doughnut(cargoData,options);
								});
							</script>
						</div>
					</div>
		  		</div>
	  		</div>
  		</div>
	<?php
	if (canEdit($trip['uid'])){
		if ($trip['delivered']){
		?>
			<div class="row">
				<a class="btn btn-md btn-danger pull-right<?php echo ($trip['deleted']?(isAdmin()?"":" disabled"):""); ?>" role="button" id="<?php echo (isAdmin()?"deleteadmin":"delete"); ?>" data-tripid="<?php echo $trip['id']; ?>" <?php echo (isAdmin()?"data-eliminated='".$trip['deleted']."'":""); ?>><?php echo ($trip['deleted']?(isAdmin()?"Recuperar viaje borrado":"Viaje Borrado"):"Borrar viaje"); ?></a>
				<?php if(isAdmin()){ ?>
				<a class="btn btn-md btn-info pull-right<?php echo ($trip['deleted']?" disabled":""); ?>" id="inranking" role="button" data-showed="<?php echo $trip['isshowed']; ?>"><?php echo ($trip['isshowed']?"Eliminar del ranking":"Mostrar en el ranking"); ?></a>
				<?php } ?>
		    </div>
			<div id="confirmation" tabindex="-1" role="dialog" class="modal animated bounceInDown" style="background-color: rgba(0, 0, 0, 0)" aria-labelledby="myModalLabel">
		        <div class="modal-dialog" role="document">
		            <div class="modal-content">
						<div class="modal-body">¿Est&aacute;s seguro que deseas eliminar el viaje?</div>
					  	<div class="modal-footer">
					    	<button type="button" data-dismiss="modal" class="btn btn-primary" id="deleteconfirm">Confirmar</button>
					    	<button type="button" data-dismiss="modal" class="btn">Cancelar</button>
					  	</div>
				  	</div>
			  	</div>
			</div>
		<?php
		}
	}
}
?>