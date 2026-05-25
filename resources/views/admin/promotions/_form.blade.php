{{--
  Shared form partial for promotion create & edit.
  Expects: $promo (null on create), $activeDiscounts
--}}
@php
    $p          = $promo ?? null;
    $oldType    = old('type', $p?->type ?? 'flash_sale');
    $oldSegment = old('target_segment', $p?->target_segment ?? 'all');
    $oldColor   = old('banner_color', $p?->banner_color ?? 'violet');
    $isEdit     = ! is_null($p);

    $typeConfig = [
        'flash_sale' => ['label' => 'Flash Sale',  'desc' => 'Short-term urgency campaign with a fixed discount window.',   'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
        'seasonal'   => ['label' => 'Seasonal',    'desc' => 'Holiday or seasonal promotions for recurring events.',        'icon' => 'M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z'],
        'loyalty'    => ['label' => 'Loyalty',     'desc' => 'Reward repeat customers with exclusive member discounts.',    'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
        'referral'   => ['label' => 'Referral',    'desc' => 'Grow your user base by rewarding referrals.',               'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
    ];

    $colors = [
        'violet' => ['bg' => 'bg-violet-600', 'ring' => 'ring-violet-500', 'label' => 'Violet'],
        'brand'  => ['bg' => 'bg-brand-600',  'ring' => 'ring-brand-500',  'label' => 'Brand'],
        'amber'  => ['bg' => 'bg-amber-500',  'ring' => 'ring-amber-400',  'label' => 'Amber'],
        'rose'   => ['bg' => 'bg-rose-500',   'ring' => 'ring-rose-400',   'label' => 'Rose'],
        'emerald'=> ['bg' => 'bg-emerald-600','ring' => 'ring-emerald-500','label' => 'Emerald'],
        'sky'    => ['bg' => 'bg-sky-500',    'ring' => 'ring-sky-400',    'label' => 'Sky'],
        'slate'  => ['bg' => 'bg-slate-600',  'ring' => 'ring-slate-500',  'label' => 'Slate'],
    ];
@endphp

<div x-data="{
    type:    '{{ $oldType }}',
    segment: '{{ $oldSegment }}',
    color:   '{{ $oldColor }}',
}">

{{-- ── Section 1: Campaign Type ────────────────────────────────────────── --}}
<div class="card p-6 mb-5">
    <h3 class="text-sm font-semibold text-slate-700 mb-5 pb-3 border-b border-slate-100">Campaign Type</h3>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        @foreach ($typeConfig as $key => $cfg)
        <label class="relative cursor-pointer group">
            <input type="radio" name="type" value="{{ $key }}"
                   x-model="type"
                   {{ $oldType === $key ? 'checked' : '' }}
                   class="sr-only peer">
            <div class="p-4 rounded-xl border-2 transition-all duration-150 flex flex-col items-center text-center gap-2
                        peer-checked:border-brand-500 peer-checked:bg-brand-50
                        border-slate-200 hover:border-slate-300 hover:bg-stone-50">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center
                            peer-checked:bg-brand-100 bg-slate-100 group-hover:bg-slate-200 transition-colors
                            [.peer:checked~&]:bg-brand-100">
                    <svg class="w-5 h-5 text-slate-500 [.peer:checked~&]:text-brand-600 transition-colors"
                         :class="type === '{{ $key }}' ? 'text-brand-600' : 'text-slate-500'"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $cfg['icon'] }}"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-700" :class="type === '{{ $key }}' ? 'text-brand-700' : ''">
                        {{ $cfg['label'] }}
                    </p>
                </div>
            </div>
        </label>
        @endforeach
    </div>

    {{-- Type description --}}
    @foreach ($typeConfig as $key => $cfg)
    <p x-show="type === '{{ $key }}'" x-cloak
       class="mt-3 text-xs text-slate-500 bg-stone-50 rounded-lg px-3 py-2">
        {{ $cfg['desc'] }}
    </p>
    @endforeach

    <x-input-error :messages="$errors->get('type')" />
</div>

{{-- ── Section 2: Basic Info ────────────────────────────────────────────── --}}
<div class="card p-6 mb-5">
    <h3 class="text-sm font-semibold text-slate-700 mb-5 pb-3 border-b border-slate-100">Campaign Details</h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        {{-- Name --}}
        <div class="sm:col-span-2">
            <x-input-label for="name" value="Campaign name" />
            <x-text-input id="name" name="name" type="text"
                          :value="old('name', $p?->name)"
                          placeholder="e.g. Summer Flash Sale 2025" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        {{-- Description --}}
        <div class="sm:col-span-2">
            <x-input-label for="description" value="Description (optional)" />
            <textarea id="description" name="description" rows="2"
                      placeholder="Internal notes about this campaign…"
                      class="form-input resize-none">{{ old('description', $p?->description) }}</textarea>
            <x-input-error :messages="$errors->get('description')" />
        </div>

        {{-- Linked discount --}}
        <div>
            <x-input-label for="discount_id" value="Linked discount code" />
            <select id="discount_id" name="discount_id" class="form-input">
                <option value="">— None —</option>
                @foreach ($activeDiscounts as $disc)
                <option value="{{ (string) $disc->_id }}"
                        {{ old('discount_id', $p?->discount_id) === (string) $disc->_id ? 'selected' : '' }}>
                    {{ $disc->code }} — {{ $disc->title }}
                </option>
                @endforeach
            </select>
            <p class="text-xs text-slate-400 mt-1">Attach a coupon code customers can redeem during this campaign.</p>
            <x-input-error :messages="$errors->get('discount_id')" />
        </div>

        {{-- Target segment --}}
        <div>
            <x-input-label for="target_segment" value="Target audience" />
            <select id="target_segment" name="target_segment"
                    x-model="segment" class="form-input">
                @foreach (\App\Models\Promotion::SEGMENT_LABELS as $key => $label)
                <option value="{{ $key }}"
                        {{ old('target_segment', $p?->target_segment ?? 'all') === $key ? 'selected' : '' }}>
                    {{ $label }}
                </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('target_segment')" />
        </div>
    </div>
