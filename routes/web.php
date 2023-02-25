<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

use Illuminate\Support\Facades\DB;

$router->get('/steam_id/{id}', function ($id) {
    $data = app()->make('db')->table('translate')
                 ->where('steam_id', $id)
                 ->select('guid')
                 ->first();

    if (is_null($data)) {
        return response('Not found', 404);
    }

    return response()->json([
        'guid' => $data->guid
    ]);
});

$router->get('/guid/{id}', function ($id) {
    $data = app()->make('db')->table('translate')
                 ->where('guid', $id)
                 ->select('steam_id')
                 ->first();

    if (is_null($data)) {
        return response('Not found', 404);
    }

    return response()->json([
        'steam_id' => $data->steam_id
    ]);
});
