<?php

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

$router->get('/', function () use ($router) {
    // return $router->app->version();
    return view('tests/user.twig', ["test" => "ahah!"]);
});

$router->get('/customer', function () use ($router) {
    // return $router->app->version();
    return view('tests/customer.twig', ["test" => "ahah!"]);
});

$router->get('/test', 'ExampleController@test');

$router->get('/ot', 'ExampleController@t2');
