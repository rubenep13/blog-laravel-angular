<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\User;



class JwtAuth{
    
    public $key;
    
    public function __construct(){
        $this->key = 'fjonqd21841nj42bu141hu41k4n1jkb';
    }
    
    public function signup($username, $password, $getToken = null){
        //Buscar si existe usuario con sus credenciales
        $user = User::where([
            'username'     => $username,
            'password'  => $password
        ])->first();
        
        //Comprobar si son correctas
        $signup = false;
        if(is_object($user)){
            $signup = true;
        }
        
        //Generar token con los datos del usuario identificado
        if($signup){
            $token = array(
                'sub'           => $user->id,
                'username'      => $user->username,
                'description'   => $user->description,
                'image'         => $user->image,
                'iat'           => time(),
                'exp'           => time() + (7*24*60*60)  
            );
            
            $jwt = JWT::encode($token, $this->key, 'HS256');
            
            //Devolver los datos decodificados o el token
            if(is_null($getToken)){
                $data = $jwt;
            } else{
                $decoded = JWT::decode($jwt, $this->key, ['HS256']);
                $data = $decoded;
            }
            
        } else {
            $data = array(
                'status'    => 'error',
                'message'   => 'Login incorrecto.'
            );
        }
        
        return $data;
    }
    
    public function checkToken($jwt, $getIdentity = false){
        $auth = false;
        
        try{
            $jwt = str_replace('"', '', $jwt);
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);
        } catch (\UnexpectedValueException $ex) {
            $auth = false;
        } catch (\DomainException $ex){
            $auth = false;
        }
        
        if(!empty($decoded) && is_object($decoded) && isset($decoded->sub)){
            $auth = true;
        } else{
            $auth = false;
        }
        
        if($getIdentity){
            return $decoded;
        }
        
        return $auth;
        
    }
    
}

