<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function show()
    {
        $lines     = $this->cart->lines();
        $subtotal  = $this->cart->subtotal();
        $storeIds  = $this->cart->storeIds();
        $storeMap  = Store::whereIn('_id', $storeIds)->get(['_id', 'name', 'slug'])
            ->keyBy(fn ($s) => (string) $s->_id);

        return view('cart.show', compact('lines', 'subtotal', 'storeMap'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => ['required', 'string'],
            'qty'        => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        $product = Product::where('is_active', true)->find($request->product_id);
        if (! $product) {
            return back()->with('error', 'Product not available.');
        }

        $this->cart->add((string) $product->_id, (int) ($request->qty ?? 1));

        return back()->with('success', "Added «{$product->name}» to your cart.");
    }

    public function update(Request $request)
    {
        $request->validate([
            'product_id' => ['required', 'string'],
            'qty'        => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        $this->cart->setQty($request->product_id, (int) $request->qty);
        return back();
    }

    public function destroy(string $productId)
    {
        $this->cart->remove($productId);
        return back()->with('success', 'Item removed from cart.');
    }

    public function clear()
    {
        $this->cart->clear();
        return back()->with('success', 'Cart cleared.');
    }
}
