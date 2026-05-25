<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRequest;
use App\Models\Store;

class StoreController extends Controller
{
    /** Show the store for the current manager (or admin's selected one). */
    public function show()
    {
        $store = $this->resolveStore();
        if (! $store) {
            return redirect()->route('admin.store.create');
        }

        $stats = [
            'products'   => $store->products()->count(),
            'active'     => $store->products()->where('is_active', true)->count(),
            'discounts'  => $store->discounts()->count(),
            'promotions' => $store->promotions()->count(),
        ];

        return view('admin.store.show', compact('store', 'stats'));
    }

    public function create()
    {
        $user = auth()->user();

        // Admins don't have personal stores
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Super admins manage the platform, not individual stores.');
        }

        if ($user->hasStore()) {
            return redirect()->route('admin.store.show')
                ->with('success', 'You already have a store registered.');
        }

        return view('admin.store.create');
    }

    public function store(StoreRequest $request)
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            abort(403, 'Admins cannot register a personal store.');
        }

        if ($user->hasStore()) {
            return redirect()->route('admin.store.show')
                ->with('error', 'You already have a registered store.');
        }

        $store = Store::create($request->validated() + ['owner_id' => (string) $user->_id]);

        return redirect()->route('admin.store.show')
            ->with('success', "Welcome, «{$store->name}» is now live on the platform!");
    }

    public function edit()
    {
        $store = $this->resolveStore();
        if (! $store) {
            return redirect()->route('admin.store.create');
        }
        return view('admin.store.edit', compact('store'));
    }

    public function update(StoreRequest $request)
    {
        $store = $this->resolveStore();
        if (! $store) abort(404);

        $store->update($request->validated());

        return redirect()->route('admin.store.show')
            ->with('success', 'Store details updated.');
    }

    /** The manager's own store (admins manage the platform — they have no personal store). */
    private function resolveStore(): ?Store
    {
        $user = auth()->user();
        if ($user->isAdmin()) return null;
        return $user->store;
    }
}
