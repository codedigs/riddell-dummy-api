<?php

namespace App\Transformers;

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
        $cut = $cartItem->getCut();
        $style = $cartItem->getStyle();

        $data = [
            'id' => $cartItem->id,
            'customizer_url' => $cartItem->customizer_url,
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

        if (!is_null($style))
        {
            $data['style'] = [
                'id' => $style->id,
                'name' => $style->getName(),
                // 'image' => $style->getImage()
                'image' => "/riddell/img/Football-Picker/Inspiration@2x.png"
            ];
        }

        return $data;
    }
}
