<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    public $timestamps = false;
    protected $fillable = ['menu_id', 'menu_name', 'price', 'quantity', 'line_total'];
    protected function casts(): array { return ['price' => 'decimal:2', 'line_total' => 'decimal:2']; }
    public function menu() { return $this->belongsTo(Menu::class); }
    public function order() { return $this->belongsTo(Order::class); }
}
