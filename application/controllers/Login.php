<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once( APPPATH.'/libraries/REST_Controller.php');
use Restserver\libraries\REST_Controller;

class Login extends REST_Controller {

    public function __construct(){

        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
        header("Access-Control-Allow-Origin: *");

        parent::__construct();
        $this->load->database();
    }

    public function index_post(){

        $data = $this->post();

        if( !isset( $data['correo']) OR !isset( $data['contrasena']) ){
            
            $response = array( 
                'err' => TRUE, 
                'message' => 'La información enviada no es válida'
            );

            $this -> response( $response, REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        //Tenemos correo y contraseña en un post
        $condicionees = array(
            'correo' => $data['correo'],
            'contrasena' => $data['contrasena']
        );

        $query = $this->db->get_where( 'login', $condicionees );
        $user = $query->row();

        if( !isset($user) ){

            $response = array( 
                'err' => TRUE, 
                'message' => 'Usuario/Contraseña no son válidos'
            );

            $this -> response( $response );
            return;
        }

        // Tenemos un usuario y contraseña válidos
        $token = hash( 'ripemd160', $data['correo'] );

        // Save token on DB
        $this->db->reset_query();
        $updateToken = array( 'token' => $token );

        $this->db->where('id', $user->id );

        $this->db->update( 'login', $updateToken );

        $response = array( 
            'err' => FALSE, 
            'token' => $token,
            'userId' => $user->id
        );

        $this -> response( $response );

    }
}