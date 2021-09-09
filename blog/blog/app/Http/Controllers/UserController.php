<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\User;

class UserController extends Controller
{
    public function register(Request $request) {

        //Recoger datos del usuario por post
        $json = $request->input('json', null);
        $params = json_decode($json);                   //objeto
        $params_array = json_decode($json, true);       //array


        if (!empty($params) && !empty($params_array)) {

            //Limpiar datos (elimina espacios por delante y por detras del string)
            $params_array = array_map('trim', $params_array);

            //Validar datos
            $validate = \Validator::make($params_array, [
                        'username' => 'required|unique:users',
                        'password' => 'required'
            ]);

            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'El nombre de usuario ya existe',
                    'errors' => $validate->errors()
                );
            } else {    //Validación pasada correctamente
                //Cifrar contraseña
                $pwdCifrada = hash('sha256', $params->password);
                
                //Crear el usuario
                $user = new User();
                $user->username = $params_array['username'];
                $user->password = $pwdCifrada;
                
                //Guardar el usuario
                $user->save();
                
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'El usuario se ha creado correctamente',
                    'user' => $user
                );
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Los datos enviados no son correctos',
            );
        }

        return response()->json($data, $data['code']);
    }

    public function login(Request $request) {
        
        $jwtAuth = new \JwtAuth();
        
        //Recibir datos por post
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);
        
        //Validar datos
        $validate = \Validator::make($params_array, [
            'username' => 'required',
            'password' => 'required'
        ]);

        if ($validate->fails()) {
            $signup = array(
                'status'    => 'error',
                'code'      => 400,
                'message'   => 'El usuario no se ha creado',
                'errors'    => $validate->errors()
            );
        } else{
            //Cifrar contrasena
            $pwd = hash('sha256', $params->password);
            
            //Devolver token o datos
            $signup = $jwtAuth->signup($params->username, $pwd);
            if(!empty($params->getToken)){
                $signup = $jwtAuth->signup($params->username, $pwd, true);
            }
        }
        
        return response()->json($signup, 200);
        
    }
    
    public function update(Request $request){
        
        //Comprobar si el usuario está identificado
        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);
        
        //Recoger los datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);
        
        if(!empty($params_array)){
            
            //Sacar usuario identificado
            $user = $jwtAuth->checkToken($token, true);
            
            //Validar datos
            $validate =\Validator::make($params_array, [
                'username' => 'required|unique:users,'.$user->sub
            ]);
            
            //Quitar los campos que no quiero actualizar
            unset($params_array['id']);
            unset($params_array['password']);
            unset($params_array['created_at']);
            unset($params_array['remember_token']);
            
            //Actualizar usuario en BD
            $user_update = User::where('id', $user->sub)->update($params_array);
            
            //Devolver array con resultado
            if($user_update == 1){
                $data = array(
                    'code' => 200,
                    'status' => 'success',
                );
            }else{
                $data = array(
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'El usuario no se ha actualizado correctamente.'
                );
            }
        }else{
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'El usuario no está identificado.'
            );
        }
        return response()->json($data, $data['code']);
    }
    
    public function upload(Request $request){
        
        //Recoger datos de la petición
        $image = $request->file('file0');
        
        //Validación de imagen
        $validate = \Validator::make($request->all(),[
           'file0' => 'required|image' 
        ]);
        
        //Guardar imagen
        if($image && !$validate->fails()){
            $image_name = time().$image->getClientOriginalName(); //pongo el time para asegurar que no se sobreescriben cosas en el servidor
            \Storage::disk('users')->put($image_name, \File::get($image));
            
            
            $data = array(
                'code' => 200,
                'status' => 'success',
                'image' => $image_name,
            );
        } else{
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al subir la imagen.'
            );
        }
        return response()->json($data, $data['code']);
    }
    
    public function getImage($filename){
        $isset = \Storage::disk('users')->exists($filename);
        if($isset){
            $file = \Storage::disk('users')->get($filename);
            return new Response($file, 200);
        }else{
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'La imagen no existe.'
            );
            return response()->json($data, $data['code']);
        }
        
    }
    
    public function detail($id){
        $user = User::find($id);
        
        if(is_object($user)){
            $data = array(
                'code'      => 200,
                'status'    => 'succes',
                'user'      => $user
            );
        } else{
            $data = array(
                'code'      => 400,
                'status'    => 'error',
                'user'      => 'El usuario no existe.'
            );
        }
        
        return response()->json($data, $data['code']);
    }
    
}
