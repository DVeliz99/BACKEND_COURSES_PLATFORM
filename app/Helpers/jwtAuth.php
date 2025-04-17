<?php

namespace App\Helpers;

use Firebase\JWT\JWT;  //Libreria para decodificar el token
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Auth; //para la autenticacion
use App\Http\Controllers\Controller;



class jwtAuth
{
    public $key;

    public function __construct()
    {

        $this->key = 'this-is-a-super-secret-key'; //Clave secreta



    }

    public function signup($email, $password, $getToken = null)
    { //$getToken =>para devolver el token o el token decodificado

        // Definir $decode antes de su uso para evitar el error
        $decode = null;

        //comprobar si existe el usuario con sus credenciales

        if (Auth::attempt(['email' => $email, 'password' => $password])) {
            $user = Auth::user(); //obtener al usuario autenticado

            $signup = false;
            if (is_object($user)) {
                $signup = true;
            }

            if ($signup) {
                $token = array(
                    'sub' => $user->id, //sub dentro de jwt hace referencia al id del usuario 
                    'email' => $user->email,
                    'name' => $user->name,
                    'surname' => $user->surname,
                    'role' => $user->role,
                    'description' => $user->description,
                    'image' => $user->image,
                    'iat' => time(), // iat en jwt hace referencia a la fecha actual
                    'exp' => time() + (7 * 24 * 60 * 60) //tiempo actual + una semana 7 dias , 24 horas , 60 minutos , 60 segundos
                );

                //Crear el token
                $jwt = JWT::encode($token, $this->key, 'HS256'); // HS256 =>tipo de algoritmo de codificacion

                $decode = JWT::decode($jwt, new \Firebase\JWT\Key($this->key, 'HS256')); //sintaxis valida


                //Devolver el token

                if (is_null($getToken)) {

                    $data = $jwt;
                } else {
                    $data = $decode;
                }
            } else {
                $data = array(
                    'message' => 'Error',
                    'user' => 'Login failed'
                );
            }
        } else {
            $data = array(
                'status' => 'error',
                'message' => 'Incorrect login'
            );
        }


        return $data;
    }


    //Verifica si existe un token, devuelve la decodificación (datos del usuario)
    public function checkToken($jwt, $identity = false)
    {
        $auth = false;
        try {
            $jwt = str_replace('"', '', $jwt); //limpiar el tocken
            $decode = JWT::decode($jwt, new \Firebase\JWT\Key($this->key, 'HS256')); //sintaxis valida
        } catch (\UnexpectedValueException $e) {
            $auth = false; //Por si llega algun fallo en el dominio
        } catch (\DomainException $e) {
            $auth = false; //Por si llega algun fallo en el dominio
        }

        if (isset($decode) && !empty($decode) && is_object($decode) && isset($decode->sub)) {
            $auth = true;
        } else {
            $auth = false;
        }

        if ($identity != false) {
            return $decode; //el objeto decodifico 
        } else {
            return $auth;
        }
    }
}
