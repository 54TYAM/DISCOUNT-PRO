<x-account-layout title="Profile Settings">

    <x-page-header
        title="Profile Settings"
        subtitle="Manage your account information and view your activity." />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── Profile form ───────────────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Success alert --}}
            @if (session('status') === 'profile-updated')
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                 class="px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-xl flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Profile updated successfully.
            </div>
            @endif

            <div class="card p-6">
                <h2 class="text-sm font-semibold text-slate-800 mb-5">Personal Information</h2>

                <form method="POST" action="{{ route('profile.update') }}" class="space-y-5">
                    @csrf @method('PATCH')

                    <div>
                        <x-input-label for="name" value="Full name" />
                        <x-text-input id="name" name="name" type="text"
                                      :value="old('name', $user->name)" required />
                        <x-input-error :messages="$errors->get('name')" />
                    </div>

                    <div>
                        <x-input-label for="email" value="Email address" />
                        <x-text-input id="email" name="email" type="email"
                                      :value="old('email', $user->email)" required />
                        <x-input-error :messages="$errors->get('email')" />
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="btn-primary">Save changes</button>
                    </div>
                </form>
            </div>

            {{-- Change password --}}
            <div class="card p-6">
                <h2 class="text-sm font-semibold text-slate-800 mb-5">Change Password</h2>

                @if (session('status') === 'password-updated')
                <div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-lg">
                    Password updated successfully.
                </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                    @csrf @method('PUT')

                    <div>
                        <x-input-label for="current_password" value="Current password" />
                        <x-text-input id="current_password" name="current_password" type="password" />
                        <x-input-error :messages="$errors->updatePassword->get('current_password')" />
                    </div>

                    <div>
                        <x-input-label for="password" value="New password" />
                        <x-text-input id="password" name="password" type="password" />
                        <x-input-error :messages="$errors->updatePassword->get('password')" />
                    </div>

                    <div>
                        <x-input-label for="password_confirmation" value="Confirm new password" />
                        <x-text-input id="password_confirmation" name="password_confirmation" type="password" />
                        <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" />
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="btn-secondary">Update password</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── Account info sidebar ───────────────────────────────────── --}}
        <div class="space-y-5">
            <div class="card p-6 text-center">
                <div class="w-16 h-16 bg-brand-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="text-white text-2xl font-bold">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </span>
                </div>
                <p class="font-semibold text-slate-800">{{ $user->name }}</p>
                <p class="text-slate-400 text-sm mt-0.5">{{ $user->email }}</p>
                <div class="mt-3">
                    <span class="badge-active">{{ $user->role_label }}</span>
                </div>
                <p class="text-xs text-slate-400 mt-3">
                    Member since {{ $user->created_at?->format('M Y') ?? 'N/A' }}
                </p>
            </div>

            {{-- Usage summary --}}
            @php
                $totalSaved = \App\Models\DiscountUsage::where('user_id', (string) $user->_id)->sum('discount_applied');
                $totalUses  = \App\Models\DiscountUsage::where('user_id', (string) $user->_id)->count();
                $recentUses = \App\Models\DiscountUsage::where('user_id', (string) $user->_id)
                    ->orderBy('used_at', 'desc')
                    ->limit(3)
                    ->get();
            @endphp

            <div class="card p-5">
                <h3 class="text-sm font-semibold text-slate-800 mb-4">Your Activity</h3>
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Total saved</span>
                        <span class="font-semibold text-emerald-600">₹{{ number_format($totalSaved, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Coupons used</span>
                        <span class="font-semibold text-slate-800">{{ $totalUses }}</span>
                    </div>
                </div>

                @if ($recentUses->isNotEmpty())
                <div class="mt-4 pt-4 border-t border-slate-100">
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-3">Recent</p>
                    <div class="space-y-2">
                    @foreach ($recentUses as $u)
                        @php $d = App\Models\Discount::find($u->discount_id); @endphp
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-mono bg-slate-100 text-slate-600 px-2 py-0.5 rounded">
                                {{ $d?->code ?? 'N/A' }}
                            </span>
                            <span class="text-emerald-600 font-medium">
                                -₹{{ number_format($u->discount_applied, 2) }}
                            </span>
                        </div>
                    @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

</x-account-layout>
