<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class JWTMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            
            if($e instanceof TokenInvalidException){
                return response()->json(['estado' => 'Token invalido'],401);
            }

            if($e instanceof TokenExpiredException){
                return response()->json(['estado' => 'El token expiró'],401);
            }

            return response()->json(['estado' => 'Token no encontrado'],401);

        }
        return $next($request);
    }
}
