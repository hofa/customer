<?php

$router->group(['prefix' => 'gm', 'namespace' => 'GM'], function () use ($router) {
    $router->group(['prefix' => 'auth'], function () use ($router) {
        $router->post('login', 'AuthController@login');
        $router->post('logout', 'AuthController@logout');
        $router->post('refresh', 'AuthController@refresh');
        // $router->post('me', 'AuthController@me');
    });

    $router->group(['prefix' => 'merchant'], function () use ($router) {
        $router->post('account', 'MerchantController@doCreate');
        $router->put('account/{id}', 'MerchantController@doUpdate');
        $router->patch('account/{id}/password', 'MerchantController@doUpdatePassword');

        $router->get('account/{id}', 'MerchantController@detail');
        $router->get('account', 'MerchantController@account');
    });
});
