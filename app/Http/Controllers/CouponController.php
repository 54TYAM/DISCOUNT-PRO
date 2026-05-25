<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use App\Models\DiscountUsage;
use App\Services\CouponService;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function __construct(private CouponService $couponService) {}

    public function show()
    {
        $userId = (string) auth()->user()->_id;

        $recentUsages = DiscountUsage::where('user_id', $userId)
            ->orderBy('used_at', 'desc')
            ->limit(8)
            ->get();

        $discountIds = $recentUsages->pluck('discount_id')->filter()->unique()->values()->all();
        $discounts   = Discount::whereIn('_id', $discountIds)
            ->get(['_id', 'code', 'title'])
            ->keyBy(fn ($d) => (string) $d->_id);

        return view('coupon.try', compact('recentUsages', 'discounts'));
    }

    public function validate(Request $request)
    {
        $request->validate([
            'code'                  => ['required', 'string', 'max:30'],
            'order_total'           => ['required', 'numeric', 'min:0'],
            'cart_items'            => ['nullable', 'array'],
            'cart_items.*.product_id' => ['nullable', 'string'],
            'cart_items.*.category' => ['nullable', 'string'],
        ]);

        $result = $this->couponService->validate(
            $request->code,
            (float) $request->order_total,
            auth()->user(),
            $request->input('cart_items', []),
        );

        if (! $result['valid']) {
            return response()->json(['valid' => false, 'error' => $result['error']], 422);
        }

        $d = $result['discount'];

        return response()->json([
            'valid'       => true,
            'code'        => $d->code,
            'title'       => $d->title,
            'type'        => $d->type,
            'savings'     => $result['savings'],
            'final_total' => $result['final_total'],
        ]);
    }

    public function apply(Request $request)
    {
        $request->validate([
            'code'        => ['required', 'string', 'max:30'],
            'order_total' => ['required', 'numeric', 'min:1'],
        ]);

        $result = $this->couponService->validate(
            $request->code,
            (float) $request->order_total,
            auth()->user()
        );

        if (! $result['valid']) {
            return back()->withErrors(['code' => $result['error']])->withInput();
        }

        $applied = $this->couponService->apply(
            $result['discount'],
            (float) $request->order_total,
            auth()->user()
        );

        // Race-condition guard: apply() re-validates and may now reject
        if (! ($applied['applied'] ?? false)) {
            return back()
                ->withErrors(['code' => $applied['error'] ?? 'Could not apply this coupon.'])
                ->withInput();
        }

        return redirect()->route('coupon.show')->with('applied', $applied);
    }
}
