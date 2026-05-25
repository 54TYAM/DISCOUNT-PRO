<x-guest-layout>

    <div class="mb-8">
        <div class="w-12 h-12 bg-brand-50 rounded-xl flex items-center justify-center mb-5">
            <svg class="w-6 h-6 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-slate-900">Forgot your password?</h2>
        <p class="text-slate-500 text-sm mt-1">
            Enter your email and we'll send you a reset link.
        </p>
    </div>

    @if (session('status'))
        <div class="mb-5 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-lg flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email address')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')"
                          placeholder="you@example.com" required autofocus />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <x-primary-button>
            Send reset link
        </x-primary-button>

        <p class="text-center text-sm text-slate-500">
            <a href="{{ route('login') }}" class="text-brand-600 hover:text-brand-700 font-medium transition-colors">
                ← Back to sign in
            </a>
        </p>
    </form>

</x-guest-layout>
