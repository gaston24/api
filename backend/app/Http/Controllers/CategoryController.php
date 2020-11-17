<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Category;

class CategoryController extends Controller
{


    public function __construct(){
        $this->middleware('api.auth', ['except' => ['index', 'show']]);
    }

    public function index(Request $request){
        $categories = Category::all();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'categories' => $categories
        ]);
    }

    public function show($id){
        $category = Category::find($id);

        if(is_object($category)){
            $data = [
                'code' => 200,
                'status' => 'success',
                'category' => $category
            ];
        }else{
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'la categoria no existe'
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function store(Request $request){
        //recoger los datos por post
        $json = $request->input('json', null);

        $params_array = json_decode($json, true);

        //validar los datos

        if(!empty($params_array)){

        

            $validate = \Validator::make($params_array, [
                'name' => 'required'
            ]);

            //guardar la categoria

            if($validate->fails()){
                $data = [
                    'code' => 400,
                    'status' => 'error', 
                    'message' => 'no se ha guardado la categoria'
                ];
            }else{
                $category = new Category();
                $category->name = $params_array['name'];
                $category->save();

                $data = [
                    'code' => 200,
                    'status' => 'succes', 
                    'category' => $category
                ];
            }

            

    }else{
        $data = [
            'code' => 400,
            'status' => 'error', 
            'message' => 'no has enviado ninguna categoria'
        ];

    }

    //devolver resultado

    return response()->json($data, $data['code']);

    }


    public function update($id, Request $request){
        //recoger datos por put

        $json = $request->input('json', null);
        $params_array = json_decode($json, true);
        if(!empty($params_array)){
            //validar los datos

            $validate = \Validator::make($params_array, [
                'name' => 'required'
            ]);
            
            if($validate){

                //quitar lo que no quiero actualizar
                unset($params_array['id']);
                unset($params_array['created_at']);
                
                //actualizar el registro
                $category = Category::where('id', $id)->update($params_array);
                
                $data = [
                    'code' => 200,
                    'status' => 'success', 
                    'category' => $params_array 
                ];
            }
                
            
            
        }else{
            $data = [
                'code' => 400,
                'status' => 'error', 
                'message' => 'no has enviado ninguna categoria para actualizar'
            ];
        }
        
        //devolver los datos
        return response()->json($data, $data['code']); 
    }

}
