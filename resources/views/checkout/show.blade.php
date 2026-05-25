<x-app-layout title="Checkout">

<x-page-header title="Checkout" subtitle="Apply a coupon and place your order." />

@if (session('error'))
    <div class="mb-4 p-4 bg-rose-50/80 dark:bg-rose-500/15 backdrop-blur border border-rose-200/70 dark:border-rose-500/30 rounded-xl text-rose-700 dark:text-rose-300 text-sm">
        {{ session('error') }}
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5"
     x-data="checkout({{ json_encode([
         'subtotal' => $subtotal,
         'applied'  => $applied,
     ]) }})">

    {{-- Items --}}
    <div class="lg:col-span-2 space-y-3">
        <div class="card-glass p-5 relative overflow-hidden">
            <div class="glass-sheen"></div>
            <div class="relative">
                <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4 pb-3 border-b border-white/40 dark:border-white/10">Your items</h2>

                <div class="space-y-3">
                @foreach ($lines as $line)
                    @php $store = $storeMap->get((string) $line['product']->store_id); @endphp
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-stone-100 dark:bg-slate-800 rounded-lg overflow-hidden flex-shrink-0">
                            @if ($line['product']->image_url)
                                <img src="{{ $line['product']->image_url }}" alt="" class="w-full h-full object-cover">
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-sm text-slate-800 dark:text-slate-100 line-clamp-1">{{ $line['product']->name }}</p>
                            @if ($store)
                                <p class="text-xs text-slate-400 dark:text-slate-500">{{ $store->name }}</p>
                            @endif
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Qty: {{ $line['qty'] }} × ₹{{ number_format($line['product']->price, 0) }}</p>
                        </div>
                        <p class="font-semibold text-slate-900 dark:text-white text-sm">₹{{ number_format($line['line_total'], 0) }}</p>
                    </div>
                @endforeach
                </div>
            </div>
        </div>

        {{-- Coupon input --}}
        <div class="card-glass p-5 relative overflow-hidden">
            <div class="glass-sheen"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-white/40 dark:border-white/10">
                    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Coupon code</h2>
                    <a href="{{ route('coupons.index') }}" class="text-xs text-brand-600 dark:text-brand-300 hover:text-brand-700 dark:hover:text-brand-200 font-medium">
                        Browse all →
                    </a>
                </div>

                <template x-if="applied">
                    <div class="bg-emerald-50/70 dark:bg-emerald-500/15 backdrop-blur border border-emerald-200/70 dark:border-emerald-500/30 rounded-xl p-4 flex items-center gap-3">
                        <div class="w-9 h-9 bg-emerald-100 dark:bg-emerald-500/25 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-emerald-800 dark:text-emerald-200" x-text="applied?.code"></p>
                            <p class="text-xs text-emerald-700 dark:text-emerald-300" x-text="applied?.title"></p>
                        </div>
                        <form method="POST" action="{{ route('checkout.coupon.remove') }}">
                            @csrf @method('DELETE')
                            <button class="text-xs text-emerald-700 dark:text-emerald-300 hover:text-emerald-900 dark:hover:text-emerald-200 font-medium">Remove</button>
                        </form>
                    </div>
                </template>

                <template x-if="!applied">
                    <div>
                        <div class="flex gap-2">
                            <input type="text" x-model="code" placeholder="Enter coupon code"
                                   class="form-input uppercase tracking-widest font-mono"
                                   autocomplete="off" autocapitalize="characters" spellcheck="false"
                                   @keydown.enter.prevent="apply">
                            <button @click="apply" :disabled="checking || !code.trim()"
                                    class="btn-primary disabled:opacity-40 disabled:cursor-not-allowed">
                                <span x-show="!checking">Apply</span>
                                <span x-show="checking" x-cloak class="inline-flex items-center gap-2">
                                    <span class="spinner"></span> Checking…
                                </span>
                            </button>
                        </div>
                        <p x-show="error" x-cloak x-text="error" class="text-xs text-rose-600 dark:text-rose-400 mt-1.5"></p>
                    </div>
                </template>
            </div>
        </div>

        {{-- ── Relevant coupons for this cart ──────────────────────────────── --}}
        @if (! empty($relevantCoupons))
        <div class="card-glass p-5 relative overflow-hidden" x-show="!applied" x-cloak>
            <div class="glass-sheen"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-white/40 dark:border-white/10">
                    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-200 flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-500 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Coupons you can use
                    </h2>
                    <span class="text-xs text-slate-400 dark:text-slate-500">{{ count($relevantCoupons) }} for your cart</span>
                </div>

                <div class="space-y-2.5">
                    @foreach ($relevantCoupons as $row)
                        @php
                            $c = $row['coupon'];
                            $accent = match($c->type) {
                                'percentage'    => ['bg' => 'bg-brand-50/70 dark:bg-brand-500/15',     'text' => 'text-brand-700 dark:text-brand-300',     'ring' => 'ring-brand-200/70 dark:ring-brand-500/30'],
                                'fixed'         => ['bg' => 'bg-emerald-50/70 dark:bg-emerald-500/15', 'text' => 'text-emerald-700 dark:text-emerald-300', 'ring' => 'ring-emerald-200/70 dark:ring-emerald-500/30'],
                                'bogo'          => ['bg' => 'bg-amber-50/70 dark:bg-amber-500/15',     'text' => 'text-amber-700 dark:text-amber-300',     'ring' => 'ring-amber-200/70 dark:ring-amber-500/30'],
                                'free_shipping' => ['bg' => 'bg-sky-50/70 dark:bg-sky-500/15',         'text' => 'text-sky-700 dark:text-sky-300',         'ring' => 'ring-sky-200/70 dark:ring-sky-500/30'],
                                'tiered'        => ['bg' => 'bg-violet-50/70 dark:bg-violet-500/15',   'text' => 'text-violet-700 dark:text-violet-300',   'ring' => 'ring-violet-200/70 dark:ring-violet-500/30'],
                                default         => ['bg' => 'bg-slate-50/70 dark:bg-slate-700/40',     'text' => 'text-slate-700 dark:text-slate-200',     'ring' => 'ring-slate-200/70 dark:ring-slate-600'],
                            };
                        @endphp
                        <div class="border border-dashed border-white/50 dark:border-white/10 backdrop-blur rounded-xl p-3 flex items-center gap-3 hover:border-brand-300/70 dark:hover:border-brand-400/40 transition-colors"
                             style="background: rgba(255,255,255,0.35);">
                            {{-- Discount badge --}}
                            <div class="flex-shrink-0 w-16 text-center {{ $accent['bg'] }} {{ $accent['text'] }} ring-1 {{ $accent['ring'] }} rounded-lg py-2 backdrop-blur">
                                <p class="text-xs font-bold leading-none">
                                    @if ($c->type === 'percentage')        {{ (int) $c->value }}%
                                    @elseif ($c->type === 'fixed')         ₹{{ (int) $c->value }}
                                    @elseif ($c->type === 'bogo')          BOGO
                                    @elseif ($c->type === 'free_shipping') FREE
                                    @elseif ($c->type === 'tiered')        Up to {{ (int) collect($c->tiered_rules ?? [])->max('discount_pct') }}%
                                    @endif
                                </p>
                                <p class="text-[10px] mt-0.5 leading-none opacity-70">OFF</p>
                            </div>

                            {{-- Details --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-mono text-sm font-semibold text-slate-800 dark:text-slate-100 tracking-wider">{{ $c->code }}</span>
                                    <span class="text-xs text-slate-400 dark:text-slate-500">·</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $row['store_name'] }}</span>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-1">{{ $c->title }}</p>

                                @if (! $row['applicable'])
                                    <p class="text-[11px] text-amber-700 dark:text-amber-300 mt-1 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Add ₹{{ number_format($row['needs_more'], 0) }} more to unlock
                                    </p>
                                @elseif ($row['estimated_savings'] > 0)
                                    <p class="text-[11px] text-emerald-700 dark:text-emerald-300 mt-1 font-medium">
                                        Save ~₹{{ number_format($row['estimated_savings'], 0) }}
                                    </p>
                                @endif
                            </div>

                            {{-- Apply button --}}
                            @if ($row['applicable'])
                                <button type="button"
                                        @click="code = '{{ $c->code }}'; apply()"
                                        class="btn-primary text-xs px-3 py-1.5 flex-shrink-0">
                                    Apply
                                </button>
                            @else
                                <button type="button" disabled
                                        class="text-xs px-3 py-1.5 bg-stone-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 rounded-lg cursor-not-allowed flex-shrink-0">
                                    Locked
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Summary panel (glass) --}}
    <div class="card-glass p-6 h-fit sticky top-24 relative overflow-hidden">
        <div class="glass-sheen"></div>
        <div class="relative">
            <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4 pb-3 border-b border-white/40 dark:border-white/10">Order summary</h2>

            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500 dark:text-slate-400">Subtotal</span>
                    <span class="font-medium text-slate-800 dark:text-slate-100">₹{{ number_format($subtotal, 2) }}</span>
                </div>
                <div x-show="applied" x-cloak class="flex justify-between">
                    <span class="text-emerald-600 dark:text-emerald-300 font-medium">Coupon (<span x-text="applied?.code"></span>)</span>
                    <span class="font-medium text-emerald-600 dark:text-emerald-300">−₹<span x-text="(applied?.savings ?? 0).toFixed(2)"></span></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 dark:text-slate-400">Shipping</span>
                    <span class="font-medium text-slate-800 dark:text-slate-100">Free</span>
                </div>
            </div>

            <div class="border-t border-white/40 dark:border-white/10 pt-4 mt-4 flex justify-between items-center">
                <span class="font-semibold text-slate-800 dark:text-slate-100">Total</span>
                <span class="text-2xl font-bold" :class="applied ? 'text-emerald-700 dark:text-emerald-300' : 'text-slate-900 dark:text-white'">
                    ₹<span x-text="(applied ? applied.final_total : {{ $subtotal }}).toFixed(2)"></span>
                </span>
            </div>

            <form method="POST" action="{{ route('checkout.place') }}" class="mt-5">
                @csrf
                <button type="submit" class="btn-primary w-full justify-center">
                    Place order
                </button>
            </form>
            <a href="{{ route('cart.show') }}" class="btn-secondary w-full justify-center mt-2">Back to cart</a>
        </div>
    </div>

</div>

@push('scripts')
<script>
function checkout(initial) {
    return {
        code: '',
        checking: false,
        error: null,
        applied: initial.applied,

        async apply() {
            if (!this.code.trim()) return;
            this.checking = true;
            this.error = null;
            try {
                const resp = await fetch('{{ route('checkout.coupon') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    },
                    body: JSON.stringify({ code: this.code.toUpperCase() }),
                });
                const data = await resp.json();
                if (resp.ok && data.valid) {
                    this.applied = data;
                    this.code = '';
                } else {
                    this.error = data.error ?? 'Invalid coupon.';
                }
            } catch {
                this.error = 'Connection error. Please try again.';
            } finally {
                this.checking = false;
            }
        },
    };
}
</script>
@endpush

</x-app-layout>
