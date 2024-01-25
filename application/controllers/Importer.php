<?php
defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set("America/Argentina/Buenos_Aires");

class Importer extends CI_Controller {

	function __construct()
    {
        parent::__construct();
		$this->load->model('Importer_model');
        $this->load->database();
    }

//view
	public function Main() {
        $data['page_title'] = 'Sinai Impacter - v.0.2';
        $data['page_name'] = 'main';
		
		$Path = './assets/info';
		$FileName =  'listado.txt' ;
		$nroSerieFiltro = GetTxtInfoSerie($FileName, $Path);

		
		
		$data['nroSerieFiltro'] = explode(',', $nroSerieFiltro);
		
		array_pop($data['nroSerieFiltro']);

        $this->load->view('Template/Main', $data);
    }

    public function Result() {

		$this->load->library('form_validation');
        
        $this->form_validation->set_rules('date_one', 'Fecha Uno', 'required');
        $this->form_validation->set_rules('date_two', 'Fecha Dos', 'required');
        $this->form_validation->set_rules('nroSerieFiltro', 'Número de Serie', 'required');

        if ($this->form_validation->run() == FALSE) {
			redirect('sinai');
        } else {
            $DateOne = $this->input->post('date_one');
            $DateTwo = $this->input->post('date_two');
            $SerieSeleccionada  = $this->input->post('nroSerieFiltro');
		}


		
        $data['page_title'] = 'Sinai Impacter - v.0.2';
        $data['page_name'] = 'result';

		$fechaInicioFormateada = date("d", strtotime($DateOne));
		$fechaFinFormateada = date("d", strtotime($DateTwo));
		$fechaFinFormateada2 = date("m", strtotime($DateTwo));
		$seg = gmdate('dmYHis', time());

		$IdentificNumber = $fechaInicioFormateada . "_" . $fechaFinFormateada."_".$fechaFinFormateada2."_".$SerieSeleccionada."_".$seg;
		
		$PreProcessDataPackage = array(
			'DateOne' => $DateOne,
			'DateTwo' => $DateTwo,
			'IdentificNumber' => $IdentificNumber,
			'nroSerieFiltro'=> $SerieSeleccionada
		);

		$DataCollection = $this->CalculateProcessAfterDone($PreProcessDataPackage)['Registers'];

		$fieldNames = array_keys(get_object_vars($DataCollection[0]));
		
		$nroSerieFiltro = $SerieSeleccionada;

		$nuevosObjetosFiltrados = array();

		$Path = './assets/img/' .$IdentificNumber;
		$FileName = $IdentificNumber ;

		$SinaiDataCollection = GetTxtInfo($FileName, $Path);
		

		$data['RegisterQtaNumber'] = count($SinaiDataCollection);
		$data['SinaiDataCollection'] = $SinaiDataCollection;
		$data['PreProcessDataPackage'] = $PreProcessDataPackage;
		$data['SerieSeleccionada'] = $SerieSeleccionada;


        $this->load->view('Template/Main', $data);
    }

    public function Process() {
        $data['page_title'] = 'Sinai Impacter - v.0.2';
        $data['page_name'] = 'process';

		$DateOne = $_POST['DateOne'];
		$DateTwo = $_POST['DateTwo'];
		$IdentificNumber = $_POST['protocol_number'];
		$SerieSeleccionada = $_POST['nroSerieFiltro'];

		$PreProcessDataPackage = array(
			'DateOne' => $DateOne,
			'DateTwo' => $DateTwo,
			'IdentificNumber' => $IdentificNumber
		);

		$data['PreProcessDataPackage'] = $PreProcessDataPackage;

		

		$Path = './assets/img/' .$IdentificNumber;
		$FileName = $IdentificNumber ;

		$info = GetTxtInfo($FileName, $Path);
		$txtFinal =array();
		$count = 0;

		$cantreg = count($info);
		$datosArray =array($SerieSeleccionada,$DateOne,$DateTwo,$cantreg);
		

		$RequestPackageProtocoloExpo = $this->Importer_model->GetProtocoloExpoMain($datosArray);

		$datosArrayProtocExpo = json_decode($RequestPackageProtocoloExpo, true);

		$valores = explode(", ", $RequestPackageProtocoloExpo);

		$Protocotolo = $valores[0];
		$Expo = $valores[1];

		
		
		$data['message'] = $this->prepareSinaiTxtIngresos($info,$Protocotolo,$Expo);

        $this->load->view('Template/Main', $data);
		
    }

