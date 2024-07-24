<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\JwtController;
use App\Http\Middleware\JwtValidation;
use Illuminate\Http\Resources\Json\JsonResource;

// use App\Http\Requests\UserRequest;

class UserController extends Controller
{

    public function login(Request $parms){
        $jwt = new JwtController;
        $tokenValid = new JwtValidation;

        if(!isset($parms['username'])  || !isset($parms['password'])){
            return error('message','Credenciais inválidas', 422 );
        }
        if(strlen($parms['username']) < 1  || strlen($parms['password']) < 1){
            return error('message','Credenciais inválidas', 422 );
        }

        $user = User::where('username', $parms['username'])->first();
        $valid = $tokenValid->validToken($user->accessToken);

        if($valid){
            return data([
                'message' => 'Usuário já autenticado'
            ],403);
        }
        if(!$user){
            return error('message','Credenciais inválidas', 422 );
        }

        if(strlen($user->password) !== 64){
            $hashPassword = hash('sha256', $user->password);
            $user->update(['password'=>  $hashPassword]); 
        }
        $user = User::where('username', $parms['username'])->first();

        if(hash('sha256', $parms['password']) !== $user->password){
            return error('message','Credenciais inválidasg', 422 );
        }
    
        $token = $jwt->Token($parms['username']);
        
        return  error('token', $token, 200 );
    }
   public function logOutUser(Request $user){
       $user = User::where('username', $user->auth['username'])->first();
       $user->update(['accessToken' => 'null']);
       return error('message', "Logout com sucesso", 200);

   }
}
