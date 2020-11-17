<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\User;

class UserController extends Controller
{
    public function pruebas(Request $request){
        return "accion de pruebas de user controller";
    }
    

    public function register(Request $request){

        //RECOGER LOS DATOS DEL USUARIO POR POST

        $json = $request->input('json', null);

        $params = json_decode($json); //objeto
        $params_array = json_decode($json, true); //array


        if(!empty($params) && !empty($params_array) ){
            //LIMPIAR DATOS ESPACIOS ADELANTE Y ATRAS

            $params_array = array_map('trim', $params_array);

            //VALIDAR DATOS

            $validate = \Validator::make($params_array, [
                'name'      => 'required|alpha',
                'surname'   => 'required|alpha',
                'email'     => 'required|email|unique:users',
                'password'  => 'required'
            ]);

            if($validate->fails()){
                $data = array(
                    'status' => 'error',
                    'code' => 404, 
                    'message' => 'El usuario no se ha creado',
                    'errors' => $validate->errors()
                );
            }else{
                //VALIDACION CORRECTA

                    //CIFRAR CONTRASEÑA

                    // $pwd = password_hash($params->password, PASSWORD_BCRYPT, ['cost' => 4]);
                    $pwd = hash('sha256', $params->password);
    
                    //COMPROBAR SI EL USUARIO YA EXISTE
                        //comprobado en el VALIDATOR
                    
                    //CREAR EL USUARIO

                    $user = new User();
                    $user->name = $params_array['name'];
                    $user->surname = $params_array['surname'];
                    $user->email = $params_array['email'];
                    $user->password = $pwd;
                    $user->role = 'ROLE_USER';

                    //GUARDAR EL USUARIO EN LA BBDD

                    $user->save();

                $data = array(
                    'status' => 'success',
                    'code' => 200, 
                    'message' => 'El usuario se ha creado correctamente',
                    'user' => $user
                );
            }
            


        }else{
            $data = array(
                'status' => 'error',
                'code' => 404, 
                'message' => 'los datos no son correctos'
            );
        }
        

        
        return response()->json($data, $data['code']);
        
        

        
    }


    public function login(Request $request){
        $jwtAuth = new \JwtAuth(); //tiene el alias configurado en app.php

        //RECIBIR EL POST
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);


        //VALIDAR DATOS RECIBIDOS

        $validate = \Validator::make($params_array, [
            'email'     => 'required|email',
            'password'  => 'required'
        ]);

        if($validate->fails()){
            $signup = array(
                'status' => 'error',
                'code' => 404, 
                'message' => 'El usuario no se ha podido identificar',
                'errors' => $validate->errors()
            );
        }else{


        //CIFRAR CONTRASEÑA
        $pwd = hash('sha256', $params->password);

        //DEVOLVER TOKEN O DATOS
        $signup = $jwtAuth->signup($params->email, $pwd);

        if(!empty($params->getToken))
            $signup = $jwtAuth->signup($params->email, $pwd, true);
        }


        return response()->json( $signup, 200);
    }


    public function update(Request $request){
        //comprobar si el usuario fue identificado
        
        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth(); 
        
        $checkToken = $jwtAuth->checkToken($token);

        //recoger datos por post

        $json = $request->input('json', null);
        $params_array = json_decode($json, true);
        
        if($checkToken && !empty($params_array)){
            //actualizar el usuario
       
            //sacar usuario identificado
            
            $user = $jwtAuth->checkToken($token, true);
            
            //validar datos

            $validate = \Validator::make($params_array,[
                'name'      => 'required|alpha',
                'surname'   => 'required|alpha',
                'email'     => 'required|email|unique:users,'.$user->sub
            ]);

            //quitar los campos que no quiero actualizar 

            unset($params_array['id']);
            unset($params_array['rol']);
            unset($params_array['password']);
            unset($params_array['created_at']);
            unset($params_array['remember_token']);

            //actualizar usuario en bbdd

            $user_update = User::where('id', $user->sub)->update($params_array);

            //devolver array con resultados

            $data = array(
                'code' => 200,
                'status' => 'success',
                'usuario' => $user_update,
                'changes' => $params_array
            );

        }else{
            //devolver msj de error
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'usuario no esta identificado correctamente'
            );
        }


        return response()->json($data, $data['code']);
    }


    public function upload(Request $request){
        
        //recoger datos de la peticion

        $image = $request->file('file0');

        //validacion de imagenes

        $validate = \Validator::make($request->all(), [
            'file0' => 'required|image|mimes:jpg,jpeg,png,png,gif'
        ]);

        //subir imagen

        if(!$image || $validate->fails()){
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'error al subir una imagen'
            );

        }else{
            
            $image_name = time().$image->getClientOriginalName();
            //configurado desde config/fylesystems copie y pegue public
            \Storage::disk('users')->put($image_name, \File::get($image));

            $data = array(
                'code' => 200,
                'status' => 'success',
                'image' => $image_name
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
                'message' => 'el archivo no existe'
            );

            return response()->json($data, $data['code']);
        }

    }

    public function detail($id){
        $user = User::find($id);
        if(is_object($user)){
            $data = array(
                'code' => 200, 
                'status' => 'Success', 
                'user' => $user
            );
        }else{
            $data = array(
                'code' => 404, 
                'status' => 'error', 
                'message' => 'user no existe'
            );
        }
        return response()->json($data, $data['code']);
    }

}