	public function UpdateList() {

		$RequestPackageequipos = $this->Importer_model->GetEquiposMain();
		$datosArrayequipos = explode(', ', $RequestPackageequipos);
		
		$Path = './assets/info';
		$FileName =  'listado.txt' ;

		createTxtEquipos($FileName, $Path, $datosArrayequipos);
				
		$data['page_title'] = 'Sinai Impacter - v.0.1';
        $data['page_name'] = 'main';

		$nroSerieFiltro = GetTxtInfoSerie($FileName, $Path);

		$data['nroSerieFiltro'] = explode(',', $nroSerieFiltro);

		array_pop($data['nroSerieFiltro']);

        $this->load->view('Template/Main', $data);
		
    }

//proces
    public function insertDB($data) {

		$this->load->model('Importer_model');
                          
		$this->Importer_model->insert($data);
		
    }


	public function GetParametersForWSCalling() {
		$InformationData = array(
			'organism' => $this->config->item('organism'),
			'token' => $this->config->item('token'),
			'tokenTest' => $this->config->item('tokenTest')
		);

		return $InformationData;
	}

	
	public function CalculateProcessAfterDone($PreProcessDataPackage) {

		$DateOne = $PreProcessDataPackage['DateOne'];
		$DateTwo = $PreProcessDataPackage['DateTwo'];
		$IdentificNumber = $PreProcessDataPackage['IdentificNumber'];

		$ConnectionData = $this->GetParametersForWSCalling();


		$data = array(
			'fechaDesde' => $DateOne, 
			'fechaHasta' => $DateTwo, 
			'organismo' => $ConnectionData['organism']
		);

		$RequestPackage = $this->Importer_model->GetInfractionsByTwoDates($data, $ConnectionData['token']);
		$InfractionsPackage = $RequestPackage->infraccionesSinai;

		$CompleteDirPath = './assets/img/' . $IdentificNumber;
		
		if (!file_exists($CompleteDirPath)) {
			mkdir($CompleteDirPath, 0777, true);
		}
		
		$this->prepareInitialTextFile($IdentificNumber,$CompleteDirPath,$InfractionsPackage);
		
		$Data = array(
			'RegisterQtaNumber' => count($InfractionsPackage),
			'Registers' => $InfractionsPackage
		);
		

		return $Data;
	}

