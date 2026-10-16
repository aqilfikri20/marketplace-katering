<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Order;
use App\Support\MarketplaceCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate(['items' => 'required|array|min:1', 'items.*.menu_id' => 'required|integer|distinct|exists:menus,id', 'items.*.quantity' => 'required|integer|min:1|max:500', 'delivery_date' => 'required|date|after_or_equal:today', 'delivery_address' => 'required|string|max:1000', 'notes' => 'nullable|string|max:1000']);
        $menus = Menu::whereIn('id', collect($data['items'])->pluck('menu_id'))->where('status', '!=', Menu::STATUS_SOLD_OUT)->get()->keyBy('id');
        abort_if($menus->count() !== count($data['items']), 422, 'Ada menu yang sudah tidak tersedia.');
        $merchantIds = $menus->pluck('merchant_id')->unique();
        abort_if($merchantIds->count() !== 1, 422, 'Pesanan harus berasal dari satu katering.');
        $order = DB::transaction(function () use ($request, $data, $menus, $merchantIds) {
            $subtotal = collect($data['items'])->sum(fn($item) => $menus[$item['menu_id']]->price * $item['quantity']);
            $order = Order::create(['invoice_number' => 'INV-'.now()->format('ymd').'-'.strtoupper(Str::random(6)), 'customer_id' => $request->user()->id, 'merchant_id' => $merchantIds->first(), 'delivery_date' => $data['delivery_date'], 'delivery_address' => $data['delivery_address'], 'notes' => $data['notes'] ?? null, 'subtotal' => $subtotal, 'delivery_fee' => 0, 'total' => $subtotal, 'status' => 'pending']);
            foreach ($data['items'] as $item) { $menu = $menus[$item['menu_id']]; $order->items()->create(['menu_id' => $menu->id, 'menu_name' => $menu->name, 'price' => $menu->price, 'quantity' => $item['quantity'], 'line_total' => $menu->price * $item['quantity']]); }
            return $order;
        });
        MarketplaceCache::invalidate();
        return redirect()->route('invoices.show', $order)->with('success', 'Pesanan berhasil dibuat. Invoice siap diakses.');
    }
    public function invoice(Request $request, Order $order)
    {
        abort_unless(in_array($request->user()->id, [$order->customer_id, $order->merchant_id]), 403);
        $order->load('items', 'merchant', 'customer');
        return view('orders.invoice', compact('order'));
    }
    public function updateStatus(Request $request, Order $order)
    {
        abort_unless($order->merchant_id === $request->user()->id, 403);
        $data = $request->validate(['status' => 'required|in:confirmed,completed,cancelled']);
        $order->update($data);
        MarketplaceCache::invalidate();
        return back()->with('success', 'Status pesanan diperbarui.');
    }

    public function rateMenu(Request $request, Order $order)
    {
        abort_unless($request->user()->role === 'customer' && $order->customer_id === $request->user()->id, 403, 'Hanya kantor pemesan yang dapat memberi rating.');
        abort_unless(in_array($order->status, [Order::STATUS_CONFIRMED, Order::STATUS_COMPLETED], true), 422, 'Rating hanya dapat diberikan setelah pesanan dikonfirmasi oleh katering.');

        $data = $request->validate([
            'menu_id' => 'required|integer|exists:menus,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ], [
            'menu_id.required' => 'Menu yang akan diberi rating tidak ditemukan.',
            'rating.required' => 'Silakan pilih rating dari 1 sampai 5 bintang.',
            'rating.integer' => 'Rating harus berupa angka bulat.',
            'rating.min' => 'Rating minimal adalah 1 bintang.',
            'rating.max' => 'Rating maksimal adalah 5 bintang.',
            'comment.max' => 'Ulasan maksimal 500 karakter.',
        ]);

        abort_unless($order->items()->where('menu_id', $data['menu_id'])->exists(), 403, 'Menu ini tidak ada dalam pesanan tersebut.');

        \App\Models\MenuRating::updateOrCreate(
            ['menu_id' => $data['menu_id'], 'customer_id' => $request->user()->id],
            ['order_id' => $order->id, 'rating' => $data['rating'], 'comment' => $data['comment'] ?? null],
        );

        MarketplaceCache::invalidate();
        return back()->with('success', 'Terima kasih, rating dan ulasan makanan berhasil disimpan.');
    }
}
