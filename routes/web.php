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
$router->get('/', function() use ($router) {
    return "Lumen RESTful API";
});
 
$router->group(['prefix' => '/v1'], function($router){
    $router->get('customer','CustomerController@index');
    $router->get('customer/{id}','CustomerController@getCustomer');
    $router->post('customer','CustomerController@createCustomer');
    $router->put('customer/{id}','CustomerController@updateCustomer');
    $router->delete('customer/{id}','CustomerController@deleteCustomer');
    
    $router->post('transaction/{id}','TransactionController@createTransaction');
    $router->get('transaction/','TransactionController@raportTransaction');
});