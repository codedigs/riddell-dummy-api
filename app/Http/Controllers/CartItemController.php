<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ClientInformation;
use App\Transformers\CartItemTransformer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;

class CartItemController extends Controller
{
    public function submit(Request $request)
    {

    }

    /**
     * Get items of cart
     *
     * Dependency
     *  - Authenticate Middleware
     *  - Cart Middleware
     *
     * @param Request $request
     */
    public function getCartItems(Request $request)
    {
        $user = $request->user();
        $currentCart = $user->getCurrentCart();

        $cartItems = transformer($currentCart->cart_items, new CartItemTransformer)->toArray();

        return response()->json([
            'success' => true,
            'data' => $cartItems['data']
        ]);
    }

    /**
     * Get items of cart
     *
     * Dependency
     *  - Authenticate Middleware
     *  - Cart Middleware
     *  - CartItem Middleware
     *
     * @param Request $request
     */
    public function show(Request $request, $cart_item_id)
    {
        $cartItem = CartItem::find($cart_item_id);
        $itemStatus = $cartItem->getStatus();

        $cartItemData = $cartItem->toArray();
        $cartItemData['status'] = $itemStatus;
        $cartItemData['customizer_url'] = $cartItem->getCustomizerUrl();
        $cartItemData['client_information'] = $cartItem->client_information;

        unset($cartItemData['is_approved']);
        unset($cartItemData['has_change_request']);
        unset($cartItemData['has_pending_approval']);
        unset($cartItemData['cart_id']);
        unset($cartItemData['created_at']);
        unset($cartItemData['updated_at']);

        if (!is_null($cartItemData['client_information']))
        {
            unset($cartItemData['client_information']['id']);
            unset($cartItemData['client_information']['cart_item_id']);
            unset($cartItemData['client_information']['created_at']);
            unset($cartItemData['client_information']['updated_at']);
        }

        return response()->json([
            'success' => true,
            'data' => $cartItemData
        ]);
    }

    /**
     * Add item to cart
     *
     * Dependency
     *  - Authenticate Middleware
     *  - Cart Middleware
     *
     * Data available
     * - cut_id
     * - style_id (optional)
     * - design_id (optional)
     * - is_approved (optional)
     * - has_change_request (optional)
     * - has_pending_approval (optional)
     *
     * @param Request $request
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $currentCart = $user->getCurrentCart();

        $params = $request->all();

        $validator = Validator::make($params, [
            'cut_id' => "required|numeric|digits_between:1,20",
            'style_id' => "numeric|digits_between:1,20",
            'design_id' => "numeric|digits_between:1,20",
            'is_approved' => "boolean",
            'has_change_request' => "boolean",
            'has_pending_approval' => "boolean"
        ]);

        if ($validator->fails())
        {
            return $this->respondWithErrorMessage($validator);
        }

        $result = $currentCart->cart_items()->create([
            'cut_id' => $params['cut_id'],
            'style_id' => isset($params['style_id']) ? $params['style_id'] : null,
            'design_id' => isset($params['design_id']) ? $params['design_id'] : null,
            'is_approved' => isset($params['is_approved']) ? $params['is_approved'] : 0,
            'has_change_request' => isset($params['has_change_request']) ? $params['has_change_request'] : 0,
            'has_pending_approval' => isset($params['has_pending_approval']) ? $params['has_pending_approval'] : 0
        ]);

        return response()->json(
            $result instanceof CartItem ?
            [
               'success' => true,
               'message' => "Successfully create cart item",
               'new_cart_item_id' => $result->id
            ] :
            [
               'success' => false,
               'message' => "Cannot create cart item this time. Please try again later."
            ]
        );
    }

    /**
     * Update cut id
     *
     * Dependency
     *  - Authenticate Middleware
     *  - Cart Middleware
     *  - CartItem Middleware
     *
     * Data available
     * - cut_id
     *
     * @param Request $request
     */
    public function updateCutId(Request $request, $cart_item_id)
    {
        $params = $request->all();

        $validator = Validator::make($params, [
            'cut_id' => "required|numeric|digits_between:1,20"
        ]);

        if ($validator->fails())
        {
            return $this->respondWithErrorMessage($validator);
        }

        $cartItem = CartItem::find($cart_item_id);
        $cartItem->cut_id = $params['cut_id'];

        return response()->json(
            $cartItem->save() ?
            [
                'success' => true,
                'message' => "Successfully update cut id"
            ] :
            [
                'success' => false,
                'message' => "Cannot update cut id this time. Please try again later."
            ]
        );
    }

