<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once( APPPATH.'/libraries/REST_Controller.php');
use Restserver\libraries\REST_Controller;

class Productos extends REST_Controller {

    public function __construct(){

        header("Access-Control-Allow-Methods: GET");
        header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
        header("Access-Control-Allow-Origin: *");

        parent::__construct();
        $this->load->database();
    }

    public function getAll_get( $pagina = 0){

        $pagina = $pagina * 10;

        $query = $this->db->query('SELECT * FROM `productos` LIMIT '. $pagina .',10');
        
        $response = array(
            'err' => FALSE,
            'productos' => $query -> result_array()
        );

        $this->response( $response );
    }

    public function getByType_get( $tipo = 0, $pagina = 0){

        if ( $tipo == 0 ){
            
            $response = array( 
                'err' => TRUE, 
                'message' => 'No existe ese tipo'
            );

            $this -> response( $response, REST_Controller::HTTP_BAD_REQUEST);

        } else {     
            
            $pagina = $pagina * 10;
    
            $query = $this->db->query('SELECT * FROM `productos` WHERE linea_id = '. $tipo .' LIMIT '. $pagina .',10');
            
            $response = array(
                'err' => FALSE,
                'productos' => $query -> result_array()
            );
    
            $this->response( $response );
        }

    }

    public function search_get( $termino = "sin termino"){

        $query = $this->db->query("SELECT * FROM `productos` where producto LIKE '%". $termino ."%'");
        
        $response = array(
            'err' => FALSE,
            'termino' => $termino,
            'productos' => $query -> result_array()
        );

        $this->response( $response );
    }
}