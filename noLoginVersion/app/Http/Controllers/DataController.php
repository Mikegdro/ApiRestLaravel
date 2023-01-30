<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class DataController extends Controller {
    public function getData() {

        $response = $this->fetchData();

        $data = $this->parseData($response);

        return response()->json($data);
    }

    function fetchData() {
        $lat = '37.16147109102704';
        $lng = '-3.5912354132361344';
        $date = Carbon::now()->format('Y-m-d');
        $url = sprintf("https://api.sunrise-sunset.org/json?lat=%s&lng=%s&date=%s", $lat, $lng, $date);

        return Http::get($url);
    }

    function parseData($response) {
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

        return array(
            "interpolados" => $valoresInterpolados,
            "sunrise" => $sunrise,
            "sunset" => $sunset,
            "fullJson" => $sunData,
        );
    }
}