    /**
     * Update style id
     *
     * Dependency
     *  - Authenticate Middleware
     *  - Cart Middleware
     *  - CartItem Middleware
     *
     * Data available
     * - style_id
     *
     * @param Request $request
     */
    public function updateStyleId(Request $request, $cart_item_id)
    {
        $params = $request->all();

        $validator = Validator::make($params, [
            'style_id' => "required|numeric|digits_between:1,20"
        ]);

        if ($validator->fails())
        {
            return $this->respondWithErrorMessage($validator);
        }

        $cartItem = CartItem::find($cart_item_id);
        $cartItem->style_id = $params['style_id'];

        return response()->json(
            $cartItem->save() ?
            [
                'success' => true,
                'message' => "Successfully update style id"
            ] :
            [
                'success' => false,
                'message' => "Cannot update style id this time. Please try again later."
            ]
        );
    }

    /**
     * Update design id
     *
     * Dependency
     *  - Authenticate Middleware
     *  - Cart Middleware
     *  - CartItem Middleware
     *
     * Data available
     * - design_id
     *
     * @param Request $request
     */
    public function updateDesignId(Request $request, $cart_item_id)
    {
        $params = $request->all();

        $validator = Validator::make($params, [
            'design_id' => "required|numeric|digits_between:1,20"
        ]);

        if ($validator->fails())
        {
            return $this->respondWithErrorMessage($validator);
        }

        $cartItem = CartItem::find($cart_item_id);
        $cartItem->design_id = $params['design_id'];

        return response()->json(
            $cartItem->save() ?
            [
                'success' => true,
                'message' => "Successfully update design id"
            ] :
            [
                'success' => false,
                'message' => "Cannot update design id this time. Please try again later."
            ]
        );
    }

    /**
     * Update thumbnails
     *
     * Dependency
     *  - Authenticate Middleware
     *  - Cart Middleware
     *  - CartItem Middleware
     *
     * Data available
     * - front_image
     * - back_image
     * - left_image
     * - right_image
     *
     * @param Request $request
     */
    public function updateThumbnails(Request $request, $cart_item_id)
    {
        $params = $request->all();

        $validator = Validator::make($params, [
            'front_image' => "required|url|max:255",
            'back_image' => "required|url|max:255",
            'left_image' => "required|url|max:255",
            'right_image' => "required|url|max:255",
        ]);

        if ($validator->fails())
        {
            return $this->respondWithErrorMessage($validator);
        }

        $cartItem = CartItem::find($cart_item_id);
        $cartItem->front_image = $params['front_image'];
        $cartItem->back_image = $params['back_image'];
        $cartItem->left_image = $params['left_image'];
        $cartItem->right_image = $params['right_image'];

        return response()->json(
            $cartItem->save() ?
            [
                'success' => true,
                'message' => "Successfully update thumbnails"
            ] :
            [
                'success' => false,
                'message' => "Cannot update thumbnails this time. Please try again later."
            ]
        );
    }

