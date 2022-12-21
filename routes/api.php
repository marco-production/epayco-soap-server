<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\SoapController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::any('/registro-cliente', function () {
    $server = new \nusoap_server();
    
    $server->configureWSDL('WalletService', false, url('api'));
    $server->wsdl->schemaTargetNamespace = 'WalletService';

    // Input
    $server->wsdl->addComplexType(
        'registro',
        'complexType',
        'struct',
        'all',
        '',
        array(
            'name' => array('name' => 'name', 'type' => 'xsd:string'),
            'lastname' => array('name' => 'lastname', 'type' => 'xsd:string'),
            'document' => array('name' => 'document', 'type' => 'xsd:string'),
            'cellphone' => array('name' => 'cellphone', 'type' => 'xsd:string'),
            'email' => array('name' => 'email', 'type' => 'xsd:string'),
        )
    );

    // Output
    $server->wsdl->addComplexType(
        'response',
        'complexType',
        'struct',
        'all',
        '',
        array(
            'success' => array('name' => 'success', 'type' => 'xsd:boolean'),
            'message' => array('name' => 'message', 'type' => 'xsd:string'),
            'cod_error' => array('name' => 'message', 'type' => 'xsd:integer'),
            'message_error' => array('name' => 'message', 'type' => 'xsd:string')
        )
    );

    $server->register(
        'registroCliente',
        array('name' => 'tns:registro'),
        array('name' => 'tns:response'),
        'WalletService',
        false,
        'rpc',
        'encoded',
        'Recibe el status de la insercion'
    );

    function registroCliente($request){
        try {
            WalletController::registroCliente($request);

            return array(
                'success' => true,
                'message' => 'Cliente registrado exitosamente'
            );
        } catch(\Exception $e) {
            return array(
                'success' => false,
                'cod_error' => 400 ,
                'message_error' => $e->getMessage()
            ); 
        }
    }

    // Receive data
    $rawPostData = file_get_contents("php://input");
    $server->service($rawPostData);
    exit();
});


Route::any('/recargar-billetera', function () {
    $server = new \nusoap_server();
    
    $server->configureWSDL('WalletService', false, url('api'));
    $server->wsdl->schemaTargetNamespace = 'WalletService';

    // Input
    $server->wsdl->addComplexType(
        'recargar',
        'complexType',
        'struct',
        'all',
        '',
        array(
            'document' => array('name' => 'document', 'type' => 'xsd:string'),
            'cellphone' => array('name' => 'cellphone', 'type' => 'xsd:string'),
            'value' => array('name' => 'value', 'type' => 'xsd:decimal')
        )
    );

    // Output
    $server->wsdl->addComplexType(
        'response',
        'complexType',
        'struct',
        'all',
        '',
        array(
            'success' => array('name' => 'success', 'type' => 'xsd:boolean'),
            'message' => array('name' => 'message', 'type' => 'xsd:string'),
            'cod_error' => array('name' => 'message', 'type' => 'xsd:integer'),
            'message_error' => array('name' => 'message', 'type' => 'xsd:string')
        )
    );

    $server->register(
        'recargaBilletera',
        array('name' => 'tns:recargar'),
        array('name' => 'tns:response'),
        'WalletService',
        false,
        'rpc',
        'encoded',
        'Recibe el nuevo balance'
    );

    function recargaBilletera($request){
        try {
            $balance = WalletController::recargaBilletera($request);

            return array(
                'success' => true,
                'message' => 'Billetera actualizada, nuevo balance: '. $balance
            );
        } catch (\Exception $e){
            return array(
                'success' => false,
                'cod_error' => 400,
                'message_error' => $e->getMessage()
            ); 
        }

    }

    // Receive data
    $rawPostData = file_get_contents("php://input");
    $server->service($rawPostData);
    exit();
});


