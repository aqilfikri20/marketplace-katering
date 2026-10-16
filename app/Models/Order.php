<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = ['invoice_number', 'customer_id', 'merchant_id', 'delivery_date', 'delivery_address', 'notes', 'subtotal', 'delivery_fee', 'total', 'status'];
    protected function casts(): array { return ['delivery_date' => 'date', 'subtotal' => 'decimal:2', 'delivery_fee' => 'decimal:2', 'total' => 'decimal:2']; }
    public function customer() { return $this->belongsTo(User::class, 'customer_id'); }
    public function merchant() { return $this->belongsTo(User::class, 'merchant_id'); }
    public function items() { return $this->hasMany(OrderItem::class); }
}