	public function prepareSinaiTxtIngresos($DataCollection,$Protocotolo,$Expo) {
	
		$count=0;

		foreach ($DataCollection as $SinaiElement) {
			

			//cambiar la serie 
			$serie = fieldSerie($SinaiElement[29],$SinaiElement[30]);
			$lugar = fieldLugar($SinaiElement[33]);
			$codigosP = fieldcodigoProyecto($SinaiElement[30]);
		
			$CodigoProyectoDPPSV =$codigosP[1];
			$CodigoProyectoCecaitra =$codigosP[0];
			$CodigoPostal =$codigosP[2];

			$velocidad_registrada = fieldVelocidadRegistrada($SinaiElement[26]);
			$fecha_proceso = date("d")."/".date("m")."/".date("Y");
			$hora_proceso = date("h").date("i").date("s");

			$separarNombreApellido = fieldnombreApellido($SinaiElement[15]);

			$tipo_documento = fieldcuitDni($SinaiElement[17], $SinaiElement[18]);
			$arrayDomicilio = fielddomicilioTitular($SinaiElement[19]);

			$inicial_sentido = fieldcapturarSentido($SinaiElement[10]);
			$inicial_ejido = fieldcapturarEjido($SinaiElement[11]);
			$numero_carril = fieldcapturarCarril($SinaiElement[33]);
	
			$ImageName = createImageName(
				$SinaiElement[30], 
				$SinaiElement[3], 
				$SinaiElement[27]
			);

			$ConnectionData = $this->GetParametersForWSCalling();

			$data  = array(
				'idActa'    => $SinaiElement[0],
				'nroActa'   => $SinaiElement[3],
				'organismo' => $ConnectionData['organism']
			);

			$RequestPackage = $this->Importer_model->GetPictureForDominioRequest($data, $ConnectionData['tokenTest']);				
			$Ley = fieldLey($RequestPackage->detalleInfraccionActa[0]->ley);
				
			$FormatedElementPackage[$count] = array(
				'tipo' => 'P', 
				'fabricante' => '25', 
				'serie' => $SinaiElement[30], 
				'operativo' => '', 
				'lugar' => $lugar, 
				'fecha_toma' => date('d/m/Y', strtotime($SinaiElement[27])), 
				'hora_toma' => date('his', strtotime($SinaiElement[27])), 
				'velper' => $SinaiElement[25],
				'velreg' => $velocidad_registrada,
				'dominio' => $SinaiElement[12], 
				'tipo_vehiculo' => '1', 
				'ruta_imagenes' => "M" . $CodigoProyectoDPPSV  . "-" . $CodigoProyectoCecaitra . "-" . "1" . "-" . $Protocotolo . "-" . $serie . "-" . date("d").date("m").date("y"),//'M089-084-1-272769-ENFORCER_066-120623'
				'imagen1' => $ImageName . ".jpg", 
				'fecha_de_proceso' => $fecha_proceso,
				'hora_de_proceso' => $hora_proceso,
				'matricula_legajo' => $SinaiElement[1],
				'apellido_nombre' => $SinaiElement[2],
				'jerarquia_cargo' => '',
				'numero_protocolo' => "M". $CodigoProyectoDPPSV . "-" . $CodigoProyectoCecaitra . "-" . "1" . "-" . $Protocotolo . "-" . $serie . "-" . date("d").date("m").date("y"),
				'jurisdiccion_de_constatacion' => $CodigoProyectoDPPSV,
				'jurisdiccion_aplicacion' => $CodigoProyectoDPPSV, 
				'autoridad_constatacion' => '09',
				'ejido_urbano' => $inicial_ejido,
				'calle_ruta' => '',
				'numero_kilometro' => '',
				'sentido' => $inicial_sentido,
				'mano' => '',
				'cp' => $CodigoPostal, 
				'localidad' => $CodigoProyectoDPPSV, 
				'minuto_diferencia_ap' => '',
				'fecha_de_configuracion' => '',
				'hora_de_configuracion' => '',
				'fecha_de_bajada' => $fecha_proceso, 
				'imput_ley' => '22222ley', 
				'imput_art' => '22222art',
				'imput_inc' => '2222 inc', 
				'imput_ley' => $Ley, 
				'imput_art' => $RequestPackage->detalleInfraccionActa[0]->articulo,
				'imput_inc' => $RequestPackage->detalleInfraccionActa[0]->inciso, 
				'desc_infraccion' => '',
				'nombre_imagen_2' => '',
				'nombre_imagen_3' => '',
				'nombre_imagen_4' => $ImageName . 'Z.jpg',
				'archivo_video' => '', 
				'coordenadas' => '',
				'archivo_coordenadas' => '',
				'carril' => $numero_carril,
				'tiempo_desde_que_encendio' => '',
				'tiempo_luz_roja' => '',
				'tiempo_luz_amarilla' => '',
				'tiempo_luz_verde' => '',
				'clip_video' => '', 
				'notificada' => 'N', 				
				'observaciones' => '',
				'id_tipo_de_medio_captura' => '2',
				'regla' => '0', 
				'codigo_negocio' => 'CF'. $SinaiElement[0],
				'marca' => $SinaiElement[13],
				'modelo' => $SinaiElement[14],
				'tipo_documento' => $tipo_documento[1],
				'numero_documento' => $tipo_documento[0],
				'sexo' => '',
				'nombre' => $separarNombreApellido[1],
				'apellido' =>$separarNombreApellido[0], 
				'razon_social' => $SinaiElement[17], 
				'calle' => $arrayDomicilio[1],
				'numero' => $arrayDomicilio[0],
				'entrecalle' => '',
				'y_calle' => '',
				'descripcion_adicional' => '',
				'piso' => '',
				'depto' => '',
				'provincia' => $SinaiElement[21], 
				'partido' => $SinaiElement[22],
				'tit_dom_localidad' => $SinaiElement[23], 
				'tit_dom_codigo_post' => $SinaiElement[24], 
				'codigo_prov_aa' => '2', 
			
				
				 'fotoVehiculo' =>$RequestPackage->fotoVehiculo,
				 'fotoDominialVehiculo' =>$RequestPackage->fotoDominialVehiculo
			);
			

			$dataPresuncion = array(
                'tipo' => $FormatedElementPackage[$count]['tipo'],
                'infraccion' => '',
                'fabricante' => $FormatedElementPackage[$count]['fabricante'],
                'serie' => $FormatedElementPackage[$count]['serie'],
                'operativo' => $FormatedElementPackage[$count]['operativo'],
                'lugar' => $FormatedElementPackage[$count]['lugar'],
                'fecha_toma' => $FormatedElementPackage[$count]['fecha_toma'],
                'hora_toma' => $FormatedElementPackage[$count]['hora_toma'],
                'velper' => $FormatedElementPackage[$count]['velper'],
                'velreg' => $FormatedElementPackage[$count]['velreg'],
                'dominio' => $FormatedElementPackage[$count]['dominio'],
                'dominio_anterior' => '',
                'tipo_vehiculo' => $FormatedElementPackage[$count]['tipo_vehiculo'],
                'ruta_imagenes' => $FormatedElementPackage[$count]['ruta_imagenes'],
                'cp' => $FormatedElementPackage[$count]['cp'],
                'imagen1' => $FormatedElementPackage[$count]['imagen1'],
                'imagen2' => $FormatedElementPackage[$count]['nombre_imagen_2'],
                'imagen3' => $FormatedElementPackage[$count]['nombre_imagen_3'],
                'imagen4' => $FormatedElementPackage[$count]['nombre_imagen_4'],
                'imagenANSV' => $FormatedElementPackage[$count]['imagen1'],
                'imgsel1' => '',
                'imgsel2' => '',
                'video' => $FormatedElementPackage[$count]['archivo_video'],
                'autoridad' => '',
                'agente' => '',
                'calle' => '',
                'calle_numero' => '',
                'coordenadas' => $FormatedElementPackage[$count]['coordenadas'],
                'coordenadas_archivo' => $FormatedElementPackage[$count]['archivo_coordenadas'],
                'sentido' => $FormatedElementPackage[$count]['sentido'],
                'carril' => $FormatedElementPackage[$count]['carril'],
                'tiempo_encendio' => $FormatedElementPackage[$count]['tiempo_desde_que_encendio'],
                'tiempo_luz_roja' => $FormatedElementPackage[$count]['tiempo_luz_roja'],
                'tiempo_luz_amarilla' => $FormatedElementPackage[$count]['tiempo_luz_amarilla'],
                'tiempo_luz_verde' => $FormatedElementPackage[$count]['tiempo_luz_verde'],
                'clip_video' => $FormatedElementPackage[$count]['clip_video'],
                'notificado' => $FormatedElementPackage[$count]['notificada'],
                'protocolo' => $Protocotolo,
                'ts' => $FormatedElementPackage[$count]['fecha_de_proceso'],
                'idprotocolo' => $Protocotolo,
                'fecha_proceso' => date('Y-m-d', strtotime($FormatedElementPackage[$count]['fecha_de_proceso'])),
                'hora_proceso' => date('His', strtotime( $FormatedElementPackage[$count]['hora_de_proceso'])),
                'matricula' => $FormatedElementPackage[$count]['matricula_legajo'],
                'nombre' => $FormatedElementPackage[$count]['apellido_nombre'],
                'jerarquia' => $FormatedElementPackage[$count]['jerarquia_cargo'],
                'jur_const' => $FormatedElementPackage[$count]['jurisdiccion_de_constatacion'],
                'jur_aplic' => $FormatedElementPackage[$count]['jurisdiccion_aplicacion'],
                'aut_const' => $FormatedElementPackage[$count]['autoridad_constatacion'],
                'ejido' => $FormatedElementPackage[$count]['ejido_urbano'],
                'mano' => $FormatedElementPackage[$count]['mano'],
                'localidad' => $FormatedElementPackage[$count]['localidad'],
                'fbajada' => $FormatedElementPackage[$count]['fecha_de_bajada'],
                'imp_ley' => $FormatedElementPackage[$count]['imput_ley'],
                'imp_art' => $FormatedElementPackage[$count]['imput_art'],
                'imp_inc' => $FormatedElementPackage[$count]['imput_inc'],
                'imagen_zoom' => '',
                'observ' => $FormatedElementPackage[$count]['observaciones'],
                'fecha_alta' => date('Y-m-d', strtotime($FormatedElementPackage[$count]['fecha_de_proceso'])),
                'idimportacion' => '',
                'tipo_medio_captura' => $FormatedElementPackage[$count]['id_tipo_de_medio_captura'],
                'etapa' => '50',
                'proyecto' => 'hard proyecto', 
                'cuit' => '',
                'idlote' => '',
                'numero_acta' => '',
                'gastos' => '',
                'uf_valor' => '',
                'uf_voluntario' => '',
                'uf_cantidad' => '',
                'fecha_generacion' => '',
                'fecha_vencimiento' => '',
                'ciclo_calidad' => '1',
                'codigo_barras' => '',
                'fecha_impresion' => '',
                'idimpresion' => '',
                'idfiscal' => '',
                'titular' => '0',
                'tit_nombre' => $FormatedElementPackage[$count]['nombre'],
                'tit_tipodoc' => $FormatedElementPackage[$count]['tipo_documento'],
                'tit_dni' => $FormatedElementPackage[$count]['numero_documento'],
                'tit_cuit' => '0',
                'tit_dom_calle' => $FormatedElementPackage[$count]['calle'],
                'tit_dom_numero' => $FormatedElementPackage[$count]['numero'],
                'tit_dom_piso' => $FormatedElementPackage[$count]['piso'],
                'tit_dom_depto' => $FormatedElementPackage[$count]['depto'],
                'tit_dom_localidad' => $FormatedElementPackage[$count]['tit_dom_localidad'],
                'tit_dom_codigopos' => $FormatedElementPackage[$count]['tit_dom_codigo_post'],
                'tit_provincia' => $FormatedElementPackage[$count]['provincia'],
                'veh_marca' => $FormatedElementPackage[$count]['marca'],
                'veh_anio' => '',
                'veh_segmento' => '',
                'razon_descarte' => '',
                'idregla' => '0', 
                'fecha_regla' => '0000-00-00 00:00:00',
                'juzgado' => '0',
                'retuvo_lic' => '',
                'retuvo_veh' => '',
                'numero_pieza' => '',
                'editado' => '',
                'fuga_ident' => '',
                'nego_ident' => '',
                'no_ident' => '',
                'agente_dni' => '',
                'MedidasCautelares' => '',
                'nro_resolucion' => '',
                'regla' => $FormatedElementPackage[$count]['regla'],
                'codigo_negocio' => $FormatedElementPackage[$count]['codigo_negocio'],
                'veh_modelo' => $FormatedElementPackage[$count]['modelo'],
                'tit_sexo' => $FormatedElementPackage[$count]['sexo'],
                'tit_apellido' => $FormatedElementPackage[$count]['apellido'],
                'tit_partido' => $FormatedElementPackage[$count]['partido'],
                'error' => '',
                'expo_nro' => $Expo,
                'fecha_expopcia' => '',
                'fecha_pdf' => '',
                'fecha_filtro_cd' => '',
                'fecha_correo' => '',
                'fecha_rendicion_correo' => '',
                'legajo_correo' => '0',
                'correo_estado' => '0',
                'fecha_recibido_correo' => '0000-00-00'
            );

			$PackageGen = array(
				'serie' =>$SinaiElement[30],
				'Protocotolo' =>$Protocotolo,
				'Expo' =>$Expo,
				'CodigoProyectoDPPSV' =>$CodigoProyectoDPPSV,
				'CodigoProyectoCecaitra' =>$CodigoProyectoCecaitra,
				'fecha_proceso' =>$fecha_proceso);
			

			if($this->insertDB($dataPresuncion)){
				$this->Importer_model->PostNotCompProceso($data);
			}

			$count++;
		}

		if($this->RenderFilesAndDocs($FormatedElementPackage,$PackageGen)){
			return "Expo generada : ".$Expo;
		}else{
			return "Error generacion expo ";
		}
	}


