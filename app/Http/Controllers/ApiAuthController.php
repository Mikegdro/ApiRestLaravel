<?php

namespace App\Http\Controllers;

use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class ApiAuthController extends Controller {

    function __construct() {
         $this->middleware('auth:api')->only(['protected']);
    }

    function login(Request $request) {

        $credentials = request(['email', 'password']);

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user = Auth::user();

        $tokenResult = $user->createToken('Access Token');

        $token = $tokenResult->token;

        $token->save();

        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse($token->expires_at)->toDateTimeString()
        ]);

    }

    function logout(Request $request) {
        dd(Auth::user()->token());
        $request->user()->token()->revoke();
        return response()->json(['Message' => 'Logged out']);
    }

    function getData() {
        $lat = '37.16147109102704';
        $lng = '-3.5912354132361344';
        $date = Carbon::now()->format('Y-m-d');
        $url = sprintf("https://api.sunrise-sunset.org/json?lat=%s&lng=%s&date=%s", $lat, $lng, $date);

        $response = Http::get($url);

        $sunData = $response->json();
        if(!isset($sunData['results']['sunset'])) {
            return response()->json(['message' => 'External API Error'], 500);
        }

        if(!isset($sunData['results']['sunrise'])) {
            return response()->json(['message' => 'External API Error'], 500);
        }

        $sunrise = new Carbon(date('H:i:s', strtotime($sunData['results']['sunrise'])));
        $sunset = new Carbon(date('H:i:s', strtotime($sunData['results']['sunset'])));

        $carbonSunrise = $sunrise->hour + ($sunrise->minute / 60);
        $carbonSunset = $sunset->hour + ($sunset->minute / 60);

        //g rojo a naranja t verde c azul
        $currentHour = Carbon::now()->hour + 1;
        $currentMinutes = Carbon::now()->minute;

        $currentTime = $currentHour + $currentMinutes/60;

        //Así se interpolan los valores
        $valoresInterpolados = ($currentTime - $carbonSunrise) / ($carbonSunset - $carbonSunrise);

        return response()->json([
            "interpolados" => $valoresInterpolados,
            "sunrise" => $sunrise,
            "sunset" => $sunset,
            "fullJson" => $sunData,
        ]);
    }

    function protected() {
        return response()->json(['user' => Auth::user()], 200);
    }

    function register(Request $request) {
        try {
            $password = Hash::make($request->password);

            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $password
            ]);
        } catch(\Exception $e) {
            return response()->json(['message' => 'Unauthorized', 'error' => $e], 401);
        }

        return response()->json(['message' => 'User now created']);
    }

}
