<?php

namespace App\Transformers;

use App\Api\Prolook\StyleApi;
use App\Api\Qx7\CutApi;
use App\Models\CartItem;
use League\Fractal\TransformerAbstract;

class CartItemTransformer extends TransformerAbstract
{
    /**
     * [transform description]
     *
     * @param  CartItem $cartItem
     * @return array
     */
    public function transform(CartItem $cartItem)
    {
        $data = [
            'id' => $cartItem->id,
            // 'style_id' => $cartItem->getStyleId(), // temporary
            'design_id' => $cartItem->getDesignId(),
            'front_image' => $cartItem->front_image,
            'back_image' => $cartItem->back_image,
            'left_image' => $cartItem->left_image,
            'right_image' => $cartItem->right_image,
            'application_size' => $cartItem->application_size,
            'line_item_id' => $cartItem->line_item_id,
            'pl_cart_id' => $cartItem->pl_cart_id_fk,
            'status' => $cartItem->getStatus()
        ];

        if ($cartItem->getCutId())
        {
            $cutApi = new CutApi;
            $cut = $cutApi->getById($cartItem->cut_id);

            if ($cut->success)
            {
                $block_pattern = $cut->master_3d_block_patterns;

                $data['cut'] = [
                    'id' => $block_pattern->id,
                    'name' => $block_pattern->block_pattern_name,
                    'image' => !is_null($block_pattern->image_thumbnail) ? $block_pattern->image_thumbnail : "/riddell/img/Cuts/cut-7.png"
                ];
            }
        }

        if (!is_null($cartItem->getStyleId()))
        {
            $styleApi = new StyleApi;
            $style = $styleApi->getInfo($cartItem->getStyleId());

            if ($style->success)
            {
                $material = $style->material;

                $data['style'] = [
                    'id' => $material->id,
                    'name' => $material->name,
                    'image' => !empty($material->thumbnail_path) ? $material->thumbnail_path : "/riddell/img/Football-Picker/Inspiration@2x.png"
                ];
            }
        }

        return $data;
    }
}
