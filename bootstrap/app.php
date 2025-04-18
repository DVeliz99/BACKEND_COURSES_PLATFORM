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

            'api/register',
            'api/login',
            'api/user/update',
            'api/user/upload',
            'api/user/avatar',
            'api/user/detail',
            'api/course/*',
            'api/getCoursesByCategory',
            'api/getCourse/*',
            'api/course/upload',
            'api/course/search/*',
            'api/video/*',
            'api/videos',
            'api/video/course',
            'api/video/course2',
            'api/video/update_title',
            'api/sale',
            'api/sale/checkbox/*',
            'api/myCourses',
            'api/comments/*',
            'api/comments/upload',
            'api/comments/image/*',
            'api/replies/*',
            'api/replies/',
            'api/replies/upload',
            'api/replies/image/',
            'api/checkbox/*',
            'api/categories/*',
            'api/delete/cart',
            'api/cart/*',
            'api/accordion/*',
            'api/accordion/upload',
            '/api/checkbox/*',
            '/api/checkbox/'






        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
