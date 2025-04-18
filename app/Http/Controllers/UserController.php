<?php




namespace App\Http\Controllers;



use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Helpers\jwtAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Routing\Controller;
use App\Http\Middleware\ApiAuthMiddleware;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File; //para manejar archivos
use Illuminate\Support\Facades\Storage; //para manejo de disco virtual
use Illuminate\Http\Response;

class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware(ApiAuthMiddleware::class, ['except' => [
            'getImage',
            'login'
        ]]);
    }


    public function test(Request $request)
    {
        return 'Testing action of UserController';
    }

    public function register(Request $request)
    {
        //Recibiremos datos formato json desde el frontend(Angular JS) 

        $json = $request->input("json", null); //Si no llega el dato formato json se asigna null

        $params = json_decode($json); //Decodifica el json y lo convierte a formato php 
        $params_array = json_decode($json, true);

        //Validar datos =>si llegaron los datos del cliente (Angular)

        if (!empty($params) && !empty($params_array)) {
            //limpiar los datos 
            $params_array = array_map('trim', $params_array); //crea otro array limpio, sin espacios 

            //validar los datos 

            $validate = Validator::make($params_array, [
                'name' => 'required|alpha',
                'surname' => 'required|alpha',
                'email' => 'required|email|unique:users',
                'password' => 'required'
            ]);

            if ($validate->fails()) { //Si la validacion falla
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'The user has not been created',
                    'errors' => $validate->errors()
                );
            } else {
                // $pwd = hash('sha256', $params->password);

                $pwd = Hash::make($params_array['password']);


                //Crear usuario

                $user = new User(); //crear el objeto tipo user

                $user->name = $params_array['name'];
                $user->surname = $params_array['surname'];
                $user->email = $params_array['email'];
                $user->password = $pwd;
                $user->role = 'ROLE_USER';
                $user->image = 'no_photo.jpg';

                //guardar el usuario 
                $user->save();

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'The user has been created successfully',
                    'user' => $user
                );
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'Data has not been sent',

            );
        }
        //Enviamos la respuesta json al cliente (Angular) 
        return response()->json($data, $data['code']);
    }

    public function login(Request $request)
    {
        $jwtAuth = new jwtAuth(); //Instanciamos el helper

        /*Probando el helper */
        // $signup = $jwtAuth->signup();
        // echo $signup;

        $json = $request->input("json", null); //Si no hay json, se asigna null 
        $params = json_decode($json, true); //lo decodifica en un array para uso de php 
        $params_array = json_decode($json, true);



        // Logging de información
        // Log::info('params_array', $params_array);

        $validate = Validator::make($params_array, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validate->fails()) {

            $signup = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'User could not log in',
                'errors' => $validate->errors()
            );
        } else {
            //Si la validacion se aprovó


            $pwd = hash('sha256', $params_array['password']); //Se genera el mismo hash de la misma contraseña dentro de la base de datos 



            $signup = $jwtAuth->signup($params_array['email'], $params_array['password']); //json_decode($json, true) devuelve un array asociativo
            //Obtenemos el token si los datos existen en la tabla

            if (!empty($params->getToken)) {
                $signup = $jwtAuth->signup($params->email, $pwd, true); //obtenemos el token decodificado
            }
        }

        return response()->json($signup, 200); //El token o la decodificacion del token
    }

    public function update(Request $request)
    {
        $token = $request->header('Authorization'); //el Header que tiene el token

        $jwtAuth = new jwtAuth(); //nstancia para utilizar el helper
        $checktoken = $jwtAuth->checkToken($token);


        //Recibir datos por POST 
        $json = $request->input("json", null);
        $params_array = json_decode($json, true);


        // Logging de información
        // Log::info('params_array', $params_array);
        if ($checktoken && !empty($params_array)) { //Si hay token y y si hay un objeto json

            //Substrae el usuario identificado
            $user = $jwtAuth->checkToken($token, true);

            //Validar datos 
            $validate = Validator::make($params_array, [
                'name' => 'required|alpha',
                'surname' => 'required|alpha',
                'email' => 'required|email|unique:users' . $user->sub //a exepcion del email del usuario indentificado (no puede repetirse con otro email en la tabla)
            ]);

            //Destruir las variables del array para que no se actualizen
            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['password']);
            unset($params_array['created_at']);
            unset($params_array['remember_token']);


            //actualizar el usuario en la base de datos 
            $user_update = User::where('id', $user->sub)->update($params_array);


            //Devolver el array con el resultado
            $data = array(
                'code' => 200,
                'status' => 'success',
                'user' => $user,
                'changes' => $params_array
            );
        } else {

            //Devolver el array con el resultado
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'The user is not authenticated'
            );
        }

        return response()->json($data, $data['code']);
    }

    public function upload(Request $request)
    {



        $image = $request->file('file'); //La libreria de angular necesita que el campo se llame de esta forma

        // Log::info('params_array', $image);

        // Obtener el parámetro adicional
        $user_name = $request->input('userName');


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
            $image_name = $user_name . '_' . $image->getClientOriginalName(); //Obtiene el nombre de la imagen y lo une con el tiempo actual 

            $isset = Storage::disk('users')->exists($image_name);

            if ($isset) {
                $data = array(
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'the image already exists'
                );
            } else {

                // Buscar archivos cuyo nombre comienza con el nombre de usuario
                $existing_files = Storage::disk('users')->files();

                foreach ($existing_files as $file) {
                    // Si un archivo comienza con el nombre de usuario, lo eliminamos
                    if (strpos($file, $user_name . '_') === 0) { //Si $user_name . '_' está al principio del nombre del archivo, entonces strpos() devolverá 0.
                        Storage::disk('users')->delete($file); // Eliminar el archivo
                    }
                }

                Storage::disk('users')->put($image_name, File::get($image)); //indicamos el nombre y el archivo que se va a guardar


                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'message' => $image_name

                );

                Storage::disk('users')->put($image_name, File::get($image));
                Log::info('Imagen guardada en el disco users:', ['path' => storage_path('app/users') . '/' . $image_name]);
            }
        }

        return response($data, $data['code'])->header('Content-type', 'text/plain');
    }

    public function getImage($filename)
    {
        $isset = Storage::disk('users')->exists($filename);
        if ($isset) {
            $file = Storage::disk('users')->get($filename);

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

    public function detail($id)
    {
        $user = User::find($id);

        if (is_object($user)) {

            $data = array(
                'code' => 200,
                'status' => 'success',
                'user' => $user
            );
        } else {
            $data = array(
                'code' => 400,
                'status' => 'Error',
                'user' => 'The user does not exist'
            );
        }

        return response()->json($data, $data['code']);
    }
}
