<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
Class Importer_model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }


    //img
    function GetPictureForDominioRequest($data, $token) {

        $url = "http:/WWW-link//WS/_API.php";
       
        $ch = curl_init($url);
        
        // Configura 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 
        
        // Realiza la solicitud 
        $response = json_decode(curl_exec($ch));
                
        if (curl_errno($ch)) {
            echo 'Error cURL: ' . curl_error($ch);
        }
        
        curl_close($ch);
                
        return $response;
        
    }


    function GetInfractionsByTwoDates($data, $token) 
    {
       
        $ch = json_decode(file_get_contents("http://http:/WWW-link//WS/_API.php?desde=".$data['fechaDesde']."&hasta=".$data['fechaHasta'].""));

        return($ch);

        
    }

    function insert($datosArray) {
        
        $this->db->insert('presuncion', $datosArray);

        if ($this->db->affected_rows() > 0) {
            echo "<br> Inserción exitosa. Se insertó el registro con ID: " . $this->db->insert_id();
        } else {
            echo "Error en la inserción: " . $this->db->error();
        }
        
    }
 

    function GetProtocoloExpoMain($datosArray){

        $serie = $datosArray[0] ;
        $DateOne = $datosArray[1] ;
        $DateTwo = $datosArray[2] ;
        $cantreg = $datosArray[3] ;       


        $urlDestino = "http://http:/WWW-link//WS/generarExpoRn.php?serie=".$serie."&fechaI=".$DateOne."&fechaF=".$DateTwo."&cantreg=".$cantreg."";
        
        $datass = http_build_query($datosArray);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlDestino);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $datass);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $respuesta = curl_exec($ch);
        curl_close($ch);

        return $respuesta;
    }


    function PostNotCompProceso($data, $token) {

        $ch = json_decode(file_get_contents("http:/WWW-link/WS/_API.php?idActa=".$data['idActa']."&nroActa=".$data['nroActa'].""));
   
        return($ch);

    }


    function GetEquiposMain(){

        $urlDestino = "http:/http:/WWW-link//WS/listados.php";

        $ch = file_get_contents($urlDestino);
   
        return($ch);
    }


    function CreateDirAndFillIt($Packagetxt) {
    
          
        $jsonData = json_encode($Packagetxt);
        $serviceUrl = 'http:/WWW-link//WS/crearImagen.php';
        $ch = curl_init($serviceUrl);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        } 

        curl_close($ch);

            
    
    }


    function createTxtEntrada($Information,$PackageGen) {

        array_push($Information, $PackageGen);

        $serviceUrl = 'http:/http:/WWW-link//WS/crearTxtEntrada.php';        

        $jsonData = json_encode($Information);

        $ch = curl_init($serviceUrl);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }

        curl_close($ch);
        
        return $response;
    }


}


