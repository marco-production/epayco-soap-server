<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SoapController extends Controller
{
    public function server() {
        //require_once ('nusoap.php');
        $server = new \nusoap_server();
    
        $server->configureWSDL('TestService', false, url('api'));
        $server->wsdl->schemaTargetNamespace = 'TestService';

        // Input
        $server->wsdl->addComplexType(
            'ordenDeCompra', //Nombre del objeto
            'complexType', // De tipo complejo
            'struct',
            'all',
            '',
            array(
                'NumeroOrden' => array('name' => 'NumeroOrden', 'type' => 'xsd:string'),
                'Ordenante' => array('name' => 'Ordenante', 'type' => 'xsd:string'),
                'Modena' => array('name' => 'Modena', 'type' => 'xsd:string'),
                'TipoCambio' => array('name' => 'TipoCambio', 'type' => 'xsd:decimal'),
            )
        );

        // Response
        $server->wsdl->addComplexType(
            'response', //Nombre del objeto
            'complexType', // De tipo complejo
            'struct',
            'all',
            '',
            array(
                'NumeroDeAutorizacion' => array('name' => 'NumeroDeAutorizacion', 'type' => 'xsd:string'),
                'Resultado' => array('name' => 'Resultado', 'type' => 'xsd:boolean'),
            )
        );
    
        $server->register(
            'guardarOrdenDeCompra',
            array('name' => 'tns:ordenDeCompra'),
            array('name' => 'tns:response'),
            'TestService',
            false,
            'rpc',
            'encoded',
            'Recibe una orden de compra y retorna un numero de autorizacion'
        );

        function guardarOrdenDeCompra($request){
            return array(
                //'NumeroDeAutorizacion' => 'La orden de compra '.$request['NumeroOrden']. 'Ha sido autorizada con el numero 23',
                'Resultado' => true
            );
        }
    
        // Recibir datos
        $rawPostData = file_get_contents("php://input");
        $server->service($rawPostData);
        exit();

        //return \Response::make($server->service($rawPostData), 200, array('Content-Type' => 'text/xml; charset=ISO-8859-1'));
    }
}
