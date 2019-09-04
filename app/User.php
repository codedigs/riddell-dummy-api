<?php

namespace App;

use App\Models\Cart;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'user_id', // for prolook user id
        'access_token'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function scopeFindBy($query, $field, $value)
    {
        return $query->where($field, $value);
    }

    public function saveAccessToken($access_token)
    {
        $this->access_token = $access_token;
        return $this->save();
    }

    public function saveUserIdAndAccessToken($user_id, $access_token)
    {
        $this->user_id = $user_id;
        $this->access_token = $access_token;
        return $this->save();
    }

    public function hasValidCart()
    {
        return $this->carts()->validToUse()->get()->isNotEmpty();
    }

    public function getCurrentCart()
    {
        return $this->carts()->validToUse()->get()->last();
    }

    public function hasUserId()
    {
        return !is_null($this->user_id);
    }
}
