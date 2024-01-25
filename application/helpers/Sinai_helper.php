<?php
      

    function createImageName($DeviceName, $ActNumber, $MesureDateTime) {
        $imageTitle = str_replace(' ', '', $DeviceName) . '_' . 
        $ActNumber . '_' . 
        date('Ymdhis', strtotime($MesureDateTime));
        $imageTitle = strtoupper($imageTitle);
        return $imageTitle;
    }

    function createTxtOrAppend($Filename, $CompleteDirPath, $Information) {
        $myfile = fopen($CompleteDirPath . '/'. $Filename.  ".txt", "a") or die("Unable to open file!");
        $txt = $Information . "\n";
        fwrite($myfile, $txt);  
        fclose($myfile);
    }
        
    function createTxtEquipos($Filename, $CompleteDirPath, $Information) {
        
        $myfile = fopen($CompleteDirPath . '/' . $Filename , "w") or die("Unable to open file!");
        foreach ($Information as $item) {
            if (is_array($item)) {
                $txt = implode(',', $item);
            } else {
                $txt = $item;
            }
    
            fwrite($myfile, $txt . ",");
        }
    
        fclose($myfile);
    }
    
    function fieldSerie($modelo, $nroSerie) {
		
		$modeloSinEspacios =str_replace(' ', '_', $modelo);
		$modeloFinal = $modeloSinEspacios."_".$nroSerie;
		
		return $modeloFinal;
	}

    function fieldcodigoProyecto($nroSerie){
         
        $data  = array();

        switch ($nroSerie) {
            case '00239F'://ceca - prov - codpostal
                $data=array('000','000','0000');
                return $data;
            case '00240F':
                $data=array('000','000','0000');
                return $data;
            case '00242F':
                $data=array('150','173','1629');
                return $data;
            case '00244F':
                $data=array('150','173','1629');
                return $data;
            case '00247F':
                $data=array('000','000','0000');
                return $data;
            case '00248F':
                $data=array('000','000','0000');
                return $data;
            default:
                return 'Municipio no encontrado';die; // Puedes cambiar esto según tus necesidades
        }


    }
    
    function fieldLey($dato){
        
        $posicionLey = strpos($dato,"LEY");
        
        if($posicionLey !== false){
            $texto_extraido = substr($dato, $posicionLey+4,7);
            //echo "TEXTO EXTRAIDO:" . $texto_extraido."\n";
            $reemplazo =str_replace('.', '', $texto_extraido);
        } else {
            echo "revisar campo ley en ws ";
            die;
        }

        Return $reemplazo;

    }
    
    function fieldLugar($dato){

        $posicionPK = strpos($dato,"PK");

        if($posicionPK !== false){
            $texto_extraido = substr($dato, 0, $posicionPK );
            //echo "TEXTO EXTRAIDO:" . $texto_extraido."\n";
            $reemplazo_PorEspacios =str_replace('_', ' ', $texto_extraido);
        } else {
            echo "revisar campo [IDENTIFICADOR EVENTO] en ws ";
            die;
        }
         
        return $reemplazo_PorEspacios;
        
    }

    function fieldVelocidadRegistrada($dato){

        $velocidadReemplazoCaracter = str_replace(',', '.', $dato);

        return $velocidadReemplazoCaracter;
    }

    function fieldnombreApellido($dato){
    
        $separarNombreApellido = preg_split('/,\s*/', $dato, 2, PREG_SPLIT_NO_EMPTY);
    
        while (count($separarNombreApellido) < 2) {
            $separarNombreApellido[] = ' ';
        }
    
        return $separarNombreApellido;
    

    }

    function fieldcuitDni($dni,$cuit){
  
       if(empty($dni)){
        $resultado = [$cuit,4];

        } else{
            $resultado = [$dni, 0];

        }

        return $resultado;
        
    }

    function fielddomicilioTitular($miString){

            $miArray = explode(" ", $miString);

        // Obtener el último elemento del array
        $ultimoElemento = array_pop($miArray);

        // El array resultante $miArray ahora contiene todos los elementos excepto el último
        // Convierte los elementos restantes en un solo string
        $restoElementos = implode(" ", $miArray);

        // Mostrar los resultados
        $array_resultado = [$ultimoElemento,$restoElementos];
        
        return $array_resultado;
    }

    function fieldcapturarSentido($dato){

        $sentido = substr($dato, 0, 1);
        
        return $sentido;
    }

    function fieldcapturarEjido($dato){

        $ejido = substr($dato, 0, 1);

        return $ejido;

    }

    function fieldcapturarCarril($dato){

        if (preg_match('/[CD](\d+)/', $dato, $matches)) 
            $numeroDespuesDeC = $matches[1];
        
        return $numeroDespuesDeC;
    }

    function GetTxtInfo($FileName, $Path) {

        if (!file_exists($Path."/".$FileName.".txt")) {
            echo "No existe material en esa fecha para equipo";
            die;
        }

		$linea =file($Path."/".$FileName.".txt");

		$bandera=0;
		foreach ($linea as $lin) {
			$contenidoArrayFinal[$bandera] = explode(';', $lin);
			$bandera++;
		}

		return $contenidoArrayFinal;
	}

    function GetTxtInfoSerie($FileName, $Path) {
		
		$doc =file($Path."/".$FileName);        
        
		$linea = implode(',', $doc);

		return $linea;
	}

    function removeUnwantedFields($DataCollection) {
        unset($DataCollection['fotoVehiculo']);
		unset($DataCollection['fotoDominialVehiculo']);
        $DataArray = array_map($DataCollection);
		return $DataArray;
	}
    
?>