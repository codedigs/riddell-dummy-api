<?php

namespace App\Http\Controllers;

use App\Api\Prolook\StyleApi;
use App\Api\Qx7\GroupCutApi;
use App\Api\Riddell\CartApi;
use App\Api\Riddell\CartItemApi;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ChangeLog;
use App\Models\ClientInformation;
use App\Transformers\CartItemTransformer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Log;
use Validator;

class CartItemController extends Controller
{
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
        $currentCart = Cart::findBy('pl_cart_id', $user->current_pl_cart_id)->first();

        $query = $currentCart->cart_items();
        $this->enableOptions($query);

        $cartItems = transformer($query->get(), new CartItemTransformer)->toArray();

        return response()->json([
            'success' => true,
            'is_cart_available' => !$currentCart->isCompleted(),
            'ready_to_submit' => $currentCart->areAllItemsApproved(),
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
        $currentCart = $cartItem->cart;

        $itemStatus = $cartItem->getStatus();

        $cartItemData = $cartItem->toArray();
        $cartItemData['line_item_id'] = $cartItem->line_item_id;

        $cartItemData['front_image'] = $cartItem->getFrontThumbnail();
        $cartItemData['back_image'] = $cartItem->getBackThumbnail();
        $cartItemData['left_image'] = $cartItem->getLeftThumbnail();
        $cartItemData['right_image'] = $cartItem->getRightThumbnail();

        $cartItemData['status'] = $itemStatus;
        $cartItemData['customizer_url'] = $cartItem->getCustomizerUrl();
        $cartItemData['client_information'] = $cartItem->client_information;

        unset($cartItemData['is_approved']);
        unset($cartItemData['has_change_request']);
        unset($cartItemData['has_pending_approval']);
        unset($cartItemData['cart_id']);
        unset($cartItemData['created_at']);
        unset($cartItemData['updated_at']);
        unset($cartItemData['cart']);

        if (!is_null($cartItem->getCutId()))
        {
            $groupCutApi = new GroupCutApi;
            $groupCutResult = $groupCutApi->getById($cartItem->cut_id);

            if ($groupCutResult->success)
            {
                $groupCut = $groupCutResult->master_block_pattern_group;

                $cartItemData['group_cut'] = [
                    'id' => $groupCut->id,
                    'name' => $groupCut->name,
                    'image' => !is_null($groupCut->thumbnail) ? $groupCut->thumbnail : "/riddell/img/Cuts/cut-7.png"
                ];
            }
        }

        $styleApi = new StyleApi;
        if (!is_null($cartItem->getStyleId()))
        {
            $style = $styleApi->getInfo($cartItem->getStyleId());

            if ($style->success)
            {
                $material = $style->material;

                $cartItemData['style'] = [
                    'id' => $material->id,
                    'name' => $material->name,
                    'image' => !empty($material->thumbnail_path) ? $material->thumbnail_path : "/riddell/img/Football-Picker/Inspiration@2x.png"
                ];
            }
        }

        if (!is_null($cartItemData['client_information']))
        {
            unset($cartItemData['client_information']['id']);
            unset($cartItemData['client_information']['cart_item_id']);
            unset($cartItemData['client_information']['created_at']);
            unset($cartItemData['client_information']['updated_at']);
        }

        if ($cartItem->isReversible() && !is_null($cartItem->side2))
        {
            $cartItemData['side2'] = $cartItem->side2;

            $style = $styleApi->getInfo($cartItemData['side2']['style_id']);

            if ($style->success)
            {
                $material = $style->material;

                $cartItemData['side2']['style'] = [
                    'id' => $material->id,
                    'name' => $material->name,
                    'image' => !empty($material->thumbnail_path) ? $material->thumbnail_path : "/riddell/img/Football-Picker/Inspiration@2x.png"
                ];
            }

            unset($cartItemData['side2']['id']);
            unset($cartItemData['side2']['cart_item_id']);
            unset($cartItemData['side2']['created_at']);
            unset($cartItemData['side2']['updated_at']);
            unset($cartItemData['side2']['deleted_at']);
        }

        return response()->json([
            'success' => true,
            'is_cart_available' => !$currentCart->isCompleted(),
            'ready_to_submit' => $currentCart->areAllItemsApproved(),
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
        $currentCart = Cart::findBy('pl_cart_id', $user->current_pl_cart_id)->first();

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

        $is_created = $result instanceof CartItem;

        if ($is_created)
        {
            // sync item to hybris
            $cartItemApi = new CartItemApi($user->hybris_access_token);
            $syncingResult = $cartItemApi->syncItem($user->current_pl_cart_id, $params['cut_id']);

            if ($syncingResult->success)
            {
                $result->saveLineItemId($syncingResult->line_item_id);
            }
        }

        return response()->json(
            $is_created ?
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
        $cartItem = CartItem::find($cart_item_id);
        $has_history_of_changes = $cartItem->changes_logs->isNotEmpty();

        // block request if coach has changes
        if ($has_history_of_changes)
        {
            return response()->json([
                'success' => false,
                'message' => "Cannot update cut id when coach has already change request."
            ]);
        }

        // block request if item status is invalid
        if (
            $cartItem->isPendingApproval() ||
            $cartItem->isReviewChanges() ||
            $cartItem->isApproved()
        )
        {
            return response()->json([
                'success' => false,
                'message' => "Cannot update cut id on ".$cartItem->getStatus()." status."
            ]);
        }

        $params = $request->all();

        $validator = Validator::make($params, [
            'cut_id' => "required|numeric|digits_between:1,20"
        ]);

        if ($validator->fails())
        {
            return $this->respondWithErrorMessage($validator);
        }

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
        $cartItem = CartItem::find($cart_item_id);
        $has_history_of_changes = $cartItem->changes_logs->isNotEmpty();

        // block request if coach has changes
        if ($has_history_of_changes)
        {
            return response()->json([
                'success' => false,
                'message' => "Cannot update style id when coach has already change request."
            ]);
        }

        // block request if item status is invalid
        if (
            $cartItem->isPendingApproval() ||
            $cartItem->isReviewChanges() ||
            $cartItem->isApproved()
        )
        {
            return response()->json([
                'success' => false,
                'message' => "Cannot update style id on ".$cartItem->getStatus()." status."
            ]);
        }

        $params = $request->all();

        $validator = Validator::make($params, [
            'style_id' => "required|numeric|digits_between:1,20"
        ]);

        if ($validator->fails())
        {
            return $this->respondWithErrorMessage($validator);
        }

        $cartItem->style_id = $params['style_id'];

        return response()->json(
            $cartItem->save() ?
            [
                'success' => true,
                'message' => "Successfully update style id."
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
    public function updateDesign(Request $request, $cart_item_id)
    {
        $cartItem = CartItem::find($cart_item_id);

        // block request if item status is invalid
        if (
            $cartItem->isPendingApproval() ||
            $cartItem->isApproved()
        )
        {
            return response()->json([
                'success' => false,
                'message' => "Cannot update design on ".$cartItem->getStatus()." status."
            ]);
        }

        $params = $request->all();

        $validator = Validator::make($params, [
            'design_id' => "required|numeric|digits_between:1,20",
            'builder_customization' => "required|json"
        ]);

        if ($validator->fails())
        {
            return $this->respondWithErrorMessage($validator);
        }

        $cartItem->design_id = $params['design_id'];
        $cartItem->builder_customization = $params['builder_customization'];

        return response()->json(
            $cartItem->save() ?
            [
                'success' => true,
                'message' => "Successfully update design."
            ] :
            [
                'success' => false,
                'message' => "Cannot update design this time. Please try again later."
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
        $cartItem = CartItem::find($cart_item_id);

        // block request if item status is invalid
        if (
            $cartItem->isPendingApproval() ||
            $cartItem->isApproved()
        )
        {
            return response()->json([
                'success' => false,
                'message' => "Cannot update thumbnails on ".$cartItem->getStatus()." status."
            ]);
        }

        $params = $request->all();

        $validator = Validator::make($params, [
            'front_image' => "url|max:255",
            'back_image' => "url|max:255",
            'left_image' => "url|max:255",
            'right_image' => "url|max:255",
        ]);

        if ($validator->fails())
        {
            return $this->respondWithErrorMessage($validator);
        }

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
        $cartItem = CartItem::find($cart_item_id);

        // block request if item status is invalid
        if (
            $cartItem->isPendingApproval() ||
            $cartItem->isApproved()
        )
        {
            return response()->json([
                'success' => false,
                'message' => "Cannot update roster on ".$cartItem->getStatus()." status."
            ]);
        }

        $params = $request->all();

        $validator = Validator::make($params, [
            'roster' => "required|json",
        ]);

        if ($validator->fails())
        {
            return $this->respondWithErrorMessage($validator);
        }

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
        $cartItem = CartItem::find($cart_item_id);

        // block request if item status is invalid
        if (
            $cartItem->isPendingApproval() ||
            $cartItem->isApproved()
        )
        {
            return response()->json([
                'success' => false,
                'message' => "Cannot update application size on ".$cartItem->getStatus()." status."
            ]);
        }

        $params = $request->all();

        $validator = Validator::make($params, [
            'application_size' => "required|json",
        ]);

        if ($validator->fails())
        {
            return $this->respondWithErrorMessage($validator);
        }

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
        $cartItem = CartItem::find($cart_item_id);

        // block request if item status is invalid
        if (
            $cartItem->isPendingApproval() ||
            $cartItem->isApproved()
        )
        {
            return response()->json([
                'success' => false,
                'message' => "Cannot update design status on ".$cartItem->getStatus()." status."
            ]);
        }

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
     * - email
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
                // 'last_name' => $params['last_name'],
                'email' => $params['email'],
                // 'business_phone' => isset($params['business_phone']) ? $params['business_phone'] : "",
                // 'address_1' => isset($params['address_1']) ? $params['address_1'] : "",
                // 'address_2' => isset($params['address_2']) ? $params['address_2'] : "",
                // 'city' => isset($params['city']) ? $params['city'] : "",
                // 'state' => isset($params['state']) ? $params['state'] : "",
                // 'zip_code' => isset($params['zip_code']) ? $params['zip_code'] : "",
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
            // $clientInformation->last_name = $params['last_name'];
            $clientInformation->email = $params['email'];
            // $clientInformation->business_phone = isset($params['business_phone']) ? $params['business_phone'] : "";
            // $clientInformation->address_1 = isset($params['address_1']) ? $params['address_1'] : "";
            // $clientInformation->address_2 = isset($params['address_2']) ? $params['address_2'] : "";
            // $clientInformation->city = isset($params['city']) ? $params['city'] : "";
            // $clientInformation->state = isset($params['state']) ? $params['state'] : "";
            // $clientInformation->zip_code = isset($params['zip_code']) ? $params['zip_code'] : "";
            $clientInformation->approval_token = ClientInformation::generateUniqueApprovalToken();

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
     * Mark as incomplete
     *
     * Dependency
     *  - Authenticate Middleware
     *  - Cart Middleware
     *  - CartItem Middleware
     *
     * @param Request $request
     */
    public function markAsIncomplete(Request $request, $cart_item_id)
    {
        $params = $request->all();

        $cartItem = CartItem::find($cart_item_id);

        return response()->json(
            $cartItem->markAsIncomplete() ?
            [
                'success' => true,
                'message' => "Successfully mark as incomplete"
            ] :
            [
                'success' => false,
                'message' => "Cannot mark as incomplete this time. Please try again later."
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
                'message' => "Successfully deleted an item."
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
     *  - CartLineItem Middleware
     *
     * @param Request $request
     */
    // public function deleteByLineItemId(Request $request, $line_item_id)
    // {
    //     $cartItem = CartItem::findBy("line_item_id", $line_item_id)->first();
    //     $is_deleted = $cartItem->delete();

    //     return response()->json(
    //         $is_deleted ?
    //         [
    //             'success' => true,
    //             'message' => "Successfully deleted an item."
    //         ] :
    //         [
    //             'success' => false,
    //             'message' => "Cannot delete item this time. Please try again later."
    //         ]
    //     );
    // }

    /**
     * Delete cart item by line item id
     *
     * @param Request $request
     */
    public function deleteByLineItemId(Request $request, $pl_cart_id, $line_item_id)
    {
        Log::debug("Bum panot!");
        $cart = Cart::findBy('pl_cart_id', $pl_cart_id)->first();

        if (!is_null($cart))
        {
            $line_item_ids = $cart->cart_items->pluck("line_item_id")->toArray();

            if (in_array($line_item_id, $line_item_ids))
            {
                Log::info("Success");

                $cartItem = CartItem::findBy("line_item_id", $line_item_id)->first();
                $is_deleted = $cartItem->delete();

                return response()->json(
                    $is_deleted ?
                    [
                        'success' => true,
                        'message' => "Successfully deleted an item."
                    ] :
                    [
                        'success' => false,
                        'message' => "Cannot delete item this time. Please try again later."
                    ]
                );
            }
            else
            {
                Log::warning("Warning: Line item id {$line_item_id} not belong to Pl cart id {$pl_cart_id}!");
            }
        }
        else
        {
            Log::warning("Warning: Pl cart id {$pl_cart_id} is not exist!");
        }

        return response()->json([
            'success' => false,
            'message' => "Unauthorized to access cart",
            'status_code' => 401
        ]);
    }

    /**
     * Add fix change log
     *
     * Dependency
     *  - Authenticate Middleware
     *  - Cart Middleware
     *  - CartItem Middleware
     *
     * @param Request $request
     */
    public function fixChanges(Request $request, $cart_item_id)
    {
        $cartItem = CartItem::find($cart_item_id);

        // get last log
        $log = $cartItem->changes_logs()
                    ->excludeQuickChange()
                    ->get()
                    ->last();

        try {
            // block request if the last log not 'ask for changes'
            if (is_null($log)) throw new \Exception("Cannot create log for 'fix changes' if coach has no request for changing.", 1);
            if (!$log->isAskForChanges()) throw new \Exception("Cannot create log for 'fix changes' if coach has no request for changing.", 1);

            $result = ChangeLog::createFixChanges($cart_item_id);

            if ($result instanceof ChangeLog)
            {
                $cartItem = CartItem::find($cart_item_id);
                $cartItem->markAsIncomplete(false); // false means not removing approval link

                return response()->json([
                    'success' => true,
                    'message' => "Successfully create log for 'fix changes'"
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => "Cannot create log for 'fix changes' this time. Please try again later."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get all change log
     *
     * Dependency
     *  - Authenticate Middleware
     *  - Cart Middleware
     *  - CartItem Middleware
     *
     * @param Request $request
     */
    public function getAllLogs(Request $request, $cart_item_id)
    {
        $cartItem = CartItem::find($cart_item_id);

        $query = $cartItem->changes_logs();
        $this->enableOptions($query);

        $logs = $query->get()->toArray();

        $filter_logs = array_map(function($log) {
            return [
                'id' => $log['id'],
                'note' => $log['note'],
                'attachments' => $log['attachments'],
                'role' => $log['role'],
                'type' => $log['type'],
                'created_at' => $log['created_at']
            ];
        }, $logs);

        return response()->json([
            'success' => true,
            'logs' => $filter_logs
        ]);
    }

    /**
     * Get all change log
     *
     * Dependency
     *  - Authenticate Middleware
     *  - Cart Middleware
     *  - CartItem Middleware
     *
     * @param Request $request
     */
    public function getChangeRequested(Request $request, $cart_item_id)
    {
        $cartItem = CartItem::find($cart_item_id);

        // get last log
        $log = $cartItem->changes_logs()
                    ->excludeQuickChange()
                    ->get()
                    ->last();

        unset($log['role']);
        unset($log['type']);
        unset($log['updated_at']);

        return response()->json([
            'success' => true,
            'log' => $log
        ]);
    }

    /**
     * Get builder customization
     *
     * Dependency
     *  - Authenticate Middleware
     *  - Cart Middleware
     *  - CartItem Middleware
     *
     * @param Request $request
     */
    public function getBuilderCustomization(Request $request, $cart_item_id)
    {
        $cartItem = CartItem::find($cart_item_id);

        return response()->json([
            'success' => true,
            'builder_customization' => json_decode($cartItem->builder_customization, true)
        ]);
    }
}
