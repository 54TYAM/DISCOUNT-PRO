<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DiscountRequest;
use App\Models\Discount;
use App\Models\DiscountUsage;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    /** Scope queries to the manager's own store; admins see all. */
    private function scope($query)
    {
        $user = auth()->user();
        if ($user->isAdmin()) return $query;
        return $query->where('store_id', (string) $user->store?->_id);
    }

    /** Guard that only the discount's owning manager (or any admin) can touch it. */
    private function findOwned(string $id): Discount
    {
        $d = Discount::findOrFail($id);
        $user = auth()->user();
        if (! $user->isAdmin() && (string) $d->store_id !== (string) $user->store?->_id) {
            abort(403, "You can only manage your own store's discounts.");
        }
        return $d;
    }

    public function index(Request $request)
    {
        $query = $this->scope(Discount::query());

        if ($search = $request->get('search')) {
            $upper = strtoupper($search);
            $query->where(function ($q) use ($search, $upper) {
                $q->where('code', 'like', '%' . $upper . '%')
                  ->orWhere('title', 'like', '%' . $search . '%');
            });
        }

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        switch ($request->get('status')) {
            case 'active':
                $query->where('is_active', true)
                      ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()))
                      ->where(fn ($q) => $q->whereNull('start_date')->orWhere('start_date', '<=', now()));
                break;
            case 'expired':
                $query->where('end_date', '<', now());
                break;
            case 'scheduled':
                $query->where('is_active', true)->where('start_date', '>', now());
                break;
            case 'paused':
                $query->where('is_active', false);
                break;
        }

        $sort      = $request->get('sort', 'created_at');
        $direction = $request->get('dir', 'desc');
        $allowed   = ['created_at', 'used_count', 'end_date', 'title'];
        if (! in_array($sort, $allowed)) $sort = 'created_at';

        $discounts = $query->orderBy($sort, $direction)->paginate()->withQueryString();
        $counts    = [
            'all'       => $this->scope(Discount::query())->count(),
            'active'    => $this->scope(Discount::query())->where('is_active', true)->count(),
            'expired'   => $this->scope(Discount::query())->where('end_date', '<', now())->count(),
            'scheduled' => $this->scope(Discount::query())->where('is_active', true)->where('start_date', '>', now())->count(),
            'paused'    => $this->scope(Discount::query())->where('is_active', false)->count(),
        ];

        return view('admin.discounts.index', compact('discounts', 'counts'));
    }

    public function create()
    {
        $categories = Product::CATEGORIES;
        $storeId    = (string) auth()->user()->store?->_id;
        // Show only this manager's products in the targeting picker
        $products   = $storeId
            ? Product::active()->where('store_id', $storeId)->get(['_id', 'name', 'category'])
            : collect();
        return view('admin.discounts.create', compact('categories', 'products'));
    }

    public function store(DiscountRequest $request)
    {
        $user = auth()->user();
        $data = $request->validated();
        $data['created_by'] = (string) $user->_id;
        $data['used_count'] = 0;

        // Bind discount to the manager's store. Admins can leave it null (platform-wide).
        if (! $user->isAdmin()) {
            if (! $user->store) {
                return redirect()->route('admin.store.create')
                    ->with('error', 'Register your store before creating coupons.');
            }
            $data['store_id'] = (string) $user->store->_id;
        }

        Discount::create($data);

        return redirect()->route('admin.discounts.index')
            ->with('success', "Discount «{$data['code']}» created successfully.");
    }

    public function show(string $id)
    {
        $discount = $this->findOwned($id);

        $usages = DiscountUsage::where('discount_id', $id)
            ->orderBy('used_at', 'desc')
            ->paginate();

        $totalRevenueSaved = DiscountUsage::where('discount_id', $id)->sum('discount_applied');
        $uniqueUsers       = DiscountUsage::where('discount_id', $id)->distinct('user_id')->count('user_id');
        $avgOrderValue     = DiscountUsage::where('discount_id', $id)->avg('original_amount') ?? 0;

        // 14-day daily usage for mini chart
        $last14Days = collect(range(13, 0))->map(function ($daysAgo) use ($id) {
            $date  = now()->subDays($daysAgo)->toDateString();
            $uses  = DiscountUsage::where('discount_id', $id)
                ->whereBetween('used_at', [now()->subDays($daysAgo)->startOfDay(), now()->subDays($daysAgo)->endOfDay()])
                ->count();
            return ['date' => $date, 'uses' => $uses];
        });

        $relatedPromotions = Promotion::where('discount_id', $id)->get();

        return view('admin.discounts.show', compact(
            'discount', 'usages', 'totalRevenueSaved',
            'uniqueUsers', 'avgOrderValue', 'last14Days', 'relatedPromotions'
        ));
    }

    public function edit(string $id)
    {
        $discount   = $this->findOwned($id);
        $categories = Product::CATEGORIES;
        $storeId    = (string) auth()->user()->store?->_id ?: (string) $discount->store_id;
        $products   = $storeId
            ? Product::active()->where('store_id', $storeId)->get(['_id', 'name', 'category'])
            : collect();
        return view('admin.discounts.edit', compact('discount', 'categories', 'products'));
    }

    public function update(DiscountRequest $request, string $id)
    {
        $discount = $this->findOwned($id);
        $discount->update($request->validated());

        return redirect()->route('admin.discounts.show', $id)
            ->with('success', "Discount «{$discount->code}» updated.");
    }

    public function destroy(string $id)
    {
        $discount = $this->findOwned($id);
        $code     = $discount->code;
        $discount->delete();

        return redirect()->route('admin.discounts.index')
            ->with('success', "Discount «{$code}» deleted.");
    }

    public function toggle(string $id)
    {
        $discount = $this->findOwned($id);
        $discount->update(['is_active' => ! $discount->is_active]);

        return response()->json([
            'is_active' => $discount->is_active,
            'status'    => $discount->status,
        ]);
    }

    public function duplicate(string $id)
    {
        $original = $this->findOwned($id);
        $newCode  = 'COPY_' . $original->code;

        // Ensure uniqueness
        $suffix = 2;
        while (Discount::where('code', $newCode)->exists()) {
            $newCode = 'COPY' . $suffix . '_' . $original->code;
            $suffix++;
        }

        $clone = $original->replicate();
        $clone->code       = $newCode;
        $clone->title      = 'Copy of ' . $original->title;
        $clone->used_count = 0;
        $clone->is_active  = false;
        $clone->created_by = (string) auth()->user()->_id;
        $clone->save();

        return redirect()->route('admin.discounts.edit', (string) $clone->_id)
            ->with('success', "Discount duplicated as «{$newCode}». Edit and activate it below.");
    }
}
