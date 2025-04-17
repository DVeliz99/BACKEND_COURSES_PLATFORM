<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\jwtAuth;
use App\Http\Middleware\ApiAuthMiddleware;
use App\Models\Category;
use App\Models\Course;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;

use function Illuminate\Log\log;

class CategoriesController extends BaseController
{

    public function __construct()
    {
        $this->middleware(ApiAuthMiddleware::class, ['except' => ['index', 'show']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::all();
        return response()->json([
            'code' => 200,
            'status' => 'success',
            'categories' => $categories

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
        $json = $request->input("json", null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            $user = $this->getIdentity($request);

            $validate = Validator::make($params_array, [
                'name' => 'required',

            ]);


            if ($validate->fails()) {

                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'The category was not saved',
                );
            } else {

                $category = new Category();
                $category->name = $params_array['name'];
                $category->save();

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'Category' => $category
                );
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'The data was not sent correctly',
            );
        }

        return response()->json($data, $data['code']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = Category::Find($id);

        if (is_object($category)) {
            $data = array(
                'status' => 'success',
                'code' => 200,
                'categories' => $category
            );
        } else {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'The category was not found',
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


        // Log::info('Request data:', ['params' => $request, 'id' => $id]);


        // Verificar si hay datos
        if (empty($params_array)) {
            return response()->json([
                'status' => 'error',
                'code' => 400,
                'message' => 'No data provided'
            ], 400);
        }

        // Validar los datos
        $validate = Validator::make($params_array, [
            'name' => 'required|string|max:255',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'code' => 422,
                'message' => 'Validation failed',
                'errors' => $validate->errors()
            ], 422);
        }

        // Buscar la categoría
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'status' => 'error',
                'code' => 404,
                'message' => 'Category not found'
            ], 404);
        }

        // Eliminar campos que no se deben actualizar
        unset($params_array['id'], $params_array['created_at']);

        // Actualizar la categoría
        $category->update($params_array);

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'Category updated successfully',
            'category' => $category
        ], 200);

        return response()->json($data, $data['code']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //

        // Log::info($id);


        $category = Category::where('id', $id)->first();
       

        if (!empty($category)) {


            // Obtener cursos relacionados y eliminarlos uno por uno
            $courses = Course::where('category_id', $id)->get();
            foreach ($courses as $course) {
                $course->delete();
            }
            $category->delete();

            $data = array(
                'status' => 'success',
                'code' => 200,
                'message' => 'The category was deleted',
                'category' => $category
            );
        } else {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'The category was not deleted',
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
