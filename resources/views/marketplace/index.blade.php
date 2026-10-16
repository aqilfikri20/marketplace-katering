@extends('layouts.app')

@section('title', 'Ketring Yuk — Temukan menu makan siang')

@section('content')
<section class="hero hero-catering">
    <div class="hero-catering-copy">
        <div class="hero-catering-eyebrow"><span></span>KATERING PILIHAN UNTUK KANTOR</div>
        <h1>Makan siang<br>yang dinanti,<br><em>setiap hari.</em></h1>
        <p>Temukan menu rumahan dan katering andalan untuk tim. Pesan praktis, jadwalkan pengiriman, lalu nikmati bersama.</p>
        <div class="hero-catering-actions">
            <a class="button" href="#merchant">Jelajahi menu <span aria-hidden="true">→</span></a>
            <a class="hero-catering-secondary" href="#cara-kerja">Cara pesan <span aria-hidden="true">↗</span></a>
        </div>
        <div class="hero-catering-note"><span></span>Menu beragam · Pesan untuk tim · Invoice tersedia</div>
    </div>
    <div class="hero-catering-caption"><span>01</span><span>MAKAN SIANG BERSAMA, SETIAP HARI</span></div>
</section>
<section class="market-section" id="merchant">
    <div class="section-heading">
        <div><div class="eyebrow eyebrow-muted">PILIHAN UNTUK TIMMU</div><h2>Menu makan siang</h2><p>Dimasak segar, dipilih sesuai kebutuhan kantor.</p></div>
        <form class="sort-form" method="GET" action="{{ route('home') }}">
            @foreach(['q', 'location', 'type'] as $filter)
                @if(request($filter))<input type="hidden" name="{{ $filter }}" value="{{ request($filter) }}">@endif
            @endforeach
            <label for="sort">Urutkan</label>
            <select id="sort" name="sort" onchange="this.form.requestSubmit()">
                <option value="popular" @selected($sort === 'popular')>Paling banyak dibeli</option>
                <option value="price_asc" @selected($sort === 'price_asc')>Harga termurah</option>
                <option value="price_desc" @selected($sort === 'price_desc')>Harga termahal</option>
            </select>
        </form>
    </div>

    <div class="search-wrap menu-search-wrap">
        <form class="search-bar" method="GET" action="{{ route('home') }}">
            <div class="search-field"><span class="field-icon" aria-hidden="true">⌕</span><label class="sr-only" for="q">Cari menu atau katering</label><input id="q" type="search" name="q" value="{{ request('q') }}" placeholder="Cari menu atau nama katering..."></div>
            <div class="search-divider"></div>
            <div class="search-field location-field"><span class="field-icon pin-icon" aria-hidden="true">⌖</span><label class="sr-only" for="location">Lokasi</label><input id="location" name="location" value="{{ request('location') }}" placeholder="Area kantor"><span class="chevron" aria-hidden="true">⌄</span></div>
            <div class="search-divider"></div>
            <div class="search-field type-field"><label class="sr-only" for="type">Jenis makanan</label><select id="type" name="type"><option value="">Semua jenis</option>@foreach($types as $type)<option value="{{ $type }}" @selected(request('type') === $type)>{{ $type }}</option>@endforeach</select></div>
            <button class="button button-primary search-submit" type="submit">Cari menu</button>
        </form>
    </div>

    <div class="category-row">
        <a class="category-pill {{ !request('type') ? 'active' : '' }}" href="{{ route('home', array_filter(['sort' => $sort, 'q' => request('q'), 'location' => request('location')])) }}"><span>✳</span> Semua menu</a>
        @foreach($types as $type)
            <a class="category-pill {{ request('type') === $type ? 'active' : '' }}" href="{{ route('home', array_filter(['type' => $type, 'sort' => $sort, 'q' => request('q'), 'location' => request('location')])) }}"><span>{{ $loop->iteration % 2 ? '◉' : '❋' }}</span> {{ $type }}</a>
        @endforeach
    </div>

    @if($menus->count())
        <div class="menu-grid" id="menu">
            @foreach($menus as $menu)
                @php($ratingScore = $menu->ratings_count ? (float) $menu->average_rating : 5.0)
                <article class="menu-card">
                    <a class="menu-card-overlay" href="{{ route('menus.show', $menu) }}" aria-label="Lihat detail {{ $menu->name }}"></a>
                    <div class="menu-image">
                        @if($menu->photo)
                            <img src="{{ Storage::disk('public')->url($menu->photo) }}" alt="Foto {{ $menu->name }}" loading="lazy" decoding="async">
                        @else
                            <img src="{{ ['https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=700&q=80', 'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=700&q=80', 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=700&q=80'][$loop->index % 3] }}" alt="Contoh hidangan {{ $menu->name }}" loading="lazy" decoding="async">
                        @endif
                        <span class="menu-category">{{ $menu->category ?? $menu->merchant->food_type ?? 'Menu pilihan' }}</span>
                        <span class="menu-sold">Terjual {{ number_format((int) ($menu->total_sold ?? 0), 0, ',', '.') }}</span>
                        <span class="menu-stock-status menu-stock-{{ $menu->status }}">{{ ['available' => 'Tersedia', 'preorder' => 'Pre-order', 'sold_out' => 'Habis'][$menu->status] ?? 'Tersedia' }}</span>
                    </div>
                    <div class="menu-info">
                        <div class="merchant-line"><span class="merchant-dot">{{ mb_substr($menu->merchant->company_name ?? 'K', 0, 1) }}</span><span>{{ $menu->merchant->company_name }}</span><span class="verified">✓</span></div>
                        <h3>{{ $menu->name }}</h3>
                        <div class="menu-rating" aria-label="Rating {{ number_format($ratingScore, 2) }} dari 5"><span>★</span><strong>{{ number_format($ratingScore, 2) }}</strong><small>/ 5</small><small>({{ $menu->ratings_count ?: 'Belum ada' }})</small></div>
                        <div class="menu-location"><span aria-hidden="true">⌖</span>{{ Str::limit($menu->merchant->address ?: 'Lokasi belum diatur', 48) }}</div>
                        <div class="menu-bottom">
                            <div><strong>Rp {{ number_format($menu->price, 0, ',', '.') }}</strong><small> / porsi</small></div>
                            @auth
                                @if(auth()->user()->role === 'customer')
                                    @if($menu->status === 'sold_out')
                                        <button class="button button-outline" type="button" disabled>Habis</button>
                                    @else
                                        <button class="button button-outline add-order" data-menu-id="{{ $menu->id }}" data-menu-name="{{ $menu->name }}" data-price="{{ $menu->price }}" data-merchant="{{ $menu->merchant_id }}">{{ $menu->status === 'preorder' ? 'Pre-order' : '+ Pilih' }}</button>
                                    @endif
                                @else
                                    <span class="menu-stock-label menu-stock-label-{{ $menu->status }}">{{ ['available' => 'Tersedia', 'preorder' => 'Pre-order', 'sold_out' => 'Habis'][$menu->status] ?? 'Tersedia' }}</span>
                                @endif
                            @else
                                @if($menu->status === 'sold_out')<span class="menu-stock-label menu-stock-label-sold_out">Habis</span>@else<a class="button button-outline" href="{{ route('login') }}">{{ $menu->status === 'preorder' ? 'Pre-order' : 'Pesan' }}</a>@endif
                            @endauth
                        </div>
                    </div>
                </article>
            @endforeach
            @if($menus->hasMorePages())<div class="menu-load-sentinel" aria-hidden="true"></div>@endif
        </div>
                
        <div class="pagination">{{ $menus->links() }}</div>
    @else
        <div class="empty-state"><span>⌕</span><h3>Belum ada menu yang cocok</h3><p>Coba ubah kata kunci atau lokasi pencarianmu.</p><a href="{{ route('home') }}" class="text-link">Tampilkan semua menu →</a></div>
    @endif
