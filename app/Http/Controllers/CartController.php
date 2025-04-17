<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\jwtAuth;
use App\Http\Middleware\ApiAuthMiddleware;
use App\Models\Cart;

use Illuminate\Routing\Controller as BaseController;

class CartController extends BaseController
{

    public function __construct()
    {
        $this->middleware(ApiAuthMiddleware::class, ['except' => ['']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $this->getIdentity($request);
        $total = 0;

        $cart = Cart::where('user_id', $user->sub)->get();

        $productsArray = [];
        $quantityArray = [];
        $subtotalArray = [];
        $coursesArray = [];

        foreach ($cart as $cartItem) {
            //a tarvex de Eloquent
            array_push($coursesArray, $cartItem->course);
            array_push($quantityArray, $cartItem->quantity);
            array_push($productsArray, $cartItem->course->id);

            $subtotal = $cartItem->course->price_now * $cartItem->quantity;

            array_push($subtotalArray, $subtotal);

            $total = $total + $subtotal; //acumulador 
        }

        $products_data = implode('_', $productsArray); //Convierte a string y los separa por un '_'

        //Para el frontend
        $data = [
            'status' => 'success',
            'code' => 200,
            'courses' => $coursesArray,
            'cart' => $cart,
            'counter' => $cart->count(), //numero de cursos en  el carrito 
            'product_data' => $products_data,
            'quantities' => $quantityArray, //cantidad de cada curso
            'subtotals' => $subtotalArray,
            'total' => $total



        ];


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
        //recoger el json
        $json = $request->input("json", null);

        $params_array = json_decode($json, true);

        $user = $this->getIdentity($request);

        $product = Cart::where('user_id', $user->sub)
            ->where('course_id', $params_array['course_id'])
            ->first();

        if ($product) {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'The course is already on cart',
                'product' => $product
            );
        } else {
            if (!empty($params_array)) {

                $validate = Validator::make($params_array, [
                    'quantity' => 'required',
                    'course_id' => 'required'

                ]);


                if ($validate->fails()) {
                    $data = array(
                        'status' => 'error',
                        'code' => 404,
                        'message' => 'Cart has not been saved'
                    );
                } else {
                    $cart = new Cart();
                    $cart->user_id = $user->sub;
                    $cart->course_id = $params_array['course_id'];
                    $cart->quantity = $params_array['quantity'];

                    $cart->save();



                    $coursesOnCart = Cart::where('user_id', $user->sub)->get();




                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'Cart has been saved',
                        'cart' => $coursesOnCart
                    );
                }
            } else {
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'No data has been sent'
                );
            }
        }

        return response()->json($data, $data['code']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id, Request $request)
    {
        $user = $this->getIdentity($request);

        $cart = Cart::all()
            ->where('user_id', $user->sub)
            ->where('course_id', $id);

        foreach ($cart as $cartItem) {
            if ($cartItem->course_id == $id) {

                return response()->json([
                    'status' => 'success',
                    'state' => 'update',
                    'cartItem' => $cart
                ], 200);
            }
        }

        if ($cart) {
            return response()->json([
                'status' => 'success',
                'state' => 'create',
                'cartItem' => $cart //carrito vacio
            ], 200);
        }
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, Request $request)
    {

        //string id =>id del curso 

        $user = $this->getIdentity($request);

        $cart = Cart::where('course_id', $id)
            ->where('user_id', $user->sub)
            ->first();

        if (!empty($cart)) {
            $cart->delete();

            $coursesOnCart = Cart::where('user_id', $user->sub)->get();

            $data = array(
                'status' => 'success',
                'code' => 200,
                'message' => 'The cart has been removed',
                'product' => $cart,
                'cart' => $coursesOnCart
            );
        } else {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'The cart does not exist',

            );
        }

        return response()->json($data, $data['code']);
    }




    public function deleteCart(Request $request)
    {
        $user = $this->getIdentity($request);

        $cart = Cart::where('user_id', $user->sub)->get(); //El carrito completo del usuario

        if (!empty($cart)) {

            foreach ($cart as $cartItem) {
                $cartItem->delete();
            }

            $data = array(
                'status' => 'success',
                'code' => 200,
                'cartDelete' => $cart

            );
        } else {

            $data = array(
                'status' => 'error',
                'code' => 404,
                'cartDelete' => 'Cart could not be deleted'

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
