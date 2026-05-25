<x-guest-layout>

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100 tracking-tight">Welcome back</h2>
        <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Sign in to continue to DiscountPro</p>
    </div>

    {{-- Session status --}}
    @if (session('status') === 'pending-approval')
        <div class="mb-4 px-4 py-3 bg-amber-50 border border-amber-200 text-amber-800 text-sm rounded-xl flex items-start gap-2">
            <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('info') ?? 'Your store-manager registration is pending super-admin approval.' }}</span>
        </div>
    @elseif (session('status'))
        <div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-lg flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5"
          x-data="{ showPass: false, loading: false }"
          x-init="$store.auth.role = '{{ old('role', 'customer') }}'"
          @submit="loading = true">
        @csrf

        {{-- Role selector — writes to the shared Alpine store so the left panel reacts --}}
        <div>
            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Sign in as</p>
            <div class="grid grid-cols-3 gap-2">
                <label class="cursor-pointer group">
                    <input type="radio" name="role" value="customer" x-model="$store.auth.role" class="sr-only peer">
                    <div class="px-2 py-3 rounded-xl border-2 border-slate-200 bg-white peer-checked:border-brand-500 peer-checked:bg-brand-50 peer-checked:shadow-soft-ring text-center transition-all hover:border-brand-300
                                dark:border-slate-700 dark:bg-slate-800/60 dark:hover:border-brand-500 dark:peer-checked:bg-brand-500/15 dark:peer-checked:border-brand-400">
                        <svg class="w-5 h-5 mx-auto mb-1 text-slate-400 peer-checked:text-brand-600 group-has-[:checked]:text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <p class="font-semibold text-xs text-slate-800 dark:text-slate-100">Customer</p>
                    </div>
                </label>
                <label class="cursor-pointer group">
                    <input type="radio" name="role" value="manager" x-model="$store.auth.role" class="sr-only peer">
                    <div class="px-2 py-3 rounded-xl border-2 border-slate-200 bg-white peer-checked:border-brand-500 peer-checked:bg-brand-50 peer-checked:shadow-soft-ring text-center transition-all hover:border-brand-300
                                dark:border-slate-700 dark:bg-slate-800/60 dark:hover:border-brand-500 dark:peer-checked:bg-brand-500/15 dark:peer-checked:border-brand-400">
                        <svg class="w-5 h-5 mx-auto mb-1 text-slate-400 group-has-[:checked]:text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <p class="font-semibold text-xs text-slate-800 dark:text-slate-100">Manager</p>
                    </div>
                </label>
                <label class="cursor-pointer group">
                    <input type="radio" name="role" value="admin" x-model="$store.auth.role" class="sr-only peer">
                    <div class="px-2 py-3 rounded-xl border-2 border-slate-200 bg-white peer-checked:border-rose-500 peer-checked:bg-rose-50 peer-checked:shadow-[0_0_0_4px_rgb(244_63_94/0.10)] text-center transition-all hover:border-rose-300
                                dark:border-slate-700 dark:bg-slate-800/60 dark:hover:border-rose-500 dark:peer-checked:bg-rose-500/15 dark:peer-checked:border-rose-400">
                        <svg class="w-5 h-5 mx-auto mb-1 text-slate-400 group-has-[:checked]:text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <p class="font-semibold text-xs text-slate-800 dark:text-slate-100">Super Admin</p>
                    </div>
                </label>
            </div>
        </div>

        {{-- Email — floating label --}}
        <div class="float-field">
            <svg class="field-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   placeholder="Email address" required autofocus autocomplete="username"
                   class="with-icon">
            <label for="email">Email address</label>
            <x-input-error :messages="$errors->get('email')" />
        </div>

        {{-- Password — floating label + toggle --}}
        <div>
            <div class="float-field">
                <svg class="field-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <input id="password" name="password"
                       :type="showPass ? 'text' : 'password'"
                       placeholder="Password" required autocomplete="current-password"
                       class="with-icon pr-10">
                <label for="password">Password</label>
                <button type="button" @click="showPass = !showPass"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 transition-colors p-1 rounded">
                    <svg x-show="!showPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg x-show="showPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                </button>
            </div>
            <div class="flex items-center justify-end mt-1.5">
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                       class="text-xs text-brand-600 dark:text-brand-300 hover:text-brand-700 dark:hover:text-brand-200 font-medium transition-colors">
                        Forgot password?
                    </a>
                @endif
            </div>
            <x-input-error :messages="$errors->get('password')" />
        </div>

        {{-- Admin-only: secret key --}}
        <div x-show="$store.auth.role === 'admin'" x-cloak>
            <div class="float-field">
                <svg class="field-icon text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                <input id="secret_key" type="password" name="secret_key"
                       placeholder="Super-admin secret key" autocomplete="off"
                       x-bind:required="$store.auth.role === 'admin'"
                       class="with-icon">
                <label for="secret_key">Super-admin secret key</label>
            </div>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Stored in your server's environment configuration.</p>
            <x-input-error :messages="$errors->get('secret_key')" />
        </div>

        {{-- Remember me --}}
        <label x-show="$store.auth.role !== 'admin'" class="flex items-center gap-2.5 cursor-pointer select-none">
            <input type="checkbox" name="remember"
                   class="w-4 h-4 rounded border-slate-300 dark:border-slate-600 text-brand-600 focus:ring-brand-500 focus:ring-offset-0 dark:bg-slate-800">
            <span class="text-sm text-slate-600 dark:text-slate-300">Keep me signed in</span>
        </label>

        {{-- Submit --}}
        <button type="submit" :disabled="loading"
                class="btn-primary w-full justify-center py-3 text-base disabled:opacity-70">
            <span x-show="!loading" class="flex items-center gap-2">
                <span x-text="$store.auth.role === 'admin' ? 'Sign in as admin' : 'Sign in'"></span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </span>
            <span x-show="loading" class="flex items-center gap-2" style="display:none">
                <span class="spinner"></span> Signing in…
            </span>
        </button>

        {{-- Divider --}}
        <div class="relative">
            <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200 dark:border-slate-700"></div></div>
            <div class="relative flex justify-center">
                <span class="px-3 text-xs text-slate-400 dark:text-slate-500 bg-white/80 dark:bg-slate-900/80 backdrop-blur rounded">or</span>
            </div>
        </div>

        <p class="text-center text-sm text-slate-500 dark:text-slate-400">
            Don't have an account?
            <a href="{{ route('register') }}" class="text-brand-600 dark:text-brand-300 hover:text-brand-700 dark:hover:text-brand-200 font-semibold transition-colors">
                Create one
            </a>
        </p>
    </form>

</x-guest-layout>
