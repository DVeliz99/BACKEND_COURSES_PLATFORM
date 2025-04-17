<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\Cors;


// header('Access-Control-Allow-Origin: *');
// header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
// header('Access-Control-Allow-Headers: Content-Type, Authorization, Origin, X-Auth-Token');
// header('Access-Control-Allow-Credentials: true');


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        //Excluir URIs de la Protección CSRF en Laravel 11
        $middleware->validateCsrfTokens(except: [

            'http://backend.rest/api/register',
            'http://backend.rest/api/login',
            'http://backend.rest/api/user/update',
            'http://backend.rest/api/user/upload',
            'http://backend.rest/api/user/avatar',
            'http://backend.rest/api/user/detail',
            'http://backend.rest/api/course/', //POST Y GET

            'http://backend.rest/api/course/*', //Para rutas dinamicas 
            'http://backend.rest/api/getCoursesByCategory',
            'http://backend.rest/api/getCourse/',
            'http://backend.rest/api/course/upload',
            'http://backend.rest/api/course/search/',
            'http://backend.rest/api/video/',
            'http://backend.rest/api/video/doc',
            'http://backend.rest/api/video',
            'http://backend.rest/api/video/*',
            'http://backend.rest/api/videos',
            'http://backend.rest/api/video/course',
            'http://backend.rest/api/video/course',
            'http://backend.rest/api/video/course2',
            'http://backend.rest/api/video/update_title',
            'http://backend.rest/api/sale',
            'http://backend.rest/api/sale/checkbox/*',
            'http://backend.rest/api/getCoursesByCategory',
            'http://backend.rest/api/myCourses',
            'http://backend.rest/api/comments/*',
            'http://backend.rest/api/comments/',
            'http://backend.rest/api/comments/upload',
            'http://backend.rest/api/comments/image/',
            'http://backend.rest/api/replies/',
            'http://backend.rest/api/replies/*',
            'http://backenwebd.rest/api/replies/upload',
            'http://backend.rest/api/replies/image/',
            'http://backend.rest/api/checkbox/',
            'http://backend.rest/api/checkbox/*',

            'http://backend.rest/api/categories',
            'http://backend.rest/api/categories/*',
            //'http://backend.rest/api/cart/*',
            'http://backend.rest/api/delete/cart',
            'http://backend.rest/api/getSalesByCategory/',
            'http://backend.rest/api/cart/*',
            'http://backend.rest/api/cart/',
            'http://backend.rest/api/cart',
            'http://backend.rest/api/delete/cart/*',
            'http://backend.rest/api/delete/cart/',
            'http://backend.rest/api/accordion/',
            'http://backend.rest/api/accordion/*',
            'http://backend.rest/api/accordion/upload'








        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
