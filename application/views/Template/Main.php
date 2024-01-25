<!DOCTYPE html>
<html lang="es">
	<head>
		<title><?php echo $page_title; ?></title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="<?php echo base_url() . 'assets/vendor/bootstrap/css/bootstrap.min.css';?>">
		<link rel="stylesheet" href="<?php echo base_url() . 'assets/css/app.css';?>">
		<script src="<?php echo base_url() . 'assets/vendor/jquery/jquery.min.js';?>"></script>
		<script src="<?php echo base_url() . 'assets/vendor/bootstrap/js/bootstrap.min.js';?>"></script>
	</head>
	<body>
		<div class="container">
			<div class="row">
				<div class="col-md-3 sidenav">
					<h4><?php echo $page_title; ?></h4>
					<ul class="nav nav-pills nav-stacked">
						<li class="active">
							<a href="main">Home</a>
						</li>

					</ul>
				</div>
				<div class="col-md-9">
                    <?php $this->view('Pages/' . $page_name); ?>
				</div>
			</div>
		</div>
		<footer class="container  text-center text-lg-start fixed-bottom"  style='background-color: rgba(0, 0, 0, 0.2);'>
			<div class="row">
				<div class="col-md-12">

				</div>
			</div>
			<p>Sinai Impacter fue desarrollado por CECAITRA. Todos los derechos reservados. 2023</p>
		</footer>
	</body>
</html>