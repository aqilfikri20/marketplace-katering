<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    protected $fillable = ['name', 'email', 'password', 'role', 'company_name', 'address', 'phone', 'description', 'food_type', 'latitude', 'longitude'];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array { return ['email_verified_at' => 'datetime', 'password' => 'hashed', 'latitude' => 'decimal:7', 'longitude' => 'decimal:7']; }
    public function menus() { return $this->hasMany(Menu::class, 'merchant_id'); }
    public function orders() { return $this->hasMany(Order::class, 'customer_id'); }
    public function receivedOrders() { return $this->hasMany(Order::class, 'merchant_id'); }
}
