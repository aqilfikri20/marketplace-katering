<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuRating extends Model
{
    protected $fillable = ['menu_id', 'customer_id', 'order_id', 'rating', 'comment'];

    protected function casts(): array
    {
        return ['rating' => 'integer'];
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
