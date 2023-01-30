<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

class ApiDataController extends Controller {

    function __construct() {
        $this->middleware('auth:api')->only(["protected"]);
    }

    function index() {
        return view('welcome');
    }

    function login(Request $request) {
        $request = Request::create('/api/login', 'POST', [
            'email' => $request['email'],
            'password' => Hash::make($request['password'])
        ]);

        $response = Route::dispatch($request);
        $response = $response->getContent();

        return view('welcome', [
            'request' => $response
        ]);
    }

    function logout() {
        $user = Auth::user();

        $request = Request::create('/api/logout', 'GET');

        $response = Route::dispatch($request);
        $response = $response->getContent();

        dd($response);
    }
}
