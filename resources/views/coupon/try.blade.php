<x-app-layout title="Try a Coupon">

{{-- ── Success receipt (after apply) ──────────────────────────────────── --}}
@if (session('applied'))
@php $a = session('applied'); @endphp
<div class="mb-6 card overflow-hidden"
     x-data="{ show: true }"
     x-show="show"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-2"
     x-transition:enter-end="opacity-100 translate-y-0">
    <div class="bg-emerald-600 px-5 sm:px-6 py-4 flex items-center gap-3">
        <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <div class="text-white min-w-0 flex-1">
            <p class="font-bold">Coupon applied successfully!</p>
            <p class="text-emerald-100 text-sm truncate">{{ $a['code'] }} — {{ $a['title'] }}</p>
        </div>
        <button @click="show = false" class="text-white/60 hover:text-white transition-colors flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    <div class="px-5 sm:px-6 py-5 grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-0 sm:divide-x sm:divide-slate-100">
        <div class="sm:pr-5">
            <p class="text-xs text-slate-400 mb-0.5">Original</p>
            <p class="text-lg font-semibold text-slate-700">₹{{ number_format($a['original_amount']) }}</p>
        </div>
        <div class="sm:px-5">
            <p class="text-xs text-slate-400 mb-0.5">You saved</p>
            <p class="text-lg font-semibold text-emerald-600">−₹{{ number_format($a['savings']) }}</p>
        </div>
        <div class="sm:pl-5">
            <p class="text-xs text-slate-400 mb-0.5">You pay</p>
            <p class="text-lg font-bold text-slate-900">₹{{ number_format($a['final_total']) }}</p>
        </div>
    </div>
    <div class="px-5 sm:px-6 pb-4 text-xs text-slate-400">
        Order ID: <span class="font-mono">{{ substr($a['order_id'], -12) }}</span>
    </div>
</div>
@endif

