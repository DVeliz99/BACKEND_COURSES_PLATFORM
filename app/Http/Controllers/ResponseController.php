<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Http\Middleware\ApiAuthMiddleware;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Validator;
use App\Helpers\jwtAuth;
use App\Models\CheckBox;
use App\Models\replies;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Response;

class ResponseController extends BaseController
{

    public function __construct()
    {
        $this->middleware(ApiAuthMiddleware::class, ['except' => [
            'show',
            'getImage'
        ]]);
    }

    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //Recoger los datos por video 

        $json = $request->input("json", null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            $user = $this->getIdentity($request);

            $validate = Validator::make($params_array, [
                'comment_id' => 'required',
                'response' => 'required'

            ]);



            if (!isset($params->image)) {
                $params->image = null;
            }


            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'the reply has not been saved'

                );
            } else {

                $reply = new replies();
                $reply->user_id = $user->sub; //obtenemos el user_id del usuario identificado 
                $reply->comment_id = $params->comment_id; //coment__id
                $reply->response = $params->response;
                $reply->image = isset($params->image) ? $params->image : NULL;
                $reply->save();


                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'Reply' => $reply
                );
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'the data was sent incorrectly'
            );
        }

        return response()->json($data, $data['code']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)



    {
        $comment = Comment::where('id', $id)->first();
        $users = User::all();

        $usersArray = [];
        $repliesArray = [];

        if (!(empty($comment))) {
            $replies = replies::where('comment_id', $id)->get();

            foreach ($replies as $reply) {
                array_push($repliesArray, $reply); //Se guarda cada respuesta asociada con el comentario

                // Find the user for this comment
                foreach ($users as $user) {

                    if ($reply->user_id == $user->id) {
                        if (!in_array($user->id, array_column($usersArray, 'id'))) { //verifica si el id del usuario no ha sido añadido antes
                            array_push($usersArray, $user); // Add the matching user
                        }
                    }
                }
            }

            if (count($repliesArray) > 0 && count($usersArray) > 0) {

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'comment' => $comment,
                    'replies' => $repliesArray, //respuestas de un solo comentario 
                    'countRepplies' => count($repliesArray), // numero de respuestas en el comentario
                    'users' => $usersArray,
                    'userComment' => $comment->user
                );
            } else {
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'isEmpty' => 'yes',
                    'comment' => $comment,
                    'count' => $comment->responses->count(),
                    'user' => $comment->user_id,
                    'created_comment' => $comment->created_at,
                    'message' => 'There is not any reply',
                );
            }
        }





        return response()->json($data, $data['code']);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $json = $request->input("json", null);
        $params_array = json_decode($json, true);

        // 🔹 Inicializar `$data` para evitar errores de variable indefinida
        $data = [
            'status' => 'error',
            'code' => 500,
            'message' => 'An unexpected error occurred'
        ];

        if (!empty($params_array)) {
            $user = $this->getIdentity($request);

            $validate = Validator::make($params_array, [
                'id' => 'required',
                'response' => 'required'
            ]);

            if ($validate->fails()) {
                $data = [
                    'status' => 'error',
                    'code' => 400, // Código 400 es más adecuado para errores de validación
                    'message' => 'The reply has not been saved'
                ];
            } else {
                unset($params_array['id'], $params_array['user_id'], $params_array['created_at'], $params_array['user']);

                $reply = replies::where('id', $id)
                    ->where('user_id', $user->sub)
                    ->first();

                if (!empty($reply) && is_object($reply)) {
                    $reply->update($params_array);
                    $data = [
                        'status' => 'success',
                        'code' => 200,
                        'reply' => $reply,
                        'user' => $user,
                        'changes' => $params_array
                    ];
                } else {
                    $data = [
                        'status' => 'error',
                        'code' => 404,
                        'message' => 'Data sent incorrectly'
                    ];
                }
            }
        } else {
            $data = [
                'status' => 'error',
                'code' => 404,
                'message' => 'The reply does not exist'
            ];
        }

        return response()->json($data, $data['code']);
    }



    public function upload(Request $request)

    {


        $image = $request->file('file');
        $replyId = $request->input('id');


        //Validar datos 
        $validate = Validator::make($request->all(), [
            'file' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        if (!$image || $validate->fails()) {
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'Error on uploading image'

            );
        } else {
            $image_name = $replyId . '_' . $image->getClientOriginalName(); //Obtiene el nombre de la imagen y lo une con el tiempo actual 

            Storage::disk('replies')->put($image_name, File::get($image)); //indicamos el nombre y el archivo que se va a guardar


            $replyToEdit = replies::where('id', $replyId)->first();

            $replyToEdit->image = $image_name;
            $replyToEdit->save();



            $data = array(
                'code' => 200,
                'status' => 'success',
                'message' => $image_name

            );
        }

        return response($data, $data['code'])->header('Content-type', 'text/plain');
    }

    public function getImage($filename)
    {
        $isset = Storage::disk('replies')->exists($filename);
        if ($isset) {
            $file = Storage::disk('replies')->get($filename);

            return new Response($file, 200);
        } else {

            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'Image does not exist'

            );


            return response()->json($data, $data['code']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, Request $response)
    {
        //Eliminar commentario y sus respuestas

        $user = $this->getIdentity($response);

        $reply = replies::where('id', $id)->where('user_id', $user->sub)->first();

        if (!empty($reply)) {
            $reply->delete(); //elimina respuestas del comentario a travez del metodo responses() en el modelo 


            $data = array(
                'status' => 'success',
                'code' => 200,
                'comment' => $reply

            );
        } else {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'replies' => $reply,
                'message' => 'The reply does not exist'

            );
        }

        return response()->json($data, $data['code']);
    }


    public function getIdentity(Request $request)
    {
        $jwtAuth = new jwtAuth();
        $token = $request->header('Authorization', null);

        // Verifica si el token es válido
        $user = $jwtAuth->checkToken($token, true);

        if (!$user) {
            // Si el token es inválido o no existe, devolver un error
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }

        return $user;
    }
}
