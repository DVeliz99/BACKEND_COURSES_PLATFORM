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

            'https://courses-platform-frontend-nine.vercel.app/api/register',
            'https://courses-platform-frontend-nine.vercel.app/api/login',
            'https://courses-platform-frontend-nine.vercel.app/api/user/update',
            'https://courses-platform-frontend-nine.vercel.app/api/user/upload',
            'https://courses-platform-frontend-nine.vercel.app/api/user/avatar',
            'https://courses-platform-frontend-nine.vercel.app/api/user/detail',
            'https://courses-platform-frontend-nine.vercel.app/api/course/', //POST Y GET

            'https://courses-platform-frontend-nine.vercel.app/api/course/*', //Para rutas dinamicas 
            'https://courses-platform-frontend-nine.vercel.app/api/getCoursesByCategory',
            'https://courses-platform-frontend-nine.vercel.app/api/getCourse/',
            'https://courses-platform-frontend-nine.vercel.app/api/course/upload',
            'https://courses-platform-frontend-nine.vercel.app/api/course/search/', //
            'https://courses-platform-frontend-nine.vercel.app/api/video/',
            'https://courses-platform-frontend-nine.vercel.app/api/video/doc',
            'https://courses-platform-frontend-nine.vercel.app/api/video',
            'https://courses-platform-frontend-nine.vercel.app/api/video/*',
            'https://courses-platform-frontend-nine.vercel.app/api/videos',
            'https://courses-platform-frontend-nine.vercel.app/api/video/course',
            'https://courses-platform-frontend-nine.vercel.app/api/video/course',
            'https://courses-platform-frontend-nine.vercel.app/api/video/course2',
            'https://courses-platform-frontend-nine.vercel.app/api/video/update_title',
            'https://courses-platform-frontend-nine.vercel.app/api/sale',
            'https://courses-platform-frontend-nine.vercel.app/api/sale/checkbox/*',
            'https://courses-platform-frontend-nine.vercel.app/api/getCoursesByCategory',
            'https://courses-platform-frontend-nine.vercel.app/api/myCourses',
            'https://courses-platform-frontend-nine.vercel.app/api/comments/*',
            'https://courses-platform-frontend-nine.vercel.app/api/comments/',
            'https://courses-platform-frontend-nine.vercel.app/api/comments/upload',
            'https://courses-platform-frontend-nine.vercel.app/api/comments/image/',
            'https://courses-platform-frontend-nine.vercel.app/api/replies/',
            'https://courses-platform-frontend-nine.vercel.app/api/replies/*',
            'https://courses-platform-frontend-nine.vercel.app/api/replies/upload',
            'https://courses-platform-frontend-nine.vercel.app/api/replies/image/',
            'https://courses-platform-frontend-nine.vercel.app/api/checkbox/',
            'https://courses-platform-frontend-nine.vercel.app/api/checkbox/*',

            'https://courses-platform-frontend-nine.vercel.app/api/categories',
            'https://courses-platform-frontend-nine.vercel.app/api/categories/*',
            //'https://courses-platform-frontend-nine.vercel.app/api/cart/*',
            'https://courses-platform-frontend-nine.vercel.app/api/delete/cart',
            'https://courses-platform-frontend-nine.vercel.app/api/getSalesByCategory/',
            'https://courses-platform-frontend-nine.vercel.app/api/cart/*',
            'https://courses-platform-frontend-nine.vercel.app/api/cart/',
            'https://courses-platform-frontend-nine.vercel.app/api/cart',
            'https://courses-platform-frontend-nine.vercel.app/api/delete/cart/*',
            'https://courses-platform-frontend-nine.vercel.app/api/delete/cart/',
            'https://courses-platform-frontend-nine.vercel.app/api/accordion/',
            'https://courses-platform-frontend-nine.vercel.app/api/accordion/*',
            'https://courses-platform-frontend-nine.vercel.app/api/accordion/upload'








        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
