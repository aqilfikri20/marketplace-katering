<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_PREORDER = 'preorder';
    public const STATUS_SOLD_OUT = 'sold_out';

    public const STATUSES = [self::STATUS_AVAILABLE, self::STATUS_PREORDER, self::STATUS_SOLD_OUT];

    protected $fillable = ['merchant_id', 'name', 'description', 'price', 'photo', 'category', 'is_available', 'status'];
    protected function casts(): array { return ['price' => 'decimal:2', 'is_available' => 'boolean']; }
    public function merchant() { return $this->belongsTo(User::class, 'merchant_id'); }
    public function orderItems() { return $this->hasMany(OrderItem::class); }
    public function ratings() { return $this->hasMany(MenuRating::class); }
}
