<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChangeLog extends Model
{
    protected $table = "changes_logs";
    protected $fillable = ["note", "attachments", "role", "type", "cart_item_id"];

    const ROLE_SALES_REP = "sales rep";
    const ROLE_COACH = "coach";

    const TYPE_FIXED = "fixed";
    const TYPE_ASK_FOR_CHANGES = "ask for changes";
    const TYPE_QUICK_CHANGE = "quick change";

    public function scopeExcludeQuickChange($query)
    {
        return $query->where("type", "<>", static::TYPE_QUICK_CHANGE);
    }

    public function isAskForChanges()
    {
        return $this->type === static::TYPE_ASK_FOR_CHANGES;
    }

    public static function createAskForChanges($note, $attachments, $cart_item_id)
    {
        return static::create([
            'note' => $note,
            'attachments' => $attachments,
            'role' => static::ROLE_COACH,
            'type' => static::TYPE_ASK_FOR_CHANGES,
            'cart_item_id' => $cart_item_id
        ]);
    }

    public static function createFixChanges($cart_item_id)
    {
        return static::create([
            'role' => static::ROLE_SALES_REP,
            'type' => static::TYPE_FIXED,
            'cart_item_id' => $cart_item_id
        ]);
    }

    public static function createQuickChange($note, $cart_item_id)
    {
        return static::create([
            'note' => $note,
            'role' => static::ROLE_COACH,
            'type' => static::TYPE_QUICK_CHANGE,
            'cart_item_id' => $cart_item_id
        ]);
    }
}
