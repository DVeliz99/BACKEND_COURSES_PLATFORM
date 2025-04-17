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

            'https://coursesplatformbackend-production.up.railway.app/api/register',
            'https://coursesplatformbackend-production.up.railway.app/api/login',
            'https://coursesplatformbackend-production.up.railway.app/api/user/update',
            'https://coursesplatformbackend-production.up.railway.app/api/user/upload',
            'https://coursesplatformbackend-production.up.railway.app/api/user/avatar',
            'https://coursesplatformbackend-production.up.railway.app/api/user/detail',
            'https://coursesplatformbackend-production.up.railway.app/api/course/', //POST Y GET

            'https://coursesplatformbackend-production.up.railway.app/api/course/*', //Para rutas dinamicas 
            'https://coursesplatformbackend-production.up.railway.app/api/getCoursesByCategory',
            'https://coursesplatformbackend-production.up.railway.app/api/getCourse/',
            'https://coursesplatformbackend-production.up.railway.app/api/course/upload',
            'https://coursesplatformbackend-production.up.railway.app/api/course/search/', //
            'https://coursesplatformbackend-production.up.railway.app/api/video/',
            'https://coursesplatformbackend-production.up.railway.app/api/video/doc',
            'https://coursesplatformbackend-production.up.railway.app/api/video',
            'https://coursesplatformbackend-production.up.railway.app/api/video/*',
            'https://coursesplatformbackend-production.up.railway.app/api/videos',
            'https://coursesplatformbackend-production.up.railway.app/api/video/course',
            'https://coursesplatformbackend-production.up.railway.app/api/video/course',
            'https://coursesplatformbackend-production.up.railway.app/api/video/course2',
            'https://coursesplatformbackend-production.up.railway.app/api/video/update_title',
            'https://coursesplatformbackend-production.up.railway.app/api/sale',
            'https://coursesplatformbackend-production.up.railway.app/api/sale/checkbox/*',
            'https://coursesplatformbackend-production.up.railway.app/api/getCoursesByCategory',
            'https://coursesplatformbackend-production.up.railway.app/api/myCourses',
            'https://coursesplatformbackend-production.up.railway.app/api/comments/*',
            'https://coursesplatformbackend-production.up.railway.app/api/comments/',
            'https://coursesplatformbackend-production.up.railway.app/api/comments/upload',
            'https://coursesplatformbackend-production.up.railway.app/api/comments/image/',
            'https://coursesplatformbackend-production.up.railway.app/api/replies/',
            'https://coursesplatformbackend-production.up.railway.app/api/replies/*',
            'https://coursesplatformbackend-production.up.railway.app/api/replies/upload',
            'https://coursesplatformbackend-production.up.railway.app/api/replies/image/',
            'https://coursesplatformbackend-production.up.railway.app/api/checkbox/',
            'https://coursesplatformbackend-production.up.railway.app/api/checkbox/*',

            'https://coursesplatformbackend-production.up.railway.app/api/categories',
            'https://coursesplatformbackend-production.up.railway.app/api/categories/*',
            //'https://coursesplatformbackend-production.up.railway.app/api/cart/*',
            'https://coursesplatformbackend-production.up.railway.app/api/delete/cart',
            'https://coursesplatformbackend-production.up.railway.app/api/getSalesByCategory/',
            'https://coursesplatformbackend-production.up.railway.app/api/cart/*',
            'https://coursesplatformbackend-production.up.railway.app/api/cart/',
            'https://coursesplatformbackend-production.up.railway.app/api/cart',
            'https://coursesplatformbackend-production.up.railway.app/api/delete/cart/*',
            'https://coursesplatformbackend-production.up.railway.app/api/delete/cart/',
            'https://coursesplatformbackend-production.up.railway.app/api/accordion/',
            'https://coursesplatformbackend-production.up.railway.app/api/accordion/*',
            'https://coursesplatformbackend-production.up.railway.app/api/accordion/upload'








        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
