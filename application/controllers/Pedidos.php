<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once( APPPATH.'/libraries/REST_Controller.php');
use Restserver\libraries\REST_Controller;

class Pedidos extends REST_Controller {

    public function __construct(){

        header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
        header("Access-Control-Allow-Origin: *");

        parent::__construct();
        $this->load->database();
    }

    public function makeOrder_post( $token = "0", $userId = "0" ){

        $data = $this->post();

        if ( $token == "0" || $userId == "0" ){
            $response = array(
                'err' => TRUE,
                'message' => "Token/usuario inválido"
            );

            $this->response( $response, REST_Controller::HTTP_BAD_REQUEST );
            return;
        }

        if( !isset ( $data["items"] ) || strlen ( $data["items"] ) == 0){
            $response = array(
                'err' => TRUE,
                'message' => "Faltan productos"
            );

            $this->response( $response, REST_Controller::HTTP_BAD_REQUEST );
            return;
        }

        //Tenemos items, usuario y token
        $condiciones = array( 
            'id' => $userId,
            'token' => $token
        );
        $this->db->where( $condiciones );
        $query = $this->db->get( 'login' );

        $existe = $query->row();

        if( !$existe ){
            $response = array(
                'err' => TRUE,
                'message' => "Usuario y token incorrectos"
            );

            $this->response( $response, REST_Controller::HTTP_UNAUTHORIZED );
            return;
        }

        // Usuario y token son correctos
        $this->db->reset_query();

        $insert = array('usuario_id' => $userId);
        $this->db->insert('ordenes', $insert);

        $orderId =  $this->db->insert_id();

        //crear el detalle de la orden
        $this->db->reset_query();

        $items = explode( ',', $data['items'] );

        foreach( $items as &$producto_id){

            $dataInsert = array(
                'producto_id' => $producto_id,
                'orden_id' => $orderId
            );

            $this->db->insert('ordenes_detalle', $dataInsert);
        }

        $response = array(
            'err' => FALSE,
            'orden_id' => $orderId
        );

        $this->response( $response );

    }

    public function getOrder_get( $token = "0", $userId = "0" ){

        if ( $token == "0" || $userId == "0" ){
            $response = array(
                'err' => TRUE,
                'message' => "Token/usuario inválido"
            );

            $this->response( $response, REST_Controller::HTTP_BAD_REQUEST );
            return;
        }

        $condiciones = array( 
            'id' => $userId,
            'token' => $token
        );
        $this->db->where( $condiciones );
        $query = $this->db->get( 'login' );

        $existe = $query->row();

        if( !$existe ){
            $response = array(
                'err' => TRUE,
                'message' => "Usuario y token incorrectos"
            );

            $this->response( $response, REST_Controller::HTTP_UNAUTHORIZED );
            return;
        }

        //Devolver todas las ordenes del usuario
        $query = $this->db->query('SELECT * FROM `ordenes` WHERE usuario_id = '. $userId);

        $orders = array ();

        foreach( $query->result()  as $row ){

            $query_detalle = $this->db->query('SELECT od.orden_id, p.* FROM `ordenes_detalle` od INNER JOIN productos p on od.producto_id = p.codigo WHERE od.orden_id = '. $row->id);

            $orden = array(
                'id' => $row->id,
                'creado_en' => $row->creado_en,
                'detalle' => $query_detalle->result()
            );

            array_push($orders, $orden);
        }

        $response = array(
            'err' => FALSE,
            'ordenes' => $orders
        );

        $this->response( $response );
    }

    public function removeOrder_delete( $token = "0", $userId = "0", $orderId = "0" ){

        if ( $token == "0" || $userId == "0" || $orderId == "0"){
            $response = array(
                'err' => TRUE,
                'message' => "Token/usuario/orden inválido"
            );

            $this->response( $response, REST_Controller::HTTP_BAD_REQUEST );
            return;
        }

        $condiciones = array( 
            'id' => $userId,
            'token' => $token
        );
        $this->db->where( $condiciones );
        $query = $this->db->get( 'login' );

        $existe = $query->row();

        if( !$existe ){
            $response = array(
                'err' => TRUE,
                'message' => "Usuario y token incorrectos"
            );

            $this->response( $response, REST_Controller::HTTP_UNAUTHORIZED );
            return;
        }

        // Verificar si la orden es de ese usuario
        $this->db->reset_query();
        $condiciones = array(
            'id' => $orderId,
            'usuario_id' => $userId
        );
        $this->db->where( $condiciones );
        $query = $this->db->get( 'ordenes' );

        $existe = $query->row();

        if( !$existe ){
            $response = array(
                'err' => TRUE,
                'message' => "Esa orden no puede ser borrada"
            );

            $this->response( $response );
            return;
        }
        
        //Todo esta bien
        $condiciones = array( 'id' => $orderId );
        $this->db->delete( 'ordenes', $condiciones );

        $condiciones = array( 'orden_id' => $orderId );
        $this->db->delete( 'ordenes_detalle', $condiciones );

        $response = array(
            'err' => FALSE,
            'message' => "Orden eliminada"
        );

        $this->response( $response );
    }

}