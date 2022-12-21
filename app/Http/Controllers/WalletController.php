<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use App\Models\UserToken;
use App\Models\UserSession;
use App\Models\Transaction;
use App\Models\Product;
use App\Jobs\SendToken;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * Create new client
     *
     * @param $request
     * @return \Illuminate\Http\Response
     */
    public static function registroCliente($request)
    {
        try {
            // Create Client/User    
            $user = User::create([
                'name' => $request['name'],
                'lastname' => $request['lastname'],
                'document' => $request['document'],
                'cellphone' => $request['cellphone'],
                'email' => $request['email'],
            ]);

            // Create Wallet
            Wallet::create([
                'value' => 0,
                'user_id' => $user->id
            ]);

            // Create session ID
            session_start();
            $sessionId = session_id();
            
            // Save session ID
            UserSession::create([
                'session_id' => $sessionId,
                'user_id' => $user->id,
            ]);

        } catch(\Exception $e) {
            if(array_key_exists('document', $request) && array_key_exists('email', $request)){
                // Validate if exists any user with same email or document
                $validation =  User::where('document', $request['document'])->orWhere('email', $request['email'])->first();

                if($validation)
                    throw new \Exception("Este cliente ya fue registrado."); 
            }

            throw new \Exception("Todos los campos son requeridos."); 
        }
    }

    /**
     * Top up wallet
     *
     * @param  $request
     * @return double
     */
    public static function recargaBilletera($request)
    {
        // Find user
        $user = User::where([
            ['document', $request['document']],
            ['cellphone', $request['cellphone']]
        ])->first();

        // If don't exists this user
        if(!$user)
            throw new \Exception("Esta billetera no existe"); 

        // Find wallet
        $wallet = Wallet::firstWhere('user_id', $user->id);

        // Update wallet
        $newValue = $wallet->value + $request['value'];
        $wallet->update(['value' => $newValue]);

        return $newValue;
    }

    /**
     * Pay with wallet
     *
     * @param $request
     * @return Session Id
     */
    public static function pagar($request)
    {
        try{
            // Find user
            $user = User::where([
                ['document', $request['document']],
                ['cellphone', $request['cellphone']]
            ])->first();
            
            // Save transaction
            $transaction = Transaction::create([
                'status' => false,
                'product_id' => $request['product_id'],
                'user_id' => $user->id
            ]);

            // Save token and transaction ID
            $token = random_int(100000, 999999);

            UserToken::create([
                'token' => $token,
                'user_id' => $user->id,
                'transaction_id' => $transaction->id
            ]);
            
            // Send token to user email
            SendToken::dispatch($user->name, $user->email, $token);

            // Return session ID
            $userSession = UserSession::firstWhere(['user_id' => $user->id]);

            return $userSession->session_id;

        } catch(\Exception $e) {
            throw new \Exception("Los datos de la billetera no son validos o este producto no existe."); 
        }
    }

    /**
     * Confirm payment
     *
     * @param array $request
     * 
     */
    public static function confirmarPago($request)
    {
        // Validate token
        $userToken = UserToken::where('token', $request['token'])->firstOrFail();

        // Validate session ID
        $userSession = UserSession::where('session_id', $request['session_id'])->firstOrFail();

        // Get transaction
        $transaction = Transaction::find($userToken->transaction_id);
        $product = Product::find($transaction->product_id);

        // Deduct product price from user wallet
        $user = User::find($userToken->user_id);
        $newBalance = $user->wallet->value - $product->price;    

        $user->wallet->update([
            'value' => $newBalance
        ]);

        // Update transaction status
        $transaction->update([
            'status' => true
        ]);
    }

    /**
     * Check balance
     *
     * @param $request
     * @return double
     */
    public static function consultarSaldo($request)
    {
        // Find user
        $user = User::where([
            ['document', $request['document']],
            ['cellphone', $request['cellphone']]
        ])->first();

        // If don't exists this user
        if(!$user)
            throw new \Exception("Esta billetera no existe"); 

        // Find wallet
        $wallet = Wallet::firstWhere('user_id', $user->id);
        
        return $wallet->value;

    }

    

}
