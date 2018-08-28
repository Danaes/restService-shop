<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once( APPPATH.'/libraries/REST_Controller.php');
use Restserver\libraries\REST_Controller;

class Prueba extends REST_Controller {

    public function __construct(){

        header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
        header("Access-Control-Allow-Origin: *");

        parent::__construct();
        $this->load->database();
    }

    public function index() {
        
        echo "Hola mundo";
    }

    public function getArray_get( $index = 0){

        if( $index > 2 ){
            $response = array( 'err' => TRUE, 'message' => 'No existe elemento en la posición '.$index);
            $this -> response( $response, REST_Controller::HTTP_BAD_REQUEST);
        } else {
            $array = array("manzana", "pera", "piña");
            $response = array( 'err' => FALSE, 'fruta' =>  $array[$index] );
            $this -> response( $response );
        }
    }

    public function getProduct_get( $code){

        $query = $this->db->query("SELECT * FROM `productos` WHERE codigo = '". $code ."'");

        $this -> response( $query -> result() );
    }
}