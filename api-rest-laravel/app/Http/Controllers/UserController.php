<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\User;

class UserController extends Controller
{
    public function pruebas(Request $request) {
        return "Acción de pruebas de USER-CONTROLLER";
    }

    public function register(Request $request) {

        // Recoger los datos del usuario por post
        $json = $request -> input('json', null);

        // Decodificar json
        $params = json_decode($json); // Devuelve un objeto
        $params_array = json_decode($json,true); // Devuelve un array

        if (!empty($params) && !empty($params_array)) {
            // Limpiar datos
            $params_array = array_map('trim', $params_array);

            // Validar datos
            $validate = \Validator::make($params_array, [
                'name'      => 'required|alpha',
                'surname'   => 'required|alpha',
                'email'     => 'required|email|unique:users', //Comprobar si el usuario existe ya (duplicado)
                'password'  => 'required'
            ]);

            if ($validate -> fails()) {
                // Validacion fallida
                $data = array(
                    'status' => 'error',
                    'code'   => 404,
                    'message'=> 'El usuario no se ha creado',
                    'errors' => $validate -> errors()
                );
            }else {
                // Validacion pasada correctamente

                // Cifrar la contraseña                
                $pwd = hash('sha256', $params -> password);

                //Crear el usuario
                $user = new User();
                $user -> name = $params_array['name'];
                $user -> surname = $params_array['surname'];
                $user -> email = $params_array['email'];
                $user -> password = $pwd;
                $user -> role = 'ROLE_USER';

                // Guardar el usuario
                $user -> save();

                $data = array(
                    'status' => 'success',
                    'code'   => 200,
                    'message'=> 'El usuario se ha creado correctamente',
                    'user'   => $user
                );
            }
        } else {
            $data = array(
                'status' => 'error',
                'code'   => 404,
                'message'=> 'Los datos enviados no son correctos'
            );
        }
        return response() -> json($data, $data['code']);
    }

    public function login(Request $request) {
        $jwtAuth = new \JwtAuth();

        // Recibir los datos por POST
        $json = $request -> input('json',null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        // Validar los datos
        $validate = \Validator::make($params_array, [
            'email'     => 'required|email',
            'password'  => 'required'
        ]);

        if ($validate -> fails()) {
            // La validacion ha fallado
            $signUp = array(
                'status'    => 'error',
                'code'      => 400,
                'message'   => 'El usuario no se ha podido identificar correctamente',
                'errors'    => $validate -> errors() 
            );
        } else {
            // Cifrar contraseña 
            $pwd = hash('sha256', $params -> password);

            // Devolver token o datos
            $signUp = $jwtAuth -> signUp($params -> email, $pwd);

            if (!empty($params -> gettoken)) {
                $signUp = $jwtAuth -> signUp($params -> email, $pwd, true);
            }
        }

        return response() -> json($signUp, 200);
    }

    public function update(Request $request) {
        // Comprobar si el usuario está identificado
        $token = $request -> header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth -> checkToken($token);

        // Recoger los datos post
        $json = $request -> input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if ($checkToken && !empty($params_array)) { 
            // Obtener usuario identificado
            $user = $jwtAuth -> checkToken($token, true);

            // Validar datos
            $validate = \Validator::make($params_array, [
                'name'      => 'required|alpha',
                'surname'   => 'required|alpha',
                'email'     => 'required|email|unique:users,'.$user -> sub
            ]);

            // Suprimir los campos que no queremos actualizar
            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['password']);
            unset($params_array['created_at']);
            unset($params_array['remember_token']);

            // Actualizar el usuario en la DB
            $user_update = User::where('id', $user -> sub) -> update($params_array);

            //Devolver array con resultado
            $data = array(
                'status'    => 'success',
                'code'      => 200,
                'user'      => $user,
                'changes'   => $params_array
            );

        } else {
            $data = array(
                'status'    => 'error',
                'code'      => 400,
                'message'   => 'El usuario no esta identificado.'
            );
        }
        return response() -> json($data, $data['code']);
    }

    public function upload(Request $request) {
        // Recoger datos de la peticion
        $image = $request -> file('file0');

        // Validacion de imagen
        $validate = \Validator::make($request -> all(), [
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        // Guardar imagen
        if (!$image || $validate -> fails()) {
            $data = array(
                'status'    => 'error',
                'code'      => 400,
                'message'   => 'Error al subir imagen.'
            );
        } else {
            $image_name = time().$image -> getClientOriginalName();
            \Storage::disk('users') -> put($image_name, \File::get($image));

            $data = array(
                'status'    => 'success',
                'code'      => 200,
                'image'     => $image_name
            );            
        }        
        return response() -> json($data, $data['code']);
    }

    public function getImage($filename) {
        $isset = \Storage::disk('users') -> exists($filename);
        
        if ($isset) {
            $file = \Storage::disk('users') -> get($filename);
            return new Response($file, 200);
        } else {
            $data = array(
                'status'    => 'error',
                'code'      =>  404,
                'message'   =>  'La imagen no existe.'
            );
            return response() -> json($data, $data['code']);
        }
    }

    public function detail($id) {
        $user = User::find($id);

        if (is_object($user)) {
            $data = array(
                'status'    =>  'success',
                'code'      =>  '200',
                'user'      =>  $user
            );
        } else {
            $data = array(
                'status'    =>  'error',
                'code'      =>  404,
                'message'   =>  'Usuario no existe.'
            );
        }
        return response() -> json($data, $data['code']);
    }
}
