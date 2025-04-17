<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Video;
use App\Http\Middleware\ApiAuthMiddleware;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Validator;
use App\Helpers\jwtAuth;
use App\Models\CheckBox;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class VideoController extends BaseController
{
    public function __construct()
    {
        $this->middleware(ApiAuthMiddleware::class, ['except' => [
            'index',
            'show',
            'getVideos',
            'getVideosByCourse'
        ]]);
    }


    public function index()
    {
        /*Obtener los videos */

        $videos = Video::paginate(15)->load('course'); //Videos que se relacionen con course(el modelo)
        $paginatedVideos = Video::paginate(15);

        return response()->json([


            'code' => 200,
            'status' => 'success',
            'video' => $videos,
            'PaginatedVideos' => $paginatedVideos

        ]);
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

        // Log::info($request);
        $json = $request->input("json", null);
        $params_array = json_decode($json, true); // Solo se necesita  esta línea.


        if (!empty($params_array)) {

            $user = $this->getIdentity($request);

            $validate = Validator::make($params_array, [
                'user_id' => 'required',
                'course_id' => 'required',
                'title' => 'required',
                'content' => 'required',
                'url' => 'required',
                'section' => 'required',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'The video has not been saved'
                ], 404);
            }

            // Asignar valores por defecto si no están presentes
            $params_array['file'] = $params_array['file'] ?? null;
            $params_array['title_accordion'] = $params_array['title_accordion'] ?? null;

            $video = new Video();
            $video->user_id = $user->sub;
            $video->course_id = $params_array['course_id'];
            $video->title = $params_array['title'];
            $video->content = $params_array['content'];
            $video->url = $params_array['url'];
            $video->file = $params_array['file'];
            $video->section = $params_array['section'];
            $video->title_accordion = $params_array['title_accordion'];

            // Guardar el video
            $video->save();

            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message' => 'Video has been registered',
                'video' => $video
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'code' => 404,
            'message' => 'Data has not been sent correctly',
            'params_array' => $params_array

        ], 404);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

        $video = Video::find($id)->load('user');
        if (is_object($video)) {
            $data = [
                'code' => 200,
                'status' => 'success',
                'video' => $video
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'The video does not exist'
            ];
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
        $params_array = json_decode($json, true); // Solo necesitas esta línea.


        if (!empty($params_array)) {
            $validate = Validator::make($params_array, [
                'course_id' => 'required',
                'title' => 'required',
                'content' => 'required',
                'url' => 'required'
            ]);

            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'Video has not been uploaded'
                );
            } else {
                unset($params_array['id']);
                unset($params_array['user_id']);
                unset($params_array['created_at']);
                unset($params_array['user']);

                //obtener usuario identificado

                $user = $this->getIdentity($request);

                $video = Video::where('id', $id)
                    ->where('user_id', $user->sub)->first();


                if (!empty($video) && is_object($video)) {

                    //actualizar el registro en concreto 
                    $video->update($params_array);

                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'video' => $video,
                        'user' => $user,
                        'changes' => $params_array
                    );
                } else {
                    $data = array(
                        'status' => 'error',
                        'code' => 404,
                        'message' => 'Data was sent incorrectly'
                    );
                }
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'The video does not exist'

            );
        }

        return response()->json($data, $data['code']);
    }


    public function upload(Request $request)
    {

        $image = $request->file('file'); //el campo del formulario 
        //validación de la imagen

        $validate = Validator::make($request->all(), [
            'file' => 'required|file|mimes:txt,docx,pdf,rar|max:5120'
        ]);

        if (!$image || $validate->fails()) {
            return response()->json([
                'code' => 400,
                'status' => 'error',
                'message' => 'Invalid file type or size'
            ], 400);
        } else {

            $image_name = time() . $image->getClientOriginalName(); //nombre unico
            Storage::disk('docs')->put($image_name, File::get($image));
            $data = array(

                'code' => 200,
                'status' => 'success',
                'message' => $image_name
            );
        }

        return response($data, $data['code'])->header('Content-type', 'text/plain');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, Request $request)
    {

        //El video solo puede ser eliminado si en user_id del video concuerda con el user_id del usario identificado

        Log::info($request);

        //usuario identificado 
        $user = $this->getIdentity($request);
        // Log::info($user);

        $video = Video::where('id', $id)->where('user_id', $user->sub)->first();

        if (!empty($video)) {
            $video->delete();

            $data = array(
                'status' => 'success',
                'code' => 200,
                'message' => 'The video has been deleted successfully',
                'video' => $video

            );
        } else {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'The video does not exist',
                'id' => $id


            );
        }

        return response()->json($data, $data['code']);
    }


    public function getVideos()
    {
        $videos = Video::get()->load('course');
        return response()->json([
            'code' => 200,
            'status' => 'success',
            'videos' => $videos
        ]);
    }

    public function getVideosByCourse($id)
    //Obtener videos por curso_id
    {
        $videos = Video::where('course_id', $id)->get();

        if (sizeof($videos) > 0) {
            $re = sizeof($videos);


            $resultado = 100 / $re;


            return response()->json([
                'status' => 'success',
                'videos' => $videos,
                'percentage' => $resultado    //Porcentage de videos vistos en el curso 
            ], 200);
        } else if (sizeof($videos)) {
            return response()->json([
                'status' => 'success',
                'videos' => 0,
                'percentage' => 0    //Porcentage de videos vistos en el curso 
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'videos' => null,
                'percentage' => 0    //Porcentage de videos vistos en el curso 
            ], 404);
        }
    }

    public function getVideosByCourse2(Request $request, $id)
    {
        $user = $this->getIdentity($request);

        $videos = Video::where('course_id', $id)->get();

        $checkboxes = CheckBox::where('course_id', $id)
            ->where('user_id', $user->sub)
            ->get();

        foreach ($videos as $video) { ///bucle de todos los videos del curso
            foreach ($checkboxes as $checkbox) { //Bucle de las vistas del curso wue hizo el usuario

                if ($video->id == $checkbox->video_id) { //significa que el usuario vio el video que se esta iterando
                    $video->checkbox = 'activado';
                    $video->checkbox_id = $checkbox->id;
                }
            }
        }

        if (sizeof($videos) > 0) { //si tenemos los videos 
            $re = sizeof($videos);

            $resultado = 100 / $re;


            return response()->json([
                'status' => 'success',
                'videos' => $videos,
                'percentage' => $resultado    //Porcentage de videos vistos en el curso 
            ], 200);
        } else if (sizeof($videos)) {
            return response()->json([
                'status' => 'success',
                'videos' => 0,
                'percentage' => 0    //Porcentage de videos vistos en el curso 
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'videos' => null,
                'percentage' => 0    //Porcentage de videos vistos en el curso 
            ], 404);
        }
    }


    public function updatetitle($id, Request $request)
    {

        $user = $this->getIdentity($request);


        if (!empty($id)) {

            $video = Video::where('id', $id)
                ->where('user_id', $user->sub)
                ->first();

            if (!empty($video) && is_object($video)) {
                $video->title_accordion = null;


                $video->update();

                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'video' => $video
                );
            } else {
                $data = array(
                    'code' => 404,
                    'status' => 'error',
                    'video' => 'Data sent incorrectly'
                );
            }
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'video' => 'The video doesn not exist'
            );
        }

        response()->json($data, $data['code']);
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
