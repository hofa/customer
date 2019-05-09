<?php
// use Illuminate\Support\Facades\Route;
// Route::group([
//     'prefix' => 'gm/auth',
// ], function ($router) {
//     Route::post('login', 'GM\AuthController@login');
//     Route::post('logout', 'GM\AuthController@logout');
//     Route::post('refresh', 'GM\AuthController@refresh');
//     Route::post('me', 'GM\AuthController@me');
// });

// $router->group(['perfix' => 'gm/auth'], function () use ($router) {
//     $router->post('login', 'GM\AuthController@login');
//     $router->post('logout', 'GM\AuthController@logout');
//     $router->post('refresh', 'GM\AuthController@refresh');
//     $router->post('me', 'GM\AuthController@me');
// });

// $router->post('login', 'GM\AuthController@login');
// $router->get('user', 'ExampleController@getUser');

$router->group(['prefix' => 'gm', 'namespace' => 'GM'], function () use ($router) {
    $router->group(['prefix' => 'auth'], function () use ($router) {
        $router->post('login', 'AuthController@login');
        $router->post('logout', 'AuthController@logout');
        $router->post('refresh', 'AuthController@refresh');
        $router->post('me', 'AuthController@me');
    });
});
