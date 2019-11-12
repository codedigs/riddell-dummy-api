<?php

namespace App\Transformers;

use App\Api\Prolook\StyleApi;
use App\Api\Qx7\CutApi;
use App\Models\CartItem;
use App\Models\Cut;
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
            'design_id' => $cartItem->getDesignId(),
            'builder_customization' => $cartItem->builder_customization,
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
            'status' => $cartItem->getStatus(),
            'has_history_of_changes' => $cartItem->changes_logs->isNotEmpty()
        ];

        if (!is_null($cartItem->getCutId()))
        {
            if (config("app.use_cuts_in_db"))
            {
                $cut = $cartItem->cut;

                if (!is_null($cut))
                {
                    $data['cut'] = [
                        'id' => $cut->cut_id,
                        'name' => $cut->name,
                        'image' => !is_null($cut->image) ? $cut->image : "/riddell/img/Cuts/cut-7.png"
                    ];
                }
            }
            else
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