</div>

{{-- ── Section 3: Banner Color ──────────────────────────────────────────── --}}
<div class="card p-6 mb-5">
    <h3 class="text-sm font-semibold text-slate-700 mb-5 pb-3 border-b border-slate-100">Banner Color</h3>

    <div class="flex flex-wrap gap-3 mb-4">
        @foreach ($colors as $key => $c)
        <label class="cursor-pointer">
            <input type="radio" name="banner_color" value="{{ $key }}"
                   x-model="color"
                   {{ $oldColor === $key ? 'checked' : '' }}
                   class="sr-only">
            <div class="w-8 h-8 rounded-full {{ $c['bg'] }} ring-2 ring-offset-2 transition-all"
                 :class="color === '{{ $key }}' ? '{{ $c['ring'] }}' : 'ring-transparent'"
                 title="{{ $c['label'] }}"></div>
        </label>
        @endforeach
    </div>

    {{-- Live preview banner --}}
    <div class="rounded-xl overflow-hidden border border-slate-200">
        <div class="h-2 transition-colors duration-300"
             :class="{
                 'bg-violet-600': color === 'violet',
                 'bg-brand-600':  color === 'brand',
                 'bg-amber-500':  color === 'amber',
                 'bg-rose-500':   color === 'rose',
                 'bg-emerald-600':color === 'emerald',
                 'bg-sky-500':    color === 'sky',
                 'bg-slate-600':  color === 'slate',
             }"></div>
        <div class="p-4 bg-stone-50 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 transition-colors duration-300"
                 :class="{
                     'bg-violet-100': color === 'violet',
                     'bg-brand-100':  color === 'brand',
                     'bg-amber-100':  color === 'amber',
                     'bg-rose-100':   color === 'rose',
                     'bg-emerald-100':color === 'emerald',
                     'bg-sky-100':    color === 'sky',
                     'bg-slate-100':  color === 'slate',
                 }">
                <svg class="w-5 h-5 transition-colors duration-300"
                     :class="{
                         'text-violet-600': color === 'violet',
                         'text-brand-600':  color === 'brand',
                         'text-amber-600':  color === 'amber',
                         'text-rose-600':   color === 'rose',
                         'text-emerald-600':color === 'emerald',
                         'text-sky-600':    color === 'sky',
                         'text-slate-600':  color === 'slate',
                     }"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                          d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-slate-800">Campaign banner preview</p>
                <p class="text-xs text-slate-400">This color appears on customer-facing banners and emails.</p>
            </div>
        </div>
    </div>

    <x-input-error :messages="$errors->get('banner_color')" />
</div>

{{-- ── Section 4: Schedule & Status ─────────────────────────────────────── --}}
<div class="card p-6 mb-5">
    <h3 class="text-sm font-semibold text-slate-700 mb-5 pb-3 border-b border-slate-100">Schedule & Status</h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
            <x-input-label for="start_at" value="Start date & time (optional)" />
            <x-text-input id="start_at" name="start_at" type="datetime-local"
                          :value="old('start_at', $p?->start_at?->format('Y-m-d\TH:i'))" />
            <p class="text-xs text-slate-400 mt-1">Leave blank to run immediately.</p>
            <x-input-error :messages="$errors->get('start_at')" />
        </div>

        <div>
            <x-input-label for="end_at" value="End date & time (optional)" />
            <x-text-input id="end_at" name="end_at" type="datetime-local"
                          :value="old('end_at', $p?->end_at?->format('Y-m-d\TH:i'))" />
            <p class="text-xs text-slate-400 mt-1">Leave blank for no expiry.</p>
            <x-input-error :messages="$errors->get('end_at')" />
        </div>

        <div class="sm:col-span-2">
            <label class="flex items-center gap-3 cursor-pointer select-none">
                <div class="relative">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" id="is_active"
                           {{ old('is_active', $p?->is_active ?? true) ? 'checked' : '' }}
                           class="sr-only peer">
                    <div class="w-10 h-5 bg-slate-200 peer-checked:bg-brand-600 rounded-full transition-colors duration-200"></div>
                    <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow
                                peer-checked:translate-x-5 transition-transform duration-200"></div>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-700">Active</p>
                    <p class="text-xs text-slate-400">Campaign is live and visible to targeted customers.</p>
                </div>
            </label>
        </div>
    </div>
</div>

{{-- ── Form actions ──────────────────────────────────────────────────────── --}}
<div class="flex items-center gap-3 justify-end">
    <a href="{{ route('admin.promotions.index') }}" class="btn-secondary">Cancel</a>
    <button type="submit" class="btn-primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ $isEdit ? 'Save changes' : 'Create campaign' }}
    </button>
</div>

</div>{{-- end x-data --}}