Route::any('/consultar-saldo', function () {
    $server = new \nusoap_server();
    
    $server->configureWSDL('WalletService', false, url('api'));
    $server->wsdl->schemaTargetNamespace = 'WalletService';

    // Input
    $server->wsdl->addComplexType(
        'saldo',
        'complexType',
        'struct',
        'all',
        '',
        array(
            'document' => array('name' => 'document', 'type' => 'xsd:string'),
            'cellphone' => array('name' => 'cellphone', 'type' => 'xsd:string'),
        )
    );

    // Output
    $server->wsdl->addComplexType(
        'response',
        'complexType',
        'struct',
        'all',
        '',
        array(
            'success' => array('name' => 'success', 'type' => 'xsd:boolean'),
            'message' => array('name' => 'message', 'type' => 'xsd:string'),
            'cod_error' => array('name' => 'message', 'type' => 'xsd:integer'),
            'message_error' => array('name' => 'message', 'type' => 'xsd:string'),
        )
    );

    $server->register(
        'consultarSaldo',
        array('name' => 'tns:saldo'),
        array('name' => 'tns:response'),
        'WalletService',
        false,
        'rpc',
        'encoded',
        'Recibe el saldo actual'
    );

    function consultarSaldo($request){
        try {
            $saldo = WalletController::consultarSaldo($request);

            return array(
                'success' => true,
                'message' => 'Tu saldo actual es: '. $saldo
            );

        } catch (\Exception $e){
            return array(
                'success' => false,
                'cod_error' => 400,
                'message_error' => $e->getMessage()
            ); 
        }
        
    }

    // Receive data
    $rawPostData = file_get_contents("php://input");
    $server->service($rawPostData);
    exit();
});


Route::any('/pagar ', function () {
    $server = new \nusoap_server();
    
    $server->configureWSDL('WalletService', false, url('api'));
    $server->wsdl->schemaTargetNamespace = 'WalletService';

    // Input
    $server->wsdl->addComplexType(
        'pago',
        'complexType',
        'struct',
        'all',
        '',
        array(
            'document' => array('name' => 'document', 'type' => 'xsd:string'),
            'cellphone' => array('name' => 'cellphone', 'type' => 'xsd:string'),
            'product_id' => array('name' => 'product_id', 'type' => 'xsd:integer'),
        )
    );

    // Output
    $server->wsdl->addComplexType(
        'response',
        'complexType',
        'struct',
        'all',
        '',
        array(
            'success' => array('name' => 'success', 'type' => 'xsd:boolean'),
            'message' => array('name' => 'message', 'type' => 'xsd:string'),
            'cod_error' => array('name' => 'message', 'type' => 'xsd:integer'),
            'message_error' => array('name' => 'message', 'type' => 'xsd:string'),
            'session_id' => array('name' => 'session_id', 'type' => 'xsd:string')
        )
    );

    $server->register(
        'pagar',
        array('name' => 'tns:pago'),
        array('name' => 'tns:response'),
        'WalletService',
        false,
        'rpc',
        'encoded',
        'Realizar el pago de un producto'
    );

    function pagar($request){
        try {
            $sessionId = WalletController::pagar($request);

            return array(
                'success' => true,
                'message' => 'Se ha enviado un correo con el token para confirmar el pago.',
                'session_id' => $sessionId
            );
        } catch(\Exception $e) {
            return array(
                'success' => false,
                'cod_error' => 400 ,
                'message_error' => $e->getMessage()
            ); 
        }
    }

    // Receive data
    $rawPostData = file_get_contents("php://input");
    $server->service($rawPostData);
    exit();
});

Route::any('/confirmar-pago ', function () {
    $server = new \nusoap_server();
    
    $server->configureWSDL('WalletService', false, url('api'));
    $server->wsdl->schemaTargetNamespace = 'WalletService';

    // Input
    $server->wsdl->addComplexType(
        'confirmar',
        'complexType',
        'struct',
        'all',
        '',
        array(
            'token' => array('name' => 'token', 'type' => 'xsd:integer'),
            'session_id' => array('name' => 'session_id', 'type' => 'xsd:string'),
        )
    );

    // Output
    $server->wsdl->addComplexType(
        'response',
        'complexType',
        'struct',
        'all',
        '',
        array(
            'success' => array('name' => 'success', 'type' => 'xsd:boolean'),
            'message' => array('name' => 'message', 'type' => 'xsd:string'),
            'cod_error' => array('name' => 'message', 'type' => 'xsd:integer'),
            'message_error' => array('name' => 'message', 'type' => 'xsd:string'),
        )
    );

    $server->register(
        'confirmarPago',
        array('name' => 'tns:confirmar'),
        array('name' => 'tns:response'),
        'WalletService',
        false,
        'rpc',
        'encoded',
        'Confirma el pago de un producto'
    );

    function confirmarPago($request){
        try {
            WalletController::confirmarPago($request);

            return array(
                'success' => true,
                'message' => 'El pago se ha confirmado exitosamente.',
            );
        } catch(\Exception $e) {
            return array(
                'success' => false,
                'cod_error' => 400 ,
                'message_error' => $e->getMessage()
            ); 
        }
    }

    // Receive data
    $rawPostData = file_get_contents("php://input");
    $server->service($rawPostData);
    exit();
});