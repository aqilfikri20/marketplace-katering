@extends('layouts.app')

@section('title', $menu->name.' - Ketring Yuk')

@section('content')
<main class="menu-detail-page">
<a class="text-link menu-back-link" href="{{ route('home') }}#menu">&larr; Kembali ke daftar menu</a>

    <section class="menu-detail-card">
        <div class="menu-detail-photo">
            @if($menu->photo)
                <img src="{{ Storage::disk('public')->url($menu->photo) }}" alt="Foto {{ $menu->name }}">
            @else
                <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=1100&q=85" alt="Contoh hidangan {{ $menu->name }}">
            @endif
        </div>
        <div class="menu-detail-content">
            <span class="menu-category-detail">{{ $menu->category ?? $menu->merchant->food_type ?? 'Menu pilihan' }}</span>
            <p class="menu-detail-merchant">Dari {{ $menu->merchant->company_name }}</p>
            <h1>{{ $menu->name }}</h1>
            <div class="menu-detail-rating"><span aria-hidden="true">★</span><strong>{{ number_format($ratingScore, 2) }}</strong><span>/ 5</span><small>{{ $menu->ratings_count }} rating</small></div>
            <span class="menu-stock-status menu-stock-{{ $menu->status }}">{{ ['available' => 'Tersedia', 'preorder' => 'Pre-order', 'sold_out' => 'Habis'][$menu->status] ?? 'Tersedia' }}</span>
            <div class="menu-detail-price">Rp {{ number_format($menu->price, 0, ',', '.') }} <small>/ porsi</small></div>
            <div class="menu-detail-location"><span aria-hidden="true">⌖</span><div><small>Lokasi katering</small><strong>{{ $menu->merchant->address ?: 'Lokasi belum diatur' }}</strong></div></div>
            <div class="menu-detail-description"><h2>Deskripsi menu</h2><p>{{ $menu->description }}</p></div>
            @auth
                @if(auth()->user()->role === 'customer')
                    <a class="button button-primary" href="{{ route('home', ['q' => $menu->name]) }}#menu">Pilih menu ini</a>
                @endif
            @else
                <a class="button button-primary" href="{{ route('login') }}">Masuk untuk memesan</a>
            @endauth
        </div>
    </section>

    <section class="menu-reviews panel">
        <div class="panel-head"><div><h2>Rating dan ulasan</h2><p>Rating dari kantor yang telah menyelesaikan pesanan.</p></div></div>
        @forelse($ratings as $rating)
            <article class="menu-review">
                <div class="menu-review-head"><strong>{{ $rating->customer->company_name ?: $rating->customer->name }}</strong><time datetime="{{ $rating->created_at->toDateString() }}">{{ $rating->created_at->format('d M Y') }}</time></div>
                <div class="menu-review-stars" aria-label="{{ $rating->rating }} dari 5 bintang">{{ str_repeat('★', $rating->rating) }}{{ str_repeat('☆', 5 - $rating->rating) }}</div>
                <p>{{ $rating->comment ?: 'Kantor memberi rating tanpa ulasan tertulis.' }}</p>
            </article>
        @empty
            <div class="menu-reviews-empty"><span aria-hidden="true">☆</span><h3>Belum ada ulasan</h3><p>Jadilah yang pertama memberi rating setelah pesanan selesai.</p></div>
        @endforelse
        @if($ratings->hasPages())<div class="pagination">{{ $ratings->links() }}</div>@endif
    </section>
</main>
@endsection
