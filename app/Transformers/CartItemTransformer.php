<?php

namespace App\Transformers;

use App\Models\CartItem;
use App\Models\Cut;
use App\Models\Style;
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
        $cut = Cut::getById($cartItem->cut_id);
        // $style = Style::getByCutId($cartItem->style_id);

        // $style = $cartItem->getStyle();

        $data = [
            'id' => $cartItem->id,
            'style_id' => $cartItem->getStyleId(), // temporary
            'design_id' => $cartItem->getDesignId(),
            'front_image' => $cartItem->front_image,
            'back_image' => $cartItem->back_image,
            'left_image' => $cartItem->left_image,
            'right_image' => $cartItem->right_image,
            'status' => $cartItem->getStatus()
        ];

        if (!is_null($cut))
        {
            $data['cut'] = [
                'id' => $cut->id,
                'name' => $cut->block_pattern_name,
                // 'image' => $cut->getImage()
                'image' => "/riddell/img/Cuts/cut-7.png"
            ];
        }

        // if (!is_null($style))
        // {
        //     $data['style'] = [
        //         'id' => $style->id,
        //         // 'name' => $style->getName(),
        //         // 'image' => $style->getImage()
        //         'image' => "/riddell/img/Football-Picker/Inspiration@2x.png"
        //     ];
        // }

        return $data;
    }
}
