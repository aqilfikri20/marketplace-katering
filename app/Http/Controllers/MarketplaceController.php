<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Order;
use App\Models\User;
use App\Support\MarketplaceCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Throwable;

class MarketplaceController extends Controller
{
    public function show(Menu $menu)
    {
        $menu->load('merchant');
        $menu->loadAvg('ratings as average_rating', 'rating');
        $menu->loadCount('ratings');
        $ratings = $menu->ratings()->with('customer')->latest()->paginate(10);
        $ratingScore = $menu->ratings_count ? (float) $menu->average_rating : 5.0;

        return view('marketplace.show', compact('menu', 'ratings', 'ratingScore'));
    }

    public function index(Request $request)
    {
        $search = $request->query('q');
        $search = is_string($search) ? mb_substr(trim($search), 0, 100) : '';
        $location = $request->query('location');
        $location = is_string($location) ? mb_substr(trim($location), 0, 120) : '';
        $type = $request->query('type');
        $type = is_string($type) ? mb_substr(trim($type), 0, 80) : '';
        $pageNumber = filter_var($request->query('page', 1), FILTER_VALIDATE_INT);
        $pageNumber = $pageNumber !== false && $pageNumber > 0 ? $pageNumber : 1;
        $request->query->set('q', $search);
        $request->query->set('location', $location);
        $request->query->set('type', $type);
        $request->query->set('page', $pageNumber);

        $sort = $request->query('sort', 'popular');
        if (! in_array($sort, ['popular', 'price_asc', 'price_desc'], true)) {
            $sort = 'popular';
        }

        $filters = [
            'url' => $request->url(),
            'q' => $request->query('q'),
            'location' => $request->query('location'),
            'type' => $request->query('type'),
            'sort' => $sort,
            'page' => max(1, (int) $request->query('page', 1)),
        ];
        $keyData = json_encode($filters, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $cacheKey = 'marketplace:catalog:v'.MarketplaceCache::version().':'.hash('sha256', $keyData);

        $loadCatalog = function () use ($request, $sort) {
            $menus = Menu::with('merchant')
                ->withSum(['orderItems as total_sold' => fn ($items) => $items->whereHas('order', fn ($orders) => $orders->whereIn('status', [Order::STATUS_CONFIRMED, Order::STATUS_COMPLETED]))], 'quantity')
                ->withAvg('ratings as average_rating', 'rating')
                ->withCount('ratings')
                ->when($request->filled('q'), function ($query) use ($request) {
                    $query->where(function ($query) use ($request) {
                        $query->where('name', 'like', '%'.$request->q.'%')
                            ->orWhere('description', 'like', '%'.$request->q.'%')
                            ->orWhereHas('merchant', fn ($merchant) => $merchant->where('company_name', 'like', '%'.$request->q.'%'));
                    });
                })
                ->when($request->filled('type'), fn ($query) => $query->whereHas('merchant', fn ($merchant) => $merchant->where('food_type', $request->type)))
                ->when($request->filled('location'), fn ($query) => $query->whereHas('merchant', fn ($merchant) => $merchant->where('address', 'like', '%'.$request->location.'%')))
                ->when($sort === 'price_asc', fn ($query) => $query->orderBy('price'))
                ->when($sort === 'price_desc', fn ($query) => $query->orderByDesc('price'))
                ->when($sort === 'popular', fn ($query) => $query->orderByDesc('total_sold')->orderByDesc('menus.created_at'))
                ->paginate(10)
                ->withQueryString();

            $types = User::where('role', 'merchant')->whereNotNull('food_type')->distinct()->pluck('food_type');
            $featured = User::where('role', 'merchant')->withCount('menus')->latest()->take(3)->get();

            return [$menus, $types, $featured];
        };

        try {
            [$menus, $types, $featured] = Cache::remember($cacheKey, now()->addMinutes(5), $loadCatalog);
        } catch (Throwable $exception) {
            report($exception);
            [$menus, $types, $featured] = $loadCatalog();
        }

        MarketplaceCache::track($cacheKey);
        return view('marketplace.index', compact('menus', 'types', 'featured', 'sort'));
    }
}
