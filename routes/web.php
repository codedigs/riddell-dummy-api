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

// cuts
$router->group([
    'prefix' => "cuts",
    'middleware' => "auth"
], function() use($router) {
    $router->get("/", "CutController@getCuts");
});

// designs
$router->group([
    'prefix' => "styles",
    'middleware' => "auth"
], function() use($router) {
    $router->get("/", "StyleController@getStyles");
});

// carts
$router->group([
    'prefix' => "carts",
    'middleware' => "auth"
], function() use($router) {
    $router->post("/add-cart", "CartController@addCart");
});

// cart items
$router->group([
    'prefix' => "carts/items",
    'middleware' => ["auth", "cart"]
], function() use($router) {
    $router->get("/", "CartItemController@getCartItems");

    // $router->put("{cart_item_id:[\d]+}/update", ['middleware' => "cart_item", 'uses' => "CartItemController@updateBuilderCustomizationItem"]);
    // $router->delete("{cart_item_id:[\d]+}/delete", ['middleware' => "cart_item", 'uses' => "CartItemController@deleteToCart"]);
});

$router->get("/authenticated-user", [ // authenticated with cart
    'middleware' => ["auth", "cart"],
    'uses' => "GetterController@getAuthenticatedUser"
]);
