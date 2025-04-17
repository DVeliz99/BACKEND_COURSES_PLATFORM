<?php

use App\Http\Controllers\AccordionController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\CheckboxController;
use App\Http\Controllers\CommentsController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ResponseController;
use App\Http\Controllers\SalesController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VideoController;
use App\Http\Middleware\ApiAuthMiddleware;
use App\Models\Accordion;

// Route::get('/', function () {
//     return view('welcome');
// });


Route::get('/', [TestController::class, 'index']);

/*RUTAS DE PRUEBA */
Route::get('/user/test', [UserController::class, 'test']);



/*RUTAS DEL CONTROLADOR DE USUARIO */
Route::post('/api/register', [UserController::class, 'register']);
Route::post('/api/login', [UserController::class, 'login']);
Route::put('/api/user/update', [UserController::class, 'update']);
Route::post('/api/user/upload', [UserController::class, 'upload'])->middleware(ApiAuthMiddleware::class); //aplicamos el middleware que verifica si el usuario esta identificado a nivel de ruta 
Route::get('/api/user/avatar/{filename}', [UserController::class, 'getImage']);
Route::get('/api/user/detail/{id}', [UserController::class, 'detail']);


/*RUTAS DEL CONTROLADOR DE COURSE */
Route::resource('/api/course', CourseController::class);
Route::get('/api/getCourse/{id}', [CourseController::class, 'getCourse']); //Mostra el curso 
Route::post('/api/course/upload', [CourseController::class, 'upload']); //Guarda la imagen del curso
Route::get('/api/course/image/{filename}', [CourseController::class, 'getImage']);
Route::get('/api/course/search/{text}', [CourseController::class, 'search']);
Route::get('/api/getCoursesByCategory/{id}', [CourseController::class, 'getCoursesByCategory']);



/*RUTAS DEL CONTROLADOR DE VIDEO */
Route::resource('/api/video', VideoController::class);
// Route::post('/api/video', [VideoController::class, 'store']);
Route::get('/api/videos', [VideoController::class, 'getVideos']);
Route::get('/api/video/course/{id}', [VideoController::class, 'getVideosByCourse']);
Route::get('/api/video/course2/{id}', [VideoController::class, 'getVideosByCourse2']);
Route::put('/api/video/update_title/{id}', [VideoController::class, 'updatetitle']);
Route::post('api/video/doc', [VideoController::class, 'upload']);


/*RUTAS DEL CONTROLADOR DE SALES */
Route::resource('/api/sale', SalesController::class);
Route::put('/api/sale/checkbox/{id}', [SalesController::class, 'updateCheckbox']);
Route::get('/api/myCourses', [SalesController::class, 'myCourses']);
Route::get('/api/getSalesByCategory/{id}', [SalesController::class, 'getSalesByCategory']);
Route::get('/api/sales/search/{text}', [SalesController::class, 'getSalesByText']);


/*RUTAS DEL CONTROLADOR DE COMMENTS */
Route::resource('api/comments', CommentsController::class);
Route::post('/api/comments/upload', [CommentsController::class, 'upload']); //Guarda la imagen del comentario
Route::get('/api/comments/image/{filename}', [CommentsController::class, 'getImage']);


/*RUTAS DEL CONTROLADOR DE Response */
Route::resource('/api/replies', ResponseController::class);
Route::post('/api/replies/upload', [ResponseController::class, 'upload']); //Guarda la imagen del curso
Route::get('/api/replies/image/{filename}', [ResponseController::class, 'getImage']);


/*RUTAS DEL CONTROLADOR DE CHECKBOX */
Route::resource('/api/checkbox', CheckboxController::class);


/*RUTAS DEL CONTROLADOR DE CATEGORIAS */
Route::resource('/api/categories', CategoriesController::class);


/*RUTAS DEL CONTROLADOR DE Cart */
Route::resource('/api/cart', CartController::class);
Route::delete('/api/delete/cart', [CartController::class, 'deleteCart']);

/*RUTAS DEL CONTROLADOR DE ACCORDION */
Route::resource('/api/accordion', AccordionController::class);
Route::post('/api/accordion/upload', [AccordionController::class, 'upload']); //Guarda la imagen del curso
