<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Comment;
use App\Helpers\jwtAuth;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Response;
use App\Models\replies;

use Illuminate\Routing\Controller as BaseController;
use App\Http\Middleware\ApiAuthMiddleware;

class CommentsController extends BaseController
{

    public function __construct()
    {
        $this->middleware(ApiAuthMiddleware::class, ['except' => [
            'index',
            'show',
            'getVideos',
            'getVideosByCourse',
            'getImage'
        ]]);
    }
    /**
     * Display a listing of the resource.
     */
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
        $json = $request->input("json", null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            $user = $this->getIdentity($request);

            $validate = Validator::make($params_array, [
                'video_id' => 'required',
                'comment' => 'required'

            ]);

            if (!isset($params->image)) {
                $params->image = null;
            }

            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'The video was not saved',
                );
            } else {
                $comment = new Comment();
                $comment->user_id = $user->sub;
                $comment->video_id = $params->video_id;
                $comment->comment = $params->comment;
                $comment->image = $params->image;
                $comment->save();

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => $comment
                );
            }
        } else {

            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'The data was not sent correctly'
            );
        }

        return response()->json($data, $data['code']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //Obtener los comentarios de un video en especifico 
        $comments = Comment::all()->where('video_id', $id);

        $reponses = replies::all();

        $users = User::all();

        $usersArray = [];

        $commentsArray = [];


        $count = 0;
        $quantity_of_responses = 0;

        foreach ($comments as $comment) {
            $count++; // Increment comment count
            $quantity_of_responses = 0; // Reset for each comment

            // Find the user for this comment
            foreach ($users as $user) {
                if (!in_array($user->id, array_column($usersArray, 'id'))) { //verifica si el id del usuario no ha sido añadido antes
                    array_push($usersArray, $user); // Add the matching user
                }
            }

            // Add the comment to the comments array
            array_push($commentsArray, $comment);

            // Count the responses for this comment
            foreach ($reponses as $response) {
                if ($comment->id == $response->comment_id) {
                    $quantity_of_responses++;
                }
            }

            //mapa

            $responsesForComment[$comment->id] = [
                'quantityOfReplies' => $quantity_of_responses
            ];
        }


        if ($count > 0) { //Si la cantidad de comentarios es mayor a 0

            $data = array(
                'status' => 'success',
                'code' => 200,
                'index' => 'full',
                'comments' => $comments,
                'count' => $count,
                'repliesPerComment' => $responsesForComment,
                'users' => $usersArray
            );
        } else {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'index' => 'empty',
                'comments' => $comments,
                'message' => 'No comments available'
            );
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
        //recoger el json
        $json = $request->input("json", null);

        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            $validate = Validator::make($params_array, [
                'comment' => 'required',

            ]);

            if ($validate->fails()) {

                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'The comment was not saved',
                );
            } else {
                unset($params_array['id']);
                unset($params_array['user_id']);
                unset($params_array['created_at']);
                unset($params_array['user']);

                $user = $this->getIdentity($request);


                //buscar el registro a actualizar

                $comment = Comment::where('id', $id)->where('user_id', $user->sub)->first();

                if (!empty($comment) && is_object($comment)) {
                    $image_path = $request->file('image_path');

                    if ($image_path) {
                        $image_path_name = time() . $image_path->getClientOriginalName();
                        Storage::disk('comment')->put($image_path_name, File::get($image_path));

                        $comment->image = $image_path_name;
                    }


                    //actualizar el registro en concreto

                    $comment->update($params_array);

                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'user' => $user,
                        'changes' => $params_array
                    );
                } else {

                    $data = array(
                        'status' => 'error',
                        'code' => 404,
                        'messgae' => 'Comment does not exist'
                    );
                }
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'messgae' => 'Data sent incorrectly'
            );
        }


        return response()->json($data, $data['code']);
    }


    public function upload(Request $request)
    {
        $image = $request->file('file'); //La libreria de angular necesita que el campo se llame de esta forma

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

            $userId = $request->input('user_id');  // Obtiene el 'user_id' de los parámetros de la solicitud
            $videoId = $request->input('video_id'); // Obtiene el 'video_id' de los parámetros de la solicitud
            $commentId = $request->input('comment_id');



            if ($userId && $videoId) {
                $image_name = $userId . '_' . $videoId . '_' . $image->getClientOriginalName(); //Obtiene el nombre de la imagen y lo une con el tiempo actual 

                $existingImageName = Storage::disk('comment')->files(); // Obtiene todos los archivos

                $commentImageName = Comment::where('user_id', $userId)->where('video_id', $videoId);


                if (!empty($commentId)) { // Verificamos que $commentId no sea null
                    $commentImageName->where('id', $commentId);
                }

                $commentImageName = $commentImageName->first();


                foreach ($existingImageName as $existingImage) {
                    // se verifica si el archivo comienza con $userID_$videoId
                    if ($existingImage == $image_name) {
                        // Si se encuentra, elimina el archivo existente
                        Storage::disk('comment')->delete($existingImage);
                    }
                }


                Storage::disk('comment')->put($image_name, File::get($image)); //indicamos el nombre y el archivo que se va a guardar


                $commentImageName->image = $image_name;
                $commentImageName->save();

                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'image_name' => $image_name

                );
            } else {

                $data = array(
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No user_id or video_id were provided'

                );
            }
        }

        return response($data, $data['code'])->header('Content-type', 'text/plain');
    }


    public function getImage($filename)
    {
        $isset = Storage::disk('comment')->exists($filename);
        if ($isset) {
            $file = Storage::disk('comment')->get($filename);

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
    public function destroy(string $id)
    {
        //Eliminar commentario y sus respuestas

        $comment = Comment::where('id', $id)->first();
        $replies = replies::where('comment_id', $id);

        if (!empty($comment)) {
            $comment->responses()->delete(); //elimina respuestas del comentario a travez del metodo responses() en el modelo 
            $imageToDelete = $comment->image;

            if (!empty($imageToDelete)) {
                Storage::disk('comment')->delete($imageToDelete);
            }
            $comment->delete(); //elimina el comentario 


            $data = array(
                'status' => 'success',
                'code' => 200,
                'comment' => $comment

            );
        } else {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'comment' => $comment,
                'replies' => $replies,
                'message' => 'The comment does not exist'

            );
        }

        return response()->json($data, $data['code']);
    }


    public function getIdentity(Request $request)
    {
        //Obtiene los datos del usuario indentificado 
        $jwtAuth = new jwtAuth();
        $token = $request->header('Authorization', null);
        $user = $jwtAuth->checkToken($token, true); //Obtenemos el token decodificado 

        return $user;
    }
}
