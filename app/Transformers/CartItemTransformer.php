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
        https://via.placeholder.com/1000x1100?text=No%20Image

        $data = [
            'id' => $cartItem->id,
            'design_id' => $cartItem->getDesignId(),
            'customizer_url' => $cartItem->getCustomizerUrl(),
            'front_image' => $cartItem->getFrontThumbnail(),
            'back_image' => $cartItem->getBackThumbnail(),
            'left_image' => $cartItem->getLeftThumbnail(),
            'right_image' => $cartItem->getRightThumbnail(),
            'roster' => $cartItem->roster,
            'application_size' => $cartItem->application_size,
            'design_status' => $cartItem->design_status,
            'pdf_url' => $cartItem->pdf_url,
            'signature_image' => $cartItem->signature_image,
            'line_item_id' => $cartItem->line_item_id,
            'pl_cart_id' => $cartItem->pl_cart_id_fk,
            'status' => $cartItem->getStatus()
        ];

        if (!is_null($cartItem->getCutId()))
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

        if (!is_null($cartItem->client_information))
        {
            $data['client_information'] = $cartItem->client_information;

            unset($data['client_information']['id']);
            unset($data['client_information']['cart_item_id']);
            unset($data['client_information']['created_at']);
            unset($data['client_information']['updated_at']);
        }

        return $data;
    }
}
