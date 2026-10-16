@extends('layouts.app')

@section('title', 'Dashboard Katering — Ketring Yuk')

@section('content')
<section class="dashboard">
    <div class="dash-heading">
        <div>
            <div class="eyebrow eyebrow-muted">PORTAL KATERING</div>
            <h1>Halo, {{ $user->company_name ?? $user->name }} 👋</h1>
            <p>Kelola profil usaha, menu, dan pesananmu.</p>
        </div>
        <button class="button button-primary" type="button" onclick="document.getElementById('menu-modal').showModal()">+ Tambah menu</button>
    </div>

    <div class="stats-grid">
        <div class="stat-card"><span class="stat-icon">▣</span><small>Total menu</small><strong>{{ $stats['menus'] }}</strong><span class="stat-caption">menu tersedia</span></div>
        <div class="stat-card"><span class="stat-icon icon-peach">⌁</span><small>Total pesanan</small><strong>{{ $stats['orders'] }}</strong><span class="stat-caption">semua waktu</span></div>
        <div class="stat-card"><span class="stat-icon icon-green">↗</span><small>Total pendapatan</small><strong>Rp {{ number_format($stats['revenue'], 0, ',', '.') }}</strong><span class="stat-caption">pesanan terkonfirmasi</span></div>
    </div>

    <div class="dashboard-grid">
        <section class="panel">
            <div class="panel-head">
                <div><h2>Pesanan terbaru</h2><p>Pantau pesanan masuk dari kantor.</p></div>
            </div>
            @forelse($orders as $order)
                <div class="order-row">
                    <div class="order-avatar">{{ mb_substr($order->customer->company_name ?? 'K', 0, 1) }}</div>
                    <div class="order-details">
                        <strong>{{ $order->customer->company_name }}</strong>
                        <small>{{ $order->invoice_number }} · {{ $order->items->sum('quantity') }} porsi · {{ $order->delivery_date->format('d M Y') }}</small>
                    </div>
                    <div class="order-trailing">
                        <strong>Rp {{ number_format($order->total, 0, ',', '.') }}</strong>
                        <span class="status status-{{ $order->status }}">{{ ucfirst($order->status) }}</span>
                    </div>
                    <a href="{{ route('invoices.show', $order) }}" class="text-link">Invoice ↗</a>
                    @if($order->status === 'pending')
                        <form method="POST" action="{{ route('orders.status', $order) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="confirmed"><button class="button button-outline button-sm">Terima</button></form>
                    @endif
                </div>
            @empty
                <div class="empty-dash"><p>Belum ada pesanan. Lengkapi profil dan tambahkan menu agar kantor mudah menemukanmu.</p></div>
            @endforelse
        </section>

        <section class="panel profile-panel">
            <div class="panel-head">
                <div><h2>Profil katering</h2><p>Informasi yang dilihat pelanggan.</p></div>
                <button class="button button-outline button-sm" type="button" onclick="document.getElementById('profile-modal').showModal()">Edit profil</button>
            </div>
            <div class="profile-cover">🥗</div>
            <div class="profile-content">
                <h3>{{ $user->company_name ?: 'Nama katering belum diisi' }}</h3>
                <p>{{ $user->description ?: 'Tambahkan deskripsi singkat agar pelanggan mengenal kateringmu.' }}</p>
                <div class="profile-meta"><span>Kontak</span>{{ $user->name }}</div>
                <div class="profile-meta"><span>Email</span>{{ $user->email }}</div>
                <div class="profile-meta"><span>Alamat</span>{{ $user->address ?: 'Belum diisi' }}</div>
                <div class="profile-meta"><span>Telepon</span>{{ $user->phone ?: 'Belum diisi' }}</div>
                <div class="profile-meta"><span>Jenis menu</span>{{ $user->food_type ?: 'Belum diatur' }}</div>
            </div>
        </section>
    </div>

    <section class="panel menus-panel">
        <div class="panel-head">
            <div><h2>Menu kamu</h2><p>Foto dan hidangan yang tampil di marketplace.</p></div>
            <button class="button button-light button-sm" type="button" onclick="document.getElementById('menu-modal').showModal()">+ Tambah menu</button>
        </div>
        <div class="merchant-menu-grid">
        @forelse($menus as $menu)
            @php($ratingScore = $menu->ratings_count ? (float) $menu->average_rating : 5.0)
            <article class="merchant-menu-card">
                <div class="merchant-menu-image">
                    @if($menu->photo)
                        <img src="{{ Storage::disk('public')->url($menu->photo) }}" alt="Foto {{ $menu->name }}">
                    @else
                        <span aria-hidden="true">🍱</span>
                    @endif
                </div>
                <div class="merchant-menu-content">
                    <h3>{{ $menu->name }}</h3>
                    <div class="menu-rating"><span>★</span><strong>{{ number_format($ratingScore, 2) }}</strong><small>/ 5 · {{ $menu->ratings_count }} ulasan</small></div>
                    <strong class="merchant-menu-price">Rp {{ number_format($menu->price, 0, ',', '.') }}</strong>
                    <small class="merchant-menu-sales">Terjual {{ number_format((int) ($menu->total_sold ?? 0), 0, ',', '.') }} porsi</small>
                    <span class="menu-stock-status menu-stock-{{ $menu->status }}">{{ ['available' => 'Tersedia', 'preorder' => 'Pre-order', 'sold_out' => 'Habis'][$menu->status] ?? 'Tersedia' }}</span>
                    <div class="merchant-menu-actions">
                        <button type="button" class="button button-outline button-sm" onclick="document.getElementById('edit-menu-{{ $menu->id }}').showModal()">Edit</button>
                        <form method="POST" action="{{ route('menus.destroy', $menu) }}" onsubmit="return confirm('Hapus menu ini?')">@csrf @method('DELETE')<button class="button button-light button-sm" type="submit">Hapus</button></form>
                    </div>
                </div>
            </article>
        @empty
            <div class="empty-dash"><p>Belum ada menu. Tambahkan menu pertama untuk mulai ditemukan kantor.</p></div>
        @endforelse
        </div>
    </section>
</section>

<dialog id="menu-modal" class="order-dialog">
    <form method="POST" action="{{ route('menus.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="dialog-head"><div><span class="eyebrow eyebrow-muted">KATALOG</span><h2>Tambah menu baru</h2></div><button type="button" class="dialog-close" onclick="this.closest('dialog').close()" aria-label="Tutup">×</button></div>
        <label>Nama menu<input name="name" required placeholder="Contoh: Nasi Ayam Bakar Madu"></label>
        <label>Deskripsi<textarea name="description" required rows="3" placeholder="Ceritakan menu ini"></textarea></label>
        <div class="form-row"><label>Harga per porsi<input type="number" name="price" required min="1000" placeholder="35000"></label><label>Kategori<input name="category" placeholder="Menu Indonesia"></label></div>
        <label>Foto menu<input type="file" name="photo" accept="image/*" required></label>
        <label>Status menu<select name="status" required><option value="available">Tersedia</option><option value="preorder">Pre-order</option><option value="sold_out">Habis</option></select></label>
        <button class="button button-primary button-wide">Simpan menu</button>
    </form>
</dialog>
@include('dashboard.menu-edit-modals')
@endsection
