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
    return view("home");
});

// todo: limit login attempt
$router->post("/login", "LoginController@login");

$router->group([
    'prefix' => "carts/cart-items",
    'middleware' => ["auth", "cart"]
], function() use($router) {
    $router->get("/", "CartItemController@getCartItems");
});

$router->group([
    'prefix' => "cuts",
    'middleware' => ["auth"]
], function() use($router) {
    $router->get("/", "CutController@getCuts");
});

$router->group([
    'prefix' => "styles",
    'middleware' => ["auth"]
], function() use($router) {
    $router->get("/", "StyleController@getStyles");
});
