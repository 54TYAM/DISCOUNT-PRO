<x-guest-layout>

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-900">Create your account</h2>
        <p class="text-slate-500 text-sm mt-1">Choose how you'd like to join DiscountPro</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5"
          x-data="{}"
          x-init="$store.auth.role = '{{ old('role', 'customer') }}'">
        @csrf

        {{-- Role selector --}}
        <div>
            <p class="text-sm font-medium text-slate-700 mb-2">I want to register as a…</p>
            <div class="grid grid-cols-2 gap-2">
                <label class="cursor-pointer">
                    <input type="radio" name="role" value="customer" x-model="$store.auth.role" class="sr-only peer">
                    <div class="p-3 rounded-xl border-2 border-slate-200 peer-checked:border-brand-500 peer-checked:bg-brand-50 transition-colors">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-slate-400 peer-checked:text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span class="font-semibold text-sm text-slate-800">Customer</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">Shop and use coupons</p>
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="role" value="manager" x-model="$store.auth.role" class="sr-only peer">
                    <div class="p-3 rounded-xl border-2 border-slate-200 peer-checked:border-brand-500 peer-checked:bg-brand-50 transition-colors">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-slate-400 peer-checked:text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span class="font-semibold text-sm text-slate-800">Store Manager</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">Sell on the platform</p>
                    </div>
                </label>
            </div>
            <x-input-error :messages="$errors->get('role')" />
        </div>

        {{-- Manager notice --}}
        <div x-show="$store.auth.role === 'manager'" x-cloak
             class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-xs flex items-start gap-2">
            <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Store-manager registrations are reviewed by our super admin. You'll be notified once your store is approved (usually within 24 hours).</span>
        </div>

        {{-- Name --}}
        <div>
            <x-input-label for="name" :value="__('Full name')" />
            <x-text-input id="name" type="text" name="name" :value="old('name')"
                          placeholder="John Doe" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        {{-- Email --}}
        <div>
            <x-input-label for="email" :value="__('Email address')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')"
                          placeholder="you@example.com" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        {{-- Manager-only: store details --}}
        <div x-show="$store.auth.role === 'manager'" x-cloak class="space-y-5">
            <div>
                <x-input-label for="store_name" :value="__('Store name')" />
                <x-text-input id="store_name" type="text" name="store_name" :value="old('store_name')"
                              placeholder="e.g. Aurora Electronics" />
                <x-input-error :messages="$errors->get('store_name')" />
            </div>

            <div>
                <x-input-label for="store_category" :value="__('Store category')" />
                <select id="store_category" name="store_category" class="form-input">
                    <option value="">Select…</option>
                    @foreach (\App\Models\Store::CATEGORIES as $cat)
                        <option value="{{ $cat }}" {{ old('store_category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('store_category')" />
            </div>
        </div>

        {{-- Password --}}
        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" type="password" name="password"
                          placeholder="Min. 8 characters" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        {{-- Confirm Password --}}
        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm password')" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation"
                          placeholder="Re-enter your password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        {{-- Submit --}}
        <x-primary-button class="mt-2">
            <span x-text="$store.auth.role === 'manager' ? 'Submit for approval' : 'Create account'"></span>
        </x-primary-button>

        <p class="text-center text-sm text-slate-500">
            Already have an account?
            <a href="{{ route('login') }}" class="text-brand-600 hover:text-brand-700 font-medium transition-colors">
                Sign in
            </a>
        </p>
    </form>

</x-guest-layout>
