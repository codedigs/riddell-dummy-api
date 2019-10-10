<?php

namespace App\Transformers;

use App\Models\Cut;
use League\Fractal\TransformerAbstract;

class CutTransformer extends TransformerAbstract
{
    /**
     * [transform description]
     *
     * @param  Cut $cut
     * @return array
     */
    public function transform(Cut $cut)
    {
        return [
            'cut_id' => $cut->cut_id,
            'hybris_sku' => $cut->hybris_sku,
            'style_category' => $cut->style_category,
            'gender' => $cut->gender,
            'cutInfo' => [
                'name' => $cut->name,
                'image' => $cut->image,
                'sport' => $cut->sport
            ]
        ];
    }
}
