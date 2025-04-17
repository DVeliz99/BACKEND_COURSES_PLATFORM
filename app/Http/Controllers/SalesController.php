<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\jwtAuth;
use Illuminate\Support\Facades\Validator;
use App\Models\Course;
use App\Models\Cart;
use App\Models\Sales;
use App\Models\Video;
use Illuminate\Support\Facades\Log;
use App\Http\Middleware\ApiAuthMiddleware;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class SalesController extends BaseController
{


    public function __construct()
    {
        $this->middleware(ApiAuthMiddleware::class, ['except' => []]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Obtener los datos del usuario
        $user = $this->getIdentity($request);

        // Compras del usuario
        $sales = Sales::where('user_id', $user->sub)->get();

        // Cursos disponibles
        $courses = Course::all();

        // Carrito del usuario
        $cart = Cart::where('user_id', $user->sub)->get();

        $courseStatus = [];

        foreach ($courses as $course) {
            // Log::info('Curso:', ['course_id' => $course->id]);

            foreach ($sales as $sale) {
                if (is_object($sale) && isset($sale->course_id)) {
                    // Log::info('Venta:', ['sale_id' => $sale->course_id]);

                    if ($course->id == $sale->course_id) {
                        // Se verifica si el id del course está en la tabla Sales para verificar si se compró el curso
                        $course->bought = 1; // Nueva propiedad
                        $course->video_id = $sale->video_id; // Nueva propiedad
                    }
                } else {
                    // Log::error('Venta inválida en el bucle', ['sale' => $sale]);
                }
            }

            foreach ($cart as $cartItem) {
                if (is_object($cartItem) && isset($cartItem->course_id) && $cartItem->course_id == $course->id && $cartItem->quantity == 1) {
                    $course->cart = 1; // Nueva propiedad
                }
            }

            array_push($courseStatus, $course); // Se le añade cada curso al array
        }

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'sales' => $sales,
            'statusCourse' => $courseStatus
        ]);
    }

    public function updateCheckbox(Request $request)
    {
        $json = $request->input("json", null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);
        Log::info($params_array); // Log de los datos recibidos

        $data = array();
        $user = $this->getIdentity($request);


        if (!empty($params_array)) {
            $validate = Validator::make($params_array, [
                'course_id' => 'required',

                'percentageXvideo' => 'required'

            ]);

            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'data was not received properly'
                );
            } else {
                $sale = Sales::where('user_id', $user->sub)->where('course_id', $params_array['course_id'])->first();

                if (!empty($sale)) {
                    $sale->progreso = $sale->progreso + $params_array['percentageXvideo'];
                    if ($sale->progreso < 0) {
                        $sale->progreso = 0;
                    } elseif ($sale->progreso > 100) {
                        $sale->progreso = 100;
                    }

                    $sale->save();

                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'The progress on sale was updated',
                        'progress' => $sale->progreso
                    );
                }
            }
        }
        return response()->json($data, $data['code']);
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
        $user = $this->getIdentity($request);

        $cart = Cart::where('user_id', $user->sub)->get();

        if (!$cart->isEmpty()) {
            foreach ($cart as $cartItem) {
                $videos = Video::where('course_id', $cartItem->course_id)->get(); // Verifica si ese curso a comprar tiene videos
                if (!$videos->isEmpty()) {
                    $video = $videos->first(); // Obtenemos el primer video

                    if (!empty($video->id)) {
                        $sale = new Sales();
                        $sale->user_id = $cartItem->user_id;
                        $sale->course_id = $cartItem->course_id;
                        $sale->video_id = $video->id; // El video principal del curso (el primero)

                        $sale->save();

                        $data = array(
                            'code' => 200,
                            'status' => 'success',
                            'sales' => $cart
                        );
                    } else {
                        $data = array(
                            'code' => 404,
                            'status' => 'error',
                            'message' => 'There is no video on the course'
                        );
                    }
                } else {
                    $data = array(
                        'code' => 200,
                        'status' => 'error',
                        'message' => 'There is no video on the course'
                    );
                }
            }
        } else {
            $data = array(
                'code' => 200,
                'status' => 'error',
                'message' => 'There is nothing on the cart'
            );
        }

        return response()->json($data, $data['code']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id, Request $request)
    {
        /*Mostrar una sale de un curso si el usuario la compró */

        $user = $this->getIdentity($request);

        $sale = Sales::where('user_id', $user->sub)
            ->where('course_id', $id)
            ->first();


        if (!empty($sale)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'message' => $sale
            );
        } else {

            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'There is not any sales'
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
        $json = $request->input("json", null);
        $params_array = json_decode($json, true); // Solo necesitas esta línea.


        if (!empty($params_array)) {
            $validate = Validator::make($params_array, [
                'course_id' => 'required',
                'video_id' => 'required',
                'progreso' => 'required',
            ]);

            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'the progress has not been uploaded'
                );
            } else {
                unset($params_array['id']);
                unset($params_array['user_id']);
                unset($params_array['created_at']);
                unset($params_array['user']);

                //obtener usuario identificado

                $user = $this->getIdentity($request);

                $sale = Sales::where('id', $id)
                    ->first();


                if (!empty($sale) && is_object($sale)) {

                    //actualizar el registro en concreto 
                    $sale->update($params_array);

                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'video' => $sale,
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
                'message' => 'Sale does not exist'

            );
        }

        return response()->json($data, $data['code']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
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

    public function myCourses(Request $request)
    {

        //Obtener el usuario identificado 

        $user = $this->getIdentity($request);
        $sales = Sales::where('user_id', $user->sub)->get();

        $courses = Course::all();
        $videos = Video::all();
        $cart = Cart::where('user_id', $user->sub)->get();

        $statusCourses = [];
        $videosVector = [];

        foreach ($courses as $course) {
            foreach ($sales as $sale) {
                if ($course->id == $sale->course_id) {
                    $course->bought = 1;
                    $course->video_id = $sale->video_id;
                }
            }

            foreach ($cart as $cartItem) {
                if ($cartItem->course_id == $course->id && $cartItem->quantity == 1) {
                }
            }

            foreach ($videos as $video) {
                if ($video->course_id == $course->id && $course->bought == 1) {
                    array_push($videosVector, $video); //todos videos del curso que compro el usuario 
                }
            }

            if ($course->bought == 1) {
                array_push($statusCourses, $course); // El curso si lo compro
            }
        }

        $data = array(
            'code' => 200,
            'status' => 'success',
            'sales' => $sales,
            'boughtCourses' => $statusCourses,
            'videos' => $videosVector
        );

        return response()->json($data, $data['code']);
    }


    public function getSalesByCategory(Request $request, string $id)
    {
        // Obtener los datos del usuario
        $user = $this->getIdentity($request);

        // Compras del usuario
        $sales = Sales::where('user_id', $user->sub)->get();

        // Cursos disponibles
        $courses = Course::where('category_id', $id)->get();

        // Carrito del usuario
        $cart = Cart::where('user_id', $user->sub)->get();

        $courseStatus = [];

        foreach ($courses as $course) {
            Log::info('Curso:', ['course_id' => $course->id]);

            foreach ($sales as $sale) {
                if (is_object($sale) && isset($sale->course_id)) {
                    // Log::info('Venta:', ['sale_id' => $sale->course_id]);

                    if ($course->id == $sale->course_id) {
                        // Se verifica si el id del course está en la tabla Sales para verificar si se compró el curso
                        $course->bought = 1; // Nueva propiedad
                        $course->video_id = $sale->video_id; // Nueva propiedad
                    }
                } else {
                    // Log::error('Venta inválida en el bucle', ['sale' => $sale]);
                }
            }

            foreach ($cart as $cartItem) {
                if (is_object($cartItem) && isset($cartItem->course_id) && $cartItem->course_id == $course->id && $cartItem->quantity == 1) {
                    $course->cart = 1; // Nueva propiedad
                }
            }

            array_push($courseStatus, $course); // Se le añade cada curso al array
        }

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'sales' => $sales,
            'statusCourse' => $courseStatus
        ]);
    }

    public function getSalesByText(Request $request, string $text)
    {
        // Obtener los datos del usuario
        $user = $this->getIdentity($request);

        // Compras del usuario
        $sales = Sales::where('user_id', $user->sub)->get();

        // Cursos disponibles
        $courses = Course::where('name', 'LIKE', '%' . $text . '%')
            ->orderBy('id', 'desc')->get();

        // Carrito del usuario
        $cart = Cart::where('user_id', $user->sub)->get();

        $courseStatus = [];

        foreach ($courses as $course) {
            Log::info('Curso:', ['course_id' => $course->id]);

            foreach ($sales as $sale) {
                if (is_object($sale) && isset($sale->course_id)) {
                    // Log::info('Venta:', ['sale_id' => $sale->course_id]);

                    if ($course->id == $sale->course_id) {
                        // Se verifica si el id del course está en la tabla Sales para verificar si se compró el curso
                        $course->bought = 1; // Nueva propiedad
                        $course->video_id = $sale->video_id; // Nueva propiedad
                    }
                } else {
                    // Log::error('Venta inválida en el bucle', ['sale' => $sale]);
                }
            }

            foreach ($cart as $cartItem) {
                if (is_object($cartItem) && isset($cartItem->course_id) && $cartItem->course_id == $course->id && $cartItem->quantity == 1) {
                    $course->cart = 1; // Nueva propiedad
                }
            }

            array_push($courseStatus, $course); // Se le añade cada curso al array
        }

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'sales' => $sales,
            'statusCourse' => $courseStatus
        ]);
    }
}