<div class="max-w-3xl mx-auto">

    <x-page-header
        title="Try a Coupon"
        subtitle="Enter a coupon code and your order amount to see your savings instantly." />

    {{-- ── Coupon simulator ─────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-5 mb-8"
         x-data="{
             code: '{{ request('code', old('code', '')) }}',
             amount: '{{ old('order_total', '') }}',
             checking: false,
             checked: false,
             result: null,
             error: null,

             async check() {
                 if (!this.code.trim() || !this.amount || parseFloat(this.amount) <= 0) return;
                 this.checking = true;
                 this.checked  = false;
                 this.result   = null;
                 this.error    = null;

                 try {
                     const resp = await fetch('{{ route('coupon.validate') }}', {
                         method: 'POST',
                         headers: {
                             'Content-Type': 'application/json',
                             'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                         },
                         body: JSON.stringify({ code: this.code.toUpperCase(), order_total: parseFloat(this.amount) }),
                     });
                     const data = await resp.json();
                     if (resp.ok && data.valid) {
                         this.result = data;
                     } else {
                         this.error = data.error ?? 'Invalid coupon.';
                     }
                 } catch {
                     this.error = 'Connection error. Please try again.';
                 } finally {
                     this.checking = false;
                     this.checked  = true;
                 }
             },

             get savings()    { return this.result ? this.result.savings    : 0; },
             get finalTotal() { return this.result ? this.result.final_total : (parseFloat(this.amount) || 0); },
             get pctOff()     {
                 const amt = parseFloat(this.amount);
                 if (!this.result || !amt) return 0;
                 return Math.round((this.savings / amt) * 100);
             },
         }">

        {{-- ── Left: form ────────────────────────────────────────────── --}}
        <div class="lg:col-span-3 card p-6">
            <h2 class="text-sm font-semibold text-slate-700 mb-5 pb-3 border-b border-slate-100">Enter Details</h2>

            @if ($errors->has('code'))
            <div class="mb-4 p-3 bg-rose-50 border border-rose-200 rounded-xl text-sm text-rose-700">
                {{ $errors->first('code') }}
            </div>
            @endif

            <form method="POST" action="{{ route('coupon.apply') }}" @submit="$event.preventDefault(); check().then(() => { if (result) $el.submit() })">
                @csrf

                {{-- Coupon code --}}
                <div class="mb-5">
                    <label class="form-label">Coupon code</label>
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <input type="text" name="code"
                                   x-model="code"
                                   @input="checked = false; result = null; error = null"
                                   placeholder="e.g. SUMMER25"
                                   maxlength="30"
                                   class="form-input uppercase tracking-widest font-mono pr-10
                                          {{ $errors->has('code') ? 'border-rose-400 focus:ring-rose-400' : '' }}"
                                   autocomplete="off" autocorrect="off" autocapitalize="characters" spellcheck="false">
                            {{-- Status icon --}}
                            <div class="absolute right-3 top-1/2 -translate-y-1/2">
                                <svg x-show="checking" class="w-4 h-4 text-slate-400 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <svg x-show="checked && result" class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <svg x-show="checked && error" class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                        <button type="button" @click="check()"
                                :disabled="!code.trim() || !amount || checking"
                                class="btn-secondary px-4 disabled:opacity-40 disabled:cursor-not-allowed flex-shrink-0">
                            Check
                        </button>
                    </div>

                    {{-- Validation feedback --}}
                    <p x-show="checked && error" x-text="error" x-cloak
                       class="mt-1.5 text-xs text-rose-600"></p>
                    <p x-show="checked && result" x-cloak
                       class="mt-1.5 text-xs text-emerald-600 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span x-text="result ? result.title : ''"></span>
                    </p>
                </div>

                {{-- Order total --}}
                <div class="mb-6">
                    <label class="form-label">Order amount (₹)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-medium">₹</span>
                        <input type="number" name="order_total"
                               x-model="amount"
                               @input="checked = false; result = null; error = null"
                               placeholder="500"
                               min="1" step="1"
                               class="form-input pl-8">
                    </div>
                    <p class="text-xs text-slate-400 mt-1">Enter a simulated order value to calculate your savings.</p>
                </div>

                <button type="submit"
                        :disabled="!code.trim() || !amount || parseFloat(amount) <= 0 || checking"
                        class="btn-primary w-full disabled:opacity-40 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Apply Coupon & Record Usage
                </button>
                <p class="text-xs text-center text-slate-400 mt-2">This records real usage against the coupon's usage limit.</p>
            </form>
        </div>

        {{-- ── Right: live summary panel ────────────────────────────── --}}
        <div class="lg:col-span-2 card p-6 flex flex-col">
            <h2 class="text-sm font-semibold text-slate-700 mb-5 pb-3 border-b border-slate-100">Order Summary</h2>

            {{-- Empty state --}}
            <div x-show="!amount || parseFloat(amount) <= 0" class="flex-1 flex flex-col items-center justify-center text-center py-6">
                <div class="w-14 h-14 bg-stone-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <svg class="w-7 h-7 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                    </svg>
                </div>
                <p class="text-sm text-slate-400">Enter an order amount to see the breakdown.</p>
            </div>

            {{-- Summary rows --}}
            <div x-show="amount && parseFloat(amount) > 0" class="space-y-3 flex-1" style="display:none">
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Subtotal</span>
                    <span class="font-medium text-slate-800" x-text="'₹' + (parseFloat(amount) || 0).toLocaleString('en-IN')"></span>
                </div>

                <div x-show="result" class="flex justify-between text-sm" style="display:none">
                    <span class="text-emerald-600 font-medium flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        Coupon (<span x-text="result ? result.code : ''"></span>)
                    </span>
                    <span class="font-medium text-emerald-600" x-text="'−₹' + (savings).toLocaleString('en-IN')"></span>
                </div>

                <div x-show="error && checked" class="flex justify-between text-sm" style="display:none">
                    <span class="text-rose-500">Coupon invalid</span>
                    <span class="text-slate-400">−₹0</span>
                </div>

                <div class="border-t border-slate-100 pt-3 flex justify-between">
                    <span class="font-semibold text-slate-700">Total</span>
                    <span class="text-xl font-bold"
                          :class="result ? 'text-emerald-700' : 'text-slate-900'"
                          x-text="'₹' + finalTotal.toLocaleString('en-IN')"></span>
                </div>

                {{-- Savings highlight --}}
                <div x-show="result"
                     class="mt-4 bg-emerald-50 border border-emerald-200 rounded-xl p-4 text-center"
                     style="display:none">
                    <p class="text-2xl font-bold text-emerald-700" x-text="'₹' + savings.toLocaleString('en-IN')"></p>
                    <p class="text-xs text-emerald-600 mt-0.5">
                        saved — <span x-text="pctOff + '% off'"></span> your order
                    </p>
                </div>

                {{-- No-deal state --}}
                <div x-show="!result && (!checked || error)"
                     class="mt-4 bg-stone-50 border border-slate-100 rounded-xl p-4 text-center">
                    <p class="text-sm text-slate-400">Enter a valid coupon to see your savings.</p>
                    <a href="{{ route('deals') }}" class="text-xs text-brand-600 hover:text-brand-700 mt-1 inline-block">
                        Browse available coupons →
                    </a>
                </div>
            </div>
        </div>

    </div>{{-- end grid --}}

    {{-- ── Usage history ─────────────────────────────────────────────── --}}
    @if ($recentUsages->isNotEmpty())
    <div class="card p-6">
        <h2 class="text-sm font-semibold text-slate-700 mb-4">Your Recent Usage</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="text-left text-xs font-medium text-slate-400 uppercase tracking-wide pb-2 pr-4">Code</th>
                        <th class="text-left text-xs font-medium text-slate-400 uppercase tracking-wide pb-2 pr-4">Original</th>
                        <th class="text-left text-xs font-medium text-slate-400 uppercase tracking-wide pb-2 pr-4">Saved</th>
                        <th class="text-left text-xs font-medium text-slate-400 uppercase tracking-wide pb-2 pr-4">Final</th>
                        <th class="text-left text-xs font-medium text-slate-400 uppercase tracking-wide pb-2">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach ($recentUsages as $usage)
                    @php
                        $disc = $discounts->get((string) $usage->discount_id);
                    @endphp
                    <tr class="hover:bg-stone-50 transition-colors">
                        <td class="py-2.5 pr-4 font-mono text-xs font-semibold text-slate-700 tracking-wider">
                            {{ $disc?->code ?? '—' }}
                        </td>
                        <td class="py-2.5 pr-4 text-slate-600">₹{{ number_format($usage->original_amount) }}</td>
                        <td class="py-2.5 pr-4 text-emerald-600 font-medium">−₹{{ number_format($usage->discount_applied) }}</td>
                        <td class="py-2.5 pr-4 text-slate-900 font-semibold">₹{{ number_format($usage->final_amount) }}</td>
                        <td class="py-2.5 text-xs text-slate-400">
                            {{ \Carbon\Carbon::parse($usage->used_at)->format('M j, Y') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>

</x-app-layout>
