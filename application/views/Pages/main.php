<div class="row">				
	<div class="col-md-12">
	</div>	

	<?php echo form_open('Importer/Result'); ?>
    <input type="date" name="date_one" value="2023-09-02">
    <input type="date" name="date_two" value="2023-09-03">
    <label for="opciones">Selecciona un Equipo : </label>
    <select name="nroSerieFiltro" id="nroSerieFiltro" >
        <?php foreach ($nroSerieFiltro as $valor => $etiqueta): ?>
            <option value="<?php echo $etiqueta; ?>"><?php echo $etiqueta; ?></option>
        <?php endforeach; ?>
    </select> 
	 <?php echo form_error('date_one'); ?>
     <?php echo form_error('date_two'); ?>
   	 <?php echo form_error('nroSerieFiltro'); ?>
    <br>
    <input type="submit" value="Solicitar Muestreo">
<?php echo form_close(); ?>

<form action="<?php echo base_url('Importer/UpdateList'); ?>" method="post">
    <button type="submit" style= "margin-left:400px">Actualizar Equipos</button>
</form>
</div>