    /**
     * Update roster
     *
     * Dependency
     *  - Authenticate Middleware
     *  - Cart Middleware
     *  - CartItem Middleware
     *
     * Data available
     * - roster
     *
     * @param Request $request
     */
    public function updateRoster(Request $request, $cart_item_id)
    {
        $params = $request->all();

        $validator = Validator::make($params, [
            'roster' => "required|json",
        ]);

        if ($validator->fails())
        {
            return $this->respondWithErrorMessage($validator);
        }

        $cartItem = CartItem::find($cart_item_id);
        $cartItem->roster = $params['roster'];

        return response()->json(
            $cartItem->save() ?
            [
                'success' => true,
                'message' => "Successfully update roster"
            ] :
            [
                'success' => false,
                'message' => "Cannot update roster this time. Please try again later."
            ]
        );
    }

    /**
     * Update application size
     *
     * Dependency
     *  - Authenticate Middleware
     *  - Cart Middleware
     *  - CartItem Middleware
     *
     * Data available
     * - application_size
     *
     * @param Request $request
     */
    public function updateApplicationSize(Request $request, $cart_item_id)
    {
        $params = $request->all();

        $validator = Validator::make($params, [
            'application_size' => "required|json",
        ]);

        if ($validator->fails())
        {
            return $this->respondWithErrorMessage($validator);
        }

        $cartItem = CartItem::find($cart_item_id);
        $cartItem->application_size = $params['application_size'];

        return response()->json(
            $cartItem->save() ?
            [
                'success' => true,
                'message' => "Successfully update application size"
            ] :
            [
                'success' => false,
                'message' => "Cannot update application size this time. Please try again later."
            ]
        );
    }

    /**
     * Update design status
     *
     * Dependency
     *  - Authenticate Middleware
     *  - Cart Middleware
     *  - CartItem Middleware
     *
     * Data available
     * - design_status
     *
     * @param Request $request
     */
    public function updateDesignStatus(Request $request, $cart_item_id)
    {
        $params = $request->all();

        $validator = Validator::make($params, [
            'design_status' => [
                "required",
                Rule::in(["incomplete", "configuration error", "complete"])
            ],
        ]);

        if ($validator->fails())
        {
            return $this->respondWithErrorMessage($validator);
        }

        $cartItem = CartItem::find($cart_item_id);
        $cartItem->design_status = $params['design_status'];

        return response()->json(
            $cartItem->save() ?
            [
                'success' => true,
                'message' => "Successfully update design status"
            ] :
            [
                'success' => false,
                'message' => "Cannot update design status this time. Please try again later."
            ]
        );
    }

    /**
     * Update pdf url
     *
     * Dependency
     *  - Authenticate Middleware
     *  - Cart Middleware
     *  - CartItem Middleware
     *
     * Data available
     * - pdf_url
     *
     * @param Request $request
     */
    public function updatePdfUrl(Request $request, $cart_item_id)
    {
        $params = $request->all();

        $validator = Validator::make($params, [
            'pdf_url' => "required|url|max:255",
        ]);

        if ($validator->fails())
        {
            return $this->respondWithErrorMessage($validator);
        }

        $cartItem = CartItem::find($cart_item_id);
        $cartItem->pdf_url = $params['pdf_url'];

        return response()->json(
            $cartItem->save() ?
            [
                'success' => true,
                'message' => "Successfully update pdf url"
            ] :
            [
                'success' => false,
                'message' => "Cannot update pdf url this time. Please try again later."
            ]
        );
    }

