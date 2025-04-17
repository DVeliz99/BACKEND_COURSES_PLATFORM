<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Accordion;
use App\Models\Course;
use App\Helpers\jwtAuth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Http\Middleware\ApiAuthMiddleware;
use App\Models\Video;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;


class AccordionController extends BaseController
{


    public function __construct()
    {
        $this->middleware(ApiAuthMiddleware::class, ['except' => [
            'index',


        ]]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index() {}

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
        //Se recibiran datos desde el frontend
        $json = $request->input("json", null);
        // $params_array = $request->all();

        $params_array = json_decode($json, true); //array para uso de php

        Log::info('params_array', $params_array);

        if (!empty($params_array)) {
            $validate = Validator::make($params_array, [
                'course_id' => 'required',
            ]);

            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'The section has not been saved'

                );
            } else {
                $accordion = new Accordion();
                $accordion->course_id = $params_array['course_id'];
                if (!empty($params_array['section_name'])) {
                    $accordion->section_name = $params_array['section_name'];
                } else {
                    $accordion->section_name = null;
                }




                $course = Course::where('id', $params_array['course_id'])->first();

                $course->accordion =  $course->accordion + 1;

                $accordion->num_of_section =  $course->accordion;
                $accordion->save();
                $course->save();


                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'accordionData' => $params_array,
                    'course' => $course
                );
            }
        } else {
            $data = array(
                'status' => 'Error',
                'code' => 404,
                'message' => 'Data has not been received'

            );
        }

        return response()->json($data, $data['code']);
    }

    /**
     * Display the specified resource.
     */

    //$id es el course_id , $request es el token
    public function show(string $id, Request $request)
    {
        $course = Course::find($id)->first(); //El curso con el id en especifico
        //Obtener un usuario identificado
        // $user = $this->getIdentity($request);

        if (isset($course)) {
            $sections = Accordion::where('course_id', $id)
                ->get();

            if (is_object($sections)) {



                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'accordionData' => $sections
                );
            } else {
                $data = array(
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Sections on the course not available'

                );
            }
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'this course does not exist'

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
        //Obtener datos por POST
        $json = $request->input("json", null);

        $params_array = json_decode($json, true); //me saca un array para uso de php


        if (!empty($params_array)) {
            //validar datos

            $validate = Validator::make($params_array, [
                // 'id' => 'required',
                'course_id' => 'required',
                'section_name' => 'required',
                'num_of_section' => 'required'

            ]);

            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'The section has not been updated'

                );
            } else {


                unset($params_array['id']);
                unset($params_array['created_at']);

                $accordion = Accordion::where('id', $id)->first();


                if (!empty($accordion) && is_object($accordion)) {
                    $accordion->update($params_array);

                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'accordion' => $accordion,
                        'changes' => $params_array

                    );
                } else {
                    $data = array(
                        'status' => 'Error',
                        'code' => 404,
                        'message' => 'Accordion was not found!'

                    );
                }
            }
        } else {

            $data = array(
                'status' => 'Error',
                'code' => 404,
                'message' => 'Data was not received correctly!'

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


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, Request $request)
    {
        //

        $section = Accordion::where('id', $id)->get()->first();

        $user = $this->getIdentity($request);
        $userId = $user->sub;
        if (isset($section)) {

            $courseId = $section->course_id;
            $num_of_section = $section->num_of_section;

            $videosToDelete = Video::where('course_id', $courseId)
                ->where('section', $num_of_section)
                ->where('user_id', $userId)->get();

            $course = Course::where('id', $courseId)->first();

            if ($courseId) {
                if ($videosToDelete) {
                    foreach ($videosToDelete as $video) {
                        $video->delete();
                    }
                }

                $pattern = storage_path('app/accordion/' . $courseId . '_' . $num_of_section . '*');

                // Buscar los archivos que coinciden con el patrón
                $files = glob($pattern);

                if (!empty($files)) {
                    // Si se encuentran archivos, eliminarlos
                    foreach ($files as $file) {
                        // Eliminar el archivo
                        if (file_exists($file)) {
                            unlink($file);  // Eliminar el archivo
                        }
                    }
                }

                $section->delete();

                $course->accordion = $course->accordion - 1;
                $course->save();

                $data = array(

                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Data has been deleted',
                    'message' => 'Archivo(s) eliminado(s) con éxito'
                );
            } else {

                $data = array(

                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Data has not been deleted'
                );
            }
        } else {
            $data = array(

                'code' => 404,
                'status' => 'error',
                'message' => 'Data has not been found'
            );
        }

        return response()->json($data, $data['code']);
    }


    public function upload(Request $request)
    {

        Log::info('Datos recibidos:', $request->all());

        $file = $request->file('file'); //el campo del formulario 


        // Usar input() para acceder a los parámetros enviados
        $course_id = $request->input('course_id', 'no_course_id');
        $section = $request->input('section', 'no_section');

        // Log::info("course_id: " . $course_id);
        // Log::info("section: " . $section);



        $validate = Validator::make($request->all(), [
            'file' => 'required|file|mimes:txt,docx,pdf,rar|max:5120'
        ]);

        if (!$file || $validate->fails()) {
            $data = array(

                'code' => 400,
                'status' => 'error',
                'message' => 'Error on format'
            );
        } else {

            $file_name = $course_id . '_' . $section . '_' . $file->getClientOriginalName();

            $pattern = storage_path('app/accordion/' . $course_id . '_' . $section . '*');

            // Buscar los archivos que coinciden con el patrón
            $files = glob($pattern);

            if (!empty($files)) {
                // Si se encuentran archivos, eliminarlos
                foreach ($files as $file_path) {

                    $file_name_to_delete = basename($file_path); // Obtener el nombre del archivo

                    // Verificar si el archivo está en el disco 'accordion' y eliminarlo
                    if (Storage::disk('accordion')->exists($file_name_to_delete)) {
                        Storage::disk('accordion')->delete($file_name_to_delete);
                    }
                }
            }

            //nombre unico
            $stored =   Storage::disk('accordion')->put($file_name, File::get($file));
            if ($stored) {

                $accordion = Accordion::where('course_id', $course_id)->where('num_of_section', $section)->first();
                // Log::info($accordion);

                if ($accordion) {
                    $accordion->file = $file_name;

                    $accordion->save();

                    $data = array(

                        'code' => 200,
                        'status' => 'success',
                        '$accordion->file' => $accordion->file
                    );
                } else {

                    $data = array(

                        'code' => 400,
                        'status' => 'error',
                        'message' => 'there is no record on accordion table'
                    );
                }
            }
        }

        return response($data, $data['code'])->header('Content-type', 'text/plain');
    }
}
