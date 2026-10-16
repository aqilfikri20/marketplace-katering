<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Support\MarketplaceCache;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'company_name' => 'required|string|max:150',
            'phone' => 'required|string|max:30',
            'address' => 'required|string|max:1000',
            'description' => 'nullable|string|max:1500',
            'food_type' => 'nullable|string|max:80',
        ]);

        if ($user->role === 'customer') {
            unset($data['description'], $data['food_type']);
        }

        $user->update($data);
        if ($user->role === 'merchant') MarketplaceCache::invalidate();

        return back()->with('success', $user->role === 'merchant'
            ? 'Profil katering berhasil diperbarui.'
            : 'Profil kantor berhasil diperbarui.');
    }

    public function __invoke(Request $request)
    {
        $user = $request->user();
        if ($user->role === 'merchant') {
            $menus = $user->menus()
                ->withSum(['orderItems as total_sold' => fn ($items) => $items->whereHas('order', fn ($orders) => $orders->whereIn('status', [Order::STATUS_CONFIRMED, Order::STATUS_COMPLETED]))], 'quantity')
                ->withAvg('ratings as average_rating', 'rating')
                ->withCount('ratings')
                ->latest()
                ->get();
            $orders = $user->receivedOrders()->with('customer', 'items')->latest()->take(8)->get();
            $stats = ['menus' => $user->menus()->count(), 'orders' => $user->receivedOrders()->count(), 'revenue' => $user->receivedOrders()->whereIn('status', ['confirmed','completed'])->sum('total')];
            return view('dashboard.merchant', compact('user', 'menus', 'orders', 'stats'));
        }
        $orders = $user->orders()->with(['merchant', 'items.menu.ratings' => fn ($query) => $query->where('customer_id', $user->id)])->latest()->take(10)->get();
        $stats = ['orders' => $user->orders()->count(), 'spend' => $user->orders()->sum('total'), 'merchants' => $user->orders()->distinct('merchant_id')->count('merchant_id')];
        return view('dashboard.customer', compact('user', 'orders', 'stats'));
    }
}
