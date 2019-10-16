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
    'middleware' => ["auth", "cart", "valid_to_use_cart"]
], function() use($router) {
    $router->put("save", "CartController@save");
    $router->post("submit", "CartController@submit");
});

// approval
$router->group([
    'prefix' => "approval",
    'middleware' => ["approval", "approval_cart_item"]
], function() use($router) {
    $router->get("client-information", "ApprovalController@getClientInformation");

    $router->put("update-roster", ['middleware' => "valid_to_user_cart_for_non_session", 'uses' => "ApprovalController@updateRoster"]);
    $router->put("update-client-information", ['middleware' => "valid_to_user_cart_for_non_session", 'uses' => "ApprovalController@updateClientInformation"]);
    $router->put("update-signature-image", ['middleware' => "valid_to_user_cart_for_non_session", 'uses' => "ApprovalController@updateSignatureImage"]);
    $router->put("approved", ['middleware' => "valid_to_user_cart_for_non_session", 'uses' => "ApprovalController@markAsApproved"]);
});

// cart items
$router->group([
    'prefix' => "carts/items",
    'middleware' => ["auth", "cart"]
], function() use($router) {
    $router->get("/", "CartItemController@getCartItems");
    $router->post("add", ['middleware' => "valid_to_use_cart", 'uses' => "CartItemController@store"]);

    $router->get("{cart_item_id:[\d]+}", ['middleware' => "cart_item", 'uses' => "CartItemController@show"]);
    $router->put("{cart_item_id:[\d]+}/update-cut-id", ['middleware' => ["valid_to_use_cart", "cart_item"], 'uses' => "CartItemController@updateCutId"]);
    $router->put("{cart_item_id:[\d]+}/update-style-id", ['middleware' => ["valid_to_use_cart", "cart_item"], 'uses' => "CartItemController@updateStyleId"]);
    $router->put("{cart_item_id:[\d]+}/update-design-id", ['middleware' => ["valid_to_use_cart", "cart_item"], 'uses' => "CartItemController@updateDesignId"]);
    $router->put("{cart_item_id:[\d]+}/update-thumbnails", ['middleware' => ["valid_to_use_cart", "cart_item"], 'uses' => "CartItemController@updateThumbnails"]);
    $router->put("{cart_item_id:[\d]+}/update-roster", ['middleware' => ["valid_to_use_cart", "cart_item"], 'uses' => "CartItemController@updateRoster"]);
    $router->put("{cart_item_id:[\d]+}/update-application-size", ['middleware' => ["valid_to_use_cart", "cart_item"], 'uses' => "CartItemController@updateApplicationSize"]);
    $router->put("{cart_item_id:[\d]+}/update-design-status", ['middleware' => ["valid_to_use_cart", "cart_item"], 'uses' => "CartItemController@updateDesignStatus"]);
    $router->put("{cart_item_id:[\d]+}/update-pdf-url", ['middleware' => ["valid_to_use_cart", "cart_item"], 'uses' => "CartItemController@updatePdfUrl"]);

    $router->get("{cart_item_id:[\d]+}/client-information", ['middleware' => "cart_item", 'uses' => "CartItemController@getClientInformation"]);
    $router->put("{cart_item_id:[\d]+}/update-client-information", ['middleware' => ["valid_to_use_cart", "cart_item"], 'uses' => "CartItemController@updateClientInformation"]);

    $router->put("{cart_item_id:[\d]+}/pending-approval", ['middleware' => ["valid_to_use_cart", "cart_item"], 'uses' => "CartItemController@markAsPendingApproval"]);
    $router->put("{cart_item_id:[\d]+}/incomplete", ['middleware' => ["valid_to_use_cart", "cart_item"], 'uses' => "CartItemController@markAsIncomplete"]);

    $router->delete("{cart_item_id:[\d]+}/delete", ['middleware' => ["valid_to_use_cart", "cart_item"], 'uses' => "CartItemController@delete"]);

    // change logs
    $router->get("{cart_item_id:[\d]+}/changes-logs", ['middleware' => "cart_item", 'uses' => "CartItemController@getAllLogs"]);
    $router->get("{cart_item_id:[\d]+}/change-requested", ['middleware' => "cart_item", 'uses' => "CartItemController@getChangeRequested"]);
    $router->post("{cart_item_id:[\d]+}/fix-changes", ['middleware' => ["valid_to_use_cart", "cart_item"], 'uses' => "CartItemController@fixChanges"]);
});

// changes logs
$router->group([
    'prefix' => "changes-logs",
    'middleware' => ["approval", "approval_cart_item"]
], function() use($router) {
    $router->get("/", "ChangeLogController@getAll");
    $router->post("ask-for-changes", ['middleware' => "valid_to_user_cart_for_non_session", 'uses' => "ChangeLogController@askForChanges"]);
    $router->post("quick-edit", ['middleware' => "valid_to_user_cart_for_non_session", 'uses' => "ChangeLogController@logQuickEdit"]);
});

// users
$router->group([
    'prefix' => "auth-user",
    'middleware' => "auth"
], function() use($router) {
    $router->get("/", "AuthUserController@getAuthenticatedUser");
    $router->get("cart", "AuthUserController@getCurrentCart");
});

$router->get("/cuts", "CutController@getAll");
$router->get("/cuts/{cut_id:[\d]+}/styles", "StyleController@getStylesByCutId");

// states
$router->group([
    'prefix' => "states"
], function() use($router) {
    $router->get("/", "ZipCodeController@getStates");
    $router->get("{state_code}/cities", "ZipCodeController@getCitiesByStateCode");
    $router->get("{state_code}/cities/{city}/zip-codes", "ZipCodeController@getZipCodesByStateCodeAndCity");
});
