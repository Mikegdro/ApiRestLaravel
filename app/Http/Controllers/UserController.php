<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;

class UserController extends Controller {
    public function login(Request $request) {
        $payload = $request->all();
        $payload['expire'] = Carbon::now()->addHour();

        $jwt = JWT::encode($payload, env('PRIVATE_KEY'), 'HS256');

        $jwt = "Bearer " . $jwt;

        $user = new User($request->all());
        $user->setAttribute('token', $jwt);

        return response()->json($jwt);
    }

}
