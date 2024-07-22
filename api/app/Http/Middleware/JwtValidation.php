<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class JwtValidation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    
    private $key;

    public function __construct(){
        $this->key = env("JWT");

    }

    public function handle(Request $request, Closure $next){

        $data = $this->getToken($request);
        if(!$data){
           return error("message",'Necessário estar autenticado no sistema', 401);
        }

        $token = $this->validToken($data);
        if(!$token){
            return error("message",'Token inválido', 403);
        }
        $request->merge(['auth' => (array) $token]);

        return $next($request);
    }
    public function getToken($request){
        $data = $request->header('Authorization');

        if(empty($data)) {
            return false;
        }
        $token = explode(' ', $data);


        return $token[1];
    }
    public function validToken($token){

        list($header, $payload, $sing) = explode('.', $token);
        
        $decPayload = json_decode(base64_decode($payload));
        $validSing = base64_encode(hash_hmac('sha256', $header . "." . $payload, $this->key, true));

        if($sing !== $validSing){
            return false;
        }
        if($decPayload->exp < time()){
            return false;
        }
        return $decPayload;
        

    }


}
