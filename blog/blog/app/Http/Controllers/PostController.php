<?php

namespace App\Http\Controllers;

use App\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helpers\JwtAuth;
use App\User;

class PostController extends Controller
{
    public function __construct(){
        $this->middleware('api.auth', ['except' => ['index', 'show', 'getImage', 'getPostsByUser']]);
    }
    
    public function index(){
        $posts = Post::orderBy('created_at', 'DESC')->get()->load('user');
        
        return response()->json([
            'code'      => 200,
            'status'    => 'success',
            'posts'     => $posts
        ]);
    }
    
    public function show($id){
        $post = Post::find($id);
        if(!empty($post)){
            $post->load('user');
            $data = array(
                'code'      => 200,
                'status'    => 'success',
                'post'      => $post
            );
        }else{
            $data = array(
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'El post no existe.'
            );
        }
        
        return response()->json($data, $data['code']);
    }
    
    public function store(Request $request){
        
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);
        
        if(!empty($params_array)){
            $user = $this->getUser($request);
            
            $validate = \Validator::make($params_array, [
                'title'     => 'required',
                'content'   => 'required',
            ]);
            
            if($validate->fails()){
                $data = array(
                    'code'      => 400,
                    'status'    => 'error',
                    'message'   => 'El post no se ha creado correctamente. Faltan datos'
                );
            } else{ 
                $post = new Post();
                $post->user_id = $user->sub;
                $post->title = $params->title;
                $post->content = $params->content;
                if(isset($params->image)){
                    $post->image = $params->image;
                }
                $post->save();
                
                $data = array(
                    'code'      => 200,
                    'status'    => 'success',
                    'post'      => $post
                );
            }
        } else{
            $data = array(
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'El post no se ha creado correctamente.'
            );
        }
        
        return response()->json($data, $data['code']);
    }
    
    public function update($id, Request $request){
        
        $user = $this->getUser($request);
        
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);
        
        if(!empty($params_array)){
            
            $validate = \Validator::make($params_array, [
                'title'     => 'required',
                'content'   => 'required',
            ]);
            
            if($validate->fails()){
                $data = array(
                    'code'      => 400,
                    'status'    => 'error',
                    'message'   => 'El post no se ha actualizado correctamente. Faltan datos'
                );
            } else{ 
                
                unset($params_array['id']);
                unset($params_array['user_id']);
                unset($params_array['created_at']);
                
                $post = Post::where('id', $id)->first();
                
                if($post->user_id == $user->sub || $user->username == 'admin'){
                    $post = Post::where('id', $id)->update($params_array);
                    $data = array(
                        'code'      => 200,
                        'status'    => 'success',
                        'post'      => $post
                    );
                }else{
                    $data = array(
                        'code'      => 400,
                        'status'    => 'error',
                        'message'   => 'No hay permisos'
                    );
                }
            }
        } else{
            $data = array(
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'El post no se ha actualizado correctamente.'
            );
        }
        
        return response()->json($data, $data['code']);
    }
    
    public function destroy($id, Request $request){
        
        $user = $this->getUser($request);
        
        $post = Post::where('id',$id)->first();
        if(isset($post)){
            if($post->user_id == $user->sub || $user->username == 'admin'){
                $post->delete();
                $data = array(
                    'code'      => 200,
                    'status'    => 'success',
                    'message'   => 'Post eliminado correctamente.'
                );
            }else{
                $data = array(
                    'code'      => 400,
                    'status'    => 'error',
                    'message'   => 'No hay permisos'
                );
            }
        }else{
            $data = array(
                'code'      => 404,
                'status'    => 'error',
                'message'   => 'El post no existe.'
            );
        }
        return response()->json($data, $data['code']);
    }
    
    
    public function upload(Request $request){
        
        $image = $request->file('file0');
        
        $validate = \Validator::make($request->all(),[
            'file0' => 'required|mimes:jpg,jpeg,png,gif'
        ]);
        
        if($validate->fails()){
            $data = array(
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'Error al subir la imagen.',
                'errors'    => $validate->errors()->all()
            );
        }else{
            $image_name = time().$image->getClientOriginalName();
            \Storage::disk('images')->put($image_name, \File::get($image));
            $data = array(
                'code'      => 200,
                'status'    => 'success',
                'image'     => $image_name
            );
        }
        
        return response()->json($data, $data['code']);
    }
    
    
    public function getImage($filename){
        $isset = \Storage::disk('images')->exists($filename);
        if($isset){
            $file = \Storage::disk('images')->get($filename);
            return new Response($file, 200);
        } else{
            $data = array(
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'La imagen no existe'
            );
        }
        return response()->json($data, $data['code']);
    }
    
    
    public function getPostsByUser($id){
        $user = User::where('id', $id)->first();
        if(isset($user)){
            $posts = Post::where('user_id', $id)->get();
            return response()->json([
                'status' => 'success',
                'posts' => $posts
            ], 200);
        }else{
            $data = array(
                'code'      => 404,
                'status'    => 'error',
                'message'   => 'El usuario no existe'
            );
        }
        
        
    }
    
    
    
     
    private function getUser($request){
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $user = $jwtAuth->checkToken($token, true);
        
        return $user;
    }
}
