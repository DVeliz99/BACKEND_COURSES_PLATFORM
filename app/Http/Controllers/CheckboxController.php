<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\CheckBox;
use App\Helpers\jwtAuth;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;
use App\Http\Middleware\ApiAuthMiddleware;
use Illuminate\Support\Facades\Log;

class CheckboxController extends BaseController
{


    public function __construct()
    {
        $this->middleware(ApiAuthMiddleware::class, ['except' => []]);
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


        $user = $this->getIdentity($request);



        //Obtener el checkbox
        $checkbox = CheckBox::where('user_id', $user->sub)->where('course_id', $params_array['course_id'])->where('video_id', $params_array['video_id'])->first();

        if ($checkbox) {
            $checkbox->delete();
        }

        if (!empty($params_array)) {
            $user = $this->getIdentity($request);

            $validate = Validator::make($params_array, [

                'course_id' => 'required',
                'video_id' => 'required',
                'checkbox' => 'required'

            ]);

            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'The video was not saved',
                );
            } else {
                $checkbox = new CheckBox();
                $checkbox->user_id = $user->sub;
                $checkbox->course_id = $params->course_id;
                $checkbox->video_id = $params->video_id;
                $checkbox->checkbox = $params->checkbox;
                $checkbox->save();


                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'checkbox' => $checkbox
                );
            }
        } else {

            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'none checkbox was sent'
            );
        }

        return response()->json($data, $data['code']);
    }

    /**
     * Display the specified resource.
     */
    public function show($course_id, Request $request)
    {

        $user = $this->getIdentity($request);

        //Obtener los comentarios de un video en especifico 
        $checkboxes = CheckBox::where('user_id', $user->sub)->where('course_id', $course_id)->get();


        $data = array(
            'status' => 'success',
            'code' => 200,
            'checkboxes' => $checkboxes
        );

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


        $data = array();



        $user = $this->getIdentity($request);


        // Log::info($params_array);

        //Obtener el checkbox
        $checkbox = CheckBox::where('user_id', $user->sub)->where('course_id', $params_array['course_id'])->where('video_id', $params_array['video_id'])->first();

        // Log::info($checkbox);





        if (!empty($checkbox)) {

            $checkbox->checkbox = $params_array['checkbox'];
            $checkbox->delete();

            $data = array(
                'status' => 'success',
                'code' => 200,
                'message' => 'The checkbox was updated',
                'checkbox' => $checkbox
            );
        } else {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'The checkbox does not exist',
                'checkbox' => $checkbox
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
