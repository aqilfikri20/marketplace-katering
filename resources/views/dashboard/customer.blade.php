@extends('layouts.app')

@section('title', 'Dashboard Kantor — Ketring Yuk')

@section('content')
<section class="dashboard">
    <div class="dash-heading">
        <div>
            <div class="eyebrow eyebrow-muted">PORTAL KANTOR</div>
            <h1>Selamat datang, {{ $user->company_name ?? $user->name }} 👋</h1>
            <p>Pesan makan siang timmu dan lacak semuanya di satu tempat.</p>
        </div>
        <a href="{{ route('home') }}#menu" class="button button-primary">Jelajahi menu</a>
    </div>

    <div class="stats-grid">
        <div class="stat-card"><span class="stat-icon">▣</span><small>Total pesanan</small><strong>{{ $stats['orders'] }}</strong><span class="stat-caption">riwayat pesanan</span></div>
        <div class="stat-card"><span class="stat-icon icon-peach">♧</span><small>Katering dicoba</small><strong>{{ $stats['merchants'] }}</strong><span class="stat-caption">partner pilihan</span></div>
        <div class="stat-card"><span class="stat-icon icon-green">↗</span><small>Total pengeluaran</small><strong>Rp {{ number_format($stats['spend'], 0, ',', '.') }}</strong><span class="stat-caption">semua waktu</span></div>
    </div>

    <section class="panel customer-profile-panel">
        <div class="panel-head">
            <div><h2>Profil kantor</h2><p>Informasi akun dan alamat pengiriman.</p></div>
            <button class="button button-outline button-sm" type="button" onclick="document.getElementById('profile-modal').showModal()">Edit profil</button>
        </div>
        <div class="customer-profile-grid">
            <div><small>Nama kantor</small><strong>{{ $user->company_name ?: 'Belum diisi' }}</strong></div>
            <div><small>Nama kontak</small><strong>{{ $user->name }}</strong></div>
            <div><small>Email</small><strong>{{ $user->email }}</strong></div>
            <div><small>Telepon</small><strong>{{ $user->phone ?: 'Belum diisi' }}</strong></div>
            <div class="customer-profile-address"><small>Alamat pengiriman</small><strong>{{ $user->address ?: 'Belum diisi' }}</strong></div>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head"><div><h2>Riwayat pesanan</h2><p>Invoice dan status pesanan makan siang tim.</p></div></div>
        @forelse($orders as $order)
            <div class="order-row">
                <div class="order-avatar merchant-avatar">🍱</div>
                <div class="order-details"><strong>{{ $order->merchant->company_name }}</strong><small>{{ $order->invoice_number }} · {{ $order->items->sum('quantity') }} porsi · Pengiriman {{ $order->delivery_date->format('d M Y') }}</small></div>
                @if(in_array($order->status, ['confirmed', 'completed'], true))
                    <dialog class="order-dialog review-dialog" id="review-dialog-{{ $order->id }}">
                        <div class="dialog-head"><div><span class="eyebrow eyebrow-muted">ULASAN PESANAN</span><h2>{{ $order->invoice_number }}</h2></div><button type="button" class="dialog-close" onclick="this.closest('dialog').close()" aria-label="Tutup">×</button></div>
                        @foreach($order->items as $item)
                            @if($item->menu)
                                @php($existingRating = $item->menu->ratings->first())
                                <form class="rating-form" method="POST" action="{{ route('orders.rate', $order) }}">
                                    @csrf
                                    <input type="hidden" name="menu_id" value="{{ $item->menu_id }}">
                                    <label>Nilai {{ $item->menu_name }}</label>
                                    <div class="rating-form-fields">
                                        <div class="rating-star-choices" role="radiogroup" aria-label="Rating {{ $item->menu_name }}">
                                            @foreach(range(1, 5) as $score)
                                                <input id="rating-{{ $order->id }}-{{ $item->id }}-{{ $score }}" type="radio" name="rating" value="{{ $score }}" @checked((int) ($existingRating?->rating ?? 5) === $score) required>
                                                <label for="rating-{{ $order->id }}-{{ $item->id }}-{{ $score }}" title="{{ $score }} bintang">★<span class="sr-only">{{ $score }} bintang</span></label>
                                            @endforeach
                                        </div>
                                        <label class="sr-only" for="comment-{{ $order->id }}-{{ $item->id }}">Ulasan opsional untuk {{ $item->menu_name }}</label>
                                        <textarea id="comment-{{ $order->id }}-{{ $item->id }}" name="comment" maxlength="500" rows="3" placeholder="Tulis ulasan tentang makanan ini (opsional)">{{ $existingRating?->comment }}</textarea>
                                        <button class="button button-primary button-sm" type="submit">{{ $existingRating ? 'Perbarui ulasan' : 'Kirim ulasan' }}</button>
                                    </div>
                                </form>
                            @endif
                        @endforeach
                    </dialog>
                @endif
                <div class="order-trailing"><strong>Rp {{ number_format($order->total, 0, ',', '.') }}</strong><span class="status status-{{ $order->status }}">{{ ucfirst($order->status) }}</span></div>
                <a href="{{ route('invoices.show', $order) }}" class="button button-outline button-sm">Lihat invoice</a>
                @if(in_array($order->status, ['confirmed', 'completed'], true))
                    <button type="button" class="button button-primary button-sm" onclick="document.getElementById('review-dialog-{{ $order->id }}').showModal()">Beri ulasan</button>
                @endif
            </div>
        @empty
            <div class="empty-dash"><span>🍽️</span><h3>Belum ada pesanan</h3><p>Temukan makan siang favorit untuk timmu.</p><a href="{{ route('home') }}" class="text-link">Jelajahi katering →</a></div>
        @endforelse
    </section>
</section>
@endsection
