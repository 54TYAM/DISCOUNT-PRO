<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Store;
use App\Models\User;

/**
 * Super-admin-only: approve or reject pending store-manager registrations.
 * On approval the requested Store is auto-created and the manager can sign in.
 */
class ManagerApprovalController extends Controller
{
    /** Inline guard — only super admins may invoke these actions. */
    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403, 'Only the super admin can review manager applications.');
    }

    public function index()
    {
        $this->authorizeAdmin();

        $pending = User::where('role', User::ROLE_MANAGER)
            ->where('is_approved', false)
            ->orderBy('created_at', 'desc')
            ->paginate();

        $approvedCount = User::where('role', User::ROLE_MANAGER)
            ->where('is_approved', true)
            ->count();

        return view('admin.approvals.index', compact('pending', 'approvedCount'));
    }

    public function approve(string $id)
    {
        $this->authorizeAdmin();

        $user = User::findOrFail($id);

        if (! $user->isManager()) {
            return back()->with('error', 'That user is not a store-manager applicant.');
        }
        if ($user->isApproved()) {
            return back()->with('info', "{$user->name} is already approved.");
        }

        // Auto-create the requested store
        if (! $user->store) {
            Store::create([
                'name'      => $user->requested_store_name ?: $user->name . "'s Store",
                'category'  => $user->requested_store_category ?: 'Other',
                'owner_id'  => (string) $user->_id,
                'is_active' => true,
            ]);
        }

        $user->approve();

        // Notify the manager
        Notification::notify((string) $user->_id, [
            'type'  => 'manager_approved',
            'title' => "Your store has been approved!",
            'body'  => "Welcome to DiscountPro — sign in to start adding products and coupons.",
            'link'  => route('admin.dashboard'),
            'icon'  => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
            'color' => 'emerald',
        ]);

        return redirect()->route('admin.approvals.index')
            ->with('success', "Approved «{$user->name}» — their store is now live.");
    }

    public function reject(string $id)
    {
        $this->authorizeAdmin();

        $user = User::findOrFail($id);

        if (! $user->isManager() || $user->isApproved()) {
            return back()->with('error', 'Only pending manager applications can be rejected.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('admin.approvals.index')
            ->with('success', "Rejected and removed «{$name}»'s application.");
    }
}
