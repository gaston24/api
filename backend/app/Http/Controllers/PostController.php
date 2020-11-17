<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Post;
use App\Helpers\JwtAuth;

class PostController extends Controller
{
    public function __construct(){
        $this->middleware('api.auth', [
            'except' => [
                'index', 
                'show', 
                'getImage', 
                'getPostsByCategory', 
                'getPostsByUser'
                ]
            ]);
    }

    public function index(){
        $posts = Post::all()->load('category');
        return response()->json([
            'code' => 200, 
            'status' => 'success',
            'posts' => $posts
        ], 200);
    }

    public function show($id){
        $posts = Post::find($id)->load('category');

        if(is_object($posts)){
            $data = ['code' => 200, 
            'status' => 'success',
            'posts' => $posts];
        }else{
            $data = ['code' => 404, 
            'status' => 'error',
            'message' => 'la entrada no existe'
        ];
        }

        return response()->json($data, $data['code']);
    }

    public function store(Request $request){
        
        //recoger datos por post

        $json = $request->input('json', null);

        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if(!empty($params_array)){

            //conseguir datos del usuario identificado
            $user = $this->getIdentity($request);
            
            //validar los datos

            $validate = \Validator::make($params_array, [
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required',
                'image' => 'required'
            ]);

            if($validate->fails()){
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'faltan datos'
                ];

            }else{

                // guardar el post
                $post = new Post();
                $post->user_id = $user->sub;
                $post->category_id = $params->category_id;
                $post->title = $params->title;
                $post->content = $params->content;
                $post->image = $params->image;

                $post->save();

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'post' => $post
                ];
            }
            
        }else{
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'faltan datos'
            ];
        }
        
        // devolver la response

        return response()->json($data, $data['code']);
    }

    public function update($id, Request $request){
        // recoger los datos por post

        $json = $request->input('json', null);

        $params = json_decode($json);
        $params_array = json_decode($json, true);

        // data por defecto

        $data = array(
            'code' => 400, 
            'status' => 'error', 
            'massage' => 'datos enviados incorrectos aca', 
            'params' => $params_array 
        );

        if(!empty($params_array)){

            
            // validar los datos

            $validate = \Validator::make($params_array, [
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required'
                ]);

            if($validate->fails()){
                $data['errors'] = $validate->errors();
                return response()->json($data, $data['code']);
            }
            
            // eliminar lo que no queremos actualizar
            
            unset($params_array['id']);
            unset($params_array['user_id']);
            unset($params_array['created_at']);
            unset($params_array['user']);

            //conseguir datos del usuario identificado
            $user = $this->getIdentity($request);
            
            // buscar el registro

            $post = Post::where('id', $id)
                    ->where('user_id', $user->sub)
                    ->first();

            if(!empty($post) && is_object($post)){

                // actualizar el regit
                $post->update($params_array);

                // devolver algo
                $data = array(
                    'code' => 200, 
                    'status' => 'success', 
                    'post' => $post,
                    'changes' => $params_array
                );

            }        

            /*
            
            $where = [
                'id' => $id, 
                'user_id' => $user->sub
            ];
            
            
            $post = Post::updateOrCreate($where, $params_array);
            
            */        
            // el metodo updateOrCreate() soporta un solo where()
            // $post = Post::where('id', $id)
            //             ->where('user_id', $user->sub)
            //             ->updateOrCreate($params_array);
            
            
            
        }

        return response()->json($data, $data['code']);
    }

    public function destroy($id, Request $request){

        // conseguir usuario identificado

        $user = $this->getIdentity($request);

        // conseguir el post

        $post = Post::where('id', $id)
                    ->where('user_id', $user->sub)
                    ->first();

        if(!empty($post)){

            
            // borrarlo
            
            $post->delete();
            
            // devolver algo
            
            $data = array(
                'code' => 200, 
                'status' => 'success', 
                'post' => $post
            );
        }else{
            $data = array(
                'code' => 404, 
                'status' => 'error', 
                'message' => 'el post no existe'
            );
        }

        return response()->json($data, $data['code']);
    }
    
    private function getIdentity($request){
        
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $user = $jwtAuth->checkToken($token, true);
        
        return $user;
    }
    
    public function upload(Request $request){

        // recoger la imagen de la peticion

        $image = $request->file('file0');

        // validar la imagen

        $validate =\Validator::make($request->all(), [
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        // guardar imagen en disco

        if(!$image || $validate->fails()){
            $data = array(
                'code' => 404, 
                'status' => 'error', 
                'message' => 'imagen inexistente aca'
            );
        }else{
            $image_name = time().$image->getClientOriginalName();


            \Storage::disk('images')->put($image_name, \File::get($image));

            $data = array(
                'code' => 200, 
                'status' => 'success', 
                'image' => $image_name
            );
        }

        // devolver datos

        return response()->json($data, $data['code']);

    }

    public function getImage($filename){
        // comprobar si existe el fichero
        $isset = \Storage::disk('images')->exists($filename);

        if($isset){
            // conseguir la imagen
            $file = \Storage::disk('images')->get($filename);
    
            // devolver la imagen

            return new Response($file, 200);

        }else{
            // mostrar posible error
            $data = array(
                'code' => 404, 
                'status' => 'error',
                'message' => 'la imagen no existe'
            );
        }

        return response()->json($data, $data['code']);


    }

    public function getPostsByCategory($id){
        $posts = Post::where('category_id', $id)->get();

        return response()->json([
            'status' => 'success', 
            'posts' => $posts
        ], 200);
    }

    public function getPostsByUser($id){
        $posts = Post::where('user_id', $id)->get();

        return response()->json([
            'status' => 'success', 
            'posts' => $posts
        ], 200);
    }

}