</section>

<section class="how-section" id="cara-kerja">
<div class="steps-section">
    <div class="steps-intro"><div class="eyebrow eyebrow-muted">GAMPANG BANGET</div><h2>Makan siang enak,<br><em>3 langkah saja.</em></h2><p>Fokus ke hal penting. Urusan makan siang, biar kami yang bantu.</p><a href="{{ route('register') }}" class="button button-dark">Mulai pesan sekarang</a></div>
    <div class="steps-list">
        <div class="step"><div class="step-number">01</div><div class="step-icon">⌕</div><div><h3>Temukan katering favorit</h3><p>Jelajahi beragam menu dari katering lokal di area kantormu.</p></div></div>
        <div class="step"><div class="step-number">02</div><div class="step-icon">▣</div><div><h3>Atur pesanan tim</h3><p>Pilih menu, jumlah porsi, dan tanggal kirim.</p></div></div>
        <div class="step"><div class="step-number">03</div><div class="step-icon">⌁</div><div><h3>Nikmati bersama</h3><p>Makanan segar diantar. Invoice otomatis tersedia.</p></div></div>
    </div>
</div>

<div class="merchant-cta">
    <div><div class="eyebrow">PUNYA USAHA KATERING?</div><h2>Menu lezatmu layak<br>dikenal lebih banyak kantor.</h2><p>Bergabung dengan Ketring Yuk dan temukan pelanggan kantor baru.</p><a href="{{ route('register', ['role' => 'merchant']) }}" class="button button-cream">Daftarkan katering</a></div>
    <div class="cta-art">🥘<span>✳</span></div>
</div>
</section>

<dialog id="order-dialog" class="order-dialog">
    <form method="POST" action="{{ route('orders.store') }}" id="order-form">
        @csrf
        <div class="dialog-head"><div><span class="eyebrow eyebrow-muted">PESAN SEKARANG</span><h2>Rencanakan makan siang</h2></div><button type="button" class="dialog-close" onclick="document.getElementById('order-dialog').close()" aria-label="Tutup">×</button></div>
        <div id="selected-items"></div>
        <label>Tanggal pengiriman<input type="date" name="delivery_date" required min="{{ now()->toDateString() }}"></label>
        <label>Alamat pengiriman<textarea name="delivery_address" required rows="2" placeholder="Alamat lengkap kantor"></textarea></label>
        <label>Catatan untuk katering <span class="optional">(opsional)</span><textarea name="notes" rows="2" placeholder="Contoh: tanpa sambal, kirim ke lobi"></textarea></label>
        <div class="dialog-total"><span>Total pesanan</span><strong id="order-total">Rp 0</strong></div>
        <button class="button button-primary button-wide" type="submit">Buat pesanan</button>
    </form>
</dialog>
@endsection