    /**
     * Update client information
     *
     * Dependency
     *  - Authenticate Middleware
     *  - Cart Middleware
     *  - CartItem Middleware
     *
     * Data available
     * - school_name
     * - first_name
     * - last_name
     * - email
     * - address_1
     * - address_2
     * - city
     * - state
     * - zip_code
     *
     * @param Request $request
     */
    public function updateClientInformation(Request $request, $cart_item_id)
    {
        $params = $request->all();

        $validator = Validator::make($params, ClientInformation::$rules);

        if ($validator->fails())
        {
            return $this->respondWithErrorMessage($validator);
        }

        $cartItem = CartItem::find($cart_item_id);

        if (is_null($cartItem->client_information))
        {
            // create client information
            $clientInformation = $cartItem->client_information()->save(new ClientInformation([
                'school_name' => isset($params['school_name']) ? $params['school_name'] : "",
                'first_name' => $params['first_name'],
                'last_name' => $params['last_name'],
                'email' => $params['email'],
                'business_phone' => $params['business_phone'],
                'address_1' => isset($params['address_1']) ? $params['address_1'] : "",
                'address_2' => isset($params['address_2']) ? $params['address_2'] : "",
                'city' => isset($params['city']) ? $params['city'] : "",
                'state' => isset($params['state']) ? $params['state'] : "",
                'zip_code' => isset($params['zip_code']) ? $params['zip_code'] : "",
                'approval_token' => ClientInformation::generateUniqueApprovalToken()
            ]));

            $saved = $clientInformation instanceof ClientInformation;
        }
        else
        {
            $clientInformation = $cartItem->client_information;

            // update client information
            $clientInformation->school_name = isset($params['school_name']) ? $params['school_name'] : "";
            $clientInformation->first_name = $params['first_name'];
            $clientInformation->last_name = $params['last_name'];
            $clientInformation->email = $params['email'];
            $clientInformation->business_phone = $params['business_phone'];
            $clientInformation->address_1 = isset($params['address_1']) ? $params['address_1'] : "";
            $clientInformation->address_2 = isset($params['address_2']) ? $params['address_2'] : "";
            $clientInformation->city = isset($params['city']) ? $params['city'] : "";
            $clientInformation->state = isset($params['state']) ? $params['state'] : "";
            $clientInformation->zip_code = isset($params['zip_code']) ? $params['zip_code'] : "";

            $saved = $clientInformation->save();
        }

        return response()->json(
            $saved ?
            [
                'success' => true,
                'approval_token' => $clientInformation->approval_token,
                'message' => "Successfully update client information"
            ] :
            [
                'success' => false,
                'message' => "Cannot update client information this time. Please try again later."
            ]
        );
    }

    public function getClientInformation(Request $request, $cart_item_id)
    {
        $cartItem = CartItem::find($cart_item_id);
        $data = $cartItem->client_information->toArray();

        unset($data['created_at']);
        unset($data['updated_at']);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Mark as pending approval
     *
     * Dependency
     *  - Authenticate Middleware
     *  - Cart Middleware
     *  - CartItem Middleware
     *
     * @param Request $request
     */
    public function markAsPendingApproval(Request $request, $cart_item_id)
    {
        $params = $request->all();

        $cartItem = CartItem::find($cart_item_id);

        return response()->json(
            $cartItem->markAsPendingApproval() ?
            [
                'success' => true,
                'message' => "Successfully mark as pending approval"
            ] :
            [
                'success' => false,
                'message' => "Cannot mark as pending approval this time. Please try again later."
            ]
        );
    }

    /**
     * Delete cart item
     *
     * Dependency
     *  - Authenticate Middleware
     *  - Cart Middleware
     *  - CartItem Middleware
     *
     * @param Request $request
     */
    public function delete(Request $request, $cart_item_id)
    {
        $cartItem = CartItem::find($cart_item_id);
        $is_deleted = $cartItem->delete();

        return response()->json(
            $is_deleted ?
            [
                'success' => true,
                'message' => "Successfully delete item"
            ] :
            [
                'success' => false,
                'message' => "Cannot delete item this time. Please try again later."
            ]
        );
    }

    /**
     * Delete cart item by line item id
     *
     * Dependency
     *  - Authenticate Middleware
     *  - Cart Middleware
     *  - CartItem Middleware
     *
     * @param Request $request
     */
    public function deleteByLineItemId(Request $request, $line_item_id)
    {
        $cartItem = CartItem::findBy('line_item_id', $line_item_id)->first();
        $is_deleted = $cartItem->delete();

        return response()->json(
            $is_deleted ?
            [
                'success' => true,
                'message' => "Successfully delete item"
            ] :
            [
                'success' => false,
                'message' => "Cannot delete item this time. Please try again later."
            ]
        );
    }
}
