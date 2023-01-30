<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;

class TokenValidator
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next) {
        //Comprueba si esta el token en la cabecera
        if( $request->headers->has('auth-token' ) ){
            try {
                //Quitamos el "Bearer" del token
                $token = substr($request->headers->get('auth-token'), 7);

                //Decodifica el token
                $decoded = JWT::decode($token, new Key(env("PRIVATE_KEY"), 'HS256'));
                $decoded = (array) $decoded;

                //Comprueba si ha expirado con la fecha del payload
                if( !$decoded['expire'] > Carbon::now() ) {
                    return response()->json(['error' => 'Tu Token ha Expirado'], 401);
                }

            } catch (\Exception $e) {
                return response()->json(['error' => 'Tu token es inválido'], 401);
            }
        } else {
            return response()->json(['error' => 'Debes aportar una API Key para acceder'], 401);
        }

        return $next($request);
    }
}
