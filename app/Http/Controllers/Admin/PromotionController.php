<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PromotionRequest;
use App\Models\Discount;
use App\Models\Promotion;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    private function scope($query)
    {
        $user = auth()->user();
        if ($user->isAdmin()) return $query;
        return $query->where('store_id', (string) $user->store?->_id);
    }

    private function findOwned(string $id): Promotion
    {
        $p = Promotion::findOrFail($id);
        $user = auth()->user();
        if (! $user->isAdmin() && (string) $p->store_id !== (string) $user->store?->_id) {
            abort(403, "You can only manage your own store's promotions.");
        }
        return $p;
    }

    public function index(Request $request)
    {
        $query = $this->scope(Promotion::query());

        if ($search = $request->get('search')) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        switch ($request->get('status')) {
            case 'active':
                $query->where('is_active', true)
                      ->where(fn ($q) => $q->whereNull('end_at')->orWhere('end_at', '>=', now()))
                      ->where(fn ($q) => $q->whereNull('start_at')->orWhere('start_at', '<=', now()));
                break;
            case 'scheduled':
                $query->where('is_active', true)->where('start_at', '>', now());
                break;
            case 'expired':
                $query->where('end_at', '<', now());
                break;
            case 'paused':
                $query->where('is_active', false);
                break;
        }

        $promotions = $query->orderBy('created_at', 'desc')->paginate()->withQueryString();

        $counts = [
            'all'       => $this->scope(Promotion::query())->count(),
            'active'    => $this->scope(Promotion::query())
                              ->where('is_active', true)
                              ->where(fn ($q) => $q->whereNull('end_at')->orWhere('end_at', '>=', now()))
                              ->where(fn ($q) => $q->whereNull('start_at')->orWhere('start_at', '<=', now()))
                              ->count(),
            'scheduled' => $this->scope(Promotion::query())->where('is_active', true)->where('start_at', '>', now())->count(),
            'expired'   => $this->scope(Promotion::query())->where('end_at', '<', now())->count(),
            'paused'    => $this->scope(Promotion::query())->where('is_active', false)->count(),
        ];

        // Attach discount codes without N+1 (small data set)
        $discountIds = $promotions->pluck('discount_id')->filter()->unique()->values()->all();
        $discounts   = Discount::whereIn('_id', $discountIds)->get(['_id', 'code'])->keyBy('_id');

        return view('admin.promotions.index', compact('promotions', 'counts', 'discounts'));
    }

    public function create()
    {
        $user = auth()->user();
        $storeId = (string) $user->store?->_id;
        $activeDiscounts = Discount::active()
            ->when(! $user->isAdmin() && $storeId, fn ($q) => $q->where('store_id', $storeId))
            ->orderBy('code')
            ->get(['_id', 'code', 'title', 'type']);

        return view('admin.promotions.create', compact('activeDiscounts'));
    }

    public function store(PromotionRequest $request)
    {
        $user = auth()->user();
        $data = $request->validated();
        $data['created_by'] = (string) $user->_id;
        $data['view_count'] = 0;

        if (! $user->isAdmin()) {
            if (! $user->store) {
                return redirect()->route('admin.store.create')
                    ->with('error', 'Register your store before creating promotions.');
            }
            $data['store_id'] = (string) $user->store->_id;
        }

        $promo = Promotion::create($data);

        return redirect()->route('admin.promotions.show', (string) $promo->_id)
            ->with('success', "Campaign «{$promo->name}» created successfully.");
    }

    public function show(string $id)
    {
        $promo    = $this->findOwned($id);
        $discount = $promo->discount_id ? Discount::find($promo->discount_id) : null;

        // Sibling promotions using the same discount (excluding self)
        $siblings = $promo->discount_id
            ? Promotion::where('discount_id', $promo->discount_id)
                ->where('_id', '!=', $id)
                ->limit(5)
                ->get()
            : collect();

        return view('admin.promotions.show', compact('promo', 'discount', 'siblings'));
    }

    public function edit(string $id)
    {
        $promo = $this->findOwned($id);
        $user  = auth()->user();
        $storeId = (string) $user->store?->_id ?: (string) $promo->store_id;
        $activeDiscounts = Discount::active()
            ->when(! $user->isAdmin() && $storeId, fn ($q) => $q->where('store_id', $storeId))
            ->orderBy('code')
            ->get(['_id', 'code', 'title', 'type']);

        // Include the currently linked discount even if it's now inactive
        if ($promo->discount_id && ! $activeDiscounts->contains('_id', $promo->discount_id)) {
            $linked = Discount::find($promo->discount_id, ['_id', 'code', 'title', 'type']);
            if ($linked) $activeDiscounts->prepend($linked);
        }

        return view('admin.promotions.edit', compact('promo', 'activeDiscounts'));
    }

    public function update(PromotionRequest $request, string $id)
    {
        $promo = $this->findOwned($id);
        $promo->update($request->validated());

        return redirect()->route('admin.promotions.show', $id)
            ->with('success', "Campaign «{$promo->name}» updated.");
    }

    public function destroy(string $id)
    {
        $promo = $this->findOwned($id);
        $name  = $promo->name;
        $promo->delete();

        return redirect()->route('admin.promotions.index')
            ->with('success', "Campaign «{$name}» deleted.");
    }

    public function toggle(string $id)
    {
        $promo = $this->findOwned($id);
        $promo->update(['is_active' => ! $promo->is_active]);

        return response()->json([
            'is_active' => $promo->is_active,
            'status'    => $promo->status,
        ]);
    }
}
