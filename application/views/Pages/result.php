<?php 
	$IdentificNumber = $PreProcessDataPackage['IdentificNumber'];
	$DateOne = $PreProcessDataPackage['DateOne'];
	$DateTwo = $PreProcessDataPackage['DateTwo'];
	$nroSerieFiltro = $PreProcessDataPackage['nroSerieFiltro'];
?>

<div class="row">				
	<div class="col-md-12">
		<div class="row">
			<div class="col-md-12">
				<h4>
					Resultados de Analisis de Material <?php echo $IdentificNumber; ?>
					<br>
					<small>
						Desde el <?php echo $DateOne; ?> al <?php echo $DateTwo; ?>
					</small>	
					del equipo  <?php echo $nroSerieFiltro; ?>
				</h4>
				<p>
					El numero de registros disponibles para estas fechas es de: <?php echo $RegisterQtaNumber; ?>. <br>
					¿Desea procesar el pedido?
				</p>
				<?php echo form_open('Importer/Process', 'onsubmit="showLoadingSpinner();"'); ?>
					<input type="hidden" name="DateOne" value="<?php echo $PreProcessDataPackage['DateOne'];?>" readonly>
					<input type="hidden" name="DateTwo" value="<?php echo $PreProcessDataPackage['DateTwo'];?>" readonly>
					<input type="hidden" name="protocol_number" value="<?php echo $PreProcessDataPackage['IdentificNumber'];?>" readonly>
					<input type="hidden" name="nroSerieFiltro" value="<?php echo $PreProcessDataPackage['nroSerieFiltro'];?>" readonly>
					<input type="submit" class="" value="Procesar y Renderizar">
				<?php echo form_close(); ?>
			</div>
			<div class="col-md-12">
				<hr>
			</div>
			<div class="col-md-12">			
			<div id="overlay" class="overlay">
				<div id="loader"></div>
				<p id="textocarg"> Cargando...</p>					
			</div>
				<table class="table table-striped">
					<thead class="thead-dark">
						<tr>
							<th scope="col">Lugar</th>
							<th scope="col">Serie</th>
							<th scope="col">Dominio</th>
							<th scope="col">Fecha y Hora</th>
						</tr>
					</thead>
					<tbody>
						<?php if($RegisterQtaNumber > 0): ?>
							<?php foreach($SinaiDataCollection as $SinaiElement): ?>
								<tr>
									<?php 
									if(($SinaiElement[0])): ?>
										<td>
											<?php 								
											$patron = '/^(.*?)PK0\.0/';
											preg_match($patron, $SinaiElement[33], $matches);
											$resultado = isset($matches[1]) ? $matches[1] : '0';						
											echo $resultado;
											?>
										</td>
										<td><?php echo $SinaiElement[30]; ?></td>
										<td><?php echo $SinaiElement[12]; ?></td>
										<td><?php echo $SinaiElement[27]; ?></td>							
									<?php endif; ?>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>	
</div>

<style>
        #overlay {            
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        #loader {
            display: none;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 2s linear infinite;
        }
        #textocarg {
            display: none;                                 
			margin : 20px;
        }
       

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>

<script>
    // Función para mostrar un mensaje de progreso
	function showLoadingSpinner() {
        // Muestra un spinner o animación de carga
		document.getElementById("overlay").style.display = "flex";
		document.getElementById("loader").style.display = "block";
		document.getElementById("textocarg").style.display = "block";

		var botones = document.getElementsByTagName('button');
		for (var i = 0; i < botones.length; i++) {
			botones[i].disabled = true;
		}
    }
</script>