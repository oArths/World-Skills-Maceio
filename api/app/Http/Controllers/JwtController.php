<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class JwtController extends Controller
{

    private $key;

    public function __construct(){
        $this->key = env('JWT');
    }
    public function Token($username){


        $header = [
            'Typ' => 'JWT',
            'alg' => 'hs256',
        ];

        $now = time();
        $payload = [
        'create' => $now,
        'exp' => $now + 3600,
        'username' => $username

        ];
        $header = base64_encode(json_encode($header));
        $payload = base64_encode(json_encode($payload));

        $sing = base64_encode(hash_hmac('sha256', $header . "." . $payload, $this->key, true));
        $token = "Bearer " . $header . "." . $payload . $sing;

        $user = User::where('username', $username)->first();

        if($user){
            $clear = explode(" ",$token);
            $user->update(['accessToken' => $clear[1]]);
            return $token;
        }

    }

}
