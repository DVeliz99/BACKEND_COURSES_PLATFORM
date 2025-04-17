<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Sales;
use App\Helpers\jwtAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Http\Middleware\ApiAuthMiddleware;
use App\Models\Video;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;



class CourseController extends BaseController //para que lea el middleware
{
    public function __construct()
    {
        $this->middleware(ApiAuthMiddleware::class, ['except' => [
            'index',
            'search',
            'getCourse',
            'getCoursesByCategory',
            'getImage'

        ]]);
    }


    public function index() //Metodo por GET
    {
        //Muestra todos los cursos
        $courses = Course::all();
        return response()->json([
            'code' => 200,
            'status' => 'success',
            'courses' => $courses
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
    public function store(Request $request) //Metodo por POST
    {
        //Se recibiran datos desde el frontend
        $json = $request->input("json", null);
        // $params_array = $request->all();
        // 
        $params = json_decode($json);
        $params_array = json_decode($json, true); //array para uso de php 

        Log::info('params_array', $params_array);


        // Log::info($request->all());
        // if ($params_array) {
        //     echo "$params_array";
        // } else {
        //     echo "params_array vacio";
        // }


        // return response()->json($params_array);

        if (!empty($params_array)) {
            $validate = Validator::make($params_array, [
                'name' => 'required|unique:courses',
                'category_id' => 'required',
                'detail' => 'required',
                'url' => 'required',
                'accordion' => 'required',
                'price_before' => 'required',
                'price_now' => 'required'

            ]);

            $params_array['image'] = isset($params_array['image']) ? $params_array['image'] : null;
            $params_array['num_of_sales'] = isset($params_array['num_of_sales']) ?  $params_array['num_of_sales'] : null;

            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'The course has not been saved'

                );
            } else {
                $course = new Course(); //Instanciar la clase de curso 
                $course->name =  $params_array['name'];
                $course->category_id = $params_array['category_id'];
                $course->detail =  $params_array['detail'];
                $course->image =  $params_array['image'];
                $course->url = $params_array['url'];
                $course->accordion =  $params_array['accordion'];
                $course->price_before =  $params_array['price_before'];
                $course->price_now =  $params_array['price_now'];

                $course->save();

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'course' => $params_array

                );
            }
        } else {
            $data = array(
                'status' => 'Error',
                'code' => 404,
                'message' => 'There is no course'

            );
        }

        return response()->json($data, $data['code']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id, Request $request)
    {
        /*Mostrar un curso determinado, verificando que el curso_id este en el mismo objeto ventas con el user_id*/

        //$id es el course_id , $request es el token

        $course = Course::find($id); //El curso con el id en especifico
        //Obtener un usuario identificado 
        $user = $this->getIdentity($request);


        $sales = Sales::where('user_id', $user->sub)
            ->where('course_id', $course->id)->first();

        if (!empty($sales) && is_object($sales)) {
            $course->bought = 1;
        }


        if (is_object($course)) {


            $vector = [];

            for ($i = 1; $id <= $course->accordion; $i++) {
                array_push($vector, $i);
            }


            $data = array(
                'code' => 200,
                'status' => $vector,
                'accordion' => $vector,
                'course' => $course,
                'sales' => $sales

            );
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'this course does not exist'

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
                'name' => 'required',
                'category_id' => 'required',
                'detail' => 'required',
                'url' => 'required',
                //'accordion' => 'required',
                'price_before' => 'required',
                'price_now' => 'required'

            ]);


            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'The course has not been saved'

                );
            } else {

                //quuitar campos no deseados a actualizar

                unset($params_array['id']);
                unset($params_array['created_at']);
                unset($params_array['user']);
                if (array_key_exists('accordion', $params_array) && $params_array['accordion'] === '') {
                    unset($params_array['accordion']); // Eliminar `accordion` para evitar sobreescribirlo con un valor vacío
                }


                //conseguir usuario identificado 

                // $user = $this->getIdentity($request);

                $course = Course::where('id', $id)->first(); //verificar si el id del parametro es el mismo del curso en la tabla courses 

                if (!empty($course) && is_object($course)) {
                    $course->update($params_array);

                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'course' => $course,
                        'changes' => $params_array

                    );
                } else {
                    $data = array(
                        'status' => 'Error',
                        'code' => 404,
                        'message' => 'Data was sent incorrectly!'

                    );
                }
            }
        } else {
            $data = array(
                'status' => 'Error',
                'code' => 404,
                'message' => 'There is no such course on existence'

            );
        }

        return response()->json($data, $data['code']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, Request $request)
    {
        /*ELiminar el curso */



        $course = Course::find($id); //El curso con el id en especifico
        // $course = Course::where('id', $id)->first();
        //Obtener un usuario identificado 
        $user = $this->getIdentity($request);


        $sales = Sales::where('user_id', $user->sub)->get();



        $videos = Video::where('user_id', $user->sub)->get();


        if (($sales && count($sales) >= 1) || ($videos && count($videos) >= 1)) {
            foreach ($sales as $sale) {
                $sale->delete();
            }

            foreach ($videos as $video) {
                $video->delete();
            }
        }


        if (!empty($course)) {

            //Eliminar el curso 
            $course->delete();

            $data = array(

                'code' => 200,
                'status' => 'success',
                'course' => $course
            );
        } else {
            $data = array(

                'code' => 404,
                'status' => 'error',
                'message' => 'the course doesn not exist'
            );
        }





        //devolver respuesta

        return response()->json($data, $data['code']);
    }

    public function getCourse($id)
    { //Muestra el curso sin importar el propietario
        $course = Course::find($id);




        if (is_object($course)) {

            $vector = [];

            array_push($vector, $course->accordion);


            $data = array(

                'code' => 200,
                'status' => 'success',
                'accordion' => $vector,
                'course' => $course
            );
        } else {
            $data = array(

                'code' => 404,
                'status' => 'error',
                'message' => 'The course does not exist'
            );
        }

        return response()->json($data, $data['code']);
    }

    public function getImage($filename)
    {
        $isset = Storage::disk('courses')->exists($filename);
        if ($isset) {
            $file = Storage::disk('courses')->get($filename);

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

    public function upload(Request $request)
    {

        // Log::info($request()->all());

        $image = $request->file('file'); //el campo del formulario 

        $course_name = $request->input('courseName');
        //validación de la imagen

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

            $image_name = $course_name . $image->getClientOriginalName(); //nombre unico
            Storage::disk('courses')->put($image_name, File::get($image));
            $data = array(

                'code' => 200,
                'status' => 'success',
                'message' => $image_name
            );
        }

        return response($data, $data['code'])->header('Content-type', 'text/plain');
    }

    public function search($query)
    {

        $courses = Course::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($query) . '%'])
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([

            'status' => 'success',
            'code' => 200,
            'courses' => $courses

        ]);
    }

    public function getCoursesByCategory($id)
    {
        $courses = Course::where('category_id', $id)->get();

        if (!empty($courses) && is_object($courses)) {
            $data = array(

                'code' => 200,
                'status' => 'success',
                'CoursesByCategory' => $courses
            );

            return response()->json($data, $data['code']);
        }
    }
}
