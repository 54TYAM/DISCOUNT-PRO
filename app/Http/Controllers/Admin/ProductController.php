<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Scope all queries to the current user's store unless they're an admin.
     * Admins see every store's products across the whole platform.
     */
    private function scope($query)
    {
        $user = auth()->user();
        if ($user->isAdmin()) return $query;
        return $query->where('store_id', (string) $user->store?->_id);
    }

    /** Find a product the current user is allowed to manage. */
    private function findOwned(string $id): Product
    {
        $product = Product::findOrFail($id);
        $user    = auth()->user();
        if (! $user->isAdmin() && (string) $product->store_id !== (string) $user->store?->_id) {
            abort(403, "You can only manage your own store's products.");
        }
        return $product;
    }

    public function index(Request $request)
    {
        $query = $this->scope(Product::query());

        if ($search = $request->get('search')) {
            $query->where('name', 'like', '%' . $search . '%');
        }
        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }
        if ($request->get('status') === 'inactive') {
            $query->where('is_active', false);
        } elseif ($request->get('status') === 'active') {
            $query->where('is_active', true);
        }

        $products = $query->orderBy('created_at', 'desc')->paginate()->withQueryString();

        $counts = [
            'all'      => $this->scope(Product::query())->count(),
            'active'   => $this->scope(Product::query())->where('is_active', true)->count(),
            'inactive' => $this->scope(Product::query())->where('is_active', false)->count(),
        ];

        return view('admin.products.index', compact('products', 'counts'));
    }

    public function create()
    {
        $categories = Product::CATEGORIES;
        return view('admin.products.create', compact('categories'));
    }

    public function store(ProductRequest $request)
    {
        $user = auth()->user();
        $storeId = (string) $user->store?->_id;
        if (! $storeId) {
            return redirect()->route('admin.store.create')
                ->with('error', 'Register your store before adding products.');
        }

        Product::create($request->validated() + ['store_id' => $storeId]);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product added successfully.');
    }

    public function edit(string $id)
    {
        $product    = $this->findOwned($id);
        $categories = Product::CATEGORIES;
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(ProductRequest $request, string $id)
    {
        $product = $this->findOwned($id);
        $product->update($request->validated());

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated.');
    }

    public function destroy(string $id)
    {
        $product = $this->findOwned($id);
        $name    = $product->name;
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', "Product «{$name}» deleted.");
    }

    public function toggle(string $id)
    {
        $product = $this->findOwned($id);
        $product->update(['is_active' => ! $product->is_active]);

        return response()->json(['is_active' => $product->is_active]);
    }
}
