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
// $router->post("login", "LoginController@login");

// carts
$router->group([
    'prefix' => "carts",
    'middleware' => ["auth", "cart"]
], function() use($router) {
    $router->put("/sync-to-hybris", "CartController@syncToHybris");
});

// cart items
$router->group([
    'prefix' => "carts/items",
    'middleware' => ["auth", "cart"]
], function() use($router) {
    $router->get("/", "CartItemController@getCartItems");
    $router->get("{cart_item_id:[\d]+}", ['middleware' => "cart_item", 'uses' => "CartItemController@show"]);
    $router->post("add", "CartItemController@store");

    $router->put("{cart_item_id:[\d]+}/update-cut-id", ['middleware' => "cart_item", 'uses' => "CartItemController@updateCutId"]);
    $router->put("{cart_item_id:[\d]+}/update-style-id", ['middleware' => "cart_item", 'uses' => "CartItemController@updateStyleId"]);
    $router->put("{cart_item_id:[\d]+}/update-design-id", ['middleware' => "cart_item", 'uses' => "CartItemController@updateDesignId"]);
    $router->put("{cart_item_id:[\d]+}/update-thumbnails", ['middleware' => "cart_item", 'uses' => "CartItemController@updateThumbnails"]);
    $router->put("{cart_item_id:[\d]+}/update-roster", ['middleware' => "cart_item", 'uses' => "CartItemController@updateRoster"]);
    $router->put("{cart_item_id:[\d]+}/update-application-size", ['middleware' => "cart_item", 'uses' => "CartItemController@updateApplicationSize"]);
    $router->put("{cart_item_id:[\d]+}/update-design-status", ['middleware' => "cart_item", 'uses' => "CartItemController@updateDesignStatus"]);
    $router->put("{cart_item_id:[\d]+}/update-pdf-url", ['middleware' => "cart_item", 'uses' => "CartItemController@updatePdfUrl"]);
    $router->put("{cart_item_id:[\d]+}/update-signature-image", ['middleware' => "cart_item", 'uses' => "CartItemController@updateSignatureImage"]);

    $router->get("{cart_item_id:[\d]+}/client-information", ['middleware' => "cart_item", 'uses' => "CartItemController@getClientInformation"]);
    $router->put("{cart_item_id:[\d]+}/update-client-information", ['middleware' => "cart_item", 'uses' => "CartItemController@updateClientInformation"]);

    $router->put("{cart_item_id:[\d]+}/approved", ['middleware' => "cart_item", 'uses' => "CartItemController@approved"]);
    $router->put("{cart_item_id:[\d]+}/pending-approval", ['middleware' => "cart_item", 'uses' => "CartItemController@markAsPendingApproval"]);

    $router->delete("{cart_item_id:[\d]+}/delete", ['middleware' => "cart_item", 'uses' => "CartItemController@delete"]);
});

// coach request logs
$router->group([
    'prefix' => "carts/items/{cart_item_id:[\d]+}/logs",
    'middleware' => ["auth", "cart", "cart_item"]
], function() use($router) {
    $router->get("/", "CoachRequestLogController@getAll");
    $router->post("add", "CoachRequestLogController@store");
});

// users
$router->group([
    'prefix' => "auth-user",
    'middleware' => "auth"
], function() use($router) {
    $router->get("/", "AuthUserController@getAuthenticatedUser");
    $router->get("cart", "AuthUserController@getCurrentCart");
});

// $router->get("/cuts/{brand}", "CutController@getAllByBrand");
$router->get("/cuts", "CutController@getAll");
$router->get("/cuts/{cut_id:[\d]+}/styles", "StyleController@getStylesByCutId");