	public function RenderFilesAndDocs($SinaiInfractionsPackage, $PackageGen) {

		foreach ($SinaiInfractionsPackage as $SinaiElement) {


			$ImageName = $SinaiElement["imagen1"];
			$FotoVehiculo = $SinaiElement["fotoVehiculo"];
			$FotoDominialVehiculo = $SinaiElement["fotoDominialVehiculo"];

			$Packagetxt['ImageName'] = $ImageName;
			$Packagetxt['fotoVehiculo'] = $FotoVehiculo;
			$Packagetxt['fotoDominialVehiculo'] = $FotoDominialVehiculo;
			
			$Packagetxt['serie'] = $SinaiElement["serie"];

			$Packagetxt['fecha_proceso'] = $PackageGen["fecha_proceso"];
			$Packagetxt['Expo'] = $PackageGen["Expo"];
			$Packagetxt['Protocotolo'] = $PackageGen["Protocotolo"];
			$Packagetxt['CodigoProyectoDPPSV']=$PackageGen["CodigoProyectoDPPSV"];
			$Packagetxt['CodigoProyectoCecaitra']= $PackageGen["CodigoProyectoCecaitra"];
			
			// Create JPG Files
			$this->Importer_model->CreateDirAndFillIt($Packagetxt);

		}

		// Create TXT File
		if($this->prepareTextEntrada($SinaiInfractionsPackage,$PackageGen)){
			return TRUE;
		}else{
			return FALSE;
		}

	}


