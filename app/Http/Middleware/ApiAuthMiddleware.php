<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\jwtAuth;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Log;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $token = $request->header('Authorization'); //El token en el header de la solicitud
        // Log::info($token);
        $jwtAuth = new jwtAuth();
        $checkToken = $jwtAuth->checkToken($token); //Devuelve un true or false

        if ($checkToken) {
            return $next($request);
        } else {
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'The user is not authenticated'
            );

            return response()->json($data, $data['code']); //la respuesta JSON
        }
    }
}
