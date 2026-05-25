<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Store;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'role'           => ['required', Rule::in(['customer', 'manager'])],
            'name'           => ['required', 'string', 'max:255'],
            'email'          => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password'       => ['required', 'confirmed', Rules\Password::defaults()],
            'store_name'     => ['required_if:role,manager', 'nullable', 'string', 'max:80'],
            'store_category' => ['required_if:role,manager', 'nullable', Rule::in(Store::CATEGORIES)],
        ]);

        $isManager = $request->role === 'manager';

        $user = User::create([
            'name'                     => $request->name,
            'email'                    => $request->email,
            'password'                 => Hash::make($request->password),
            'requested_store_name'     => $isManager ? $request->store_name : null,
            'requested_store_category' => $isManager ? $request->store_category : null,
        ]);

        // Assign role + approval flag from the server side (anti-tampering)
        $user->assignRole($isManager ? User::ROLE_MANAGER : User::ROLE_CUSTOMER);
        $user->forceFill(['is_approved' => ! $isManager])->save();

        event(new Registered($user));

        // Managers must be approved by the super admin first — don't log them in.
        if ($isManager) {
            // Ping every super admin so they can review
            foreach (User::where('role', User::ROLE_ADMIN)->get(['_id']) as $admin) {
                Notification::notify((string) $admin->_id, [
                    'type'  => 'new_application',
                    'title' => "New store-manager application",
                    'body'  => "{$user->name} ({$user->requested_store_name}) is awaiting approval.",
                    'link'  => route('admin.approvals.index'),
                    'icon'  => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z',
                    'color' => 'amber',
                ]);
            }

            return redirect()->route('login')
                ->with('status', 'pending-approval')
                ->with('info', "Thanks {$user->name}! Your store-manager request has been submitted. A super admin will review it shortly — you'll be able to sign in once it's approved.");
        }

        // Welcome notification for new customers
        Notification::notify((string) $user->_id, [
            'type'  => 'welcome',
            'title' => "Welcome to DiscountPro, {$user->name}!",
            'body'  => "Browse the shop, save your favourites, and grab coupons to save on every order.",
            'link'  => route('coupons.index'),
            'icon'  => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
            'color' => 'brand',
        ]);

        Auth::login($user);
        return redirect()->route('shop.index');
    }
}