	public function prepareInitialTextFile($FileName, $Path, $DataCollection) {

		$SerieSeleccionada = $_POST['nroSerieFiltro'];		

		$iLimit = count($DataCollection);

		if ($iLimit == 0){
			echo"NO HAY MATERIAL EN ESAS FECHAS";
			die;
		}else{
		
			for ($i=0; $i < $iLimit; $i++) { 

				$serieDispositivo = $DataCollection[$i]->nro_Serie_Dispositivo;

				if ($serieDispositivo == $SerieSeleccionada) {

					$dataArray = get_object_vars($DataCollection[$i]);
			
					foreach ($dataArray as &$value) {
						if (is_array($value)) {							
							$value = implode(',', $value);
						}
					}

					array_pop($dataArray);
					$List = implode(';', $dataArray);

					createTxtOrAppend($FileName, $Path, $List);

				}
			}
		}

		$rutaArchivo = $Path. '/' . $FileName. '.txt';

		if (file_exists($rutaArchivo) && is_file($rutaArchivo) && pathinfo($rutaArchivo)['extension'] == 'txt') {
		
			$SinaiDataCollection = GetTxtInfo($FileName, $Path);

		} else {
			echo"NO HAY MATERIAL EN ESAS FECHAS DE ESE EQUIPO";
			die;
		}
	
	}
  
	
	public function prepareTextEntrada($DataCollection,$PackageGen) {
		$processedDataArray = removeUnwantedFields($DataCollection);
		
		
		if($this->Importer_model->createTxtEntrada($processedDataArray,$PackageGen)){
			return TRUE;
		}else{
			return FALSE;
		}

	}
	
	
	public function GetInfractionsByDate() {

		$ConnectionData = $this->GetParametersForWSCalling();
		$data = array(
			'fechaDesde' => $DateOne, 
			'fechaHasta' => $DateTwo, 
			'organismo' => $ConnectionData['organism']
		);
		$RequestPackage = $this->Importer_model->GetInfractionsByTwoDates($data, $ConnectionData['token']);
		$InfractionsPackage = $RequestPackage->infraccionesSinai;
		$CompleteDirPath = './assets/img/' . $IdentificNumber;
		$this->prepareTextFile($IdentificNumber, $CompleteDirPath, $DataCollection);

	}

	


}
